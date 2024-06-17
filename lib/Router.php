<?php
namespace Techart\BxApp;

/**
 * Класс для работы роутера. Сводит всё во едино.
 */

use \Bitrix\Main\Application;

class Router
{
	private static $routerConfigFile = 'routerConfig.txt';



	/**
	 * Возвращает переданный $url или, если он пустой, то RequestUri
	 *
	 * @param string $url
	 * @return string
	 */
	private static function getCurrentUrl(string $url = ''): string
	{
		return !empty($url) ? $url : Application::getInstance()->getContext()->getRequest()->getRequestUri();
	}

	/**
	 * Возвращает true, если API роутер работает
	 *
	 * @return boolean
	 */
	public static function isActive()
	{
		if (Glob::get('APP_SETUP_API_ROUTER_ACTIVE', true)) {
			if (
				Glob::get('APP_SETUP_API_ROUTER_CHECK_HTTPS', false) === false ||
				(Glob::get('APP_SETUP_API_ROUTER_CHECK_HTTPS', false) === true && CMain::IsHTTPS() === true)
			) {
				if (!empty(Config::get('Router.APP_ROUTER_BUNDLES', []))) {
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

	public static function isCacheActive()
	{
		// у роута включёно кэширование? в файле .env - APP_ROUTER_CACHE_ACTIVE
		return Glob::get('APP_SETUP_API_ROUTER_CACHE_ACTIVE', true);
	}

	/**
	 * Возвращает true, если есть файл кэша роутов
	 *
	 * @return boolean
	 */
	public static function isCacheAlive()
	{
		// файл кэша присутствует?
		if (file_exists(APP_CACHE_ROUTER_DIR.'/'.self::$routerConfigFile)) {
			return true;
		} else {
			return false;
		}
	}

	public static function build()
	{
		$bundles = Config::get('Router.APP_ROUTER_BUNDLES', []);

		if (count($bundles) > 0) {
			foreach ($bundles as $bundle) {
				if (file_exists(APP_ROUTES_DIR.'/'.$bundle.'/Routes.php')) {
					Glob::set('ROUTER_BUILD_CURRENT_BUNDLE', $bundle);
					require_once(APP_ROUTES_DIR.'/'.$bundle.'/Routes.php');
				} else {
					Logger::error('Router: нет файла группы роутов '.$bundle);
				}
			}
			return true;
		} else {
			Logger::error('Router: не указано ни одной группы роутов в APP_ROUTER_BUNDLES');

			return false;
		}
	}

	public static function explodeUrl(string $url = '')
	{
		$match = array_filter(explode('/', $url));
		$stat = [];

		foreach ($match as $k => $v) {
			if (count($stat) == 2) {
				break;
			}
			$stat[] = $v;
			unset($match[$k]);
		}

		return [
			'prefix' => $stat[0],
			'bundle' => mb_strtolower($stat[1]),
			'route' => '/'.implode('/', $match).'/',
		];
	}

	private static function buildPattern(string $template = '', array $routParams = [])
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

	public static function findCurrentRoute(array $routerData = [])
	{
		$currentUri = self::getCurrentUrl();
		$currentRequestMethod = mb_strtolower(Application::getInstance()->getContext()->getRequest()->getRequestMethod());
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

	public static function getRouteFromDataByUrl(string $url = '')
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

	public static function toPageCache(string $url = '', array $pageRouteData = [])
	{
		$curMethod = Application::getInstance()->getContext()->getRequest()->getRequestMethod();
		checkCreateDir(APP_CACHE_ROUTER_PAGES_DIR);

		if (file_put_contents(APP_CACHE_ROUTER_PAGES_DIR.'/'.md5($url.'_'.$curMethod).'.txt', serialize($pageRouteData))) {
			Logger::info('кэш роута для страницы "'.$url.'" записан');
		} else {
			Logger::error('кэш роута для страницы "'.$url.'" записать не удалось');
		}
	}

	public static function getRouteFromPageCacheByUrl(string $url = '')
	{
		$curMethod = Application::getInstance()->getContext()->getRequest()->getRequestMethod();
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

	public static function getRouteFromCacheByUrl(string $url = '')
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
			Logger::error('Router: файл кэша не создан');
		}

		return false;
	}

	public static function toCache()
	{
		checkCreateDir(APP_CACHE_ROUTER_DIR);

		if (file_put_contents(APP_CACHE_ROUTER_DIR.'/'.self::$routerConfigFile, serialize(RouterConfigurator::get()))) {
			Logger::info('кэш роутера записан');
		} else {
			Logger::error('кэш роутера записать не удалось');
		}
	}

	public static function doAction($routeData = [])
	{
		if (!empty($routeData)) {
			if (isset($routeData['controller']) && isset($routeData['method'])) {
				$controllerFile = realpath(APP_ROUTES_DIR.'/'.$routeData['bundle'].'/Controllers/'.$routeData['controller'].'.php');

				if (file_exists($controllerFile)) {
					require_once($controllerFile);
					$controllerClass = 'Routes\\'.$routeData['bundle'].'\\Controllers\\'.$routeData['controller'];

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
