<?php
namespace Techart\BxApp;


/**
 * Класс для хранения экземпляров вызываемых сущностей
 *
 * Название файла сущности и класса в нём должны совпадать
 * Можно сущности распределять по папкам и указывать их через слэш - Main/MainCatalog
 * Можно передать $collect = false, чтобы не сохранять экземпляр
 *
 * Для обращения к разным сущностям есть свои методы:
 *
 * App::core()
 * Файлы ядра ищутся в php_interface/BxApp/App/Core/{$file}.php
 *
 * App::menu()
 * Файлы меню ищутся в php_interface/BxApp/Menu/{$file}.php
 * Одноимённый класс модели надо наследовать от BaseMenu
 *
 * App::model()
 * Файлы моделей ищутся в php_interface/BxApp/Models/{$file}.php
 * Одноимённый класс модели надо наследовать от BaseIblockModel
 *
 * App::module()
 * Файлы модулей ищутся в php_interface/BxApp/Modules/{$file}.php
 *
 * App::service()
 * Файлы моделей ищутся в php_interface/BxApp/Services/{$file}.php
 *
 * App::form()
 * Файлы битрикс форм ищутся в php_interface/BxApp/Forms/{$file}.php
 *
 * Можно вызвать App::instances() и получить список всех сохранённых экземпляров
 *
 * Пример вызова:
 * App::model('Main/MainAdvantages')->getElementsData()['data'];
 * App::model('Main/MainAdvantages', false)->getElementsData()['data']; - просто вернёт инстанс, не будет его запоминать
 *
 * =========
 *
 * Если в полученном классе есть метод setDefaultValues, то он будет вызван до ретурна инстанса
 * В методе должно быть описано установление дефолтных значений переменных
 * Для очистки переменных в классах с чейн вызовом
 * Как вариант можно вызывать сущность с переданным вторым параметром false - тогда будет возвращён чистый инстанс
 * При $collect = false setDefaultValues не вызывается, для этого можно использовать стандартный __construct()
 *
 * Если нужно не чистить переменные, а при каждом вызове устанавливать значения, как это делается при обычном вызове
 * классов с помощью __construct: new Class($params). То просто сделать для этих целей setup() и вызывать через чейн
 *
 * =========
 *
 * С помощью showStatistic('Models') можно вывести статистику по созданным моделям
 * Выводит в графическом виде статистику по моделям
 * Показывает id, code и путь к файлу
 * Подсвечивает красным модели с одинаковыми инфоблоками
 *
 */

class App
{
	private static $currentRouteData = [];
	protected static $frontendInstance = false;
	protected static $instances = [
		'core' => [],
		'menu' => [],
		'models' => [],
		'modules' => [],
		'services' => [],
		'forms' => [],
	]; // массив с экземплярами всех вызванных типов


	/**
	 * Запускатор BxApp
	 *
	 * @param string $initPath
	 * @return void
	 */
	public static function init(string $initPath = ''): void
	{
		Define::setDefine($initPath);

		self::setup();

		Autoload::register();
		Shutdown::register();

		AppGlobals::setGlobals();
		Logger::setup();

		BitrixPageStart::setup();

		include_once ('ShortCallFunc.php');

		include_once (APP_ROOT_DIR.'/Traits/ProtectorTrait.php');
		include_once (APP_ROOT_DIR.'/Traits/ValidatorTrait.php');
		Glob::setSiteGlobals();

		EventsModel::setEvents();
		ExtraAuth::setup();
		BitrixEpilog::setup();
	}

	/**
	 * Базовая настройка
	 *
	 * @return void
	 */
	protected static function setup(): void
	{
		if (!is_dir(APP_CACHE_DIR)) {
			mkdir(APP_CACHE_DIR);
		}
		if (!is_dir(APP_CACHE_DIR.'/blade')) {
			mkdir(APP_CACHE_DIR.'/blade');
		}
	}

