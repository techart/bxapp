<?php
namespace Routes\BxappDefault\Controllers;

class Actions extends \BaseRoutesController
{
	public function logger()
	{
		$props = $this->getValues();

		// тут договориться и в $props сделать массив с типами ошибок и обрабатывать их тут

		\Logger::add('frontendError', $props, 'frontendError');

		return $this->result('', '', []);
	}
}
