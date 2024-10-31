<?php
/**
 * Класс для работы с https://yookassa.ru/
 */

use YooKassa\Client;

class YooKassa
{
	use BuildResultTrait;


	protected $client = null;
	protected $exception = null;


	/**
	 * Подключиться к Юкассе
	 *
	 * @return void
	 */
	protected function setClient(): void
	{
		if ($this->client === null) {
			$client = new Client();

			try {
				$this->client = $client->setAuth(
					intval(\Env::get('APP_YOOKASSA_SHOP_ID', 0)),
					\Env::get('APP_YOOKASSA_SECRET_KEY', '')
				);
			} catch (\Exception $e) {
				$this->exception = $e;
			}
		}
	}

	/**
	 * Возвращает или объект Юкасы new Client() для дальнейшей работы
	 * Или массив трейта buildResult с ошибкой
	 *
	 * @return mixed
	 */
	public function getClient()
	{
		$this->setClient();

		if ($this->client !== null && $this->exception === null) {
			return $this->client;
		} else {
			\Logger::error('YooKassa: не удалось авторизоваться - "'.$this->exception->getMessage().'"');
			return $this->buildResult(true, $this->exception->getMessage());
		}
	}

	/**
	 * Проверяет является ли входящий ip один из Yookassa Notification IP
	 * Если $ip = '', то берётся Helpers::getRealIp()
	 *
	 * @param string $ip
	 * @return boolean
	 */
	public function isNotificationIPTrusted(string $ip = '')
	{
		$client = $this->getClient();

		if (!empty($ip)) {
			$curIP = $ip;
		} else {
			$curIP = \Helpers::getRealIp();
		}

		return $client->isNotificationIPTrusted($curIP);
	}
}
