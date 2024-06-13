<?php
namespace Techart\BxApp\Core;

class Auth
{
	/**
	 * Метод получения объекта пользователя
	 *
	 * @return object
	 */
	public function getUser()
	{
		global $USER;

		return $USER;
	}

	/**
	 * Метод для проверки авторизации ( true\false )
	 *
	 * @return boolean
	 */
	public function isAuthorized()
	{
		return $this->getUser()->isAuthorized();
	}

	/**
	 * Метод для проверки авторизации ( true\false )
	 *
	 * @return boolean
	 */
	public function isAdmin()
	{
		return $this->getUser()->IsAdmin();
	}

	/**
	 * Метод для проверки у текущего юзера группы developer ( true\false )
	 *
	 * @return boolean
	 */
	public function isDeveloper()
	{
		$return = false;
		$developerGroupCode = 'developer';
		$result = \Bitrix\Main\UserGroupTable::getList(array(
			'filter' => ['USER_ID'=>$this->getUser()->GetID(),'GROUP.ACTIVE'=>'Y'],
			'select' => ['GROUP_ID','GROUP_CODE'=>'GROUP.STRING_ID'],
			'order' => ['GROUP.C_SORT'=>'ASC'],
		));

		while ($arGroup = $result->fetch()) {
			if ($arGroup['GROUP_CODE'] == $developerGroupCode) {
				$return = true;
				break;
			}
		}

		return $return;
	}

	/**
	 * Метод проверки на существование сессии с определённым id
	 *
	 * @param string $sessID
	 *
	 * @return string
	 */
	public function checkBitrixSesID($sessID = '')
	{
		if (self::isAuthorized()) {
			$session = \Bitrix\Main\Application::getInstance()->getSession();
			if ($sessID === $session['fixed_session_id'])
				$result = 'ID Сессии совпадает';
			else
				$result = 'ID Сессии не совпадают';
		} else {
			$result = 'Пользователь не авторизован';
		}

		return $result;
	}

	/**
	 * Метод получения полей юзера по его Login'у
	 *
	 * @param string $login
	 *
	 * @return array
	 */
	public function getByLogin($login)
	{
		return $this->getUser()->getByLogin($login)->fetch();
	}

	/**
	 * Метод получения полей юзера по его E-Mail
	 *
	 * @param string $email
	 *
	 * @return array
	 */
	public function getByEmail($email)
	{
		return $this->getUser()->GetList([], [], ['EMAIL' => $email])->fetch();
	}

	/**
	 *
	 */
	public function getByID($ID)
	{
		return $this->getUser()->GetList([], [], ['ID' => $ID], ['SELECT' => ['UF_*']])->fetch();
	}

	/**
	 * Метод получения ID текущего пользователя
	 *
	 * @return object
	 */
	public function getUserId()
	{
		return $this->getUser()->GetId();
	}

	/**
	 * Метод проверки активности текущего пользователя
	 *
	 * @return array
	 */
	public function checkActive()
	{
		$user = $this->getUser();
		$userId = $this->getUserId();

		return $user->GetById($userId)->fetch();
	}
}
