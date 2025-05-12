<?php
/**
 * Файл с назначенными обработчиками событий OnAfter...
 * Подключается в init.php
 */

AddEventHandler("iblock", "OnAfterIBlockSectionAdd", "ibChangedAfter");//arr
AddEventHandler("iblock", "OnAfterIBlockSectionUpdate", "ibChangedAfter");//arr
AddEventHandler("iblock", "OnBeforeIBlockSectionDelete", "ibSectionDeleteAfter");//id удалённого раздела
AddEventHandler("iblock", "OnAfterIBlockElementAdd", "ibChangedAfter");//arr
AddEventHandler("iblock", "OnAfterIBlockElementUpdate", "ibChangedAfter");//arr
AddEventHandler("iblock", "OnAfterIBlockElementDelete", "ibChangedAfter");//arr


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

			$eventManager->AddEventHandler("", $code."OnAfterDelete", "hbChangedAfter");
			$eventManager->AddEventHandler("", $code."OnAfterAdd", "hbChangedAfter");
			$eventManager->AddEventHandler("", $code."OnAfterUpdate", "hbChangedAfter");
		}
	}
}

function hbChangedAfter(): void {

}
