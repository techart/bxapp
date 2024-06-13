<?php
namespace Techart\BxApp\Base\Bundle;

/**
 * Все контроллеры бандлов наследовать от этого класса
 *
 * Есть готовые методы для before и after урлов бандлов.
 * Можно написать по примеру свои в Traits/BundleControllerTrait.php
 * Использовать так:
 *
 * '{^/api/user/authorization/checkLogin/$}' => [
		'action' => 'checkLogin',
		'controller' => 'Auth',
		'before' => 'checkRecaptchaV3|checkDomain',
		'after' => 'clearSession',
	],

 * doBefore запускается сразу после протектора бандлов
 * doAfter запускается прямо перед выводом данных
 *
 * В переменной $this->request - текущий объект битрикс getRequest()
 * В переменной $this->args - все аргументы бандла
 *
 * Метод $this->getValues() - возвращает обработанный текущий post реквест или get, если первый параметр = 'get'
 * Метод $this->getFiles() - возвращает объект битрикс загруженных через форму временных файлов - $_FILES
 */


use \Bitrix\Main\Application;


class BaseBundleController extends \TAO\Controller
{
	use \BundleControllerTrait; // методы для before и after
	use ResultTrait;
	use ErrorTrait;
	use CacheTrait;

	protected $request = []; // объект битрикс getRequest()
	protected $args = []; // параметры вызова контроллера в роуте
	protected $isDomain = false; // результат проверки checkDomain()
	protected $isSession = false; // результат проверки checkSession()
	protected $isRecaptchaV2 = false; // результат проверки checkRecaptchaV2()
	protected $isRecaptchaV3 = false; // результат проверки checkRecaptchaV3()
	protected $isAuth = false; // результат проверки checkAuth()
	protected $isActive = false; // результат проверки checkActive()


	public function clearSession()
	{
		// очистка сессии
	}

	public function checkDomain()
	{
		$this->isDomain = \App::core('Protector')->checkDomain()->go();

		return $this->isDomain;
	}

	public function checkSession()
	{
		$this->isSession = \App::core('Protector')->checkSession()->go();

		return $this->isSession;
	}

	public function checkRecaptchaV2()
	{
		$this->isRecaptchaV2 = \App::core('Recaptcha')->checkV2('13123');

		return $this->isRecaptchaV2;
	}

	public function checkRecaptchaV3()
	{
		$this->isRecaptchaV3 = \App::core('Recaptcha')->checkV3('13123');

		return $this->isRecaptchaV3;
	}

	public function checkAuth()
	{
		$this->isAuth = App::core('Protector')->checkAuth()->go();

		return $this->isAuth;
	}

	public function checkActive()
	{
		$this->isActive = App::core('Protector')->checkActive()->go();

		return $this->isActive;
	}

	/**
	 * Метод записывает в переменную класса $request текущий объект битрикс реквеста
	 * Application::getInstance()->getContext()->getRequest()
	 *
	 * @return void
	 */
	private function collectRequest(): void
	{
		$this->request = Application::getInstance()->getContext()->getRequest();
	}

	/**
	 * Синоним для $this->request->getFileList()
	 * Возвращает объект битрикс загруженных через форму временных файлов - $_FILES
	 *
	 * @return void
	 */
	public function getFiles(): object
	{
		return $this->request->getFileList();
	}

	/**
	 * Возвращает текущий post реквест
	 * Или get, если $method = 'get'
	 *
	 * @param string $method
	 * @return array
	 */
	public function getValues(string $method = 'post'): array
	{
		$props = [];
		$values = \App::core('Main')->getCurRequest($method);

		if (count($values) > 0) {
			foreach ($values as $k => $v) {
				$props[$k] = $v;
			}
		}

		return $props;
	}

	/**
	 * Запускаем методы перечисленные в роуте в args['before'] - строка с | в качестве рзделителя
	 *
	 * @return void
	 */
	private function doBefore(): void
	{
		$this->collectRequest(); // вызывается всегда и первым

		if (isset($this->args['before']) && !empty($this->args['before'])) {
			$curMethods = explode('|', $this->args['before']);

			foreach ($curMethods as $method) {
				if (method_exists($this, $method)) {
					call_user_func([$this, $method]);
				}
			}
		}
	}

	/**
	 * Запускаем методы перечисленные в роуте в args['after'] - строка с | в качестве рзделителя
	 *
	 * @return void
	 */
	private function doAfter(): void
	{
		if (isset($this->args['args']['after']) && !empty($this->args['args']['after'])) {
			$curMethods = explode('|', $this->args['args']['after']);

			foreach ($curMethods as $method) {
				if (method_exists($this, $method)) {
					call_user_func([$this, $method]);
				}
			}
		}
	}

	/**
	 * Запускаем защитника с массивом проверок заданным в методе bundleProtector бандла
	 * Если хотя бы одна проверка возвращает false, то отдаётся 404 и выполняется exit()
	 * Данные проверки идут ДО проверок в before роута
	 *
	 * @return void
	 */
	private function doBundleProtect(array $methods = []): void
	{
		\App::core('Protector')->setRules($methods)->do404(true)->go();
	}

	/**
	 * Каждый экшн должен заканчиваться ретурном данных через этот метод, который:
	 *
	 * - обрабатывает ключ бандла after
	 * - вызывает noLayout()
	 * - устанавливает заголовок application/json
	 * - форматирует ответ в json_encode
	 *
	 * @param mixed $data
	 * @return string
	 */
	public function return(mixed $data = []): string
	{
		$this->doAfter();

		return $this->jsonResponse($data);
	}

	/**
	 * Метод для разруливания кастомных и дефолтных экшенов
	 *
	 * @param array $args
	 * @return mixed
	 */
	public function baseBundleAction(array $args = [])
	{
		$this->args = $args;
		$curArgs[] = $args;

		$this->doBundleProtect($args['bundleProtector']); // запускаем защиту бандлов
		$this->doBefore();

		return call_user_func_array(array($this, $args['action']), $curArgs);
	}
}
