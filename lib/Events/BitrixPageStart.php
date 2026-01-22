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
			// урлы начинающиеся с /bitrix/ или /upload/ никогда не участвуют в BxApp редиректах
			if (strpos($_SERVER['REQUEST_URI'], '/bitrix/') !== 0 && strpos($_SERVER['REQUEST_URI'], '/upload/') !== 0) {
				if (\Config::get('Redirects.APP_SITE_REDIRECTS_GET_PARAMS', false) === true) {
					$query = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
					$curUrl = urldecode(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH) . (!empty($query) ? '?' . $query : ''));
				} else {
					$curUrl = urldecode(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH));
				}

				// редирект с большого регистра на маленький
				if (\Config::get('Redirects.APP_SITE_REDIRECTS_TO_LOWER', false) === true) {
					if ($curUrl != mb_strtolower($curUrl)) {
						\App::core('Main')->doRedirect(mb_strtolower($curUrl), false, '301');
						exit();
					}
				}

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
}
