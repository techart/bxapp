<?php
namespace Techart\BxApp\Core;

/**
 * Класс для создания роутов.
 *
 * Структура роутов хранится в папке Routes. В ней создаётся подпапка - она же имя бандла.
 * В данной папке создаётся файл Routes.php, где указываются все роуты бандла.
 * А так же папка Controllers, в которой хранятся файлы контроллеров бандла.
 *
 * У роутера есть настройки в файле .env и в конфиге Router.php - в них есть комментарии что и как.
 *
 * Имя роута составляется так: APP_ROUTER_PREFIX + ИМЯ_ПАПКИ_БАНДЛА + ИМЯ_ГРУППЫ + УКАЗАННЫЙ_ПУТЬ
 *
 * ИМЯ_ГРУППЫ задаётся через метод group(), но его может не быть или имя группы может быть пустой.
 * Пустая группа может быть полезна для задания протекторов и мидлваеров нескольким роутам за раз.
 *
 * В файле Routes.php роуты задаются таким образом, например для бандла Catalog:
 *
 * App::route()->get('/get-all/{name}/{id}/', 'Actions.mainTestt')->where(['name' => '[a-zA-Z]+', 'id' => '[0-9]+']);
 *
 * При заходе на урл вида /siteapi/catalog/get-all/[a-zA-Z]+/[0-9]+/ в метод mainTestt() контроллера Actions.php и
 * бандла Catalog будут переданы две переменные с данными из соответствующих регулярок.
 *
 * Регулярки не обязательно каждый разписать руками, есть готовые подстановки, например:
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
 * Отдельным роутам и группам можно задавать протектор и мидлваеры.
 *
 * App::route()->get('/get-all/', 'Actions.mainTestt')->protector(['checkDomain', 'checkSession', 'checkAuth']);
 * App::route()->get('/get-all/', 'Actions.mainTestt')->after(['checkDomain'])->before(['checkDomain']);
 *
 * Заданное для группы плюсуется ко всем её роутам.Ниже группа без имени - для задания общего протектора
 *
 * App::route()->protector(['checkDomain'])->group('', function($r) {
 * 		$r->get('/delete/', 'Actions.Test')->protector(['checkSession']); // протектор будет checkDomain и checkSession
 * 		$r->get('/update/', 'Actions.Test'); // протектор будет checkDomain
 * });
 *
 * Роуты задаются по методу запроса: get, post, delete, options для чего есть одноимённые методы.
 * Вместо App::route()->get() можно написать App::route()->post() и т.д.
 * Таким образом может быть несколько одинаковых урлов.
 */

class Route
{
	private $url = '';
	private $requestMethod = '';
	private $bundle = '';
	private $routeBundle = '';
	private $group = '';
	private $groupProtector = [];
	private $protector = [];


	public function group($group = '', callable $method)
	{
		$this->group = $group;

		if (is_callable($method)) {
			call_user_func($method, $this);
		}
	}

	public function getCurrentUrl()
	{
		if (empty($this->group) && empty($this->url)) {
			return false;
		} else {
			return preg_replace('/(\/+)/','/', '/'.$this->group.'/'.$this->url.'/');
		}
	}

	public function setRoute(string $requestMethod = '', string $url = '', string $classMethod = '')
	{
		$classMethod = explode('.', $classMethod);

		if (empty($classMethod) || count($classMethod) < 2) {
			\Logger::warning('Route: незаданн контроллер или его метод для url = '.$url);
		} else {
			$this->url = $url;
			$this->requestMethod = mb_strtolower($requestMethod);
			$this->bundle = mb_strtolower(\Glob::get('ROUTER_BUILD_CURRENT_BUNDLE', ''));
			$this->routeBundle = \Glob::get('ROUTER_BUILD_CURRENT_BUNDLE', '');
			\Techart\BxApp\RouterConfigurator::setRequestMethod($requestMethod); // 1
			\Techart\BxApp\RouterConfigurator::setBundle($this->requestMethod, $this->bundle); // 2
			\Techart\BxApp\RouterConfigurator::setRouteUrl($this->requestMethod, $this->bundle, $this->getCurrentUrl()); // 3
			\Techart\BxApp\RouterConfigurator::setRouteRequestMethod($this->requestMethod, $this->bundle, $this->getCurrentUrl()); // 3
			\Techart\BxApp\RouterConfigurator::setRouteBundle($this->requestMethod, $this->bundle, $this->getCurrentUrl(), $this->routeBundle); // 3
			\Techart\BxApp\RouterConfigurator::setRouteGroup($this->requestMethod, $this->bundle, $this->getCurrentUrl(), $this->group);
			\Techart\BxApp\RouterConfigurator::setRouteMethod($this->requestMethod, $this->bundle, $this->getCurrentUrl(), $classMethod[0], $classMethod[1]);

			if (!empty($this->protector)) {
				\Techart\BxApp\RouterConfigurator::setRouteProtector($this->requestMethod, $this->bundle, $this->getCurrentUrl(), $this->groupProtector);
			}
		}
		return $this;
	}

	public function get(string $url = '', string $classMethod = '')
	{
		$this->setRoute('get', $url, $classMethod);

		return $this;
	}

	public function post(string $url = '', string $classMethod = '')
	{
		$this->setRoute('post', $url, $classMethod);

		return $this;
	}

	public function delete(string $url = '', string $classMethod = '')
	{
		$this->setRoute('delete', $url, $classMethod);

		return $this;
	}

	public function options(string $url = '', string $classMethod = '')
	{
		$this->setRoute('options', $url, $classMethod);

		return $this;
	}

	public function where(array $where = [])
	{
		if ($this->getCurrentUrl() !== false) {
			\Techart\BxApp\RouterConfigurator::setRouteWhere($this->requestMethod, $this->bundle, $this->getCurrentUrl(), $where);
		}

		return $this;
	}

	public function name($name = '')
	{
		if ($this->getCurrentUrl() !== false) {
			\Techart\BxApp\RouterConfigurator::setRouteName($this->requestMethod, $this->bundle, $this->getCurrentUrl(), $name);
		}

		return $this;
	}

	public function protector($protector = [])
	{
		if ($this->getCurrentUrl() === false) {
			$this->groupProtector = $protector;
		} else {
			if (!empty($protector)) {
				$this->protector = array_merge($this->groupProtector, $protector);

				\Techart\BxApp\RouterConfigurator::setRouteProtector($this->requestMethod, $this->bundle, $this->getCurrentUrl(), $this->protector);
			}
		}

		return $this;
	}
}
