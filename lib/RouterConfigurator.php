<?php
namespace Techart\BxApp;

/**
 * Класс формирует массив $RouterData где и хранятся все данные всех роутов
 */

class RouterConfigurator
{
	private static $RouterData = [];


	/**
	 * Возвращает массив даных протектора
	 *
	 * @return array
	 */
	public static function get(): array
	{
		return self::$RouterData;
	}

	/**
	 * Undocumented function
	 *
	 * @param string $url
	 * @return void
	 */
	public static function getRouteByUrl(string $url = '')
	{
		if (isset(self::$RouterData[$url])) {
			return self::$RouterData[$url];
		} else {
			return false;
		}
	}

	public static function setRequestMethod(string $requestMethod = '')
	{
		if (!isset(self::$RouterData[$requestMethod])) {
			self::$RouterData[$requestMethod] = [];
		}
	}

	public static function setBundle(string $requestMethod = '', string $bundle = '')
	{
		if (!isset(self::$RouterData[$requestMethod][$bundle])) {
			self::$RouterData[$requestMethod][$bundle] = [];
		}
	}

	/**
	 *
	 *
	 * @param string $requestMethod
	 * @param string $bundle
	 * @param string $url
	 * @return void
	 */
	public static function setRouteUrl(string $requestMethod = '', string $bundle = '', string $url = '')
	{
		if (!isset(self::$RouterData[$requestMethod][$bundle][$url])) {
			self::$RouterData[$requestMethod][$bundle][$url] = [];
		}
	}

	/**
	 *
	 *
	 * @param string $requestMethod
	 * @param string $bundle
	 * @param string $url
	 * @return void
	 */
	public static function setRouteRequestMethod(string $requestMethod = '', string $bundle = '', string $url = '')
	{
		self::$RouterData[$requestMethod][$bundle][$url]['requestMethod'] = $requestMethod;
	}

	/**
	 *
	 *
	 * @param string $requestMethod
	 * @param string $bundle
	 * @param string $url
	 * @return void
	 */
	public static function setRouteBundle(string $requestMethod = '', string $bundle = '', string $url = '', string $routeBundle = '')
	{
		self::$RouterData[$requestMethod][$bundle][$url]['bundle'] = $routeBundle;
	}

	/**
	 *
	 *
	 * @param string $url
	 * @param string $group
	 * @return void
	 */
	public static function setRouteGroup(string $requestMethod = '', string $bundle = '', string $url = '', string $group = '')
	{
		self::$RouterData[$requestMethod][$bundle][$url]['group'] = $group;
	}

	/**
	 *
	 *
	 * @param string $url
	 * @param string $controller
	 * @param string $method
	 * @return void
	 */
	public static function setRouteMethod(string $requestMethod = '', string $bundle = '', string $url = '', string $controller = '', string $method = '')
	{
		self::$RouterData[$requestMethod][$bundle][$url]['controller'] = $controller;
		self::$RouterData[$requestMethod][$bundle][$url]['method'] = $method;
	}

	/**
	 *
	 *
	 * @param string $url
	 * @param array $where
	 * @return void
	 */
	public static function setRouteWhere(string $requestMethod = '', string $bundle = '', string $url = '', array $where = [])
	{
		self::$RouterData[$requestMethod][$bundle][$url]['where'] = $where;
	}

	/**
	 *
	 *
	 * @param string $url
	 * @param string $name
	 * @return void
	 */
	public static function setRouteName(string $requestMethod = '', string $bundle = '', string $url = '', string $name = '')
	{
		self::$RouterData[$requestMethod][$bundle][$url]['name'] = $name;
	}

	/**
	 *
	 *
	 * @param string $url
	 * @param array $protector
	 * @return void
	 */
	public static function setRouteProtector(string $requestMethod = '', string $bundle = '', string $url = '', array $protector = [])
	{
		self::$RouterData[$requestMethod][$bundle][$url]['protector'] = $protector;
	}
}
