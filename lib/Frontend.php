<?php
namespace Techart\BxApp;

/**
 * Класс для работы с фронтендом.
 */

class Frontend
{
	private $bladeBlockName = '';
	private $blockName = '';


	/**
	 * Возвращает массив с предустановленными переменными для передачи во фронтенд блок:
	 * block - инстанс BemBlock
	 * renderer - инстанс текущего класса
	 *
	 * @return void
	 */
	private function bladeBlockDefaultVars(): array
	{
		return [
			'block' => App::frontend()->block($this->bladeBlockName),
			'renderer' => new self(),
		];
	}

	/**
	 * Рендерит фронтенд блок $block с переданными переменными $vars
	 *
	 * @param string $block
	 * @param array $vars
	 * @return string
	 */
	public function renderBlock(string $block = '', array $vars = []): string
	{
		$blockPath = explode('/', trim($block, '/'));
		$this->bladeBlockName = end($blockPath);
		$block .= '/'.$this->bladeBlockName.'.blade.php';
		$vars = array_merge($vars, $this->bladeBlockDefaultVars());

		return \BladeTemplate::getBlade()->run($block, $vars);
	}

	/**
	 * Возвращает инстанс BemBlock.
	 * Для формирования классов блока по BEM.
	 *
	 * @param string $blockName
	 * @return object
	 */
	public function block(string $blockName = ''): object
	{
		return new BemBlock($blockName);
	}
}
