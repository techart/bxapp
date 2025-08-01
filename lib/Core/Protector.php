<?php
namespace Techart\BxApp\Core;

/**
 * Защитник от несанкционированного доступа
 *
 * Вызывается с конкретным правилом или со списком правил
 * Можно выбрать действие по результату проверки:
 * - вернуть true/false
 * - выполнить битриксовую 404
 * - выдать заголовок 404 и exit
 * - сделать редирект на урл
 * - вызвать файл
 * - перезагрузить страницу
 *
 * Примеры:
 *
 * App::core('Protector')->checkDomain()->go() - проверить домен (вернёт true/false)
 * App::core('Protector')->setRules(['checkDomain', 'checkSession'])->go() - проверить домен и сессию (вернёт true/false)
 * App::core('Protector')->checkDomain()->do404(true)->go() - проверить домен (при ошибке - заголовок 404 и exit)
 * App::core('Protector')->checkDomain()->call404(true)->go() - проверить домен (при ошибке - битрикс 404)
 *
 * Если методам проверки передать первым параметром true, то будет включен реверс режим.
 * То же самое можно сделать в setRules(), если перед именем метода поставить восклицательный знак.
 *
 * App::core('Protector')->checkDomain(true)->go() - проверить домен на оборот (вернёт true/false)
 * App::core('Protector')->setRules(['!checkDomain'])->go() - проверить домен на оборот (вернёт true/false)
 *
 * Собственные правила можно добавлять через Traits/ProtectorTrait.php
 */


class Protector
{
	use \ProtectorTrait;

	protected $rules; // правила
	protected $friend; // результат проверки правил - друг нам посетитель или нет
	protected $call404; // вызвать 404 с помощью битрикса
	protected $doRefresh; // перезагрузить текущую страницу
	protected $do404; // на прямую вызвать 404 или нет
	protected $doRedirect; // сделать редирект - LocalRedirect()
	protected $redirectUrl; // урл для редиректа
	protected $redirectSkipSecurityCheck; // пропустить проверку безопасности
	protected $redirectStatus; // статус редиректа (302 по дефолту)
	protected $callFile; // вызвать файл
	protected $callableFile; // адрес вызываемого файла


	public function __construct ()
	{
		$this->setDefaultValues();
	}

	/**
	 * Установливает переменным дефолтные значения.
	 * Если данный метод есть в классе, то он будет вызван в App до ретурна инстанса
	 * Для очистки переменных в классах с чейн вызовом
	 * Как вариант можно вызывать класс с переданным вторым параметром false - тогда будет возвращён чистый инстанс
	 *
	 * @return void
	 */
	public function setDefaultValues(): void
	{
		$this->rules = null;
		$this->friend = null;
		$this->call404 = false;
		$this->doRefresh = false;
		$this->do404 = false;
		$this->doRedirect = false;
		$this->redirectUrl = '/';
		$this->redirectSkipSecurityCheck = false;
		$this->redirectStatus = "302 Found";
		$this->callFile = false;
		$this->callableFile = '/404.php';
	}

	/**
	 * Смотрит пройдена ли проверка рекапчи checkV2()
	 *
	 * @param bool $reverse
	 * @return object
	 */
	public function checkRecaptchaV2(bool $reverse = false): object
	{
		$values = \App::core('Main')->getCurRequestValues();
		$check = \App::core('Recaptcha')->checkV2($values['g-recaptcha-response']);

		$this->setFriendship($check, $reverse);

		return $this;
	}

	/**
	 * Смотрит пройдена ли проверка рекапчи checkV3()
	 *
	 * @param bool $reverse
	 * @return object
	 */
	public function checkRecaptchaV3(bool $reverse = false): object
	{
		$values = \App::core('Main')->getCurRequestValues();
		$check = \App::core('Recaptcha')->checkV3($values['g-recaptcha-response']);

		$this->setFriendship($check, $reverse);

		return $this;
	}

