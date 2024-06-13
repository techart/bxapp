<?
namespace Techart\BxApp\Base\Form;

/**
* В данный момент не используется
* Нужно будет переписать полностью для работы с битрикс формами, если оно вообще когда-то будет необходимо
* Работа с формами-инфоблоками будет через модель и трейт FormInfoblockTrait
*/

class BaseForm extends \TAO\Controller {
    /**
     * @uses answers - Массив со стандартными ответами формы
     * переопределите массив или часть массива в дочернем классе при необходимости
    */
	const answers = [
        'wrongInput' => [
            'title' => 'Заявка не была отправлена',
            'text' => 'Неверно заполнено(ы) поле(я)'
        ],
        'wrongError' => [
            'title' => 'Заявка не была отправлена',
            'text' => 'Произошла ошибка'
        ],
        'bot' => [
            'title' => 'Заявка не была отправлена',
            'text' => 'Возможно вы робот'
        ],
        'success' => [
            'title' => 'Заявка отправлена',
            'text' => 'Наши менеджеры свяжутся с вами'
        ]
    ];

    /** Составление json ответа формы
     *
     * @param $error - ошибка (true|false|string)
     * @param $title - заголовок (string)
     * @param $text - текст (string)
     * @param $status - статус (true|false)
     *
     * @return string
     */
    public static function resultToJson($error, $title, $text, $status = true) {
		return json_encode([
			'status' => $status ? 'ok' : false,
			'error' => $error,
			'resultTitle' => $title,
			'resultText' => $text
		]);
	}

    /**
     * Отправка данных с формы в веб-форму
     *
     * @param $names - массив с именами полей из админки веб формы (array)
     * @example $names = ["form_text_170" => $props['name'], "form_text_171" => $props['company']];
     *
     * @param $props - массив с данными c формы (array)
     * @param $webFormId - id веб-формы (int)
     *
     * @return true|false
    */
    public static function sendIntoWebForm($names, $props, $webFormId) {
        $prepere = [];

        foreach($names as $index => $item) {
            $prepere[$item] = $props[$index];
        }

        if(\CModule::IncludeModule("form")) {
			if ($result_id = \CFormResult::Add($webFormId, $prepere)) {
				\CFormResult::Mail($result_id);

				return true;
			}

			return false;
		}

		return false;
    }

    public static function reCaptchaCheck($response, $secret) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

        foreach($ips as $ip) {
            if($ip == '217.170.125.50') {
                return true;
            }
        }

        $data = array(
            'secret' => $secret,
            'response' => $response
        );

        $verify = curl_init();
        curl_setopt($verify, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
        curl_setopt($verify, CURLOPT_POST, true);
        curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($verify, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($verify);

        $response = json_decode($response, true);

        if(!$response['success'])
            return false;
        else
            if($response['score'] < 0.4)
                return false;

        return true;
    }
}
