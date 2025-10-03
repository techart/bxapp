<?php
namespace Techart\BxApp\Cache\Router\Store\Config;

use Techart\BxApp\Cache\Router\Store\StoreInterface;
use Bitrix\Iblock\IblockTable;


class DB implements StoreInterface
{
	use \Techart\BxApp\Cache\Router\Store\DBStoreTrait;

	private $ibName = 'techcacheconfig';
	private $elemCode = 'config';


	public function __construct()
	{
		$this->checkModel();
	}

	public function checkIB()
	{
		if ($this->checkIBGroup()) {
			$arFilter = [
				'IBLOCK_TYPE_ID' => $this->ibType,
				'CODE' => $this->ibName,
			];
			$rsIblock = IblockTable::getList(['filter' => $arFilter]);

			if (!$rsIblock->getSelectedRowsCount()) {
				global $DB;

				$DB->StartTransaction();
				$newIBlock = new \CIBlock;
				$result = $newIBlock->Add([
					'SITE_ID' => TBA_SITE_ID,
					'LID' => TBA_SITE_ID,
					'VERSION' => 2,
					'IBLOCK_TYPE_ID' => $this->ibType,
					'ACTIVE' => 'Y',
					'NAME' => 'Конфиг роутера',
					'CODE' => $this->ibName,
					'LIST_PAGE_URL' => '',
					'SECTION_PAGE_URL' => '',
					'DETAIL_PAGE_URL' => '',
					'GROUP_ID' => [2 => 'R'],
				]);

				if (!$result) {
					$DB->Rollback();
					\Logger::error('Cache\Router\Store\Config\DB -> checkIB() не смог создать инфоблок "'.$this->ibName.'", ошибка: "'.$newIBlock->LAST_ERROR);
					$this->ibCheck = false;
				} else {
					$DB->Commit();
					$this->ibCheck = true;
				}
			} else {
				$this->ibCheck = true;
			}
		} else {
			$this->ibCheck = false;
			\Logger::error('Cache\Router\Store\Config\DB -> checkIB() не смог создать инфоблок "'.$this->ibName.'" - нет группы "'.$this->ibType.'"');
		}

		return $this->ibCheck;
	}

	public function checkModel()
	{
		if ($this->checkIB()) {
			\Techart\BxApp\AppSetup::createModel([
				'TechCache/TechCacheConfig'
			]);
			$this->ibCheckModel = true;
		} else {
			\Logger::error('Cache\Router\Store\Config\DB -> checkModel() модель не создана - инфоблока "'.$this->ibName.'" не существует');
			$this->ibCheckModel = false;
		}

		return $this->ibCheckModel;
	}

	public function get()
	{
		if ($this->exists() === false) {
			return '';
		}
		$element = \App::model('TechCache/TechCacheConfig')->getElement(['ID', 'DETAIL_TEXT'], ['CODE' => $this->elemCode]);

		if ($element) {
			$data = $this->normalizeData($element['DETAIL_TEXT'] ?? '');

			return unserialize($data) ?? '';
		} else {
			return false;
		}
	}

	public function dbGetElementID()
	{
		$element = \App::model('TechCache/TechCacheConfig')->getElement(['ID'], ['CODE' => $this->elemCode]);

		if ($element) {
			return $element['ID'] ?? false;
		} else {
			return false;
		}
	}

	public function put(mixed $data = [])
	{
		if ($this->checkModel()) {
			$elemID = $this->dbGetElementID();

			if ($elemID) {
				$result = \App::model('TechCache/TechCacheConfig')->update($elemID, ['DETAIL_TEXT' => serialize($data)]);

				if(isset($result['error']) && $result['error']) {
					\Logger::error('Cache\Router\Store\Config\DB -> put() обновить кэш не удалось: '.$result['message']);

					return false;
				} else {
					return true;
				}
			} else {
				$result = \App::model('TechCache/TechCacheConfig')->add([
					'NAME' => 'Config',
					'CODE' => $this->elemCode,
					'DETAIL_TEXT' => serialize($data),
				]);

				if(isset($result['error']) && $result['error']) {
					\Logger::error('Cache\Router\Store\Config\DB -> put() добавить кэш не удалось: '.$result['message']);

					return false;
				} else {
					return true;
				}
			}
		} else {
			return false;
		}
	}

	public function delete()
	{
		if ($this->checkModel()) {
			$elemID = $this->dbGetElementID();
			$result = \App::model('TechCache/TechCacheConfig')->delete($elemID);

			if(isset($result['error']) && $result['error']) {
				\Logger::error('Cache\Router\Store\Config\DB -> delete() удалить не удалось: '.$result['message']);

				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}

	public function exists()
	{
		if ($this->checkModel()) {
			if ($this->dbGetElementID()) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function flush()
	{
		if ($this->checkModel()) {
			$elements = \App::model('TechCache/TechCacheConfig')->getElements(['ID'], []);

			if ($elements) {
				while ($element = $elements->getNext()) {
					\App::model('TechCache/TechCacheConfig')->delete($element['ID']);
				}
			}

			return true;
		} else {
			return false;
		}
	}
}
