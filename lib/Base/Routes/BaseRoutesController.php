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
	 * Каждый экшн должен заканчиваться ретурном данных через этот метод.
	 *
	 * @param mixed $data
	 * @return string
	 */
	public function return(mixed $data = []): string
	{
		return $this->jsonResponse($data);
	}

	/**
	 * - устанавливает заголовок application/json
	 * - форматирует ответ в json_encode
	 *
	 * @param mixed $data
	 * @return string
	 */
	protected function jsonResponse($data)
	{
		header('Content-Type: application/json');
		return json_encode($data);
	}


	/**
	 * Метод для разруливания кастомных и дефолтных экшенов
	 *
	 * @param array $args
	 * @return mixed
	 */
	public function baseAction()
	{
		$i = 1;
		foreach (\App::getRouteData('where') as $name => $v) {
			$this->args[$name] = \App::getRouteData('args')[$i];
			$i++;
		}

		$this->collectRequest();

		return call_user_func_array(array($this, \App::getRouteData('method')), \App::getRouteData('args'));
	}
}
