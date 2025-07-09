<?php
/**
 * Файл с назначенными обработчиками событий OnAfter...
 * Подключается в init.php
 */

AddEventHandler("iblock", "OnAfterIBlockSectionAdd", "ibChangedAfter", 100);//arr
AddEventHandler("iblock", "OnAfterIBlockSectionUpdate", "ibChangedAfter", 100);//arr
AddEventHandler("iblock", "OnBeforeIBlockSectionDelete", "ibSectionDeleteAfter", 100);//id удалённого раздела
AddEventHandler("iblock", "OnAfterIBlockElementAdd", "ibChangedAfter", 100);//arr
AddEventHandler("iblock", "OnAfterIBlockElementUpdate", "ibChangedAfter", 100);//arr
AddEventHandler("iblock", "OnAfterIBlockElementDelete", "ibChangedAfter", 100);//arr


function ibChangedAfter(&$arFields): void {

}

function ibSectionDeleteAfter(&$ID): void {

}

// У хайлоадблоков нету общих эвентов, поэтому назначаем только для тех, которые перечислены в конфиге App
// в ключе APP_HIGHLOAD_BLOCKS_LIST
if (count(\Config::get('App.APP_HIGHLOAD_BLOCKS_LIST', [])) > 0 ) {
	$eventManager = \Bitrix\Main\EventManager::getInstance();

	foreach (\Config::get('App.APP_HIGHLOAD_BLOCKS_LIST') as $v) {
		$table = \App::model($v)->table;

		// нужно назначать эвенты для всех возможных HB таблиц с учётом языков и режима локализации code
		// эвенты сработают только при изменении в соответствующем HB
		foreach (\App::getLanguages() as $lang) {
			if (\Config::get('App.APP_LANG', 'ru') == $lang['LANGUAGE_ID']) {
				$code = $table;
			} else {
				$code = $table.\H::ucfirst($lang['LANGUAGE_ID']);
			}

			$eventManager->AddEventHandler("", $code."OnAfterDelete", "hbChangedAfter", false, 100);
			$eventManager->AddEventHandler("", $code."OnAfterAdd", "hbChangedAfter", false, 100);
			$eventManager->AddEventHandler("", $code."OnAfterUpdate", "hbChangedAfter", false, 100);
		}
	}
}

function hbChangedAfter(): void {

}
