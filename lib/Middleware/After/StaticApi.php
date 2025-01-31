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
		// В $data приходят данные возвращаемые акшеном контроллера роута
		// Эти данные можно модифицировать, например:
		// $data['ModifyData'] = 'ModifyData';
		if (defined('BXAPP_IS_STATIC') && BXAPP_IS_STATIC === true && defined('BXAPP_ROUTER_CURRENT_REQUEST_URL') && !empty(BXAPP_ROUTER_CURRENT_REQUEST_URL)) {
			$cachePath = APP_CACHE_STATIC_DIR.BXAPP_ROUTER_CURRENT_REQUEST_URL;

			if (!is_dir($cachePath)) {
				mkdir($cachePath, 0777, true);
			}
			if (!file_exists($cachePath .'data.json')) {
				$dataForCache = $data;
				$dataForCache['cache'] = true;

				file_put_contents($cachePath .'data.json', json_encode($dataForCache, JSON_UNESCAPED_UNICODE));
			}
		}

		return $data;
	}
}
