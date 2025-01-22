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
	use \CacheTrait;

	protected $data = [];
	protected $allowElements = false;
	protected $modelName = '';


	public function __construct()
	{
		if (!empty($this->modelName)) {
			$this->table = \App::model($this->modelName)->table;
		} else {
			$this->table .= APP_CACHE_MENU_DIR_NAME.'/'.BXAPP_SITE_ID.'/'.get_called_class();
		}
	}

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
	 * Возвращает массив иерархически построенного дерева из секций и элементов инфоблока
	 * Рекурсивный метод ( метод тяжёлый, по возможности использоваться линейный linearBuildTree )
	 * Принимает в себя массив секций
	 * Обратить внимание на $this->setSelectedMenuElement($code)
	 *
	 * @param array $pID
	 * @return array
	 */
	protected function recursionBuildTree(string $ibModelName = '', $pID = null)
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
				'elements' => $this->allowElements ? $this->getIBlockElements(
					$ibModelName,
					$section['ID'],
					function($code = '') {
						return $this->setSelectedMenuElement($code);
					})['data'] : '',
			];

			if ($newSection['IBLOCK_SECTION_ID'] === $pID) {
				$child = $this->recursionBuildTree($newSection['ID']);
				if ($child) {
					$newSection['sub'] = $child;
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
	protected function linearBuildTree(string $ibModelName = '')
	{
		$branch = [];
		$tree = [];

		foreach ($this->data as $section) {
			$selected = $this->setSelectedMenuElement($section['CODE']);

			$newSection = [
				'link' => $section['CODE'],
				'title' => $section['NAME'],
				'text' => $section['DESCRIPTION'],
				'code' => $section['CODE'],
				'selected' => $selected,
				'level' => $section['DEPTH_LEVEL'],
				'IBLOCK_SECTION_ID' => $section['IBLOCK_SECTION_ID'],
				'ID' => $section['ID'],
				'elements' => $this->allowElements ? $this->getIBlockElements(
					$ibModelName,
					$section['ID'],
					function($code = '') {
						return $this->setSelectedMenuElement($code);
					}) : '',
			];

			$tree[$section['ID']] = $newSection;
			$tree[$section['ID']]['sub'] = [];
		}

		foreach ($tree as &$node) {
			if ($node['IBLOCK_SECTION_ID'] == null) {
				$branch[] = &$node;
			} else {
				$parent = &$tree[$node['IBLOCK_SECTION_ID']];
				$parent['sub'][] = &$node;
			}
		}

		return $branch;
	}

	protected function getHbElements(string $hbModelName, object $callback)
	{
		$data = [];

		$items = \App::model($hbModelName)->getElements(
			['UF_LINK', 'UF_TEXT', 'UF_CODE']
		);
		while ($item = $items->fetch()) {
			$data[] = [
				'link' => $item['UF_LINK'],
				'text' => $item['UF_TEXT'],
				'code' => $item['UF_CODE'],
				'selected' => $callback($item['CODE']),
			];
		}

		return $data;
	}

	protected function getIBlockElements(string $ibModelName, string|int $id = '', object $callback)
	{
		$data = [];

		$items = \App::model($ibModelName)->getElements(['PROPERTY_LINK', 'NAME', 'CODE', 'IBLOCK_SECTION_ID'], ['IBLOCK_SECTION_ID' => $id], ['LEFT_MARGIN' => 'ASC']);
		while ($item = $items->fetch()) {
			$data[] = [
				'link' => $item['CODE'],
				'text' => $item['NAME'],
				'code' => $item['CODE'],
				'sectionId' => $item['IBLOCK_SECTION_ID'],
				'selected' => $callback($item['CODE']),
			];
		}

		return $data;
	}

	protected function getIBlockSections(string $ibModelName)
	{
		$data = [];

		$items = \App::model($ibModelName)->getSections(
			['ID', 'CODE', 'NAME', 'DESCRIPTION', 'DEPTH_LEVEL', 'IBLOCK_SECTION_ID'],
			[],
			['LEFT_MARGIN' => 'ASC']
		);

		while ($item = $items->fetch()) {
			$data[] = $item;
		}

		return $data;
	}

	/**
	 * Возвращает меню построенное на элементах HighLoadBlock'a
	 * Принимает в себя массив элементов
	 *
	 * @return array
	 */
	protected function buildMenuByHLBlockElems(string $hbModelName = ''): array
	{
		$curModelName = !empty($hbModelName) ? $hbModelName : $this->modelName;
		$this->data = $this->getHbElements(
			$curModelName,
			function($code = '') {
				return $this->setSelectedMenuElement($code);
			}
		);

		return $this->data;
	}

	/**
	 * Возвращает массив секций и элементов инфоблока
	 * Вызывает метод у наследованного класса BaseIblockModel
	 *
	 * @return array
	 */
	protected function buildMenuByIBlockSections(string $ibModelName = '', bool $allowElements = false): array
	{
		$curModelName = !empty($ibModelName) ? $ibModelName : $this->modelName;
		$this->allowElements = $allowElements;
		$this->data = $this->getIBlockSections($curModelName);
		$this->data = $this->linearBuildTree($curModelName);
		// $this->data = $this->recursionBuildTree();

		return $this->data;
	}

	/**
	 * Возвращает массив всех элементов инфоблока, приведённый к общему виду
	 * Для вывода элементов n-уровня, указать в фильтре в модели необходимый уровень
	 *
	 * @return array
	 */
	protected function buildMenuByIBlockElems(string $ibModelName = '', string|int $id = ''): array
	{
		$curModelName = !empty($ibModelName) ? $ibModelName : $this->modelName;
		$this->data = $this->getIBlockElements(
			$curModelName,
			$id,
			function($code = '') {
				return $this->setSelectedMenuElement($code);
			}
		);

		return $this->data;
	}

	/**
	 * Метод, где будет ваша логика
	 *
	 * @return array
	 */
	abstract function build();
}
