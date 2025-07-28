<?php
namespace Techart\BxApp\Base\Model;

/**
 * Базовый класс моделей для HIGHLOAD блоков.
 *
 * Нужно от него наследовать классы своих моделей highload-блоков.
 * Обязательно нужно указать код highload в переменной $table.
 *
 * Работа с моделью хайлоадблоков очень похожа на работу с моделью инфоблока.
 * Читай описание в файле BaseIblockModel.php
 *
 *
 * ______ЛОКАЛИЗАЦИЯ:
 *
 * При вызове модели третьим параметром можно передать требуемый язык
 * App::model('HbTest', true, 'en')->getElementsData()
 * В этом случае:
 *
	1)
	Если язык указан и если getLocalizationMode() = 'code'
	То в модели к коду $this->table с большой буквы добавляется язык - "*En"
	Если getLocalizationMode() = 'none', то $this->table остаётся как было указано в модели
 */

\CModule::IncludeModule('highloadblock');

use Bitrix\Highloadblock as HL;
// use Bitrix\Highloadblock\HighloadBlockTable;
// use Bitrix\Main\Loader;


class BaseHighloadModel
{
	use \ResultTrait;
	use \BuildResultTrait;
	use \CacheTrait;
	use \ErrorTrait;
	use \LocalizationMessageTrait;


	public $table = ''; // код highload блока
	public $lid = ''; // постфикс-указатель на текущий язык модели
	public $pid = ''; // постфикс-указатель глобальный (объединяющий язык и группу)
	public $hblockElementsSelect = ['*']; // выборка полей для элементов модели
	public $hblockElementsSelectForLocalization = []; // выборка полей для элементов модели
	public $localizationMode = ''; // режим локализации модели
	private $hblockData = []; // массив параметров highload блока
	private $curModes = ['none', 'code', 'select', 'checkbox']; // режимы локализации модели хайлоадблока


