<?php
namespace Techart\BxApp\Traits;

/**
 * Класс для кэширования данных.
 * Подключаем трейт в свойм классе.
 *
 * Кэш не работает, если APP_SETUP_CACHE_TRAIT_USE_CACHE = false или перменная CacheTraitUseCache=false
 * или если сайт запускается локально (intranet).
 *
 * Пример использования:
 *
 * 	if ($this->hasCache()) {
		$data = $this->getCache();
	} elseif ($this->startCache()) {
		$data = 123;

		$this->endCache($data);
	}

	return $this->result('Тайтл ответа', 'текст сообщения', $data);
 *
 * ID кэша формируется на основе имени класса и имени метода в котором используется кэш
 *
 * Если кэшируются динамические данные, то нужно сформировать ID кэша с помощью buildCacheID(),
 * в которую передать массив с параметрами получения кэша
 *
 * Данный ID надо передать во все функции кэша, а так же в функции связанных трейтов: ResultTrait и ErrorTrait
 *
 *
 * Папка кэша берётся из переменной $table (в классах моделях там же указывается код инфоблока)
 * При изменениях в инфоблоке через админку по эвенту чистится весь кэш для инфоблока - одноимённая папка в кэше битрикса
 * Делается это в файле App/Events/EventsModel.php
 */


use Bitrix\Main\Data\Cache;

trait CacheTrait
{
	public $table = ''; // таблица (используется в качестве имени директории для хранения кэша модели)
	public $CacheTraitUseCache = true; // использовать кэш в модели или нет (если false, то hasCache() всегда возвращает false)
	public $CacheTraitCacheLifeTime = 604800; // время жизни кэша
	private $CacheTraitCacheObjects = []; // массив сохранённых объектов кэша
	private $CacheTraitFromCache = []; // показывает был использован кэш при прошлом вызове метода или нет (нужно для result)


	/**
	 * Возвращает ID текущего кэша.
	 * Это или переданная строка $cacheID.
	 * Или по умолчанию строка состоящая из:
	 * SITE_ID, LANGUAGE_ID, имени класса модели и имени метода, где вызывается функция кэша.
	 *
	 * @param string $cacheID
	 * @return string
	 */
	private function getCacheID(string $cacheID = ''): string
	{
		return !empty($cacheID) ? $cacheID : SITE_ID.'_'.LANGUAGE_ID.'_'.get_called_class().'_'.debug_backtrace()[2]['function'];
	}

	/**
	 * Составляет уникальный кэш ид. Нужно передать массив с динамическими параметрами, от которых зависит запрос.
	 * Далее полученный id передать во все методы кэша.
	 * $cacheID = $this->buildCacheID($params);
	 * $this->hasCache($cacheID)
	 * и т.д.
	 * а так же трейты зависящие от кэша: ResultTrait и ErrorTrait.
	 *
	 * @param array $params
	 * @return string
	 */
	public function buildCacheID(array $params = []): string
	{
		$curCacheId = SITE_ID.'_'.LANGUAGE_ID.'_'.get_called_class().'_'.debug_backtrace()[1]['function'].'_'.md5(json_encode($params));

		return $curCacheId;
	}

	/**
	 * Возвращает текущий сохранённый объект кэша.
	 * Можно задать ид кэша ($cacheID). Но лучше так не делать.
	 * Это нужно, чтобы можно было вызывать методы с кэшем внутри других методов с кэшем
	 *
	 * @param string $cacheID
	 * @return object
	 */
	private function getCacheObject(string $cacheID = ''): object
	{
		if (!isset($this->CacheTraitCacheObjects[$cacheID]) || empty($this->CacheTraitCacheObjects[$cacheID])) {
			$this->CacheTraitCacheObjects[$cacheID] = Cache::createInstance();
		}

		return $this->CacheTraitCacheObjects[$cacheID];
	}

	/**
	 * Определяет активен кэш для текущей модели или нет
	 * Возвращает false, если кэш отключен глобально через APP_SETUP_CACHE_TRAIT_USE_CACHE
	 * Возвращает false, если кэш отключен в модели через свойство CacheTraitUseCache
	 * Возвращает false, если вызывается локально (в урле есть intranet)
	 *
	 * @return boolean
	 */
	private function isActiveCache(): bool
	{
		$status = true;

		// если кэш отключён через APP_SETUP_CACHE_TRAIT_USE_CACHE или в модели через CacheTraitUseCache
		if (\Glob::get('APP_SETUP_CACHE_TRAIT_USE_CACHE') === false || $this->CacheTraitUseCache === false) {
			$status = false;
		} else {
			if (strpos($_SERVER['HTTP_HOST'], 'intranet') !== false) {
				$status = false;
			}
		}

		// return false;
		return $status;
	}

