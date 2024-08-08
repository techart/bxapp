<?
namespace Techart\BxApp;


/**
 * Записывает в папку проекта Logs в указанный файл переданный текст:
 *
 * Log::write('Test', '123') - запишет в файл Logs/Test.txt текст 123
 *
 * Файл будет создан, если его нет.
 * Если третьим параметром передать false, то файл будет полностью перезаписан.
 */


class Log
{
	/**
	 * Записывает в папку проекта Logs в файл $fileName, текст $text
	 * Если $append = true, то $text допишется в конец файл, а иначе файл будет перезаписан.
	 *
	 * Возвращает false при ошибке или количество записанных байт при успехе.
	 *
	 * @param string $fileName
	 * @param string $text
	 * @param boolean $append
	 * @return bool|int
	 */
	public static function write(string $fileName = '', string $text = '', bool $append = true): bool|int
	{
		$fileName = APP_LOGS_DIR.'/'.$fileName.'.txt';
		$date = new \DateTime();

		return file_put_contents($fileName, $date->format ( 'd.m.Y H:i:s:u' ).' - '.$text.PHP_EOL, $append ? FILE_APPEND : 0);
	}
}
