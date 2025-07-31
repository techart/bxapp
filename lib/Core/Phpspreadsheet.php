<?php
namespace Techart\BxApp\Core;

/**
 * Класс с небольшими полезностями для работы с эксель файлами через пакет phpoffice/phpspreadsheet
 *
 * Пакет: https://packagist.org/packages/phpoffice/phpspreadsheet
 * Документация: https://phpspreadsheet.readthedocs.io/en/stable/
 * Создание эксель файла: https://hard-skills.ru/other/excel-phpspreadsheet
 *
 * Чтение больших файлов порциями:
 * https://phpspreadsheet.readthedocs.io/en/stable/topics/reading-files/#combining-read-filters-with-the-setsheetindex-method-to-split-a-large-csv-file-across-multiple-worksheets
 *
 * Нужен стандартный ChunkReadFilter, который уже есть в этом классе в одноимённом методе
 *
 *
 * _______________ПРИМЕР. Записываем Xlsx файл:
 *
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

//Создаем экземпляр класса электронной таблицы
$spreadsheet = new Spreadsheet();
//Получаем текущий активный лист
$sheet = $spreadsheet->getActiveSheet();
// Записываем в ячейку A1 данные
$sheet->setCellValue('A1', 'Hello my Friend!');

$writer = new Xlsx($spreadsheet);
//Сохраняем файл в текущей папке, в которой выполняется скрипт.
//Чтобы указать другую папку для сохранения.
//Прописываем полный путь до папки и указываем имя файла
$writer->save('hello.xlsx');
 *
 * _______________ПРИМЕР. Читаем Xlsx файл:
 *
try {
	$totalColumns = 5; // кол-во колонок с информацией
	$chunkSize = 5; // сколько строчек обрабатывать за раз
	$reader = App::core('Phpspreadsheet')->reader('Xlsx');
	$reader->setReadDataOnly(true);
	$reader->setReadEmptyCells(false);
	$info = $reader->listWorksheetInfo(TBA_SITE_ROOT_DIR.'/Xlsx.xlsx')[0];
	$highestCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalColumns);
	$lastRow = 0;
	$chunkFilter = App::core('Phpspreadsheet')->chunkReadFilter(); // фильтр для обработки строк порциями
	$reader->setReadFilter($chunkFilter);

	// проходим файл порциями
	for ($startRow = 1; $startRow <= intval($info['totalRows']); $startRow += $chunkSize) {
		$lastRow = $startRow + $chunkSize - 1;
		$chunkFilter->setRows($startRow, $lastRow);
		$spreadsheet = $reader->load(TBA_SITE_ROOT_DIR.'/Xlsx.xlsx');
		$worksheet = $spreadsheet->getActiveSheet();
		// какую группу строк нужно вытащить
		$curRows = $worksheet->rangeToArray(
			'A'.$startRow.':'.$highestCol.$lastRow,
			null,
			false,
			false,
			true
		);

		// чистим бесполезные данные - все нужное теперь в $curRows
		$worksheet->__destruct();
		unset($worksheet);
		$spreadsheet->__destruct();
		unset($spreadsheet);
		unset($chunkFilter);
		// ============================

		dd($curRows);
	}
} catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
	// $e->getMessage();
}
 *
 */


class Phpspreadsheet
{
	/**
	 * Возвращает ридер для переданного типа $type или Xlsx, если не указан
	 *
	 * @param string $type
	 * @return object
	 */
	public function reader(string $type = 'Xlsx'): object
	{
		return \PhpOffice\PhpSpreadsheet\IOFactory::createReader($type);
	}

	/**
	 * Для порционного перебора больших эксель файлов нужно ридеру назначать фильтр через setReadFilter()
	 * Готовый класс для этого фильтра и возвращает данный метод
	 *
	 * @return ChunkReadFilter
	 */
	public function chunkReadFilter(): ChunkReadFilter
	{
		return new ChunkReadFilter();
	}
}


/**
 * Класс для фильтра порционного перебора
 */
class ChunkReadFilter implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter
{
	private $startRow = 0;
	private $endRow   = 0;

	// Set the list of rows that we want to read
	public function setRows($startRow, $chunkSize) {
		$this->startRow = $startRow;
		$this->endRow   = $startRow + $chunkSize;
	}

	public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
	{
		// Only read the heading row, and the configured rows
		if (($row == 1) || ($row >= $this->startRow && $row < $this->endRow)) {
			return true;
		}
		return false;
	}
}
