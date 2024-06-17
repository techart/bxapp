<?php
namespace Techart\BxApp;


use \Bitrix\Main\Application;


class Middleware
{
	public static function before()
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

	public static function specialBefore()
	{
		$curMiddleware = Config::get('Middleware.specialBefore', []);
		$uri = Application::getInstance()->getContext()->getRequest()->getRequestUri();

		if (isset($curMiddleware[$uri])) {
			if (count($curMiddleware[$uri]) > 0) {
				foreach ($curMiddleware[$uri] as $className) {
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

	public static function after(array $actionData = [])
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
								return call_user_func_array([$curClass, 'handle'], [$actionData]);
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

	public static function specialAfter(array $actionData = [])
	{
		$curMiddleware = Config::get('Middleware.specialAfter', []);
		$uri = Application::getInstance()->getContext()->getRequest()->getRequestUri();

		if (isset($curMiddleware[$uri])) {
			if (count($curMiddleware[$uri]) > 0) {
				foreach ($curMiddleware[$uri] as $className) {
					$classFile = APP_MIDDLEWARE_AFTER_DIR.'/'.$className.'.php';

					if (file_exists($classFile)) {
						require_once($classFile);

						$className = "Site\\Middleware\\After\\".$className;

						if (class_exists($className)) {
							$curClass = new $className;

							if (method_exists($curClass, 'handle')) {
								return call_user_func_array([$curClass, 'handle'], [$actionData]);
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
}
