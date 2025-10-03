<?php
namespace Techart\BxApp\Cache\Router\Store\Names;

use Techart\BxApp\Cache\Router\Store\StoreInterface;

class File implements StoreInterface
{
	private $file = 'routerNames.txt';


	public function __construct()
	{

	}

	public function getFilePath()
	{
		return preg_replace('|([/]+)|s', '/', TBA_APP_CACHE_ROUTER_DIR.'/'.$this->file);
	}

	public function exists()
	{
		return file_exists($this->getFilePath());
	}

	public function get()
	{
		if ($this->exists() === false) {
			return '';
		}

		$data = file_get_contents($this->getFilePath());

		if ($data === false) {
			return false;
		}

		return unserialize($data);
	}

	public function put(mixed $data = [])
	{
		return file_put_contents($this->getFilePath(), serialize($data));
	}

	public function delete()
	{
		return unlink($this->getFilePath());
	}

	public function flush()
	{
		return unlink($this->getFilePath());
	}
}
