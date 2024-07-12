<?php
namespace Techart\BxApp\Core;

/**
 * Класс для генерации pdf файлов используя библиотеку dompdf
 *
 * В самом простом варианте выглядит так:
 *
 * $pdf = App::core('Pdf')->getView(
		'test',
		[
			'title' => 'Заголовок',
			'logo' => H::imgToBase64('logo.jpg'),
		],
	);
	if ($pdf) {
		$pdf->render();
		file_put_contents(APP_VIEWS_PDF_DIR.'/test.pdf', $pdf->output());
	}
 *
 * В getView() первым параметром файл шаблона из Views/Pdf, а вторым массив переменных шаблона
 * Сохранять через file_put_contents()
 *
 * Картинки передавать через переменные, предварительно завернув в H::imgToBase64()
 *
 * В Configs/Pdf.php указаны дефолтные настройки, которые модно перебить 3 параметром в App::core('Pdf')->getView()
 *
 * Так же можно взять чистый инстанс (с учётом конфига) с помощью App::core('Pdf')->getDompdf()
 * И делать с ним дальше что хочешь
 *
 * Wiki: https://github.com/dompdf/dompdf/wiki
 *
 *
 * Если нужно в одном документе иметь страницы с разной ориентацией, то придётся поставить доп.пакет
 * https://packagist.org/packages/iio/libmergepdf и потом:

	use iio\libmergepdf\Merger;

	$m = new Merger();
	$m->addRaw($dompdf->output());
	unset($dompdf);
	$m->addRaw($dompdf2->output());
	unset($dompdf2);

	file_put_contents('combined.pdf', $m->merge());
	unset($m);

 * Можно шаблоны в отдельную папку положить:
 * Views/Pdf/TestPdf/Page1.blade.php
 * Views/Pdf/TestPdf/Page2.blade.php
 *
 * И потом App::core('Pdf')->getView('TestPdf/Page1') и т.д.
 */


use Dompdf\Dompdf;
use Dompdf\Options;


class Pdf
{
	/**
	 * Возвращает инстанс Dompdf с загруженными опциями из Configs/Pdf.php смерженными с $pdfOptions
	 *
	 * @param array $pdfOptions
	 * @return object
	 */
	public function getDompdf(array $pdfOptions = []): object
	{
		$curOptions = [];
		$options = new Options();

		if (\Config::get('Pdf.APP_PDF_OPTIONS', false)) {
			$curOptions = array_merge(\Config::get('Pdf.APP_PDF_OPTIONS', []), $pdfOptions);
		} else {
			$curOptions = $pdfOptions;
		}

		$options->set($curOptions);

		return new Dompdf($options);
	}

	/**
	 * Загружает файл шаблона с имемнем $template и переданными туда переменными $vars
	 * В зависимости от значения APP_PDF_USE_BLADE в Configs/Pdf.php загружает blade.php или обычный php файл
	 *
	 * В $template можно использовать вложенность: TestPdf/TestPdf1
	 * Будет искать в: Views/Pdf/TestPdf/TestPdf1.blade.php
	 *
	 * @param string $template
	 * @param array $vars
	 * @return mixed
	 */
	private function getTemplate(string $template = '', array $vars = []): mixed
	{
		if (\Config::get('Pdf.APP_PDF_USE_BLADE', true)) {
			$templatePath = APP_VIEWS_PDF_DIR.'/'.$template.'.blade.php';
		} else {
			$templatePath = APP_VIEWS_PDF_DIR.'/'.$template.'.php';
		}

		if (file_exists($templatePath)) {
			if (\Config::get('Pdf.APP_PDF_USE_BLADE', true)) {
				\Logger::warning('PDF - файл шаблона не существует: '.$templatePath);
				return \BladeTemplate::getBlade()->run($template, $vars);
			} else {
				extract($vars);
				ob_start();
				require_once $templatePath;
				return ob_get_clean();
			}
		} else {
			\Logger::warning('PDF - файл шаблона не существует: '.$templatePath);
			return false;
		}
	}

	/**
	 * Возвращает dompdf инстанс с уже загруженным loadHtml() взятым из папки шаблонов в Views\Pdf
	 *
	 * $template - имя файла шаблона
	 * $vars - массив переменных для передачи в файл
	 * $options - массив с дефолтными опциями (можно перебить значения указанные в Configs/Pdf.php)
	 *
	 * @param string $template
	 * @param array $vars
	 * @param array $options
	 * @return object
	 */
	public function getView(string $template = '', array $vars = [], array $options = []): object
	{
		$dompdf = $this->getDompdf($options);
		$dompdf->loadHtml($this->getTemplate($template, $vars));

		return $dompdf;
	}
}
