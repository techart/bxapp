<?php
namespace Techart\BxApp;

/**
 * TODO: переделать переменную block на класс new Block, как в ТАО
 */

class Frontend
{
	private $bladeBlockName = '';
	private $blockName = '';


	private function bladeBlockDefaultVars()
	{
		return [
			'block' => App::frontend()->block($this->bladeBlockName),
			'renderer' => new self(),
		];
	}

	public function renderBlock(string $block = '', array $vars = [])
	{
		$blockPath = explode('/', trim($block, '/'));
		$this->bladeBlockName = end($blockPath);
		$block .= '/'.$this->bladeBlockName.'.blade.php';
		$vars = array_merge($vars, $this->bladeBlockDefaultVars());

		return \BladeTemplate::getBlade()->run($block, $vars);
	}

	public function block(string $blockName = '')
	{
		return new BemBlock($blockName);
	}
}
