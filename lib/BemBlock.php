<?php
namespace Techart\BxApp;


class BemBlock
{
	private $bladeBlockName = '';
	private $blockName = '';


	public function __construct(string $blockName = '')
	{
		$this->blockName = 'b-'.$blockName;
	}


	public function __toString()
	{
		return $this->blockName;
	}


	public function elem(string $elemName = ''): object
	{
		return new BemElem($this->blockName, $elemName);
	}
}
