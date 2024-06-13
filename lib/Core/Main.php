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
	public function checkDomain():bool
	{
		return $_SERVER['HTTP_HOST'] == $_SERVER['SERVER_NAME'] ? true : false;
	}

	/**
	 * Обновляет текущую страницу
	 *
	 * @return void
	 */
	public function doRefresh()
	{
		header('Location: '.$_SERVER['REQUEST_URI']);
		exit();
	}

	/**
	 * Отдаёт 404 статус
	 *
	 * @return void
	 */
	public function do404()
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
	public function call404()
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
	public function callFile(string $callableFile = '')
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
	public function doRedirect(string $redirectUrl = '', bool $redirectSkipSecurityCheck = false, string $redirectStatus = '302 Found')
	{
		LocalRedirect($redirectUrl, $redirectSkipSecurityCheck, $redirectStatus);
		exit();
	}

	/**
	 * Возвращает из реквеста объект с самыми подходящими параметрами
	 * В $method можно конкретно указать откуда брать пременные
	 *
	 * @param string $method
	 * @return void
	 */
	public function getCurRequest(string $method = 'post')
	{
		$request = Application::getInstance()->getContext()->getRequest();
		$values = [];

		if ($method == 'post') {
			if ($request->isJson()) {
				$values = $request->getJsonList();
			} else {
				$values = $request->getPostList();
			}
		} else {
			$values = $request->getQueryList();
		}

		return $values;
	}

	/**
	 * Возвращает текущий IP посетителя, проверяет несколько
	 *
	 * @return string
	 */
	public function getCurrentClientIP(): string
	{
		$ip = '';

		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} else {
			if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} else {
				$ip = $_SERVER['REMOTE_ADDR'];
			}
		}

		return $ip;
	}
}
