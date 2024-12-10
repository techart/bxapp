<?php
return [
	'APP_LANG' => 'ru', // базовый язык (влияет на локализацию моделей и прочее)
	'APP_MODEL_LOCALIZATION_MODE' => 'code', // режим локализации моделей: code | select (можно менять в модели)
	'APP_MODEL_CLEAN_CACHE_ON_CHANGE' => true, // чистить кэш модели после добавления/удаления/обновления
	'APP_HIGHLOAD_BLOCKS_LIST' => [], // список $table всех HB которые нужно орабатывать (например,чистить кэш)
];