	/**
	 * Смотрит пройдена ли проверка капчи checkSmart()
	 *
	 * @param bool $reverse
	 * @return object
	 */
	public function checkSmartCaptcha(bool $reverse = false): object
	{
		$values = \App::core('Main')->getCurRequestValues();
		$check = \App::core('SmartCaptcha')->checkSmart($values['smart-token']);

		$this->setFriendship($check, $reverse);

		return $this;
	}

	/**
	 * Проверяет совпадает ли домен запроса с доменом сервера
	 *
	 * @param bool $reverse
	 * @return object
	 */
	public function checkDomain(bool $reverse = false): object
	{
		$check = \App::core('Main')->checkDomain();

		$this->setFriendship($check, $reverse);

		return $this;
	}

	/**
	 * Проверяет совпадает ли домен запроса с доменом сервера
	 *
	 * @param bool $reverse
	 * @return object
	 */
	public function checkSpam(bool $reverse = false): object
	{
		$curRoute = \App::getRoute();
		$code = 'protector-check-spam';

		if (!empty($curRoute)) {
			$code = 'route-'.$curRoute['bundle'].(isset($curRoute['name']) ? '-'.$curRoute['name'] : '');
		}

		$check = \App::core('StopSpam')->checkAndBan(strtolower($code));

		$this->setFriendship($check, $reverse);

		return $this;
	}

	/**
	 * Проверяет есть ли активная сессия или нет
	 *
	 * @param bool $reverse
	 * @return object
	 */
	public function checkSession(bool $reverse = false): object
	{
		$check = \App::core('Session')->checkSession();

		$this->setFriendship($check, $reverse);

		return $this;
	}

	/**
	 * Проверяет есть ли установленный $_COOKIE['PHPSESSID']
	 *
	 * @param bool $reverse
	 * @return object
	 */
	public function checkPhpSessidIsActive(bool $reverse = false): object
	{
		$check = \App::core('Session')->phpSessidIsActive();

		$this->setFriendship($check, $reverse);

		return $this;
	}

	/**
	 * Проверяет является ли установленный $_COOKIE['PHPSESSID'] корректным
	 *
	 * @param bool $reverse
	 * @return object
	 */
	public function checkPhpSessidIsCorrect(bool $reverse = false): object
	{
		$check = \App::core('Session')->phpSessidIsCorrect();

		$this->setFriendship($check, $reverse);

		return $this;
	}

	/**
	 * Проверяет помечена ли сессия как destroyed
	 *
	 * @param bool $reverse
	 * @return object
	 */
	public function checkSessionIsDestroyed(bool $reverse = false): object
	{
		$check = \App::core('Session')->isDestroyed();

		$this->setFriendship($check, $reverse);

		return $this;
	}

	/**
	 * Проверяет сессия помеченная как destroyed ($_SESSION['destroyed']) всё ещё активна?
	 * Время жизни destroyed сессии в конфиге - Auth.APP_SESSION_ACTIVE_TIME_AFTER_DESTROYED
	 *
	 * @param bool $reverse
	 * @return object
	 */
	public function checkSessionIsAlive(bool $reverse = false): object
	{
		$check = \App::core('Session')->isAlive();

		$this->setFriendship($check, $reverse);

		return $this;
	}

	/**
	 * Проверяет есть активная bitrix_sessid или нет
	 *
	 * @param bool $reverse
	 * @return object
	 */
	public function checkBitrixSessid(bool $reverse = false): object
	{
		$check = \App::core('Session')->checkBitrixSessid();

		$this->setFriendship($check, $reverse);

		return $this;
	}

	/**
	 * Проверяет активен текущий посетитель или нет
	 *
	 * @param bool $reverse
	 * @return object
	 */
	public function checkActive(bool $reverse = false): object
	{
		$check = \App::core('Auth')->checkActive();

		$this->setFriendship($check, $reverse);

		return $this;
	}

	/**
	 * Проверяет у текущего юзера наличие группы developer
	 *
	 * @param bool $reverse
	 * @return object
	 */
	public function checkDeveloper(bool $reverse = false): object
	{
		$check = boolval(\App::core('Auth')->isDeveloper());

		$this->setFriendship($check, $reverse);

		return $this;
	}

