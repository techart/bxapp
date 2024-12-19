<?php
namespace Techart\BxApp;

/**
 * Класс для регистрации серверов/сайтов/языков
 * На основе этого можно менять окружение и поведение BxApp
 *
 * Настройки забирает из файла php_interface/BxAppRegistry.php если он есть
 * В файле одноимёный класс, в котором вызывается метод apply() для применения настроек
 * Назначенные в результате работы $currentServer, $currentSite и $currentLanguage влияют на BxApp константы
 * BXAPP_SERVER_ID, BXAPP_SITE_ID, BXAPP_LANGUAGE_ID, BXAPP_SITE_GROUP_ID
 */

class Registry
{
	/**
	 * Возвращает массив настроек на основе php_interface/BxAppRegistry.php
	 *
	 * @return array
	 */
	public static function setup(): array
	{
		$settings = [
			'servers' => [],
			'sites' => [],
			'groups' => [],
			'currentServer' => null,
			'currentSite' => null,
			'currentLanguage' => null,
		];
		$registryFile = realpath(APP_PHP_INTERFACE_DIR.'/BxAppRegistry.php');

		if ($registryFile !== false) {
			if (file_exists($registryFile)) {
				require_once($registryFile);

				if (class_exists('BxAppRegistry')) {
					$registryClass = new \BxAppRegistry();

					if (method_exists($registryClass, 'apply')) {
						$registryClass->apply();

						$settings['servers'] = $registryClass->getServers();
						$settings['sites'] = $registryClass->getSites();
						$settings['groups'] = $registryClass->getGroups();
						$settings['currentServer'] = $registryClass->getCurrentServer();
						$settings['currentSite'] = $registryClass->getCurrentSite();
						$settings['currentLanguage'] = $registryClass->getCurrentLanguage();
					}
				}
			}
			$registryFile = '';
		}

		return $settings;
	}
}
