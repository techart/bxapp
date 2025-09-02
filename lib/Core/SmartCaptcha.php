<?php
namespace Techart\BxApp\Core;

/**
 * Класс для проверки правильного заполнения яндекс смарт капчи
 *
 * Для начала, как всё настроить:
 *
 * 1) В .env файле надо заполнить поля:
 * APP_SMARTCAPTCHA_SITE_KEY и APP_SMARTCAPTCHA_SECRET_KEY
 *
 */

class SmartCaptcha
{

	/**
	 * возвращает true если включена проверка - APP_CAPTCHA_CHECK_LOCAL в .env
	 * и если при этом текущий запрос с локалки (intranet)
	 *
	 * @return boolean
	 */
	public function checkLocal():bool
	{
		$return = false;

		if (\Glob::get('APP_CAPTCHA_CHECK_LOCAL', true)) {
			if (\H::isLocalHost()) {
				$return = true;
			}
		}

		return $return;
	}

	private function check(string $token = ''):array
	{
		$args = http_build_query([
			'secret' => \Glob::get('APP_SMARTCAPTCHA_SECRET_KEY'),
			'token' => $token,
			"ip" => $_SERVER['REMOTE_ADDR'],
		]);

		$verify = curl_init();
		curl_setopt($verify, CURLOPT_URL, "https://smartcaptcha.yandexcloud.net/validate?".$args);
		curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($verify, CURLOPT_TIMEOUT, 1);
		$response = curl_exec($verify);
		$httpcode = curl_getinfo($verify, CURLINFO_HTTP_CODE);
		curl_close($verify);

		if ($httpcode === 200) {
			$response = json_decode($response, true);
		} else {
			$response = ['status' => 'failed', 'message' => 'код ответа '.$httpcode];
		}

		/*
		{"status":"failed","message":"Invalid or expired Token."}
		*/

		return $response;
	}


	/**
	 * Возвращает true, если пройдена проверка smartCaptcha, а иначе false
	 *
	 * @param string $token
	 * @return boolean
	 */
	public function checkSmart(string $token = ''):bool
	{
		$return = false;

		if ($this->checkLocal() === true) {
			$response = $this->check($token);

			if ($response["status"] !== "ok") {
				\Logger::debug('smartCaptcha - коды ошибок с сервера: '.(!empty($response["message"]) ? $response["message"] : 'РОБОТ!!!'));
			} else {
				$return = true;
			}
		} else {
			$return = true;
		}

		return $return;
	}
}