	/**
	 * Аналог битриксового метода GetTemplatePath()
	 * Но, в отличии от битрикс метода, данный работает со старта init.php
	 * Если битрикс SITE_TEMPLATE_ID доступен, то работает GetTemplatePath(), а если нет, то формируется отдельно
	 *
	 * Битрикс метод, так же как и битрикс константа SITE_TEMPLATE_PATH, назначаются где-то после SITE_ID, а не вместе
	 *
	 * @param string $path
	 * @return string|boolean
	 */
	public static function getCurrentTemplatePath(string $path = ''): string|bool
	{
		if (defined('SITE_TEMPLATE_ID')) {
			return $GLOBALS['APPLICATION']->GetTemplatePath($path);
		} else {
			$curTemplatePath = '/local/templates/'.\CSite::GetCurTemplate().'/assets/';

			if (realpath(SITE_ROOT_DIR.$curTemplatePath)) {
				return $curTemplatePath;
			}
		}
	}

	/**
	 * Является обёрткой для core класса Route
	 *
	 * @return object
	 */
	public static function route(): object
	{
		return self::core('Route', false);
	}


	/**
	 * Возвращает массив с экзеплярами всех сохранённых сущностей по типам
	 *
	 * @param string $type
	 * @return array
	 */
	public static function instances(string $type = ''): array
	{
		if (empty($type)) {
			return self::$instances;
		}
		if (isset(self::$instances[$type])) {
			return self::$instances[$type];
		}
	}

	/**
	 * Возввращает путь к файлу класса. Ищет в двух местах:
	 * по прямому имени, как было передано
	 * если по прямому пути нет, то ищет в одноимённой папке с классом
	 *
	 * App::model('Test/Test') - ищет в Models/Test/Test.php
	 * App::model('Test')  - ищет в Models/Test.php, а если не найдено то ищет в Models/Test/Test.php
	 *
	 * @param string $path
	 * @param string $file
	 * @return string
	 */
	protected static function getFilePath(string $path = '', string $file = ''): string
	{
		$rootDir = ($path == 'Core') ? APP_SELF_DIR : APP_ROOT_DIR;
		$className = array_slice(explode('/', $file), -1)[0];
		$classFile = realpath($rootDir.'/'.$path.'/'.$file.'.php');

		if ($classFile === false) {
			$classFile = realpath($rootDir.'/'.$path.'/'.$file.'/'.$className.'.php');

			if ($classFile === false) {
				$classFile = '';
			}
		}

		return $classFile;
	}

	/**
	 * Возвращает сохранённый экземпляр сущности из файла $file
	 * Можно сущности распределять по папкам и указывать их в $file через слэш - Main/MainCatalog
	 * Если $collect = false, то не сохраняет экземпляр в массив
	 * $type - название типа
	 * $path - путь к папке в которой искать файл
	 * $locale - требуемый язык, передаётся далее в модель
	 *
	 * @param string $type
	 * @param string $path
	 * @param string $file
	 * @param bool $collect
	 * @param string $locale
	 * @return object
	 */
	protected static function get(string $type = '', string $path = '', string $file = '', bool $collect = true, string $locale = ''): object
	{
		$className = ($type == 'core') ? '\\Techart\\BxApp\\Core\\'.$file : array_slice(explode('/', $file), -1)[0];
		$fullEntityName = $path.'_'.$className.(!empty($locale) ? '_'.$locale : '');

		if (!isset(self::$instances[$type][$fullEntityName]) || $collect === false) {
			$classFile = self::getFilePath($path, $file);

			if ($type == 'core' && substr_count($classFile, $file) == 2) {
				$className .= '\\Validator';
			}

			// core классы берутся по неймспейсу, проверять через file_exists не надо
			if ($type == 'core' || file_exists($classFile)) {
				// не core классы надо подключить
				if ($type != 'core') {
					require_once($classFile);
				}

				if (class_exists($className)) {
					if ($collect) {
						self::$instances[$type][$fullEntityName] = new $className($locale);
					} else {
						return new $className($locale);
					}
				} else {
					Logger::error('В файле "'.$classFile.'" не найден класс "'.$className.'"!');
					throw new \LogicException('В файле "'.$classFile.'" не найден класс "'.$className.'"!');
					exit();
				}
			} else {
				Logger::error('Файл "'.$path.'/'.$file.'" не найден!');
				throw new \LogicException('Файл "'.$path.'/'.$file.'" не найден!');
				exit();
			}
		}

		if (method_exists(self::$instances[$type][$fullEntityName], 'setDefaultValues')) {
			// Если метод setDefaultValues есть в классе, то он будет вызван до ретурна инстанса
			// В методе должно быть описано установление дефолтных значений переменных
			// Для очистки переменных в классах с чейн вызовом
			// Как вариант можно вызывать класс с переданным вторым параметром false - тогда будет возвращён чистый инстанс
			// При $collect = false setDefaultValues не вызывается, для этого можно использовать стандартный __construct()
			self::$instances[$type][$fullEntityName]->setDefaultValues();
		}

		return self::$instances[$type][$fullEntityName];
	}

