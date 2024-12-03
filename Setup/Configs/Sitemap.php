<?php

/**
 * Настройки из конфига используются для генерации сайтмапа через cli
 *
 * MODE может принимать только одно из двух значений: bitrix или models
 *
 * Если NAME задан пустой, то название файла будет формироваться по шаблону: sitemap_ID.xml
 *
 * Примеры заполнения:
 *
 * Для URLS:
 *
 * 'URLS' => [
 *		'/test/ => ['priority' => '0.5', 'change' => 'weekly'],
 * ]
 *
 * Для BITRIX:
 *
 * 'BITRIX' => [
 * 		'infoblocks' => [
 * 			'CODE' => [ // CODE - символьный код инфоблока
 * 				'page' => ['priority' => '0.5', 'change' => 'weekly'],
 * 				'sections' => ['priority' => '0.5', 'change' => 'weekly'],
 * 				'elements' => ['priority' => '0.5', 'change' => 'weekly']
 * 			]
 * 		],
 * 		'files' => [ // file_path - путь до файла, например: /404.php
 * 			'file_path' => ['priority' => '0.5', 'change' => 'weekly']
 * 		]
 * ]
 *
 * Для MODELS:
 *
 * 'MODELS' => [
 *		'TestModel' => [
 *			'page' => ['priority' => '0.5', 'change' => 'weekly'],
 *			'sections' => ['priority' => '0.5', 'change' => 'weekly'],
 *			'elements' => ['priority' => '0.5', 'change' => 'weekly']
 *		]
 * ]
 *
 */

return [
	'ACTIVE' => false, // Включает генерацию сайтмапа
	'SITE_ID' => '', // ID сайта
	'NAME' => '', // Название файла с сайтмапом (Если не задано, формирует по шаблону sitemap_ID.xml)
	'DOMAIN' => '', // Домен сайта (без https)
	'PROTOCOL' => 'https', // Протокол сайта
	'MODE' => 'bitrix', // Режим генерации сайтмапа (bitrix или models)
	'COMPRESSION' => false, // Включает сохранение файла сайтмапа в архиве .gz
	'MAX_URLS_PER_SITEMAP' => 50000, // Максимальное количество ссылок в файле сайтмапа
	// Свои урлы, которые должны попасть в сайтмап
	'URLS' => [],
	'SITEMAP_ID' => 0, // ID настроек сайтмапа в битриксе (только для режима bitrix)
	'SITEMAP_PATH' => '/', // Путь сохранения файла сайтмапа (относительно /)
	// Параметры для генерации по настройкам битрикса
	'BITRIX' => [
		// Параметры для инфоблоков
		'infoblocks' => [],
		//Параметры для файлов
		'files' => []
	],
	// Параметры для генерации по моделям
	'MODELS' => []
];
