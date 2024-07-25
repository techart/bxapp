<?php
namespace Techart\BxApp;


class Autoload
{
	protected static $aliases = [
		'App' => '\Techart\BxApp\App',
		'Env' => '\Techart\BxApp\Env',
		'Config' => '\Techart\BxApp\Config',
		'Glob' => '\Techart\BxApp\Glob',
		'BladeTemplate' => '\Techart\BxApp\BladeTemplate',
		'M' => '\Techart\BxApp\Message',
		'H' => '\Techart\BxApp\Helpers',
		'Log' => '\Techart\BxApp\Log',
		'Logger' => '\Techart\BxApp\Logger',

		'FormModelTrait' => '\Techart\BxApp\Traits\FormModelTrait',
		'ResultTrait' => '\Techart\BxApp\Traits\ResultTrait',
		'ErrorTrait' => '\Techart\BxApp\Traits\ErrorTrait',
		'CacheTrait' => '\Techart\BxApp\Traits\CacheTrait',
		'BuildResultTrait' => '\Techart\BxApp\Traits\BuildResultTrait',

		'BaseForm' => '\Techart\BxApp\Base\Form\BaseForm',
		'BaseIblockModel' => '\Techart\BxApp\Base\Model\BaseIblockModel',
		'BaseHighloadModel' => '\Techart\BxApp\Base\Model\BaseHighloadModel',
		'BaseRoutesController' => '\Techart\BxApp\Base\Routes\BaseRoutesController',
		'BaseMenu' => '\Techart\BxApp\Base\Menu\BaseMenu',
		//'BaseSeo' => '\Techart\BxApp\Base\Seo\BaseSeo',
	];


	public static function register(): void
	{
		spl_autoload_register(function($className) {
			if (class_exists('Techart\BxApp\Autoload')) {
				Autoload::Run($className);
			}
		}, true, true);
	}

	public static function Run(string $className = '')
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
