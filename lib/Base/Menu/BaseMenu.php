<?php
namespace Techart\BxApp\Base\Menu;

/**
 * Базовый класс для построения меню
 * Класс для построения меню из Infoblock'a / HighLoadBlock'a Bitrix
 *
 * Умеет строить меню -
 * - От элементов infoblock'a
 * - От элементов highloadblock'a
 * - От секций infoblock'a + элементы, в иерархическом порядке
 *
 * При построении меню от секций инфоблока, если не нунжны элементы, то необходимо указать $allowElements = false
 *
 * Содержит 1 абстрактный метод executeBuildMenu(), который должен быть обязательно
 * В котором обрабатываются данные пришедшие из модели, наследованной от BaseIblockModel
 *
 * Особое внимание требуется обратить на выставление selected = true (активная страница, там где находится пользователь)
 * Функция выставления selected = true передаётся атрибутом в функцию модели по получению элементов раздела ( getIBlockElements )
 * при составлении элемента необходимо указать это - / 'selected' => $callback($item['CODE']), /
 *
 * Не забываем про использование кэш'а
 *
 */


abstract class BaseMenu
{
	use CacheTrait;
	use ResultTrait;

	public $className = '';
	protected $allowElements = true;
	protected $data = null;

	/**
	 * Проходит по массиву, сравнивая url с полем code
	 * Выставляет selected true при соответствии поля link с текущим url
	 *
	 * @param string $code
	 * @return array
	 */
	protected function setSelectedMenuElement($code)
	{
		if(!empty($code)) {
			$expUrl = $_SERVER['REQUEST_URI'];
			$result = str_contains($expUrl, $code);

			return $result;
		} else {
			return false;
		}
	}

	/**
	 * Возвращает меню построенное на элементах HighLoadBlock'a
	 * Принимает в себя массив элементов
	 *
	 * @return array
	 */
	protected function buildMenuByHLBlockElems()
	{
		$this->data = \App::model($this->className)->getElementsData()['data']['items'];

		foreach ($this->data as $item) {
			$data[] = [
				'link' => $item['UF_LINK'],
				'text' => $item['UF_TEXT'],
				'code' => $item['UF_CODE'],
				'selected' => $this->setSelectedMenuElement($item['UF_CODE']),
			];
		}

		return $data;
	}

	/**
	 * Возвращает массив иерархически построенного дерева из секций и элементов инфоблока
	 * Рекурсивный метод ( метод тяжёлый, по возможности использоваться линейный linearBuildTree )
	 * Принимает в себя массив секций
	 * Обратить внимание на $this->setSelectedMenuElement($code)
	 *
	 * @param array $pID
	 * @return array
	 */
	protected function recursionBuildTree($pID = null)
	{
		$branch = [];

		foreach ($this->data as $section) {
			if($this->allowElements) {
				$selected = $this->setSelectedMenuElement($section['CODE']);
			}

			$newSection = [
				'link' => $section['CODE'],
				'title' => $section['NAME'],
				'text' => $section['DESCRIPTION'],
				'code' => $section['CODE'],
				'selected' => $selected,
				'level' => $section['DEPTH_LEVEL'],
				'IBLOCK_SECTION_ID' => $section['IBLOCK_SECTION_ID'],
				'ID' => $section['ID'],
				'sub' => $this->allowElements ? \App::model($this->className)->getIBlockElements(
					$section['ID'],
					function($code = '') {
						return $this->setSelectedMenuElement($code);
					})['data'] : '',
			];

			if ($newSection['IBLOCK_SECTION_ID'] === $pID) {
				$child = $this->recursionBuildTree($newSection['ID']);
				if ($child) {
					$newSection['child'] = $child;
				}

				$branch[$newSection['ID']] = $newSection;
			}
		}

		return $branch;
	}

	/**
	 * Возвращает массив иерархически построенного дерева из секций и элементов инфоблока
	 * Линейный метод ( метод намного легче рукурсивного )
	 * Принимает в себя массив секций
	 * Обратить внимание на $this->setSelectedMenuElement($code)
	 *
	 * @return array
	 */
	protected function linearBuildTree()
	{
		$branch = [];
		$tree = [];

		foreach ($this->data as $section) {
			if($this->allowElements) {
				$selected = $this->setSelectedMenuElement($section['CODE']);
			}

			$newSection = [
				'link' => $section['CODE'],
				'title' => $section['NAME'],
				'text' => $section['DESCRIPTION'],
				'code' => $section['CODE'],
				'selected' => $selected,
				'level' => $section['DEPTH_LEVEL'],
				'IBLOCK_SECTION_ID' => $section['IBLOCK_SECTION_ID'],
				'ID' => $section['ID'],
				'sub' => $this->allowElements ? \App::model($this->className)->getIBlockElements(
					$section['ID'],
					function($code = '') {
						return $this->setSelectedMenuElement($code);
					})['data'] : '',
			];

			$tree[$section['ID']] = $newSection;
			$tree[$section['ID']]['child'] = [];
		}

		foreach ($tree as &$node) {
			if ($node['IBLOCK_SECTION_ID'] == null) {
				$branch[] = &$node;
			} else {
				$parent = &$tree[$node['IBLOCK_SECTION_ID']];
				$parent['child'][] = &$node;
			}
		}

		return $branch;
	}

	/**
	 * Возвращает массив секций и элементов инфоблока
	 * Вызывает метод у наследованного класса BaseIblockModel
	 * Требует указания $className -> Название класса\файла вашей модели
	 *
	 * @return array
	 */
	protected function buildMenuByIblockSections()
	{
		$this->data = \App::model($this->className)->getElementsData()['data'];

		// $this->data = $this->recursionBuildTree();
		$this->data = $this->linearBuildTree();

		return $this->data;
	}

	/**
	 * Возвращает массив всех элементов инфоблока, приведённый к общему виду
	 * Для вывода элементов n-уровня, указать в фильтре в модели необходимый уровень
	 *
	 * @return array
	 */
	protected function buildMenuByIBlockElems()
	{
		$this->data = \App::model($this->className)->getElementsData()['data']['items'];

		foreach ($this->data as $item) {
			$data[] = [
				'link' => $item['PROPERTY_LINK_VALUE'],
				'text' => $item['NAME'],
				'code' => $item['CODE'],
				'selected' => $this->setSelectedMenuElement($item['CODE']),
			];
		}

		return $data;
	}

	/**
	 * Абстрактный метод, где будет ваша логика
	 *
	 * @return array
	 */
	abstract protected function executeBuildMenu();
}
