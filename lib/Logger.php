<?
namespace Techart\BxApp;

/**
 * Класс логера.
 *
 * Используется в трейте ErrorTrait, где есть синонимы для назначение ошибок.
 * Подключается в моделях, соответственно в модели есть активные методы:
 *
	$this->critical('Критикал ошибка');
	$this->error('Ошибка');
	$this->warning('Предупреждение');
	$this->info('Сообщение');
	$this->debug('Дебаг инфа');
 *
 * Эти сообщение попадут в ResultTrait и в логер
 *
 * Так же можно писать на прямую в лог:
 * Logger::debug('Дебаг')
 *
 * Логер пишет в файл и отправляет на почту.
 * Можно указывать с какого уровня ошибок делать и то и другое: $typesForEmail, $typesForFile
 * Можно отключать отдельные выводы: $showLoggerBar, $sendEmail, $writeToFile
 *
 * По умолчанию на почту отправляется логи уровня warning и выше.
 * По умолчанию в файл лога пишутся логи уровня debug и выше.
 * В debugbar выводятся все логи, начиная с info.
 */


class Logger
{
	protected static $count = 0; // считает кол-во запомненных сообщений
	protected static $showLoggerBar; // выводить ли дебаг бар на сайт?
	protected static $sendEmail; // отправлять на почту?
	protected static $writeToFile; // записывать в файл лога?
	protected static $emails; // получатели через запятую
	protected static $pathToLogFile = '/Logger'; // путь к файлу лога от www
	protected static $typesForEmail; // с какого типа сообщений отправлять почту
	protected static $typesForFile; // с какого типа сообщений писать в файл
	protected static $curTypeID = 999;
	protected static $types = [
		'frontendError',
		'critical',
		'error',
		'warning',
		'debug',
		'info',
	]; // порядок типов по важности - ПОРЯДОК ВАЖЕН!
	protected static $messages = []; // массив со всеми сообщениями логера


	/**
	 * Назначаем настройки с учётом .env файла
	 *
	 * @return void
	 */
	public static function setup(): void
	{
		self::$showLoggerBar = \Glob::get('APP_SETUP_LOG_BAR', true);
		self::$sendEmail = \Glob::get('APP_SETUP_LOG_TO_EMAIL', true);
		self::$writeToFile = \Glob::get('APP_SETUP_LOG_TO_FILE', true);
		self::$typesForEmail = \Glob::get('APP_SETUP_LOG_LEVEL_EMAIL', 'warning');
		self::$typesForFile = \Glob::get('APP_SETUP_LOG_LEVEL_FILE', 'debug');
		self::$emails = \Glob::get('APP_SETUP_LOG_EMAILS', '');
	}


	/**
	 * Запись сообщения в массив
	 *
	 * @param string $type
	 * @param mixed $message
	 * @return void
	 */
	protected static function write(string $type = 'info', mixed $message = ''): void
	{
		$typeID = array_search($type, self::$types);
		$trace = debug_backtrace();
		$file = '';
		$line = '';

		self::$curTypeID = self::$curTypeID < $typeID ? self::$curTypeID : $typeID;

		if (strpos($trace[1]['file'], 'ErrorTrait') !== false) {
			$file = $trace[2]['file'];
			$line = $trace[2]['line'];
		} else {
			$file = $trace[1]['file'];
			$line = $trace[1]['line'];
		}

		$date = new \DateTime();
		self::$messages[] = [
			'typeID' => $typeID,
			'type' => $type,
			'date' => $date->format ( 'd.m.Y H:i:s:u' ),
			'file' => $file,
			'line' => $line,
			'message' => $message,
		];
		self::$count++;
	}