	/**
	 * Проверяет админ посетитель или нет
	 *
	 * @param bool $reverse
	 * @return object
	 */
	public function checkAdmin(bool $reverse = false): object
	{
		$check = boolval(\App::core('Auth')->IsAdmin());

		$this->setFriendship($check, $reverse);

		return $this;
	}

	/**
	 * Проверяет авторизован посетитель или нет
	 *
	 * @param bool $reverse
	 * @return object
	 */
	public function checkAuth(bool $reverse = false): object
	{
		$check = boolval(\App::core('Auth')->isAuthorized());

		$this->setFriendship($check, $reverse);

		return $this;
	}

	/**
	 * Проверяет, что значение TBA_REQUEST_TYPE равно public
	 *
	 * @param bool $reverse
	 * @return object
	 */
	public function checkRequestTypePublic(bool $reverse = false): object
	{
		$check = \App::core('Session')->isRequestTypePublic();

		$this->setFriendship($check, $reverse);

		return $this;
	}

	/**
	 * Проверяет, что значение TBA_REQUEST_TYPE равно mixed
	 *
	 * @param bool $reverse
	 * @return object
	 */
	public function checkRequestTypeMixed(bool $reverse = false): object
	{
		$check = \App::core('Session')->isRequestTypeMixed();

		$this->setFriendship($check, $reverse);

		return $this;
	}

	/**
	 * Проверяет, что значение TBA_REQUEST_TYPE равно secure
	 *
	 * @param bool $reverse
	 * @return object
	 */
	public function checkRequestTypeSecure(bool $reverse = false): object
	{
		$check = \App::core('Session')->isRequestTypeSecure();

		$this->setFriendship($check, $reverse);

		return $this;
	}

	/**
	 * Проверяет, что запрос помечен как secure - isRequestTypeSecure(), а так же
	 * что сессия: phpSessidIsActive(), phpSessidIsCorrect() и isAlive()
	 *
	 * @param bool $reverse
	 * @return object
	 */
	public function checkSecure(bool $reverse = false): object
	{
		if (
			\App::core('Session')->isRequestTypeSecure() &&
			\App::core('Session')->phpSessidIsActive() &&
			\App::core('Session')->phpSessidIsCorrect() &&
			\App::core('Session')->isAlive()
		) {
			$check = true;
		} else {
			$check = false;
		}

		$this->setFriendship($check, $reverse);

		return $this;
	}

	/**
	 * Назначает массив правил для проверки
	 * Включает реверс правилу чьё название начинается с !
	 *
	 * @param array $rules
	 * @return object
	 */
	public function setRules(array $rules = []): object
	{
		$this->rules = $rules;

		return $this;
	}

	/**
	 * Если $do404 = true то при провале проверок назначается header 404 и делается exit
	 *
	 * @param boolean $do404
	 * @return object
	 */
	public function do404(bool $do404 = false): object
	{
		$this->do404 = $do404;

		return $this;
	}

	/**
	 * Если $doRefresh = true то при провале проверок делается рефреш текущей страницы
	 *
	 * @param boolean $do404
	 * @return object
	 */
	public function doRefresh(bool $doRefresh = false): object
	{
		$this->doRefresh = $doRefresh;

		return $this;
	}

	/**
	 * Если $call404 = true то при провале проверок делается битриксовый process404
	 *
	 * @param boolean $call404
	 * @return object
	 */
	public function call404(bool $call404 = false): object
	{
		$this->call404 = $call404;

		return $this;
	}

	/**
	 * Если $doRedirect = true то при провале проверок происходит битрикс process404 на указанный файл
	 *
	 * @param boolean $doRedirect
	 * @param boolean $url
	 * @param boolean $skipSecurityCheck
	 * @param string $status
	 * @return object
	 */
	public function callFile(bool $callFile = false, string $callableFile = '/404.php'): object
	{
		$this->callFile = $callFile;
		$this->callableFile = $callableFile;

		return $this;
	}

