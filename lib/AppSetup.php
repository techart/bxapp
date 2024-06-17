<?php
namespace Techart\BxApp;


class AppSetup
{
	/**
	 * vphp cli.php app_createModel Test/Test/Test
	 *
	 * @param array $options
	 * @return void
	 */
	public static function createModel(array $options = [])
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

		file_put_contents(APP_MODELS_DIR.'/'.$path.'.php', $template);
	}

	/**
	 * vphp cli.php app_createCli CliDir/CliClass method
	 *
	 * @param array $options
	 * @return void
	 */
	public static function createCli(array $options = [])
	{
		$path = str_replace('\\', '/', $options[0]);
		$method = $options[1] ?? 'cliMethod';
		$explodePath = explodePathString($path);
		$cliName = ucfirst($explodePath['file']);
		$cliMethod = strtolower($method);
		$template = file_get_contents(APP_CORE_SETUP_DIR.'/TemplateFiles/CliTemplate.php');
		$template = str_replace(
			['{{cli_name}}', '{{cli_method}}'],
			[$cliName, strtolower($cliMethod)],
			$template
		);

		if (!is_dir(APP_CLI_DIR)) {
			mkdir(APP_CLI_DIR);
		}
		createDirsChaine(APP_CLI_DIR, $explodePath['dirs']);

		file_put_contents(APP_CLI_DIR.'/'.$path.'.php', $template);
	}

	/**
	 * vphp cli.php app_createBundle Catalog
	 *
	 * @param array $options
	 * @return void
	 */
	public static function createBundle(array $options = [])
	{
		$bundleName = ucfirst(str_replace(['\\', '/'], '', $options[0]));

		if (!is_dir(APP_ROUTES_DIR.'/'.$bundleName)) {
			recurseCopy(APP_CORE_SETUP_DIR.'/TemplateFiles/Routes/BundleName', APP_ROUTES_DIR.'/'.$bundleName);

			$template = file_get_contents(APP_ROUTES_DIR.'/'.$bundleName.'/Controllers/Actions.php');
			$template = str_replace(
				['{{BundleName}}'],
				[$bundleName],
				$template
			);
			file_put_contents(APP_ROUTES_DIR.'/'.$bundleName.'/Controllers/Actions.php', $template);
		}
	}

	/**
	 * vphp cli.php app_app_createMiddlewareAfter MiddlewareName
	 *
	 * @param array $options
	 * @return void
	 */
	public static function createMiddlewareAfter(array $options = [])
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
	 * vphp cli.php app_createMiddlewareBefore MiddlewareName
	 *
	 * @param array $options
	 * @return void
	 */
	public static function createMiddlewareBefore(array $options = [])
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
	 * vphp cli.php app_setup
	 *
	 * @return void
	 */
	public static function setup()
	{
		recurseCopy(APP_CORE_SETUP_DIR.'/Cli', APP_CLI_DIR);
		recurseCopy(APP_CORE_SETUP_DIR.'/Configs', APP_CONFIGS_DIR);
		recurseCopy(APP_CORE_SETUP_DIR.'/Localization', APP_LOCALIZATION_DIR);
		recurseCopy(APP_CORE_SETUP_DIR.'/Logs', APP_LOGS_DIR);
		// recurseCopy(APP_CORE_SETUP_DIR.'/Menu', APP_MENU_DIR);
		if (!is_dir(APP_MENU_DIR)) {
			mkdir(APP_MENU_DIR);
		}
		recurseCopy(APP_CORE_SETUP_DIR.'/Middleware', APP_MIDDLEWARE_DIR);
		// recurseCopy(APP_CORE_SETUP_DIR.'/Models', APP_MODELS_DIR);
		if (!is_dir(APP_MODELS_DIR)) {
			mkdir(APP_MODELS_DIR);
		}
		// recurseCopy(APP_CORE_SETUP_DIR.'/Modules', APP_MODULES_DIR);
		if (!is_dir(APP_MODULES_DIR)) {
			mkdir(APP_MODULES_DIR);
		}
		// recurseCopy(APP_CORE_SETUP_DIR.'/Routes', APP_ROUTES_DIR);
		if (!is_dir(APP_ROUTES_DIR)) {
			mkdir(APP_ROUTES_DIR);
		}
		// recurseCopy(APP_CORE_SETUP_DIR.'/Services', APP_SERVICES_DIR);
		if (!is_dir(APP_SERVICES_DIR)) {
			mkdir(APP_SERVICES_DIR);
		}
		recurseCopy(APP_CORE_SETUP_DIR.'/Traits', APP_TRAITS_DIR);
		recurseCopy(APP_CORE_SETUP_DIR.'/Views', APP_VIEWS_DIR);

		if (!is_dir(APP_FAVICON_FILES_DIR)) {
			mkdir(APP_FAVICON_FILES_DIR);
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
	}

	/**
	 * vphp cli.php app_setupTemplate
	 *
	 * @return void
	 */
	public static function setupTemplate()
	{
		$templateHeader = file_get_contents(APP_CORE_SETUP_DIR.'/TemplateFiles/header.php');
		$templateFooter = file_get_contents(APP_CORE_SETUP_DIR.'/TemplateFiles/footer.php');

		file_put_contents(SITE_ROOT_DIR.SITE_TEMPLATE_PATH.'/header.php', $templateHeader);
		file_put_contents(SITE_ROOT_DIR.SITE_TEMPLATE_PATH.'/footer.php', $templateFooter);

	}
}
