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
		'setup:app' => 'setup',
		'setup:template' => 'setupTemplate',
		'cache:clear' => 'clearCache',
		'create:model' => 'createModel',
		'create:cli' => 'createCli',
		'create:bundle' => 'createBundle',
		'create:middlewareAfter' => 'createMiddlewareAfter',
		'create:middlewareBefore' => 'createMiddlewareBefore',
		'create:sitemap' => 'createSitemap',
		'openapi:create' => 'createOpenAPI',
	);
	static $siteId = '';
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
		self::$siteId = $argv[1];
		$isSetSiteId = true;

		if (!empty(BXAPP_REGISTRY_SITES[self::$siteId])) {
			self::$action = $argv[2];
			self::$options = array_slice($argv, 3);
		} else {
			self::$siteId = BXAPP_SITE_ID;

			if (strpos($argv[2], '_') !== false || strpos($argv[2], ':') !== false) {
				self::$action = $argv[2];
				self::$options = array_slice($argv, 3);
			} else {
				$isSetSiteId = false;
				self::$action = $argv[1];
				self::$options = array_slice($argv, 2);
			}
		}

		// Вызов дефолтного действия, если такое есть
		if (isset(self::$defaultActions[self::$action])) {
			call_user_func_array(
				[new CliActions, self::$defaultActions[self::$action]],
				[self::$options, self::$siteId, $isSetSiteId]
			);
		} else {
			$classMethod = explode('_', self::$action);

			if (count($classMethod) == 2) {
				$className = $classMethod[0];
				$methodName = $classMethod[1];
				$methodArgs = self::$options;

				if (!empty($className) && !empty($methodName)) {
					$classFile = APP_CLI_DIR.'/'.$className.'.php';

					if (file_exists($classFile)) {
						require_once($classFile);

						$className = "Site\\Cli\\".str_replace("/", "\\", $className);

						if (class_exists($className)) {
							$curClass = new $className;

							if (method_exists($curClass, $methodName)) {
								call_user_func_array([$curClass, $methodName], [$methodArgs, self::$siteId, $isSetSiteId]);
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
