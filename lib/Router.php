<?php
namespace Techart\BxApp;

/**
 * Класс для работы роутера. Сводит всё во едино.
 */

use \Bitrix\Main\Application;

class Router
{
	private static $routerConfigFile = 'routerConfig.txt';
	private static $routerNamesFile = 'routerNames.txt';
	private static $defaultRoutes = ['BxappDefault'];


	public static function routeUrl(string $name = '', array $args = []): string
	{
		$url = '';

		if (!empty($name) && count($args) > 0 && self::isCacheNamesAlive()) {
			$cacheNamesData = file_get_contents(APP_CACHE_ROUTER_DIR.'/'.self::$routerNamesFile);

			if (!empty($cacheNamesData)) {
				if ($cacheNamesData !== false) {
					$routerNamesData = unserialize($cacheNamesData);

					if (isset($routerNamesData['names'][$name])) {
						$search = [];

						foreach (array_keys($args) as $v) {
							$search[] = '{'.$v.'}';
						}

						$url = str_replace($search, array_values($args), $routerNamesData['names'][$name]);
					}
				}
			}
		}

		return $url;
	}

	/**
	 * Возвращает массив текущих GET параметров
	 * Учитывает работу через BxApp роутер и staticapi
	 *
	 * @return array
	 */
	public static function getRequestQuery(): array
	{
		return defined('BXAPP_ROUTER_CURRENT_REQUEST_QUERY') ? BXAPP_ROUTER_CURRENT_REQUEST_QUERY : $_GET;
	}

