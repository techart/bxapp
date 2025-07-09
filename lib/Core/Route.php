<?php
namespace Techart\BxApp\Core;

/**
 * Класс для создания роутов.
 *
 * Структура роутов хранится в папке Router. В ней создаётся подпапка - она же имя бандла.
 * В данной папке создаётся файл Routes.php, где указываются все роуты бандла.
 * А так же папка Controllers, в которой хранятся файлы контроллеров бандла.
 *
 * У роутера есть настройки в файле .env и в конфиге Router.php - в них есть комментарии что и как.
 *
 * Путь роута составляется так: APP_ROUTER_PREFIX + ИМЯ_ПАПКИ_БАНДЛА + ИМЯ_ГРУППЫ + УКАЗАННЫЙ_ПУТЬ
 *
 * ИМЯ_ГРУППЫ задаётся через метод group(), но его может не быть или имя группы может быть пустой.
 * Пустая группа может быть полезна для задания протекторов.
 *
 * В файле Routes.php роуты задаются таким образом, например для бандла Catalog:
 *
 * App::route()->get('/get-all/{name}/{id}/', 'Actions.mainTestt')->where(['name' => '[a-zA-Z]+', 'id' => '[0-9]+']);
 *
 * При заходе на урл вида /siteapi/catalog/get-all/[a-zA-Z]+/[0-9]+/ в метод mainTestt() контроллера Actions.php и
 * бандла Catalog будут переданы две переменные с данными из соответствующих регулярок.
 *
 * Регулярки не обязательно каждый раз писать руками, есть готовые подстановки, например:
 *
 * App::route()->get('/get-all/int/{id}/', 'Actions.mainTestt')->where(['name' => 'int']);
 * Нужная регулярка [0-9]+ подставится сама.
 *
 *
 * Полный список подстановок и соответствующих регулярок:
 *
 * int			: [0-9]+
 * string		: [a-z-]+
 * stringCase	: [a-zA-Z-]+
 * code			: [a-zA-Z0-9-]+
 *
 * Если в значении массива where будет массив, то он заменится на регулярку через или (|) по значениям массива:
 * where(['array' => ['raz', 'dva', 'tri']]) - [raz|dva|tri]+
 * Таким образом можно ограничить доступ только конкретными совпадениями
 *
 *
 * Можно обЪединять роуты в группы:
 *
 * App::route()->group('action', function($r) {
 * 		$r->get('/delete/', 'Actions.Test'); // /siteapi/catalog/action/delete/
 * 		$r->get('/update/', 'Actions.Test'); // /siteapi/catalog/action/update/
 * });
 *
 *
 * Отдельным роутам и группам можно задавать протектор
 *
 * App::route()->get('/get-all/', 'Actions.mainTestt')->protector(['checkDomain', 'checkSession', 'checkAuth']);
 *
 * Заданное для группы плюсуется ко всем её роутам.Ниже группа без имени - для задания общего протектора
 *
 * App::route()->protector(['checkDomain'])->group('', function($r) {
 * 		$r->get('/delete/', 'Actions.Test')->protector(['checkSession']); // протектор будет checkDomain и checkSession
 * 		$r->get('/update/', 'Actions.Test'); // протектор будет checkDomain
 * });
 *
 * Роуты задаются по методу запроса: get, post, delete, put, options для чего есть одноимённые методы.
 * Вместо App::route()->get() можно написать App::route()->post() и т.д.
 * Таким образом может быть несколько одинаковых урлов.
 *
 * Роуту можно задать имя с помощью метода name().
 * Имя роута используется для задания middleware, что делается в конфиге /Configs/Middleware.php
 */

class Route
{
	private $url = '';
	private $requestMethod = '';
	private $bundle = '';
	private $routeBundle = '';
	private $routeProtector = [];
	private $routeParams = [];
	// private $routeModels = [];
	private $defaultRouteName = '';
	private $group = '';
	private $groupProtector = [];
	private $groupParams = [];
	// private $groupModels = [];
	private $protector = [];
	private $params = [];
	// private $models = [];



	/**
	 * Назначает текущему бандлу глобальные протекторы
	 *
	 * @param array $protector
	 * @return void
	 */
	public static function setBundleProtector(array $protector = []): void
	{
		\Techart\BxApp\Glob::set('ROUTER_BUILD_CURRENT_BUNDLE_PROTECTOR', $protector);
	}

