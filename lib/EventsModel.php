<?php
namespace Techart\BxApp;

/**
 * Назначает на эвенты изменения инфоблоков и хайлоадблоков очистку всего связанного кэша.
 * Класс CacheTrait сохраняет кэш в папку с именем кода модели.
 * Соответственно, при правке данного инфоблока происходит очищение данной папки.
 *
 * !!!__ВАЖНО__!!!
 *
 * В битриксе у хайлоадблоков нету общих эвентов. Нужно назначать для каждого самостоятельно. Типа:
 *
 * AddEventHandler('', 'WebinarOptionsOnAfterAdd', 'OnAfterAdd'); //где "WebinarOptions" название highload блока
 *
 * Так как в хайлоадблоках обычно небольшие списки. То было решено не городить что-то надмозговое.
 * По умолчанию у хайлоадблоков нету кэша. Если где-то он понадобится, то написать тут по примеру.
 */

use Bitrix\Main\Data\Cache;

class EventsModel
{
	public static function setEvents(): void
	{
		// назначать эвенты, если кэш включён
		if (\Glob::get('APP_SETUP_CACHE_TRAIT_USE_CACHE') === true && strpos($_SERVER['HTTP_HOST'], 'intranet') === false && \Config::get('App.APP_MODEL_CLEAN_CACHE_ON_CHANGE', true) === true) {
			AddEventHandler("iblock", "OnAfterIBlockSectionAdd", ["\Techart\BxApp\EventsModel", "ibChanged"]);//arr
			AddEventHandler("iblock", "OnAfterIBlockSectionUpdate", ["\Techart\BxApp\EventsModel", "ibChanged"]);//arr
			AddEventHandler("iblock", "OnBeforeIBlockSectionDelete", ["\Techart\BxApp\EventsModel", "ibSectionDelete"]);//id удалённого раздела
			AddEventHandler("iblock", "OnAfterIBlockElementAdd", ["\Techart\BxApp\EventsModel", "ibChanged"]);//arr
			AddEventHandler("iblock", "OnAfterIBlockElementUpdate", ["\Techart\BxApp\EventsModel", "ibChanged"]);//arr
			AddEventHandler("iblock", "OnAfterIBlockElementDelete", ["\Techart\BxApp\EventsModel", "ibChanged"]);//arr
		}
	}

	public static function ibChanged(&$arFields): void
	{
		if (isset($arFields['IBLOCK_ID']) && !empty($arFields['IBLOCK_ID'])) {
			$ar = \CIBlock::GetByID($arFields['IBLOCK_ID'])->getNext();

			if (isset($ar['CODE']) && !empty($ar['CODE'])) {
				$cache = Cache::createInstance();
				$cache->CleanDir($ar['CODE']);
			}
		}
	}

	public static function ibSectionDelete(&$ID): void
	{
		if (isset($ID) && !empty($ID)) {
			$arFields = \CIBlockSection::GetByID($ID)->getNext();

			if (isset($arFields['IBLOCK_ID']) && !empty($arFields['IBLOCK_ID'])) {
				$ar = \CIBlock::GetByID($arFields['IBLOCK_ID'])->getNext();

				if (isset($ar['CODE']) && !empty($ar['CODE'])) {
					$cache = Cache::createInstance();
					$cache->CleanDir($ar['CODE']);
				}
			}
		}
	}
}
