<?php
namespace Middleware\After;

class StaticApi
{
	/**
	 * Данный метод вызывается при использовании middleware
	 *
	 * @param mixed $data
	 * @return void
	 */
	function handle($data)
	{
		// Если запрос помечен как staticapi
		if (defined('BXAPP_IS_STATIC') && BXAPP_IS_STATIC === true && defined('BXAPP_ROUTER_CURRENT_REQUEST_URL') && !empty(BXAPP_ROUTER_CURRENT_REQUEST_URL)) {
			$putCache = true;

			// Если среди протекторов роута есть checkSecure или checkAuth, то записывать статик кэш не надо
			if (
				isset(\App::getRoute()['protector']) &&
				count(array_intersect(['checkSecure', 'checkAuth'], \App::getRoute()['protector'])) > 0
			) {
				$putCache = false;
			}
			// Если среди параметров роута есть noStatic, то записывать статик кэш не надо
			if (
				isset(\App::getRoute()['params']) &&
				count(array_intersect(['noStatic'], \App::getRoute()['params'])) > 0
			) {
				$putCache = false;
			}

			if ($putCache) {
				$cachePath = APP_CACHE_STATIC_DIR.BXAPP_ROUTER_CURRENT_REQUEST_URL;

				if (!is_dir($cachePath)) {
					mkdir($cachePath, 0777, true);
				}
				if (!file_exists($cachePath .'data.json')) {
					$dataForCache = $data;
					$dataForCache['cache'] = true;

					file_put_contents($cachePath .'data.json', json_encode($dataForCache, JSON_UNESCAPED_UNICODE));

					\Logger::info('Middleware\After\StaticApi: данные записаны в '.$cachePath .'data.json');
				}
			}
		}

		return $data;
	}
}
