<?php
namespace Techart\BxApp\Base\Model;

/**
 * Базовый класс моделей для ИНФОБЛОКОВ.
 *
 * Нужно от него наследовать классы своих моделей инфоблоков.
 * В идеале называть файл модели и класс так же, как code инфоблока, но только в CamelCase.
 * Обязательно нужно указать код инфоблока в переменной $table.
 *
 * Можно указать стандартный набор полей при выборки секций и элементов
 * через переменные:
 * $iblockSectionsSelect, $iblockSectionsSelectForLocalization, $iblockElementsSelect, $iblockElementsSelectForLocalization
 *
 * Содержит 5 публичных стандартных метода: getInfoblock, getSections, getSection, getElements, getElement, getSeoFields
 * для получения соответствующей информации из инфоблока.
 *
 * Содержит 4 публичных метода для изменения данных элементов инфоблока: delete, add, update, setPropertyValuesEx
 *
 * Содержит 3 публичных метода для изменения данных секций инфоблока: deleteSection, addSection, updateSection
 *
 * setPropertyValuesEx - для элемента $id обновляет значения пропсов $propertyValues.
 * Может содержать неполный список пропсов. Не указанным пропсам не будет чиститься значение.
 *
 * Содержит 4 пустых метода для личного творчества: getSectionsData, getSectionData, getElementsData, и getElementData.
 *
 * Метод getElementsData, в котором надо описать обработку данных пришедших из $this->getElements.
 * Чтобы сформировать готовый массив для передачи во фронтенд блок.
 *
 * А так же методы для кэширования. Об этом в конце.
 *
 * Если планируется разводящая страница по секциям, то надо по аналогии описать метод getSectionsData для передачи во
 * фронтенд блок отрисовки секций. Если планируется страница элемента, то аналогично методы getElement и getElementData.
 * Они нужны далеко не во всех инфоблоках, но если они понадобятся, то придерживаться таких имён для удобства.
 *
 * Если данные выбираются не через getElements, а приходят извне. Например, стандартный компонент битрикса news и
 * там уже есть выборка элементов в $arResult. То просто передаём этот массив в объявленную переменную у
 * getElementsData и разбираем уже его.
 *
 * Предполагается, что зарезервированные методы используются для базовых задач:
 * getSections (getSectionsData) - для разводящей страницы инфоблока (.section.list)
 * getElements (getElementsData) - для страницы секции (.section)
 * getElement (getElementData) - для страницы элемента (.element)
 *
 *
 * Имеются встроенные методы кэширования. В большинстве - это обёртки для стандартного кэша битрикса.
 * Только подставляются параметры:
 *
 * id - это строка состоящая из имени класса модели и метода, где вызывается функция кэша.
 * dir - это table
 *
 * Кэш не работает, если APP_SETUP_CACHE_TRAIT_USE_CACHE = false или перменная CacheTraitUseCache = false
 * или если сайт запускается локально (intranet).
 * На эвенте изменения инфоблока запускается очистка кэша для всей модели.
 *
 *
 * Если нужно что-то дополнительно, то пишем свой метод в модели и называем понятно.
 *
 * При этом return данных для API или для понятного обмена надо обязательно делать через метод result() или buildResult
 * Соответствующие трейты уже подключены.
 * Это нужно для форматирования результатов по одному принципу.
 *
 * Например, для вывода особых новостей на главной:
 *
 * public function getMainElementsData(): array
	{
		$data = [];

		if ($this->hasCache()) {
			$data = $this->getCache();
		} elseif ($this->startCache()) {
			$items = $this->getElements([], ['PROPERTY_MAIN_VALUE' => 'Y']);

			while ($item = $items->fetch()) {
				$data[] = [
					'id' => $item['ID'],
					'name' => $item['NAME'],
				];
			}

			$this->endCache($data);
		}
		return $this->buildResult(false, 'Данные получены', $data);
	}

 * Если у метода предполагается динамическая выборка. То по умолчанию кэш будет с одинаковым ИД.
 * Чтобы этого не было нужно в таком случае составить ID самому и передать в функции кэша и result.
 * Можно воспользоваться уже готовым методом:
 *
 * $cacheID = $this->buildCacheID($params);
 *
 * А можно что-то и самому составить. Например:
 *
 * $cacheID = __CLASS__.'_'.__FUNCTION__.'_'.md5(json_encode($params));
 *
 * 		if ($this->hasCache($cacheID)) {
			$data = $this->getCache($cacheID);
		} elseif ($this->startCache($cacheID)) {
			$this->endCache($data, $cacheID);
		}
		return $this->result($data, [], $cacheID); !!!!!!!!!!
 *
 *
 * ______ЛОКАЛИЗАЦИЯ:
 *
 * При вызове модели третьим параметром можно передать требуемый язык
 * App::model('Test', true, 'en')->getElementsData()
 * В этом случае:
 *
	1)
	Если язык указан и если getLocalizationMode() = 'code' и ЯЗЫК != APP_LANG
	То в модели к коду $this->table через подчеркивание добавляется язык - "*_en"
	В противном случае $this->table остаётся как было указано в модели
 *
 *
 */

