<?php
namespace Techart\BxApp\Cache\Router\Store;

use Bitrix\Main\Loader;

Loader::includeModule("iblock");


trait DBStoreTrait
{
	private $ibCheckGroup = false;
	private $ibCheck = false;
	private $ibCheckModel = false;
	private $ibType = 'techCache';


	public function normalizeData(string $data = '')
	{
		$data = str_replace(PHP_EOL, '', $data);
		$data = str_replace(["\n", "\r"], '', $data);
		$data = htmlspecialchars_decode($data, ENT_QUOTES);

		return $data;
	}

	public function normalizePath(string $path = '')
	{
		return preg_replace('|([/]+)|s', '/', $path) ?? '';
	}

	public function checkIBGroup()
	{
		$ibGroupExists = \CIBlockType::GetByID($this->ibType)->Fetch();

		if ($ibGroupExists) {
			$this->ibCheckGroup = true;
		} else {
			global $DB;

			$newGroup = new \CGroup;
			$newGroup->Add([
				'ACTIVE' => 'Y',
				'NAME' => 'Разработчики',
				'USER_ID' => [1],
				'STRING_ID' => 'developer'
			]);


			$newType = new \CIBlockType;
			$DB->StartTransaction();
			$result = $newType->Add([
				'ID' => $this->ibType,
				'SECTIONS' => 'Y',
				'IN_RSS'    => 'N',
				'SORT'      => 100,
				'LANG' => [
					'ru' => [
						'NAME' => 'Кэш',
						'SECTION_NAME' => '',
						'ELEMENT_NAME' => ''
					],
					'en' => [
						'NAME' => 'КЭш',
						'SECTION_NAME' => '',
						'ELEMENT_NAME' => ''
					],
				]
			]);

			if (!$result) {
				$this->ibCheckGroup  =false;
				\Logger::error('StoreTrait -> checkIBGroup() не смог создать группу инфоблоков "'.$this->ibType.'", ошибка:'.$newType->LAST_ERROR);
				$DB->Rollback();
			} else {
				$DB->Commit();
				$this->ibCheckGroup = true;
			}
		}

		return $this->ibCheckGroup;
	}

	public function checkIB()
	{
		return $this->ibCheck;
	}

	public function checkModel()
	{
		return $this->ibCheckModel;
	}
}
