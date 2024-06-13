<?php
namespace Routes\{{BundleName}}\Controllers;

class Actions
{
	public function method()
	{
		// $this->args
		$data = [];

		return $this->return($this->result('', '', $data));
	}
}
