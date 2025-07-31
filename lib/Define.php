<?php
namespace Techart\BxApp;

/**
 * Класс для задания констант BxApp
 */

class Define
{
	/**
	 * Запускает установку констант
	 *
	 * @param string $initPath
	 * @return void
	 */
	public static function set(string $initPath = ''): void
	{
		self::defineBasePaths($initPath);
		self::defineVendorsPaths();
		self::defineRegistry();
		self::defineEnv();
		self::defineBxAppPaths();
	}

	/**
	 * Устанавливает константы базовых путей
	 *
	 * @return void
	 */
	private static function defineBasePaths(string $initPath = ''): void
	{
		if (empty($initPath)) {
			throw new \LogicException('В \Techart\BxApp\App::init() не передан $initPath!');
			exit();
		}
		// общее
		define("TBA_PROJECT_ROOT_DIR", realpath($initPath.'/../../../'));
		define("TBA_SITE_ROOT_DIR", TBA_PROJECT_ROOT_DIR.'/www');
		define("TBA_SITE_UPLOAD_DIR", TBA_SITE_ROOT_DIR.'/upload');
		define("TBA_APP_CACHE_DIR", TBA_SITE_ROOT_DIR.'/local/cache');
		define("TBA_APP_BITRIX_CACHE_DIR", TBA_SITE_ROOT_DIR.'/bitrix/cache');
		define("TBA_APP_CACHE_MENU_DIR_NAME", '_BxAppMenu');
		define("TBA_APP_FAVICON_FILES_DIR", TBA_SITE_ROOT_DIR.'/local/favicon-files');
		define("TBA_APP_PHP_INTERFACE_DIR", TBA_SITE_ROOT_DIR.'/local/php_interface');
	}

	/**
	 * Устанавливает константы registry
	 *
	 * @return void
	 */
	private static function defineRegistry(): void
	{
		$settings = \Techart\BxApp\Registry::setup();

		define('TBA_REGISTRY_SERVERS', $settings['servers']);
		define('TBA_REGISTRY_SITES', $settings['sites']);
		define('TBA_REGISTRY_GROUPS', $settings['groups']);
		define('TBA_REGISTRY_CURRENT_SERVER', $settings['currentServer']);
		define('TBA_REGISTRY_CURRENT_SITE', $settings['currentSite']);
		define('TBA_REGISTRY_CURRENT_LANGUAGE', $settings['currentLanguage']);
	}

	/**
	 * Устанавливает константы окружения, которые влияют на всё BxApp
	 *
	 * @return void
	 */
	private static function defineEnv(): void
	{
		define("TBA_SERVER_ID", TBA_REGISTRY_CURRENT_SERVER);
		define("TBA_SITE_ID", !empty(TBA_REGISTRY_CURRENT_SITE) ? TBA_REGISTRY_CURRENT_SITE : SITE_ID);
		define("TBA_LANGUAGE_ID", !empty(TBA_REGISTRY_CURRENT_LANGUAGE) ? TBA_REGISTRY_CURRENT_LANGUAGE : LANGUAGE_ID);

		if (isset(TBA_REGISTRY_SITES[TBA_SITE_ID]['group']) && !empty(TBA_REGISTRY_SITES[TBA_SITE_ID]['group'])) {
			if (isset(TBA_REGISTRY_GROUPS[TBA_REGISTRY_SITES[TBA_SITE_ID]['group']])) {
				define("TBA_SITE_GROUP_ID", TBA_REGISTRY_SITES[TBA_SITE_ID]['group']);
			} else {
				define("TBA_SITE_GROUP_ID", null);
			}
		} else {
			define("TBA_SITE_GROUP_ID", null);
		}

		Logger::info('defineEnv (TBA_SERVER_ID): '.json_encode(TBA_SERVER_ID));
		Logger::info('defineEnv (TBA_SITE_ID): '.TBA_SITE_ID);
		Logger::info('defineEnv (TBA_LANGUAGE_ID): '.TBA_LANGUAGE_ID);
		Logger::info('defineEnv (TBA_SITE_GROUP_ID): '.json_encode(TBA_SITE_GROUP_ID));

		// dd(TBA_REGISTRY_SERVERS, TBA_REGISTRY_SITES, TBA_REGISTRY_GROUPS, TBA_REGISTRY_CURRENT_SERVER, TBA_REGISTRY_CURRENT_SITE, TBA_REGISTRY_CURRENT_LANGUAGE, TBA_SERVER_ID, TBA_SITE_ID, TBA_LANGUAGE_ID, TBA_SITE_GROUP_ID);
	}

