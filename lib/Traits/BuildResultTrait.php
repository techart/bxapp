<?php
namespace Techart\BxApp\Traits;

/**
 * Нужен для форматирования возвращаемых данных функциями в обговоренном виде:
 *
	error: true | false
	message: string
	data: []
 *
 * В основном используется для работы с бандлами.
 *
 * Если бандлу нужно получить данные из модуля/сервиса, то он ожидает получить:
 * - указание на успех/ошибку
 * - текстовое сообщение о результате
 * - возможный набор данных
 *
 * Например:
 *
 * $result = $this->buildResult([], true, 'Пользователь с таким именем не найден');
 *
 * или
 *
 * $this->buildResult([
		'user' => $userEmail,
		'checkWord' => $checkWord,
		'dispatchTime' => time(),
	],
	false, 'Проверочный код был успешно отправлен на почту');

	А контроллер бандла на основе этих данных сформирует ответ во фронтенд (для чего использует ResultTrait).

	По надобности можно использовать не только для общения с бандлами, но и в целом в приложении
 */

trait BuildResultTrait
{
	/**
	 * Возвращает массив с ключами error, message и data со значениями одноимённых параметров
	 *
	 * $error - если true, значит произошла ошибка
	 *
	 * @param mixed $data
	 * @param boolean $isError
	 * @param string $message
	 * @return array
	 */
	public function buildResult(mixed $data = [], bool $isError = false, string $message = ''): array
	{
		return [
			'error' => $isError,
			'message' => $message,
			'data' => $data,
		];
	}
}
