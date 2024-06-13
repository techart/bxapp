<?php
namespace Techart\BxApp;


class CliActions
{
	public function setup()
	{
		AppSetup::setup();

		echo 'Создание файлов завершено!'.PHP_EOL;
	}

	public function setupTemplate()
	{
		AppSetup::setupTemplate();

		echo 'Создание файлов завершено!'.PHP_EOL;
	}

	public function createModel()
	{
		AppSetup::createModel(func_get_args());

		echo 'Создание модели завершено!'.PHP_EOL;
	}

	public function createCli()
	{
		AppSetup::createCli(func_get_args());

		echo 'Создание Cli класса завершено!'.PHP_EOL;
	}

	public function createBundle()
	{
		AppSetup::createBundle(func_get_args());

		echo 'Создание бандла завершено!'.PHP_EOL;
	}
}
