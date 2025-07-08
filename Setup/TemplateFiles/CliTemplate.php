<?php
namespace TechartBxApp\Cli{{cli_namespace}};

/**
 * Пример вызова метода класса через консоль (в папке php_interface):
 *
 * vphp cli.php MyCliClass_myMethod param1 param2
 */


class {{cli_name}}
{
	public function {{cli_method}}()
	{
		$params = func_get_args();

		dd($params[0], $params[1]);
	}
}