use Bitrix\Main\Loader;
Loader::includeModule("iblock");


class BaseIblockModel
{
	use \ResultTrait;
	use \BuildResultTrait;
	use \CacheTrait;
	use \ErrorTrait;


	public $table = ''; // код инфоблока
	public $lid = ''; // постфикс-указатель на текущий язык модели
	public $iblockSectionsSelect = []; // выборка полей для секций модели
	public $iblockSectionsSelectForLocalization = []; // выборка полей для секций модели
	public $iblockElementsSelect = []; // выборка полей для элементов модели
	public $iblockElementsSelectForLocalization = []; // выборка полей для элементов модели
	public $localizationMode = ''; // режим локализации модели
	private $iblockData = []; // массив параметров инфоблока
	private $curModes = ['code', 'select', 'checkbox']; // режимы локализации модели инфоблока


	/**
	 * $locale - требуемый язык
	 * Если $locale указана и если APP_MODEL_LOCALIZATION_MODE = 'code' и $locale != APP_LANG
	 * То в модели к коду $this->table через подчеркивание добавляется $locale - "*_en"
	 * В противном случае $this->table остаётся как было указано в модели
	 *
	 * @param string $locale
	 */
	public function __construct(string $locale = '')
	{
		$curLang = !empty($locale) ? $locale : LANGUAGE_ID;

		$this->setLocalizationMode();

		if (!empty($this->table)) {
			// если дефолтный язык не равен языку модели
			if (\H::isDefaultLanguage($curLang) === false) {
				$this->lid = strtoupper('_'.$curLang); // обновляем указатель языка модели
			}

			// если режим локализации моделей указан как "code"
			if ($this->getLocalizationMode() == 'code') {
				$this->table .= strtolower($this->lid); // добавляем к коду инфоблока указание на язык модели
			}

			$this->getInfoblock();
		} else {
			throw new \LogicException('Модель "'.get_class($this).'" должна содержать не пустую переменную $table!');
			exit();
		}
	}

	/**
	 * Устанавливает текущий режим локализации модели
	 *
	 * @return void
	 */
	private function setLocalizationMode(): void
	{
		$curMode = !empty($this->localizationMode) ? $this->localizationMode : \Config::get('App.APP_MODEL_LOCALIZATION_MODE', 'code');

		if (!in_array($curMode, $this->curModes)) {
			throw new \LogicException('Для модели "'.get_class($this).'" указан неправильный тип локализации. Доступные значения: '.implode(', ', $this->curModes));
			exit();
		}

		$this->localizationMode = $curMode;
	}

	/**
	 * Возвращает текущий режим локализации модели
	 *
	 * @return string
	 */
	public function getLocalizationMode(): string
	{
		return $this->localizationMode;
	}

	/**
	 * Составляет фильтр у секций для текущего запроса
	 * Условия из $filter плюсуются к базовому условию с IBLOCK_ID
	 *
	 * @param array $filter
	 * @return array
	 */
	private function makeSectionsFilter(array $filter = []): array
	{
		$curFilter = ['IBLOCK_ID' => $this->iblockData['ID']];

		if (count($filter) > 0) {
			$curFilter += $filter;
		}

		return $curFilter;
	}