	/**
	 * Назначает текущему бандлу глобальные параметры
	 *
	 * @param array $params
	 * @return void
	 */
	public static function setBundleParams(array $params = []): void
	{
		\Techart\BxApp\Glob::set('ROUTER_BUILD_CURRENT_BUNDLE_PARAMS', $params);
	}

	// NOTE: Удалить если не нужно выставлять модели в конфиге роута
	// /**
	//  * Назначает текущему бандлу глобальные модели
	//  *
	//  * @param array $models
	//  * @return void
	//  */
	// public static function setBundleModels(array $models = []): void
	// {
	// 	\Techart\BxApp\Glob::set('ROUTER_BUILD_CURRENT_BUNDLE_MODELS', $models);
	// }

	/**
	 * Оборачивает несколько роутов в группу.
	 * $group - может быть пустой строкой
	 *
	 * @param string $group
	 * @param callable $method
	 * @return void
	 */
	public function group($group = '', callable $method): void
	{
		$this->group = $group;

		if (is_callable($method)) {
			call_user_func($method, $this);
		}
	}

	/**
	 * Возвращает текущий разобранный урл или false
	 *
	 * @return mixed
	 */
	public function getCurrentUrl(): mixed
	{
		if (empty($this->group) && empty($this->url)) {
			return false;
		} else {
			return preg_replace('/(\/+)/','/', '/'.$this->group.'/'.$this->url.'/');
		}
	}

