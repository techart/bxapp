<?php
namespace Techart\BxApp;

/**
 * Класс для регистрации серверов/сайтов/языков
 * На основе этого можно менять окружение и поведение BxApp
 *
 * Настройки забирает из файла php_interface/BxAppRegistry.php если он есть
 * В файле одноимёный класс, в котором вызывается метод apply() для применения настроек
 * Назначенные в результате работы $currentServer, $currentSite и $currentLanguage влияют на BxApp константы
 * TBA_SERVER_ID, TBA_SITE_ID, TBA_LANGUAGE_ID, TBA_SITE_GROUP_ID
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
		$registryFile = realpath(TBA_APP_PHP_INTERFACE_DIR.'/BxAppRegistry.php');

		if ($registryFile !== false) {
			if (file_exists($registryFile)) {
				require_once($registryFile);

				if (class_exists('BxAppRegistry')) {
					$registryClass = new \BxAppRegistry();

					if (method_exists($registryClass, 'apply')) {
						$registryClass->apply();

						if (!defined('TBA_IS_SITE_PAGE')) {
							define('TBA_IS_SITE_PAGE', true);
						}
						if (!defined('TBA_IS_STATIC')) {
							define('TBA_IS_STATIC', false);
						}

						Logger::info('Registry::setup (TBA_IS_STATIC): '.json_encode(TBA_IS_STATIC));

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
		$bxappSiteId = !empty($siteId) ? $siteId : TBA_SITE_ID;

		if (empty(self::$bxAppEntitiesDirs[$bxappSiteId])) {
			$bxAppEntitiesDirs = [
				'bxAppDir' => 'BxApp',
				'cliDir' => 'Cli',
				'configsDir' => 'Configs',
				'entitiesDir' => 'Entities',
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

			if (isset(TBA_REGISTRY_SITES[$bxappSiteId]['bxappDir']) && !empty(TBA_REGISTRY_SITES[$bxappSiteId]['bxappDir'])) {
				$bxAppEntitiesDirs['bxAppDir'] = TBA_REGISTRY_SITES[$bxappSiteId]['bxappDir'];
			}
			if (!isset(TBA_REGISTRY_SITES[$bxappSiteId]['bxappDir'])) {
				if (isset(TBA_REGISTRY_SITES[$bxappSiteId]['bxappEntities']) && in_array('Cli', TBA_REGISTRY_SITES[$bxappSiteId]['bxappEntities'])) {
					$bxAppEntitiesDirs['cliDir'] .= '_'.$bxappSiteId;
				}
				if (isset(TBA_REGISTRY_SITES[$bxappSiteId]['bxappEntities']) && in_array('Configs', TBA_REGISTRY_SITES[$bxappSiteId]['bxappEntities'])) {
					$bxAppEntitiesDirs['configsDir'] .= '_'.$bxappSiteId;
				}
				if (isset(TBA_REGISTRY_SITES[$bxappSiteId]['bxappEntities']) && in_array('Entities', TBA_REGISTRY_SITES[$bxappSiteId]['bxappEntities'])) {
					$bxAppEntitiesDirs['entitiesDir'] .= '_'.$bxappSiteId;
				}
				if (isset(TBA_REGISTRY_SITES[$bxappSiteId]['bxappEntities']) && in_array('Localization', TBA_REGISTRY_SITES[$bxappSiteId]['bxappEntities'])) {
					$bxAppEntitiesDirs['localizationDir'] .= '_'.$bxappSiteId;
				}
				if (isset(TBA_REGISTRY_SITES[$bxappSiteId]['bxappEntities']) && in_array('Logs', TBA_REGISTRY_SITES[$bxappSiteId]['bxappEntities'])) {
					$bxAppEntitiesDirs['logsDir'] .= '_'.$bxappSiteId;
				}
				if (isset(TBA_REGISTRY_SITES[$bxappSiteId]['bxappEntities']) && in_array('Menu', TBA_REGISTRY_SITES[$bxappSiteId]['bxappEntities'])) {
					$bxAppEntitiesDirs['menuDir'] .= '_'.$bxappSiteId;
				}
				if (isset(TBA_REGISTRY_SITES[$bxappSiteId]['bxappEntities']) && in_array('Middleware', TBA_REGISTRY_SITES[$bxappSiteId]['bxappEntities'])) {
					$bxAppEntitiesDirs['middlewareDir'] .= '_'.$bxappSiteId;
				}
				if (isset(TBA_REGISTRY_SITES[$bxappSiteId]['bxappEntities']) && in_array('Models', TBA_REGISTRY_SITES[$bxappSiteId]['bxappEntities'])) {
					$bxAppEntitiesDirs['modelsDir'] .= '_'.$bxappSiteId;
				}
				if (isset(TBA_REGISTRY_SITES[$bxappSiteId]['bxappEntities']) && in_array('Modules', TBA_REGISTRY_SITES[$bxappSiteId]['bxappEntities'])) {
					$bxAppEntitiesDirs['modulesDir'] .= '_'.$bxappSiteId;
				}
				if (isset(TBA_REGISTRY_SITES[$bxappSiteId]['bxappEntities']) && in_array('Router', TBA_REGISTRY_SITES[$bxappSiteId]['bxappEntities'])) {
					$bxAppEntitiesDirs['routerDir'] .= '_'.$bxappSiteId;
				}
				if (isset(TBA_REGISTRY_SITES[$bxappSiteId]['bxappEntities']) && in_array('Services', TBA_REGISTRY_SITES[$bxappSiteId]['bxappEntities'])) {
					$bxAppEntitiesDirs['servicesDir'] .= '_'.$bxappSiteId;
				}
				if (isset(TBA_REGISTRY_SITES[$bxappSiteId]['bxappEntities']) && in_array('Traits', TBA_REGISTRY_SITES[$bxappSiteId]['bxappEntities'])) {
					$bxAppEntitiesDirs['traitsDir'] .= '_'.$bxappSiteId;
				}
				if (isset(TBA_REGISTRY_SITES[$bxappSiteId]['bxappEntities']) && in_array('Views', TBA_REGISTRY_SITES[$bxappSiteId]['bxappEntities'])) {
					$bxAppEntitiesDirs['viewsDir'] .= '_'.$bxappSiteId;
				}
			}

			self::$bxAppEntitiesDirs[$bxappSiteId] = $bxAppEntitiesDirs;
		}

		return self::$bxAppEntitiesDirs[$bxappSiteId];
	}
}
