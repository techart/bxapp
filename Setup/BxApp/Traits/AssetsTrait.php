<?php
/**
 * Трейт для расширения кор сервиса App::core('Assets').
 *
 * В первую очередь нужен для реализации метода setEntryPoints(), через который добавляются на стрницу
 * сборки фронтенда. Данный метод App::core('Assets')->setEntryPoints() вызывается в page.content,
 * а так же просто на страницах.
 *
 * В файле уже присутствует базовая реализация. Нужно внести изменения, если требуется для сайта.
 */


use \Bitrix\Main\Page\Asset;


trait AssetsTrait
{
	/**
	 * Подключает ассеты по переданной точке входа
	 *
	 * @param string $entry
	 * @return void
	 */
	public function setEntryPoints(string $entry): void
	{
		$path = $this->getJsTag($entry, ['defer' => '']);

		if ($path) {
			$this->currentEntryPoints[] = $entry;
			$this->setLibs($entry);

			Asset::getInstance()->addString($path);
			Asset::getInstance()->addString($this->getCssTag($entry));
		} else {
			throw new \LogicException("Точка входа ".$entry." не существует");
			exit();
		}
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
		$file_entry = stristr($file, $entry.':');
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
}
