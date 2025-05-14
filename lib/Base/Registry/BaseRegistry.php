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
	protected $currentSecretKey = null;
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

	public function setForStaticApi()
	{
		if (isset($_GET['bxapp_staticapi']) && $_GET['bxapp_staticapi'] == true) {
			define('BXAPP_IS_STATIC', true);
			define('BXAPP_IS_SITE_PAGE', false);
			define('BXAPP_ROUTER_CURRENT_REQUEST_METHOD', $_GET['bxapp_method']);
			define('BXAPP_ROUTER_CURRENT_REQUEST_URL', $_GET['bxapp_url']);
			unset($_GET['bxapp_staticapi']);
			unset($_GET['bxapp_method']);
			unset($_GET['bxapp_url']);
			if (isset($_GET['bxapp_serverid'])) {
				$this->currentServer = $_GET['bxapp_serverid'];
				unset($_GET['bxapp_serverid']);
			}
			if (isset($_GET['bxapp_siteid'])) {
				$this->currentSite = $_GET['bxapp_siteid'];
				unset($_GET['bxapp_siteid']);
			}
			if (isset($_GET['bxapp_secretkey'])) {
				$this->currentSecretKey = $_GET['bxapp_secretkey'];
				unset($_GET['bxapp_secretkey']);
			}
			if (isset($this->sites[$this->currentSite]['language']) && !empty($this->sites[$this->currentSite]['language'])) {
				$this->currentLanguage = $this->sites[$this->currentSite]['language'];
			}
			define('BXAPP_ROUTER_CURRENT_REQUEST_QUERY', $_GET);
		}
	}

	public function setForCli()
	{
		if (defined('BXAPP_CLI_ARGV') && $this->sites[BXAPP_CLI_ARGV[1]]) {
			$this->currentSite = BXAPP_CLI_ARGV[1];

			if (isset($this->sites[$this->currentSite]['language']) && !empty($this->sites[$this->currentSite]['language'])) {
				$this->currentLanguage = $this->sites[$this->currentSite]['language'];
			}
		}
	}
}
