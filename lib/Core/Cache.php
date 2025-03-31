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
		\Techart\BxApp\Cache::clearMenu(!empty($siteId) ? $siteId : BXAPP_SITE_ID);
	}

	/**
	 * Очищает кеш статики
	 *
	 * @param string $siteId
	 * @return void
	 */
	public function clearStatic(string $siteId = ''): void
	{
		\Techart\BxApp\Cache::clearStatic(!empty($siteId) ? $siteId : BXAPP_SITE_ID);
	}

	/**
	 * Очищает кеш компонентов
	 *
	 * @param string $siteId
	 * @return void
	 */
	public function clearComponents(string $siteId = ''): void
	{
		\Techart\BxApp\Cache::clearComponents(!empty($siteId) ? $siteId : BXAPP_SITE_ID);
	}

	/**
	 * Очищает кеш блейд шаблонов
	 *
	 * @param string $siteId
	 * @return void
	 */
	public function clearBlade(string $siteId = ''): void
	{
		\Techart\BxApp\Cache::clearBlade(!empty($siteId) ? $siteId : BXAPP_SITE_ID);
	}

	/**
	 * Очищает кеш моделей
	 *
	 * @param string $siteId
	 * @return void
	 */
	public function clearModels(string $siteId = ''): void
	{
		\Techart\BxApp\Cache::clearModels(!empty($siteId) ? $siteId : BXAPP_SITE_ID);
	}

	/**
	 * Очищает кеш роутера
	 *
	 * @param string $siteId
	 * @return void
	 */
	public function clearRouter(string $siteId = ''): void
	{
		\Techart\BxApp\Cache::clearRouter(!empty($siteId) ? $siteId : BXAPP_SITE_ID);
	}

	/**
	 * Очищает весь кеш
	 *
	 * @param string $siteId
	 * @return void
	 */
	public function clearAll(string $siteId = ''): void
	{
		\Techart\BxApp\Cache::clearAll(!empty($siteId) ? $siteId : BXAPP_SITE_ID);
	}
}
