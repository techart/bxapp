<?php
namespace Techart\BxApp;

/**
 * Класс для работы роутера. Сводит всё во едино.
 */

use \Bitrix\Main\Application;

class Router
{
	private static $routerConfigFile = 'routerConfig.txt';
	private static $defaultRoutes = ['BxappDefault'];


	private static function getRequestQuery(): string
	{
		return defined('BXAPP_ROUTER_CURRENT_REQUEST_QUERY') ? BXAPP_ROUTER_CURRENT_REQUEST_QUERY : '';
	}

	private static function getRequestMethod(): string
	{
		return defined('BXAPP_ROUTER_CURRENT_REQUEST_METHOD') ? BXAPP_ROUTER_CURRENT_REQUEST_METHOD : Application::getInstance()->getContext()->getRequest()->getRequestMethod();
	}

	private static function getRequestUri(): string
	{
		return defined('BXAPP_ROUTER_CURRENT_REQUEST_URL') ? BXAPP_ROUTER_CURRENT_REQUEST_URL : Application::getInstance()->getContext()->getRequest()->getRequestUri();
	}

	/**
	 * Возвращает переданный $url или, если он пустой, то RequestUri
	 *
	 * @param string $url
	 * @return string
	 */
	private static function getCurrentUrl(string $url = ''): string
	{
		return !empty($url) ? $url : self::getRequestUri();
	}

	/**
	 * Возвращает true, если API роутер работает или false в обратном случае
	 *
	 * @return boolean
	 */
	public static function isActive(): bool
	{
		if (Glob::get('APP_SETUP_API_ROUTER_ACTIVE', true)) {
			if (
				Glob::get('APP_SETUP_API_ROUTER_CHECK_HTTPS', false) === false ||
				(Glob::get('APP_SETUP_API_ROUTER_CHECK_HTTPS', false) === true && CMain::IsHTTPS() === true)
			) {
				if (!empty(Config::get('Router.APP_ROUTER_BUNDLES', [])) || !empty(self::$defaultRoutes)) {
					return true;
				} else {
					Logger::info('Router: Роутер неактивен - нет указанных групп APP_ROUTER_BUNDLES');
					return false;
				}
			} else {
				Logger::info('Router: Роутер неактивен - не используется HTTPS протокол');
				return false;
			}
		} else {
			Logger::info('Router: Роутер неактивен - APP_SETUP_API_ROUTER_ACTIVE = false');
			return false;
		}
	}

	/**
	 * Проверяет true, если кэш роутов активен на сайте
	 * Проверяет в .env файле параметр APP_ROUTER_CACHE_ACTIVE
	 *
	 *
	 * @return boolean
	 */
	public static function isCacheActive(): bool
	{
		// у роута включёно кэширование? в файле .env - APP_ROUTER_CACHE_ACTIVE
		return Glob::get('APP_SETUP_API_ROUTER_CACHE_ACTIVE', true);
	}

	/**
	 * Возвращает true, если есть файл кэша роутов
	 *
	 * @return boolean
	 */
	public static function isCacheAlive(): bool
	{
		// файл кэша присутствует?
		if (file_exists(APP_CACHE_ROUTER_DIR.'/'.self::$routerConfigFile)) {
			return true;
		} else {
			return false;
		}
	}

	public static function buildDefault(): bool
	{
		if (count(self::$defaultRoutes) > 0) {
			foreach (self::$defaultRoutes as $bundle) {
				if (file_exists(APP_CORE_ROUTER_DIR.'/'.$bundle.'/Routes.php')) {
					Glob::set('ROUTER_BUILD_CURRENT_BUNDLE', $bundle);
					App::setBundleProtector([]);
					require_once(APP_CORE_ROUTER_DIR.'/'.$bundle.'/Routes.php');
				} else {
					Logger::error('Router: нет файла группы роутов '.$bundle);
				}
			}
			return true;
		} else {
			Logger::info('Router: не указано ни одной группы роутов в APP_ROUTER_BUNDLES');

			return false;
		}
	}

	/**
	 * Строит роутер
	 *
	 * @return boolean
	 */
	public static function build(): bool
	{
		$bundles = Config::get('Router.APP_ROUTER_BUNDLES', []);

		if (count($bundles) > 0) {
			foreach ($bundles as $bundle) {
				if (file_exists(APP_ROUTER_DIR.'/'.$bundle.'/Routes.php')) {
					Glob::set('ROUTER_BUILD_CURRENT_BUNDLE', $bundle);
					App::setBundleProtector([]);
					require_once(APP_ROUTER_DIR.'/'.$bundle.'/Routes.php');
				} else {
					Logger::error('Router: нет файла группы роутов '.$bundle);
				}
			}
			return true;
		} else {
			Logger::info('Router: не указано ни одной группы роутов в APP_ROUTER_BUNDLES');

			return false;
		}
	}

