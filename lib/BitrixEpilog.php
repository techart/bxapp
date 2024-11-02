<?php
namespace Techart\BxApp;

/**
 * Класс для обработки OnEpilog эвента битрикса.
 * Регистрируется в App.php
 */

class BitrixEpilog
{
	public static function setup()
	{
		// AddEventHandler("main", "OnEpilog", ['Techart\BxApp\BitrixEpilog', 'OnEpilog'], 999999);
	}

	public static function OnEpilog()
	{
		// если это не json запрос
		// если это не апи запрос
		// если это не админка
		if (
			strpos($_SERVER['HTTP_ACCEPT'], 'application/json') === false &&
			strpos($_SERVER['REQUEST_URI'], Config::get('Router.APP_ROUTER_PREFIX', 'siteapi')) === false &&
			strpos($_SERVER['REQUEST_URI'], '/bitrix/admin') === false
		) {
			$currentEntryPoints = App::core('Assets')->getCurrentEntryPoints();

			// если точка layout ещё не подключена
			if (is_array($currentEntryPoints) && !in_array('layout', $currentEntryPoints)) {
				App::core('Assets')->setEntryPoints('layout');
			}
		}
	}
}
