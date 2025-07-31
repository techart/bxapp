<?php
namespace Techart\BxApp\Core;

/**
 * Класс, где собраны методы для работы с ассетами в той или иной мере
 */

use \Bitrix\Main\Page\Asset;
use \Bitrix\Main\Page\AssetLocation;

class Assets
{
	use \AssetsTrait;


	private $currentEntryPoints = [];

	/**
	 * Добавляет в тег <head> страницы до подключения скриптов и стилей строку для прелоада каритнки по пути $path
	 *
	 * @param string|null $string
	 * @param string $srcset
	 * @param string $sizes
	 * @return void
	 */
	public function addPreloadImage(string|null $path = '', string $srcset = '', string $sizes = ''): void
	{
		if (!empty($path)) {
			$ext = pathinfo($path, PATHINFO_EXTENSION);
			$imagesrcset = !empty($srcset) ? ' imagesrcset="'.$srcset.'"' : '';
			$imagesizes = !empty($sizes) ? ' imagesizes="'.$sizes.'"' : '';

			Asset::getInstance()->addString('<link rel="preload" as="image" href="'.$path.'" type="image/'.$ext.'"'.$imagesrcset.$imagesizes.'/>', true, AssetLocation::BEFORE_CSS);
		}
	}

	/**
	 * Добавляет произвольную строку кода $string в тег <head> страницы после подключения скриптов и стилей
	 *
	 * Работает через Asset::getInstance()->addString()
	 *
	 * @param string $string
	 * @param string $location
	 * @return void
	 */
	public function addHeadString(string $string = '', string $location = AssetLocation::BODY_END): void
	{
		if (!empty($string)) {
			Asset::getInstance()->addString($string, true, $location);
		}
	}

	/**
	 * Добавляет ссылку $path на скрипт в тег <head> страницы после подключения скриптов и стилей
	 *
	 * Работает через Asset::getInstance()->addJs()
	 *
	 * @param string $path
	 * @return void
	 */
	public function addHeadJs(string $path = ''): void
	{
		if (!empty($path)) {
			Asset::getInstance()->addJs($path, false);
		}
	}

	/**
	 * Добавляет ссылку $path на стиль в тег <head> страницы после подключения скриптов и стилей
	 *
	 * Работает через Asset::getInstance()->addCss()
	 *
	 * @param string $path
	 * @return void
	 */
	public function addHeadCss(string $path = ''): void
	{
		if (!empty($path)) {
			Asset::getInstance()->addCss($path, false);
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
	 * Возвращает массив с именами добавленных точек входа
	 *
	 * @return array
	 */
	public function getCurrentEntryPoints(): array
	{
		return $this->currentEntryPoints;
	}

	/**
	 * Вставляет svg файл инлайном на страницу
	 *
	 * $svgPath - путь к svg файлу (от www сайта)
	 * $params - массив аттрибутов для svg файла (с учётом ригистра)
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
		$svgRealPath = realpath(TBA_SITE_ROOT_DIR.'/'.$svgPath);

		if(file_exists($svgRealPath)) {
			$fileContent = file_get_contents($svgRealPath);

			if ($fileContent !== FALSE) {
				preg_match('|(<svg.*?\</svg>)|s', $fileContent, $match);

				if (count($match) > 0) {
					$svg = new \DOMDocument();

					if ($svg->load($svgRealPath)) {
						foreach ($currentParams as $attr => $value) {
							if (!empty($value)) {
								$svg->getElementsByTagName('svg')[0]->setAttribute($attr, $value);
							}
						}

						return $svg->saveHTML();
					} else {
						\Logger::error('Файл "'.$svgPath.'" не удалось загрузить loadHTML()!');
						return false;
					}
				} else {
					\Logger::error('Файл "'.$svgPath.'" не является svg файлом!');
					return false;
				}

			} else {
				\Logger::error('Файл "'.$svgPath.'" не может быть прочитан!');
				return false;
			}
		} else {
			\Logger::error('Файл "'.$svgPath.'" не найден!');
			return false;
		}
	}
}
