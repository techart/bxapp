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

$prologFile = $_SERVER['DOCUMENT_ROOT'].'/local/php_interface/BxAppRouterProcessingProlog.php';
if( realpath($prologFile)) {
	// подключается файл, в котором можно до ядра битрикса определить константы (или ещё чего)
	require_once $prologFile;
}

if (defined('TBA_USE_BX_SECURITY_SESSION_VIRTUAL') && TBA_USE_BX_SECURITY_SESSION_VIRTUAL === true) {
	// if (!isset($_SERVER['HTTP_REQUEST_TYPE']) or $_SERVER['HTTP_REQUEST_TYPE'] !== 'secure') {
	// TODO: убрать проверку $_GET и вернуть проверку заголовка
	if (!isset($_GET['type']) or $_GET['type'] !== 'secure') {
		if (isset($_COOKIE['PHPSESSID']) && !empty($_COOKIE['PHPSESSID'])) {
			// если secure запрос не указан, но при этом передаётся PHPSESSID,
			// то это mixed запрос - запоминаем это в TBA_REQUEST_TYPE
			define('TBA_REQUEST_TYPE', 'mixed');
			// включаем виртуальную сессию (PHPSESSID игнорируется) (файл сессии не создаётся, заголовок куки не посылается)
			define('BX_SECURITY_SESSION_VIRTUAL', true);
		} else {
			// если это чисто public запрос, то запоминаем это в TBA_REQUEST_TYPE
			define('TBA_REQUEST_TYPE', 'public');
			// включаем виртуальную сессию (файл сессии не создаётся, заголовок куки не посылается)
			define('BX_SECURITY_SESSION_VIRTUAL', true);
		}
	} else {
		// если это чисто secure запрос, то запоминаем это в TBA_REQUEST_TYPE
		// используется обычная сессия
		define('TBA_REQUEST_TYPE', 'secure');
	}
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

$do404 = false;

if (Router::isActive()) { // роут включён
	Logger::info('RouterProccessing: роутер включён');

	if (Router::isCacheActive()) { // кэш роутера включён
		Logger::info('RouterProccessing: кэш роутера включён');

		$routeData = Router::getRouteFromCacheByUrl();

		if ($routeData !== false) { // роут взят из кэша
			Logger::info('RouterProccessing: роут взят из кэша');
			\App::setRoute($routeData);

			if (isset($routeData['protector']) && count($routeData['protector']) > 0) {
				Logger::info('RouterProccessing: работает протектор');
				App::core('Protector')->setRules($routeData['protector'])->do404(true)->go();
			}

			\Techart\BxApp\Middleware::before();
			\Techart\BxApp\Middleware::specialBefore();
			\Techart\BxApp\Middleware::defaultBefore();
			\Techart\BxApp\Middleware::defaultSpecialBefore();

			$routeData = Router::doAction($routeData);
			$routeData = \Techart\BxApp\Middleware::after($routeData);
			$routeData = \Techart\BxApp\Middleware::specialAfter($routeData);
			$routeData = \Techart\BxApp\Middleware::defaultAfter($routeData);
			$routeData = \Techart\BxApp\Middleware::defaultSpecialAfter($routeData);

			echo \App::core('Main')->jsonResponse($routeData);
		} else { // роута нет в кэше
			Logger::info('RouterProccessing: роута нет в кэше');

			if (Config::get('Router.APP_ROUTER_CACHE_REBUILD', false)) {
				Logger::info('RouterProccessing: ребилд роутера включён');

				$statusDefault = Router::buildDefault();
				$statusRouter = Router::build();

				if ($statusDefault || $statusRouter) { // роутер построен
					Logger::info('RouterProccessing: роутер построен');

					Router::toCache();
					$routeData = Router::getRouteFromDataByUrl();

					if ($routeData !== false) { // роут взят
						Logger::info('RouterProccessing: роут взят');
						\App::setRoute($routeData);

						if (isset($routeData['protector']) && count($routeData['protector']) > 0) {
							Logger::info('RouterProccessing: работает протектор');
							App::core('Protector')->setRules($routeData['protector'])->do404(true)->go();
						}

						\Techart\BxApp\Middleware::before();
						\Techart\BxApp\Middleware::specialBefore();
						\Techart\BxApp\Middleware::defaultBefore();
						\Techart\BxApp\Middleware::defaultSpecialBefore();

						$routeData = Router::doAction($routeData);
						$routeData = \Techart\BxApp\Middleware::after($routeData);
						$routeData = \Techart\BxApp\Middleware::specialAfter($routeData);
						$routeData = \Techart\BxApp\Middleware::defaultAfter($routeData);
						$routeData = \Techart\BxApp\Middleware::defaultSpecialAfter($routeData);

						echo \App::core('Main')->jsonResponse($routeData);
					} else { // роут не найден
						Logger::info('RouterProccessing: роут не найден');
						$do404 = true;
					}
				} else { // построить роутер не получилось
					Logger::error('RouterProccessing: построить роутер не получилось');
					$do404 = true;
				}
			} else {
				Logger::info('RouterProccessing: ребилд роутера выключен');
				$do404 = true;
			}
		}
	} else { // роутер работает без кэша
		Logger::info('RouterProccessing: роутер работает без кэша');

		$statusDefault = Router::buildDefault();
		$statusRouter = Router::build();

		if ($statusDefault || $statusRouter) { // роутер построен
			Logger::info('RouterProccessing: роутер построен');

			$routeData = Router::getRouteFromDataByUrl();

			if ($routeData !== false) { // роут взят
				Logger::info('RouterProccessing: роут взят');
				\App::setRoute($routeData);

				if (isset($routeData['protector']) && count($routeData['protector']) > 0) {
					Logger::info('RouterProccessing: работает протектор');
					App::core('Protector')->setRules($routeData['protector'])->do404(true)->go();
				}

				\Techart\BxApp\Middleware::before();
				\Techart\BxApp\Middleware::specialBefore();
				\Techart\BxApp\Middleware::defaultBefore();
				\Techart\BxApp\Middleware::defaultSpecialBefore();

				$routeData = Router::doAction($routeData);
				$routeData = \Techart\BxApp\Middleware::after($routeData);
				$routeData = \Techart\BxApp\Middleware::specialAfter($routeData);
				$routeData = \Techart\BxApp\Middleware::defaultAfter($routeData);
				$routeData = \Techart\BxApp\Middleware::defaultSpecialAfter($routeData);

				echo \App::core('Main')->jsonResponse($routeData);

			} else { // роут не найден
				Logger::info('RouterProccessing: роут не найден');
				$do404 = true;
			}
		} else { // построить роутер не получилось
			Logger::error('RouterProccessing: построить роутер не получилось');
			$do404 = true;
		}
	}
} else { // роут выключен
	Logger::info('RouterProccessing: роутер выключен');
	$do404 = true;
}


if ($do404) {
	Logger::info('роут 404');
	App::core('Main')->do404();
}

exit();
