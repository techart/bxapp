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
		define("PROJECT_ROOT_DIR", realpath($initPath.'/../../../'));
		define("SITE_ROOT_DIR", PROJECT_ROOT_DIR.'/www');
		define("SITE_UPLOAD_DIR", SITE_ROOT_DIR.'/upload');
		define("APP_CACHE_DIR", SITE_ROOT_DIR.'/local/cache');
		define("APP_CACHE_MENU_DIR_NAME", '_BxAppMenu');
		define("APP_FAVICON_FILES_DIR", SITE_ROOT_DIR.'/local/favicon-files');
		define("APP_PHP_INTERFACE_DIR", SITE_ROOT_DIR.'/local/php_interface');
	}

	/**
	 * Устанавливает константы registry
	 *
	 * @return void
	 */
	private static function defineRegistry(): void
	{
		$settings = \Techart\BxApp\Registry::setup();

		define('BXAPP_REGISTRY_SERVERS', $settings['servers']);
		define('BXAPP_REGISTRY_SITES', $settings['sites']);
		define('BXAPP_REGISTRY_GROUPS', $settings['groups']);
		define('BXAPP_REGISTRY_CURRENT_SERVER', $settings['currentServer']);
		define('BXAPP_REGISTRY_CURRENT_SITE', $settings['currentSite']);
		define('BXAPP_REGISTRY_CURRENT_LANGUAGE', $settings['currentLanguage']);
	}

	/**
	 * Устанавливает константы окружения, которые влияют на всё BxApp
	 *
	 * @return void
	 */
	private static function defineEnv(): void
	{
		define("BXAPP_SERVER_ID", BXAPP_REGISTRY_CURRENT_SERVER);
		define("BXAPP_SITE_ID", !empty(BXAPP_REGISTRY_CURRENT_SITE) ? BXAPP_REGISTRY_CURRENT_SITE : SITE_ID);
		define("BXAPP_LANGUAGE_ID", !empty(BXAPP_REGISTRY_CURRENT_LANGUAGE) ? BXAPP_REGISTRY_CURRENT_LANGUAGE : LANGUAGE_ID);

		if (isset(BXAPP_REGISTRY_SITES[BXAPP_SITE_ID]['group']) && !empty(BXAPP_REGISTRY_SITES[BXAPP_SITE_ID]['group'])) {
			if (isset(BXAPP_REGISTRY_GROUPS[BXAPP_REGISTRY_SITES[BXAPP_SITE_ID]['group']])) {
				define("BXAPP_SITE_GROUP_ID", BXAPP_REGISTRY_SITES[BXAPP_SITE_ID]['group']);
			} else {
				define("BXAPP_SITE_GROUP_ID", null);
			}
		} else {
			define("BXAPP_SITE_GROUP_ID", null);
		}

		Logger::info('defineEnv (BXAPP_SERVER_ID): '.json_encode(BXAPP_SERVER_ID));
		Logger::info('defineEnv (BXAPP_SITE_ID): '.BXAPP_SITE_ID);
		Logger::info('defineEnv (BXAPP_LANGUAGE_ID): '.BXAPP_LANGUAGE_ID);
		Logger::info('defineEnv (BXAPP_SITE_GROUP_ID): '.json_encode(BXAPP_SITE_GROUP_ID));

		// dd(BXAPP_REGISTRY_SERVERS, BXAPP_REGISTRY_SITES, BXAPP_REGISTRY_GROUPS, BXAPP_REGISTRY_CURRENT_SERVER, BXAPP_REGISTRY_CURRENT_SITE, BXAPP_REGISTRY_CURRENT_LANGUAGE, BXAPP_SERVER_ID, BXAPP_SITE_ID, BXAPP_LANGUAGE_ID, BXAPP_SITE_GROUP_ID);
	}

	/**
	 * Устанавливает константы путей к вендору bxapp
	 *
	 * @return void
	 */
	private static function defineVendorsPaths(): void
	{
		// Пути vendor
		define("APP_VENDOR_DIR", SITE_ROOT_DIR.'/local/vendor/techart/bxapp');
		define("APP_CORE_SETUP_DIR", APP_VENDOR_DIR.'/Setup');
		define("APP_CORE_BXAPP_SETUP_DIR", APP_VENDOR_DIR.'/Setup/BxApp');
		define("APP_SELF_DIR", APP_VENDOR_DIR.'/lib');
		define("APP_CORE_BASE_DIR", APP_SELF_DIR.'/Base');
		define("APP_CORE_CONFIGS", APP_SELF_DIR.'/Configs');
		define("APP_CORE_DIR", APP_SELF_DIR.'/Core');
		define("APP_CORE_MIDDLEWARE_DIR", APP_SELF_DIR.'/Middleware');
		define("APP_CORE_ROUTER_DIR", APP_SELF_DIR.'/Router');
		define("APP_CORE_TRAITS_DIR", APP_SELF_DIR.'/Traits');
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
		define("APP_ROOT_DIR", APP_PHP_INTERFACE_DIR.'/'.$bxAppPaths['bxAppDir']); // путь к папке BxApp (или её переопределению)
		define("APP_CLI_DIR", APP_ROOT_DIR.'/'.$bxAppPaths['cliDir']); // папка Cli
		define("APP_CONFIGS_DIR", APP_ROOT_DIR.'/'.$bxAppPaths['configsDir']); // папка Configs
		define("APP_LOCALIZATION_DIR", APP_ROOT_DIR.'/'.$bxAppPaths['localizationDir']); // папка Localization
		define("APP_LOGS_DIR", APP_ROOT_DIR.'/'.$bxAppPaths['logsDir']); // папка Logs
		define("APP_MENU_DIR", APP_ROOT_DIR.'/'.$bxAppPaths['menuDir']); // папка Menu
		define("APP_MIDDLEWARE_DIR", APP_ROOT_DIR.'/'.$bxAppPaths['middlewareDir']); // папка Middleware
		define("APP_MIDDLEWARE_BEFORE_DIR", APP_MIDDLEWARE_DIR.'/Before'); // папка Middleware/Before
		define("APP_MIDDLEWARE_AFTER_DIR", APP_MIDDLEWARE_DIR.'/After'); // папка Middleware/After
		define("APP_MODELS_DIR", APP_ROOT_DIR.'/'.$bxAppPaths['modelsDir']); // папка Models
		define("APP_MODULES_DIR", APP_ROOT_DIR.'/'.$bxAppPaths['modulesDir']); // папка Modules
		define("APP_ROUTER_DIR", APP_ROOT_DIR.'/'.$bxAppPaths['routerDir']); // папка Router
		define("APP_SERVICES_DIR", APP_ROOT_DIR.'/'.$bxAppPaths['servicesDir']); // папка Services
		define("APP_TRAITS_DIR", APP_ROOT_DIR.'/'.$bxAppPaths['traitsDir']); // папка Traits
		define("APP_VIEWS_DIR", APP_ROOT_DIR.'/'.$bxAppPaths['viewsDir']); // папка Views
		define("APP_VIEWS_PDF_DIR", APP_VIEWS_DIR.'/Pdf'); // папка Views/Pdf
		define("APP_CACHE_BLADE_DIR", APP_CACHE_DIR.'/blade'); // папка кэша блейда
		define("APP_CACHE_ROUTER_ROOT_DIR", APP_CACHE_DIR.'/router'); // папка кэша роутера
		define("APP_CACHE_ROUTER_DIR", APP_CACHE_ROUTER_ROOT_DIR.'/'.BXAPP_SITE_ID); // папка кэша роутера сайта
		define("APP_CACHE_ROUTER_PAGES_DIR", APP_CACHE_ROUTER_DIR.'/routes'); // папка кэша роутов (страниц)
		define("APP_CACHE_STATIC_ROOT_DIR", APP_CACHE_DIR.'/static'); // папка кэша статики
		define("APP_CACHE_STATIC_DIR", APP_CACHE_STATIC_ROOT_DIR.'/'.BXAPP_SITE_ID); // папка кэша статики сайта
		define("APP_CACHE_MODELS_DIR", APP_CACHE_DIR.'/models/'.BXAPP_SITE_ID); // папка кэша роутов моделей
	}
}
