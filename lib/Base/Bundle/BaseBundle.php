<?php
namespace Techart\BxApp\Base\Bundle;

/**
 * Все классы бандлов наследовать от этого класса
 *
 * В методе bundleProtector можно передать правила для всех урлов
 * Каждый урл можно дополнительно закрыть своими правилами указанными в ключе protector
 *
*	'{^/api/user/authorization/checkLogin/$}' => [
		'action' => 'checkLogin',
		'controller' => 'Auth',
		'protector' => ['!checkAuth'],
	],

	* Если хотя бы одна проверка возвращает false, то выполнение обрубается и отдаётся 404
 */

class BaseBundle extends \TAO\Bundle
{

	/**
	 * Должен возвращать массив с перечислением методов проверок
	 * Проверки запускаются для всех урлов бандла
	 * Данные проверки запускаются в начале, до методов перечисленных в ключе before
	 * Можно каждому урлу добавить своих проверок через ключ protector
	 * Если хотя бы одна првоерка возвращает false, то выполнение обрубается и отдаётся 404
	 *
	 * @return array
	 */
	public function bundleProtector():array
	{
		return [];
	}

	public function dispatch($route)
	{
		self::checkComposite($route);

		if (isset($route['element_of'])) {
			return self::dispatchElement($route);
		}
		if (isset($route['elements_of'])) {
			return self::dispatchElements($route);
		}
		if (isset($route['section_of'])) {
			return self::dispatchSection($route);
		}
		if (isset($route['sections_of'])) {
			return self::dispatchSections($route);
		}

		$controller = $this->getController($route['controller']);
		$controller->route = $route;
		$action = $route['action'];

		if (!method_exists($controller, $action)) {
			die("Unknown action {$this->name}:{$route['controller']}:{$action}");
		}

		$args = array();
		foreach ($route as $k => $v) {
			if (is_int($k)) {
				$args[] = $v;
			}
		}
		$args[] = $route;
		$args[0]['bundleProtector'] = $this->bundleProtector(); // методы протектора

		if (isset($route['protector']) && !empty($route['protector'])) {
			// протектор текущего урла = общий протектор бандла + протектор урла
			$args[0]['bundleProtector'] =  array_merge($args[0]['bundleProtector'], $route['protector']);
		}

		// перекидываем обработчик на кастомный экшн baseBundleAction
		return call_user_func_array(array($controller, 'baseBundleAction'), $args);
	}
}
