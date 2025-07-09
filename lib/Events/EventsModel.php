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
 * AddEventHandler('', 'WebinarOptionsOnAfterAdd', 'OnAfterAdd', 1); //где "WebinarOptions" название highload блока
 *
 * поэтому назначаем для тех, которые перечислены в конфиге App - в ключе APP_HIGHLOAD_BLOCKS_LIST
 */

use Bitrix\Main\Data\Cache;

class EventsModel
{
	public static function setEvents(): void
	{
		// назначать эвенты, если кэш включён
		// если это intranet и APP_LOCAL_FORCED_CACHE=true
		// ИЛИ
		// если это не intranet и APP_TRAIT_CACHE=true и конфиг App.APP_MODEL_CLEAN_CACHE_ON_CHANGE=true
		if (
			(\H::isLocal() && \Glob::get('APP_SETUP_LOCAL_FORCED_CACHE') ) ||
			(\Glob::get('APP_SETUP_CACHE_TRAIT_USE_CACHE') === true && \H::isLocal() === false && \Config::get('App.APP_MODEL_CLEAN_CACHE_ON_CHANGE', true) === true)
		) {
			AddEventHandler("iblock", "OnAfterIBlockSectionAdd", ["\Techart\BxApp\Events\EventsModel", "ibChanged"], 1);//arr
			AddEventHandler("iblock", "OnAfterIBlockSectionUpdate", ["\Techart\BxApp\Events\EventsModel", "ibChanged"], 1);//arr
			AddEventHandler("iblock", "OnBeforeIBlockSectionDelete", ["\Techart\BxApp\Events\EventsModel", "ibSectionDelete"], 1);//id удалённого раздела
			AddEventHandler("iblock", "OnAfterIBlockElementAdd", ["\Techart\BxApp\Events\EventsModel", "ibChanged"], 1);//arr
			AddEventHandler("iblock", "OnAfterIBlockElementUpdate", ["\Techart\BxApp\Events\EventsModel", "ibChanged"], 1);//arr
			AddEventHandler("iblock", "OnAfterIBlockElementDelete", ["\Techart\BxApp\Events\EventsModel", "ibChanged"], 1);//arr

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

						$eventManager->AddEventHandler("", $code."OnAfterDelete", ["\Techart\BxApp\Events\EventsModel", "hbChanged"], false, 1);
						$eventManager->AddEventHandler("", $code."OnAfterAdd", ["\Techart\BxApp\Events\EventsModel", "hbChanged"], false, 1);
						$eventManager->AddEventHandler("", $code."OnAfterUpdate", ["\Techart\BxApp\Events\EventsModel", "hbChanged"], false, 1);
					}
				}
			}
		}
	}

	private static function clearModelsCache(string $tableName = '')
	{
		if (!empty($tableName)) {
			if (is_dir(APP_CACHE_MODELS_DIR . '/' . $tableName)) {
				$filePath = APP_CACHE_MODELS_DIR . '/' . $tableName . '/router.json';

				if (file_exists($filePath)) {
					$data = json_decode(file_get_contents($filePath), true);
					$models = json_decode(file_get_contents(APP_CACHE_MODELS_DIR . '/models.json'), true);
					$routeNames = array_keys($data);
					$tables = [];
					
					if ($data !== false) {
						if ($models !== false) {
							$paths = [];

							foreach ($data as $name => $routes) {
								$paths[] = $name;
								$tables = [];
								if (isset($models) && !empty($models[$name])) {
									$tables = $tables + $models[$name];
								}

								foreach ($routes as $route) {
									\H::deleteFile($route . 'data.json', 'static');
								}
							}

							unlink($filePath);
							unset($tables[array_search($tableName, $tables)]);
							foreach ($tables as $table) {
								if (file_exists(APP_CACHE_MODELS_DIR . '/' . $table . '/router.json')) {
									$tableData = json_decode(file_get_contents(APP_CACHE_MODELS_DIR . '/' . $table . '/router.json'), true);
									$tableData = array_diff_key($tableData, array_flip($routeNames));
									if (file_put_contents(APP_CACHE_MODELS_DIR . '/' . $table . '/router.json', json_encode($tableData)) === false) {
										\Logger::info('Router: Не удалось записать файл router.json в папке ' . $table);
									}
								}
							}

							\Glob::set('EVENTS_MODEL_CLEARED_ROUTE_NAMES', $paths);
						} else {
							\Logger::info('Router: Не удалось прочитать файл models.json');
						}
					} else {
						\Logger::info('Router: Не удалось прочитать файл router.json из папки ' . $tableName);
					}
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

			if (\Config::get('Router.APP_ROUTER_CACHE_MODELS_TAGS', false)) {
				self::clearModelsCache('h_'.$name);
			}
		}
	}

	public static function ibChanged(&$arFields): void
	{
		if (isset($arFields['IBLOCK_ID']) && !empty($arFields['IBLOCK_ID'])) {
			$ar = \CIBlock::GetByID($arFields['IBLOCK_ID'])->getNext();

			if (isset($ar['CODE']) && !empty($ar['CODE'])) {
				$cache = Cache::createInstance();
				$cache->CleanDir($ar['CODE']);
			
				if (\Config::get('Router.APP_ROUTER_CACHE_MODELS_TAGS', false)) {
					self::clearModelsCache('i_'.$ar['CODE']);
				}
			}

			// if (\Config::get('Sitemap.ACTIVE', false)) {
			// 	$shouldCreate = false;
			// 	$mode = \Config::get('Sitemap.MODE', 'bitrix');
			// 	$models = \Config::get('Sitemap.MODELS', []);
			// 	$bitrix = \Config::get('Sitemap.BITRIX', []);

			// 	if ($mode === 'bitrix' && !isset($bitrix['infoblocks'][$ar['CODE']])) {
			// 		$shouldCreate = true;
			// 	}

			// 	if ($mode === 'models') {
			// 		foreach($models as $model => $params) {
			// 			if ($ar['CODE'] === \App::model($model)->table) {
			// 				$shouldCreate = true;
			// 				break;
			// 			}
			// 		}
			// 	}

			// 	if ($shouldCreate) {
			// 		\App::core('Sitemap')
			// 			->active(\Config::get('Sitemap.ACTIVE', false))
			// 			->site(\Config::get('Sitemap.SITE_ID'))
			// 			->name(\Config::get('Sitemap.NAME', ''))
			// 			->domain(\Config::get('Sitemap.DOMAIN'))
			// 			->protocol(\Config::get('Sitemap.PROTOCOL', 'http'))
			// 			->mode($mode)
			// 			->compression(\Config::get('Sitemap.COMPRESSION', false))
			// 			->bitrixSitemapId(\Config::get('Sitemap.SITEMAP_ID'))
			// 			->sitemapPath(\Config::get('Sitemap.SITEMAP_PATH', '/'))
			// 			->maxUrlsPerSitemap(\Config::get('Sitemap.MAX_URLS_PER_SITEMAP'))
			// 			->models($models)
			// 			->urls(\Config::get('Sitemap.URLS', []))
			// 			->bitrix($bitrix)
			// 			->create();

			// 		\Logger::info('Sitemap: Sitemap автоматически сгенерирован из-за изменения инфоблока ' . $ar['CODE']);
			// 	}
			// }
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

					if (\Config::get('Router.APP_ROUTER_CACHE_MODELS_TAGS', false)) {
						self::clearModelsCache('i_'.$ar['CODE']);
					}
				}
			}
		}
	}
}
