<?
namespace Techart\BxApp;

/**
 * Класс дебаг бара
 * Хранит в себе порядок вызова различных сущностей bxapp, фронтенд-блоков и компонентов.
 *
 * Для включения дебаг-бара на сайте необходимо в .env файле включить параметр APP_DEBUG_BAR.
 * Дебаг-бар работает только на локальных сайтах.
 *
 * В дебаг баре вывод сообщений разделён по табам:
 * - Сообщения логгера
 * - Таймлайн вызова всех сущностей, фронтенд-блоков и компонентов
 * - Таймлайн вызова фронтенд-блоков
 * - Таймлайн вызова компонентов page.content
 * - Таймлайн вызова сущностей BxApp
 * - Вызовы кеша моделей и компонентов с возможность удаления одного или всех кешей
 * - Значения в .env файле
 *
 * Вывод дебагбара:
 *
 * DebugBar::showDebugBar();
 */

class DebugBar
{
	protected static $timeline = []; // массив с таймлайном вызова
	protected static $cache = []; // массив с получением данных из кеша
	protected static $countLog = 0; // количество сообщений логгера
	protected static $countCache = 0; // количество вызванных данных из кеша

	/**
	 * Проверка надо ли отображать дебаг бар
	 * 
	 * @return bool
	 */
	public static function checkSetup(): bool
	{
		$check = false;

		if (\Glob::get('APP_SETUP_DEBUG_BAR', true)) {
			if (\H::isLocalHost()) {
				$check = true;
			}
		}

		return $check;
	}

	/**
	 * Добавляет вызов сущности в таймлайн
	 *
	 * @param string $type
	 * @param string $name
	 * @param string $path
	 * @param string $callFrom
	 * @param int $line
	 * @param array $data
	 * @return void
	 */
	public static function add(string $type = '', string $name = '', string $path = '', string $callFrom = '', int $line = 0, array $data = []): void
	{
		if (!empty($type) && !empty($name)) {
			$date = new \DateTime();
			self::$timeline[] = [
				'type' => $type, // entity / view
				'name' => $name,
				'path' => $path,
				'call_from' => $callFrom,
				'line' => $line,
				'time' => $date->format('H:i:s,u'),
				'data' => $data
			];
		}
	}

	/**
	 * Добавляет вызов кеша
	 * 
	 * @param string $type
	 * @param string $name
	 * @param string $path
	 * @param string $callFrom
	 * @return void
	 */
	public static function addCache(string $type = '', string $name = '', string $path = '', string $callFrom = ''): void
	{
		if (!empty($type) && !empty($name)) {
			self::$cache[] = [
				'type' => $type,
				'name' => $name,
				'path' => $path,
				'call_from' => $callFrom,
				'button' => [
					'link' => $path
				]
			];
		}
		self::$countCache++;
	}

	/**
	 * Выводит логер бар на сайт
	 *
	 * @return void
	 */
	public static function showDebugBar(): void
	{
		if (self::checkSetup()) {
			$loggerLog = self::getLoggerLog(\Glob::get('APP_SETUP_LOG_LEVEL_DEBUGBAR'));
			echo '
			<link rel="stylesheet" href="/local/vendor/techart/bxapp/lib/Assets/debugbar.css">
			<div class="tba_debug_bar__collapsedOpener' . (self::$countLog === 0 ? ' no-error' : '') . '" onClick="collapseDebugBar()">'.(self::$countLog > 0 ? self::$countLog : 'D').'</div>
			<div class="tba_debug_bar" data-prefix="'.\Config::get('Router.APP_ROUTER_PREFIX').'">
				<div class="tba_debug_bar__head">
					<div class="tba_debug_bar__count" title="logger" onClick="toggleBody(0)"><span>'.self::$countLog.'</span></div>
					<div class="tba_debug_bar__count" title="cache" onClick="toggleBody(5)"><span>'.self::$countCache.'</span></div>
					<div class="tba_debug_bar__resizeLine"></div>
				</div>
				<div class="tba_debug_bar__body">
					<div class="tba_debug_bar__panel">
						<div class="tba_debug_bar__tabs">
							<div class="tba_debug_bar__tab active">Logger</div>
							<div class="tba_debug_bar__tab">Timeline</div>
							<div class="tba_debug_bar__tab">Frontend</div>
							<div class="tba_debug_bar__tab">Components</div>
							<div class="tba_debug_bar__tab">Entities</div>
							<div class="tba_debug_bar__tab">Cache</div>
							<div class="tba_debug_bar__tab">Env</div>
						</div>
						<div class="tba_debug_bar__control">
							<div class="tba_debug_bar__controlBtn" onClick="expandDebugBar()"><img src="/local/vendor/techart/bxapp/lib/Assets/svg/expand.svg"></div>
							<div class="tba_debug_bar__controlBtn" onClick="collapseDebugBar()"><img src="/local/vendor/techart/bxapp/lib/Assets/svg/collapse.svg"></div>
							<div class="tba_debug_bar__controlBtn" onClick="closeDebugBar()"><img src="/local/vendor/techart/bxapp/lib/Assets/svg/close.svg"></div>
						</div>
					</div>
					<div class="tba_debug_bar__body-content vis">'.$loggerLog.'</div>
					<div class="tba_debug_bar__body-content">'.self::getLog().'</div>
					<div class="tba_debug_bar__body-content">'.self::getLog('view').'</div>
					<div class="tba_debug_bar__body-content">'.self::getLog('component').'</div>
					<div class="tba_debug_bar__body-content">'.self::getLog('entity').'</div>
					<div class="tba_debug_bar__body-content">'.self::getCacheLog().'</div>
					<div class="tba_debug_bar__body-content">'.var_export(\Env::get(), true).'</div>
				</div>
			</div>
			<script src="/local/vendor/techart/bxapp/lib/Assets/debugbar.js"></script>';
		}
	}

