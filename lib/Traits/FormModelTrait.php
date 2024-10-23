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
			"EVENT_NAME" => $this->eventName,
			"LID" => SITE_ID,
			"C_FIELDS" => $fields,
			'FILE' => $file,
			'MESSAGE_ID' => $messageId,
			'LANGUAGE_ID' => $languageId,
		]);
	}

	// ждёт что в file объект new UploadedFile
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
