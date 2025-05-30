<?php
namespace Techart\BxApp\Core;

use Bitrix\Main\SiteTable;
use \Symfony\Component\Yaml\Yaml;

/**
 * Класс для генерации Open API файла.
 */

class OpenAPI
{
	protected $fileNamePattern = 'openapi_#ID#.yaml'; // шаблон для составления названия файла
	protected $optionalInfo = ['termsOfService' => 'TERMS', 'contact' => 'CONTACT', 'license' => 'LICENSE']; // необязательные параметры, которых нет по умолчанию в конфиге
	protected $routesApiData = []; // Описания для составления роутов из бандлов в Open API файле
	protected $routes = []; // Массив урлов сгенерированный BxApp
	protected $schema; // Схемы
	protected $schemaDefault; // Дефолтные схемы BxApp
	protected $apiFile = []; // Массив с данными для Open API файла
	protected $withDefaultRoutes = false; // Учитывать или нет дефолтные урлы при генерации Open API файла
	protected $siteId = 's1'; // ID сайта
	protected $tags = []; // Описание бандлов

	/**
	 * Устанавливает нужно ли учитывать дефолтные урлы BxApp в Open API файле
	 * 
	 * @param string $withDefault
	 * @return object
	 */
	public function withDefaultRoutes(string $withDefault = '0'): object
	{
		$this->withDefaultRoutes = $withDefault === '1';

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
	 * Инициализирует роуты BxApp и получает все урлы из BxApp
	 * 
	 * @return void
	 */
	private function collectRoutes(): void
	{
		\Router::build();
		if ($this->withDefaultRoutes) {
			\Router::buildDefault();
		}

		$this->routes = \Techart\BxApp\RouterConfigurator::get();
	}

	/**
	 * Собирает массив урлов под нужный формат
	 * 
	 * @return void
	 */
	private function init(): void
	{
		$this->schema = include_once(APP_ROUTER_DIR . '/' . 'SchemaAPI.php');
		$this->schemaDefault = include_once(APP_VENDOR_DIR . '/lib/Router/SchemaAPI.php');
		$this->tags = array_merge($this->schema['TAGS'], $this->schemaDefault['TAGS']);

		if (empty(\Env::get('APP_OPENAPI_DOMAIN'))) {
			\Logger::error('Open API: В файле .env заполните значение APP_OPENAPI_DOMAIN');
		}

		$this->apiFile = [
			'openapi' => !empty($schema['OPENAPI_VERSION']) ? $schema['OPENAPI_VERSION'] : '3.0.2',
			'info' => [
				'title' => $this->schema['TITLE'],
				'description' => $this->schema['DESCRIPTION'],
				'version' => $this->schema['VERSION'],
			],
			'servers' => [
				['url' => \Env::get('APP_OPENAPI_DOMAIN', '')]
			],
			'tags' => [],
			'paths' => [],
			'components' => [
				'schemas' => $this->withDefaultRoutes ? array_merge($this->schema['SCHEMAS'], $this->schemaDefault['SCHEMAS']) : $this->schema['SCHEMAS']
			]
		];

		foreach ($this->optionalInfo as $key => $val) {
			if (!empty($this->schema[$val])) {
				$this->apiFile['info'][$key] = $this->schema[$val];
			} 
		}
	}

	/**
	 * Формирует регулярку в зависимости от BxApp параметра
	 */
	private function buildPattern(string|array $pattern = ''): string
	{
		if ($pattern == 'int') {
			$pattern = '[0-9]+';
		}
		if ($pattern == 'string') {
			$pattern = '[a-zа-я-]+';
		}
		if ($pattern == 'stringCase') {
			$pattern = '[a-zA-Zа-яА-Я-]+';
		}
		if ($pattern == 'stringEn') {
			$pattern = '[a-z-]+';
		}
		if ($pattern == 'stringEnCase') {
			$pattern = '[a-zA-Z-]+';
		}
		if ($pattern == 'stringRu') {
			$pattern = '[а-я-]+';
		}
		if ($pattern == 'stringRuCase') {
			$pattern = '[а-яА-Я-]+';
		}
		if ($pattern == 'code') {
			$pattern = '[a-zA-Z0-9-]+';
		}
		if (is_array($pattern)) {
			$pattern = '['.implode('|', $pattern).']+';
		}

		return $pattern;
	}

	/**
	 * Формирует урлы роутов, соединяя их с данными из RoutesAPI.php файлов каждого бандла
	 * 
	 * @return void
	 */
	private function formatRoutes(): void
	{
		foreach ($this->routes as $method) {
			foreach ($method as $group) {
				foreach ($group as $route) {
					if (!empty($this->tags[$route['bundle']]) && empty($this->apiFile['tags'][$route['bundle']])) {
						$this->apiFile['tags'][$route['bundle']] = [
							'name' => $route['bundle'],
							...$this->tags[$route['bundle']]
						];
					}

					// Получаем данные для бандла из RoutesAPI.php
					if (empty($this->routesApiData[$route['bundle']])) {
						if ($route['bundle'] === 'BxappDefault') {
							$this->routesApiData[$route['bundle']] = include_once(APP_VENDOR_DIR . '/lib/Router/BxappDefault/RoutesAPI.php');
						} else {
							$this->routesApiData[$route['bundle']] = include_once(APP_ROUTER_DIR . '/' . $route['bundle'] . '/' . 'RoutesAPI.php');
						}
					}

					// Формируем path параметры
					if (!empty($route['where'])) {
						foreach ($route['where'] as $param => $type) {
							$pattern = $this->buildPattern($type);

							$this->apiFile['paths'][$route['routeUrl']][$route['requestMethod']]['parameters'][] = [
								'name' => $param,
								'in' => 'path',
								'required' => true,
								'description' => 'Pattern: ' . $pattern,
								'schema' => [
									'type' => $type === 'int' ? 'integer' : 'string',
									'pattern' => $pattern
								]
							];
						}
					}

					// Формируем get параметры
					if (!empty($route['allowedQueryParams'])) {
						if (is_array($route['allowedQueryParams'])) {
							foreach ($route['allowedQueryParams'] as $param) {
								$this->apiFile['paths'][$route['routeUrl']][$route['requestMethod']]['parameters'][] = [
									'name' => $param,
									'in' => 'query',
									'required' => false
								];
							} 
						} elseif ($route['allowedQueryParams'] === true) {
							$this->apiFile['paths'][$route['routeUrl']][$route['requestMethod']]['parameters'][] = [
								'name' => 'params',
								'in' => 'query',
								'schema' => [
									'type' => 'object',
									'additionalProperties' => [
										'type' => 'string'
									]
								],
								'style' => 'form',
								'explode' => true,
								'example' => []
							];
						}
					}

					$this->apiFile['paths'][$route['routeUrl']][$route['requestMethod']]['parameters'][] = [
						'name' => 'IsSwagger',
						'in' => 'header',
						'required' => true,
						'schema' => [
							'type' => 'string'
						],
						'example' => 'true'
					];

					$this->apiFile['paths'][$route['routeUrl']][$route['requestMethod']]['tags'] = [$route['bundle']];

					if (empty($this->routesApiData[$route['bundle']][$route['name']]['requestBody']) && $route['requestMethod'] !== 'get') {
						$this->apiFile['paths'][$route['routeUrl']][$route['requestMethod']]['requestBody'] = [
							'content' => [
								'application/json' => [
									'schema' => [
										'type' => 'object'
									]
								]
							]
						];
					}

					if (isset($this->routesApiData[$route['bundle']]) && $this->routesApiData[$route['bundle']] !== false) {
						// Формируем схему ответа
						$schemaRef = $this->routesApiData[$route['bundle']][$route['name']]['responses']['200']['content']['application/json']['schema'];
						if (!empty($schemaRef)) {
							if (!empty($schemaRef['bxappResult']) && $schemaRef['bxappResult']) {
								$this->routesApiData[$route['bundle']][$route['name']]['responses']['200']['content']['application/json']['schema'] = $this->schemaDefault['SCHEMAS']['Response'];
								$this->routesApiData[$route['bundle']][$route['name']]['responses']['200']['content']['application/json']['schema']['properties']['result']['properties']['data'] = $schemaRef['data'];
							} else {
								$this->routesApiData[$route['bundle']][$route['name']]['responses']['200']['content']['application/json']['schema'] = $schemaRef['data'];
							}
						}

						// Совмещаем то что сгенерировалось и то что написано в файле RoutesAPI.php
						if (!empty($this->routesApiData[$route['bundle']][$route['name']])) {
							$this->apiFile['paths'][$route['routeUrl']][$route['requestMethod']] = array_merge_recursive($this->routesApiData[$route['bundle']][$route['name']], $this->apiFile['paths'][$route['routeUrl']][$route['requestMethod']]);
						}
					} else {
						echo "\033[0;31mДля бандла " . $route['bundle'] . " не существует файла RoutesAPI.php\033[0m".PHP_EOL;
					}

					// Если не заполнены 200 и 404, то по дефолту выставляем заглушки
					if (!isset($this->routesApiData[$route['bundle']]) || empty($this->routesApiData[$route['bundle']][$route['name']]['responses']['200'])) {
						$this->apiFile['paths'][$route['routeUrl']][$route['requestMethod']]['responses']['200']['description'] = 'Успешно';
					}
					if (!isset($this->routesApiData[$route['bundle']]) || empty($this->routesApiData[$route['bundle']][$route['name']]['responses']['404'])) {
						$this->apiFile['paths'][$route['routeUrl']][$route['requestMethod']]['responses']['404']['description'] = 'Доступ закрыт';
					}
				}
			}
		}

		$this->apiFile['tags'] = array_values($this->apiFile['tags']);
	}

	/**
	 * Возвращает название OpenAPI файла
	 *
	 * @return string
	 */
	private function getFileName(): string
	{
		return str_replace('#ID#', $this->siteId, $this->fileNamePattern);
	}

	/**
	 * Генерирует Open API файл
	 * 
	 * @return void
	 */
	public function generate(): void
	{
		$this->collectRoutes();
		$this->init();
		$this->formatRoutes();

		$yaml = Yaml::dump($this->apiFile, 2, 4, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
		file_put_contents(SiteTable::getDocumentRoot($this->siteId) . '/../' . $this->getFileName(), $yaml);
	}
}
