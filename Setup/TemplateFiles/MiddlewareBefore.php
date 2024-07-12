<?php
namespace Site\Middleware\Before;

class {{middleware_name}}
{
	/**
	 * Данный метод вызывается при использовании middleware
	 *
	 * @return void
	 */
	function handle(): void
	{
		// Мидлваер before - ничего не принимает и ничего не возвращает
		// Можно модифицировать данные текущего роута с помощью:
		// $data = \App::getRoute();
		// $data['test'] = 'werewrewrewr';
		// \App::setRoute($data);
	}
}
