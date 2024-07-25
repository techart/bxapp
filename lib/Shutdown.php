<?
namespace Techart\BxApp;


/**
 * Класс для обработки шатдаун эвента php.
 * Регистрируется в App.php
 */

class Shutdown
{
	static function register(): void
	{
		register_shutdown_function(function () {
			if (class_exists('Techart\BxApp\Shutdown')) {
				Shutdown::run();
			}
		});
	}

	static function run(): void
	{
		// обработка всего переданного в логер: отправка почты, запись в файл и т.д.
		\Logger::final(); // всегда последний
	}
}
