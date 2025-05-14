<?php
namespace Site\Middleware\Before;

class ChangeHeaders
{
	/**
	 * Данный метод вызывается при использовании middleware
	 *
	 * @param mixed $data
	 * @return void
	 */
	function handle()
	{
		// Мидлваер before - ничего не принимает и ничего не возвращает
		// Можно модифицировать данные текущего роута с помощью:
		// $data = \App::getRoute();
		// $data['test'] = 'werewrewrewr';
		// \App::setRoute($data);
	}
}
