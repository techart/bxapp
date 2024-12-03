<?php
namespace Techart\BxApp\Core;

use Bitrix\Seo\SitemapTable;
use Bitrix\Main\SiteTable;
use Bitrix\Main\IO;
use Bitrix\Main\IO\Path;

use \Icamys\SitemapGenerator\Config;
use \Icamys\SitemapGenerator\SitemapGenerator;
use Bitrix\Main\Loader;

Loader::includeModule("iblock");

/**
 * Класс для генерации сайтмапа.
 */

class Sitemap
{
	protected $sitemapNamePattern = 'sitemap_#ID#.xml'; // шаблон для составления названия файла
	protected $defaultChangefreq = 'weekly'; // значение changefreq по умолчанию
	protected $defaultPriority = 0.5; // значение priority по умолчанию
	protected $modeList = ['bitrix', 'models'];
	protected $priorityList = [0.0, 0.1, 0.2, 0.3, 0.4, 0.5, 0.6, 0.7, 0.8, 0.9, 1.0];
	protected $changefreqList = ['always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'];

	protected $active = false; // Статус генерации sitemap (включена или выключена)
	protected $siteId = ''; // ID сайта
	protected $name; // Название файла sitemap, если не задано, то будет задаваться по шаблону $sitemapNamePattern
	protected $domain; // Домен сайта
	protected $protocol = 'https'; // Протокол сайта
	protected $mode = 'bitrix'; // Режим по которому формируется сайтмап (bitrix или models)
	protected $models; // Параметры для режима models
	protected $bitrix; // Параметры для режима bitrix
	protected $urls; // Свои ссылки, добавленные через методы add и urls
	protected $compression = false;

	protected $collectedUrls = []; // Массив, по которому формируется sitemap. Заполняется методами collectUrls и setUrls

	protected $docRoot; // путь до папки сайта
	protected $sitemapPath = '/'; // путь, где должен храниться sitemap, относительно /

	protected $bitrixSitemapId = 0;  // ID настроек sitemap в bitrix
	protected $settingsFromBitrix = []; // Настройки sitemap из bitrix
	protected $maxUrlsPerSitemap = 50000;




	/**
	 * Устанавливает статус генерации sitemap (включена или выключена)
	 *
	 * @param boolean $active
	 * @return object
	 */
	public function active(bool $active): object
	{
		$this->active = $active;

		return $this;
	}

	/**
	 * Устанавливает ID сайта
	 *
	 * @param string $id
	 * @return object
	 */
	public function site(string $id): object
	{
		$id = trim($id);

		if (!empty($id)) {
			$this->siteId = $id;
		}

		return $this;
	}

	/**
	 * Устанавливает название файла sitemap
	 *
	 * @param string $name
	 * @return object
	 */
	public function name(string $name = ''): object
	{
		$name = trim($name);

		if (!empty($name)) {
			$this->name = $name;
		}

		return $this;
	}

	/**
	 * Устанавливает режим генерации sitemap (models или bitrix)
	 *
	 * @param string $mode
	 * @return object
	 */
	public function mode(string $mode = 'bitrix'): object
	{
		if (in_array($mode, $this->modeList)) {
			$this->mode = $mode;
		} else {
			\Logger::error('Sitemap: Задан неверный mode');
		}

		return $this;
	}

	/**
	 * Устанавливает домен сайта
	 *
	 * @param string $domain
	 * @return object
	 */
	public function domain(string $domain = ''): object
	{
		$domain = trim($domain);

		if (!empty($domain)) {
			$this->domain = $domain;
		} else {
			$this->domain = $_SERVER['SERVER_NAME'];
			\Logger::warning('Sitemap: Задан пустой домен, значение взято из сервера: ' . $this->domain);
		}

		return $this;
	}

	/**
	 * Устанавливает протокол сайта
	 *
	 * @param string $protocol
	 * @return object
	 */
	public function protocol(string $protocol = 'https'): object
	{
		if ($protocol === 'http' || $protocol === 'https') {
			$this->protocol = $protocol;
		} else {
			$this->protocol = $_SERVER['REQUEST_SCHEME'];
			\Logger::warning('Sitemap: Задан неверный протокол, значение взято из сервера: ' . $this->protocol);
		}

		return $this;
	}

