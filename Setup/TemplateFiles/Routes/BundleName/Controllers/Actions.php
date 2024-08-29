<?php
namespace Routes\{{BundleName}}\Controllers;

class Actions extends \BaseRoutesController
{
	public function method()
	{
		// $this->args
		// $props = $this->getValues();
		$data = [];

		return $this->result('', '', $data);
	}
}