	/**
	 * Если $doRedirect = true то при провале проверок происходит битрикс LocalRedirect
	 *
	 * @param boolean $doRedirect
	 * @param boolean $url
	 * @param boolean $skipSecurityCheck
	 * @param string $status
	 * @return object
	 */
	public function doRedirect(bool $doRedirect = false, string $url = '/', bool $skipSecurityCheck = false, string $status = '302 Found'): object
	{
		$this->doRedirect = $doRedirect;
		$this->redirectUrl = $url;
		$this->redirectSkipSecurityCheck = $skipSecurityCheck;
		$this->redirectStatus = $status;

		return $this;
	}

	/**
	 * Запускает проверку доступа
	 * Если проверка пройдена, то возвращает true
	 * Если прроверка не пройдена, то:
	 * Если установлены call404, do404, doRefresh, doRedirect или callFile то запускаются они
	 * А иначе возвращается false
	 *
	 * @return mixed
	 */
	public function go(): mixed
	{
		if ($this->rules !== null) {
			if (!empty($this->rules)) {
				$this->checkRules();
			} else {
				return true;
			}
		}

		\Logger::info('Protector: friend = '.$this->friend);
		if (!$this->friend) {
			if ($this->callFile) {
				\Logger::info('Protector callFile(): '.$this->callableFile);
				\App::core('Main')->callFile($this->callableFile);
			}
			if ($this->call404) {
				\Logger::info('Protector call404()');
				\App::core('Main')->call404();
			}
			if ($this->do404) {
				\Logger::info('Protector do404()');
				\App::core('Main')->do404();
			}
			if ($this->doRefresh) {
				\Logger::info('Protector doRefresh()');
				\App::core('Main')->doRefresh();
			}
			if ($this->doRedirect) {
				\Logger::info('Protector doRedirect(): '.$this->redirectUrl);
				\App::core('Main')->doRedirect($this->redirectUrl, $this->redirectSkipSecurityCheck, $this->redirectStatus);
			}
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Записывает в $this->friend прошла проверка (с учётом реверса) или нет
	 *
	 * @param boolean $check
	 * @param boolean $reverse
	 * @return void
	 */
	protected function setFriendship(bool $check = false, bool $reverse = false): void
	{
		// если предыдущие проверки прошли
		if ($this->friend !== false) {
			if ($reverse) {
				$this->friend = $check ? false : true;
			} else {
				$this->friend = $check;
			}
		}
	}

	/**
	 * Запускает на проверку установленные правила
	 * Проверяется по порядку. Первое непройденное правило завершает проверку
	 *
	 * @return void
	 */
	protected function checkRules(): void
	{
		$curMethod = '';
		$reverse = false;

		if (!empty($this->rules)) {
			foreach ($this->rules as $method) {
				if (strpos($method, '!') === false) {
					// не включаем реверс
					$curMethod = $method;
					$reverse = false;
				} else {
					// включаем реверс если название метода начинается с !
					$curMethod = str_replace('!', '', $method);
					$reverse = true;
				}

				if (method_exists($this, $curMethod)) {
					call_user_func([$this, $curMethod], $reverse);

					if ($this->friend === false) {
						break;
					}
				}
			}
		}
	}

	/**
	 * Проверяет является ли входящий ip один из Yookassa Notification IP
	 *
	 * @param bool $reverse
	 * @return object
	 */
	public function checkYookassaNotificationIP(bool $reverse = false): object
	{
		if (\App::service('YooKassa')->isNotificationIPTrusted()) {
			$check = true;
		}
		else {
			$check = false;
		}

		$this->setFriendship($check, $reverse);

		return $this;
	}

	/**
	 * Проверяет не устарел ли ID сессии PHPSESSID в куках
	 *
	 * @return bool
	 */
	public function checkNextSessionID(): bool
	{
		if ($_COOKIE['PHPSESSID'] !== session_id()) {
			return false;
		}

		return true;
	}
}