	/**
	 * Устанавливает ID настроек генерации sitemap из bitrix
	 *
	 * @param int $id
	 * @return object
	 */
	public function bitrixSitemapId(int $id): object
	{
		$this->bitrixSitemapId = $id;

		return $this;
	}

	/**
	 * Устанавливает путь, где должен храниться файл sitemap, относительно /
	 *
	 * @param string $sitemapPath
	 * @return object
	 */
	public function sitemapPath(string $sitemapPath = '/'): object
	{
		$this->sitemapPath = $sitemapPath;

		return $this;
	}

	/**
	 * Включает сжатие сгенерированного сайтмапа в архив .gz
	 *
	 * @param bool $compression
	 * @return object
	 */
	public function compression(bool $compression = false): object
	{
		$this->compression = $compression;

		return $this;
	}

	/**
	 * Устанавливает максимальное число ссылок в одном сайтмапе
	 *
	 * @param int $max
	 * @return object
	 */
	public function maxUrlsPerSitemap(int $max = 50000): object
	{
		if ($max > 0) {
			$this->maxUrlsPerSitemap = $max;
		}

		return $this;
	}

	/**
	 * Добавляет ссылку в $this->urls
	 * В $params массивом можно передать значения:
	 * time - lastmod
	 * change - changefreq
	 * priority - prioroty
	 *
	 * Если значения не заданы, установятся значения по умолчанию
	 *
	 * @param string $url
	 * @param array $params
	 * @return object
	 */
	public function add(string $url, array $params): object
	{
		$url = trim($url);

		if (empty($url)) {
			\Logger::error('Sitemap: Переданный URL пустой');
		} else {
			$time = trim($params['time']);
			if (empty($time)) {
				$time = date('c', time());
			}

			$change = trim($params['change']);
			if (empty($change)) {
				$change = $this->defaultChangefreq;
			}

			$priority = trim($params['priority']);
			if (empty($priority)) {
				$priority = $this->defaultPriority;
			}

			$this->urls[$url] = [
				'url' => $url,
				'lastmod' => $time,
				'changefreq' => $change,
				'priority' => $priority
			];
		}

		return $this;
	}

	/**
	 * Устанавливает параметры для генерации sitemap по моделям
	 *
	 * @param array $models
	 * @return object
	 */
	public function models(array $models = []): object
	{
		if (count($models) > 0) {
			$this->models = $models;
		}

		return $this;
	}

	/**
	 * Устанавливает параметры инфоблоков и файлов для генерации sitemap по настройкам bitrix
	 *
	 * @param array $params
	 * @return object
	 */
	public function bitrix(array $params = []): object
	{
		if (count($params) > 0) {
			$this->bitrix = $params;
		}

		return $this;
	}

	/**
	 * Устанавливает свои ссылки в sitemap
	 *
	 * @param array $urls
	 * @return object
	 */
	public function urls(array $urls = []): object
	{
		if (count($urls) > 0) {
			foreach($urls as $key => $url) {
				$this->add($key, $url);
			}
		}

		return $this;
	}

	/**
	 * Устанавливает массив обработанных урлов в $this->collectedUrls, готовый для генерации sitemap
	 *
	 * @param array $urls
	 * @return object
	 */
	public function setUrls(array $urls): object
	{
		$this->collectedUrls = $urls;
		return $this;
	}

	/**
	 * Устанавливает и возвращает путь к корневой папке сайта
	 *
	 * @return string
	 */
	protected function docRoot(): string
	{
		if (!$this->siteId) {
			\Logger::error('Sitemap: Не задан обязательный параметр siteId');
		} else {
			$this->docRoot = SiteTable::getDocumentRoot($this->siteId);
		}

		return $this->docRoot;
	}

