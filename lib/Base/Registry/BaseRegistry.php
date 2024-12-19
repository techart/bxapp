<?php
namespace Techart\BxApp\Base\Registry;

/**
 * Базовый класс для файла /php_interface/BxAppRegistry.php
 */

class BaseRegistry
{
	protected $servers = [];
	protected $sites = [];
	protected $groups = [];
	protected $currentServer = null;
	protected $currentSite = null;
	protected $currentLanguage = null;


	public function getServers()
	{
		return $this->servers;
	}

	public function getSites()
	{
		return $this->sites;
	}

	public function getGroups()
	{
		return $this->groups;
	}

	public function getCurrentServer()
	{
		return $this->currentServer;
	}

	public function getCurrentSite()
	{
		return $this->currentSite;
	}

	public function getCurrentLanguage()
	{
		return $this->currentLanguage;
	}
}