	/**
	 * Собирает в строку сообщения из переданного массива
	 *
	 * @param array $messages
	 * @param bool $showTime
	 * @param bool $showType
	 * @param bool $showData
	 * @return string
	 */
	protected static function buildLog(array $messages = [], bool $showTime = false, bool $showType = false, bool $showData = false): string
	{
		$text = '';

		foreach ($messages as $item) {
			$text .= '<div class="tba_debug_bar__line">';
			if ($showTime) {
				$text .= '<b>[' . $item['time'] . '] </b>';
			}
			if ($showType) {
				$text .= '<b>' . $item['type'] . '</b>: ';
			}
			$text .= '<b>' . $item['name'] . '</b>';
			if (!empty($item['path'])) {
				$text .= ' | <span title=' . $item['path'] . '>' . str_replace(TBA_SITE_ROOT_DIR, '', $item['path']) . '</span>';
			}
			if (!empty($item['call_from'])) {
				$text .= ' | <span class="tba_debug_bar__calledFrom" title=' . $item['call_from'] . '>' . str_replace(TBA_SITE_ROOT_DIR, '', $item['call_from']);

				if ($item['line'] > 0) {
					$text .= ' on line ' . $item['line'];
				}
				$text .= '</span>';
			}
			if (isset($item['button']) && !empty($item['button'])) {
				$text .= ' <button class="tba_debug_bar__button deleteBtn" data-link="' . $item['button']['link'] . '">x</button>';
			}
			if ($showData && !empty($item['data'])) {
				$text .= '<br><br>';

				foreach ($item['data'] as $key => $value) {
					$text .= $key . ' => ' . (is_array($value) ? var_export($value, true) : $value) . '<br>';
				}
			}
			$text .=  '</div>';
		}

		return $text;
	}

	/**
	 * Собирает в строку сообщения для лога, тип которых не меньше переданного $level
	 *
	 * @param string $level
	 * @return string
	 */
	public static function getLoggerLog(string $level = 'warning'): string
	{
		$text = '';
		$typeID = array_search($level, Log::$types);
		$titleFiles = [];
		$messages = Log::getMessages();

		if (count($messages) > 0) {
			foreach ($messages as $file => $data) {
				foreach ($data as $v) {
					if ($v['typeID'] <= $typeID) {
						if (!in_array($file, $titleFiles)) {
							$text .= '<b>['.$file.'.log] ('.TBA_APP_LOGS_DIR.'/'.$file.'.log)</b>';
							$titleFiles[] = $file;
						}

						if ($v['typeID'] == array_search('frontendError', Log::$types)) {
							$text .= '<div class="tba_debug_bar__line"><b>' . $v['date']."</b> - <b>[".strtoupper($v['type'])."]</b> - ".var_export($v['message'], true).'</div>';
						} else {
							$text .= '<div class="tba_debug_bar__line"><b>' . $v['date']."</b> - <b>[".strtoupper($v['type'])."]</b> - ".var_export($v['message'], true)." <span class='tba_debug_bar__calledFrom'>(".$v['file']." on line ".$v['line'].")</span>".'</div>';
						}
						self::$countLog++;
					}
				}
			}
		}

		return $text;
	}

	/**
	 * Собирает сообщения переданного типа. Если вызвать ничего не передавая, то выводит весь таймлайн
	 * Типы: view, entity, component
	 *
	 * @param string $type
	 * @return string
	 */
	public static function getLog(string $type = ''): string
	{
		$messages = self::$timeline;

		if (!empty($type)) {
			$messages = array_filter($messages, function($elem) use ($type) {
				return $elem['type'] === $type ? true : false;
			});
		}

		$isTimeline = empty($type) ? true : false;

		return self::buildLog($messages, $isTimeline, $isTimeline, !$isTimeline);
	}

	/**
	 * Собирает лог кеша с кнопками
	 * 
	 * @return string
	 */
	public static function getCacheLog(): string
	{
		$text = '<div class="tba_debug_bar__buttons">
			<button class="tba_debug_bar__button" id="cacheClearAll">Очистить кеш страницы</button>
		</div>';

		return $text . self::buildLog(self::$cache, false, true);
	}
}
