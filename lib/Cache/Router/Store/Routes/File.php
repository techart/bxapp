<?php
namespace Techart\BxApp\Cache\Router\Store\Routes;

use Bitrix\Main\IO\Directory;
use Techart\BxApp\Cache\Router\Store\StoreInterface;

class File implements StoreInterface
{
	private $file = 'data.txt';

	public function __construct()
	{

	}

	public function getFilePath(string $path = '')
	{
		return preg_replace('|([/]+)|s', '/', TBA_APP_CACHE_ROUTER_PAGES_DIR.'/'.$path.$this->file);
	}

	public function exists(string $path = '')
	{
		return file_exists($this->getFilePath($path));
	}

	public function get(string $path = '')
	{
		if ($this->exists($path) === false) {
			return '';
		}
		$data = file_get_contents($this->getFilePath($path));

		if ($data === false) {
			return false;
		}

		return unserialize($data);
	}

	public function put(mixed $data = [], string $path = '')
	{
		return file_put_contents($this->getFilePath($path), serialize($data));
	}

	public function delete(string $path = '')
	{
		return unlink($this->getFilePath($path));
	}

	public function flush()
	{
		Directory::deleteDirectory(TBA_APP_CACHE_ROUTER_PAGES_DIR);
	}
}
