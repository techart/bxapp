<?php
/**
 * Базовый класс валидатора уже расширен некоторыми общими удобными правилами
 * Например, для проверки рекапчи
 * Эти правила написаны в /App/Core/Validator/Validator.php метод customRules()
 *
 * Но можно добавить и свои уникальные для сайта
 * Добавляется в этот трейт в методе myRules()
 * По примеру из customRules() будет как-то так:
 *
* 	$validator->extend(
		'awesome_rule',
		function($attribute, $value, $parameters) {
			return $value == 'awesome';
		},
		'Поле :attribute должно иметь значение - "awesome"!!!'
	);

	Функция проверки должна возвращать true|false

	Разумеется, так же текст ошибки можно написать в локализации - /Localization/Validator/ru/validation.php:
	'awesome_rule' => 'Поле :attribute должно иметь значение - "awesome"!!!',

	А сама проверка будет писаться так:
	'name' => 'required|awesome_rule',
 */


trait ValidatorTrait
{
	/**
	 * Дополнительные правила для валидатора - особенные для проекта
	 *
	 * @param object $validator
	 * @return void
	 */
	protected function myRules(object $validator):void
	{

	}
}
