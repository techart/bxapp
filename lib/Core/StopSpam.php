<?php
namespace Techart\BxApp\Core;

\Bitrix\Main\Loader::includeModule('iblock');

/**
 * Проверяет забанен ли $сurIP для добавления (ACTIVE == Y и DATE_ACTIVE_TO). Если нет, то дальше.
 *
 * Проверяет для текущего $ip кол-во запросов $reqNum за $limTime секунд
 * Если лимит не выбран, то возвращает true
 * Если лимит выбран, то возвращает false и банит $сurIP на $banTime секунд
 */

class StopSpam
{
	private $reqNum;     // кол-во разрешенных запросов
	private $limTime;  // время ограничения отправки запросов (в секундах)
	private $banTime; // время бана (в секундах)
	private $сurIP;      // текущий IP
	private $whiteList; // массив IP к которым не применяется проверка


	public function __construct ()
	{
		$this->reqNum = \Config::get('StopSpam.APP_STOP_SPAM_REQ_NUM', 4);
		$this->limTime = \Config::get('StopSpam.APP_STOP_SPAM_LIM_TIME', 300);
		$this->banTime = \Config::get('StopSpam.APP_STOP_SPAM_BAN_TIME', 604800);
		$this->сurIP = 0;
		$this->whiteList = \Config::get('StopSpam.APP_STOP_SPAM_WHITE_LIST', []);
	}

	/**
	 * Проверяет текущий запрос и банит ip, если проверка не пройдена
	 *
	 * @param mixed $code
	 * @return boolean
	 */
	public function checkAndBan(mixed $code = 0): bool
	{
		$this->сurIP = $_SERVER['HTTP_X_REAL_IP'];
		$status = false;

			if ($this->checkWhiteList()) {
				// если это локалка или если IP в белом списке, то всё хорошо и не паримся
				$status = true;
			} else {
				if ($this->checkBan()) {
					// если ip уже в бане, то отменяем обработку формы
					$status = false;
				} else {
					if ($this->checkRequestsLimit()) {
						// лимит не выбран - разрешаем обработку формы, запоминаем данный запрос
						$status = true;
						$this->addRequest($code);
					} else {
						// лимит уже выбран - отменяем обработку формы, баним ip
						$status = false;
						$this->doBan();
					}
				}
			}

		return $status;
	}

	/**
	 * Проверяет относится ли запрос к белому списку (которых не надо проверять)
	 *
	 * @return boolean
	 */
	public function checkWhiteList(): bool
	{
		if (\H::isLocalHost() || in_array($this->сurIP, $this->whiteList)) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Проверяет забанен сurIP или нет
	 *
	 * @return bool
	 */
	public function checkBan(): bool
	{
		$result = \CIBlockElement::GetList(
			[
				'DATE_ACTIVE_TO' => 'DESC'
			],
			[
				"ACTIVE" => 'Y',
				"IBLOCK_ID" => getIblockId('stop_spam_ip_banned'),
				'NAME' => $this->сurIP,
				'>=DATE_ACTIVE_TO' => ConvertTimeStamp(time(), 'FULL'), // старше данной даты
			],
			false,
			false,
			[
				'ID',
			]
		);

		if ($result->result->num_rows > 0) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Банит сurIP на banTime секунд
	 * Вызывает Logger::warning()
	 *
	 * @return void
	 */
	public function doBan(): void
	{
		$date = time() + $this->banTime;
		$el = new \CIBlockElement;
		$el->Add([
			'IBLOCK_ID' => getIblockId('stop_spam_ip_banned'),
			'NAME' => $this->сurIP,
			'DATE_ACTIVE_TO' => ConvertTimeStamp($date, 'FULL'),
		]);

		\Logger::warning("StopSpam: был забанен IP `".$this->сurIP."` на странице `".$_SERVER['REQUEST_URI']."`");
	}

	/**
	 * Проверяет для текущего сurIP кол-во запросов reqNum за limTime секунд
	 * Возвращает TRUE, если проверка пройдена
	 * Возвращает FALSE, если лимит выбран
	 *
	 * @return bool
	 */
	public function checkRequestsLimit(): bool
	{
		$date = time() - $this->limTime;

		$result = \CIBlockElement::GetList(
			[
				'ID' => 'DESC'
			],
			[
				"IBLOCK_ID" => getIblockId('stop_spam_ip_stat'),
				'NAME' => $this->сurIP,
				'>DATE_CREATE' => ConvertTimeStamp($date, 'FULL'), // старше данной даты
			],
			false,
			false,
			[
				'ID',
			]
		);

		if ($result->result->num_rows >= $this->reqNum) {
			// если лимит выбран, то проверка не пройдена
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Запоминает текущий запрос
	 *
	 * @param mixed $code
	 * @return void
	 */
	public function addRequest(mixed $code = 0): void
	{
		$el = new \CIBlockElement;
		$el->Add([
			'IBLOCK_ID' => getIblockId('stop_spam_ip_stat'),
			'NAME' => $this->сurIP,
			'PROPERTY_VALUES' => [
				'CODE' => $code,
				'URL' => $_SERVER['REQUEST_URI'],
			],
		]);
	}
}
