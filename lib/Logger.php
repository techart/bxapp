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
 * Logger::error('Ошибка');
 * Logger::debug('Дебаг');
 * или
 * Logger::add('info', 'Сообщение');
 *
 * По умолчанию пишет в файл /BxApp/Logs/Logger.log
 * но можно указать кастомный 3 параметром в методах:
 * Logger::add('info', 'Сообщение', 'MyFileError');
 */


class Logger
{
	/**
	 * Добавлает в логер запись $message типа critical
	 *
	 * @param mixed $message
	 * @param string $fileName
	 * @return void
	 */
	public static function critical(mixed $message = '', string $fileName = ''): void
	{
		Log::add('critical', $message, $fileName);
	}

	/**
	 * Добавлает в логер запись $message типа error
	 *
	 * @param mixed $message
	 * @param string $fileName
	 * @return void
	 */
	public static function error(mixed $message = '', string $fileName = ''): void
	{
		Log::add('error', $message, $fileName);
	}

	/**
	 * Добавлает в логер запись $message типа warning
	 *
	 * @param mixed $message
	 * @param string $fileName
	 * @return void
	 */
	public static function warning(mixed $message = '', string $fileName = ''): void
	{
		Log::add('warning', $message, $fileName);
	}

	/**
	 * Добавлает в логер запись $message типа debug
	 *
	 * @param mixed $message
	 * @param string $fileName
	 * @return void
	 */
	public static function debug(mixed $message = '', string $fileName = ''): void
	{
		Log::add('debug', $message, $fileName);
	}

	/**
	 * Добавлает в логер запись $message типа info
	 *
	 * @param mixed $message
	 * @param string $fileName
	 * @return void
	 */
	public static function info(mixed $message = '', string $fileName = ''): void
	{
		Log::add('info', $message, $fileName);
	}

	/**
	 * Добавлает в логер запись $message типа $type
	 *
	 * @param string $type
	 * @param mixed $message
	 * @param string $fileName
	 * @return void
	 */
	public static function add(string $type = 'info', mixed $message = '', string $fileName = ''): void
	{
		Log::add($type, $message, $fileName);
	}
}
