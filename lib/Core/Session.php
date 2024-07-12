<?php
namespace Techart\BxApp\Core;

class Session
{
	/**
	 * Возвращает объект \Bitrix\Main\Application::getInstance()->getSession();
	 *
	 * @return object
	 */
	public function getSession(): object
	{
		return \Bitrix\Main\Application::getInstance()->getSession();
	}

	/**
	 * Возвращает true, если установлена сессия, а иначе false
	 *
	 * @return boolean
	 */
	public function checkSession():bool
	{
		return isset($_SESSION) && !empty($_SESSION) ? true : false;
	}

	/**
	 * Возвращает true, если прошла проверка check_bitrix_sessid
	 *
	 * @return boolean
	 */
	public function checkBitrixSessid():bool
	{
		return check_bitrix_sessid();
	}

	/**
	 * Возвращает true, если fixed_session_id текущей сессии равен переданному id
	 * А иначе возвращает false
	 *
	 * @param string $sessID
	 *
	 * @return bool
	 */
	public function checkBitrixSessionID(string $sessID = ''): bool
	{
		if (\App::core('Auth')->isAuthorized()) {
			$session = \Bitrix\Main\Application::getInstance()->getSession();

			if ($sessID === $session['fixed_session_id'])
				$result = true; // ID Сессии совпадает
			else
				$result = false; // ID Сессии не совпадают
		} else {
			$result = false; // Пользователь не авторизован
		}

		return $result;
	}
}
