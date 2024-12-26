<?php
namespace Techart\BxApp\Core;

/**
 * Общие глобальные методы, которые не выгодно выносить в отдельный класс сервиса
 */

use \Bitrix\Main\Application;

class Main
{
	/**
	 * Возвращает true, если домен запроса совпадает с доменом сервера, а иначе false
	 *
	 * @return boolean
	 */
	public function checkDomain(): bool
	{
		return $_SERVER['HTTP_HOST'] == $_SERVER['SERVER_NAME'] ? true : false;
	}

	/**
	 * Обновляет текущую страницу
	 *
	 * @return void
	 */
	public function doRefresh(): void
	{
		header('Location: '.$_SERVER['REQUEST_URI']);
		exit();
	}

	/**
	 * Отдаёт 404 статус
	 *
	 * @return void
	 */
	public function do404(): void
	{
		header('HTTP/1.0 404 not found');
		exit();
	}

	/**
	 * Тригерит битриксовую ошибку 404
	 * Работает через process404
	 *
	 * @return void
	 */
	public function call404(): void
	{
		\Bitrix\Iblock\Component\Tools::process404('', true, true, true, false);
		exit();
	}

	/**
	 * Тригерит файл указанный по пути $callableFile
	 * Работает через process404
	 *
	 * @param string $callableFile
	 * @return void
	 */
	public function callFile(string $callableFile = ''): void
	{
		\Bitrix\Iblock\Component\Tools::process404('', false, false, true, $callableFile);
		exit();
	}

	/**
	 * Делает редирект на адрес $redirectUrl со статусом $redirectStatus
	 * Используется битриксовый метод LocalRedirect
	 *
	 * @param string $redirectUrl
	 * @param boolean $redirectSkipSecurityCheck
	 * @param string $redirectStatus
	 * @return void
	 */
	public function doRedirect(string $redirectUrl = '', bool $redirectSkipSecurityCheck = false, string $redirectStatus = '302 Found'): void
	{
		LocalRedirect($redirectUrl, $redirectSkipSecurityCheck, $redirectStatus);
		exit();
	}

	/**
	 * Возвращает из реквеста массив переменных для post запроса
	 * В $method можно указать конкретный тип запроса
	 * Запросы типов put, delete, options должны быть в json формате.
	 *
	 * @param string $method
	 * @return array
	 */
	public function getCurRequestValues(string $method = 'post'): array
	{
		$request = Application::getInstance()->getContext()->getRequest();
		$method = strtolower($method);
		$values = [];

		if ($method == 'post') {
			if ($request->isJson()) {
				$values = $request->getJsonList()->toArray();
			} else {
				$values = $request->getPostList()->toArray();
			}
		}
		if ($method == 'get') {
			$values = $request->getQueryList()->toArray();
		}
		if ($request->isJson() ?? ($method == 'delete' || $method == 'put' || $method == 'options')) {
			$values = json_decode(file_get_contents('php://input'), true);
		}

		return $values;
	}

	/**
	 * - устанавливает заголовок application/json
	 * - форматирует ответ в json_encode
	 *
	 * @param mixed $data
	 * @return string
	 */
	public function jsonResponse(mixed $data = ''): string
	{
		// header('Content-Type: application/json');
		return json_encode($data);
	}
}
