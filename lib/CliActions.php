<?php
namespace Techart\BxApp;

/**
 * Класс содержит дефолтные cli команды проекта.
 */

class CliActions
{
	/**
	 * vphp cli.php setup:app
	 *
	 * @return void
	 */
	public function setup(): void
	{
		$args = func_get_args();
		AppSetup::setup($args[1], $args[2]);

		echo 'Создание файлов завершено!'.PHP_EOL;
	}

	/**
	 * vphp cli.php setup:template
	 *
	 * @return void
	 */
	public function setupTemplate(): void
	{
		AppSetup::setupTemplate();

		echo 'Создание файлов завершено!'.PHP_EOL;
	}

	/**
	 * vphp cli.php create:model Test/Test/Test
	 *
	 * @return void
	 */
	public function createModel(): void
	{
		$args = func_get_args();
		AppSetup::createModel($args[0], $args[1]);

		echo 'Создание модели завершено!'.PHP_EOL;
	}

	/**
	 * vphp cli.php create:cli CliClass method
	 *
	 * @return void
	 */
	public function createCli(): void
	{
		$args = func_get_args();
		AppSetup::createCli($args[0], $args[1]);

		echo 'Создание Cli класса завершено!'.PHP_EOL;
	}

	/**
	 * vphp cli.php create:bundle Catalog
	 *
	 * @return void
	 */
	public function createBundle(): void
	{
		$args = func_get_args();
		AppSetup::createBundle($args[0], $args[1]);

		echo 'Создание бандла завершено!'.PHP_EOL;
	}

	/**
	 * vphp cli.php create:middlewareAfter MiddlewareName
	 *
	 * @return void
	 */
	public function createMiddlewareAfter(): void
	{
		$args = func_get_args();
		AppSetup::createMiddlewareAfter($args[0], $args[1]);

		echo 'Создание middleware завершено!'.PHP_EOL;
	}

	/**
	 * vphp cli.php create:middlewareBefore MiddlewareName
	 *
	 * @return void
	 */
	public function createMiddlewareBefore(): void
	{
		$args = func_get_args();
		AppSetup::createMiddlewareBefore($args[0], $args[1]);

		echo 'Создание middleware завершено!'.PHP_EOL;
	}

	/**
	 * vphp cli.php cache:clear all
	 *
	 * @return void
	 */
	public function clearCache(): void
	{
		$args = func_get_args();
		Cache::clear($args[0], $args[1]);

		echo 'Очистка кэша выполнена!'.PHP_EOL;
	}

	/**
	 * vphp cli.php create:sitemap
	 *
	 * @return void
	 */
	public function createSitemap(): void
	{
		if (!\Config::get('Sitemap.ACTIVE', false)) {
			echo 'Генерация Sitemap выключена параметром ACTIVE!'.PHP_EOL;
		} else {
			\App::core('Sitemap')
				->active(\Config::get('Sitemap.ACTIVE', false))
				->site(\Config::get('Sitemap.SITE_ID'))
				->name(\Config::get('Sitemap.NAME', ''))
				->domain(\Config::get('Sitemap.DOMAIN'))
				->protocol(\Config::get('Sitemap.PROTOCOL', 'http'))
				->mode(\Config::get('Sitemap.MODE', 'bitrix'))
				->compression(\Config::get('Sitemap.COMPRESSION', false))
				->bitrixSitemapId(\Config::get('Sitemap.SITEMAP_ID'))
				->sitemapPath(\Config::get('Sitemap.SITEMAP_PATH', '/'))
				->maxUrlsPerSitemap(\Config::get('Sitemap.MAX_URLS_PER_SITEMAP'))
				->models(\Config::get('Sitemap.MODELS', []))
				->urls(\Config::get('Sitemap.URLS', []))
				->bitrix(\Config::get('Sitemap.BITRIX', []))
				->create();

			echo 'Sitemap сгенерирован!'.PHP_EOL;
		}
	}

}
