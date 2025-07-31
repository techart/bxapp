<?php
/**
 * Это конфиг для настроек Dompdf, который вызывается через App::core('Pdf')
 *
 * В "APP_PDF_USE_BLADE" можно указать использовать блейд файлы шаблонов из "Views\Pdf" или нет
 *
 * В ключе APP_PDF_OPTIONS - список опций из: https://github.com/dompdf/dompdf/blob/master/src/Options.php
 * Эти опции можно перебить при вызове App::core('Pdf')->getView() указав массив 3 параметром:
 *
 * $pdf = App::core('Pdf')->getView(
	'test',
	[],
	[
		'default_paper_orientation' => 'landscape'
	]
);

 * В log_output_file пишется статистика и ошибки (если они включены) последней генерации pdf
 * А debugLayout - рисует в пдфке границы блоков
 */
return [
	'APP_PDF_USE_BLADE' => true, // искать в "Views/Pdf" blade.php или просто .php файл
	'APP_PDF_OPTIONS' => [
		'default_font' => 'DejaVu Sans',
		'enable_remote' => true,
		'log_output_file' => TBA_APP_LOGS_DIR.'/DomPdf.html',
		'default_paper_orientation' => 'portrait', // portrait | landscape
		'default_paper_size' => 'A4',
		//'debugLayout' => true,
		//'debugPng' => true,
		//'debugCss' => true,
		//'debugKeepTemp' => true,
	]
];