	/**
	 * Возвращает экземпляр класса ядра из файла $file
	 * Если $collect = false, то не сохраняет экземпляр
	 *
	 * @param string $file
	 * @param boolean $collect
	 * @return object
	 */
	public static function core(string $file = '', bool $collect = true): object
	{
		return self::get('core', 'Core', $file, $collect);
	}

	/**
	 * Возвращает экземпляр класса модели из файла $file
	 * Если $collect = false, то не сохраняет экземпляр
	 * В $locale можно передать требуемый язык: en, ru...
	 * Если $locale указана и если APP_MODEL_LOCALIZATION_MODE = 'file' и $locale != APP_LANG
	 * То модель ищется по пути Modes/_Lang/$locale/$file
	 * В противном случае модель ищется по стандартному пути
	 *
	 * @param string $file
	 * @param boolean $collect
	 * @param string $locale
	 * @return object
	 */
	public static function model(string $file = '', bool $collect = true, string $locale = ''): object
	{
		$dir = 'Models';
		$curLang = !empty($locale) ? $locale : LANGUAGE_ID;

		// если режим локализации моделей указан как "file"
		if (Config::get('App.APP_MODEL_LOCALIZATION_MODE', 'file') == 'file') {
			// если дефолтный язык не равен переданному
			if (Config::get('App.APP_LANG', 'ru') !== $curLang) {
				$dir .= '/_Lang/'.$curLang;
			}
		}

		return self::get('models', $dir, $file, $collect, $curLang);
	}

	/**
	 * Возвращает экземпляр класса модуля из файла $file
	 * Если $collect = false, то не сохраняет экземпляр
	 *
	 * @param string $file
	 * @param boolean $collect
	 * @return object
	 */
	public static function module(string $file = '', bool $collect = true): object
	{
		return self::get('modules', 'Modules', $file, $collect);
	}

	/**
	 * Возвращает экземпляр класса меню из файла $file
	 * Если $collect = false, то не сохраняет экземпляр
	 *
	 * @param string $file
	 * @param boolean $collect
	 * @return object
	 */
	public static function menu(string $file = '', bool $collect = true): object
	{
		return self::get('menu', 'Menu', $file, $collect);
	}

	/**
	 * Возвращает экземпляр класса сервиса из файла $file
	 * Если $collect = false, то не сохраняет экземпляр
	 *
	 * @param string $file
	 * @param boolean $collect
	 * @return object
	 */
	public static function service(string $file = '', bool $collect = true): object
	{
		return self::get('services', 'Services', $file, $collect);
	}

	/**
	 * --------
	 * Это задел на случай, если будут реализовываться БИТРИКС ФОРМЫ
	 * В данный момент это ни как не используется
	 * --------
	 *
	 * Возвращает экземпляр класса формы из файла $file
	 * Если $collect = false, то не сохраняет экземпляр
	 *
	 * @param string $file
	 * @param boolean $collect
	 * @return object
	 */
	/*public static function form(string $file = '', bool $collect = true): object
	{
		return self::get('forms', 'Forms', $file, $collect);
	}*/