	/**
	 * Составляет список полей у секций для текущего запроса
	 * Если переданный $select не пустой, то использует его.
	 * Если нет, то смотрит на переменную класса $this->iblockSectionsSelect.
	 *
	 * Если режим локализации модели =  select,
	 * то смотрит на переменную класса $this->iblockSectionsSelectForLocalization
	 *
	 * @param array $select
	 * @return array
	 */
	private function makeSectionsSelect(array $select = []): array
	{
		$curSelect = [];

		if (count($select) > 0) {
			$curSelect = array_merge($curSelect, $select);
		} else {
			if (count($this->iblockSectionsSelect) > 0) {
				// свойста из iblockElementsSelect нужно брать всегда независимо от $localizationMode
				$curSelect = array_merge($curSelect, $this->iblockSectionsSelect);
			}
			if ($this->getLocalizationMode() == 'select') {
				if (count($this->iblockSectionsSelectForLocalization) > 0) {
					$locSelect = [];

					foreach ($this->iblockSectionsSelectForLocalization as $v) {
						$locSelect[] = $v.$this->lid; // подставляем постфикс языка для свойства
					}

					// добавляет локализованные свойства
					$curSelect = array_merge($curSelect, $locSelect);
				}
			}
		}

		return $curSelect;
	}

	/**
	 * Составляет сортировку у секций для текущего запроса
	 *
	 * @param array $order
	 * @return array
	 */
	private function makeSectionsOrder(array $order = []): array
	{
		$curOrder = ["SORT" => "ASC", "ID" => "ASC"];

		if (count($order) > 0) {
			$curOrder = $order;
		}

		return $curOrder;
	}

	/**
	 * Составляет фильтр у элементов для текущего запроса
	 * Условия из $filter плюсуются к базовому условию с IBLOCK_ID
	 *
	 * @param array $filter
	 * @return array
	 */
	private function makeElementsFilter(array $filter = []): array
	{
		$curFilter = ['IBLOCK_ID' => $this->iblockData['ID']];

		if (count($filter) > 0) {
			$curFilter += $filter;
		}

		return $curFilter;
	}

	/**
	 * Составляет список полей у элементов для текущего запроса.
	 * Если переданный $select не пустой, то использует его.
	 * Если нет, то смотрит на переменную класса $this->iblockElementsSelect.
	 *
	 * Если режим локализации модели =  select,
	 * то смотрит на переменную класса $this->iblockElementsSelectForLocalization
	 *
	 * @param array $select
	 * @return array
	 */
	private function makeElementsSelect(array $select = []): array
	{
		$curSelect = [];

		if (count($select) > 0) {
			$curSelect = array_merge($curSelect, $select);
		} else {
			// свойста из iblockElementsSelect нужно брать всегда независимо от $localizationMode
			if (count($this->iblockElementsSelect) > 0) {
				$curSelect = array_merge($curSelect, $this->iblockElementsSelect);
			}
			if ($this->getLocalizationMode() == 'select') {
				if (count($this->iblockElementsSelectForLocalization) > 0) {
					$locSelect = [];

					foreach ($this->iblockElementsSelectForLocalization as $v) {
						$locSelect[] = $v.$this->lid; // подставляем постфикс языка для свойства
					}

					// добавляет локализованные свойства
					$curSelect = array_merge($curSelect, $locSelect);
				}
			}
		}

		return $curSelect;
	}

	/**
	 * Составляет сортировку у элементов для текущего запроса
	 *
	 * @param array $order
	 * @return array
	 */
	private function makeElementsOrder(array $order = []): array
	{
		$curOrder = ["SORT" => "ASC", "ID" => "ASC"];

		if (count($order) > 0) {
			$curOrder = $order;
		}

		return $curOrder;
	}

	/**
	 * Составляет группировку у элементов для текущего запроса
	 *
	 * @param array $order
	 * @return array
	 */
	private function makeElementsGroup(mixed $group = false): mixed
	{
		$curGroup = $group == '' ? false : $group;

		return $curGroup;
	}

