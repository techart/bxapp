<?php
namespace Routes\{{BundleName}}\Controllers;

class Actions
{
	public function method()
	{
		// $this->args
		// $props = $this->getValues();
		$data = [];

		return $this->return($this->result('', '', $data));
	}
}