	/**
	 * Основной метод.
	 * Добавляет роут в массив роутов (роутер).
	 * Работает через класс RouterConfigurator.
	 *
	 * @param string $requestMethod
	 * @param string $url
	 * @param string $classMethod
	 * @param mixed $props
	 * @return object
	 */
	public function setRoute(string $requestMethod = '', string $url = '', string $classMethod = '', mixed $props = ''): object
	{
		$classMethod = explode('.', $classMethod);

		if (empty($classMethod) || count($classMethod) < 2) {
			\Logger::warning('Route: незаданн контроллер или его метод для url = '.$url);
		} else {
			$this->url = $url;
			$this->requestMethod = mb_strtolower($requestMethod);
			$this->bundle = mb_strtolower(\Glob::get('ROUTER_BUILD_CURRENT_BUNDLE', ''));
			$this->routeBundle = \Glob::get('ROUTER_BUILD_CURRENT_BUNDLE', '');
			$this->routeProtector = \Glob::get('ROUTER_BUILD_CURRENT_BUNDLE_PROTECTOR', []);
			$this->routeParams = \Glob::get('ROUTER_BUILD_CURRENT_BUNDLE_PARAMS', []);
			// NOTE: Удалить если не нужно выставлять модели в конфиге роута
			// $this->routeModels = \Glob::get('ROUTER_BUILD_CURRENT_BUNDLE_MODELS', []);
			$this->defaultRouteName = strtolower($requestMethod.'-'.$this->bundle.(!empty($this->group) ? '-'.$this->group : '').'-'.str_replace(['{', '}'], '', str_replace('/', '-', trim($this->getCurrentUrl(), '/'))));

			\Techart\BxApp\RouterConfigurator::setRequestMethod($requestMethod);
			\Techart\BxApp\RouterConfigurator::setBundle($this->requestMethod, $this->bundle);
			\Techart\BxApp\RouterConfigurator::setRouteUrl($this->requestMethod, $this->bundle, $this->getCurrentUrl());
			\Techart\BxApp\RouterConfigurator::setRouteRequestMethod($this->requestMethod, $this->bundle, $this->getCurrentUrl());
			\Techart\BxApp\RouterConfigurator::setRouteName($this->requestMethod, $this->bundle, $this->getCurrentUrl(), $this->defaultRouteName);
			\Techart\BxApp\RouterConfigurator::setRouteBundle($this->requestMethod, $this->bundle, $this->getCurrentUrl(), $this->routeBundle);
			\Techart\BxApp\RouterConfigurator::setRouteGroup($this->requestMethod, $this->bundle, $this->getCurrentUrl(), $this->group);
			\Techart\BxApp\RouterConfigurator::setRouteMethod($this->requestMethod, $this->bundle, $this->getCurrentUrl(), $classMethod[0], $classMethod[1]);

			if ($this->requestMethod == 'get') {
				\Techart\BxApp\RouterConfigurator::setRouteAllowedQueryParams($this->requestMethod, $this->bundle, $this->getCurrentUrl(), $props);
			}

			if (!empty($this->routeProtector)) {
				\Techart\BxApp\RouterConfigurator::setRouteProtector($this->requestMethod, $this->bundle, $this->getCurrentUrl(), $this->routeProtector);
			}
			if (!empty($this->groupProtector)) {
				\Techart\BxApp\RouterConfigurator::setRouteProtector($this->requestMethod, $this->bundle, $this->getCurrentUrl(), array_merge($this->routeProtector, $this->groupProtector));
			}
			if (!empty($this->routeParams)) {
				\Techart\BxApp\RouterConfigurator::setRouteParams($this->requestMethod, $this->bundle, $this->getCurrentUrl(), $this->routeParams);
			}
			if (!empty($this->groupParams)) {
				\Techart\BxApp\RouterConfigurator::setRouteParams($this->requestMethod, $this->bundle, $this->getCurrentUrl(), array_merge($this->routeParams, $this->groupParams));
			}
			// NOTE: Удалить если не нужно выставлять модели в конфиге роута
			/*if (!empty($this->routeModels)) {
				\Techart\BxApp\RouterConfigurator::setRouteModels($this->requestMethod, $this->bundle, $this->getCurrentUrl(), $this->routeModels);
			}
			if (!empty($this->groupModels)) {
				\Techart\BxApp\RouterConfigurator::setRouteModels($this->requestMethod, $this->bundle, $this->getCurrentUrl(), array_merge($this->routeModels, $this->groupModels));
			}
			if (!empty($this->routeBundle)) {
				if (file_exists(APP_ROUTER_DIR . '/' . $this->routeBundle . '/RoutesAPI.php')) {
					if (!isset(\Techart\BxApp\RouterConfigurator::$bundles[$this->routeBundle])) {
						\Techart\BxApp\RouterConfigurator::$bundles[$this->routeBundle] = include_once(APP_ROUTER_DIR . '/' . $this->routeBundle . '/RoutesAPI.php');
					}

					if (!empty(\Techart\BxApp\RouterConfigurator::$bundles[$this->routeBundle])) {
						$data = \Techart\BxApp\RouterConfigurator::$bundles[$this->routeBundle];
						if (isset($data[$this->defaultRouteName]) && !empty($data[$this->defaultRouteName]['models'])) {
							$this->models = array_merge(\Glob::get('ROUTER_BUILD_CURRENT_BUNDLE_MODELS', []), $this->groupModels, $data[$this->defaultRouteName]['models']);

							\Techart\BxApp\RouterConfigurator::setRouteModels($this->requestMethod, $this->bundle, $this->getCurrentUrl(), $this->models);
						}
					}
				}
			}*/
			if (!empty($this->routeBundle)) {
				if (file_exists(APP_ROUTER_DIR . '/' . $this->routeBundle . '/RoutesAPI.php')) {
					if (!isset(\Techart\BxApp\RouterConfigurator::$bundles[$this->routeBundle])) {
						\Techart\BxApp\RouterConfigurator::$bundles[$this->routeBundle] = include_once(APP_ROUTER_DIR . '/' . $this->routeBundle . '/RoutesAPI.php');
					}

					if (!empty(\Techart\BxApp\RouterConfigurator::$bundles[$this->routeBundle])) {
						$data = \Techart\BxApp\RouterConfigurator::$bundles[$this->routeBundle];

						if (isset($data[$this->defaultRouteName]) && !empty($data[$this->defaultRouteName]['data'])) {
							\Techart\BxApp\RouterConfigurator::setRouteData($this->requestMethod, $this->bundle, $this->getCurrentUrl(), $data[$this->defaultRouteName]['data']);
						}
					}
				}
			}
		}
		return $this;
	}

	/**
	 * Создаёт роут с реквест методом get.
	 *
	 * $url - урл роута, можно использовать подстановки
	 * $classMethod - указывается через точку имя файла экшена и метода в нём: "Actions.method"
	 * $allowedQueryParams - либо массив разрешённых query параметров (названия), либо true|false:
	 * true - разрешены все (проверка в экшене), false - запрещены все
	 *
	 * @param string $url
	 * @param string $classMethod
	 * @param string $allowedQueryParams
	 * @return object
	 */
	public function get(string $url = '', string $classMethod = '', bool|array $allowedQueryParams = []): object
	{
		$this->setRoute('get', $url, $classMethod, $allowedQueryParams);

		return $this;
	}