	/**
	 * Возвращает информацию об инфоблоке
	 *
	 * @return array
	 */
	public function getInfoblock(): array
	{
		if (count($this->iblockData) == 0) {
			$res = \CIBlock::GetList(
				[],
				[
					'SITE_ID' => SITE_ID,
					"CODE" => $this->table
				],
				false
			);
			if ($res->result->num_rows > 0) {
				$this->iblockData = $res->Fetch();
			} else {
				throw new \LogicException('Модель "'.get_class($this).'" инфоблок "'.$this->table.'" указанный в переменной $table не существует!');
				exit();
			}
		}

		return $this->iblockData;
	}

	/**
	 * Возвращает объект всех секций инфоблока в необработанном виде
	 * Можно указать список полей, условия фильтра и т.д.
	 * $select перебивает переменные инфоблока $iblockSectionsSelect и $iblockSectionsSelectForLocalization
	 *
	 * @param array $select
	 * @param array $filter
	 * @param array $order
	 * @return object
	 */
	public function getSections(array $select = [], array $filter = [], array $order = []): object
	{
		$arFilter = $this->makeSectionsFilter($filter);
		$arSelect = $this->makeSectionsSelect($select);
		$arOrder = $this->makeSectionsOrder($order);
		$sections = \CIBlockSection::GetList($arOrder, $arFilter, false, $arSelect, false);

		return $sections;
	}

	/**
	 * Возвращает массив с данными конкретной секции
	 * Можно указать список полей, условия фильтра и т.д.
	 * $select перебивает переменные инфоблока $iblockSectionsSelect и $iblockSectionsSelectForLocalization
	 *
	 * @param array $select
	 * @param array $filter
	 * @param array $order
	 * @return array | bool
	 */
	public function getSection(array $select = [], array $filter = [], array $order = []): array | bool
	{
		$arFilter = $this->makeSectionsFilter($filter);
		$arSelect = $this->makeSectionsSelect($select);
		$arOrder = $this->makeSectionsOrder($order);
		$section = \CIBlockSection::GetList($arOrder, $arFilter, false, $arSelect, false)->GetNext();

		return $section;
	}

	/**
	 * Удаляет секцию по переданному $id
	 * Возвращает массив трейта buildResult
	 *
	 * @param integer $id
	 * @return array
	 */
	public function deleteSection(int $id = 0): array
	{
		$result = [];
		$el = new \CIBlockSection;

		if ($el->Delete($id)) {
			$result = $this->buildResult(false, 'Секция успешно удалена');
		} else {
			$result = $this->buildResult(true, 'Ошибка удаления секции '.$id);
		}

		return $result;
	}

	/**
	 * Обновляет секцию с указанным $id данными указанными в $fields.
	 * $resort - пересчитывать ли правую и левую границы после изменения
	 * $updateSearch - Индексировать элемент для поиска.
	 * $resizePictures - Использовать настройки инфоблока для обработки изображений.
	 *
	 * Возвращает массив трейта buildResult
	 *
	 * @param int $id
	 * @param array $fields
	 * @param boolean $resort
	 * @param boolean $updateSearch
	 * @param boolean $resizePictures
	 * @return array
	 */
	public function updateSection(int $id, array $fields = [], bool $resort = true, bool $updateSearch = true, bool $resizePictures = false): array
	{
		$result = [];
		$el = new \CIBlockSection;

		if ($el->update($id, $fields, $resort, $updateSearch, $resizePictures)) {
			$result = $this->buildResult(false, 'Секция успешно обновлена!');
		} else {
			$result = $this->buildResult(true, $el->LAST_ERROR);
		}

		return $result;
	}

	/**
	 * Добавляет в инфоблок секцию с полями $fields
	 * $resort - пересчитывать ли правую и левую границы после изменения
	 * $updateSearch - Индексировать элемент для поиска.
	 * $resizePictures - Использовать настройки инфоблока для обработки изображений.
	 *
	 * Возвращает массив трейта buildResult
	 * При успехе в ключе data будет указан id добавленного элемента
	 *
	 * @param array $fields
	 * @param boolean $resort
	 * @param boolean $updateSearch
	 * @param boolean $resizePictures
	 * @return array
	 */
	public function addSection(array $fields = [], bool $resort = true, bool $updateSearch = true, bool $resizePictures = false): array
	{
		$result = [];
		$el = new \CIBlockSection;
		$default = [
			"IBLOCK_ID" => $this->getInfoblock()['ID'],
		];
		$curFields = $default + $fields;

		if ($elemID = $el->add($curFields, $resort, $updateSearch, $resizePictures)) {
			$result = $this->buildResult(false, 'Запись успешно добавлена!', ['id' => $elemID]);
		} else {
			$result = $this->buildResult(true, $el->LAST_ERROR);
		}

		return $result;
	}

