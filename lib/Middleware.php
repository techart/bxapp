<?php
namespace Techart\BxApp;

/**
 * Класс реализует посредников для роутера
 */

use \Bitrix\Main\Application;


class Middleware
{
	/**
	 * На основе данных ключа "before" в конфиге Middleware.php выполняет указанные
	 * классы посредников ДО выпонения экшена роута
	 *
	 * @return void
	 */
	public static function before(): void
	{
		$curBeforeArray = Config::get('Middleware.before', []);

		if (isset($curBeforeArray[App::getRoute('name')])) {
			if (count($curBeforeArray[App::getRoute('name')]) > 0) {
				foreach ($curBeforeArray[App::getRoute('name')] as $className) {
					$classFile = APP_MIDDLEWARE_BEFORE_DIR.'/'.$className.'.php';

					if (file_exists($classFile)) {
						require_once($classFile);

						$className = "Site\\Middleware\\Before\\".$className;

						if (class_exists($className)) {
							$curClass = new $className;

							if (method_exists($curClass, 'handle')) {
								call_user_func_array([$curClass, 'handle'], []);
							} else {
								Logger::info('В классе '.$className.' не найден метод: handle');
							}
						} else {
							Logger::info('Не найден класс: '.$className);
						}
					} else {
						Logger::info('Не найден файл: '.$classFile);
					}
				}
			}
		} else {
			Logger::info('Для роута нет назначенных Middleware before');
		}
	}

	/**
	 * На основе данных ключа "specialBefore" в конфиге Middleware.php выполняет указанные
	 * классы посредников ДО выпонения экшена роута
	 *
	 * @return void
	 */
	public static function specialBefore(): void
	{
		$curMiddleware = Config::get('Middleware.specialBefore', []);
		$uri = Application::getInstance()->getContext()->getRequest()->getRequestUri();
		$classes = [];

		foreach ($curMiddleware as $pattern => $className) {
			if (preg_match($pattern, $uri)) {
				$classes[$uri] = $className;
			}
		}

		if (isset($classes[$uri])) {
			if (count($classes[$uri]) > 0) {
				foreach ($classes[$uri] as $className) {
					$classFile = APP_MIDDLEWARE_BEFORE_DIR.'/'.$className.'.php';

					if (file_exists($classFile)) {
						require_once($classFile);

						$className = "Site\\Middleware\\Before\\".$className;

						if (class_exists($className)) {
							$curClass = new $className;

							if (method_exists($curClass, 'handle')) {
								call_user_func_array([$curClass, 'handle'], []);
							} else {
								Logger::info('В классе '.$className.' не найден метод: handle');
							}
						} else {
							Logger::info('Не найден класс: '.$className);
						}
					} else {
						Logger::info('Не найден файл: '.$classFile);
					}
				}
			}
		} else {
			Logger::info('Для роута нет назначенных Middleware specialBefore');
		}
	}

	/**
	 * На основе данных ключа "after" в конфиге Middleware.php выполняет указанные
	 * классы посредников ПОСЛЕ выпонения экшена роута
	 *
	 * @param array|string $actionData
	 * @return mixed
	 */
	public static function after(array|string $actionData = []): mixed
	{
		$curMiddleware = Config::get('Middleware.after', []);

		if (isset($curMiddleware[App::getRoute('name')])) {
			if (count($curMiddleware[App::getRoute('name')]) > 0) {
				foreach ($curMiddleware[App::getRoute('name')] as $className) {
					$classFile = APP_MIDDLEWARE_AFTER_DIR.'/'.$className.'.php';

					if (file_exists($classFile)) {
						require_once($classFile);

						$className = "Site\\Middleware\\After\\".$className;

						if (class_exists($className)) {
							$curClass = new $className;

							if (method_exists($curClass, 'handle')) {
								$actionData = call_user_func_array([$curClass, 'handle'], [$actionData]);
							} else {
								Logger::info('В классе '.$className.' не найден метод: handle');
							}
						} else {
							Logger::info('Не найден класс: '.$className);
						}
					} else {
						Logger::info('Не найден файл: '.$classFile);
					}
				}
			}
		} else {
			Logger::info('Для роута нет назначенных Middleware after');
		}

		return $actionData;
	}

	/**
	 * На основе данных ключа "specialAfter" в конфиге Middleware.php выполняет указанные
	 * классы посредников ПОСЛЕ выпонения экшена роута
	 *
	 * @param array|string $actionData
	 * @return mixed
	 */
	public static function specialAfter(array|string $actionData = []): mixed
	{
		$curMiddleware = Config::get('Middleware.specialAfter', []);
		$uri = Application::getInstance()->getContext()->getRequest()->getRequestUri();
		$classes = [];

		foreach ($curMiddleware as $pattern => $className) {
			if (preg_match($pattern, $uri)) {
				$classes[$uri] = $className;
			}
		}

		if (isset($classes[$uri])) {
			if (count($classes[$uri]) > 0) {
				foreach ($classes[$uri] as $className) {
					$classFile = APP_MIDDLEWARE_AFTER_DIR.'/'.$className.'.php';

					if (file_exists($classFile)) {
						require_once($classFile);

						$className = "Site\\Middleware\\After\\".$className;

						if (class_exists($className)) {
							$curClass = new $className;

							if (method_exists($curClass, 'handle')) {
								$actionData = call_user_func_array([$curClass, 'handle'], [$actionData]);
							} else {
								Logger::info('В классе '.$className.' не найден метод: handle');
							}
						} else {
							Logger::info('Не найден класс: '.$className);
						}
					} else {
						Logger::info('Не найден файл: '.$classFile);
					}
				}
			}
		} else {
			Logger::info('Для роута нет назначенных Middleware specialAfter');
		}

		return $actionData;
	}
}
