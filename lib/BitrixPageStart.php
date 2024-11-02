<?php
namespace Techart\BxApp;

/**
 * Класс для обработки OnPageStart эвента битрикса.
 * Регистрируется в App.php
 */

class BitrixPageStart
{
	public static function setup()
	{
		AddEventHandler("main", "OnPageStart", ['Techart\BxApp\BitrixPageStart', 'OnPageStart'], 1);
	}


	/**
	 * Метод вызывается на эвенте OnPageStart
	 *
	 * @return void
	 */
	public static function OnPageStart(): void
	{
		self::siteRedirects();
	}

	/**
	 * Если Redirects.APP_SITE_REDIRECTS_ACTIVE = true
	 * то делает редиректы на основе заполненного Redirects.APP_SITE_REDIRECTS
	 *
	 * Для ключа pattern:
	 *
	 * если в начале адреса поставить собачку - "@...", то страница будет проверяться через strpos
	 * нужно для указания всех страниц с общим началом (разводящая, вложенная и т.д.)
	 *
	 * если в конце адреса поставить собачку - "...@", то это будет соответствовать всем вложенным сраницам
	 * (исключая базовую)
	 *
	 * если обёрнуто в "|...|", то это регулярка
	 * если в ключе "url" будут подстановки через $, то они заменятся на соответствующие значения из паттерна
	 *
	 * если @ и регулярок нет, то сравнивается строка как есть
	 *
	 * @return void
	 */
	public static function siteRedirects(): void
	{
		if (Config::get('Redirects.APP_SITE_REDIRECTS_ACTIVE', false) === true) {
			$curUrl = $_SERVER['REQUEST_URI'];

			foreach(Config::get('Redirects.APP_SITE_REDIRECTS', []) as $v) {
				$match = false;
				$url = $v['url'];

				// если это регулярка (ограничена палками - |)
				if (strpos($v['pattern'], '|') === 0) {
					// если совпадает
					if (preg_match($v['pattern'], $curUrl, $matches) === 1) {
						if ($matches > 1) {
							// делаем замены, если они нужны
							foreach ($matches as $key => $val) {
								$url = str_replace('$'.$key, $val, $url);
							}
						}
						$match = 0;
					}
				}

				$dog = strpos($v['pattern'], '@');
				if ($dog !== false) {
					if ($dog === 0) {
						// если в начале адреса есть "@", то проверяем через strpos
						$match = stripos($curUrl, str_replace('@', '', $v['pattern']));
					}
					if ($dog > 0) {
						$curPattern = str_replace('@', '', $v['pattern']);

						// если не в начале адреса есть "@", то надо исключить точное совпадение
						if ($curPattern != $curUrl) {
							$match = stripos($curUrl, $curPattern);
						}
					}
				}

				// если точное соответствие
				if ($v['pattern'] == $curUrl) {
					$match = 0;
				}

				if ($match !== false) {
					// совпасть должно с начала, а не где-то с середины
					if ($match === 0) {
						App::core('Main')->doRedirect($url, false, $v['status']);
						exit();
					}
				}
			}
		}
	}
}