	/**
	 * $locale - требуемый язык
	 * Если $locale указана и если APP_MODEL_LOCALIZATION_MODE = 'code'
	 * То в модели к коду $this->table с большой буквы добавляется $locale - "*En"
	 * Если APP_MODEL_LOCALIZATION_MODE = 'none', то $this->table остаётся как было указано в модели
	 *
	 * @param string $locale
	 */
	public function __construct(string $locale = '')
	{
		$curLang = !empty($locale) ? $locale : BXAPP_LANGUAGE_ID;

		$this->setLocalizationMode();

		if (!empty($this->table)) {
			$locMode = $this->getLocalizationMode();

			if ($locMode !== 'none') {
				$this->lid = strtoupper('_'.$curLang); // обновляем указатель языка модели
				$this->pid = __GID__.$this->lid;

				// если режим локализации моделей указан как "code"
				if ($locMode == 'code') {
					$this->table .= \H::ucfirst(trim(__GID__, '_'), lowerStrEnd: true).\H::ucfirst(trim($this->lid, '_'), lowerStrEnd: true);
				}
			}

			$this->getHighloadBlock();
		} else {
			throw new \LogicException('Модель "' . get_class($this) . '" должна содержать не пустую переменную $iblockCode!');
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
	 * Получает информацию о highload-блоке
	 *
	 * @return array
	 */
	public function getHighloadBlock(): array
	{
		if(count($this->hblockData) == 0) {
			$hlblock = HL\HighloadBlockTable::getList([
				'filter' => ['=NAME' => $this->table]
			])->fetch();

			if($hlblock) {
				$this->hblockData = $hlblock;
			} else {
				throw new \LogicException('Модель "'.get_class($this).'" highload-блок "'.$this->table.'" указанный в переменной $table не существует!');
				exit();
			}
		}

		return $this->hblockData;
	}

	/**
	 * Получает класс highload-блока
	*/
	private function getEntityClass(): string
	{
		$entity = HL\HighloadBlockTable::compileEntity($this->hblockData);

		return $entity->getDataClass();
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
		$entity = HL\HighloadBlockTable::compileEntity($this->hblockData);
		$entityDataClass = $entity->getDataClass();
		$res = $entityDataClass::delete($id);

		if ($res->isSuccess()) {
			$result = $this->buildResult([], false, 'Элемент успешно удалён');
		} else {
			$result = $this->buildResult([], true, 'Ошибка удаления элемента '.$id.': '.implode('; ', $res->getErrorMessages()));
		}

		return $result;
	}

	/**
	 * Добавляет элемент с полями $fields
	 *
	 * Возвращает массив трейта buildResult
	 * При успехе в ключе data будет указан id добавленного элемента
	 *
	 * @param array $fields
	 * @return array
	 */
	public function add(array $fields = []): array
	{
		$entity = HL\HighloadBlockTable::compileEntity($this->hblockData);
		$entityDataClass = $entity->getDataClass();
		$res = $entityDataClass::add($fields);

		if ($res->isSuccess()) {
			$result = $this->buildResult(['id' => $res->getId()], false, 'Запись успешно добавлена!');
		} else {
			$result = $this->buildResult([], true, implode('; ', $res->getErrorMessages()));
		}

		return $result;
	}

	/**
	 * Ообновляет элемент с ид $id и полями $fields
	 *
	 * Возвращает массив трейта buildResult
	 *
	 * @param array $fields
	 * @return array
	 */
	public function update(int $id = 0, array $fields = []): array
	{
		$entity = HL\HighloadBlockTable::compileEntity($this->hblockData);
		$entityDataClass = $entity->getDataClass();
		$res = $entityDataClass::update($id, $fields);

		if ($res->isSuccess()) {
			$result = $this->buildResult([], false, 'Запись успешно обновлена!');
		} else {
			$result = $this->buildResult([], true, implode('; ', $res->getErrorMessages()));
		}

		return $result;
	}

	/**
	 * Составляет Order для текущего запроса
	 *
	 * Order по умолчанию переопределяется в случае получения Order из вне
	 *
	 * @param array $filter
	 *
	 * @return array
	*/
	private function makeOrder(array $order = []): array
	{
		$curOrder = ["ID" => "ASC"];
		if (count($order) > 0) {
			$curOrder = $order;
		}

		return $curOrder;
	}

	/**
	 * Составляет Select для текущего запроса
	 *
	 * Select по умолчанию переопределяется в случае получения Select из вне
	 * Если нет Select, то смотрит на переменную класса $hblockElementsSelect или $hblockElementsSelectForLocalization
	 * В $hblockElementsSelect по умолчанию выбираются все поля
	 *
	 * @param array $select
	 *
	 * @return array
	*/
	private function makeSelect(array $select = []): array
	{
		$curSelect = [];

		if (count($select) > 0) {
			$curSelect = array_merge($curSelect, $select);
		} else {
			// свойста из iblockElementsSelect нужно брать всегда независимо от $localizationMode
			if(count($this->hblockElementsSelect) > 0) {
				$curSelect = array_merge($curSelect, $this->hblockElementsSelect);
			}
			if ($this->getLocalizationMode() == 'select') {
				if (count($this->hblockElementsSelectForLocalization) > 0) {
					$locSelect = [];

					foreach ($this->hblockElementsSelectForLocalization as $v) {
						$locSelect[] = $v.$this->pid; // подставляем постфикс языка для свойства
					}

					// добавляет локализованные свойства
					$curSelect = array_merge($curSelect, $locSelect);
				}
			}
		}

		return $curSelect;
	}

	/**
	 * Возвращает объект всех элементов highload-блока в необработанном виде.
	 * Можно указать список полей, условия фильтра и т.д.
	 *
	 * @param array $select
	 * @param array $filter
	 * @param array $order
	 *
	 * @return object
	 */
	public function getElements(array $select = [], array $filter = [], array $order = []): object
	{
		$entityDataClass = $this->getEntityClass();

		$hlselect = $this->makeSelect($select);
		$hlfilter = $filter;
		$hlorder = $this->makeOrder($order);

		$elements = $entityDataClass::getList([
			'select' => $hlselect,
			'order' => $hlorder,
			'filter' => $hlfilter
		]);

		return $elements;
	}

	/**
	 * Возвращает один элемент highload-блока в необработанном виде
	 * Можно указать список полей и условия фильтра
	 *
	 * @param array $select
	 * @param array $filter
	 *
	 * @return array
	 */
	public function getElement(array $select = [], array $filter = []): array
	{
		$entityDataClass = $this->getEntityClass();
		$hlselect = $this->makeSelect($select);
		$hlfilter = $filter;

		$element = $entityDataClass::getList([
			'select' => $hlselect,
			'filter' => $hlfilter,
		]);

		$data = $element->Fetch();

		return !$data ? [] : $data;
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
}