	/**
	 * Возвращает объект всех элементов инфоблока в необработанном виде.
	 * Можно указать список полей, условия фильтра и т.д.
	 * $select перебивает переменные инфоблока $iblockElementsSelect и $iblockElementsSelectForLocalization
	 *
	 * @param array $select
	 * @param array $filter
	 * @param array $order
	 * @param array $limit
	 * @param mixed $group
	 * @return object|string
	 */
	public function getElements(array $select = [], array $filter = [], array $order = [], mixed $limit = [], mixed $group = false): object|string
	{
		$arFilter = $this->makeElementsFilter($filter);
		$arSelect = $this->makeElementsSelect($select);
		$arOrder = $this->makeElementsOrder($order);
		$arGroup = $this->makeElementsGroup($group);
		$elements = \CIBlockElement::GetList($arOrder, $arFilter, $arGroup, $limit, $arSelect);

		return $elements;
	}

	/**
	 * Возвращает массив с данными конкретного элемента
	 * Можно указать список полей, условия фильтра и т.д.
	 * $select перебивает переменные инфоблока $iblockElementsSelect и $iblockElementsSelectForLocalization
	 * $filter может быть по ID или CODE (указываем сами)
	 *
	 * @param array $select
	 * @param array $filter
	 * @return array | bool
	 */
	public function getElement(array $select = [], array $filter = []): array | bool
	{
		$arFilter = $this->makeElementsFilter($filter);
		$arSelect = $this->makeElementsSelect($select);
		$element = \CIBlockElement::GetList([], $arFilter, false, [], $arSelect)->GetNext();

		return $element;
	}

	/**
	 * Удаляет элемент по переданному $id
	 * Возвращает массив трейта buildResult
	 *
	 * @param integer $id
	 * @return array
	 */
	public function delete(int $id = 0): array
	{
		$result = [];
		$el = new \CIBlockElement;

		if ($el->Delete($id)) {
			$result = $this->buildResult(false, 'Элемент успешно удалён');
		} else {
			$result = $this->buildResult(true, 'Ошибка удаления элемента '.$id);
		}

		return $result;
	}

	/**
	 * Добавляет в инфоблок элемент с полями $fields
	 * $workFlow - Вставка в режиме документооборота.
	 * $updateSearch - Индексировать элемент для поиска.
	 * $resizePictures - Использовать настройки инфоблока для обработки изображений.
	 *
	 * Возвращает массив трейта buildResult
	 * При успехе в ключе data будет указан id добавленного элемента
	 *
	 * @param array $fields
	 * @param boolean $workFlow
	 * @param boolean $updateSearch
	 * @param boolean $resizePictures
	 * @return array
	 */
	public function add(array $fields = [], bool $workFlow = false, bool $updateSearch = true, bool $resizePictures = false): array
	{
		$result = [];
		$el = new \CIBlockElement;
		$default = [
			"IBLOCK_ID" => $this->getInfoblock()['ID'],
		];
		$curFields = $default + $fields;

		if ($elemID = $el->add($curFields, $workFlow, $updateSearch, $resizePictures)) {
			$result = $this->buildResult(false, 'Запись успешно добавлена!', ['id' => $elemID]);
		} else {
			$result = $this->buildResult(true, $el->LAST_ERROR);
		}

		return $result;
	}

