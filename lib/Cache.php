<?php
namespace Techart\BxApp;

use Bitrix\Main\IO\Directory;

class Cache {
	/**
	 * Очищает кеш роутера
	 *
	 * @param string $siteId
	 * @return void
	 */
	public static function clearRouter(string $siteId = ''): void
	{
		Directory::deleteDirectory(APP_CACHE_ROUTER_ROOT_DIR.'/'.$siteId);
	}

	private static function collectModels(string $path, string $subpath = ''): array
	{
		$models = [];
		$dirs = array_filter((array) glob($path.'/*'));

		foreach ($dirs as $dir) {
			if (is_dir($dir)) {
				$newSubpath = $subpath . pathinfo($dir, PATHINFO_BASENAME) . '/';
				$models = array_merge($models, self::collectModels($dir, $newSubpath));
			} else {
				$dir = pathinfo($dir, PATHINFO_FILENAME);
				$models[] = $subpath . $dir;
			}
		}

		return $models;
	}

	/**
	 * Очищает кеш моделей
	 *
	 * @param string $siteId
	 * @return void
	 */
	public static function clearModels(string $siteId = ''): void
	{
		$dirs = Registry::buildBxAppEntitiesDirs($siteId);
		$path = APP_PHP_INTERFACE_DIR . '/' . $dirs['bxAppDir'] . '/' . $dirs['modelsDir'];
		$models = self::collectModels($path);

		foreach($models as $model) {
			Directory::deleteDirectory(SITE_ROOT_DIR."/bitrix/cache/" . App::model($model)->table);
		}
	}

	/**
	 * Очищает кеш blade
	 *
	 * @param string $siteId
	 * @return void
	 */
	public static function clearBlade(): void
	{
		array_map('unlink', array_filter((array) glob(APP_CACHE_BLADE_DIR . '/*')));
	}

	/**
	 * Очищает кеш компонентов
	 *
	 * @param string $siteId
	 * @return void
	 */
	public static function clearComponents(string $siteId = ''): void
	{
		Directory::deleteDirectory(SITE_ROOT_DIR."/bitrix/cache/".$siteId);
	}

	/**
	 * Очищает кеш static
	 *
	 * @param string $siteId
	 * @return void
	 */
	public static function clearStatic(string $siteId = ''): void
	{
	}

	/**
	 * Очищает заданный кеш
	 *
	 * @param string $cacheName
	 * @return void
	 */
	public static function clear(array $options = [], string $siteId = ''): void
	{
		$cacheName = $options[0];

		if ($cacheName === 'router' || $cacheName === 'all') {
			self::clearRouter($siteId);
			Logger::info('Cache: Очистка кеша роутера');
		}

		if ($cacheName === 'models' || $cacheName === 'all') {
			self::clearModels($siteId);
			Logger::info('Cache: Очистка кеша моделей');
		}

		if ($cacheName === 'blade' || $cacheName === 'all') {
			self::clearBlade();
			Logger::info('Cache: Очистка кеша блейда');
		}

		if ($cacheName === 'components' || $cacheName === 'all') {
			self::clearComponents($siteId);
			Logger::info('Cache: Очистка кеша компонентов');
		}

		if ($cacheName === 'static' || $cacheName === 'all') {
			self::clearStatic($siteId);
			Logger::info('Cache: Очистка кеша статики');
		}
	}
}