	/**
	 * Создаёт роут с реквест методом post.
	 *
	 * $url - урл роута, можно использовать подстановки
	 * $classMethod - указывается через точку имя файла экшена и метода в нём: "Actions.method"
	 *
	 * @param string $url
	 * @param string $classMethod
	 * @return object
	 */
	public function post(string $url = '', string $classMethod = ''): object
	{
		$this->setRoute('post', $url, $classMethod);

		return $this;
	}

	/**
	 * Создаёт роут с реквест методом delete.
	 *
	 * $url - урл роута, можно использовать подстановки
	 * $classMethod - указывается через точку имя файла экшена и метода в нём: "Actions.method"
	 *
	 * @param string $url
	 * @param string $classMethod
	 * @return object
	 */
	public function delete(string $url = '', string $classMethod = ''): object
	{
		$this->setRoute('delete', $url, $classMethod);

		return $this;
	}

	/**
	 * Создаёт роут с реквест методом put.
	 *
	 * $url - урл роута, можно использовать подстановки
	 * $classMethod - указывается через точку имя файла экшена и метода в нём: "Actions.method"
	 *
	 * @param string $url
	 * @param string $classMethod
	 * @return object
	 */
	public function put(string $url = '', string $classMethod = ''): object
	{
		$this->setRoute('put', $url, $classMethod);

		return $this;
	}

	/**
	 * Создаёт роут с реквест методом options.
	 *
	 * $url - урл роута, можно использовать подстановки
	 * $classMethod - указывается через точку имя файла экшена и метода в нём: "Actions.method"
	 *
	 * @param string $url
	 * @param string $classMethod
	 * @return object
	 */
	public function options(string $url = '', string $classMethod = ''): object
	{
		$this->setRoute('options', $url, $classMethod);

		return $this;
	}

	/**
	 * Задаёт роуту подстановки.
	 * В роуте пишется в фигурных скобках имя подстановки.
	 *
	 * $where - массив, где ключ - имя подстановки, а значение - регулярное выражение, ключевое слово или массив вариантов
	 *
	 * @param array $where
	 * @return object
	 */
	public function where(array $where = []): object
	{
		if ($this->getCurrentUrl() !== false) {
			\Techart\BxApp\RouterConfigurator::setRouteWhere($this->requestMethod, $this->bundle, $this->getCurrentUrl(), $where);
		}

		return $this;
	}

	/**
	 * Задаёт роуту имя. Имя нужно для обращения к конкретному роуту.
	 * Например, используется для задания мидлваеров.
	 *
	 * @param string $name
	 * @return object
	 */
	public function name($name = ''): object
	{
		if ($this->getCurrentUrl() !== false) {
			\Techart\BxApp\RouterConfigurator::removeRouteName($this->defaultRouteName);
			\Techart\BxApp\RouterConfigurator::setRouteName($this->requestMethod, $this->bundle, $this->getCurrentUrl(), $name);
		}

		// NOTE: Удалить если не нужны модели в конфиге роута
		/*if (!empty($this->routeBundle)) {
			if (file_exists(APP_ROUTER_DIR . '/' . $this->routeBundle . '/RoutesAPI.php')) {
				if (!isset(\Techart\BxApp\RouterConfigurator::$bundles[$this->routeBundle])) {
					\Techart\BxApp\RouterConfigurator::$bundles[$this->routeBundle] = include_once(APP_ROUTER_DIR . '/' . $this->routeBundle . '/RoutesAPI.php');
				}

				if (!empty(\Techart\BxApp\RouterConfigurator::$bundles[$this->routeBundle])) {
					$data = \Techart\BxApp\RouterConfigurator::$bundles[$this->routeBundle];

					if (isset($data[$name]['models']) && !empty($data[$name]['models'])) {
						$this->models = array_merge(\Glob::get('ROUTER_BUILD_CURRENT_BUNDLE_MODELS', []), $this->groupModels, $data[$name]['models']);

						\Techart\BxApp\RouterConfigurator::setRouteModels($this->requestMethod, $this->bundle, $this->getCurrentUrl(), $this->models);
					} else {
						if (isset($data[$this->defaultRouteName]['models']) && !empty($data[$this->defaultRouteName]['models'])) {
							$this->models = [];
							if (!empty(\Glob::get('ROUTER_BUILD_CURRENT_BUNDLE_MODELS', []))) {
								$this->models = \Glob::get('ROUTER_BUILD_CURRENT_BUNDLE_MODELS', []);
							}
							if (!empty($this->groupModels)) {
								$this->models = array_merge($this->models, $this->groupModels);
							}
							\Techart\BxApp\RouterConfigurator::setRouteModels($this->requestMethod, $this->bundle, $this->getCurrentUrl(), $this->models);
						}
					}
				}
			}
		}*/
		if (!empty($this->routeBundle)) {
			if (file_exists(APP_ROUTER_DIR . '/' . $this->routeBundle . '/RoutesAPI.php')) {
				if (!isset(\Techart\BxApp\RouterConfigurator::$bundles[$this->routeBundle])) {
					\Techart\BxApp\RouterConfigurator::$bundles[$this->routeBundle] = include_once(APP_ROUTER_DIR . '/' . $this->routeBundle . '/RoutesAPI.php');
				}

				if (!empty(\Techart\BxApp\RouterConfigurator::$bundles[$this->routeBundle])) {
					$data = \Techart\BxApp\RouterConfigurator::$bundles[$this->routeBundle];

					if (isset($data[$name]['data']) && !empty($data[$name]['data'])) {
						\Techart\BxApp\RouterConfigurator::setRouteData($this->requestMethod, $this->bundle, $this->getCurrentUrl(), $data[$name]['data']);
					}
				}
			}
		}

		return $this;
	}

