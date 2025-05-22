<?php

return [
	'TAGS' => [
		'BxappDefault' => [
			'description' => 'default routes BxApp'
		]
	],
	'SCHEMAS' => [
		'Response' => [
			'type' => 'object',
			'properties' => [
				'status' => [
					'type' => 'string',
					'enum' => [
						'success',
						'fail'
					],
					'example' => 'success'
				],
				'site' => [
					'type' => 'string',
					'example' => 's1'
				],
				'language' => [
					'type' => 'string',
					'example' => 'ru'
				],
				'cache' => [
					'type' => 'boolean',
					'example' => 'false'
				],
				'result' => [
					'type' => 'object',
					'properties' => [
						'title' => [
							'type' => 'string',
							'example' => 'Title'
						],
						'message' => [
							'type' => 'string',
							'example' => 'Message'
						],
						'data' => [
						]
					]
				]
			]
		],
	]
];