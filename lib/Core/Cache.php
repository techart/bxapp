<?php
namespace Techart\BxApp\Core;

class Cache
{
	/**
	 * Очищает кеш меню
	 *
	 * @param string $siteId
	 * @return void
	 */
	public function clearMenu(string $siteId = ''): void
	{
		\Techart\BxApp\Cache\Cache::clearMenu(!empty($siteId) ? $siteId : TBA_SITE_ID);
	}

	/**
	 * Очищает кеш статики
	 *
	 * @param string $siteId
	 * @return void
	 */
	public function clearStatic(string $siteId = ''): void
	{
		\Techart\BxApp\Cache\Cache::clearStatic(!empty($siteId) ? $siteId : TBA_SITE_ID);
	}

	/**
	 * Очищает кеш компонентов
	 *
	 * @param string $siteId
	 * @return void
	 */
	public function clearComponents(string $siteId = ''): void
	{
		\Techart\BxApp\Cache\Cache::clearComponents(!empty($siteId) ? $siteId : TBA_SITE_ID);
	}

	/**
	 * Очищает кеш блейд шаблонов
	 *
	 * @param string $siteId
	 * @return void
	 */
	public function clearBlade(string $siteId = ''): void
	{
		\Techart\BxApp\Cache\Cache::clearBlade(!empty($siteId) ? $siteId : TBA_SITE_ID);
	}

	/**
	 * Очищает кеш моделей
	 *
	 * @param string $siteId
	 * @return void
	 */
	public function clearModels(string $siteId = ''): void
	{
		\Techart\BxApp\Cache\Cache::clearModels(!empty($siteId) ? $siteId : TBA_SITE_ID);
	}

	/**
	 * Очищает кеш роутера
	 *
	 * @param string $siteId
	 * @return void
	 */
	public function clearRouter(string $siteId = ''): void
	{
		\Techart\BxApp\Cache\Cache::clearRouter(!empty($siteId) ? $siteId : TBA_SITE_ID);
	}

	/**
	 * Очищает кеш привязки роутов к моделям
	 *
	 * @param string $siteId
	 * @return void
	 */
	public function clearRouterModels(string $siteId = ''): void
	{
		\Techart\BxApp\Cache\Cache::clearRouterModels(!empty($siteId) ? $siteId : TBA_SITE_ID);
	}

	/**
	 * Очищает HTML-кеш страниц
	 *
	 * @param string $siteId
	 * @return void
	 */
	public function clearHtml(string $siteId = ''): void
	{
		\Techart\BxApp\Cache\Cache::clearHtml(!empty($siteId) ? $siteId : TBA_SITE_ID);
	}

	/**
	 * Очищает весь кеш
	 *
	 * @param string $siteId
	 * @return void
	 */
	public function clearAll(string $siteId = ''): void
	{
		\Techart\BxApp\Cache\Cache::clearAll(!empty($siteId) ? $siteId : TBA_SITE_ID);
	}
}