	/**
	 * Обновляет элемент с указанным $id данными указанными в $fields.
	 * Если у $fields задан массив PROPERTY_VALUES, то он должен содержать полный набор значений свойств
	 * для данного элемента, т.е. если в нем будет отсутствовать одно из свойств, то все его значения
	 * для данного элемента будут удалены.
	 *
	 * Если передать третьим параметром $workFlow = TRUE, то будут обновляться только указанные пропсы.
	 * Но только в том случае, если стоит модуль ДОКУМЕНТООБОРОТ. А его можно ставить не во все версии битрикса.
	 *
	 * Если модуля нет, то ПРОПСЫ обновляются через отдельный метод - SetPropertyValuesEx
	 *
	 * $workFlow - Вставка в режиме документооборота.
	 * $updateSearch - Индексировать элемент для поиска.
	 * $resizePictures - Использовать настройки инфоблока для обработки изображений.
	 * $checkDiskQuota - Проверять ограничение
	 *
	 * Возвращает массив трейта buildResult
	 *
	 * @param integer $id
	 * @param array $fields
	 * @param boolean $workFlow
	 * @param boolean $updateSearch
	 * @param boolean $resizePictures
	 * @param boolean $checkDiskQuota
	 * @return array
	 */
	public function update(int $id, array $fields = [], bool $workFlow = false, bool $updateSearch = true, bool $resizePictures = false, bool $checkDiskQuota = true): array
	{
		$result = [];
		$el = new \CIBlockElement;

		if ($el->update($id, $fields, $workFlow, $updateSearch, $resizePictures, $checkDiskQuota)) {
			$result = $this->buildResult(false, 'Запись успешно обновлена!');
		} else {
			$result = $this->buildResult(true, $el->LAST_ERROR);
		}

		return $result;
	}

	/**
	 * Для элемента $id обновляет значения пропсов $propertyValues.
	 * Может содержать неполный список пропсов. Не указанным пропсам не будет чиститься значение.
	 * Это в отличии от метода update с указанным массивом PROPERTY_VALUES и отключённым документооборотом.
	 * А так же в отличии от битриксовой SetPropertyValues
	 *
	 * $flags - предоставляет информацию для оптимизации выполнения
	 *
	 * Возвращает массив трейта buildResult
	 *
	 * @param integer $id
	 * @param array $propertyValues
	 * @param array $flags
	 * @return array
	 */
	public function setPropertyValuesEx(int $id = 0, array $propertyValues = [], array $flags = []): array
	{
		$result = [];

		if ($id > 0) {
			$el = new \CIBlockElement;
			$el->SetPropertyValuesEx($id, $this->getInfoblock()['ID'], $propertyValues, $flags);

			$result = $this->buildResult(false, 'Обновлено');
		} else {
			$result = $this->buildResult(true, 'Элемент ID должен быть больше 0');
		}

		return $result;
	}

	/**
	 * SEO
	 * Возвращает для текущего инфоблока new \Bitrix\Iblock\InheritedProperty\IblockValues
	 *
	 * @return object
	 */
	public function iblockValues(): object
	{
		return new \Bitrix\Iblock\InheritedProperty\IblockValues($this->iblockData['ID']);
	}

	/**
	 * SEO
	 * Возвращает для текущего инфоблока getValues() от \Bitrix\Iblock\InheritedProperty\IblockValues
	 *
	 * @return array
	 */
	public function getIblockValues(): array
	{
		return $this->iblockValues()->getValues();
	}

	/**
	 * SEO
	 * Возвращает для текущего инфоблока Bitrix\Iblock\InheritedProperty\SectionValues
	 *
	 * @param integer $sectionId
	 * @return object
	 */
	public function sectionValues(int $sectionId = 0): object
	{
		return new \Bitrix\Iblock\InheritedProperty\SectionValues($this->iblockData['ID'], $sectionId);
	}

	/**
	 * SEO
	 * Возвращает для текущего инфоблока getValues() от \Bitrix\Iblock\InheritedProperty\SectionValues
	 *
	 * @param integer $sectionId
	 * @return array
	 */
	public function getSectionValues(int $sectionId = 0): array
	{
		return $this->sectionValues($sectionId)->getValues();
	}

	/**
	 * SEO
	 * Возвращает для текущего инфоблока Bitrix\Iblock\InheritedProperty\ElementValues
	 *
	 * @param integer $elementId
	 * @return object
	 */
	public function elementValues(int $elementId = 0): object
	{
		return new \Bitrix\Iblock\InheritedProperty\ElementValues($this->iblockData['ID'], $elementId);
	}

