<?php
namespace Techart\BxApp\Base\Routes;


use \Bitrix\Main\Application;


class BaseRoutesController
{
	use \ResultTrait;
	use \ErrorTrait;
	use \CacheTrait;


	protected $request = []; // объект битрикс getRequest()
	protected $args = []; // параметры вызова контроллера в роуте



	/**
	 * Метод записывает в переменную класса $request текущий объект битрикс реквеста
	 * Application::getInstance()->getContext()->getRequest()
	 *
	 * @return void
	 */
	public function collectRequest(): void
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
	 * Метод для разруливания кастомных и дефолтных экшенов
	 *
	 * @param array $args
	 * @return mixed
	 */
	public function baseAction()
	{
		if (!empty(\App::getRoute('where'))) {
			$i = 1;
			foreach (\App::getRoute('where') as $name => $v) {
				$this->args[$name] = \App::getRoute('args')[$i];
				$i++;
			}
		}

		$this->collectRequest();

		return call_user_func_array(array($this, \App::getRoute('method')), \App::getRoute('args'));
	}
}