	/**
	 * Сохраняет настройки генерации sitemap из битрикса в $this->settingsFromBitrix
	 *
	 * @return void
	 */
	protected function initSitemapSettingsFromBitrix(): void
	{
		if (!$this->bitrixSitemapId) {
			\Logger::error('Sitemap: Не задан обязательный параметр sitemapId');
		} else {
			$dbSitemap = SitemapTable::getById($this->bitrixSitemapId);
			$this->settingsFromBitrix = $dbSitemap->fetch();

			if ($this->settingsFromBitrix) {
				$this->site($this->settingsFromBitrix['SITE_ID']);
				$this->settingsFromBitrix['SETTINGS'] = unserialize($this->settingsFromBitrix['SETTINGS']);
			} else {
				\Logger::error('Sitemap: Указан неверный Sitemap ID');
			}
		}
	}

	/**
	 * Формирует массив для sitemap заданных в настройках битрикса файлов
	 *
	 * @param string $dir
	 * @return array
	 */
	protected function makeSitemapFilesUrlsFromBitrix(string $dir)
	{
		$data = [];

		$structure = \CSeoUtils::getDirStructure($this->settingsFromBitrix['SETTINGS']['logical'], $this->settingsFromBitrix['SITE_ID'], $dir);

		foreach ($structure as $cur) {
			if ($cur['TYPE'] == 'D') {
				$data = array_merge($data, $this->makeSitemapFilesUrlsFromBitrix($cur['DATA']['ABS_PATH']));
			} else {
				$dirKey = "/" . ltrim($cur['DATA']['ABS_PATH'], "/");
				$isDirActive = true;

				foreach ($this->settingsFromBitrix['SETTINGS']['DIR'] as $tmpDir => $isActive) {
					if (strpos($dirKey, $tmpDir) === 0) {
						if ($isActive == 'N') {
							$isDirActive = false;
						} else {
							$isDirActive = true;
						}
					}
				}

				if (($isDirActive && !isset($this->settingsFromBitrix['SETTINGS']['FILE'][$dirKey]))
					|| (isset($this->settingsFromBitrix['SETTINGS']['FILE'][$dirKey])
						&& $this->settingsFromBitrix['SETTINGS']['FILE'][$dirKey] == 'Y')) {
					if (preg_match($this->settingsFromBitrix['SETTINGS']['FILE_MASK_REGEXP'], $cur['FILE'])) {
						$f = new IO\File($cur['DATA']['PATH'], $this->settingsFromBitrix['SITE_ID']);
						$url = $this->getFileUrl($f);

						$arrLoc = array(
							'url' => $url,
							'lastmod' =>  date('c', $f->getModificationTime()),
							'priority' => $this->bitrix['files'][$url]['priority'] ?? $this->defaultPriority,
							'changefreq' => $this->bitrix['files'][$url]['change'] ?? $this->defaultChangefreq
						);

						$data[$url] = $arrLoc;
					}
				}
			}
		}

		return $data;
	}

	/**
	 * Формирует относительный путь к файлу
	 *
	 * @param
	 * @return string
	 */
	protected function getFileUrl(IO\File $f): string
	{
		static $indexNames;

		if (!is_array($indexNames)) {
			$indexNames = GetDirIndexArray();
		}

		$path = '/' . substr($f->getPath(), strlen($this->docRoot));
		$path = Path::convertLogicalToUri($path);
		$path = in_array($f->getName(), $indexNames)
		? str_replace('/' . $f->getName(), '/', $path)
		: $path;

		return '/' . ltrim($path, '/');
	}

