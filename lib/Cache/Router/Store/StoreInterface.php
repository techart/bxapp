<?php
namespace Techart\BxApp\Cache\Router\Store;


interface StoreInterface
{
	public function __construct();
	public function get();
	public function put(mixed $data = []);
	public function delete();
	public function exists();
	public function flush();
}