	/**
	 * Разбирает урл на составляющие
	 *
	 * @param string $url
	 * @return array
	 */
	public static function explodeUrl(string $url = ''): array
	{
		$prefix = (string)Config::get('Router.APP_ROUTER_PREFIX', 'siteapi');
		$match = array_filter(explode($prefix, $url));
		$siteData = explode('/', trim($match[0], '/'));
		$routeData = explode('/', trim($match[1], '/'));
		$bundle = mb_strtolower($routeData[0]);
		unset($routeData[0]);

		return [
			'prefix' => $prefix,
			'bundle' => $bundle,
			'route' => '/'.implode('/', $routeData).'/',
		];
	}

	/**
	 * Составляет итоговый паттерн для поиска роута (с учётом подстановок)
	 *
	 * @param string $template
	 * @param array $routParams
	 * @return string
	 */
	private static function buildPattern(string $template = '', array $routParams = []): string
	{
		if (isset($routParams['where'])) {
			foreach ($routParams['where'] as $var => $pattern) {
				if ($pattern == 'int') {
					$pattern = '[0-9]+';
				}
				if ($pattern == 'string') {
					$pattern = '[a-z-]+';
				}
				if ($pattern == 'stringCase') {
					$pattern = '[a-zA-Z-]+';
				}
				if ($pattern == 'code') {
					$pattern = '[a-zA-Z0-9-]+';
				}
				if (is_array($pattern)) {
					$pattern = '['.implode('|', $pattern).']+';
				}

				$template = str_replace('{'.$var.'}', '('.$pattern.')', $template);
			}

		}

		return $template;
	}

	/**
	 * Ищет роут на основе $routerData
	 *
	 * @param array $routerData
	 * @return mixed
	 */
	public static function findCurrentRoute(array $routerData = []): mixed
	{
		$currentUri = self::getCurrentUrl();
		$currentRequestMethod = mb_strtolower(self::getRequestMethod());
		$prefixBundle = self::explodeUrl($currentUri);

		if (isset($routerData[$currentRequestMethod][$prefixBundle['bundle']])) {//[$prefixBundle['route']]
			foreach ($routerData[$currentRequestMethod][$prefixBundle['bundle']] as $routeUrl => $routParams) {
				$pattern = self::buildPattern($routeUrl, $routParams);
				$pregCase = Config::get('Router.APP_ROUTER_CASE_SENSITIVE', true) ? '' : 'i';

				if (preg_match("#^".$pattern."$#".$pregCase, $prefixBundle['route'], $match)) {
					unset($match[0]);
					$routParams['args'] = $match;

					return $routParams;
				}
			}
		} else {
			Logger::info('не найден роутер с данными: '.implode('; ', $prefixBundle));
			return false;
		}
		return false;
	}

	/**
	 * Ищет роут на основе его урла $url в данных конфигуратора
	 *
	 * @param string $url
	 * @return mixed
	 */
	public static function getRouteFromDataByUrl(string $url = ''): mixed
	{
		$routerData = RouterConfigurator::get();

		if ($routerData !== false) {
			$currentRouteData = self::findCurrentRoute($routerData);

			if ($currentRouteData !== false) {
				return $currentRouteData;
			} else {
				Logger::error('Router: в данных роутера нет переданного урла');
			}
		} else {
			Logger::error('Router: данные повреждены');
		}

		return false;
	}

	/**
	 * Записывает данные роута $pageRouteData для страницы $url в кэш
	 *
	 * @param string $url
	 * @param array $pageRouteData
	 * @return void
	 */
	public static function toPageCache(string $url = '', array $pageRouteData = []): void
	{
		$curMethod = self::getRequestMethod();
		checkCreateDir(APP_CACHE_ROUTER_PAGES_DIR);

		if (file_put_contents(APP_CACHE_ROUTER_PAGES_DIR.'/'.md5($url.'_'.$curMethod).'.txt', serialize($pageRouteData))) {
			Logger::info('кэш роута для страницы "'.$url.'" записан');
		} else {
			Logger::error('кэш роута для страницы "'.$url.'" записать не удалось');
		}
	}

