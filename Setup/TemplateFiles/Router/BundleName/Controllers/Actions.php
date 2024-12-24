<?php
namespace Router\{{BundleName}}\Controllers;

class Actions extends \BaseRouterController
{
	public function method()
	{
		// $this->args
		// $props = $this->getValues();
		$data = [];

		return $this->result('', '', $data);
	}
}
