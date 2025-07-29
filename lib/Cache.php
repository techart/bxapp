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

		if (\Config::get('Router.APP_ROUTER_CACHE_REBUILD', false) === false) {
			Router::routerNamesToCache();
			Router::routerConfigToCache();
		}
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
		$models = self::collectModels(APP_MODELS_DIR);

		foreach($models as $model) {
			Directory::deleteDirectory(APP_BITRIX_CACHE_DIR.'/'. App::model($model)->table);
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
		Directory::deleteDirectory(APP_BITRIX_CACHE_DIR.'/'.$siteId);
	}

	/**
	 * Очищает кеш static
	 *
	 * @param string $siteId
	 * @return void
	 */
	public static function clearStatic(string $siteId = ''): void
	{
		Directory::deleteDirectory(APP_CACHE_STATIC_ROOT_DIR.'/'.$siteId);
	}

	/**
	 * Очищает кеш меню
	 *
	 * @param string $siteId
	 * @return void
	 */
	public static function clearMenu(string $siteId = ''): void
	{
		Directory::deleteDirectory(APP_BITRIX_CACHE_DIR.'/'.APP_CACHE_MENU_DIR_NAME.'/'.$siteId);
	}

	/**
	 * Очищает кеш привязки моделей к роутам
	 * 
	 * @param string $siteId
	 * @return void
	 */
	public static function clearRouterModels(string $siteId = ''): void
	{
		$routes = json_decode(file_get_contents(APP_CACHE_MODELS_DIR . '/models.json'), true);
		$tables = [];
		$staticRoutes = [];

		if ($routes !== false) {
			foreach ($routes as $models) {
				$tables = array_merge($tables, $models);
			}

			foreach (array_unique($tables) as $table) {
				$data = json_decode(file_get_contents(APP_CACHE_MODELS_DIR . '/' . $table . '/router.json'));

				if ($data !== false) {
					foreach ($data as $routeData) {
						$staticRoutes = array_merge($staticRoutes, $routeData);
					}
				} else {
					\Logger::info('Не удалось прочитать файл router.json из папки ' . $table);
				}
			}

			$staticRoutes = array_unique($staticRoutes);
			Directory::deleteDirectory(APP_CACHE_MODELS_DIR);

			foreach ($staticRoutes as $route) {
				\H::deleteFile($route . 'data.json', 'static');
			}
		} else {
			\Logger::info('Не удалось прочитать файл models.json');
		}
	}

	/**
	 * Очищает HTML-кеш страниц
	 * 
	 * @param string $siteId
	 * @return void
	 */
	public static function clearHtml(string $siteId = ''): void
	{
		Directory::deleteDirectory(APP_BITRIX_CACHE_DIR.\Config::get('HtmlCache.APP_HTML_CACHE_PATH'));
	}

	/**
	 * Очищает весь кеш
	 *
	 * @param string $siteId
	 * @return void
	 */
	public static function clearAll(string $siteId = ''): void
	{
		self::clear(['all'], $siteId);
		Logger::info('Cache: Очистка всех кешей' . $siteId !== '' ?? ' для сайта ' . $siteId);
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

		if ($cacheName === 'menu' || $cacheName === 'all') {
			self::clearMenu($siteId);
			Logger::info('Cache: Очистка кеша меню');
		}

		if ($cacheName === 'html' || $cacheName === 'all') {
			self::clearHtml($siteId);
		}

		if ($cacheName === 'routerModels' || $cacheName === 'all') {
			self::clearRouterModels($siteId);
			Logger::info('Cache: Очистка кеша роутов привязанных к моделям');
		}
	}
}
