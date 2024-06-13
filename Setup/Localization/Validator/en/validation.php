<?php
/**
 * Тут можно указать тексты сообщений валидатора для сайта
 *
 * Указывается только то, что является уникальным для данного сайта
 * Полный список текстов с описанием что есть что можно найти в App/Core/Validator/lang/ru/validation.php
 *
 * Если надо что-то изменить/добавить, то копируем/дописываем в этот файл. В App не трогаем
 *
 * Доп переводы можно найти тут: https://github.com/Laravel-Lang/lang/tree/main/locales
 * Можно сначала добавить в App/Core/Validator/lang/
 * А потом, если надо, поменять что-то в Lang/Validator/ как сделано с ru и en
 * А можно новый уникальный язык добавить полностью в Lang/Validator/, а в App вообще не лезть.
 */

return [

	//'accepted' => 'The :attribute field must be accepted.',

	'custom' => [
		'attribute-name' => [
			'rule-name' => 'custom-message',
		],
	],

	'attributes' => [
	],
];
