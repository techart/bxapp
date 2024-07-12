<?php
namespace Techart\BxApp\Base\Routes;


/**
 * Все контроллеры роутов должны наследоватсья от этого класса.
 *
 * Подключаются трейты: ResultTrait, ErrorTrait и CacheTrait
 *
 * В переменной $this->request - текущий объект битрикс getRequest()
 * В переменной $this->args - значения перменных роута
 *
 * Метод $this->getValues() - Возвращает переменные текущий тип запроса, но в $method можно указать конкретный
 * Метод $this->getFiles() - возвращает объект битрикс загруженных через форму временных файлов - $_FILES
 */


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
	 * Возвращает переменные текущего реквеста
	 * По умолчанию выбирает текущий тип запроса, но в $method можно указать конкретный
	 * Запросы типов put, delete, options должны быть в json формате.
	 *
	 * @param string $method
	 * @return array
	 */
	public function getValues(string $method = ''): array
	{
		$props = [];
		$method = !empty($method) ? $method : strtolower($this->request->getRequestMethod());
		$values = \App::core('Main')->getCurRequestValues($method);

		if (count($values) > 0) {
			foreach ($values as $k => $v) {
				$props[$k] = $v;
			}
		}

		return $props;
	}

	/**
	 * Вызывает у класса контроллера роута необходимый метод экшена
	 *
	 * @return mixed
	 */
	public function baseAction(): mixed
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