	/**
	 * Задаёт роуту протекторы.
	 * Так же можно задать протекторы для группы (они будут ДОБАВЛЕНЫ ко всем роутам группы)
	 * Перечисленные протекторы выполняются до всех прочих мидлваеров и действий.
	 * Если хотя бы один протектор возвращает false, то урл роута отдаёт 404.
	 *
	 * @param array $protector
	 * @return object
	 */
	public function protector($protector = []): object
	{
		if ($this->getCurrentUrl() === false) {
			$this->groupProtector = $protector;
		} else {
			if (!empty($protector)) {
				$this->protector = array_merge(\Glob::get('ROUTER_BUILD_CURRENT_BUNDLE_PROTECTOR', []), $this->groupProtector, $protector);

				\Techart\BxApp\RouterConfigurator::setRouteProtector($this->requestMethod, $this->bundle, $this->getCurrentUrl(), $this->protector);
			}
		}

		return $this;
	}

	/**
	 * Задаёт роуту протекторы.
	 * Так же можно задать протекторы для группы (они будут ДОБАВЛЕНЫ ко всем роутам группы)
	 * Перечисленные протекторы выполняются до всех прочих мидлваеров и действий.
	 * Если хотя бы один протектор возвращает false, то урл роута отдаёт 404.
	 *
	 * @param array $params
	 * @return object
	 */
	public function params($params = []): object
	{
		if ($this->getCurrentUrl() === false) {
			$this->groupParams = $params;
		} else {
			if (!empty($params)) {
				$this->params = array_merge(\Glob::get('ROUTER_BUILD_CURRENT_BUNDLE_PARAMS', []), $this->groupParams, $params);

				\Techart\BxApp\RouterConfigurator::setRouteParams($this->requestMethod, $this->bundle, $this->getCurrentUrl(), $this->params);
			}
		}

		return $this;
	}

	// NOTE: Удалить если не нужны модели в конфиге роута
	/*
	/**
	 * Задаёт роуту модели.
	 * Так же можно задать модели для группы (они будут ДОБАВЛЕНЫ ко всем роутам группы)
	 * 
	 * @param array $models
	 * @return object
	 
	public function models($models = []): object
	{
		if ($this->getCurrentUrl() === false) {
			$this->groupModels = $models;
		} /*else {
			if (!empty($models)) {
				$this->models = array_merge(\Glob::get('ROUTER_BUILD_CURRENT_BUNDLE_MODELS', []), $this->groupModels, $models);

				\Techart\BxApp\RouterConfigurator::setRouteModels($this->requestMethod, $this->bundle, $this->getCurrentUrl(), $this->models);
			}
		}

		return $this;
	}*/
}
