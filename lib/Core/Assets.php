<?php
namespace Techart\BxApp\Core;

/**
 * Класс, где собраны методы для работы с ассетами в той или иной мере
 */

use \Bitrix\Main\Page\Asset;
use \Bitrix\Main\Page\AssetLocation;

class Assets
{
	/**
	 * Добавляет в тег <head> страницы до подключения скриптов и стилей строку для прелоада каритнки по пути $path
	 *
	 * @param string $string
	 * @return void
	 */
	public function addPreloadImage(string $path = ''): void
	{
		if (!empty($path)) {
			$ext = pathinfo($path, PATHINFO_EXTENSION);

			Asset::getInstance()->addString('<link rel="preload" as="image" href="'.$path.'" type="image/'.$ext.'"/>', true, AssetLocation::BEFORE_CSS);
		}
	}

	/**
	 * Добавляет произвольную строку кода $string в тег <head> страницы после подключения скриптов и стилей
	 *
	 * @param string $string
	 * @return void
	 */
	public function addHeadString(string $string = ''): void
	{
		if (!empty($string)) {
			Asset::getInstance()->addString($string, true, AssetLocation::BODY_END);
		}
	}

	/**
	 * Возвращает код шрифтов для подключения в header.php
	 * Код заполняется в Configs/Assets.php - APP_ASSETS_FONT_FACE_CODE
	 *
	 * @return string
	 */
	public function showFontFace(): string
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
		return \Config::get('Assets.APP_ASSETS_DIR', $GLOBALS['APPLICATION']->GetTemplatePath('assets/')).$name;
	}

	/**
	 * Возвращает код шрифтов для подключения в header.php
	 * Код заполняется в Configs/Assets.php - APP_ASSETS_FAVICON_CODE
	 *
	 * @return string
	 */
	public function showFaviconHtmlCode(): string
	{
		return \Config::get('Assets.APP_ASSETS_FAVICON_CODE', '');
	}

	/**
	 * Возвращает массив с аттребутами для тега подключения ассетов (script, link)
	 * Возвращает false, если нет файла сборки
	 *
	 * Метод проверяет, если SITE_TEMPLATE_ID == '.default', то принудительно смотрит в шаблоне "site",
	 * в противном случае текущий GetTemplatePath()
	 *
	 * @param string $item
	 * @param array $attrs
	 * @param string $type
	 * @return array|bool
	 */
	private function buildTagAttrs(string $item = '', array $attrs = [], string $type = ''): array|bool
	{
		$pathToAssets = SITE_TEMPLATE_ID == '.default' ? SITE_ROOT_DIR.'/local/templates/site/frontend/assets/' : SITE_ROOT_DIR.$GLOBALS['APPLICATION']->GetTemplatePath('frontend/assets/');
		$pathToBuildJson = realpath($pathToAssets . '/' . \Glob::get('APP_ENV', 'dev') . '.json');

		if ($pathToBuildJson === false) {
			return false;
		} else {
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

		return !$jsTag ? '' : '<script src="' . $jsTag[0] . '" ' . $jsTag[1] . ' ></script>';
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

		return !$cssTag ? '' : '<link href="' . $cssTag[0] . '" ' . $cssTag[1] . ' media="screen" rel="stylesheet" >';
	}

	/**
	 * Подключает js библиотеки указанные в user.settings для переданной точки входа
	 *
	 * Метод проверяет, если SITE_TEMPLATE_ID == '.default', то принудительно смотрит в шаблоне "site",
	 * в противном случае текущий GetTemplatePath()
	 *
	 * @param string $entry
	 * @return void
	 */
	private function setLibs(string $entry): void
	{
		$file = SITE_TEMPLATE_ID == '.default' ? file_get_contents(SITE_ROOT_DIR.'/local/templates/site/frontend/user.settings.js') : file_get_contents(SITE_ROOT_DIR.$GLOBALS['APPLICATION']->GetTemplatePath('frontend').'/user.settings.js');
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
			throw new \LogicException("Точка входа ".$entry." не существует");
			exit();
		}
	}

	/**
	 * Вставляет svg файл инлайном на страницу
	 *
	 * $svgPath - путь к svg файлу (от www сайта)
	 * $params - массив аттрибутов для svg файла
	 *
	 * App::core('Assets')->inlineSvg(H::getAssetUrl('/svg/bottom-text__img.svg'))
	 * App::core('Assets')->inlineSvg('/upload/test/bottom-text__img.svg')
	 *
	 * @param string $svgPath
	 * @param array $params
	 * @return string|bool
	 */
	public static function inlineSvg(string $svgPath = '', array $params = []): string | bool
	{
		$defaultParams = [
			'class' => '',
			'viewBox' => '',
			'width' => '',
			'height' => '',
		];
		$currentParams = array_merge($defaultParams, $params);
		$svgRealPath = realpath(SITE_ROOT_DIR.'/'.$svgPath);

		if(file_exists($svgRealPath)) {
			$fileContent = file_get_contents($svgRealPath);

			if ($fileContent !== FALSE) {
				preg_match('|(<svg.*?\</svg>)|s', $fileContent, $match);

				if (count($match) > 0) {
					$svg = new DOMDocument();

					if ($svg->loadHTML($fileContent)) {
						$svgElem = $svg->getElementsByTagName('svg');

						foreach ($currentParams as $attr => $value) {
							if (!empty($value)) {
								$svgElem[0]->setAttribute(strtolower($attr), $value);
							}
						}

						return $svg->saveHTML();
					} else {
						Logger::error('Файл "'.$svgPath.'" не удалось загрузить loadHTML()!');
						return false;
					}
				} else {
					Logger::error('Файл "'.$svgPath.'" не является svg файлом!');
					return false;
				}

			} else {
				Logger::error('Файл "'.$svgPath.'" не может быть прочитан!');
				return false;
			}
		} else {
			Logger::error('Файл "'.$svgPath.'" не найден!');
			return false;
		}
	}
}
