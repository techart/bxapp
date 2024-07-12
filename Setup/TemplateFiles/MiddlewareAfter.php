<?php
namespace Site\Middleware\After;

class {{middleware_name}}
{
	/**
	 * Данный метод вызывается при использовании middleware
	 *
	 * @param mixed $data
	 * @return void
	 */
	function handle($data): mixed
	{
		// В $data приходят данные возвращаемые акшеном контроллера роута
		// Эти данные можно модифицировать, например:
		// $data['ModifyData'] = 'ModifyData';

		return $data;
	}
}
