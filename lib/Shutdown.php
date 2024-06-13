<?
namespace Techart\BxApp;


register_shutdown_function(function () {
	if (class_exists('Techart\BxApp\Shutdown')) {
		Shutdown::run();
	}
});

/**
 * Класс для обработки шатдаун эвента php.
 * Регистрируется в App.php
 */

class Shutdown
{
	static function run()
	{
		// обработка всего переданного в логер: отправка почты, запись в файл и т.д.
		\Logger::final(); // всегда последний
	}
}
