<?php

/**
 * Файл в котором заполняются параметры для сваггера для каждого роута бандла
 * 
 * summary - краткое описание
 * description - полное описание
 * operationId - уникальный идентификатор пути
 * requestBody - тело запроса
 * parameters - Параметры в роуте (GET параметры и обязательные параметры урла типа /{id}/)
 * responses - описание ответов роута
 */

return [
	// 'name-route' => [
	// 	'summary' => '',
	// 	'description' => '',
	// 	'operationId' => '',
	// 	'parameters' => [
	// 		[
	// 			'name' => 'test',
	// 			'in' => 'query',
	// 			'required' => false,
	// 			'schema' => [
	// 				'type' => 'string'
	// 			]
	// 		]
	// 	],
	// 	'responses' => [
	// 		'200' => [
	// 			'description' => 'ok',
	//			'content' => [
	//				'application/json' => [
	//					'schema' => [
	//						'bxappResult' => true, // Нужно ли подставлять обёртку из resultTrait (если true, засунет данные из ключа data в ключ result)
	//						'data' => [ // Данные отдаваемые роутом
	//							'$ref' => '#/components/schemas/Test'
	//						]
	//					]
	//				]
	//			]
	// 		]
	// 	]
	// ],
];