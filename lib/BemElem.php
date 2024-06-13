<?php
namespace Techart\BxApp;


class BemElem
{
	const MOD_DIVIDER = '--';
	const VALUE_DIVIDER = '_';
	private $blockName = '';
	private $elemName = '';
	private $mods = [];


	public function __construct(string $blockName = '', string $elemName = '')
	{
		$this->blockName = $blockName;
		$this->elemName = $elemName;
	}


	public function __toString()
	{
		$bemSelector = $this->blockName.self::VALUE_DIVIDER.$this->elemName;

		foreach ($this->mods as $modName => $modValue) {
			$mod = null;
			if ($modValue === false) {
				$mod = '';
			} else if ($modValue === true || $modValue === '' || is_null($modValue)) {
				$mod = $modName;
			} else {
				$mod = $modName . self::VALUE_DIVIDER . $modValue;
			}

			if ($mod !== '') {
				$bemSelector .= " {$bemSelector}" . self::MOD_DIVIDER . $mod;
			}
		}

		return $bemSelector;
	}

	/**
	 * @param string $name - имя модификатора
	 * @param mixed $value = true - значение модификатора (опционально)
	 * @return $this
	 */
	public function mod($name, $value = true)
	{
		if (empty($name)) {
			return $this;
		}

		if (!is_array($name)) {
			if (preg_match('/[\s]+/', $name)) {
				$name = preg_split('/[\s,]+/', $name);
			}
		}

		if (is_array($name)) {
			if ($this->is_assoc($name)) {
				foreach ($name as $modName => $modValue) {
					$this->mod($modName, $modValue);
				}
			} else {
				foreach ($name as $modName) {
					$this->mod($modName);
				}
			}
		} else if (strpos($name, self::VALUE_DIVIDER) !== false) {
			$exploded = explode(self::VALUE_DIVIDER, $name);
			$this->mod($exploded[0], $exploded[1]);
		} else {
			$this->mods[$name] = $value;
		}

		return $this;
	}

	protected function is_assoc($arr) {
		if (!is_array($arr) || array() === $arr) {
			return false;
		}

		return array_keys($arr) !== range(0, count($arr) - 1);
	}
}
