<?php
namespace Techart\BxApp\Core;

class Auth
{
	/**
	 * Метод получения объекта пользователя
	 *
	 * @return \CUser
	 */
	public function getUser(): \CUser
	{
		global $USER;

		return $USER;
	}

	/**
	 * Метод для проверки авторизации ( true\false )
	 *
	 * @return boolean
	 */
	public function isAuthorized(): bool
	{
		return $this->getUser()->isAuthorized();
	}

	/**
	 * Метод для проверки авторизации ( true\false )
	 *
	 * @return boolean
	 */
	public function isAdmin(): bool
	{
		return $this->getUser()->IsAdmin();
	}

	/**
	 * Метод для проверки у текущего юзера группы developer ( true\false )
	 *
	 * @return boolean
	 */
	public function isDeveloper(): bool
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
	 * Метод получения полей юзера по его Login'у
	 *
	 * @param string $login
	 *
	 * @return array|bool
	 */
	public function getByLogin(string $login = ''): array|bool
	{
		return $this->getUser()->getByLogin($login)->fetch();
	}

	/**
	 * Метод получения полей юзера по его E-Mail
	 *
	 * @param string $email
	 *
	 * @return array|bool
	 */
	public function getByEmail(string $email = ''): array|bool
	{
		return $this->getUser()->GetList([], [], ['EMAIL' => $email])->fetch();
	}

	/**
	 * Метод получения полей юзера по его id
	 *
	 * @param int $id
	 *
	 * @return array|bool
	 */
	public function getByID(int $ID = 0): array|bool
	{
		return $this->getUser()->GetList([], [], ['ID' => $ID], ['SELECT' => ['UF_*']])->fetch();
	}

	/**
	 * Метод получения ID текущего пользователя
	 *
	 * @return int
	 */
	public function getUserId(): int
	{
		return intval($this->getUser()->GetId());
	}

	/**
	 * Метод проверки активности текущего пользователя
	 *
	 * @return bool
	 */
	public function checkActive(): bool
	{
		$curUser = $this->getUser()->GetById($this->getUserId())->fetch();

		return $curUser === false || $curUser['ACTIVE'] !== 'Y' ? false : true;
	}
}
