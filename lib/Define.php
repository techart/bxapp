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
		define("APP_SELF_DIR", APP_VENDOR_DIR.'/lib');
		define("APP_CORE_DIR", APP_SELF_DIR.'/Core');
		define("APP_CORE_BASE_DIR", APP_SELF_DIR.'/Base');
		define("APP_CORE_ROUTES_DIR", APP_SELF_DIR.'/Routes');
		define("APP_CORE_TRAITS_DIR", APP_SELF_DIR.'/Traits');
	}

	/**
	 * Собирает пути к BxApp директориям
	 *
	 * @return void
	 */
	private static function buildBxAppPaths(): array
	{
		$bxAppPaths = [
			'bxAppDir' => 'BxApp',
			'cliDir' => 'Cli',
			'configsDir' => 'Configs',
			'localizationDir' => 'Localization',
			'logsDir' => 'Logs',
			'menuDir' => 'Menu',
			'middlewareDir' => 'Middleware',
			'modelsDir' => 'Models',
			'modulesDir' => 'Modules',
			'routesDir' => 'Routes',
			'servicesDir' => 'Services',
			'traitsDir' => 'Traits',
			'viewsDir' => 'Views',
		];

		if (isset(BXAPP_REGISTRY_SITES[BXAPP_SITE_ID]['bxappDir']) && !empty(BXAPP_REGISTRY_SITES[BXAPP_SITE_ID]['bxappDir'])) {
			$bxAppPaths['bxAppDir'] = BXAPP_REGISTRY_SITES[BXAPP_SITE_ID]['bxappDir'];
		}
		if (isset(BXAPP_REGISTRY_SITES[BXAPP_SITE_ID]['bxappEntities']) && in_array('Cli', BXAPP_REGISTRY_SITES[BXAPP_SITE_ID]['bxappEntities'])) {
			$bxAppPaths['cliDir'] .= '_'.BXAPP_SITE_ID;
		}
		if (isset(BXAPP_REGISTRY_SITES[BXAPP_SITE_ID]['bxappEntities']) && in_array('Configs', BXAPP_REGISTRY_SITES[BXAPP_SITE_ID]['bxappEntities'])) {
			$bxAppPaths['configsDir'] .= '_'.BXAPP_SITE_ID;
		}
		if (isset(BXAPP_REGISTRY_SITES[BXAPP_SITE_ID]['bxappEntities']) && in_array('Localization', BXAPP_REGISTRY_SITES[BXAPP_SITE_ID]['bxappEntities'])) {
			$bxAppPaths['localizationDir'] .= '_'.BXAPP_SITE_ID;
		}
		if (isset(BXAPP_REGISTRY_SITES[BXAPP_SITE_ID]['bxappEntities']) && in_array('Logs', BXAPP_REGISTRY_SITES[BXAPP_SITE_ID]['bxappEntities'])) {
			$bxAppPaths['logsDir'] .= '_'.BXAPP_SITE_ID;
		}
		if (isset(BXAPP_REGISTRY_SITES[BXAPP_SITE_ID]['bxappEntities']) && in_array('Menu', BXAPP_REGISTRY_SITES[BXAPP_SITE_ID]['bxappEntities'])) {
			$bxAppPaths['menuDir'] .= '_'.BXAPP_SITE_ID;
		}
		if (isset(BXAPP_REGISTRY_SITES[BXAPP_SITE_ID]['bxappEntities']) && in_array('Middleware', BXAPP_REGISTRY_SITES[BXAPP_SITE_ID]['bxappEntities'])) {
			$bxAppPaths['middlewareDir'] .= '_'.BXAPP_SITE_ID;
		}
		if (isset(BXAPP_REGISTRY_SITES[BXAPP_SITE_ID]['bxappEntities']) && in_array('Models', BXAPP_REGISTRY_SITES[BXAPP_SITE_ID]['bxappEntities'])) {
			$bxAppPaths['modelsDir'] .= '_'.BXAPP_SITE_ID;
		}
		if (isset(BXAPP_REGISTRY_SITES[BXAPP_SITE_ID]['bxappEntities']) && in_array('Modules', BXAPP_REGISTRY_SITES[BXAPP_SITE_ID]['bxappEntities'])) {
			$bxAppPaths['modulesDir'] .= '_'.BXAPP_SITE_ID;
		}
		if (isset(BXAPP_REGISTRY_SITES[BXAPP_SITE_ID]['bxappEntities']) && in_array('Routes', BXAPP_REGISTRY_SITES[BXAPP_SITE_ID]['bxappEntities'])) {
			$bxAppPaths['routesDir'] .= '_'.BXAPP_SITE_ID;
		}
		if (isset(BXAPP_REGISTRY_SITES[BXAPP_SITE_ID]['bxappEntities']) && in_array('Services', BXAPP_REGISTRY_SITES[BXAPP_SITE_ID]['bxappEntities'])) {
			$bxAppPaths['servicesDir'] .= '_'.BXAPP_SITE_ID;
		}
		if (isset(BXAPP_REGISTRY_SITES[BXAPP_SITE_ID]['bxappEntities']) && in_array('Traits', BXAPP_REGISTRY_SITES[BXAPP_SITE_ID]['bxappEntities'])) {
			$bxAppPaths['traitsDir'] .= '_'.BXAPP_SITE_ID;
		}
		if (isset(BXAPP_REGISTRY_SITES[BXAPP_SITE_ID]['bxappEntities']) && in_array('Views', BXAPP_REGISTRY_SITES[BXAPP_SITE_ID]['bxappEntities'])) {
			$bxAppPaths['viewsDir'] .= '_'.BXAPP_SITE_ID;
		}

		return $bxAppPaths;
	}

	/**
	 * Устанавливает константы путей к BxApp
	 *
	 * @return void
	 */
	private static function defineBxAppPaths(): void
	{
		$bxAppPaths = self::buildBxAppPaths();

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
		define("APP_ROUTES_DIR", APP_ROOT_DIR.'/'.$bxAppPaths['routesDir']); // папка Routes
		define("APP_SERVICES_DIR", APP_ROOT_DIR.'/'.$bxAppPaths['servicesDir']); // папка Services
		define("APP_TRAITS_DIR", APP_ROOT_DIR.'/'.$bxAppPaths['traitsDir']); // папка Traits
		define("APP_VIEWS_DIR", APP_ROOT_DIR.'/'.$bxAppPaths['viewsDir']); // папка Views
		define("APP_VIEWS_PDF_DIR", APP_VIEWS_DIR.'/Pdf'); // папка Views/Pdf
		define("APP_CACHE_ROUTER_DIR", APP_CACHE_DIR.'/router/'.BXAPP_SITE_ID); // папка кэша роутера
		define("APP_CACHE_ROUTER_PAGES_DIR", APP_CACHE_ROUTER_DIR.'/pages'); // папка кэша роутов (страниц)
	}
}