	 /**
	 * Формирует массив урлов по настройкам битрикса
	 *
	 * @return array
	 */
	protected function makeSitemapUrlsFromBitrix(): array
	{
		$data = [];

		$this->initSitemapSettingsFromBitrix();
		if ($this->settingsFromBitrix) {
			foreach($this->settingsFromBitrix['SETTINGS']['IBLOCK_ACTIVE'] as $iblockId => $isActive) {
				if ($isActive == 'Y') {
					$iblockCode = \CIBlock::GetByID($iblockId)->GetNext()['CODE'];

					if ($this->settingsFromBitrix['SETTINGS']['IBLOCK_LIST'][$iblockId] == 'Y') {
						$item = \CIBlockElement::GetList(['SORT' => 'ASC'], ['ACTIVE' => 'Y', 'IBLOCK_ID' => $iblockId], false, false, ['LIST_PAGE_URL', 'TIMESTAMP_X'])->GetNext();

						if($item['LIST_PAGE_URL'] !== '') {
							$data[$item['LIST_PAGE_URL']] = [
								'url' => $item['LIST_PAGE_URL'],
								'lastmod' => $item['TIMESTAMP_X'],
								'changefreq' => $this->bitrix['infoblocks'][$iblockCode]['page']['change'] ?? $this->defaultChangefreq,
								'priority' => $this->bitrix['infoblocks'][$iblockCode]['page']['priority'] ?? $this->defaultPriority
							];
						}
					}

					if ($this->settingsFromBitrix['SETTINGS']['IBLOCK_SECTION'][$iblockId] == 'Y') {
						$items = \CIBlockSection::GetList([], ['ACTIVE' => 'Y', 'IBLOCK_ACTIVE' => 'Y', 'GLOBAL_ACTIVE' => 'Y', 'IBLOCK_ID' => $iblockId], false, ['SECTION_PAGE_URL', 'TIMESTAMP_X'], false);

						while ($item = $items->GetNext()) {
							if($item['SECTION_PAGE_URL'] !== '') {
								$data[$item['SECTION_PAGE_URL']] = [
									'url' => $item['SECTION_PAGE_URL'],
									'lastmod' => $item['TIMESTAMP_X'],
									'changefreq' => $this->bitrix['infoblocks'][$iblockCode]['sections']['change'] ?? $this->defaultChangefreq,
									'priority' => $this->bitrix['infoblocks'][$iblockCode]['sections']['priority'] ?? $this->defaultPriority
								];
							}
						}
					}

					if ($this->settingsFromBitrix['SETTINGS']['IBLOCK_ELEMENT'][$iblockId] == 'Y') {
						$items = \CIBlockElement::GetList([], ['ACTIVE' => 'Y', 'IBLOCK_ACTIVE' => 'Y', 'SECTION_ACTIVE' => 'Y', 'SECTION_GLOBAL_ACTIVE' => 'Y', 'IBLOCK_ID' => $iblockId], false, false, ['DETAIL_PAGE_URL', 'TIMESTAMP_X']);

						while ($item = $items->GetNext()) {
							if($item['DETAIL_PAGE_URL'] !== '') {
								$data[$item['DETAIL_PAGE_URL']] = [
									'url' => $item['DETAIL_PAGE_URL'],
									'lastmod' => $item['TIMESTAMP_X'],
									'changefreq' => $this->bitrix['infoblocks'][$iblockCode]['elements']['change'] ?? $this->defaultChangefreq,
									'priority' => $this->bitrix['infoblocks'][$iblockCode]['elements']['priority'] ?? $this->defaultPriority
								];
							}
						}
					}
				}

				foreach($this->settingsFromBitrix['SETTINGS']['DIR'] as $dir => $active) {
					$data = array_merge($data, $this->makeSitemapFilesUrlsFromBitrix($dir));
				}
			}
		} else {
			\Logger::error('Sitemap: Настройки из битрикс не были получены');
		}

		return $data;
	}

	 /**
	 * Формирует массив урлов по моделям
	 *
	 * @return array
	 */
	protected function makeSitemapUrlsFromModels(): array
	{
		$data = [];

		foreach($this->models as $key => $model) {
			$data = array_merge($data, \App::model($key)->sitemap($model));
		}

		return $data;
	}

	/**
	 * Собирает все урлы в один массив, сохраняет в $this->collectedUrls и возвращает его
	 *
	 * @return array
	 */
	protected function collectUrls(): array
	{
		$data = [];
		$this->docRoot();

		if ($this->urls) {
			$data = array_merge($data, $this->urls);
		}

		if ($this->mode === 'models') {
			$data = array_merge($data, $this->makeSitemapUrlsFromModels());
		}

		if ($this->mode === 'bitrix') {
			$data = array_merge($data, $this->makeSitemapUrlsFromBitrix());
		}

		$this->collectedUrls = $data;

		return $data;
	}

