<?php
namespace Techart\BxApp\Core\Validator;

/**
 * Требует пакеты:
 * https://packagist.org/packages/illuminate/validation
 * https://packagist.org/packages/illuminate/translation
 *
	Работает как сервис

	Использовать очень просто.

	$validator = App::core('Validator')->get();
	$check = $validator->make($requestData, [
		'name' => 'required',
		'email' => 'required|email:rfc,dns',
	]);

	if($check->fails()) {
		$errors = $check->errors();

		foreach($errors->all() as $message){
			dd($message);
		}
	}

 * ______ДОБАВЛЕНЫ КАСТОМНЫЕ ПРОВЕРКИ______
 *
 * - проверка рекапчи (правила: recaptchav2 и recaptchav3)
 * - проверки мобильного телефона (правило phone_number) - смотри описание тут в customRules()
 *
 * Доступные правила проверок по дефолту: https://laravel.com/docs/11.x/validation#available-validation-rules
 *
 * В папке lang можно задать переводы для: ошибок, имён атрибутов, а также конкретных ошибок конкретного атрибута
 *
 * Кастомные правила проверок пишутся в двух местах:
 * 1) метод customRules() в этом файле - аппшные
 * 2) метод myRules() в ValidatorTrait - личное творчество
 */


use Illuminate\Validation\Factory;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;

class Validator
{
	use \ValidatorTrait;

	protected $validator = []; // массив экземпляров валидаторов по языкам


	/**
	 * Возвращает инстанс валидатора для указанной локали $locale
	 * Если $locale не указана, то берётся из битрикс константы LANGUAGE_ID
	 *
	 * @param string $locale
	 * @return object
	 */
	public function get(string $locale = ''): object
	{
		$loc = !empty($locale) ? $locale : LANGUAGE_ID;

		if (!$this->$validator[$loc]) {
			$this->$validator[$loc] = new Factory($this->trans($loc));

			if (method_exists($this, 'customRules')) {
				$this->customRules($this->$validator[$loc]);
			}

			if (method_exists($this, 'myRules')) {
				$this->myRules($this->$validator[$loc]);
			}
		}

		return $this->$validator[$loc];
	}

	/**
	 * Дополнительные правила для валидатора, специально для App
	 *
	 * @param Illuminate\Validation\Factory $validator
	 * @return void
	 */
	protected function customRules(\Illuminate\Validation\Factory $validator): void
	{
		$validator->extend('recaptchav2', function($attribute, $value, $parameters)
		{
			return \App::core('Recaptcha')->checkV2($value);
		}, 'Пройдите тест reCaptcha!');

		$validator->extend('recaptchav3', function($attribute, $value, $parameters)
		{
			return \App::core('Recaptcha')->checkV3($value);
		}, 'Пройдите тест reCaptcha!');

		$validator->extend('smartCaptcha', function($attribute, $value, $parameters)
		{
			return \App::core('SmartCaptcha')->checkSmart($value);
		}, 'Пройдите тест smartCaptcha!');

		/**
		 * Распознаёт разные варианты мобильного номера:
		 *
		 * Если в параметре передан "strict" (phone_number:strict), то проверяет точное соответствие маске -
		 * +7 (123) 123-45-67
		 *
		 * Если "strict" не передан, то телефон трактуется очень широко:
		 * +7 (123) 123-45-67; 81231234567; 8 (123) 1234567; 8(123)1234567 и т.д.
		 */
		$validator->extend('phone_number', function($attribute, $value, $parameters)
		{
			if (in_array('strict', $parameters)) {
				return preg_match('/\+7 \([0-9]{3}\) [0-9]{3}-[0-9]{2}-[0-9]{2}/', $value);
			} else {
				return  strlen($value) >= 10 && preg_match('%^(?:(?:\(?(?:00|\+)([1-4]\d\d|[1-9]\d?)\)?)?[\-\.\ \\\/]?)?((?:\(?\d{1,}\)?[\-\.\ \\\/]?){0,})(?:[\-\.\ \\\/]?(?:#|ext\.?|extension|x)[\-\.\ \\\/]?(\d+))?$%i', $value);
			}
		}, 'Значение поля '.$attribute.' должно быть корректным номером телефона!');
	}

	/**
	 * Подключает переводы текстов ошибок переданной локали $locale
	 *
	 * @param string $locale
	 * @return object
	 */
	protected function trans(string $locale = 'ru'): object
	{
		$translationDir = [
			realpath(__DIR__.'/lang'), // языковые App файлы
			APP_LOCALIZATION_DIR.'/Validator', // уникальные языковые файлы сайта
		];
		$fileLoader = new FileLoader(new Filesystem(), $translationDir);
		$fileLoader->addNamespace('lang', $translationDir);
		$fileLoader->load($locale, 'validation', 'lang');
		$translator = new Translator($fileLoader, $locale);
		$translator->setLocale($locale);
		$translator->setFallback($locale);

		return $translator;
	}

	/**
	 * Готовый метод для обработки полей форм. Возвращает массив ошибок, сгруппированными по полям
	 *
	 * Проверка переданных $formData с помощью валидатора по указанным правилам $rules
	 *
	 * Про правила проверок и шаблоны сообщений смотреть тут:
	 * https://laravel.com/docs/10.x/validation#manual-customizing-the-error-messages
	 *
	 * Задание имени атрибутов: ['first_name' => 'Имя']
	 *
	 * @param array $formData
	 * @param array $rules
	 * @param array $attributeNames
	 * @param array $messages
	 * @return array
	 */
	public function validateFormData(array $formData = [], array $rules = [], array $attributeNames = [], array $messages = []): array
	{
		$errors = [];
		$validator = $this->get();
		$check = $validator->make($formData, $rules, $messages);
		$check->setAttributeNames($attributeNames);

		if($check->fails()) {
			$validatorErrors = $check->errors();

			foreach($formData as $k => $v){
				if ($validatorErrors->has($k)) {
					foreach ($validatorErrors->get($k) as $message) {
						$errors[$k][] = $message;
					}
				}
			}
		}

		return $errors;
	}
}
