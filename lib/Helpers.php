<?php
namespace Techart\BxApp;

/**
 * Класс со всякими полезностями
 */


class Helpers
{
	/**
	 * Вставляет svg файл инлайном на страницу
	 *
	 * $svgPath - путь к свг файлу
	 * $class - строка, которая будет добавлена в параметр class тега svg
	 *
	 * \H::inlineSvg('/upload/test/bottom-text__img.svg')
	 *
	 * @param string $svgPath
	 * @param string $class
	 * @return string|bool
	 */
	public static function inlineSvg(string $svgPath = '', string $class = ''): string | bool
	{
		$base64Decode = false;

		if (strpos($svgPath, 'data:image') !== false) {
			$base64Decode = base64_decode(str_replace('data:image/svg+xml;base64,', '', $svgPath));
		} else {
			$svgPath = realpath(SITE_ROOT_DIR.'/'.$svgPath);

			if (strpos($svgPath, '/www/frontend/')) {
				$svgPath = str_replace('/www/frontend/', '/frontend/', $svgPath);
			}
		}

		if(file_exists($svgPath) or $base64Decode !== false) {
			$fileContent = ($base64Decode !== false ? $base64Decode : file_get_contents($svgPath));

			if ($fileContent !== FALSE) {
				preg_match('|(<svg.*?\</svg>)|s', $fileContent, $match);

				if (count($match) > 0) {
					$curClass = $class!=''?' class="'.$class.'"':'';
					$svgContent = str_replace('<svg', '<svg'.$curClass.'', $match[1]);

					return $svgContent;
				} else {
					var_dump('Файл "'.$svgPath.'" не является svg файлом!');
					return false;
				}

			} else {
				var_dump('Файл "'.$svgPath.'" не может быть прочитан!');
				return false;
			}
		} else {
			var_dump('Файл "'.$svgPath.'" не найден!');
			return false;
		}
	}

	/**
	 * Возвращает base64 представление картинки по адресу $imgPath
	 * Адрес указывать начиная от корня проекта
	 *
	 * @param string $imgPath
	 * @return string
	 */
	public static function imgToBase64(string $imgPath = ''): string
	{
		$path = realpath(PROJECT_ROOT_DIR.'/'.$imgPath);

		if (file_exists($path)) {
			$type = pathinfo($path, PATHINFO_EXTENSION);

			return 'data:image/' . $type . ';base64,' . base64_encode(file_get_contents($path));
		} else {
			\Logger::warning('imgToBase64 - картинка не существует: '.$path);

			return false;
		}
	}

	/**
	 * Первый символ в верхний регистр
	 *
	 * $lowerStrEnd - нужно ли перед этим строку перевести в нижний регистр
	 *
	 * @param string $str
	 * @param string $encoding
	 * @param boolean $lowerStrEnd
	 * @return string
	 */
	public static function ucfirst(string $str = '', string $encoding = "UTF-8", bool $lowerStrEnd = false): string
	{
		$firstLetter = mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding);
		$strEnd = "";

		if ($lowerStrEnd) {
			$strEnd = mb_strtolower(mb_substr($str, 1, mb_strlen($str, $encoding), $encoding), $encoding);
		}
		else {
			$strEnd = mb_substr($str, 1, mb_strlen($str, $encoding), $encoding);
		}
		$str = $firstLetter.$strEnd;

