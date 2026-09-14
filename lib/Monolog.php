<?
namespace Techart\BxApp;

use Monolog\Level;
use Monolog\Utils;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Handler\BufferHandler;
use Monolog\Handler\TestHandler;
use Monolog\Processor\IntrospectionProcessor;

/**
 * Класс для реализации логгеров через Monolog
 * 
 * Настраивается в конфиге Configs/Logger.php
 * В ключе MONOLOG_LOGGERS заполняются все необходимые логгеры:
 * 
 * return [
 * 	'DEFAULT_LOGGER' => 'logger1',
 * 	'DEFAULT_EMAIL' => 'test@techart.ru',
 * 	'MONOLOG_LOGGERS' => [
 * 		'logger1' => [
 * 			'writeToFile' => true,
 * 			'pathToLogFile' => 'Logger/log.log',
 * 			'levelForFile' => 'debug',
 * 			'maskFile' => 'Y-m-d'
 * 		],
 * 		'logger2' => [
 *	 		'writeToFile' => true,
 *	 		'pathToLogFile' => 'Logger/Logger2/log.log',
 *	 		'levelForFile' => 'info',
 *	 		'maskFile' => ''
 * 		],
 * 	]
 * ];
 * 
 * Каждому логгеру можно задать свои параметры:
 * - writeToFile - нужно ли писать в файл
 * - pathToLogFile - путь до файла с логом
 * - levelForFile - начиная с какого типа писать логи в файл
 * - maskFile - маска для метода date() для деления файлов по дате, приписывается в конец названия файла через _ 
 * перед расширением файла. Например: маска Y-m-d будет писать новый файл каждый день с названием типа log_2026-08-05.log
 * 
 * 
 * Обращаться к логгерам через вызов метода соответствующего названию логгера в конфиге:
 * Для логгера с названием logger1 будет вызов Monolog::logger1()
 * 
 * Уровни ошибок: debug, info, notice, warning, error, critical, alert, emergency
 * 
 * Можно обращаться к логгеру по умолчанию через быстрый вызов метода ошибки: Monolog::error('test');
 * В таком случае берётся логгер указанный в DEFAULT_LOGGER. Если DEFAULT_LOGGER не задан, берётся первый логгер в списке.
 * 
 * Настройка отправки на email осуществляется в .env файле под каждый логгер отдельно:
 * 
 * APP_MONOLOG_LOGGER1_SEND_EMAIL=false
 * APP_MONOLOG_LOGGER1_LOG_LEVEL=warning
 * APP_MONOLOG_LOGGER1_EMAIL=test@techart.ru
 * APP_MONOLOG_LOGGER2_SEND_EMAIL=false
 * APP_MONOLOG_LOGGER2_LOG_LEVEL=error
 * APP_MONOLOG_LOGGER2_EMAIL=test@techart.ru
 * 
 * Заполняется по шаблону APP_MONOLOG_[Логгер]_[Параметр]. Название логгера и параметра должно быть написано в верхнем регистре.
 * Для каждого логгера настраиваются следующие параметры:
 * - SEND_EMAIL - Нужно ли отправлять лог на email
 * - LOG_LEVEL - Уровень с которого нужно отправлять ошибки на email
 * - EMAIL - список email на который нужно отправлять логи
 * 
 * Если значения не заполнены, то берутся значения по умолчанию:
 * SEND_EMAIL=false
 * LOG_LEVEL=warning
 * EMAIL - берётся из DEFAULT_EMAIL
 */
class Monolog
{
	private static $loggers = [];
	private static $emailFrom = null;
	private static $defaultEmails = '';
	private static $defaultLogLevel = '';

	public static function setup(): void
	{
		self::$emailFrom = \COption::GetOptionString("main", "email_from", "");
		self::$defaultEmails = \Glob::get('APP_SETUP_LOG_EMAILS', '');
		self::$defaultLogLevel = \Glob::get('APP_SETUP_LOG_LEVEL_DEBUGBAR', 'warning');
		$loggersParams = \Config::get('Logger.MONOLOG_LOGGERS', []);

		foreach ($loggersParams as $key => $params) {
			self::$loggers[$key] = self::createLogger($key, $params);
		}
	}

	public static function getLoggers(): array
	{
		return self::$loggers;
	}

