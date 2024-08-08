<?php
namespace Techart\BxApp;

class Define
{
	public static function setDefine(string $initPath = '')
	{
		// общее
		define("PROJECT_ROOT_DIR", realpath($initPath.'/../../../'));
		define("SITE_ROOT_DIR", PROJECT_ROOT_DIR.'/www');
		define("SITE_UPLOAD_DIR", SITE_ROOT_DIR.'/upload');
		define("APP_CACHE_DIR", SITE_ROOT_DIR.'/local/cache');
		define("APP_FAVICON_FILES_DIR", SITE_ROOT_DIR.'/local/favicon-files');
		define("APP_ROOT_DIR", SITE_ROOT_DIR.'/local/php_interface/lib');

		// Пути vendor
		define("APP_VENDOR_DIR", SITE_ROOT_DIR.'/local/vendor/techart/bxapp');
		define("APP_CORE_SETUP_DIR", APP_VENDOR_DIR.'/Setup');
		define("APP_SELF_DIR", APP_VENDOR_DIR.'/lib');
		define("APP_CORE_DIR", APP_SELF_DIR.'/Core');
		define("APP_CORE_BASE_DIR", APP_SELF_DIR.'/Base');
		define("APP_CORE_ROUTES_DIR", APP_SELF_DIR.'/Routes');
		define("APP_CORE_TRAITS_DIR", APP_SELF_DIR.'/Traits');

		// Пути app на сайте: php_interface/lib
		define("APP_CLI_DIR", APP_ROOT_DIR.'/Cli');
		define("APP_CONFIGS_DIR", APP_ROOT_DIR.'/Configs');
		define("APP_LOCALIZATION_DIR", APP_ROOT_DIR.'/Localization');
		define("APP_LOGS_DIR", APP_ROOT_DIR.'/Logs');
		define("APP_MENU_DIR", APP_ROOT_DIR.'/Menu');
		define("APP_MIDDLEWARE_DIR", APP_ROOT_DIR.'/Middleware');
		define("APP_MIDDLEWARE_BEFORE_DIR", APP_MIDDLEWARE_DIR.'/Before');
		define("APP_MIDDLEWARE_AFTER_DIR", APP_MIDDLEWARE_DIR.'/After');
		define("APP_MODELS_DIR", APP_ROOT_DIR.'/Models');
		define("APP_MODULES_DIR", APP_ROOT_DIR.'/Modules');
		define("APP_ROUTES_DIR", APP_ROOT_DIR.'/Routes');
		define("APP_SERVICES_DIR", APP_ROOT_DIR.'/Services');
		define("APP_TRAITS_DIR", APP_ROOT_DIR.'/Traits');
		define("APP_VIEWS_DIR", APP_ROOT_DIR.'/Views');
		define("APP_VIEWS_PDF_DIR", APP_VIEWS_DIR.'/Pdf');
		define("APP_CACHE_ROUTER_DIR", APP_CACHE_DIR.'/router');
		define("APP_CACHE_ROUTER_PAGES_DIR", APP_CACHE_ROUTER_DIR.'/pages');
	}
}
