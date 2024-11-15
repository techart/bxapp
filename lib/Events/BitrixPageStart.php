<?php
namespace Techart\BxApp\Events;

/**
 * Класс для обработки OnPageStart эвента битрикса.
 * Регистрируется в App.php
 */

class BitrixPageStart
{
	public static function setup()
	{
		AddEventHandler("main", "OnPageStart", ['\Techart\BxApp\Events\BitrixPageStart', 'OnPageStart'], 1);
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
	 * Для ключа (паттерна):
	 *
	 * если в начале поставить собачку - "@...", то страница будет проверяться через strpos
	 * нужно для указания всех страниц с общим началом (разводящая, вложенная и т.д.)
	 *
	 * если в конце поставить собачку - "...@", то это будет соответствовать всем вложенным сраница (исключая базовую)
	 *
	 * если обёрнуто в "|...|", то это регулярка
	 * если в значении (url) будут подстановки через $, то они заменятся на соответствующие значения из паттерна
	 *
	 * если @ и регулярок нет, то сравнивается строка как есть
	 *
	 * @return void
	 */
	public static function siteRedirects(): void
	{
		if (\Config::get('Redirects.APP_SITE_REDIRECTS_ACTIVE', false) === true) {
			$curUrl = $_SERVER['REQUEST_URI'];

			foreach (\Config::get('Redirects.APP_SITE_REDIRECTS', []) as $status => $redirects) {
				if (count($redirects) > 0) {
					foreach ($redirects as $pattern => $url) {
						$match = false;

						// если это регулярка (ограничена палками - |)
						if (strpos($pattern, '|') === 0) {
							// если совпадает
							if (preg_match($pattern, $curUrl, $matches) === 1) {
								if ($matches > 1) {
									$url = preg_replace($pattern, $url, $curUrl); // делаем замены
								}
								$match = 0;
							}
						}

						$dog = strpos($pattern, '@');
						if ($dog !== false) {
							if ($dog === 0) {
								// если в начале адреса есть "@", то проверяем через strpos
								$match = stripos($curUrl, str_replace('@', '', $pattern));
							}
							if ($dog > 0) {
								$curPattern = str_replace('@', '', $pattern);

								// если не в начале адреса есть "@", то надо исключить точное совпадение
								if ($curPattern != $curUrl) {
									$match = stripos($curUrl, $curPattern);
								}
							}
						}

						// если точное соответствие
						if ($pattern == $curUrl) {
							$match = 0;
						}

						if ($match !== false) {
							// совпасть должно с начала, а не где-то с середины
							if ($match === 0 && !empty($url)) {
								\App::core('Main')->doRedirect($url, false, $status);
								exit();
							}
						}
					}
				}
			}
		}
	}
}
