<?php
/**
 * В этом трейте пишутся все хелперы сайта
 *
 * Будет доступно как Help::methodName()
 *
 * Это не отменяет стандартный BxApp хелпер H, а дополняет его.
 * H - библиотечный хелпер.
 * Help - хелпер для самостоятельного творчества.
 */


trait HelpTrait
{
	/**
	 * Вызывается в стандартном \H::isLocalHost() до дефолтного кода
	 * Чтобы заменить поведение метода
	 * Возвращает true, если intranet
	 * Возвращает false в противном случае
	 *
	 * @return boolean
	 */
	// public static function bxappIsLocalHost(): bool
	// {

	// }

	/**
	 * Вызывается в стандартном \H::isTestHost() до дефолтного кода
	 * Чтобы заменить поведение метода
	 * Возвращает true, если test
	 * Возвращает false в противном случае
	 *
	 * @return boolean
	 */
	// public static function bxappIsTestHost(): bool
	// {

	// }

	/**
	 * Вызывается в стандартном \H::isDevHost() до дефолтного кода
	 * Чтобы заменить поведение метода
	 * Возвращает true, если intranet или testz
	 * Возвращает false в противном случае
	 *
	 * @return boolean
	 */
	// public static function bxappIsDevHost(): bool
	// {

	// }
}
