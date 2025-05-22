<?php

/**
 * Описание общих параметров Open API файла
 * 
 * TITLE - Заголовок
 * DESCRIPTION - Описание
 * VERSION - Версия
 * TAGS - Тэги
 * SCHEMAS - Схемы компонентов
 * 
 * Необязательные параметры:
 * TERMS - Ссылка на страницу с описанием условий обслуживания
 * CONTACT - Связь с разработчиком (ключи - name, url, email)
 * LICENSE - Используемая лицензия (ключи - name, url)
 */

return [
	'TITLE' => 'title', // Заголовок API файла
	'DESCRIPTION' => 'description', // Описание API файла
	'VERSION' => '1.0', // Версия API файла
	'TAGS' => [ // Тэги роутов
		// 'Test' => [
		// 	'description' => 'test'
		// ],
	], // Схемы компонентов
	'SCHEMAS' => [
		// 'Test' => [
		// 	'type' => 'object',
		// 	'properties' => [
		// 		'test' => [
		// 			'type' => 'string',
		// 			'example' => 'data'
		// 		]
		// 	]
		// ]
	]
];