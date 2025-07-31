<?php
namespace Techart\BxApp;

/**
 * Класс для хранения глобальных переменных
 * Независимый от битрикса и ТАО
 * В отличии от Config можно: задать, получить и изменить везде по коду
 */

class Glob
{
	protected static $values = []; // массив запомненных значений


	public static function setSiteGlobals(): void
	{
		include_once (TBA_APP_ROOT_DIR.'/Configs/SiteGlobals.php');
	}

	/**
	 * Возвращает true если переменная с именем $name существует, а иначе возвращает false
	 *
	 * @param string $name
	 * @return bool
	 */
	public static function has(string $name = ''): bool
	{
		return isset(self::$values[$name]);
	}

	/**
	 * Возвращает полный массив запомненных значений
	 *
	 * @return array
	 */
	public static function all(): array
	{
		return self::$values;
	}

	/**
	 * Возвращает значение переменной с именем $name
	 * Если такой переменной нет, то возвращает $defValue
	 *
	 * @param string $name
	 * @param mixed $defValue
	 * @return mixed
	 */
	public static function get(string $name = '', mixed $defValue = null): mixed
	{
		return isset(self::$values[$name]) ? self::$values[$name] : $defValue;
	}

	/**
	 * Записывает переменную с именем $name и значением $value
	 * Так же можно использовать для обновления значения
	 * Не записывает переменную без указанного значения или со значением null
	 * Пустые строки записываются
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public static function set(string $name = '', mixed $value = null): void
	{
		if ($value !== null) {
			self::$values[$name] = $value;
		}
	}

	/**
	 * Удаляет переменную с именем $name
	 *
	 * @param string $name
	 * @return void
	 */
	public static function unset(string $name = ''): void
	{
		if (isset(self::$values[$name])) {
			unset(self::$values[$name]);
		}
	}
}
