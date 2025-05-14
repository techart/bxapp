<?php
namespace Techart\BxApp;

/**
 * Класс содержит методы для выполнения cli команд по сетапу проекта
 */

class AppSetup
{
	/**
	 * Создаёт файл модели
	 * vphp cli.php app_createModel Test/Test/Test
	 *
	 * @param array $options
	 * @return void
	 */
	public static function createModel(array $options = [], string $siteId = ''): void
	{
		$path = str_replace('\\', '/', $options[0]);
		$explodePath = explodePathString($path);
		$modelName = ucfirst($explodePath['file']);
		$template = file_get_contents(APP_CORE_SETUP_DIR.'/TemplateFiles/ModelTemplate.php');
		$template = str_replace(
			['{{model_name}}', '{{model_table}}'],
			[$modelName, strtolower($modelName)],
			$template
		);

		if (!is_dir(APP_MODELS_DIR)) {
			mkdir(APP_MODELS_DIR);
		}
		createDirsChaine(APP_MODELS_DIR, $explodePath['dirs']);

		file_put_contents(APP_MODELS_DIR.'/'.implode('/', $explodePath['dirs']).'/'.$modelName.'.php', $template);
	}

	/**
	 * Создаёт класс для CLI команды
	 * vphp cli.php app_createCli CliClass method
	 *
	 * @param array $options
	 * @return void
	 */
	public static function createCli(array $options = [], string $siteId = ''): void
	{
		$path = str_replace('\\', '/', $options[0]);
		$method = $options[1] ?? 'cliMethod';
		$explodePath = explodePathString($path);
		$cliName = ucfirst($explodePath['file']);
		$cliMethod = strtolower($method);
		$template = file_get_contents(APP_CORE_SETUP_DIR.'/TemplateFiles/CliTemplate.php');
		$cliNamespace = !empty($explodePath['dirs']) ? '\\'.implode('\\', $explodePath['dirs']) : '';
		$template = str_replace(
			['{{cli_name}}', '{{cli_method}}', '{{cli_namespace}}'],
			[$cliName, strtolower($cliMethod), $cliNamespace],
			$template
		);

		if (!is_dir(APP_CLI_DIR)) {
			mkdir(APP_CLI_DIR);
		}
		createDirsChaine(APP_CLI_DIR, $explodePath['dirs']);

		file_put_contents(APP_CLI_DIR.'/'.implode('/', $explodePath['dirs']).'/'.$cliName.'.php', $template);
	}

	/**
	 * Создаёт бандл
	 * vphp cli.php app_createBundle Catalog
	 *
	 * @param array $options
	 * @return void
	 */
	public static function createBundle(array $options = [], string $siteId = ''): void
	{
		$bundleName = ucfirst(str_replace(['\\', '/'], '', $options[0]));

		if (!is_dir(APP_ROUTER_DIR.'/'.$bundleName)) {
			recurseCopy(APP_CORE_SETUP_DIR.'/TemplateFiles/Router/BundleName', APP_ROUTER_DIR.'/'.$bundleName);

			$template = file_get_contents(APP_ROUTER_DIR.'/'.$bundleName.'/Controllers/Actions.php');
			$template = str_replace(
				['{{BundleName}}'],
				[$bundleName],
				$template
			);
			file_put_contents(APP_ROUTER_DIR.'/'.$bundleName.'/Controllers/Actions.php', $template);
		}
	}

	/**
	 * Создаёт файл паосредника After
	 * vphp cli.php app_app_createMiddlewareAfter MiddlewareName
	 *
	 * @param array $options
	 * @return void
	 */
	public static function createMiddlewareAfter(array $options = [], string $siteId = ''): void
	{
		$name = ucfirst(str_replace(['\\', '/'], '', $options[0]));

		if (!file_exists(APP_MIDDLEWARE_AFTER_DIR.'/'.$name.'.php')) {
			copy(APP_CORE_SETUP_DIR.'/TemplateFiles/MiddlewareAfter.php', APP_MIDDLEWARE_AFTER_DIR.'/'.$name.'.php');

			$template = file_get_contents(APP_MIDDLEWARE_AFTER_DIR.'/'.$name.'.php');
			$template = str_replace(
				['{{middleware_name}}'],
				[$name],
				$template
			);
			file_put_contents(APP_MIDDLEWARE_AFTER_DIR.'/'.$name.'.php', $template);
		}
	}

	/**
	 * Создаёт файл паосредника Before
	 * vphp cli.php app_createMiddlewareBefore MiddlewareName
	 *
	 * @param array $options
	 * @return void
	 */
	public static function createMiddlewareBefore(array $options = [], string $siteId = ''): void
	{
		$name = ucfirst(str_replace(['\\', '/'], '', $options[0]));

		if (!file_exists(APP_MIDDLEWARE_BEFORE_DIR.'/'.$name.'.php')) {
			copy(APP_CORE_SETUP_DIR.'/TemplateFiles/MiddlewareBefore.php', APP_MIDDLEWARE_BEFORE_DIR.'/'.$name.'.php');

			$template = file_get_contents(APP_MIDDLEWARE_BEFORE_DIR.'/'.$name.'.php');
			$template = str_replace(
				['{{middleware_name}}'],
				[$name],
				$template
			);
			file_put_contents(APP_MIDDLEWARE_BEFORE_DIR.'/'.$name.'.php', $template);
		}
	}

