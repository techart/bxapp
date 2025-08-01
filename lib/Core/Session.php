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
	 * Возвращает true, если установлен и не пуст $_COOKIE['PHPSESSID'], а иначе false
	 *
	 * @return boolean
	 */
	public function phpSessidIsActive():bool
	{
		return isset($_COOKIE['PHPSESSID']) && !empty($_COOKIE['PHPSESSID']) ? true : false;
	}

	/**
	 * Возвращает true, если $_COOKIE['PHPSESSID'] корректен (равен текущему session_id), а иначе false
	 *
	 * @return boolean
	 */
	public function phpSessidIsCorrect():bool
	{
		return $_COOKIE['PHPSESSID'] === session_id() ? true : false;
	}

	/**
	 * Возвращает true, если сессия помечена как destroyed ($_SESSION['destroyed']), а иначе false
	 *
	 * @return boolean
	 */
	public function isDestroyed():bool
	{
		return isset($_SESSION['destroyed']) ? true : false;
	}

	/**
	 * Возвращает true, если сессия помеченная как destroyed ($_SESSION['destroyed']) всё ещё активна
	 * Время жизни destroyed сессии в конфиге - Auth.APP_SESSION_ACTIVE_TIME_AFTER_DESTROYED
	 * А иначе возвращает false
	 *
	 * @return boolean
	 */
	public function isAlive():bool
	{

		return
		(intval($_SESSION['destroyed']) + intval(\Config::get('Auth.APP_SESSION_ACTIVE_TIME_AFTER_DESTROYED', 120))
		 < time()) ? true : false;
	}

	/**
	 * Возвращает true, если значение TBA_REQUEST_TYPE равно mixed
	 * А иначе возвращает false
	 *
	 * @return boolean
	 */
	public function isRequestTypeMixed():bool
	{

		return
		(defined('TBA_REQUEST_TYPE') && TBA_REQUEST_TYPE == 'mixed') ? true : false;
	}

	/**
	 * Возвращает true, если значение TBA_REQUEST_TYPE равно public
	 * А иначе возвращает false
	 *
	 * @return boolean
	 */
	public function isRequestTypePublic():bool
	{

		return
		(defined('TBA_REQUEST_TYPE') && TBA_REQUEST_TYPE == 'public') ? true : false;
	}

	/**
	 * Возвращает true, если значение TBA_REQUEST_TYPE равно secure
	 * А иначе возвращает false
	 *
	 * @return boolean
	 */
	public function isRequestTypeSecure():bool
	{

		return
		(defined('TBA_REQUEST_TYPE') && TBA_REQUEST_TYPE == 'secure') ? true : false;
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

	/**
	 * Создаёт новую сессию с сохранением данных (массив $_SESSION) текущей сессии
	 *
	 * Если удалось переключиться на новую сессию, то возвращает её session_id
	 * Если что-то случилось и не удалось, то возвращает false
	 *
	 * @return boolean|string
	 */
	public function regenerateSessionIdUnstableNetworks(): bool|string
	{
		$regenStatus = false; // указатель удачно ли поднялась новая сессия
		$newSessionId = session_create_id(); // id новой сессии
		$_SESSION['newSessionId'] = $newSessionId; // запомнить id новой сессии в текущую
		$_SESSION['destroyed'] = time(); // запомнить в текущую сесию когда её надо 100% уничтожить

		session_write_close(); // записать и закрыть текущую сессию

		$wasInStrictMode = false;
		if (ini_get('session.use_strict_mode') == 1) {
			// если строгий режим включён, то надо его отключить
			ini_set('session.use_strict_mode', 0);
			// и запомнить это, чтобы потом включить опять
			$wasInStrictMode = true;
		}

		$oldSessionData = $_SESSION; // запомнить данные старой сессии

		session_id($newSessionId); // установить новый session_id
		$regenStatus = $this->regenerateSessionStart(); // старт новой сессии с новым запомненным id

		$_SESSION = $oldSessionData; // записать в новую сессию данные старой
		// в новой сессии эти данные не нужны
		unset($_SESSION['destroyed']);
		unset($_SESSION['newSessionId']);
		unset($oldSessionData);

		session_write_close(); // записать и закрыть текущую сессию (чтобы вызвать ini_set)
		if ($wasInStrictMode) {
			@ini_set('session.use_strict_mode', 1); // восстановить строгий режим
		}

		$regenStatus = $this->regenerateSessionStart(); // возобновляем нужную сессию

		// если удалось переключиться на новую сессию, то возвращает её session_id
		// если что-то случилось и не удалось, то возвращает false
		return $regenStatus ? $newSessionId : false;
	}

	/**
	 * Проверяет текущую сессию на предмет её актуальности - $_SESSION['destroyed']
	 * Если подозрение на взлом, то ошибка и возвращает false
	 * Если устаревшая сессия из-за ошибок сети, то восстанавливает правильную сессию и возвращает true
	 *
	 * @return boolean
	 */
	public function regenerateSessionStart(): bool
	{
		$result = session_start();

		// если текущая сессия помечена как уничтоженная (старая)
		if (isset($_SESSION['destroyed'])) {

			// если сессия запрашивается спустя 5 минут как она должна быть уничтожена
			// это подозрительно, возможно плохой человек, делаем что-то для защиты данных юзера
			if ($this->isAlive()) {
				// возможно, тут разлогин юзеров
				// возможно, тут какой-то лог
				// throw(new DestroyedSessionAccessException); // возможно, выбросим специальную ошибку
				$result = false;
			}

			// если сессия запрашивается раньше 5 минут как она должна быть уничтожена
			// возможно это из-за плохого соединения
			if ($result) {
				// пробуем поднять правильную сессию если она указана
				if (isset($_SESSION['newSessionId'])) {
					session_write_close(); // записать и закрыть текущую сессию
					session_id($_SESSION['newSessionId']); // id новой правильной сессии
					$result = session_start(); // запускаем правильную сессию
				} else {
					// нет указателя на новую сессию - какая-то фигня
					$result = false;
				}
			}
		}

		// если удалось переключиться на новую сессию, то возвращает true
		// если что-то случилось и не удалось, то возвращает false
		return $result;
	}
}
