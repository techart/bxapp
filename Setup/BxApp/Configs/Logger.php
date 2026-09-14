<?php

/**
 * Пример заполнения:
 * 
 * return [
 * 	'DEFAULT_LOGGER' => 'logger1',
 * 	'DEFAULT_EMAIL' => 'test@techart.ru',
 * 	'MONOLOG_LOGGERS' => [
 * 		'logger1' => [
 * 			'writeToFile' => true,
 * 			'pathToLogFile' => 'Logger/log.log',
 * 			'levelForFile' => 'debug',
 * 			'maskFile' => 'Y-m-d'
 * 		],
 * 		'logger2' => [
 *	 		'writeToFile' => true,
 *	 		'pathToLogFile' => 'Logger/Logger2/log.log',
 *	 		'levelForFile' => 'info',
 *	 		'maskFile' => ''
 * 		],
 * 	]
 * ];
 * 
 * Каждому логгеру можно задать свои параметры:
 * - writeToFile - нужно ли писать в файл
 * - pathToLogFile - путь до файла с логом
 * - levelForFile - начиная с какого типа писать логи в файл
 * - maskFile - маска для метода date() для деления файлов по дате, приписывается в конец названия файла через _ 
 * перед расширением файла. Например: маска Y-m-d будет писать новый файл каждый день с названием типа log_2026-08-05.log
 * 
 * 
 * Обращаться к логгерам через вызов метода соответствующего названию логгера в конфиге:
 * Для логгера с названием logger1 будет вызов Monolog::logger1()
 * 
 * Уровни ошибок: debug, info, notice, warning, error, critical, alert, emergency
 * 
 * Можно обращаться к логгеру по умолчанию через быстрый вызов метода ошибки: Monolog::error('test');
 * В таком случае берётся логгер указанный в DEFAULT_LOGGER. Если DEFAULT_LOGGER не задан, берётся первый логгер в списке.
 * 
 * Настройка отправки на email осуществляется в .env файле под каждый логгер отдельно:
 * 
 * APP_MONOLOG_LOGGER1_SEND_EMAIL=false
 * APP_MONOLOG_LOGGER1_LOG_LEVEL=warning
 * APP_MONOLOG_LOGGER1_EMAIL=test@techart.ru
 * APP_MONOLOG_LOGGER2_SEND_EMAIL=false
 * APP_MONOLOG_LOGGER2_LOG_LEVEL=error
 * APP_MONOLOG_LOGGER2_EMAIL=test@techart.ru
 * 
 * Заполняется по шаблону APP_MONOLOG_[Логгер]_[Параметр]. Название логгера и параметра должно быть написано в верхнем регистре.
 * Для каждого логгера настраиваются следующие параметры:
 * - SEND_EMAIL - Нужно ли отправлять лог на email
 * - LOG_LEVEL - Уровень с которого нужно отправлять ошибки на email
 * - EMAIL - список email на который нужно отправлять логи
 * 
 * Если значения не заполнены, то берутся значения по умолчанию:
 * SEND_EMAIL=false
 * LOG_LEVEL=warning
 * EMAIL - берётся из DEFAULT_EMAIL
 */

return [
	'DEFAULT_LOGGER' => '',
	'DEFAULT_EMAIL' => '',
	'MONOLOG_LOGGERS' => [
		// 'logger' => [
		// 	'writeToFile' => true,
		// 	'pathToLogFile' => 'Logger/log.log',
		// 	'levelForFile' => 'error',
		// 	'maskFile' => '',
		// ],
	]
];