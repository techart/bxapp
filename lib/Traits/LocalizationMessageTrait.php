<?php
namespace Techart\BxApp\Traits;

/**
 * Трейт для локализации различных сущностей BxApp
 * 
 * По умолчанию подключено в базовых классах моделей инфоблоков и хайлоадблоков.
 * Для остальных сущностей нужно подключать самостоятельно:
 * 
 * use \LocalizationMessageTrait;
 *
 * Берёт шаблоны из APP_LOCALIZATION_MESSAGES_TEMPLATES в конфиге App.php
 * 
 * Вызов локализации:
 * 
 * $this->locMessage('TITLE');
 * 
 * При вызове составляет ключ локализации по заданному шаблону 
 * и прокидывает его в метод get класса Message 
 */


trait LocalizationMessageTrait
{
	/**
	 * По заданному в конфиге шаблону и переданному коду значения достаёт локализацию из папки Localization
	 * 
	 * @param string $code
	 * @return string
	 */
	public function locMessage(string $code = ''): string
	{
		$trace = debug_backtrace();
		$path = explode('/php_interface/', $trace[0]['file'])[1];
		$path = explode('/', $path);
		$path = array_splice($path, 1, count($path) - 2);

		$entity = explode('_', array_shift($path))[0];
		$fileName = basename($trace[0]['file'], '.php');
		$className = isset($trace[1]['class']) && !empty($trace[1]['class']) ? $trace[1]['class'] : '';
		$methodName = isset($trace[1]['function']) && !empty($trace[1]['function']) ? $trace[1]['function'] : '';
		$templates = \Config::get('App.APP_LOCALIZATION_MESSAGES_TEMPLATES', []);
		$classFile = !empty($path) ? $path[0] : $fileName;
		$template = (isset($templates[$entity]) ? $templates[$entity] : '{entity_name}/{first_part}::{class_name}.{method_name}_') . $code;
		preg_match('/^((?P<namespace>.*)::)?((?P<group>.*)\\.)?(?P<key>(.*_)?.*)$/', $template, $matches);

		foreach (['namespace' => '::', 'group' => '.', 'key' => ''] as $param => $postfix) {
			$matches[$param] = str_replace(
				['{entity_name}', '{class_name}', '{method_name}', '{site_id}', '{first_dir}', '{all_dir}', '{first_part}', '{file_name}'],
				[$entity, $className, $methodName, TBA_SITE_ID, $path[0], implode('/', $path), $classFile, $fileName],
				isset($matches[$param]) && !empty($matches[$param]) ? $matches[$param] . $postfix : ''
			);
		}

		return \M::get($matches['namespace'].$matches['group'].$matches['key']);
	}
}