	/**
	 * Собирает все урлы в один массив, сохраняет в $this->collectedUrls и возвращает его
	 *
	 * @return array
	 */
	public function getCollectedUrls(): array
	{
		return $this->collectUrls();
	}

	/**
	 * Возвращает URL с протоколом
	 *
	 * @return string
	 */
	protected function getBaseURL(): string
	{
		if (empty($this->domain)) {
			$this->domain();
		}

		return $this->protocol . '://' . $this->domain;
	}

	/**
	 * Возвращает директорию сохранения сайтмапа
	 *
	 * @return string
	 */
	protected function getSaveDirectory(): string
	{
		$dir = realpath($this->docRoot . '/' . $this->sitemapPath);

		if (!$dir) {
			\Logger::error('Sitemap: Указан неверный путь для сохранения sitemap: ' . $this->docRoot . '/' . $this->sitemapPath);
		}

		return $dir;
	}

	/**
	 * Возвращает корректный lastmod
	 *
	 * @param string $lastmod
	 * @return object
	 */
	protected function getCorrectLastmod(string $lastmod): object
	{
		if (empty($lastmod) || strtotime($lastmod) === false) {
			$lastmod = 'now';
		}

		return new \DateTime($lastmod);
	}

	/**
	 * Возвращает корректный priority
	 *
	 * @param int|string|float|null $priority
	 * @param string $url
	 * @return float
	 */
	protected function getCorrectPriority(int|string|float|null $priority, string $url): float
	{
		$newPriority = floatval($priority);

		if (!in_array($newPriority, $this->priorityList)) {
			\Logger::info('Sitemap: Задан неправильный priority = "' . $priority . '" для ' . $url);
			$newPriority = $this->defaultPriority;
		}

		return $newPriority;
	}

	/**
	 * Возвращает корректный changefreq
	 *
	 * @param string|null $changefreq
	 * @param string $url
	 * @return string
	 */
	protected function getCorrectChangefreq(string|null $changefreq, string $url): string
	{
		$newChange = strval($changefreq);

		if (!in_array($newChange, $this->changefreqList)) {
			\Logger::info('Sitemap: Задан неправильный change = "' . $changefreq . '" для ' . $url);
			$newChange = $this->defaultChangefreq;
		}

		return $newChange;
	}

	/**
	 * Возвращает название файла сайтмапа
	 *
	 * @return string
	 */
	protected function getFileName(): string
	{
		return $this->name ? $this->name : str_replace('#ID#', $this->siteId, $this->sitemapNamePattern);
	}

	/**
	 * Генерирует sitemap по заданным урлам в $this->collectedUrls
	 *
	 * @return void
	 */
	public function create(): void
	{
		if (!$this->collectedUrls) {
			$this->collectUrls();
		}

		if (!$this->active) {
			\Logger::info('Sitemap: Генерация sitemap выключена параметром active');
		} else {
			if (count($this->collectedUrls) > 0) {
				$config = new Config();
				$config->setBaseURL($this->getBaseURL());
				$config->setSaveDirectory($this->getSaveDirectory());
				$config->setSitemapIndexURL('https://bxapptest.sharapov.techart.intranet');

				$generator = new SitemapGenerator($config);

				if ($this->compression) {
					$generator->enableCompression();
				}

				$generator->setMaxURLsPerSitemap($this->maxUrlsPerSitemap);
				$generator->setSitemapFileName($this->getFileName());
				$generator->setSitemapIndexFileName("sitemap-index.xml");

				foreach($this->collectedUrls as $item) {
					if (!empty($item['url'])) {
						$generator->addURL(
							$item['url'],
							$this->getCorrectLastmod($item['lastmod']),
							$this->getCorrectChangefreq($item['changefreq'], $item['url']),
							$this->getCorrectPriority($item['priority'], $item['url'])
						);
					}
				}

				$generator->flush();
				$generator->finalize();

				$generator->submitSitemap();
			} else {
				\Logger::error('Sitemap: Нет URL для генерации сайтмапа');
			}
		}
	}
}
