<?php
namespace Techart\BxApp\Core;

/**
 * Класс для проверки правильного заполнения гугло рекапчи
 *
 * Для начала, как всё настроить:
 *
 * 1) В конфиге www/local/php_interface/lib/Configs/App.php надо заполнить поля:
 * APP_RECAPTCHA_SECRET_KEY, APP_RECAPTCHA_SITE_KEY и APP_RECAPTCHA_SCORE (для проверки v3)
 *
 * 2) ВАЖНО - инпут в форме, куда будет записываться значение капчи, должен называться ВСЕГДА g-recaptcha-response !!!!
 *
 *
 * Как правильно добавить рекапчу?
 *
 * Есть несколько способов для рекапчи V3:
 *
	___1 - самый простой (тут поле с именем g-recaptcha-response само подставляется)

	<script async src="https://www.google.com/recaptcha/api.js"></script>
	<button class="g-recaptcha"
	data-sitekey="<?=Config::get('Recaptcha.APP_RECAPTCHA_SITE_KEY')?>"
	data-callback='onSubmit'
	data-action='submit'>Submit</button>


	___2 - добавляем в нужное поле значение руками через grecaptcha.execute

	<script async src="https://www.google.com/recaptcha/api.js?render=<?=Config::get('Recaptcha.APP_RECAPTCHA_SITE_KEY')?>"></script>
	<input type="hidden" name="g-recaptcha-response" id="recaptchaResponse">

	grecaptcha.ready(function () {
		grecaptcha.execute('<?=Config::get('Recaptcha.APP_RECAPTCHA_SITE_KEY')?>', { action: 'contact' }).then(function (token) {
			var recaptchaResponse = document.getElementById('recaptchaResponse');
			recaptchaResponse.value = token;
		});
	});


	___3 - react (в jsx файл ключ придётся вставлять руками, а не брать из конфига)

	script.src = "https://www.google.com/recaptcha/api.js?render=6LftO5onAAAAAKH9Mq-bhNyxFXssd6S9GzAIpq_w"
	data.append('g-recaptcha-response', await Store.greCaptchaToken())


	В рекапче V2 всё просто (g-recaptcha-response само подставится):

	<script async src="https://www.google.com/recaptcha/api.js"></script>
	<div class="g-recaptcha" data-sitekey="<?=Config::get('Recaptcha.APP_RECAPTCHA_SITE_KEY')?>"></div>


	Как всё обработать?

	В модели формы указываем правила валидации полей:

	$errors = $this->validateFormData($formData, [
		'name' => 'required|',
		'g-recaptcha-response' => 'required|recaptchav3', // Или recaptchav2
	]);

	Если локально нужно проверить форму с капчей и нет доступа к её настройкам. То можно просто убрать из правил
	g-recaptcha-response и форма будет работать игнорируя капчу.

	Так же можно в .env файле назначить APP_RECAPTCHA_CHECK_LOCAL=true, тогда рекапчу можно будет игнорировать.
	Но, правда, надо будет в правле для поля рекапчи убрать required.

	Так же проверку можно использовать не только в валидаторе, но и в протекторе, например, в бандле:
	'protector' => ['checkRecaptchaV3'], или 'protector' => ['checkRecaptchaV2'],

	Ну и ни кто не мешает использовать в ручную методы класса checkV2() или checkV3() и передавать туда как-то
	полученный токен.


	Локализация.

	Если не нравится сообщение, которое выскакивает при ошибке, то можно его поменять в
	www/local/php_interface/lib/Localization/Validator/ru/validation.php, пишем свой текст так:

	'recaptchav2' => 'Пройдите очень важный тест reCaptcha !',
	'recaptchav3' => 'Пройдите очень важный тест reCaptcha !',
 */


class Recaptcha
{

	/**
	 * возвращает true если включена проверка - APP_RECAPTCHA_CHECK_LOCAL в .env
	 * и если при этом текущий запрос с локалки (techart)
	 *
	 * @return boolean
	 */
	private function checkLocal():bool
	{
		$return = false;

		if (\Glob::get('APP_RECAPTCHA_CHECK_LOCAL', true)) {
			$host = explode('.', $_SERVER['HTTP_HOST']);

			if (strpos($_SERVER['SERVER_NAME'], '.techart.') !== false) {
				$return = true;
			}
		}

		return $return;
	}

	private function check(string $token = ''):array
	{
		$data = array(
			'secret' => \Config::get('Recaptcha.APP_RECAPTCHA_SECRET_KEY', ''),
			'response' => $token
		);

		$verify = curl_init();
		curl_setopt($verify, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
		curl_setopt($verify, CURLOPT_POST, true);
		curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt($verify, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($verify);
		$response = json_decode($response, true);

		/*$response = [
			"success" => true,
			"challenge_ts" => 234234234,
			"hostname" => 'host',
			"error-codes" => [123, 234],
			"score" => 0.3,
		];*/

		return $response;
	}


	/**
	 * Возвращает true, если пройдена проверка reCaptcha v2, а иначе false
	 *
	 * @param string $token
	 * @return boolean
	 */
	public function checkV2(string $token = ''):bool
	{
		$return = false;

		if ($this->checkLocal() === false) {
			$response = $this->check($token);

			if ($response["success"] === false) {
				\Logger::debug('reCaptcha v2 - коды ошибок с сервера: '.implode('; ', $response["error-codes"]));
			} else {
				$return = true;
			}
		} else {
			$return = true;
		}

		return $return;
	}

	/**
	 * Возвращает true, если пройдена проверка reCaptcha v3, а иначе false
	 *
	 * @param string $token
	 * @return boolean
	 */
	public function checkV3(string $token = ''):bool
	{
		$return = false;

		if ($this->checkLocal() === false) {
			$response = $this->check($token);

			if ($response["success"] === false) {
				\Logger::debug('reCaptcha v3 - коды ошибок с сервера: '.implode('; ', $response["error-codes"]));
			} else {
				$score = floatval(\Config::get('Recaptcha.APP_RECAPTCHA_SCORE', 0.4));

				if ($response['score'] < $score) {
					\Logger::debug('reCaptcha v3 - проверка не пройдена '.$response['score'].' < '.$score);
				} else {
					$return = true;
				}
			}
		} else {
			$return = true;
		}

		return $return;
	}
}
