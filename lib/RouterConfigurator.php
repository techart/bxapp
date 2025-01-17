<?php
namespace Techart\BxApp;

/**
 * Класс формирует массив $RouterData где хранятся все данные всех роутов
 */

class RouterConfigurator
{
	private static $RouterData = [];
	private static $namesData = [];

	/**
	 * Возвращает массив даных конфигуратора
	 *
	 * @return array
	 */
	public static function get(): array
	{
		return self::$RouterData;
	}

	/**
	 * Возвращает массив имён даных
	 *
	 * @return array
	 */
	public static function getNames(): array
	{
		return self::$namesData;
	}

	/**
	 * Возвращает роут найденный по $url
	 *
	 * @param string $url
	 * @return mixed
	 */
	public static function getRouteByUrl(string $url = ''): mixed
	{
		if (isset(self::$RouterData[$url])) {
			return self::$RouterData[$url];
		} else {
			return false;
		}
	}

	/**
	 * Назначает роуту $requestMethod
	 *
	 * @param string $requestMethod
	 * @return void
	 */
	public static function setRequestMethod(string $requestMethod = ''): void
	{
		if (!isset(self::$RouterData[$requestMethod])) {
			self::$RouterData[$requestMethod] = [];
		}
	}

	/**
	 * Назначает роуту $bundle
	 *
	 * @param string $requestMethod
	 * @param string $bundle
	 * @return void
	 */
	public static function setBundle(string $requestMethod = '', string $bundle = ''): void
	{
		if (!isset(self::$RouterData[$requestMethod][$bundle])) {
			self::$RouterData[$requestMethod][$bundle] = [];
		}
	}

	/**
	 * Назначает роуту $url
	 *
	 * @param string $requestMethod
	 * @param string $bundle
	 * @param string $url
	 * @return void
	 */
	public static function setRouteUrl(string $requestMethod = '', string $bundle = '', string $url = ''): void
	{
		if (!isset(self::$RouterData[$requestMethod][$bundle][$url])) {
			self::$RouterData[$requestMethod][$bundle][$url] = [];
		}
	}

	/**
	 * Назначает роуту $requestMethod
	 *
	 * @param string $requestMethod
	 * @param string $bundle
	 * @param string $url
	 * @return void
	 */
	public static function setRouteRequestMethod(string $requestMethod = '', string $bundle = '', string $url = ''): void
	{
		self::$RouterData[$requestMethod][$bundle][$url]['requestMethod'] = $requestMethod;
		self::$RouterData[$requestMethod][$bundle][$url]['url'] = $url;
		self::$RouterData[$requestMethod][$bundle][$url]['routeUrl'] = '/'.(string)Config::get('Router.APP_ROUTER_PREFIX', 'siteapi').'/'.$bundle.$url;
	}

	/**
	 * Назначает роуту $routeBundle
	 *
	 * @param string $requestMethod
	 * @param string $bundle
	 * @param string $url
	 * @param string $routeBundle
	 * @return void
	 */
	public static function setRouteBundle(string $requestMethod = '', string $bundle = '', string $url = '', string $routeBundle = ''): void
	{
		self::$RouterData[$requestMethod][$bundle][$url]['bundle'] = $routeBundle;
	}

	/**
	 * Назначает роуту $group
	 *
	 * @param string $requestMethod
	 * @param string $bundle
	 * @param string $url
	 * @param string $group
	 * @return void
	 */
	public static function setRouteGroup(string $requestMethod = '', string $bundle = '', string $url = '', string $group = ''): void
	{
		self::$RouterData[$requestMethod][$bundle][$url]['group'] = $group;
	}

	/**
	 * Назначает роуту $controller и $method
	 *
	 * @param string $requestMethod
	 * @param string $bundle
	 * @param string $url
	 * @param string $controller
	 * @param string $method
	 * @return void
	 */
	public static function setRouteMethod(string $requestMethod = '', string $bundle = '', string $url = '', string $controller = '', string $method = ''): void
	{
		self::$RouterData[$requestMethod][$bundle][$url]['controller'] = $controller;
		self::$RouterData[$requestMethod][$bundle][$url]['method'] = $method;
	}

	/**
	 * Назначает роуту $where
	 *
	 * @param string $requestMethod
	 * @param string $bundle
	 * @param string $url
	 * @param array $where
	 * @return void
	 */
	public static function setRouteWhere(string $requestMethod = '', string $bundle = '', string $url = '', array $where = []): void
	{
		self::$RouterData[$requestMethod][$bundle][$url]['where'] = $where;
	}

	/**
	 * Назначает роуту $name
	 *
	 * @param string $requestMethod
	 * @param string $bundle
	 * @param string $url
	 * @param string $name
	 * @return void
	 */
	public static function setRouteName(string $requestMethod = '', string $bundle = '', string $url = '', string $name = ''): void
	{
		if (!empty($name)) {
			self::$RouterData[$requestMethod][$bundle][$url]['name'] = $name;
			self::$namesData['names'][$name] = self::$RouterData[$requestMethod][$bundle][$url]['routeUrl'];
		}
	}

	/**
	 *Назначает роуту $protector
	 *
	 * @param string $requestMethod
	 * @param string $bundle
	 * @param string $url
	 * @param array $protector
	 * @return void
	 */
	public static function setRouteProtector(string $requestMethod = '', string $bundle = '', string $url = '', array $protector = []): void
	{
		self::$RouterData[$requestMethod][$bundle][$url]['protector'] = $protector;
	}
}
