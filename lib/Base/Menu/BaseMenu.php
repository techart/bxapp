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
	protected $depth = null;
	protected $curFlag = '';
	protected $tree = [];
	protected $pid = 0;

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
	 * @param array $comparable
	 * @return bool
	 */
	protected function setSelectedMenuElement(string $code, mixed $pid = null, array $comparable = []): bool
	{
		$result = false;

		if(!empty($code)) {
			if (!empty($comparable)) {
				if (!empty($comparable['flag']) && !empty($this->curFlag)) {
					$result = in_array($this->curFlag, $comparable['flag']);
				}

				if (!$result && !empty($comparable['match'])) {
					if (mb_substr($comparable['match'], mb_strlen($comparable['match']) - 1) === '*') {
						$startUrl = mb_substr($comparable['match'], 0, mb_strlen($comparable['match']) - 1);
						$result = strpos($_SERVER['REQUEST_URI'], $startUrl) === 0;
					}
				}
			}

			if (!$result) {
				$expUrl = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
				$result = $code === $expUrl;
			}

			if ($result) {
				$this->pid = $pid;
			}
		}

		return $result;
	}

	/**
	 * Возвращает массив иерархически построенного дерева из секций и элементов инфоблока
	 * Рекурсивный метод ( метод тяжёлый, по возможности использоваться линейный linearBuildTree )
	 * Принимает в себя массив секций
	 * Обратить внимание на $this->setSelectedMenuElement($code)
	 *
	 * @param string $ibModelName
	 * @param array $pID
	 * @return array
	 */
	protected function recursionBuildTree(string $ibModelName = '', $pID = null): array
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
					function($code = '') {
						return $this->setSelectedMenuElement($code);
					})['data'] : '',
					[
						'id' => $section['ID'],
					]
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
	 * Устанавливает selected = true для всех верхних пунктов меню, если активен один из внутренних элементов этих пунктов
	 *
	 * @param string|int $pid
	 * @return void
	 */
	public function setSelectedAll(string|int $pid = 0): void {
		if ($pid > 0) {
			if (isset($this->tree[$pid])) {
				$this->tree[$pid]['selected'] = true;

				if (!empty($this->tree[$pid]['IBLOCK_SECTION_ID']) && intval($this->tree[$pid]['level']) > 1) {
					$this->setSelectedAll($this->tree[$pid]['IBLOCK_SECTION_ID']);
				}
			}
		}
	}

	/**
	 * Возвращает массив иерархически построенного дерева из секций и элементов инфоблока
	 * Линейный метод ( метод намного легче рукурсивного )
	 * Принимает в себя массив секций
	 * Обратить внимание на $this->setSelectedMenuElement($code)
	 *
	 * @param string $ibModelName
	 * @return array
	 */
	public function linearBuildTree(string $ibModelName = ''): array
	{
		$branch = [];

		foreach ($this->data as $section) {
			$currentModel = '';
			if ($section['selected'] === true) {
				$selected = $section['selected'];
			} else {
				$selected = $this->setSelectedMenuElement($section['SECTION_PAGE_URL'] ?? $section['link'] ?? $section['code'], $section['IBLOCK_SECTION_ID'] ?? '', ['flag' => $section['flag'], 'match' => $section['match']]);
			}

			if ($this->allowElements && !empty($section['IBLOCK_CODE'])) {
				foreach (\App::instances('models') as $model) {
					if ($section['IBLOCK_CODE'] === $model->table) {
						$currentModel = get_class($model);
					}
				}
			}

			$newSection = [
				'link' => $section['SECTION_PAGE_URL'] ?? $section['link'],
				'title' => $section['NAME'] ?? $section['title'],
				'text' => $section['DESCRIPTION'] ?? $section['text'] ?? '',
				'code' => $this->genCode($section['CODE'] ?? $section['code'] ?? $section['link']),
				'selected' => $selected,
				'level' => $section['DEPTH_LEVEL'] ?? $section['level'],
				'IBLOCK_SECTION_ID' => $section['IBLOCK_SECTION_ID'],
				'ID' => $section['ID'],
				'elements' => $this->allowElements && !empty($section['IBLOCK_CODE']) ? $this->getIBlockElements(
					!empty($ibModelName) ? $ibModelName : $currentModel,
					function($code = '', $sectionId = '', $comp = []) {
						return $this->setSelectedMenuElement($code, $sectionId ?? '');
					}) : '',
					[
						'id' => $section['ID'],
					]
			];

			$this->tree[$section['ID']] = $newSection;
			$this->tree[$section['ID']]['sub'] = [];
		}

		$this->setSelectedAll($this->pid);

		foreach ($this->tree as &$node) {
			if ($node['IBLOCK_SECTION_ID'] == null) {
				$branch[] = &$node;
			} else {
				$parent = &$this->tree[$node['IBLOCK_SECTION_ID']];
				$parent['sub'][] = &$node;
			}
		}

		return $branch;
	}

	/**
	 * Получение элементов хайлоадблока для меню
	 *
	 * @param string $hbModelName
	 * @param object $callback
	 * @return array
	*/
	protected function getHbElements(string $hbModelName, object $callback, array $additionalProps = []): array
	{
		$data = [];

		$items = \App::model($hbModelName)->getElements(
			array_merge(['UF_LINK', 'UF_TEXT', 'UF_TITLE', 'UF_CODE', 'ID'], array_values($additionalProps))
		);
		while ($item = $items->fetch()) {
			$newData = [
				'ID' => $hbModelName . $item['ID'],
				'link' => $item['UF_LINK'],
				'title' => $item['UF_TITLE'],
				'text' => $item['UF_TEXT'],
				'code' => $item['UF_CODE'],
				'selected' => $callback($item['UF_LINK']),
			];

			foreach($additionalProps as $key => $prop) {
				$newData[$key] = $item[$prop];
			}

			$data[] = $newData;
		}

		return $data;
	}

	/**
	 * Получение элементов инфоблока для меню
	 *
	 * @param string $ibModelName
	 * @param string|int $id
	 * @param object $callback
	 * @return array
	 */
	public function getIBlockElements(string $ibModelName, object $callback, array $additionalProps = []): array
	{
		$data = [];
		$select = ['PROPERTY_LINK', 'NAME', 'CODE', 'IBLOCK_SECTION_ID', 'DETAIL_PAGE_URL'];

		if (isset($additionalProps['addElemProps']) && is_array($additionalProps['addElemProps'])) {
			$select = array_merge($select, array_values($additionalProps['addElemProps']));
		}

		$items = \App::model($ibModelName)->getElements($select, ['IBLOCK_SECTION_ID' => $additionalProps['id'], 'ACTIVE' => 'Y'], ['LEFT_MARGIN' => 'ASC']);
		while ($item = $items->GetNext()) {

			$newData = [
				'link' => $item['DETAIL_PAGE_URL'],
				'text' => $item['NAME'],
				'code' => $item['CODE'],
				'sectionId' => $item['IBLOCK_SECTION_ID'],
				'selected' => $callback($item['DETAIL_PAGE_URL'], $item['IBLOCK_SECTION_ID']),
			];

			foreach($additionalProps['addElemProps'] as $key => $prop) {
				if (strpos($prop, 'PROPERTY_') === false) {
					$newData[$key] = $item[$prop];
				} else {
					$newData[$key] = $item[$prop.'_VALUE'];
				}
			}

			$data[] = $newData;
		}

		return $data;
	}

	/**
	 * Получение секций инфоблока для меню
	 *
	 * @param string $ibModelName
	 * @param array $additionalProps
	 * @param int $offset
	 * @return array
	 */
	protected function getIBlockSections(string $ibModelName, array $additionalProps = [], int $offset = 0): array
	{
		$data = [];
		$select = array_merge(['ID', 'CODE', 'NAME', 'DESCRIPTION', 'DEPTH_LEVEL', 'IBLOCK_SECTION_ID', 'SECTION_PAGE_URL', 'IBLOCK_CODE'], array_values($additionalProps));

		$items = \App::model($ibModelName)->getSections(
			$select,
			['<=DEPTH_LEVEL' => ($this->depth ? $this->depth + $offset : $this->depth), 'ACTIVE' => 'Y'],
			['LEFT_MARGIN' => 'ASC']
		);

		while ($item = $items->GetNext()) {
			$newData = $item;

			foreach($additionalProps as $key => $prop) {
				$newData[$key] = $item[$prop];
			}

			$data[] = $newData;
		}

		return $data;
	}

	/**
	 * Возвращает меню построенное на элементах HighLoadBlock'a
	 * Принимает в себя массив элементов
	 *
	 * @param string $hbModelName
	 * @return array
	 */
	protected function buildMenuByHLBlockElems(string $hbModelName = '', array $props = []): array
	{
		$curModelName = !empty($hbModelName) ? $hbModelName : $this->modelName;
		$this->data = $this->getHbElements(
			$curModelName,
			function($code = '', $pid = null) {
				return $this->setSelectedMenuElement($code, $pid);
			},
			$props['addElemProps'] ?? []
		);
		return $this->data;
	}

	/**
	 * Возвращает массив секций и элементов инфоблока
	 * Вызывает метод у наследованного класса BaseIblockModel
	 *
	 * @param string $ibModelName
	 * @param bool $allowElements
	 * @return array
	 */
	protected function buildMenuByIBlockSections(string $ibModelName = '', array $props = []): array
	{
		$curModelName = !empty($ibModelName) ? $ibModelName : $this->modelName;
		$this->data = $this->getIBlockSections($curModelName, !empty($props['addSectionProps']) ? $props['addSectionProps'] : []);
		$this->data = $this->linearBuildTree($curModelName);
		// $this->data = $this->recursionBuildTree();

		return $this->data;
	}

	/**
	 * Возвращает массив всех элементов инфоблока, приведённый к общему виду
	 * Для вывода элементов n-уровня, указать в фильтре в модели необходимый уровень
	 *
	 * @param string $ibModelName
	 * @param string|int $id
	 * @return array
	 */
	protected function buildMenuByIBlockElems(string $ibModelName = '', array $props = []): array
	{
		$curModelName = !empty($ibModelName) ? $ibModelName : $this->modelName;
		$this->data = $this->getIBlockElements(
			$curModelName,
			function($code = '', $pid = null) {
				return $this->setSelectedMenuElement($code, $pid);
			},
			$props
		);

		$this->data = $this->linearBuildTree($curModelName);
		return $this->data;
	}

	/**
	 * Устанавливает глубину добавления разделов в меню
	 *
	 * @param int $depth
	 * @return object
	 */
	public function depth(int $depth): object
	{
		$this->depth = $depth;

		return $this;
	}

	/**
	 * Включает добавление элементов инфоблоков в меню
	 *
	 * @param bool $allowElements
	 * @return object
	 */
	public function allowElements(bool $allowElements = false): object
	{
		$this->allowElements = $allowElements;

		return $this;
	}

	/**
	 * Устанавливает флаг
	 *
	 * @param string $flag
	 * @return void
	 */
	public function setFlag(string $flag = ''): void
	{
		$this->curFlag = $flag;
	}

	/**
	 * Генерирует код пункта меню
	 *
	 * @param string $code
	 * @return string
	 */
	public function genCode(string $code = ''): string
	{
		$code = str_replace('/', '', trim(\H::removeBOM(strval($code))));
		$code = \Cutil::translit(
			$code,
			'ru',
			['max_len' => 25, 'change_case' => 'L', 'replace_space' => '-']
		).'_'.rand(1000000, 10000000);

		return $code;
	}

	/**
	 * Формирует меню по вручную заданному массиву
	 *
	 * @param array $menuArray
	 * @return array
	 */
	protected function buildMenuByArray(array $menuArray = []): array
	{
		$data = [];
		$level = "1";

		foreach($menuArray as $pid => $item) {
			$selectParams = [];
			$item['flag'] && $selectParams['flag'] = $item['flag'];
			$item['match'] && $selectParams['match'] = $item['match'];
			$data['c' . $pid] = [
				'link' => $item['link'],
				'title' => $item['title'],
				'code' => $item['code'],
				'selected' => $this->setSelectedMenuElement($item['link'], $pid, $selectParams),
				'level' => $level,
				'ID' => 'c' . $pid,
			];

			if (!empty($item['sub'])) {
				preg_match('/(.+):(.+)/', $item['sub']['data'], $matches);

				if ($matches[1] === 'highloadblock' && !empty($matches[2])) {
					$elems = $this->getHbElements(
						$matches[2],
						function($code = '', $pid = null) {
							return $this->setSelectedMenuElement($code, $pid);
						},
						$item['sub']['addElemProps']
					);

					$this->pid = 'c' . $pid;

					foreach($elems as $element) {
						if ($data['c' . $pid]['level'] + 1 <= $this->depth) {
							$data[$element['ID']] = $element;
							$data[$element['ID']]['IBLOCK_SECTION_ID'] = 'c' . $pid;
							$data[$element['ID']]['level'] = (string) ($data[$data[$element['ID']]['IBLOCK_SECTION_ID']]['level'] + 1);
						}
					}
				}

				if ($matches[1] === 'infoblock' && !empty($matches[2])) {
					$elems = $this->getIBlockSections(
						$matches[2],
						!empty($item['sub']['addSectionProps']) ? $item['sub']['addSectionProps'] : [],
						(int) $data['c' . $pid]['level']
					);

					foreach($elems as $element) {
						if ($element['DEPTH_LEVEL'] + 1 <= $this->depth) {
							$data[$element['ID']] = $element;
							$data[$element['ID']]['DEPTH_LEVEL'] = (string) ($data[$element['ID']]['DEPTH_LEVEL'] + 1);
							$data[$element['ID']]['IBLOCK_SECTION_ID'] = $data[$element['ID']]['IBLOCK_SECTION_ID'] ?? 'c' . $pid;
						}
					}
				}
			}
		}

		$this->data = $data;
		$this->data = $this->linearBuildTree();

		return $this->data;
	}

	/**
	 * Метод, где будет ваша логика
	 *
	 * @return array
	 */
	abstract function build();
}
