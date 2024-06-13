<?php
namespace Site\Cli;

/**
 * Пример вызова метода класса через консоль (в папке php_interface):
 *
 * vphp cli.php MyCliClass_myMethod param1 param2
 */


class MyCliClass
{
	public function myMethod()
	{
		$params = func_get_args();

		dd($params[0], $params[1]);
	}
}