	/**
	 * Показывает в графическом виде статистику по моделям
	 * Выводит id, code и путь к файлу
	 * Подсвечивает красным модели с одинаковыми инфоблоками
	 *
	 * @return string
	 */
	protected static function showModelsStatistic():string
	{
		$stat = [];
		$text = '';
		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator(APP_MODELS_DIR, \RecursiveDirectoryIterator::SKIP_DOTS),
			\RecursiveIteratorIterator::SELF_FIRST
		);
		$iterator->setMaxDepth(5);

		foreach ($iterator as $path => $obj) {
			if ($obj->isFile()) {
				$curPath = $obj->getPath();
				$curClassName = str_replace('.php', '', $obj->getFilename());

				if (file_exists($path)) {
					require_once($path);

					if (class_exists($curClassName)) {
						$curClass = new $curClassName();

						if (method_exists($curClass, 'getInfoblock')) {
							$curClassInfo = $curClass->getInfoblock();
							$stat['Infoblocks'][$curClassInfo['ID']][] = ['path' => $path, 'id' => $curClassInfo['ID'], 'code' => $curClassInfo['CODE']];
						} else {
							$curClassInfo = $curClass->getHighloadBlock();
							$stat['HighloadBlocks'][$curClassInfo['ID']][] = ['path' => $path, 'id' => $curClassInfo['ID'], 'code' => $curClassInfo['NAME']];
						}

					}
				}
			}
		}

		if (!empty($stat)) {
			foreach ($stat as $type => $group) {
				$text .= '<h2>'.$type.' ('.count($group).')</h2>';

				foreach ($group as $ib) {
					foreach ($ib as $v) {
						$style = count($ib) > 1 ? 'style="color: red;"' : '';
						$text .= '<p '.$style.'>'.$v['id'].' - '.$v['code'].' - '.$v['path'].'</p>';
					}
				}
			}
		}

		return $text;
	}

	/**
	 * Выводит статистику по переданному в $type типа сущности
	 *
	 * @param string $type
	 * @return void
	 */
	public static function showStatistic(string $type = ''):void
	{
		$stat = '';

		if ($type == 'Models') {
			$stat = self::showModelsStatistic();
		}

		echo $stat;
	}

	/**
	 * Обёртка для класса Frontend.
	 *
	 * @return object
	 */
	public static function frontend(): object
	{
		if (self::$frontendInstance === false) {
			self::$frontendInstance = new Frontend();
		}

		return self::$frontendInstance;
	}

	/**
	 * Запускает обработку cli команд.
	 * Используется класс Cli.
	 *
	 * @param array $argv
	 * @return void
	 */
	public static function cli($argv = []): void
	{
		\Techart\BxApp\Cli::run($argv);
	}

	/**
	 * Возвращает данные текущего роута
	 * Если указать $param, то возвратит не общий массив данных, а конкретный ключ
	 *
	 * Вернёт пустую строку при ошибке.
	 *
	 * @param string $param
	 * @return array|string
	 */
	public static function getRoute(string $param = ''): array|string
	{
		if (empty($param)) {
			return self::$currentRouteData;
		} else {
			if (isset(self::$currentRouteData[$param])) {
				return self::$currentRouteData[$param];
			} else {
				return '';
			}
		}
	}

	/**
	 * Устанавливает данные текущего роута
	 *
	 * @param array $routeData
	 * @return void
	 */
	public static function setRoute(array $routeData = []): void
	{
		self::$currentRouteData = $routeData;
	}

	/**
	 * Назначает текущему бандлу протекторы
	 *
	 * @param array $protector
	 * @return void
	 */
	public static function setBundleProtector(array $protector = []): void
	{
		Glob::set('ROUTER_BUILD_CURRENT_BUNDLE_PROTECTOR', $protector);
	}
}