	/**
	 * SEO
	 * Возвращает для текущего инфоблока getValues() от \Bitrix\Iblock\InheritedProperty\ElementValues
	 *
	 * @param integer $elementId
	 * @return array
	 */
	public function getElementValues(int $elementId = 0): array
	{
		return $this->elementValues($elementId)->getValues();
	}

	/**
	 * Возвращает массив-дерево всех секций инфоблока.
	 * Вложенные секции лежат в ключе "sections"
	 *
	 * $select - по дефолту берёт из $iblockSectionsSelect или $iblockSectionsSelectForLocalization модели
	 *
	 * @param array $select
	 * @param array $filter
	 * @param array $order
	 * @return array
	 */
	public function getTree(array $select = [], array $filter = [], array $order = []): array
	{
		$arFilter = $this->makeSectionsFilter($filter);
		$arSelect = array_merge($this->makeSectionsSelect($select), ['ID', 'IBLOCK_SECTION_ID', 'DEPTH_LEVEL']);
		$arOrder = $this->makeSectionsOrder($order);
		$tree = \CIBlockSection::GetList(["left_margin" => "asc"], $arFilter, false, $arSelect, false);

		$arSectionList = [];
		$arDepthLavel = [];

		while($section = $tree->GetNext()) {
			$arSectionList[$section['ID']] = $section;
			$arDepthLavel[] = $section['DEPTH_LEVEL'];
		}

		$arDepthLavel = array_unique($arDepthLavel);
		rsort($arDepthLavel);
		$maxDepthLevel = $arDepthLavel[0];

		for( $i = $maxDepthLevel; $i > 1; $i-- ) {
			foreach ( $arSectionList as $sectionID => $value ) {
				if( $value['DEPTH_LEVEL'] == $i ) {
					$arSectionList[$value['IBLOCK_SECTION_ID']]['sections'][] = $value;
					unset( $arSectionList[$sectionID] );
				}
			}
		}

		usort($arSectionList, function ($a, $b) {
			if ($a['SORT'] == $b['SORT']) {
				return 0;
			}
			return ($a['SORT'] < $b['SORT']) ? -1 : 1;
		});

		return $arSectionList;
	}

	/**
	/**
	 * Возвращает массив-дерево всех секций инфоблока и их элементов.
	 * Вложенные секции лежат в ключе "sections"
	 * Вложенные элементы лежат в ключе "elements"
	 *
	 * $select - по дефолту берёт из $iblockSectionsSelect или $iblockSectionsSelectForLocalization модели
	 * $selectElement - по дефолту берёт из $iblockElementsSelect или $iblockElementsSelectForLocalization модели
	 *
	 * @param array $select
	 * @param array $filter
	 * @param array $order
	 * @param array $selectElement
	 * @param array $filterElement
	 * @param array $orderElement
	 * @return array
	 */
	public function getTreeWithElements(array $select = [], array $filter = [], array $order = [], array $selectElement = [], array $filterElement = [], array $orderElement = []): array
	{
		$arFilter = $this->makeSectionsFilter($filter);
		$arSelect = array_merge($this->makeSectionsSelect($select), ['ID', 'IBLOCK_SECTION_ID', 'DEPTH_LEVEL']);
		$arOrder = $this->makeSectionsOrder($order);
		$tree = \CIBlockSection::GetList(["left_margin" => "asc"], $arFilter, false, $arSelect, false);

		$arSectionList = [];
		$arDepthLavel = [];

		while($section = $tree->GetNext()) {
			$arSectionList[$section['ID']] = $section;
			$arDepthLavel[] = $section['DEPTH_LEVEL'];

			$arElemFilter = array_merge($this->makeElementsFilter($filterElement), ["IBLOCK_SECTION_ID" => $section['ID']]);
			$arElemSelect = $this->makeElementsSelect($selectElement);
			$arElemOrder = $this->makeElementsOrder($orderElement);
			$res = \CIBlockElement::GetList(
				$arElemOrder,
				$arElemFilter,
				false,
				[],
				$arElemSelect
			);
			while($el = $res->GetNextElement())
			{
				$arFields = $el->GetFields();
				$arSectionList[$section['ID']]['elements'][] = $arFields;
			}
		}

		$arDepthLavel = array_unique($arDepthLavel);
		rsort($arDepthLavel);
		$maxDepthLevel = $arDepthLavel[0];

		for( $i = $maxDepthLevel; $i > 1; $i-- ) {
			foreach ( $arSectionList as $sectionID => $value ) {
				if( $value['DEPTH_LEVEL'] == $i ) {
					$arSectionList[$value['IBLOCK_SECTION_ID']]['sections'][] = $value;
					unset( $arSectionList[$sectionID] );
				}
			}
		}

		usort($arSectionList, function ($a, $b) {
			if ($a['SORT'] == $b['SORT']) {
				return 0;
			}
			return ($a['SORT'] < $b['SORT']) ? -1 : 1;
		});

		return $arSectionList;
	}

