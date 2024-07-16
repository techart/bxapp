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

	/**
	 * @param string $name - имя модификатора
	 * @param mixed $value = true - значение модификатора (опционально)
	 * @return $this
	 */
	public function mod($name, $value = true): object
	{
		$be = new BemElem($this->blockName, '');
		return $be->mod($name, $value);
	}

	public function elem(string $elemName = ''): object
	{
		return new BemElem($this->blockName, $elemName);
	}
}
