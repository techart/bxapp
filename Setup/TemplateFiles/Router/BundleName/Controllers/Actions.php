<?php
namespace Router\{{BundleName}}\Controllers;

class Actions extends \BaseRouterController
{
	public function method()
	{
		// $this->args
		// $props = $this->getValues();
		// $curUrl = \Router::getRequestUri();
		$data = [];

		return $this->result('', '', $data);
	}
}
