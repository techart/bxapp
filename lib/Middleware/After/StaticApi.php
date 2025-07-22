<?php
namespace Techart\BxApp\Middleware\After;

class StaticApi
{
	/**
	 * Данный метод вызывается при использовании middleware
	 *
	 * @param mixed $data
	 * @return void
	 */
	function handle($data)
	{
		// Если статик кэш включён и если запрос помечен как staticapi
		if (\Glob::get('APP_SETUP_STATIC_API_ACTIVE', true) === true && defined('BXAPP_IS_STATIC') && BXAPP_IS_STATIC === true && defined('BXAPP_ROUTER_CURRENT_REQUEST_URL') && !empty(BXAPP_ROUTER_CURRENT_REQUEST_URL)) {
			if (\App::getRoute('requestMethod') !== 'get') {
				\App::core('Main')->do404();
			}

			$putCache = true;

			// Если среди протекторов роута есть checkSecure или checkAuth, то записывать статик кэш не надо
			if (
				isset(\App::getRoute()['protector']) &&
				count(array_intersect(['checkSecure', 'checkAuth'], \App::getRoute()['protector'])) > 0
			) {
				$putCache = false;
			}
			// Если среди параметров роута есть noStatic, то записывать статик кэш не надо
			if (
				isset(\App::getRoute()['params']) &&
				count(array_intersect(['noStatic'], \App::getRoute()['params'])) > 0
			) {
				$putCache = false;
			}

			if ($putCache) {
				$cachePath = APP_CACHE_STATIC_DIR.BXAPP_ROUTER_CURRENT_REQUEST_URL;

				if (!is_dir($cachePath)) {
					mkdir($cachePath, 0777, true);
				}
				if (!file_exists($cachePath .'data.json')) {
					$dataForCache = $data;
					$dataForCache['cache'] = true;

					file_put_contents($cachePath .'data.json', json_encode($dataForCache, JSON_UNESCAPED_UNICODE));

					\Logger::info('Middleware\After\StaticApi: данные записаны в '.$cachePath .'data.json');
				}

				if (\Config::get('Router.APP_ROUTER_CACHE_MODELS_TAGS', false)) {
					$routeName = \App::getRoute('name');
					if (!is_dir(APP_CACHE_MODELS_DIR)) {
						mkdir(APP_CACHE_MODELS_DIR, 0777, true);
					}
					$models = [];
					if (file_exists(APP_CACHE_MODELS_DIR . '/models.json')) {
						$models = json_decode(file_get_contents(APP_CACHE_MODELS_DIR . '/models.json'), true);
					}

					if ($models !== false) {
						$called = array_map(function ($model) {
							$model = \App::model($model);

							if (is_subclass_of($model, 'BaseIblockModel')) {
								return 'i_' . $model->table;
							} else {
								return 'h_' . $model->table;
							}
						}, \App::called('models'));

						if (!empty($called)) {
							$models[$routeName] = $called;

							if (file_put_contents(APP_CACHE_MODELS_DIR . '/models.json', json_encode($models)) === false) {
								\Logger::info('StaticApi: Не удалось записать файл models.json');
							}
						}

						if (!empty($models[$routeName])) {
							foreach ($models[$routeName] as $table) {
								$dataModel = [];

								if (!is_dir(APP_CACHE_MODELS_DIR . '/' . $table)) {
									mkdir(APP_CACHE_MODELS_DIR . '/' . $table, 0777, true);
								}
								if (file_exists(APP_CACHE_MODELS_DIR . '/' . $table . '/router.json')) {
									$dataModel = json_decode(file_get_contents(APP_CACHE_MODELS_DIR . '/' . $table . '/router.json'), true);
								}
								if ($dataModel !== false) {
									if (!empty($dataModel[$routeName]) &&
										!in_array(APP_CACHE_STATIC_DIR.BXAPP_ROUTER_CURRENT_REQUEST_URL, $dataModel[$routeName]) ||
										empty($dataModel[$routeName])) {
											$dataModel[$routeName][] = APP_CACHE_STATIC_DIR.BXAPP_ROUTER_CURRENT_REQUEST_URL;

											if (file_put_contents(APP_CACHE_MODELS_DIR . '/' . $table . '/router.json', json_encode($dataModel)) === false) {
												\Logger::info('StaticApi: Не удалось записать файл router.json в папке ' . $table);
											}
									}
								} else {
									\Logger::info('StaticApi: Не удалось прочитать файл router.json из папки ' . $table);
								}
							}
						} 
						// NOTE: Сохранение урлов без моделей в отдельный файл default.json. Удалить если не нужна кли cache:generateModels
						/*else {
							$default = [];
							if (file_exists(APP_CACHE_MODELS_DIR . '/default.json')) {
								$default = json_decode(file_get_contents(APP_CACHE_MODELS_DIR . '/default.json'), true);
							}

							if ($default !== false) {
								$default[$routeName][] = APP_CACHE_STATIC_DIR.BXAPP_ROUTER_CURRENT_REQUEST_URL;
								if (file_put_contents(APP_CACHE_MODELS_DIR . '/default.json', json_encode($default)) === false) {
									\Logger::info('StaticApi: Не удалось записать файл default.json');
								}
							} else {
								\Logger::info('StaticApi: Не удалось прочитать файл default.json');
							}
						}*/
					} else {
						\Logger::info('StaticApi: Не удалось прочитать файл models.json. Файлы router.json к каждой используемой модели не будут сформированы!');
					}
				}
			}
		}

		return $data;
	}
}
