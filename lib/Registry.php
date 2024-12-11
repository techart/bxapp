<?php
namespace Techart\BxApp;

/**
 * Класс для регистрации серверов/сайтов/языков
 * На основе этого можно менять окружение и поведение BxApp
 *
 * Настройки забирает из файла php_interface/BxAppRegistry.php если он есть
 * В файле одноимёный класс, в котором вызывается метод apply() для применения настроек
 * Назначенные в результате работы $currentServer, $currentSite и $currentLanguage влияют на BxApp константы
 * BXAPP_SERVER_ID, BXAPP_SITE_ID, BXAPP_LANGUAGE_ID
 */

class Registry
{

	public static function setup(): void
	{
		$registryFile = realpath(APP_PHP_INTERFACE_DIR.'/BxAppRegistry.php');

	}
}
