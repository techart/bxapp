<?php
namespace Techart\BxApp\Traits;

/**
 * Трейт предназначен для работы только внутри модели формы
 * Если модель (инфоблок) используется для формы, то надо подключать данный трейт для удобства
 *
 * После подключения нужно описать метод processForm(), который будет вызыван в бандле формы:
 *
	$props = $this->getValues();
	$formProcess = \App::model('Forms/FormAskQuestion')->processForm($props);
 */

use Bitrix\Main\Mail\Event;

trait FormModelTrait
{
	//public $eventName; // тип почтового события


	/**
	 * Метод, который используется для обработки данных пришедших из формы
	 * Именно он вызывается в экшене роута
	 * $props = $this->getValues();
	 * $formProcess = \App::model('Forms/FormAskQuestion')->processForm($props);
	 *
	 * @return void
	 */
	abstract function processForm();

	/**
	 * Выполнить проверку переданных $formData с помощью валидатора по указанным правилам $rules
	 *
	 * Про правила проверок и шаблоны сообщений смотреть тут:
	 * https://laravel.com/docs/10.x/validation#manual-customizing-the-error-messages
	 *
	 * Задание имени атрибутов: ['first_name' => 'Имя']
	 *
	 * @param array $formData
	 * @param array $rules
	 * @param array $attributeNames
	 * @param array $messages
	 * @return array
	 */
	public function validateFormData(array $formData = [], array $rules = [], array $attributeNames = [], array $messages = []): array
	{
		return \App::core('Validator')->validateFormData($formData, $rules, $attributeNames, $messages);
	}

	/**
	 * Добавляет данные формы в новый элемент инфоблока
	 *
	 * $props = [
			"NAME" => $formData['name'],
			"PROPERTY_VALUES" => [
				"FULL_NAME" => $formData['fullName'],
				"EMAIL" => $formData['email'],
				"MESSAGE" => $formData['message'],
				"PAGE" => $_SERVER['REQUEST_URI'],
			]
		]
	 *
	 * @param array $props
	 * @return array
	 */
	public function addFormToInfoblock(array $props = []): array
	{
		$default = [
			"ACTIVE" => "Y",
			"IBLOCK_SECTION_ID" => false,
		];
		$status = $this->add($default + $props);

		return $status;
	}

	/**
	 * Создает новый результат веб-формы.
	 * В случае успеха - возвращает ID нового результата, в противном случае - "false"
	 *
	 * Работает на основе CFormResult::Add()
	 *
	 * @param integer $formID
	 * @param boolean $values
	 * @param string $checkRights
	 * @param boolean $userID
	 * @return boolean|integer
	 */
	public function addFormToWebForm(int $formID, array|bool $values = false, string $checkRights = 'Y', int|bool $userID = false): bool|int
	{
		if (\CModule::IncludeModule("form")) {
			return \CFormResult::Add($formID, $values, $checkRights, $userID);
		}
		return false;
	}

	/**
	 * Создает почтовое событие для отсылки данных результата по e-mail.
	 * Возвращает "true" в случае успеха, в противном случае - "false"
	 *
	 * Работает на основе CFormResult::Mail()
	 *
	 * @param integer $resultID
	 * @param mixed $templateID
	 * @return boolean
	 */
	public function sendWebFormResultToMail(int $resultID, mixed $templateID = false): bool
	{
		if (\CModule::IncludeModule("form")) {
			return \CFormResult::Mail($resultID, $templateID);
		}
		return false;
	}

	/**
	 * Создает событие в модуле "Статистика".
	 * Возвращает "true" в случае успеха, в противном случае - "false".
	 *
	 * Работает на основе CFormResult::SetEvent()
	 *
	 * @param integer $resultID
	 * @param boolean $event1
	 * @param boolean $event2
	 * @param boolean $event3
	 * @param mixed $money
	 * @param mixed $currency
	 * @param mixed $goto
	 * @param mixed $chargeback
	 * @return boolean
	 */
	public function setEventForWebForm(int $resultID, string|bool $event1 = false,  string|bool $event2 = false,  string|bool $event3 = false, mixed $money = '', mixed $currency = '', mixed $goto = '', mixed $chargeback = 'N' ): bool
	{
		if (\CModule::IncludeModule("form")) {
			return \CFormResult::SetEvent($resultID, $event1, $event2, $event3, $money, $currency, $goto, $chargeback);
		}
		return false;
	}

	/**
	 * На основе Event::send()
	 * Тригерит эвент формы и перадёт туда данные.
	 * Имя эвента должно быть указано в public $eventName = ''; модели
	 *
	 * $fields = [
			"FULL_NAME" => $formData['fullName'],
			"EMAIL" => $formData['email'],
			"MESSAGE" => $formData['message'],
			"PAGE" => $_SERVER['REQUEST_URI'],
		]
	 *
	 * @param array $fields
	 * @param array $file
	 * @param int|string $messageId
	 * @param string $languageId
	 * @return void
	 */
	public function sendFormToEvent(array $fields = [], array $file = [], int|string $messageId = '', string $languageId = ''): void
	{
		Event::send([
			"EVENT_NAME" => $this->eventName.strtoupper($this->pid),
			"LID" => BXAPP_SITE_ID,
			"C_FIELDS" => $fields,
			'FILE' => $file,
			'MESSAGE_ID' => $messageId,
			'LANGUAGE_ID' => $languageId,
		]);
	}

	/**
	 * На основе Event::sendImmediate()
	 * Отправляет немедленно данные в эвент.
	 * Имя эвента должно быть указано в public $eventName = ''; модели
	 *
	 * $fields = [
			"FULL_NAME" => $formData['fullName'],
			"EMAIL" => $formData['email'],
			"MESSAGE" => $formData['message'],
			"PAGE" => $_SERVER['REQUEST_URI'],
		]
	 *
	 * @param array $fields
	 * @param array $file
	 * @param int|string $messageId
	 * @param string $languageId
	 * @return void
	 */
	public function sendFormToEventImmediate(array $fields = [], array $file = [], int|string $messageId = '', string $languageId = ''): void
	{
		Event::sendImmediate([
			"EVENT_NAME" => $this->eventName.strtoupper($this->pid),
			"LID" => BXAPP_SITE_ID,
			"C_FIELDS" => $fields,
			'FILE' => $file,
			'MESSAGE_ID' => $messageId,
			'LANGUAGE_ID' => $languageId,
		]);
	}


	/**
	 * Сохраняет файл по пути upload/$savePath и регистрирует его в таблице битрикса
	 * Ждёт в $file объект типа Symfony\Component\HttpFoundation\File\UploadedFile
	 * Возвращает ID сохранённого файла
	 *
	 * Работает через CFile::SaveFile
	 *
	 * @param object $file
	 * @param string $savePath
	 * @return mixed
	 */
	public function saveUploadedFile(object $file, string $savePath = 'formFiles'): mixed
	{
		$arrFile = [
			"name" => $file->getClientOriginalName(),
			"size" => $file->getSize(),
			"tmp_name" => $file->getRealPath(),
			"type" => $file->getMimeType()
		];

		return \CFile::SaveFile($arrFile, $savePath);
	}
}
