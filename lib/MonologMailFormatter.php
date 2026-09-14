<?php

namespace Techart\BxApp;

use Monolog\Formatter\NormalizerFormatter;
use Monolog\LogRecord;

class MonologMailFormatter extends NormalizerFormatter
{
	public function __construct(?string $dateFormat = null)
	{
		parent::__construct($dateFormat);
	}

	public function format(LogRecord $record): string
	{
		$record = parent::format($record);

		$output = $record['datetime'] . ' - [' . $record['level_name'] . '] - ' . $record['message'];

		if (isset($record['extra']['file'])) {
			$output .= ' (' . $record['extra']['file'] . (isset($record['extra']['line']) ? ' on line ' . $record['extra']['line'] . ')' : '');
		}

		if ($record['context']) {
			ob_start();
			dump($record['context']);
			$content = ob_get_clean();

			$output .= html_entity_decode($content);
		}

		$output .= '<br>';

		return $output;
	}

	public function formatBatch(array $records)
	{
		$message = '';

		foreach ($records as $key => $record) {
			$message .= $this->format($record);
		}

		return $message;
	}
}