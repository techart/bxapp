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
			// 'server' => 'server1',
			// 'group' => 'ss',
			'language' => 'ru', // _ОБЯЗАТЕЛЬНО_
		],
		/*'s2' => [
			'group' => 'ss',
			'server' => 'server1',
			'language' => 'en', // _ОБЯЗАТЕЛЬНО_
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
		// $this->setForStaticApi(); // для работы staticapi механизма
		$this->setForCli(); // для правильной работы cli команд

		// Если нужна своя логика определения, то её можно реализовать устанавливая значения ниже
		// $this->currentServer = '';
		// $this->currentSite = SITE_ID;
		// $this->currentLanguage = LANGUAGE_ID;
	}
}
