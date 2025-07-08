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
	'DESCRIPTION' => 'Файл формируется на основе данных в <b>BxApp/Router</b>.</br>
 В файле <b>.env</b> надо заполнить переменную <b>APP_OPENAPI_DOMAIN</b> указав через запятую список доменов (например, локальный и титан).</br>
 Файл формируется при вызове <b>./update.sh</b>. Так же можно сформировать CLI командой: <b>vphp cli.php openapi:create</b>', // Описание API файла
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