	static function frontendError(array $params = []): void
	{
		if (Glob::get('APP_SETUP_LOG_FRONTEND_TO_EMAIL', false) || Glob::get('APP_SETUP_LOG_FRONTEND_TO_FILE', false)) {
			$typeID = array_search('frontendError', self::$types);
			$date = new \DateTime();

			self::$curTypeID = self::$curTypeID < $typeID ? self::$curTypeID : $typeID;
			self::$messages[] = [
				'typeID' => $typeID,
				'type' => 'frontendError',
				'date' => $date->format ( 'd.m.Y H:i:s:u' ),
				'message' => $params,
			];
			self::$sendEmail = Glob::get('APP_SETUP_LOG_FRONTEND_TO_EMAIL', false);
			self::$writeToFile = Glob::get('APP_SETUP_LOG_FRONTEND_TO_FILE', false);
			self::$pathToLogFile = '/LoggerFrontend';
		}
	}

	/**
	 * Добавлает в логер запись $message типа critical
	 *
	 * @param mixed $message
	 * @return void
	 */
	static function critical(mixed $message = ''): void
	{
		self::write('critical', $message);
	}

	/**
	 * Добавлает в логер запись $message типа error
	 *
	 * @param mixed $message
	 * @return void
	 */
	static function error(mixed $message = ''): void
	{
		self::write('error', $message);
	}

	/**
	 * Добавлает в логер запись $message типа warning
	 *
	 * @param mixed $message
	 * @return void
	 */
	static function warning(mixed $message = ''): void
	{
		self::write('warning', $message);
	}

	/**
	 * Добавлает в логер запись $message типа debug
	 *
	 * @param mixed $message
	 * @return void
	 */
	public static function debug(mixed $message = ''): void
	{
		self::write('debug', $message);
	}

	/**
	 * Добавлает в логер запись $message типа info
	 *
	 * @param mixed $message
	 * @return void
	 */
	public static function info(mixed $message = ''): void
	{
		self::write('info', $message);
	}

	/**
	 * Добавлает в логер запись $message типа $type
	 *
	 * @param string $type
	 * @param mixed $message
	 * @return void
	 */
	public static function add(string $type = 'info', mixed $message = ''): void
	{
		self::write($type, $message);
	}

	/**
	 * Возвращает текущие запомненные сообщения логера
	 *
	 * @return array
	 */
	public static function get(): array
	{
		return self::$messages;
	}

	/**
	 * Выводит текущие запомненные сообщения логера
	 *
	 * @return array
	 */
	public static function print(): void
	{
		if (strpos($_SERVER['HTTP_HOST'], 'intranet') !== false) {
			dd(self::$messages);
		}
	}

	/**
	 * Выводит логер бар на сайт
	 *
	 * @return void
	 */
	protected static function showLoggerBar(): void
	{
		if (self::$showLoggerBar) {
			if (strpos($_SERVER['HTTP_HOST'], 'intranet') !== false) {
				echo '
				<style>
					.debugbar {
						position: fixed;
						bottom: 0;
						left: 0;
						z-index: 99999999;
						width: 100%;
						font-family: inherit;
					}
					.debugbar * {
						box-sizing: content-box;
					}
					.debugbar__close {
						position: absolute;
						top: 5px;
						right: 20px;
						z-index: 1;
						color: black;
						font-weight: bold;
						font-family: inherit;
						cursor: pointer;
					}
					.debugbar__head {
						position: absolute;
						top: -35px;
						left: 0;
						width: 100%;
						display: flex;
						align-items: center;
						justify-content: center;
						overflow: hidden;
					}
					.debugbar__count {
						position: relative;
						z-index: 1;
						display: flex;
						align-items: center;
						justify-content: center;
						width: 25px;
						height: 18px;
						line-height: 1;
						padding: 10px;
						background-color: red;
						border-radius: 50%;
						color: white;
						font-size: 24px;
						font-weight: bold;
						cursor: pointer;
					}
					.debugbar__count span {
						position: relative;
						z-index: 5;
					}
					.debugbar__count::after {
						position: absolute;
						z-index: 1;
						top: 0;
						left: 0;
						right: 0;
						content: "";
						width: 100%;
						height: 100%;
						background-color: red;
						border-radius: 50% 50% 0 0;
					}
					.debugbar__body {
						display: flex;
						height: 0px;
						border: 3px solid red;
						background-color: #d3a2a2;
						overflow: hidden;
					}
					.debugbar__body.show {
						height: 50vh;
					}
					.debugbar__body-content {
						display: none;
						width: 100%;
						height: 100%;
						padding: 20px;
						overflow: scroll;
						white-space: break-spaces;
					}
					.vis {
						display: block;
					}
					.hide {
						display: none;
					}
				</style>
				<script>
					function toggleBody() {
						let body = document.querySelector(".debugbar__body");
						body.classList.toggle("show");
					}
					function closeDebugbar() {
						let body = document.querySelector(".debugbar");
						body.classList.toggle("hide");
					}
				</script>
				<div class="debugbar">
					<div class="debugbar__head">
						<div class="debugbar__count" data-id="1" onClick="toggleBody()"><span>'.self::$count.'</span></div>
					</div>
					<div class="debugbar__body">
						<div class="debugbar__close" onClick="closeDebugbar()">x</div>
						<div class="debugbar__body-content content-1 vis">'.self::buildLogText('info', '<br><br>').'</div>
					</div>
				</div>';
			}
		}
	}

