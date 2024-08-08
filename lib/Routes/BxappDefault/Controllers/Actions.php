<?php
namespace Routes\BxappDefault\Controllers;

class Actions extends \BaseRoutesController
{
	public function logger()
	{
		$props = $this->getValues();

		\Logger::frontendError($props);

		return $this->result('', '', []);
	}
}
