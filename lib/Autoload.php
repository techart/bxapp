<?php
namespace Techart\BxApp;


class Autoload
{
	protected static $aliases = [];


	public static function register(): void
	{
		spl_autoload_register(function($className) {
			if (class_exists('Techart\BxApp\Autoload')) {
				Autoload::aliases();
				Autoload::run($className);
			}
		}, true, true);
	}

	public static function aliases()
	{
		$aliases = [
			'App' => '\Techart\BxApp\App',
			'Env' => '\Techart\BxApp\Env',
			'Config' => '\Techart\BxApp\Config',
			'Glob' => '\Techart\BxApp\Glob',
			'BladeTemplate' => '\Techart\BxApp\BladeTemplate',
			'Router' => '\Techart\BxApp\Router',
			'M' => '\Techart\BxApp\Message',
			'H' => '\Techart\BxApp\Helpers',
			'Help' => '\Techart\BxApp\HelpersCustom',
			'Log' => '\Techart\BxApp\Log',
			'Logger' => '\Techart\BxApp\Logger',
			'AppCache' => '\Techart\BxApp\Cache',

			'FormModelTrait' => '\Techart\BxApp\Traits\FormModelTrait',
			'ResultTrait' => '\Techart\BxApp\Traits\ResultTrait',
			'ErrorTrait' => '\Techart\BxApp\Traits\ErrorTrait',
			'CacheTrait' => '\Techart\BxApp\Traits\CacheTrait',
			'BuildResultTrait' => '\Techart\BxApp\Traits\BuildResultTrait',

			'BaseForm' => '\Techart\BxApp\Base\Form\BaseForm',
			'BaseIblockModel' => '\Techart\BxApp\Base\Model\BaseIblockModel',
			'BaseHighloadModel' => '\Techart\BxApp\Base\Model\BaseHighloadModel',
			'BaseRouterController' => '\Techart\BxApp\Base\Router\BaseRouterController',
			'BaseMenu' => '\Techart\BxApp\Base\Menu\BaseMenu',
			//'BaseSeo' => '\Techart\BxApp\Base\Seo\BaseSeo',
		];

		if (defined('TECHART_BX_APP_ALIASES')) {
			$aliases = array_merge($aliases, TECHART_BX_APP_ALIASES);
		}

		self::$aliases = $aliases;
	}

	public static function run(string $className = '')
	{
		if (!empty($className)) {
			if (isset(self::$aliases[$className]) and !empty(self::$aliases[$className])) {
				class_alias(self::$aliases[$className], $className);
				return;
			}
		} else {
			return false;
		}
	}
}
