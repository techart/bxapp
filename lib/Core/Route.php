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
	private $group = '';
	private $groupProtector = [];
	private $protector = [];


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
	 * @return object
	 */
	public function setRoute(string $requestMethod = '', string $url = '', string $classMethod = ''): object
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

			\Techart\BxApp\RouterConfigurator::setRequestMethod($requestMethod);
			\Techart\BxApp\RouterConfigurator::setBundle($this->requestMethod, $this->bundle);
			\Techart\BxApp\RouterConfigurator::setRouteUrl($this->requestMethod, $this->bundle, $this->getCurrentUrl());
			\Techart\BxApp\RouterConfigurator::setRouteRequestMethod($this->requestMethod, $this->bundle, $this->getCurrentUrl());
			\Techart\BxApp\RouterConfigurator::setRouteBundle($this->requestMethod, $this->bundle, $this->getCurrentUrl(), $this->routeBundle);
			\Techart\BxApp\RouterConfigurator::setRouteGroup($this->requestMethod, $this->bundle, $this->getCurrentUrl(), $this->group);
			\Techart\BxApp\RouterConfigurator::setRouteMethod($this->requestMethod, $this->bundle, $this->getCurrentUrl(), $classMethod[0], $classMethod[1]);

			if (!empty($this->routeProtector)) {
				\Techart\BxApp\RouterConfigurator::setRouteProtector($this->requestMethod, $this->bundle, $this->getCurrentUrl(), $this->routeProtector);
			}

			if (!empty($this->groupProtector)) {
				\Techart\BxApp\RouterConfigurator::setRouteProtector($this->requestMethod, $this->bundle, $this->getCurrentUrl(), array_merge($this->routeProtector, $this->groupProtector));
			}
		}
		return $this;
	}

	/**
	 * Создаёт роут с реквест методом get.
	 *
	 * $url - урл роута, можно использовать подстановки
	 * $classMethod - указывается через точку имя файла экшена и метода в нём: "Actions.method"
	 *
	 * @param string $url
	 * @param string $classMethod
	 * @return object
	 */
	public function get(string $url = '', string $classMethod = ''): object
	{
		$this->setRoute('get', $url, $classMethod);

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
			\Techart\BxApp\RouterConfigurator::setRouteName($this->requestMethod, $this->bundle, $this->getCurrentUrl(), $name);
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
}
