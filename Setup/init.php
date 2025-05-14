<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/local/vendor/autoload.php');

\Techart\BxApp\App::init(__DIR__);

// Автозагрузка вспомогательных классов вне BxApp
// CModule::AddAutoloadClasses(
// 	'',
// 	array(
// 		'TestClass' => '/local/php_interface/Lib/TestClass.php',
// 	)
// );

// Обработчики событий админки
// if (\Bitrix\Main\Context::getCurrent()->getRequest()->isAdminSection()) {
// 	include_once SITE_ROOT_DIR.'/local/php_interface/Events/OnAfter.php';
// }
