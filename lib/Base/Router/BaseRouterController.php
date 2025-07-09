<?php
namespace Techart\BxApp\Base\Router;


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
use Symfony\Component\HttpFoundation\File\UploadedFile;

class BaseRouterController
{
	use \ResultTrait;
	use \ErrorTrait;
	use \CacheTrait;


	protected $request = []; // объект битрикс getRequest()
	protected $args = []; // параметры вызова контроллера в роуте
	// NOTE: Удалить если доступ к моделям через $this->models в экшене роутера точно не нужен
	// protected $models = []; // модели роута



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
	 * Возвращает массив загруженных через форму временных файлов - $_FILES
	 * Массив форматируется для проверки файлов через кор сервис Validator
	 *
	 * Ключ - имя поля, значение - объект Symfony\Component\HttpFoundation\File\UploadedFile
	 *
	 * @return array
	 */
	public function getUploadedFiles(): array
	{
		$uploadedFiles = [];
		$curFiles = $this->request->getFileList();

		if (count($curFiles) > 0) {
			foreach ($curFiles as $k => $v) {
				if (is_array($v['name'])) {
					foreach ($v['name'] as $key => $val) {
						if (isset($v['tmp_name'][$key]) && !empty($v['tmp_name'][$key])) {
							$uploadedFiles[$k][$key] = new UploadedFile($v['tmp_name'][$key], $v['name'][$key], $v['type'][$key]);
						}
					}
				} else {
					if (isset($v['tmp_name']) && !empty($v['tmp_name'])) {
						$uploadedFiles[$k] = new UploadedFile($v['tmp_name'], $v['name'], $v['type']);
					}
				}
			}
		}
		return $uploadedFiles;
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

		if ($method == 'get' && defined('BXAPP_ROUTER_CURRENT_REQUEST_QUERY')) {
			$values = BXAPP_ROUTER_CURRENT_REQUEST_QUERY;
		} else {
			$values = \App::core('Main')->getCurRequestValues($method);
		}

		if (count($values) > 0) {
			foreach ($values as $k => $v) {
				$props[$k] = $v;
			}
		}

		return $props;
	}

	/**
	 * Возвращает массив ВСЕХ переменных текущего реквеста (включая файлы из $_FILES)
	 * Делает array_merge для $this->getValues() и $this->getUploadedFiles()
	 *
	 * По умолчанию выбирает текущий тип запроса, но в $method можно указать конкретный
	 * Запросы типов put, delete, options должны быть в json формате.
	 *
	 * @param string $method
	 * @return array
	 */
	public function getFullValues(string $method = ''): array
	{
		return array_merge($this->getValues($method), $this->getUploadedFiles());
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

		// NOTE: Удалить если доступ к моделям через $this->models в экшене роутера точно не нужен
		/*foreach (\App::getRoute('models') as $k => $v) {
			$curModel = \App::model($v, true);

			if (is_numeric($k)) {
				$k = str_replace('/', '', $v);
			}
			$this->models[$k] = $curModel;
		}*/

		// $fullActionArgs = array_merge($this->args, $this->models);

		$this->collectRequest();

		return call_user_func_array(array($this, \App::getRoute('method')), \App::getRoute('args'));
	}
}
