<?php
use \Techart\BxApp\Base\Registry\BaseRegistry;


class BxAppRegistry extends BaseRegistry
{
	protected $servers = [
		'server1' => [
			'ip' => '123456',
			'secretKey' => 'qwedsfcvbgfhtrqwdasrf',
			'description' => 'Самый лучший сервер',
		],
	];
	protected $sites = [
		's1' => [
			// 'group' => 'ss',
			'server' => 'server1',
			'language' => 'ru',
		],
		/*'s2' => [
			'group' => 'ss',
			'server' => 'server1',
			'language' => 'en',
			'bxappDir' => 'BxApp_s2',
			'bxappEntities' => ['env'],
		],*/
	];
	protected $groups = [
		'ss' => [
			'name' => 'Super Site',
		],
	];
	protected $currentServer = null;
	protected $currentSite = null;
	protected $currentLanguage = null;



	public function apply()
	{
		// $this->currentServer = '';
		// $this->currentSite = SITE_ID;
		// $this->currentLanguage = LANGUAGE_ID;

		// $this->setForStaticApi();
		$this->setForCli();
	}
}