	/**
	 * Устанавливает константы путей к вендору bxapp
	 *
	 * @return void
	 */
	private static function defineVendorsPaths(): void
	{
		// Пути vendor
		define("TECHART_BXAPP_VENDOR_DIR", TBA_SITE_ROOT_DIR.'/local/vendor/techart/bxapp');
		define("TECHART_BXAPP_CORE_SETUP_DIR", TECHART_BXAPP_VENDOR_DIR.'/Setup');
		define("TECHART_BXAPP_CORE_BXAPP_SETUP_DIR", TECHART_BXAPP_VENDOR_DIR.'/Setup/BxApp');
		define("TECHART_BXAPP_SELF_DIR", TECHART_BXAPP_VENDOR_DIR.'/lib');
		define("TECHART_BXAPP_CORE_BASE_DIR", TECHART_BXAPP_SELF_DIR.'/Base');
		define("TECHART_BXAPP_CORE_CONFIGS", TECHART_BXAPP_SELF_DIR.'/Configs');
		define("TECHART_BXAPP_CORE_DIR", TECHART_BXAPP_SELF_DIR.'/Core');
		define("TECHART_BXAPP_CORE_MIDDLEWARE_DIR", TECHART_BXAPP_SELF_DIR.'/Middleware');
		define("TECHART_BXAPP_CORE_ROUTER_DIR", TECHART_BXAPP_SELF_DIR.'/Router');
		define("TECHART_BXAPP_CORE_TRAITS_DIR", TECHART_BXAPP_SELF_DIR.'/Traits');
	}

	/**
	 * Устанавливает константы путей к BxApp
	 *
	 * @return void
	 */
	private static function defineBxAppPaths(): void
	{
		$bxAppPaths = Registry::buildBxAppEntitiesDirs();

		// Пути app на сайте: php_interface/BxApp
		define("TBA_APP_ROOT_DIR", TBA_APP_PHP_INTERFACE_DIR.'/'.$bxAppPaths['bxAppDir']); // путь к папке BxApp (или её переопределению)
		define("TBA_APP_CLI_DIR", TBA_APP_ROOT_DIR.'/'.$bxAppPaths['cliDir']); // папка Cli
		define("TBA_APP_CONFIGS_DIR", TBA_APP_ROOT_DIR.'/'.$bxAppPaths['configsDir']); // папка Configs
		define("TBA_APP_ENTITIES_DIR", TBA_APP_ROOT_DIR.'/'.$bxAppPaths['entitiesDir']); // папка Entities
		define("TBA_APP_LOCALIZATION_DIR", TBA_APP_ROOT_DIR.'/'.$bxAppPaths['localizationDir']); // папка Localization
		define("TBA_APP_LOGS_DIR", TBA_APP_ROOT_DIR.'/'.$bxAppPaths['logsDir']); // папка Logs
		define("TBA_APP_MENU_DIR", TBA_APP_ROOT_DIR.'/'.$bxAppPaths['menuDir']); // папка Menu
		define("TBA_APP_MIDDLEWARE_DIR", TBA_APP_ROOT_DIR.'/'.$bxAppPaths['middlewareDir']); // папка Middleware
		define("TBA_APP_MIDDLEWARE_BEFORE_DIR", TBA_APP_MIDDLEWARE_DIR.'/Before'); // папка Middleware/Before
		define("TBA_APP_MIDDLEWARE_AFTER_DIR", TBA_APP_MIDDLEWARE_DIR.'/After'); // папка Middleware/After
		define("TBA_APP_MODELS_DIR", TBA_APP_ROOT_DIR.'/'.$bxAppPaths['modelsDir']); // папка Models
		define("TBA_APP_MODULES_DIR", TBA_APP_ROOT_DIR.'/'.$bxAppPaths['modulesDir']); // папка Modules
		define("TBA_APP_ROUTER_DIR", TBA_APP_ROOT_DIR.'/'.$bxAppPaths['routerDir']); // папка Router
		define("TBA_APP_SERVICES_DIR", TBA_APP_ROOT_DIR.'/'.$bxAppPaths['servicesDir']); // папка Services
		define("TBA_APP_TRAITS_DIR", TBA_APP_ROOT_DIR.'/'.$bxAppPaths['traitsDir']); // папка Traits
		define("TBA_APP_VIEWS_DIR", TBA_APP_ROOT_DIR.'/'.$bxAppPaths['viewsDir']); // папка Views
		define("TBA_APP_VIEWS_PDF_DIR", TBA_APP_VIEWS_DIR.'/Pdf'); // папка Views/Pdf
		define("TBA_APP_CACHE_BLADE_DIR", TBA_APP_CACHE_DIR.'/blade'); // папка кэша блейда
		define("TBA_APP_CACHE_ROUTER_ROOT_DIR", TBA_APP_CACHE_DIR.'/router'); // папка кэша роутера
		define("TBA_APP_CACHE_ROUTER_DIR", TBA_APP_CACHE_ROUTER_ROOT_DIR.'/'.TBA_SITE_ID); // папка кэша роутера сайта
		define("TBA_APP_CACHE_ROUTER_PAGES_DIR", TBA_APP_CACHE_ROUTER_DIR.'/routes'); // папка кэша роутов (страниц)
		define("TBA_APP_CACHE_STATIC_ROOT_DIR", TBA_APP_CACHE_DIR.'/static'); // папка кэша статики
		define("TBA_APP_CACHE_STATIC_DIR", TBA_APP_CACHE_STATIC_ROOT_DIR.'/'.TBA_SITE_ID); // папка кэша статики сайта
		define("TBA_APP_CACHE_MODELS_ROOT_DIR", TBA_APP_CACHE_DIR.'/models'); // папка кэша роутов моделей
		define("TBA_APP_CACHE_MODELS_DIR", TBA_APP_CACHE_MODELS_ROOT_DIR.'/'.TBA_SITE_ID); // папка кэша роутов моделей сайта
	}
}
