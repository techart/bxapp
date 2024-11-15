<?
namespace Techart\BxApp\Events;


/**
 * Класс для обработки шатдаун эвента php.
 * Регистрируется в App.php
 */

class Shutdown
{
	static function register(): void
	{
		register_shutdown_function(function () {
			if (class_exists('\Techart\BxApp\Events\Shutdown')) {
				\Techart\BxApp\Events\Shutdown::run();
			}
		});
	}

	static function run(): void
	{
		// обработка всего переданного в логер: отправка почты, запись в файл и т.д.
		\Log::final(); // всегда последний
	}
}