		return $str;
	}

	/**
	 * Из массива $encodingList выбираем самую подходящую кодировку для строки $text и конвертируем её в UTF-8
	 *
	 * @param string $text
	 * @param array $encodingList
	 * @return string
	 */
	public static function convertEncoding(string $text, array $encodingList = ['UTF-8', 'cp1251', 'Windows-1251', 'ASCII']): string
	{
		$goodEncoding = '';

		foreach ($encodingList as $encoding) {
			if (mb_check_encoding($text, $encoding) === true) {
				$goodEncoding = $encoding;
				break;
			}
		}

		return (($goodEncoding != '' && $goodEncoding != 'UTF-8') ? iconv($goodEncoding, 'UTF-8', $text) : $text);
	}

	/**
	 * Удаляет из строки $str BOM
	 *
	 * @param string $str
	 * @return string
	 */
	public static function removeBOM(string $str = ''): string
	{
		if (substr($str, 0, 3) == pack('CCC', 0xef, 0xbb, 0xbf)) {
			$str = substr($str, 3);
		}

		return $str;
	}

	/**
	 * Переводит месяц в русский вариант.
	 *
	 * $month - месяц. Можно указать число или английский вариант: 1, 01, Jan, December...
	 * $case - падеж. Варианты: im, rod. (да, транслитом, ибо фиг его знает как оно на англ...)
	 *
	 * @param mixed $month
	 * @param string $case
	 * @return string
	 */
	public static function convertMonth(mixed $month = 1, string $case = 'im'): string
	{
		$mNumber = date('n', strtotime('1.'.$month.'.2019'));
		$mNameRus = [
			'im' => [1 => 'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
			'rod' => [1 => 'Января', 'Февраля', 'Марта', 'Апреля', 'Мая', 'Июня', 'Июля', 'Августа', 'Сентября', 'Октября', 'Ноября', 'Декабря'],
		];
		if ($mNumber <= 0) {
			$mNumber = 1;
		}
		if ($mNumber > 12) {
			$mNumber = 12;
		}
		$mName = $mNameRus[$case][$mNumber];

		return $mName;
	}

	/**
	 * Возвращает true, если мы на главной странице сайта, false в противном случае
	 *
	 * @return boolean
	 */
	public static function isMain(): bool
	{
		return $GLOBALS['APPLICATION']->GetCurPage(false) === SITE_DIR;
	}

	/**
	 * Возвращает true если личный комп или titan
	 * Возвращает false в противном случае
	 *
	 * @return boolean
	 */
	public static function isDevHost(): bool
	{
		if (strpos($_SERVER['HTTP_HOST'], 'intranet') === false && strpos($_SERVER['HTTP_HOST'], '.projects.') === false) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Возвращает true, если локальная сеть и false в противном случае
	 *
	 * @return boolean
	 */
	public static function isLocal(): bool
	{
		return in_array(\Glob::get('APP_ENV'), ['dev', 'hot']);
	}

	/**
	 * Возвращает код svg картинки по пути $svgPathInfFrontend
	 *
	 * @param string $svgPathInfFrontend
	 * @return string
	 */
	public static function putSvgImageFromFrontend(string $svgPathInfFrontend): string
	{
		return file_get_contents(
			SITE_ROOT_DIR.rtrim($GLOBALS['APPLICATION']->GetTemplatePath('frontend'), '/').'/'.ltrim($svgPathInfFrontend, '/')
		);
	}

	/**
	 * Возвращает ссылку на ассет с именем $name
	 * Короткий синоним для App::core('Assets')->getAssetUrl()
	 *
	 * @param string $name
	 * @return string
	 */
	public static function getAssetUrl(string $name): string
	{
		return \App::core('Assets')->getAssetUrl($name);
	}

	/**
	 * По переданному $fileId возвращает массив с SEO данными файла: alt, title, src, size
	 *
	 * @param int $fileId
	 * @param boolean $withSize
	 * @return array
	 */
	public static function getFileInfo(int $fileId = 0, bool $withSize = false): array
	{
		$file = \CFile::GetFileArray($fileId);

		if (!$file) {
			return false;
		}

		$alt = pathinfo($file['ORIGINAL_NAME'], PATHINFO_FILENAME);

		$result = [
			'alt' => $alt,
			'title' => $alt.' - Фото',
			'src' => $file['SRC'],
		];

		if ($withSize) {
			$result['size'] = \CFile::FormatSize($file['FILE_SIZE']);
		}

		return $result;
	}
}
