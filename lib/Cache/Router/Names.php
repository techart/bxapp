<?php
namespace Techart\BxApp\Cache\Router;

class Names
{
	private static $store;


	private static function getStore()
	{
		if (!self::$store) {
			$storeType = \Config::get('Router.APP_ROUTER_CACHE_STORE', 'file');

			if ($storeType === 'file') {
				self::$store = new \Techart\BxApp\Cache\Router\Store\Names\File();
			}
			if ($storeType === 'db') {
				self::$store = new \Techart\BxApp\Cache\Router\Store\Names\DB();
			}
		}

		return self::$store;
	}

	public static function exists()
	{
		return self::getStore()->exists();
	}

	public static function get()
	{
		return self::getStore()->get();
	}

	public static function put(mixed $data = [])
	{
		return self::getStore()->put($data);
	}

	public static function delete()
	{
		return self::getStore()->delete();
	}

	public static function flush()
	{
		return self::getStore()->flush();
	}
}
