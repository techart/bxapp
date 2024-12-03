<?php
namespace Techart\BxApp\Events;

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
 * поэтому назначаем для тех, которые перечислены в конфиге App - в ключе APP_HIGHLOAD_BLOCKS_LIST
 */

use Bitrix\Main\Data\Cache;

class EventsModel
{
	public static function setEvents(): void
	{
		// назначать эвенты, если кэш включён
		if (\Glob::get('APP_SETUP_CACHE_TRAIT_USE_CACHE') === true && strpos($_SERVER['HTTP_HOST'], 'intranet') === false && \Config::get('App.APP_MODEL_CLEAN_CACHE_ON_CHANGE', true) === true) {
			AddEventHandler("iblock", "OnAfterIBlockSectionAdd", ["\Techart\BxApp\Events\EventsModel", "ibChanged"]);//arr
			AddEventHandler("iblock", "OnAfterIBlockSectionUpdate", ["\Techart\BxApp\Events\EventsModel", "ibChanged"]);//arr
			AddEventHandler("iblock", "OnBeforeIBlockSectionDelete", ["\Techart\BxApp\Events\EventsModel", "ibSectionDelete"]);//id удалённого раздела
			AddEventHandler("iblock", "OnAfterIBlockElementAdd", ["\Techart\BxApp\Events\EventsModel", "ibChanged"]);//arr
			AddEventHandler("iblock", "OnAfterIBlockElementUpdate", ["\Techart\BxApp\Events\EventsModel", "ibChanged"]);//arr
			AddEventHandler("iblock", "OnAfterIBlockElementDelete", ["\Techart\BxApp\Events\EventsModel", "ibChanged"]);//arr

			// У хайлоадблоков нету общих эвентов, поэтому назначаем только для тех, которые перечислены в конфиге App
			// в ключе APP_HIGHLOAD_BLOCKS_LIST
			if (count(\Config::get('App.APP_HIGHLOAD_BLOCKS_LIST', [])) > 0 ) {
				$eventManager = \Bitrix\Main\EventManager::getInstance();

				foreach (\Config::get('App.APP_HIGHLOAD_BLOCKS_LIST') as $v) {
					$eventManager->AddEventHandler("", $v."OnAfterDelete", ["\Techart\BxApp\Events\EventsModel", "hbChanged"]);
					$eventManager->AddEventHandler("", $v."OnAfterAdd", ["\Techart\BxApp\Events\EventsModel", "hbChanged"]);
					$eventManager->AddEventHandler("", $v."OnAfterUpdate", ["\Techart\BxApp\Events\EventsModel", "hbChanged"]);
				}
			}
		}
	}

	public static function hbChanged(\Bitrix\Main\Entity\Event $event): void
	{
		$name = $event->getEntity()->getName();

		if (isset($name) && !empty($name)) {
			$cache = Cache::createInstance();
			$cache->CleanDir($name);
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

			if (\Config::get('Sitemap.ACTIVE', false)) {
				\App::core('Sitemap')
					->active(\Config::get('Sitemap.ACTIVE', false))
					->site(\Config::get('Sitemap.SITE_ID'))
					->name(\Config::get('Sitemap.NAME', ''))
					->mode(\Config::get('Sitemap.MODE', 'bitrix'))
					->domain(\Config::get('Sitemap.DOMAIN'))
					->protocol(\Config::get('Sitemap.PROTOCOL', 'http'))
					->sitemapId(\Config::get('Sitemap.SITEMAP_ID'))
					->sitemapPath(\Config::get('Sitemap.SITEMAP_PATH', '/'))
					->models(\Config::get('Sitemap.MODELS', []))
					->urls(\Config::get('Sitemap.URLS', []))
					->bitrix(\Config::get('Sitemap.BITRIX', []))
					->create();
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
