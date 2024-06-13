<?php
namespace Techart\BxApp;


class Cli
{
	static $defaultActions = array(
		'app_setup' => 'setup',
		'app_setupTemplate' => 'setupTemplate',
		'app_createModel' => 'createModel',
		'app_createCli' => 'createCli',
		'app_createBundle' => 'createBundle',
	);
	static $action = '';
	static $options = [];


	public static function run($argv = [])
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
								echo 'В классе '.$className.' не найден метод: '.$methodName.PHP_EOL;
							}
						} else {
							echo 'Не найден класс: '.$className.PHP_EOL;
						}
					} else {
						echo 'Не найден файл: '.$classFile.PHP_EOL;
					}
				} else {
					echo 'Нужно передать имя файла и метод в нём'.PHP_EOL;
				}
			} else {
				echo 'Нужно передать имя файла и метод в нём'.PHP_EOL;
			}
		}
	}
}
