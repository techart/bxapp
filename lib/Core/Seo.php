<?
namespace Techart\BxApp\Core;

/**
* Методы, необходимые для установки Мета тегов и в будущем, возможно, для новых фич,
* которые связаны с SEO
*
*/


class Seo
{
	public $code = ''; // код страницы
	public $metas = []; // меты для страницы
	public $iblockId = ''; // id инфоблока


	public function setMetas(string $iblock_code)
	{
		global $APPLICATION;

		$this->getInfoblock($iblock_code);
		$this->getPageCode();

		$metas = $this->getMetas();

		foreach($metas as $key => $value) {
			if($value)
				$APPLICATION->SetPageProperty($key, $value);
		}
	}

	/**
	 * Получает code, к которому относится текущий компонент
	 * Для главной - это main
	 * Для остальных страниц - это последний элемент из url path
	 *
	 * @return string
	 */
	private function getPageCode(): void
	{
		$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

		if($url == '/') {
			$this->code = 'main';
		} else {
			$this->code = array_pop(explode('/', trim($url, '/')));
		}
	}

	/**
	 * Возвращает информацию об инфоблоке
	 *
	 * @param string $iblock_code
	 *
	 * @return array
	 */
	private function getInfoblock(string $iblock_code): bool
	{
		$infoblock = CIBlock::GetList(
			[],
			[
				'SITE_ID' => SITE_ID,
				"CODE" => $iblock_code
			],
			false
		);

		if ($infoblock->result->num_rows > 0) {
			$this->iblockId = $infoblock->Fetch()['ID'];
		} else {
			throw new LogicException("Инфоблок ".$iblock_code." не существует");
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

		$items = CIBlockElement::GetList(
			['SORT' => 'ASC'],
			[
				'IBLOCK_ID' => $this->iblockId,
				'CODE' => $this->code
			], false, false,
			['PROPERTY_TITLE', 'PROPERTY_DESCRIPTION', 'PROPERTY_KEYWORDS']
		);

		while($item = $items->Fetch()) {
			$metas = [
				'title' => $item['PROPERTY_TITLE_VALUE'],
				'description' => $item['PROPERTY_DESCRIPTION_VALUE'],
				'keywords' => $item['PROPERTY_KEYWORDS_VALUE']
			];
		}

		return $metas;
	}
}
