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
		if (isset($_GET['staticapi']) && $_GET['staticapi'] == true) {
			define('BXAPP_IS_STATIC', true);
			define('BXAPP_IS_SITE_PAGE', false);
			define('BXAPP_ROUTER_CURRENT_REQUEST_METHOD', $_GET['method']);
			define('BXAPP_ROUTER_CURRENT_REQUEST_URL', $_GET['url']);
			unset($_GET['staticapi']);
			unset($_GET['method']);
			unset($_GET['url']);
			if (isset($_GET['serverid'])) {
				$this->currentServer = $_GET['serverid'];
				unset($_GET['serverid']);
			}
			if (isset($_GET['siteid'])) {
				$this->currentSite = $_GET['siteid'];
				unset($_GET['siteid']);
			}
			if (isset($_GET['secretkey'])) {
				$this->currentSecretKey = $_GET['secretkey'];
				unset($_GET['secretkey']);
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
