<?php
return [
	'APP_LANG' => 'ru', // базовый язык (влияет на локализацию моделей и прочее)
	'APP_LOCALIZATION_MESSAGES_TEMPLATES' => [ // шаблоны поиска сообщений локализации
		'Models' => '{entity_name}/{first_part}::{class_name}.{method_name}_',
		'Entities' => '{entity_name}/{first_part}::{class_name}.{method_name}_',
		'Menu' => '{entity_name}/{first_part}::{class_name}.{method_name}_',
		'Middleware' => '{entity_name}/{first_part}::{class_name}.{method_name}_',
		'Modules' => '{entity_name}/{first_part}::{class_name}.{method_name}_',
		'Services' => '{entity_name}/{first_part}::{class_name}.{method_name}_',
		'Router' => '{entity_name}/{first_part}::{class_name}.{method_name}_',
	],
	'APP_MODEL_LOCALIZATION_MODE' => 'none', // режим локализации моделей: none | code | select | directory (можно менять в модели)
	'APP_MODEL_CLEAN_CACHE_ON_CHANGE' => true, // чистить кэш модели после добавления/удаления/обновления
	'APP_HIGHLOAD_BLOCKS_LIST' => [], // список $table всех HB которые нужно орабатывать (например,чистить кэш)
	'APP_DO_404_MODE' => 'default', // режим работы метода do404 из класса Core/Main: default (Возвращает хедер 404) | result (Возвращает null в данных traitResult)
];