	/**
	 * Собирает в строку сообщения для лога, тип которых не меньше переданного $type
	 *
	 * @param string $type
	 * @param string $lineBreak
	 * @return string
	 */
	protected static function buildLogText(string $type = 'debug', string $lineBreak = "\r\n"): string
	{
		$text = '';
		$typeID = array_search($type, self::$types);

		if (count(self::$messages) > 0) {
			foreach (self::$messages as $v) {
				if ($v['typeID'] <= $typeID) {
						if ($v['typeID'] == array_search('frontendError', self::$types)) {
							$text .= $v['date']." - ".strtoupper($v['type']).": ".var_export($v['message'], true).$lineBreak;
						} else {
							$text .= $v['date']." - ".strtoupper($v['type']).": ".var_export($v['message'], true)." (".$v['file']." строчка ".$v['line'].")".$lineBreak;
						}
				}

			}
		}

		return $text;
	}

	/**
	 * Записывает в файл self::$pathToLogFile установленные ошибки с учётом типа self::$typesForFile
	 *
	 * @return void
	 */
	protected static function writeToFile(): void
	{
		if (self::$writeToFile && count(self::$messages) > 0) {
			$fileName = self::$pathToLogFile;
			$text = self::buildLogText(self::$typesForFile);

			if (!empty($text)) {
				\Log::write($fileName, $text);
			}
		}
	}

	/**
	 * Отправляет на почту self::$emails установленные ошибки с учётом типа self::$typesForEmail
	 *
	 * @return void
	 */
	protected static function sendEmail(): void
	{
		if (self::$sendEmail && count(self::$messages) > 0) {
			$text = self::buildLogText(self::$typesForEmail);

			if (!empty($text) && !empty(self::$emails)) {
				$type = 'WARNING';
				if (self::$curTypeID == 2) {
					$type = 'ERROR';
				}
				if (self::$curTypeID == 1) {
					$type = 'CRITICAL';
				}
				if (self::$curTypeID == 0) {
					$type = 'FRONTEND_ERROR';
				}

				mail(self::$emails, $type.'! - ошибки с сайта '.$_SERVER['HTTP_HOST'], $text, 'Content-Type: text/plain; charset=utf-8' . "\r\n");
			}
		}
	}

	/**
	 * Запускает обработку ошибок
	 *
	 * @return void
	 */
	public static function final(): void
	{
		self::writeToFile();
		self::sendEmail();
		if (
			strpos($_SERVER['HTTP_ACCEPT'], 'application/json') === false &&
			strpos($_SERVER['REQUEST_URI'], Config::get('Router.APP_ROUTER_PREFIX', 'siteapi')) === false &&
			strpos($_SERVER['REQUEST_URI'], '/bitrix/admin') === false
		) {
			self::showLoggerBar();
		}
	}
}