	/**
	 * Ищет роут на основе его урла $url в данных кэша страницы
	 *
	 * @param string $url
	 * @return mixed
	 */
	public static function getRouteFromPageCacheByUrl(string $url = ''): mixed
	{
		$curMethod = self::getRequestMethod();
		$pageCacheData = file_get_contents(APP_CACHE_ROUTER_PAGES_DIR.'/'.md5($url.'_'.$curMethod).'.txt');

		if ($pageCacheData !== false) {
			$routeData = unserialize($pageCacheData);

			if ($routeData !== false) {
				return $routeData;
			} else {
				Logger::error('Router: данные кэша повреждены для страницы '.$url);
			}
		} else {
			Logger::info('Router: не удалось взять данные из файла кэша для страницы '.$url);
		}

		return false;
	}

	/**
	 * Ищет роут на основе его урла $url в данных кэша роутера
	 *
	 * @param string $url
	 * @return mixed
	 */
	public static function getRouteFromCacheByUrl(string $url = ''): mixed
	{
		if (self::isCacheAlive()) {
			$curUrl = self::getCurrentUrl($url);
			$routeCacheData = self::getRouteFromPageCacheByUrl($curUrl);

			if ($routeCacheData !== false) {
				Logger::info('данные роута взяты из кэша страницы');
				return $routeCacheData;
			} else {
				Logger::info('данные роута ищутся в общем кэше роутера');
				$cacheData = file_get_contents(APP_CACHE_ROUTER_DIR.'/'.self::$routerConfigFile);

				if (!empty($cacheData)) {
					if ($cacheData !== false) {
						$routerData = unserialize($cacheData);

						if ($routerData !== false) {
							$currentRouteData = self::findCurrentRoute($routerData);

							if ($currentRouteData !== false) {
								self::toPageCache($curUrl, $currentRouteData);
								return $currentRouteData;
							} else {
								Logger::error('Router: в кэше нет роута для переданного урла');
							}
						} else {
							Logger::error('Router: данные кэша повреждены');
						}
					} else {
						Logger::error('Router: не удалось взять данные из файла кэша');
					}
				} else {
					Logger::error('Router: файл кэша пуст');
				}
			}
		} else {
			Logger::info('Router: файл кэша не создан');
		}

		return false;
	}

	/**
	 * Записывает кэш роутера
	 *
	 * @return void
	 */
	public static function toCache(): void
	{
		checkCreateDir(APP_CACHE_ROUTER_DIR);

		if (file_put_contents(APP_CACHE_ROUTER_DIR.'/'.self::$routerConfigFile, serialize(RouterConfigurator::get()))) {
			Logger::info('кэш роутера записан');
		} else {
			Logger::error('кэш роутера записать не удалось');
		}
	}

	/**
	 * Выполняет экшен роута на основе данных $routeData
	 *
	 * @param array $routeData
	 * @return mixed
	 */
	public static function doAction($routeData = []): mixed
	{
		if (!empty($routeData)) {
			if (isset($routeData['controller']) && isset($routeData['method'])) {
				if ($routeData['bundle'] == 'BxappDefault') {
					// dump(11);
					$controllerFile = APP_CORE_ROUTER_DIR.'/'.$routeData['bundle'].'/Controllers/'.$routeData['controller'].'.php';
					// dump(APP_CORE_ROUTER_DIR);
				} else {
					// dump(22);
					$controllerFile = realpath(APP_ROUTER_DIR.'/'.$routeData['bundle'].'/Controllers/'.$routeData['controller'].'.php');
				}
				// dd($controllerFile);
				if (file_exists($controllerFile)) {
					require_once($controllerFile);
					$controllerClass = 'Router\\'.$routeData['bundle'].'\\Controllers\\'.$routeData['controller'];

					if (class_exists($controllerClass)) {
						$controller = new $controllerClass();

						if (method_exists($controller, $routeData['method'] )) {
							return call_user_func_array([$controller, 'baseAction'], []);
						} else {
							Logger::error('Router: в файле контроллера ('.$controllerFile.') не найден метод - '.$routeData['method']);
						}
					} else {
						Logger::error('Router: в файле контроллера ('.$controllerFile.') не найден класс - '.$routeData['controller']);
					}
				} else {
					Logger::error('Router: файл контроллера не найден - '.$controllerFile);
				}
			} else {
				Logger::error('Router: для роута не задан контроллер или метод');
			}
		} else {
			Logger::error('Router: данные роута не переданы');
		}

		App::core('Main')->do404();
	}
}
