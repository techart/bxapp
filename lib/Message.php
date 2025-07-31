<?php
namespace Techart\BxApp;

/**
 * Для получения данных из языковых файлов в единной точке входа
 * Файлы хранятся в Localization/Messages/
 * В дефолтном виде там такая структура:
 * Имеется папка Default, в ней две папки языков: en, ru, в каждой файл messages.php
 * В файле messages.php просто return ассоциативного массива
 *
 * ______________
 *
 * Самый простой вариант:
 *
 * \M::get('TEST') - ищет в Localization/Messages/Default/TBA_LANGUAGE_ID/messages.php и вернёт значение из ключа TEST
 *
 * TBA_LANGUAGE_ID - текущий язык BxApp
 *
 * Можно рядом создать файл не messages.php, а, например, buttons.php
 *
 * И обращаться к нему так:
 *
 * \M::get('buttons.CALL_ME_TEXT')
 *
 * Можно создать папку отличную от Default и любую вложенность внутри.
 * Тогда обратиться можно будет так:
 *
 * \M::get('Site/Buttons::main.CALL_ME_TEXT')
 * В примере выше ищется файл Localization/Messages/Site/Buttons/TBA_LANGUAGE_ID/main.php и в нём ключ CALL_ME_TEXT
 *
 * \M::get('Site/Buttons::CALL_ME_TEXT')
 * В примере выше ищется файл Localization/Messages/Site/Buttons/TBA_LANGUAGE_ID/messages.php и в нём ключ CALL_ME_TEXT
 *
 * Если путь к файлу найти не удалось, то возвращается указанный текст.
 *
 * ______________
 *
 * ВТОРЫМ параметром можно передать масив подстановок в текст:
 *
 * \M::get('Это просто текст с заменой :this фигни', ['this' => 'этой'])

 * Получится: "Это просто текст с заменой этой фигни"
 *
 * Разумеется текст в файлах тоже можно писать с заменами
 *
 * ______________
 *
 * ТРЕТЬИМ параметром можно передать число для функции плюрализации
 * В общем, число на основе которого можно сделать простое условие для выбора текста
 *
 * \M::get('Одно яблоко|Много яблок', [], 1)
 * Получится: "Одно яблоко"
 *
 * \M::get('Одно яблоко|Много яблок', [], 2)
 * Получится: "Много яблок"
 *
 * Более сложные условия:
 *
 * \M::get('{1} :value минута назад|[2,4] :value минуты назад|[5,*] :value минут назад', ['value' => 5], 5)
 * Получится: "5 минут назад"
 *
 * ______________
 *
 * ЧЕТВЁРТЫМ параметром можно принудительно указать язык: ru|en и т.д.
 *
 * \M::get('buttons.CALL_ME_TEXT', [], 0, 'en')
 *
 * В этом случае ищется в Localization/Messages/Default/en/buttons.php
 *
 * _____________
 *
 * Замены можно указывать не только вторым параметром, но и в файлах по пути Localization/Replacements
 * Это будут общие замены для всего сайта
 *
 * Полный список замен составляется из:
 * замены из Localization/Replacements + второй параметр \M::get()
 *
 * Массив из \M::get() перетирает одноимённые ключи из Localization/Replacements
 *
 */


use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;


class Message
{
	private static $messagesPath = '/Messages';
	private static $translatorData = [];


	/**
	 * Разбирает переданную строку $name в массив с ключами для:
	 *
	 * namespace - папка относительно Localization (по дефолту Default)
	 * group - имя файла с текстами (по дефолту messages.php)
	 * key - ключ в массиве с текстами (по дефолту пустая строка)
	 *
	 * @param string $name
	 * @return array
	 */
	private static function parseName(string $name = ''): array
	{
		$parse = [
			'namespace' => 'Default',
			'group' => 'messages.',
			'key' => '',
		];

		if (!empty($name)) {
			if (strpos($name, '::') !== false) {
				$namespace = explode('::', $name);
				$parse['namespace'] = $namespace[0];

				if (strpos($namespace[1], '.') !== false) {
					$group = explode('.', $namespace[1]);
					$parse['group'] = $group[0].'.';
					$parse['key'] = $group[1];
				} else {
					$parse['key'] = $namespace[1];
				}
			} else {
				if (strpos($name, '.') !== false) {
					$group = explode('.', $name);
					$parse['group'] = $group[0].'.';
					$parse['key'] = $group[1];
				} else {
					$parse['key'] = $name;
				}
			}
		}

		return $parse;
	}

	/**
	 * Составляет массив замен для строк локализаций
	 *
	 * Полный список замен составляется из:
	 * замены из Localization/Replacements + второй параметр \M::get()
	 *
	 * Массив из \M::get() перетирает одноимённые ключи из Localization/Replacements
	 *
	 * @param array $replace
	 * @param string $lang
	 * @return array
	 */
	private static function getReplacements(array $replace = [], string $lang = 'ru'): array
	{
		$fullReplacements = [];
		$file = TBA_APP_LOCALIZATION_DIR.'/Replacements/'.$lang.'/replace.php';

		if ($file !== false) {
			$fileReplacements = include_once($file);

			if (is_array($fileReplacements)) {
				$fullReplacements = array_merge($fileReplacements, $replace);
			} else {
				$fullReplacements = $replace;
			}
		}

		return $fullReplacements;
	}

	/**
	 * Возвращает значение для $name
	 * С подстановками из $replace
	 * $number - число для функции плюрализации
	 * По умолчанию $locale = TBA_LANGUAGE_ID
	 *
	 * Если $name не существует, то возвращает пустую строку
	 *
	 * @param string $name
	 * @param array $replace
	 * @param int $number
	 * @param string $locale
	 * @return string
	 */
	public static function get(string $name = '', array $replace = [], int $number = 0, string $locale = ''): string
	{
		$curLang = !empty($locale) ? $locale : TBA_LANGUAGE_ID;
		$parse = self::parseName($name);
		$curTranslatorName = $parse['namespace'].'-'.$parse['group'].'-'.$curLang;
		$curReplacements = self::getReplacements($replace, $curLang);

		if (!isset(self::$translatorData[$curTranslatorName])) {
			$string = '';
			$translationDir = TBA_APP_LOCALIZATION_DIR.self::$messagesPath.'/'.$parse['namespace'].'/';
			$fileLoader = new FileLoader(new Filesystem(), $translationDir);
			$fileLoader->addNamespace('lang', $translationDir);
			$fileLoader->load($curLang, $parse['group'], 'lang');
			$translator = new Translator($fileLoader, $curLang);
			$translator->setLocale($curLang);
			$translator->setFallback($curLang);

			// сохраняем уже загруженный ранее файл
			self::$translatorData[$curTranslatorName] = $translator;
		}

		// если указанный ключ присутствует по указанному адресу
		if (self::$translatorData[$curTranslatorName]->has($parse['group'].$parse['key'])) {
			$string = self::$translatorData[$curTranslatorName]->choice($parse['group'].$parse['key'], $number, $curReplacements);
		} else {
			// если указанного ключ не найден, то обрабатываем как строку
			$string = self::$translatorData[$curTranslatorName]->choice($parse['key'], $number, $curReplacements);
		}

		return $string;
	}
}
