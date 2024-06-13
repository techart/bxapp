<?php
namespace Techart\BxApp\Core;

/**
 * Класс, где собраны методы для работы с ассетами в той или иной мере
 */

use \Bitrix\Main\Page\Asset;

class Assets
{
	/**
	 * Возвращает код шрифтов для подключения в header.php
	 * Код заполняется в Configs/Assets.php - APP_ASSETS_FONT_FACE_CODE
	 *
	 * @return void
	 */
	public function showFontFace()
	{
		return \Config::get('Assets.APP_ASSETS_FONT_FACE_CODE', '');
	}

	/**
	 * Возвращает ссылку на ассет с именем $name
	 *
	 * @param string $name
	 * @return string
	 */
	public function getAssetUrl(string $name = ''): string
	{
		return '/local/templates/site/assets/'.$name;
	}

	/**
	 * Возвращает код шрифтов для подключения в header.php
	 * Код заполняется в Configs/Assets.php - APP_ASSETS_FAVICON_CODE
	 *
	 * @return void
	 */
	public function showFaviconHtmlCode():string
	{
		return \Config::get('Assets.APP_ASSETS_FAVICON_CODE', '');
	}

	/**
	 * Возвращает массив с аттребутами для тега подключения ассетов (script, link)
	 *
	 * @param string $item
	 * @param array $attrs
	 * @param string $type
	 * @return array
	 */
	private function buildTagAttrs(string $item = '', array $attrs = [], string $type = ''): array
	{
		$pathToAssets = SITE_ROOT_DIR.'/local/templates/site/frontend/assets/';
		$pathToBuildJson = realpath($pathToAssets . '/' . \Glob::get('APP_ENV') . '.json');
		$buildedJson = json_decode(file_get_contents($pathToBuildJson), true);
		$pathToTag = $buildedJson[$item][$type];

		$attrsString = '';
		foreach ($attrs as $name => $value) {
			if($value) {
				$attrsString .= " {$name}=\"{$value}\"";
			} else {
				$attrsString .= " {$name}";
			}
		}

		$attrsString = trim($attrsString);

		return [$pathToTag, $attrsString];
	}

	/**
	 * Возвращает строку с кодом подключаемого скрипта
	 *
	 * @param string $item
	 * @param array $attrs
	 * @return string
	 */
	public function getJsTag(string $item = '', array $attrs = []): string
	{
		$jsTag = $this->buildTagAttrs($item, $attrs, "js");

		return '<script src="' . $jsTag[0] . '" ' . $jsTag[1] . ' ></script>';
	}

	/**
	 * Возвращает строку с кодом подключаемого стиля
	 *
	 * @param string $item
	 * @param array $attrs
	 * @return string
	 */
	public function getCssTag(string $item = '', array $attrs = []): string
	{
		$cssTag = $this->buildTagAttrs($item, $attrs, "css");

		return '<link href="' . $cssTag[0] . '" ' . $cssTag[1] . ' media="screen" rel="stylesheet" >';
	}

	/**
	 * Подключает js библиотеки указанные в user.settings для переданной точки входа
	 *
	 * @param string $entry
	 * @return void
	 */
	public function setLibs(string $entry): void
	{
		$file = file_get_contents(SITE_ROOT_DIR.'/local/templates/site/frontend/user.settings.js');
		$file_entry = stristr($file, $entry);
		$file_entry = stristr($file_entry, '{');
		$file_entry = stristr($file_entry, '}', true).'}';

		$libs = \OviDigital\JsObjectToJson\JsConverter::convertToArray($file_entry);

		if($libs['dependOn']) {
			foreach($libs['dependOn'] as $item) {
				Asset::getInstance()->addString($this->getJsTag($item, ['defer' => '']));
			}
		}
	}

	/**
	 * Подключает ассеты по переданной точке входа
	 *
	 * @param string $entry
	 * @return void
	 */
	public function setEntryPoints(string $entry): void
	{
		$path = $this->getJsTag($entry, ['defer' => '']);

		if($path) {
			$this->setLibs($entry);

			Asset::getInstance()->addString($path);
			Asset::getInstance()->addString($this->getCssTag($entry));
		} else {
			throw new LogicException("Точка входа ".$entry." не существует");
			exit();
		}
	}
}