	/**
	 * Вернёт true, если для текущего id есть кэш.
	 * ID - это или переданная строка $cacheID (очень нежелательно).
	 * Или по умолчанию строка состоящая из имени класса модели и метода, где вызывается функция кэша.
	 *
	 * Если в модели CacheTraitUseCache = false или APP_SETUP_CACHE_TRAIT_USE_CACHE = false
	 * или сайт запускается локально, то всегда возвращает false.
	 *
	 * Сохраняет весь кэш модели под дирректорией равной значению table в классе.
	 * Или переданное через параметр $table
	 *
	 * @param string $cacheID
	 * @param string $table
	 * @return boolean
	 */
	public function hasCache(string $cacheID = '', string $table = ''): bool
	{
		if ($this->isActiveCache() === false) {
			return false;
		} else {
			$id = $this->getCacheID($cacheID);
			$cache = $this->getCacheObject($id);
			$curTable = !empty($table) ? $table : $this->table;

			return $cache->initCache($this->CacheTraitCacheLifeTime, $id, $curTable);
		}
	}

	/**
	 * Обёртка для стандартной битриксовой функции startDataCache.
	 * Стартует кэш для текущего id.
	 * Это или переданная строка $cacheID (очень нежелательно).
	 * Или по умолчанию строка состоящая из имени класса модели и метода, где вызывается функция кэша.
	 *
	 * @param string $cacheID
	 * @return boolean
	 */
	public function startCache(string $cacheID = ''): bool
	{
		if ($this->isActiveCache()) {
			$id = $this->getCacheID($cacheID);
			$cache = $this->getCacheObject($id);

			return $cache->startDataCache();
		} else {
			return true;
		}
	}

	/**
	 * Обёртка для стандартной битриксовой функции endDataCache.
	 * Сохраняет кэш для текущего id.
	 * Это или переданная строка $cacheID (очень нежелательно).
	 * Или по умолчанию строка состоящая из имени класса модели и метода, где вызывается функция кэша.
	 *
	 * $var - данные, которые надо запомнить
	 *
	 * @param mixed $var
	 * @param string $cacheID
	 * @return void
	 */
	public function endCache(mixed $var = false, string $cacheID = ''): void
	{
		if ($this->isActiveCache()) {
			$id = $this->getCacheID($cacheID);
			$cache = $this->getCacheObject($id);
			$this->CacheTraitFromCache[$id] = false;

			$cache->endDataCache($var);
		}
	}

	/**
	 * Обёртка для стандартной битриксовой функции getVars.
	 * Возвращает кэш для текущего id.
	 * Это или переданная строка $cacheID (очень нежелательно).
	 * Или по умолчанию строка состоящая из имени класса модели и метода, где вызывается функция кэша.
	 *
	 * @param string $cacheID
	 * @return mixed
	 */
	public function getCache(string $cacheID = ''): mixed
	{
		if ($this->isActiveCache()) {
			$id = $this->getCacheID($cacheID);
			$cache = $this->getCacheObject($id);
			$this->CacheTraitFromCache[$id] = true;

			return $cache->getVars();
		} else {
			return '';
		}
	}

	/**
	 * Обёртка для стандартной битриксовой функции abortDataCache.
	 * Отменяет создание кэша для текущего id.
	 * Это или переданная строка $cacheID (очень нежелательно).
	 * Или по умолчанию строка состоящая из имени класса модели и метода, где вызывается функция кэша.
	 *
	 * @param string $cacheID
	 * @return void
	 */
	public function abortCache(string $cacheID = ''): void
	{
		if ($this->isActiveCache()) {
			$id = $this->getCacheID($cacheID);
			$cache = $this->getCacheObject($id);

			$cache->abortDataCache();
		}
	}

	/**
	 * Очищает весь кэш заданный для модели. Ориентируется на указанный table.
	 *
	 * @return void
	 */
	public function cleanCache(string $table = ''): void
	{
		if ($this->isActiveCache()) {
			$cache = Cache::createInstance();
			$curTable = !empty($table) ? $table : $this->table;

			$cache->CleanDir($curTable);
		}
	}
}
