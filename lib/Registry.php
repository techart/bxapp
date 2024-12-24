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
	private static array $bxAppEntitiesDirs = [];


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

		$request = \Bitrix\Main\Context::getCurrent()->getRequest();

		if($request->isAdminSection()) {
			$settings['currentSite'] = array_key_first($settings['sites']);
		}

		return $settings;
	}

	/**
	 * Собирает пути к BxApp директориям
	 *
	 * @return void
	 */
	public static function buildBxAppEntitiesDirs(string $siteId = ''): array
	{
		$bxappSiteId = !empty($siteId) ? $siteId : BXAPP_SITE_ID;
		
		if (empty(self::$bxAppEntitiesDirs[$bxappSiteId])) {
			$bxAppEntitiesDirs = [
				'bxAppDir' => 'BxApp',
				'cliDir' => 'Cli',
				'configsDir' => 'Configs',
				'localizationDir' => 'Localization',
				'logsDir' => 'Logs',
				'menuDir' => 'Menu',
				'middlewareDir' => 'Middleware',
				'modelsDir' => 'Models',
				'modulesDir' => 'Modules',
				'routerDir' => 'Router',
				'servicesDir' => 'Services',
				'traitsDir' => 'Traits',
				'viewsDir' => 'Views',
			];
	
			if (isset(BXAPP_REGISTRY_SITES[$bxappSiteId]['bxappDir']) && !empty(BXAPP_REGISTRY_SITES[$bxappSiteId]['bxappDir'])) {
				$bxAppEntitiesDirs['bxAppDir'] = BXAPP_REGISTRY_SITES[$bxappSiteId]['bxappDir'];
			}
			if (isset(BXAPP_REGISTRY_SITES[$bxappSiteId]['bxappEntities']) && in_array('Cli', BXAPP_REGISTRY_SITES[$bxappSiteId]['bxappEntities'])) {
				$bxAppEntitiesDirs['cliDir'] .= '_'.$bxappSiteId;
			}
			if (isset(BXAPP_REGISTRY_SITES[$bxappSiteId]['bxappEntities']) && in_array('Configs', BXAPP_REGISTRY_SITES[$bxappSiteId]['bxappEntities'])) {
				$bxAppEntitiesDirs['configsDir'] .= '_'.$bxappSiteId;
			}
			if (isset(BXAPP_REGISTRY_SITES[$bxappSiteId]['bxappEntities']) && in_array('Localization', BXAPP_REGISTRY_SITES[$bxappSiteId]['bxappEntities'])) {
				$bxAppEntitiesDirs['localizationDir'] .= '_'.$bxappSiteId;
			}
			if (isset(BXAPP_REGISTRY_SITES[$bxappSiteId]['bxappEntities']) && in_array('Logs', BXAPP_REGISTRY_SITES[$bxappSiteId]['bxappEntities'])) {
				$bxAppEntitiesDirs['logsDir'] .= '_'.$bxappSiteId;
			}
			if (isset(BXAPP_REGISTRY_SITES[$bxappSiteId]['bxappEntities']) && in_array('Menu', BXAPP_REGISTRY_SITES[$bxappSiteId]['bxappEntities'])) {
				$bxAppEntitiesDirs['menuDir'] .= '_'.$bxappSiteId;
			}
			if (isset(BXAPP_REGISTRY_SITES[$bxappSiteId]['bxappEntities']) && in_array('Middleware', BXAPP_REGISTRY_SITES[$bxappSiteId]['bxappEntities'])) {
				$bxAppEntitiesDirs['middlewareDir'] .= '_'.$bxappSiteId;
			}
			if (isset(BXAPP_REGISTRY_SITES[$bxappSiteId]['bxappEntities']) && in_array('Models', BXAPP_REGISTRY_SITES[$bxappSiteId]['bxappEntities'])) {
				$bxAppEntitiesDirs['modelsDir'] .= '_'.$bxappSiteId;
			}
			if (isset(BXAPP_REGISTRY_SITES[$bxappSiteId]['bxappEntities']) && in_array('Modules', BXAPP_REGISTRY_SITES[$bxappSiteId]['bxappEntities'])) {
				$bxAppEntitiesDirs['modulesDir'] .= '_'.$bxappSiteId;
			}
			if (isset(BXAPP_REGISTRY_SITES[$bxappSiteId]['bxappEntities']) && in_array('Router', BXAPP_REGISTRY_SITES[$bxappSiteId]['bxappEntities'])) {
				$bxAppEntitiesDirs['routerDir'] .= '_'.$bxappSiteId;
			}
			if (isset(BXAPP_REGISTRY_SITES[$bxappSiteId]['bxappEntities']) && in_array('Services', BXAPP_REGISTRY_SITES[$bxappSiteId]['bxappEntities'])) {
				$bxAppEntitiesDirs['servicesDir'] .= '_'.$bxappSiteId;
			}
			if (isset(BXAPP_REGISTRY_SITES[$bxappSiteId]['bxappEntities']) && in_array('Traits', BXAPP_REGISTRY_SITES[$bxappSiteId]['bxappEntities'])) {
				$bxAppEntitiesDirs['traitsDir'] .= '_'.$bxappSiteId;
			}
			if (isset(BXAPP_REGISTRY_SITES[$bxappSiteId]['bxappEntities']) && in_array('Views', BXAPP_REGISTRY_SITES[$bxappSiteId]['bxappEntities'])) {
				$bxAppEntitiesDirs['viewsDir'] .= '_'.$bxappSiteId;
			}

			self::$bxAppEntitiesDirs[$bxappSiteId] = $bxAppEntitiesDirs;
		}

		return self::$bxAppEntitiesDirs[$bxappSiteId];
	}
}
