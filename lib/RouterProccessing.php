<?php
namespace Techart\BxApp;

/**
 * На этот файл .htaccess пересылает запросы, для урлов роута (начинающихся с APP_ROUTER_PREFIX в Config/Router.php)
 *
 * Так же есть настройки в файле .env:
 *
 * APP_ROUTER_ACTIVE=true - отключается роутер
 * APP_ROUTER_CACHE_ACTIVE=false - отключает кэш роутера
 * APP_ROUTER_CHECK_HTTPS=false - отключает проверку https запросов
 *
 * На основе настроек данный файл разруливает что куда
 *
 */
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

$do404 = false;

if (Router::isActive()) { // роут включён
	Logger::info('роутер включён');

	if (Router::isCacheActive()) { // кэш роутера включён
		Logger::info('кэш роутера включён');

		$routeData = Router::getRouteFromCacheByUrl();

		if ($routeData !== false) { // роут взят из кэша
			Logger::info('роут взят из кэша');

			if (isset($routeData['protector']) && count($routeData['protector']) > 0) {
				Logger::info('работает протектор');
				App::core('Protector')->setRules($routeData['protector'])->do404(true)->go();
			}

			\App::setRoute($routeData);
			\Techart\BxApp\Middleware::before();
			\Techart\BxApp\Middleware::specialBefore();

			$routeData = Router::doAction($routeData);
			$routeData = \Techart\BxApp\Middleware::after($routeData);
			$routeData = \Techart\BxApp\Middleware::specialAfter($routeData);

			echo \App::core('Main')->jsonResponse($routeData);
		} else { // роута нет в кэше
			Logger::info('роута нет в кэше');

			if (Config::get('Router.APP_ROUTER_CACHE_REBUILD', false)) {
				Logger::info('ребилд роутера включён');


				if (Router::build() === true) { // роутер построен
					Logger::info('роутер построен');

					Router::toCache();
					$routeData = Router::getRouteFromDataByUrl();

					if ($routeData !== false) { // роут взят
						Logger::info('роут взят');

						if (isset($routeData['protector']) && count($routeData['protector']) > 0) {
							Logger::info('работает протектор');
							App::core('Protector')->setRules($routeData['protector'])->do404(true)->go();
						}
						\App::setRoute($routeData);
						\Techart\BxApp\Middleware::before();
						\Techart\BxApp\Middleware::specialBefore();

						$routeData = Router::doAction($routeData);
						$routeData = \Techart\BxApp\Middleware::after($routeData);
						$routeData = \Techart\BxApp\Middleware::specialAfter($routeData);

						echo \App::core('Main')->jsonResponse($routeData);
					} else { // роут не найден
						Logger::info('роут не найден');
						$do404 = true;
					}
				} else { // построить роутер не получилось
					Logger::error('построить роутер не получилось');
					$do404 = true;
				}
			} else {
				Logger::info('ребилд роутера выключен');
				$do404 = true;
			}
		}
	} else { // роутер работает без кэша
		Logger::info('роутер работает без кэша');

		if (Router::build() === true) { // роутер построен
			Logger::info('роутер построен');

			$routeData = Router::getRouteFromDataByUrl();

			if ($routeData !== false) { // роут взят
				Logger::info('роут взят');

				if (isset($routeData['protector']) && count($routeData['protector']) > 0) {
					Logger::info('работает протектор');
					App::core('Protector')->setRules($routeData['protector'])->do404(true)->go();
				}

				\App::setRoute($routeData);
				\Techart\BxApp\Middleware::before();
				\Techart\BxApp\Middleware::specialBefore();

				$routeData = Router::doAction($routeData);
				$routeData = \Techart\BxApp\Middleware::after($routeData);
				$routeData = \Techart\BxApp\Middleware::specialAfter($routeData);

				echo \App::core('Main')->jsonResponse($routeData);

			} else { // роут не найден
				Logger::info('роут не найден');
				$do404 = true;
			}
		} else { // построить роутер не получилось
			Logger::error('построить роутер не получилось');
			$do404 = true;
		}
	}
} else { // роут выключен
	Logger::info('роутер выключен');
	$do404 = true;
}


if ($do404) {
	Logger::info('роут 404');
	App::core('Main')->do404();
}

exit();
