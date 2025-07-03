<?php

return [
	'bxapp-default-logger' => [
		'summary' => 'Добавить сообщение в лог',
		'requestBody' => [
			'content' => [
				'multipart/form-data' => [
					'schema' => [
						'type' => 'object',
						'properties' => [
							'type' => [
								'type' => 'string',
								'description' => 'Тип',
								'example' => 'info'
							],
							'text' => [
								'type' => 'string',
								'description' => 'Текст',
								'example' => 'Текст'
							],
						]
					]
				]
			]
		]
	],
	'bxapp-session-getData' => [
		'summary' => 'Получить сохранённые данные из сессии'
	],
	'bxapp-session-updateData' => [
		'summary' => 'Обновить данные в сессии',
		'requestBody' => [
			'content' => [
				'multipart/form-data' => [
					'schema' => [
						'type' => 'object',
						'properties' => [
							'key' => [
								'type' => 'string',
								'description' => 'Ключ',
								'example' => 'key'
							],
							'data' => [
								'type' => 'array',
								'description' => 'Данные',
								'example' => []
							],
						]
					]
				]
			]
		]
	],
	'bxapp-session-removeData' => [
		'summary' => 'Удалить данные из сессии',
	],
	'bxapp-session-createNextSession' => [
		'summary' => 'Создание сессии некста'
	],
	'bxapp-session-checkNextSession' => [
		'summary' => 'Проверка сессии некста'
	]
];
