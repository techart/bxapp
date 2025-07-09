<?php

/**
 * Файл в котором заполняются параметры роута и сваггера для каждого роута бандла
 * 
 * data - Различные значения, которые нужно передать роуту по примеру 'ключ' => 'значение'
 * openapi - Параметры роута для сваггера
 * 
 * Параметры сваггера:
 * 
 * summary - краткое описание
 * description - полное описание
 * operationId - уникальный идентификатор пути
 * requestBody - тело запроса
 * parameters - Параметры в роуте (GET параметры и обязательные параметры урла типа /{id}/)
 * responses - описание ответов роута
 */

return [
	//'name-route' => [
	//	'data' => [ // Данные для роута
	//		'key' => 'value'
	//	]
	//	'openapi' => [ // Данные для Open API файла
	//		'summary' => '',
	//		'description' => '',
	//		'operationId' => '',
	//		'parameters' => [
	//			[
	//				'name' => 'test',
	//				'in' => 'query',
	//				'required' => false,
	//				'schema' => [
	//					'type' => 'string'
	//				]
	//			]
	//		],
	//		'responses' => [
	//			'200' => [
	//				'description' => 'ok',
	//				'content' => [
	//					'application/json' => [
	//						'schema' => [
	//							'bxappResult' => true, // Нужно ли подставлять обёртку из resultTrait (если true, засунет данные из ключа data в ключ result)
	//							'data' => [ // Данные отдаваемые роутом
	//								'$ref' => '#/components/schemas/Test'
	//							]
	//						]
	//					]
	//				]
	// 			]
	//	 	]
	//	]
	// ],
];