	private static function createLogger(string $name = '', array $config = []): object {
		$logger = new Logger($name);
		$logger->pushProcessor(new IntrospectionProcessor('info'));

		if (isset($config['writeToFile']) && $config['writeToFile'] && isset($config['pathToLogFile']) && !empty($config['pathToLogFile'])) {
			$path = self::buildFilePath($config['pathToLogFile'], $config['maskFile']);
			$logger->pushHandler(new StreamHandler($path, Level::fromName($config['levelForFile'] ?? 'info')));
		}

		$needSend = Env::get('APP_MONOLOG_' . strtoupper($name) . '_SEND_EMAIL', false);
		if ($needSend) {
			$emails = Env::get('APP_MONOLOG_' . strtoupper($name) . '_EMAIL', null);
			$level = Env::get('APP_MONOLOG_' . strtoupper($name) . '_LOG_LEVEL', 'warning');

			$mailHandler = new NativeMailerHandler(
				$emails ?? self::$defaultEmails,
				'('.$name.') ошибки с сайта '.$_SERVER['HTTP_HOST'],
				self::$emailFrom,
				Level::fromName($level ?? self::$defaultLogLevel)
			);
			$mailHandler->addHeader('Content-type: text/html; charset=utf-8');

			$mailHandler->setFormatter(new MonologMailFormatter(
				'd.m.Y H:i:s,u'
			));

			$logger->pushHandler(new BufferHandler($mailHandler));
		}

		if (DebugBar::checkSetup()) {
			$logger->pushHandler(new TestHandler());
		}

		return $logger;
	}

	private static function buildFilePath(string $path = '', string $mask = ''): string {
		if (empty($mask)) {
			return $path;
		}

		$pathInfo = pathinfo($path);
		$path = '';
		$path .= !empty($pathInfo['dirname']) && $pathInfo['dirname'] !== '.' ? $pathInfo['dirname'] . '/' : '';
		$path .= $pathInfo['filename'] . '_' . date($mask);
		$path .= isset($pathInfo['extension']) ? '.' . $pathInfo['extension'] : '';

		return $path;
	}

	public static function __callStatic($name, $arguments) {
		if(count(self::$loggers) > 0 && in_array($name, ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'])) {
			$logger = \Config::get('Logger.DEFAULT_LOGGER');
			
			if (empty($logger)) {
				$logger = array_key_first(self::$loggers);
			}

			if (isset(self::$loggers[$logger])) {
				return call_user_func([self::$loggers[$logger], $name], ...$arguments);
			} else {
				\Logger::error("Ни одного Monolog логгера не заполнено или в DEFAULT_LOGGER указан несуществующий логгер");
				throw new \LogicException("Ни одного Monolog логгера не заполнено или в DEFAULT_LOGGER указан несуществующий логгер");
			}
		}

		if (isset(self::$loggers[$name]) && !empty(self::$loggers[$name])) {
			return self::$loggers[$name];
		}

		\Logger::error("Логгер ".$name." не существует");
		throw new \LogicException("Логгер ".$name." не существует");
	}

	public static function getLoggerLog(string $name = '')
	{
		$log = '';
		$loggers = self::$loggers;

		if (!empty($name)) {
			if (isset($loggers[$name])) {
				$loggers = [$loggers[$name]];
			} else {
				\Logger::error("Логгер ".$name." не существует");
				throw new \LogicException("Логгер ".$name." не существует");
			}
		}

		foreach ($loggers as $key => $logger) {
			$logLevel = Env::get('APP_MONOLOG_' . strtoupper($key) . '_LOG_LEVEL', 'warning');
			$handlersList = $logger->getHandlers();
			$handlers = [];

			foreach ($handlersList as $handler) {
				$class = Utils::getClass($handler);
				$handlers[substr($class, strrpos($class, '\\') + 1)] = $handler;
			}

			if (isset($handlers['TestHandler'])) {
				$records = $handlers['TestHandler']->getRecords();

				if (is_array($records) && count($records) > 0) {
					$log .= '<b>' . ($key !== array_key_first($loggers) ? '<br>' : '') . '[' . $key . ']';
					if (isset($handlers['StreamHandler'])) {
						$log .= ' (' . $handlers['StreamHandler']->getUrl() . ')';
					}
					$log .= '</b>';

					foreach ($records as $record) {
						if (!$record->level->isLowerThan(Level::fromName($logLevel))) {
							$dumpOutput = '';
							if (!empty($record['context'])) {
								ob_start();
								dump($record['context']);
								$dumpOutput = ob_get_clean();
							}

							ob_start(); ?>
							<div class="tba_debug_bar__line">
								<b><?= $record['datetime']->format('d.m.Y H:i:s,u') ?></b> - <b>[<?= $logger->getLevelName($record['level']) ?>]</b> - <?= $record['message'] ?>
								<span class="tba_debug_bar__calledFrom"> (<?= $record['extra']['file'] ?> on line <?= $record['extra']['line'] ?>)</span>
								<? if (!empty($record['context'])): ?>
									{{CONTENT_DUMP}}
								<? endif; ?>
							</div>
							<? $content = ob_get_clean();
							$content = str_replace(["\t", "\n"], "", $content);
							$content = str_replace('{{CONTENT_DUMP}}', $dumpOutput, $content);

							$log .= $content;
						}
					}
				}
			}
		}

		return $log;
	}
}