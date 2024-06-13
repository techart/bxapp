<?php
namespace Techart\BxApp\Core;

class Session
{
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
	public function checkBitrixSession():bool
	{
		return check_bitrix_sessid();
	}
}