	/**
	 * Представляет собой обработку данных пришедших из $this->getElements().
	 * Чтобы сформировать из них готовый массив для передачи во фронтенд блок.
	 *
	 * Например:
	 *
	 * public function getElementsData(): array
		{
			$data = [];
			$items = $this->getElements();

			while ($item = $items->fetch()) {
				$data[] = [
					'id' => $item['ID'],
					'name' => $item['NAME'],
				];
			}

			return $data;
		}
	 *
	 * @return array
	 */
	public function getElementsData(): array {}

	/**
	 * Описать у себя в модели, если будет нужно
	 *
	 * @return array
	 */
	public function getElementData(): array {}

	/**
	 * Описать у себя в модели, если будет нужно
	 *
	 * @return array
	 */
	public function getSectionsData(): array {}

	/**
	 * Описать у себя в модели, если будет нужно
	 *
	 * @return array
	 */
	public function getSectionData(): array {}

	/**
	 * Генерация урлов для сайтмапа по заданному в модели инфоблоку
	 *
	 * В классе модели можно переопределить метод и написать свой сборщик урлов
	 *
	 * @param array $params
	 * @return array
	 */
	public function sitemap($params): array
	{
		$data = [];

		if (!empty($params['page'])) {
			$item = $this->getElement(['LIST_PAGE_URL', 'TIMESTAMP_X'], ['ACTIVE' => 'Y'], ['SORT' => 'ASC']);

			$data[$item['LIST_PAGE_URL']] = [
				'url' => $item['LIST_PAGE_URL'],
				'lastmod' => $item['TIMESTAMP_X'],
				'changefreq' => $params['page']['change'],
				'priority' => $params['page']['priority']
			];
		}

		$sections = [];

		if (!empty($params['sections'])) {
			$items = $this->getSections(['SECTION_PAGE_URL', 'TIMESTAMP_X'], ['ACTIVE' => 'Y', 'IBLOCK_ACTIVE' => 'Y', 'GLOBAL_ACTIVE' => 'Y'], ['SORT' => 'ASC']);

			while ($item = $items->GetNext()) {
				$sections[$item['SECTION_PAGE_URL']] = [
					'url' => $item['SECTION_PAGE_URL'],
					'lastmod' => $item['TIMESTAMP_X'],
					'changefreq' => $params['sections']['change'],
					'priority' => $params['sections']['priority']
				];
			}
		}

		$data = array_merge($data, $sections);

		if (!empty($params['elements'])) {
			if (count($sections) > 0) {
				$items = $this->getElements(['DETAIL_PAGE_URL', 'TIMESTAMP_X'], ['ACTIVE' => 'Y', 'IBLOCK_ACTIVE' => 'Y', 'SECTION_ACTIVE' => 'Y', 'SECTION_GLOBAL_ACTIVE' => 'Y'], ['SORT' => 'ASC']);
			} else {
				$items = $this->getElements(['DETAIL_PAGE_URL', 'TIMESTAMP_X'], ['ACTIVE' => 'Y', 'IBLOCK_ACTIVE' => 'Y'], ['SORT' => 'ASC']);
			}

			while ($item = $items->GetNext()) {
				$data[$item['DETAIL_PAGE_URL']] = [
					'url' => $item['DETAIL_PAGE_URL'],
					'lastmod' => $item['TIMESTAMP_X'],
					'changefreq' => $params['elements']['change'],
					'priority' => $params['elements']['priority']
				];
			}
		}

		return $data;
	}
}