	/**
	 * Возвращает текущий метод запроса
	 * Учитывает работу через BxApp роутер и staticapi
	 *
	 * @return array
	 */
	public static function getRequestMethod(): string
	{
		if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']) && !empty($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']) &&
			strpos($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'], 'isswagger') !== false && isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']) &&
			!empty($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
				return $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'];
		}

		return defined('BXAPP_ROUTER_CURRENT_REQUEST_METHOD') ? BXAPP_ROUTER_CURRENT_REQUEST_METHOD : Application::getInstance()->getContext()->getRequest()->getRequestMethod();
	}

	/**
	 * Возвращает текущий адрес запроса
	 * Учитывает работу через BxApp роутер и staticapi
	 *
	 * @return array
	 */
	public static function getRequestUri(): string
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
		return urldecode(!empty($url) ? $url : self::getRequestUri());
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

	/**
	 * Возвращает true, если есть файл кэша имён роутов
	 *
	 * @return boolean
	 */
	public static function isCacheNamesAlive(): bool
	{
		// файл кэша присутствует?
		if (file_exists(APP_CACHE_ROUTER_DIR.'/'.self::$routerNamesFile)) {
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
					App::setBundleParams([]);
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
					App::setBundleParams([]);
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
		$parsedUrl = parse_url($match[1]);
		// $siteData = explode('/', trim($match[0], '/'));
		$routeData = explode('/', trim($parsedUrl['path'], '/'));
		$bundle = mb_strtolower($routeData[0]);
		unset($routeData[0]);

		return [
			'prefix' => $prefix,
			'bundle' => $bundle,
			'route' => '/'.implode('/', $routeData).'/',
			'query' => isset($parsedUrl['query']) ? $parsedUrl['query'] : '',
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
					$pattern = '[a-zа-я-]+';
				}
				if ($pattern == 'stringCase') {
					$pattern = '[a-zA-Zа-яА-Я-]+';
				}
				if ($pattern == 'stringEn') {
					$pattern = '[a-z-]+';
				}
				if ($pattern == 'stringEnCase') {
					$pattern = '[a-zA-Z-]+';
				}
				if ($pattern == 'stringRu') {
					$pattern = '[а-я-]+';
				}
				if ($pattern == 'stringRuCase') {
					$pattern = '[а-яА-Я-]+';
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
	 * Проверяет разрешены переданные $requestQuery на основе $routeParams или нет
	 *
	 * $requestQuery - проверяемый массив query параметров
	 * $routeParams - массив данных роута
	 *
	 * Проверки делаются в таком порядке (настройки роута важнее настроек в параметрах):
	 *
	 * Если $routeParams['allowedQueryParams'] = true, то любые query параметры разрешены (вернёт true)
	 * Если $routeParams['allowedQueryParams'] = false, то любые query параметры запрещены (вернёт false)
	 * Если $routeParams['allowedQueryParams'] - массив, то если в $requestQuery есть не перечисленное
	 * в $routeParams['allowedQueryParams'], то вернёт false, а иначе true
	 * Если среди $routeParams['params'] - есть 'noQueryParams', то любые query параметры запрещены (вернёт false)
	 *
	 * @param array $requestQuery
	 * @param array $routeParams
	 * @return boolean
	 */
	private static function isRequestQueryAllowed(array $requestQuery = [], array $routeParams = []): bool
	{
		// если у роута разрешены любые параметры строки
		if ($routeParams['allowedQueryParams'] === true) {
			return true;
		}
		// если у роута запрещены любые параметры строки и они есть в запросе
		if ($routeParams['allowedQueryParams'] === false && !empty($requestQuery)) {
			return false;
		}
		// если у роута перечислены разрешённые параметры строки
		if (is_array($routeParams['allowedQueryParams']) && count($routeParams['allowedQueryParams']) > 0) {
			if (!empty(array_diff(array_keys($requestQuery), $routeParams['allowedQueryParams']))) {
				// если в переданных параметрах строки есть не из разрешённого списка
				return false;
			} else {
				// если все переданные параметры строки разрешены
				return true;
			}
		}
		// если у роута не назначены персональные настройки allowedQueryParams, то проверяем массив params()
		// если в параметрах params() запрещены любые параметры строки ("noQueryParams") и они есть в запросе
		if (isset($routeParams['params']) && is_array($routeParams['params']) && in_array('noQueryParams', $routeParams['params']) && !empty($requestQuery)) {
			return false;
		}

		return true;
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
				$requestQuery = Router::getRequestQuery();

				if (preg_match("#^".$pattern."$#u".$pregCase, $prefixBundle['route'], $match)) {
					if ($currentRequestMethod == 'get') {
						if (self::isRequestQueryAllowed($requestQuery, $routParams) === false) {
							return false;
						}
					}
					unset($match[0]);
					$routParams['args'] = $match;
					$routParams['query'] = $requestQuery;

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
		$cachePath = APP_CACHE_ROUTER_PAGES_DIR.'/'.$curMethod.'/'.$url.'/';

		if (!is_dir($cachePath)) {
			mkdir($cachePath, 0777, true);
		}

		if (file_put_contents($cachePath.'data.txt', serialize($pageRouteData))) {
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
		$cachePath = APP_CACHE_ROUTER_PAGES_DIR.'/'.$curMethod.'/'.$url.'/';
		$pageCacheData = file_get_contents($cachePath.'data.txt');

		if ($pageCacheData !== false) {
			$routeData = unserialize($pageCacheData);

			if ($routeData !== false) {
				if (self::isRequestQueryAllowed(Router::getRequestQuery(), $routeData) === false) {
					return false;
				}
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
								Logger::info('Router: в кэше нет роута для переданного урла');
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
		checkChaineCreateDir(APP_CACHE_ROUTER_DIR);

		if (file_put_contents(APP_CACHE_ROUTER_DIR.'/'.self::$routerConfigFile, serialize(RouterConfigurator::get()))) {
			Logger::info('кэш роутера записан');
		} else {
			Logger::error('кэш роутера записать не удалось');
		}

		if (file_put_contents(APP_CACHE_ROUTER_DIR.'/'.self::$routerNamesFile, serialize(RouterConfigurator::getNames()))) {
			Logger::info('кэш имён роутера записан');
		} else {
			Logger::error('кэш имён роутера записать не удалось');
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
					$controllerFile = APP_CORE_ROUTER_DIR.'/'.$routeData['bundle'].'/Controllers/'.$routeData['controller'].'.php';
				} else {
					$controllerFile = realpath(APP_ROUTER_DIR.'/'.$routeData['bundle'].'/Controllers/'.$routeData['controller'].'.php');
				}
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
