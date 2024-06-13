<?php
namespace Techart\BxApp;

/**
 * Класс для получения настроек из файлов конфига: Configs/***.php
 *
 * если имя конфига не передано, то возвращаем все уже сохранённые конфиги
 * если искомого конфига нет, то возвращает $defValue
 * если ключ не указан, то возвращает все данные конфига
 * если ключа нет в конфиге, то возвращает $defValue
 * если ключ есть в конфиге, то возвращает его значение
 *
 * Имя задаётся по принципу: ИмяФайла.ИмяКлюча
 * Например: Recaptcha.APP_RECAPTCHA_SCORE
 *
 * Config::get('Config') - получит данные из Configs/Config.php
 * Config::get('Config.test') - получит данные из Configs/Config.php и ключа "test"
 *
 * В отличии от Glob данные в конфигах задаются один раз массивом в файле и их нельзя менять, а только получить
 */

class Config
{
	protected static $configs = [];


	/**
	 * Прочесть данные из файла конфига (на прямую из файла)
	 *
	 * @param string $name
	 * @return array
	 */
	private static function read(string $name = '')
	{
		if (!empty($name)) {
			$configFile = APP_CONFIGS_DIR.'/'.$name.'.php';

			if (file_exists($configFile)) {
				$configContent = include_once($configFile);

				if(is_array($configContent)) {
					return $configContent;
				} else {
					\Logger::warning('Файл лога "'.$configFile.'" имеет неправильный формат');
					return false;
				}
			} else {
				\Logger::warning('Файл лога "'.$configFile.'" не найден');
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Получить данные из файла конфига:
	 *
	 * если имя конфига не передано, то возвращаем все уже сохранённые конфиги
	 * если искомого конфига нет, то возвращает $defValue
	 * если ключ не указан, то возвращает все данные конфига
	 * если ключа нет в конфиге, то возвращает $defValue
	 * если ключ есть в конфиге, то возвращает его значение
	 *
	 * Имя задаётся по принципу: ИмяФайла.ИмяКлюча
	 * Например: Recaptcha.APP_RECAPTCHA_SCORE
	 *
	 * @param string $name
	 * @param mixed $defValue
	 * @return array
	 */
	public static function get(string $name = '', mixed $defValue = null):mixed
	{
		if (empty($name)) {
			// если имя конфига не передано, то возвращает все уже сохранённые конфиги
			return self::$configs;
		} else {
			$configName = '';
			$configKey = '';

			if (strpos($name, '.') !== false) {
				$group = explode('.', $name);
				$configName = $group[0];
				$configKey = $group[1];
			} else {
				$configName = $name;
			}

			if (!isset(self::$configs[$configName])) {
				self::$configs[$configName] = self::read($configName);
			}

			if (self::$configs[$configName] !== false) {
				if (empty($configKey)) {
					// если ключ не указан, то возвращает все данные конфига
					return self::$configs[$configName];
				}

				if (isset(self::$configs[$configName][$configKey])) {
					// если ключ есть в конфиге, то возвращает его значение
					return self::$configs[$configName][$configKey];
				} else {
					// если ключа нет в конфиге, то возвращает $defValue
					return $defValue;
				}
			} else {
				// если искомого конфига нет, то возвращает null
				return $defValue;
			}
		}
	}
}
