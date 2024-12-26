<?php
namespace Techart\BxApp;

/**
 * Класс для обработки cli команд.
 *
 * Если команда из списка готовых - перечислены в $defaultActions, то запускает соответствующий метод класса CliActions
 *
 * Если команда самописная, то ищёт её класс в BxApp/Cli.
 */

class Cli
{
	static $defaultActions = array(
		'app_setup' => 'setup',
		'app_setupTemplate' => 'setupTemplate',
		'app_clearCacheRouter' => 'clearCacheRouter',
		'app_createModel' => 'createModel',
		'app_createCli' => 'createCli',
		'app_createBundle' => 'createBundle',
		'app_createMiddlewareAfter' => 'createMiddlewareAfter',
		'app_createMiddlewareBefore' => 'createMiddlewareBefore',
		'app_createSitemap' => 'createSitemap',
	);
	static $action = '';
	static $options = [];


	/**
	 * Разбирает cli команды на дефолтные и кастомные
	 *
	 * @param array $argv
	 * @return void
	 */
	public static function run($argv = []): void
	{
		self::$action = $argv[1];
		self::$options = array_slice($argv, 2);

		// Вызов дефолтного действия, если такое есть
		if (isset(self::$defaultActions[self::$action])) {
			call_user_func_array(
				[new CliActions, self::$defaultActions[self::$action]],
				self::$options
			);
		} else {
			$classMethod = explode('_', $argv[1]);

			if (count($classMethod) == 2) {
				$className = $classMethod[0];
				$methodName = $classMethod[1];
				$methodArgs = array_slice($argv, 2);

				if (!empty($className) && !empty($methodName)) {
					$classFile = APP_CLI_DIR.'/'.$className.'.php';

					if (file_exists($classFile)) {
						require_once($classFile);

						$className = "Site\\Cli\\".$className;

						if (class_exists($className)) {
							$curClass = new $className;

							if (method_exists($curClass, $methodName)) {
								call_user_func_array([$curClass, $methodName], $methodArgs);
							} else {
								// echo 'В классе '.$className.' не найден метод: '.$methodName.PHP_EOL;
								Logger::warning('В классе '.$className.' не найден метод: '.$methodName);
								throw new \LogicException('В классе '.$className.' не найден метод: '.$methodName);
								exit();
							}
						} else {
							// echo 'Не найден класс: '.$className.PHP_EOL;
							Logger::warning('Не найден класс: '.$className);
							throw new \LogicException('Не найден класс: '.$className);
							exit();
						}
					} else {
						// echo 'Не найден файл: '.$classFile.PHP_EOL;
						Logger::warning('Не найден файл: '.$classFile);
						throw new \LogicException('Не найден файл: '.$classFile);
						exit();
					}
				} else {
					// echo 'Нужно передать имя файла и метод в нём'.PHP_EOL;
					Logger::warning('Нужно передать имя файла и метод в нём');
					throw new \LogicException('Нужно передать имя файла и метод в нём');
					exit();
				}
			} else {
				// echo 'Нужно передать имя файла и метод в нём'.PHP_EOL;
				Logger::warning('Нужно передать имя файла и метод в нём');
				throw new \LogicException('Нужно передать имя файла и метод в нём');
				exit();
			}
		}
	}
}
