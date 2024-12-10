<?
/**
 * Файл для указания кратких методов
 */


if (!function_exists('dumpV')) {
	/**
	 * Вызывает var_dump() с переданным набором параметров
	 *
	 * @return void
	 */
	function dumpV(): void
	{
		$arguments = func_get_args();
		$lastCall = debug_backtrace()[0];

		if (!empty($arguments)) {
			echo '<pre style="margin: 2em; background-color:#f5d5b3; padding:1em; color:#000; font-size: 14px; max-height: 90vh; overflow: auto; box-shadow: 0 0 20px rgba(73, 77, 97, 0.25), 0 0 3px rgba(73, 77, 97, 0.1);"><div style="border-top: 1px dashed #000; padding: 1em;">';

			foreach ($arguments as $row) {
				echo '<div style="border-bottom: 1px dashed #000; padding: 1em;">';
					var_dump($row);
					echo '</div>';
			}
			echo '<p>FILE: '.$lastCall['file'].'; LINE:'.$lastCall['line'].'</p></div></pre>';
		}
	}
}

if (!function_exists('dumpP')) {
	/**
	 * Вызывает print_r() с переданным набором параметров
	 *
	 * @return void
	 */
	function dumpP(): void
	{
		$arguments = func_get_args();
		$lastCall = debug_backtrace()[0];

		if (!empty($arguments)) {
			echo '<pre style="margin: 2em; background-color:#cecff5; padding:1em; color:#000; font-size: 14px; max-height: 90vh; overflow: auto; box-shadow: 0 0 20px rgba(73, 77, 97, 0.25), 0 0 3px rgba(73, 77, 97, 0.1);"><div style="border-top: 1px dashed #000; padding: 1em;">';

			foreach ($arguments as $row) {
				echo '<div style="border-bottom: 1px dashed #000; padding: 1em;">';
					print_r($row);
					echo '</div>';
			}
			echo '<p>FILE: '.$lastCall['file'].'; LINE:'.$lastCall['line'].'</p></div></pre>';
		}
	}
}

if (!function_exists('dumpE')) {
	/**
	 * Вызывает var_export() с переданным набором параметров
	 *
	 * @return void
	 */
	function dumpE(): void
	{
		$arguments = func_get_args();
		$lastCall = debug_backtrace()[0];

		if (!empty($arguments)) {
			echo '<pre style="margin: 2em; background-color:#f5b3b3; padding:1em; color:#000; font-size: 14px; max-height: 90vh; overflow: auto; box-shadow: 0 0 20px rgba(73, 77, 97, 0.25), 0 0 3px rgba(73, 77, 97, 0.1);"><div style="border-top: 1px dashed #000; padding: 1em;">';

			foreach ($arguments as $row) {
				echo '<div style="border-bottom: 1px dashed #000; padding: 1em;">';
					var_export($row);
					echo '</div>';
			}
			echo '<p>FILE: '.$lastCall['file'].'; LINE:'.$lastCall['line'].'</p></div></pre>';
		}
	}
}

if (!function_exists('dumpD')) {
	/**
	 * Вызывает \Logger::debug() с переданным набором параметров
	 *
	 * @return void
	 */
	function dumpD(): void
	{
		$arguments = func_get_args();

		if (!empty($arguments)) {
			foreach ($arguments as $row) {
				\Logger::debug($row);
			}
		}
	}
}

if (!function_exists('recurseCopy')) {
	/**
	 * Рекурсивно копирует папку и файлы в ней
	 * Не копирует файлы, которые уже есть в $destinationDirectory
	 *
	 * @param string $sourceDirectory
	 * @param string $destinationDirectory
	 * @param string $childFolder
	 * @return void
	 */
	function recurseCopy(string $sourceDirectory = '', string $destinationDirectory = '', string $childFolder = ''): void
	{
		$directory = opendir($sourceDirectory);

		if (is_dir($destinationDirectory) === false) {
			mkdir($destinationDirectory);
		}

		if ($childFolder !== '') {
			if (is_dir("$destinationDirectory/$childFolder") === false) {
				mkdir("$destinationDirectory/$childFolder");
			}

			while (($file = readdir($directory)) !== false) {
				if ($file === '.' || $file === '..') {
					continue;
				}

				if (is_dir("$sourceDirectory/$file") === true) {
					recurseCopy("$sourceDirectory/$file", "$destinationDirectory/$childFolder/$file");
				} else {
					if (!file_exists("$destinationDirectory/$childFolder/$file")) {
					copy("$sourceDirectory/$file", "$destinationDirectory/$childFolder/$file");
					}
				}
			}

			closedir($directory);

			return;
		}

		while (($file = readdir($directory)) !== false) {
			if ($file === '.' || $file === '..') {
				continue;
			}

			if (is_dir("$sourceDirectory/$file") === true) {
				recurseCopy("$sourceDirectory/$file", "$destinationDirectory/$file");
			}
			else {
				if (!file_exists("$destinationDirectory/$file")) {
					copy("$sourceDirectory/$file", "$destinationDirectory/$file");
				}
			}
		}

		closedir($directory);
	}
}

if (!function_exists('explodePathString')) {
	/**
	 * Разбивает переданную строку пути по слэшам
	 * Возвращает массив, где в ключе file - последний элемент строки
	 * А в ключе dirs - массив всех остальных
	 *
	 * @param string $path
	 * @return array
	 */
	function explodePathString(string $path = ''): array
	{
		$path = explode('/', trim($path, '/'));
		$dirs = array_slice($path, 0, -1);
		$file = end($path);

		return [
			'dirs' => $dirs,
			'file' => $file,
		];
	}
}

if (!function_exists('createDirsChaine')) {
	/**
	 * В $mainDir создаёт цепочку папок указанных в массиве $dirs
	 *
	 * @param string $mainDir
	 * @param array $dirs
	 * @return void
	 */
	function createDirsChaine(string $mainDir = '', array $dirs = []): void
	{
		if (count($dirs) > 0 && !empty($mainDir)) {
			$curDir = $mainDir;

			if (!is_dir($mainDir)) {
				mkdir($mainDir);
			}

			foreach ($dirs as $dir) {
				$curDir .= '/'.$dir;

				if (!is_dir($curDir)) {
					mkdir($curDir);
				}
			}
		}
	}
}

if (!function_exists('checkCreateDir')) {
	/**
	 * Првоеряет наличие директории $dir и создаёт её, если она отсутствует
	 *
	 * @param string $dir
	 * @return void
	 */
	function checkCreateDir(string $dir = ''): void
	{
		if (!empty($dir)) {
			if (!is_dir($dir)) {
				mkdir($dir);
			}
		}
	}
}

if (!function_exists('getIblockId')) {
	/**
	 * Функция обертка для CIBlock::GetList возвращает ID инфоблока по коду $code
	 * Возвращает false в случае ошибки
	 *
	 * @param string $code
	 * @return int|bool
	 */
	function getIblockId(string $code = ""): int|bool
	{
		$result = false;

		if (!empty($code)) {
			$res = CIBlock::GetList(
				array(),
				array('SITE_ID' => SITE_ID, 'ACTIVE' => 'Y', "CODE" => $code)
			);

			if ($arRes = $res->Fetch()) {
				$result = intval($arRes["ID"]);
			}
		}

		return $result;
	}
}