	/**
	 * Создаёт BxApp структуру файлов в проекте
	 *
	 * vphp cli.php app_setup
	 *
	 * @return void
	 */
	public static function setup($siteId = '', $isSetSiteId = false): void
	{
		$rootDir = APP_PHP_INTERFACE_DIR . '/' . 'BxApp' . ($isSetSiteId ? '_'.$siteId : '');
		if (!is_dir($rootDir)) {
			mkdir($rootDir);
		}

		recurseCopy(APP_CORE_BXAPP_SETUP_DIR.'/Cli', $rootDir . '/Cli');
		recurseCopy(APP_CORE_BXAPP_SETUP_DIR.'/Configs', $rootDir . '/Configs');
		recurseCopy(APP_CORE_BXAPP_SETUP_DIR.'/Localization', $rootDir . '/Localization');
		recurseCopy(APP_CORE_BXAPP_SETUP_DIR.'/Logs', $rootDir . '/Logs');
		recurseCopy(APP_CORE_BXAPP_SETUP_DIR.'/Menu', $rootDir . '/Menu');
		recurseCopy(APP_CORE_BXAPP_SETUP_DIR.'/Middleware', $rootDir . '/Middleware');
		recurseCopy(APP_CORE_BXAPP_SETUP_DIR.'/Models', $rootDir . '/Models');
		recurseCopy(APP_CORE_BXAPP_SETUP_DIR.'/Modules', $rootDir . '/Modules');
		recurseCopy(APP_CORE_BXAPP_SETUP_DIR.'/Router', $rootDir . '/Router');
		recurseCopy(APP_CORE_BXAPP_SETUP_DIR.'/Services', $rootDir . '/Services');
		recurseCopy(APP_CORE_BXAPP_SETUP_DIR.'/Traits', $rootDir . '/Traits');
		recurseCopy(APP_CORE_BXAPP_SETUP_DIR.'/Views', $rootDir . '/Views');

		recurseCopy(APP_CORE_SETUP_DIR.'/Events', APP_PHP_INTERFACE_DIR.'/Events');
		recurseCopy(APP_CORE_SETUP_DIR.'/Lib', APP_PHP_INTERFACE_DIR.'/Lib');

		if (!file_exists(APP_PHP_INTERFACE_DIR.'/BxAppRegistry.php')) {
			copy(APP_CORE_SETUP_DIR.'/BxAppRegistry.php', APP_PHP_INTERFACE_DIR.'/BxAppRegistry.php');
		}
		if (!file_exists(APP_PHP_INTERFACE_DIR.'/BxAppRouterProcessingProlog.php')) {
			copy(APP_CORE_SETUP_DIR.'/BxAppRouterProcessingProlog.php', APP_PHP_INTERFACE_DIR.'/BxAppRouterProcessingProlog.php');
		}
		if (file_exists(APP_PHP_INTERFACE_DIR.'/init.php')) {
			unlink(APP_PHP_INTERFACE_DIR.'/init.php');
		}
		copy(APP_CORE_SETUP_DIR.'/init.php', APP_PHP_INTERFACE_DIR.'/init.php');
		if (file_exists(APP_PHP_INTERFACE_DIR.'/cli.php')) {
			unlink(APP_PHP_INTERFACE_DIR.'/cli.php');
		}
		copy(APP_CORE_SETUP_DIR.'/cli.php', APP_PHP_INTERFACE_DIR.'/cli.php');
		if (!is_dir(APP_FAVICON_FILES_DIR)) {
			mkdir(APP_FAVICON_FILES_DIR);
			copy(APP_CORE_SETUP_DIR.'/gitkeep', APP_FAVICON_FILES_DIR.'/.gitkeep');
		}
		if (!is_dir(APP_CACHE_DIR)) {
			mkdir(APP_CACHE_DIR);
		}
		if (!is_dir(SITE_ROOT_DIR.SITE_TEMPLATE_PATH.'/assets')) {
			mkdir(SITE_ROOT_DIR.SITE_TEMPLATE_PATH.'/assets');
			copy(APP_CORE_SETUP_DIR.'/gitkeep', SITE_ROOT_DIR.SITE_TEMPLATE_PATH.'/assets/.gitkeep');
		}
		if (!is_dir(APP_CACHE_DIR.'/blade')) {
			mkdir(APP_CACHE_DIR.'/blade');
		}
		if (!is_dir(APP_CACHE_ROUTER_DIR)) {
			mkdir(APP_CACHE_ROUTER_DIR);
		}
		if (!file_exists(PROJECT_ROOT_DIR.'/.env')) {
			copy(APP_CORE_SETUP_DIR.'/.env', PROJECT_ROOT_DIR.'/.env');
		}
		if (!file_exists(PROJECT_ROOT_DIR.'/.env.example')) {
			copy(APP_CORE_SETUP_DIR.'/.env.example', PROJECT_ROOT_DIR.'/.env.example');
		}
		if (!file_exists(APP_ROOT_DIR.'/.gitignore')) {
			copy(APP_CORE_SETUP_DIR.'/gitignore', APP_ROOT_DIR.'/.gitignore');
		}
		if (file_exists(SITE_ROOT_DIR.'/local/.config.php')) {
			unlink(SITE_ROOT_DIR.'/local/.config.php');
		}
	}

	/**
	 * Создаёт файлы для BxApp лейаута
	 *
	 * vphp cli.php app_setupTemplate
	 *
	 * @return void
	 */
	public static function setupTemplate(): void
	{
		$templateHeader = file_get_contents(APP_CORE_SETUP_DIR.'/TemplateFiles/header.php');
		$templateFooter = file_get_contents(APP_CORE_SETUP_DIR.'/TemplateFiles/footer.php');

		file_put_contents(SITE_ROOT_DIR.SITE_TEMPLATE_PATH.'/header.php', $templateHeader);
		file_put_contents(SITE_ROOT_DIR.SITE_TEMPLATE_PATH.'/footer.php', $templateFooter);
	}
}
