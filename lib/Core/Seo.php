<?
namespace Techart\BxApp\Core;

use \Bitrix\Iblock\InheritedProperty\ElementValues;

\Bitrix\Main\Loader::includeModule('iblock');

/**
* Методы, необходимые для установки Мета тегов и в будущем, возможно, для новых фич,
* которые связаны с SEO
*
*/


class Seo
{
	public $name = ''; // название страницы
	public $metas = []; // меты для страницы
	public $iblockId = ''; // id инфоблока


	/**
	 * Устанавливает для текущей страницы метатеги взятые из инфоблока указанного
	 * в SEO_IBLOCK_CODE компонента page.content
	 *
	 * @param string $iblockСode
	 * @param string $entryPoint
	 * @return void
	 */
	public function setMetas(string $iblockСode = '', string $entryPoint = ''): void
	{
		global $APPLICATION;

		$this->getInfoblock($iblockСode);
		$this->getPageCode($entryPoint);

		$metas = $this->getMetas();

		foreach($metas as $key => $value) {
			if($value)
				$APPLICATION->SetPageProperty($key, $value);
		}
	}

	/**
	 * Получает code, к которому относится текущий компонент
	 * Для entryPoint = mainPage или error404 - это одноимённый код
	 * Для остальных страниц - это последний элемент из url path
	 *
	 * @return string
	 */
	private function getPageCode(string $entryPoint = ''): void
	{
		$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

		if($entryPoint == 'mainPage' || $entryPoint == 'error404') {
			$this->name = $entryPoint;
		} else {
			$this->name = $url;
		}
	}

	/**
	 * Возвращает информацию об инфоблоке
	 *
	 * @param string $iblockСode
	 *
	 * @return array
	 */
	private function getInfoblock(string $iblockСode): bool
	{
		$infoblock = \CIBlock::GetList(
			[],
			[
				'SITE_ID' => BXAPP_SITE_ID,
				"CODE" => $iblockСode
			],
			false
		);

		if ($infoblock->result->num_rows > 0) {
			$this->iblockId = $infoblock->Fetch()['ID'];
		} else {
			throw new \LogicException("Инфоблок ".$iblockСode." не существует");
			exit();
		}

		return true;
	}

	/**
	 * Получает меты из нужного нам элемента инфоблока с соответствующим кодом
	 *
	 * @return array
	 */
	private function getMetas(): array
	{
		$metas = [];

		$items = \CIBlockElement::GetList(
			['SORT' => 'ASC'],
			[
				'IBLOCK_ID' => $this->iblockId,
				'SECTION_CODE' => BXAPP_SITE_ID . '-' . BXAPP_LANGUAGE_ID,
				'NAME' => $this->name
			], false, false,
			['ID']
		);

		while($item = $items->Fetch()) {
			$iprops = new ElementValues($this->iblockId, $item['ID']);
			$ipropValues = $iprops->getValues();

			$metas = [
				'title' => $ipropValues['ELEMENT_META_TITLE'],
				'description' => $ipropValues['ELEMENT_META_DESCRIPTION'],
				'keywords' => $ipropValues['ELEMENT_META_KEYWORDS'],
				'h1' => $ipropValues['ELEMENT_PAGE_TITLE']
			];
		}

		return $metas;
	}
}
