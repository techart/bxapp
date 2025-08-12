<?php
namespace Techart\BxApp;

/**
 * Читает и возвращает значения из .env файла
 *
 * Env::get() - полный массив значений из .env
 * Env::get('APP_ENV') - текущая сборка
 * Env::get('QWEQWE') - null, если нет такого ключа
 * Env::get('QWEQWE', 123) - 123, если нет такого ключа
 */

use Dotenv\Dotenv;


class Env
{
	protected static $env = false;


	/**
	 * Возвращает массив значений из файла .env, если $key = ''
	 * Возвращает значение из файла .env, если $key указан
	 * Возвращает null, если значения с ключом $key не существует
	 *
	 * @param string $key
	 * @return mixed
	 */
	protected static function getEnv(string $key = ''): mixed
	{
		if (self::$env === false) {
			$envFile = '.env';

			if (isset(TBA_REGISTRY_SITES[TBA_SITE_ID]['bxappEntities']) && in_array('env', TBA_REGISTRY_SITES[TBA_SITE_ID]['bxappEntities'])) {
				$envFile .= '_'.TBA_SITE_ID;
			}

			$envPath = TBA_PROJECT_ROOT_DIR.'/'.$envFile;

			if (file_exists($envPath)) {
				self::$env = Dotenv::createImmutable([TECHART_BXAPP_CORE_SETUP_DIR, TBA_PROJECT_ROOT_DIR], [$envFile, '.env.default'], false)->load();
			} else {
				self::$env = Dotenv::createImmutable(TECHART_BXAPP_CORE_SETUP_DIR, '.env.default', false)->load();
			}
		}

		if (empty($key)) {
			return self::$env;
		} else {
			if (isset(self::$env[$key])) {
				return self::$env[$key];
			} else {
				return null;
			}
		}
	}

	/**
	 * Возвращает массив всех значений из файла .env, если $key = ''
	 * Возвращает значение из файла .env, если $key указан
	 * Подставляет $default, если такого $key нет
	 *
	 * Приводит строковые 'false', 'true' в булевый тип и т.д.
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public static function get(string $key = '', mixed $default = null): mixed
	{
		$return = null;

		if (empty($key)) {
			$return = self::getEnv();
		} else {
			$value = self::getEnv($key);

			if ($value === null) {
				$return = $default;
			} else {
				$return = $value;
			}

			switch (strtolower($value)) {
				case 'true':
				case '(true)':
					$return = true;
					break;
				case 'false':
				case '(false)':
					$return = false;
					break;
				case 'empty':
				case '(empty)':
					$return = '';
					break;
				case 'null':
				case '(null)':
					$return = null;
					break;
			}
		}

		return $return;
	}
}
