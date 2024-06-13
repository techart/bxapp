<?php
namespace Techart\BxApp\Traits;

/**
 * Трейт для добавления методов логера в класс.
 * Имеет теже методы добавления ошибок,что и Logger: critical, error, warning, debug, info.
 * Сам Logger так же вызывается в методах.
 *
 * Это нужно для связи произошедших ошибок и трейта ResultTrait, через который отдаются данные на фронт.
 * Разные уровни ошибок записываются в разные ключи массива result, чтобы на фронте удобно это обработать.
 *
 * Использование в классе:
 *
 * 	$this->critical('Критикал ошибка');
	$this->error('Ошибка');
	$this->warning('Предупреждение');
	$this->info('Сообщение');
	$this->debug('Дебаг инфа');

 * Если в методе формируется cacheID, то его надо передавать вторым параметром в функциях еррор трейта
 *
 * Если ошибки не нужно добавлять в ResultTrait, то и данный трейт использовать не надо.
 * Лучше использовать логер на прямую: Logger::error('Ошибка')
 */


trait ErrorTrait
{
	protected $ErrorTraitErrors = []; // массив ошибок класса


	private function getErrorID(string $id = ''): string
	{
		return !empty($id) ? $id : get_called_class().'_'.debug_backtrace()[2]['function'];
	}

	/**
	 * Устанавливает ошибку уровня critical
	 * Ошибка передастся в трейт ResultTrait
	 * А так же добавится в глобальный Logger
	 *
	 * Если в методе формируется cacheID, то надо передать вторым параметром
	 *
	 * @param string $message
	 * @param string $cacheID
	 * @return void
	 */
	public function critical(string $message = '', string $cacheID = ''): void
	{
		$id = '';

		// если в классе используется кэш, то берём его id
		if (isset($this->CacheTraitFromCache)) {
			$id = $this->getCacheID($cacheID);
		} else {
			$id = $this->getErrorID($cacheID);
		}

		$this->ErrorTraitErrors[$id][] = [
			'type' => 'critical',
			'message' => $message,
		];
		\Logger::critical($message);
	}

	/**
	 * Устанавливает ошибку уровня error
	 * Ошибка передастся в трейт ResultTrait
	 * А так же добавится в глобальный Logger
	 *
	 * Если в методе формируется cacheID, то надо передать вторым параметром
	 *
	 * @param string $message
	 * @param string $cacheID
	 * @return void
	 */
	public function error(string $message = '', string $cacheID = ''): void
	{
		$id = '';

		// если в классе используется кэш, то берём его id
		if (isset($this->CacheTraitFromCache)) {
			$id = $this->getCacheID($cacheID);
		} else {
			$id = $this->getErrorID($cacheID);
		}

		$this->ErrorTraitErrors[$id][] = [
			'type' => 'error',
			'message' => $message,
		];
		\Logger::error($message);
	}

	/**
	 * Устанавливает ошибку уровня warning
	 * Ошибка передастся в трейт ResultTrait
	 * А так же добавится в глобальный Logger
	 *
	 * Если в методе формируется cacheID, то надо передать вторым параметром
	 *
	 * @param string $message
	 * @param string $cacheID
	 * @return void
	 */
	public function warning(string $message = '', string $cacheID = ''): void
	{
		$id = '';

		// если в классе используется кэш, то берём его id
		if (isset($this->CacheTraitFromCache)) {
			$id = $this->getCacheID($cacheID);
		} else {
			$id = $this->getErrorID($cacheID);
		}

		$this->ErrorTraitErrors[$id][] = [
			'type' => 'warning',
			'message' => $message,
		];
		\Logger::warning($message);
	}

	/**
	 * Устанавливает ошибку уровня debug
	 * Ошибка передастся в трейт ResultTrait
	 * А так же добавится в глобальный Logger
	 *
	 * Если в методе формируется cacheID, то надо передать вторым параметром
	 *
	 * @param string $message
	 * @param string $cacheID
	 * @return void
	 */
	public function debug(string $message = '', string $cacheID = ''): void
	{
		$id = '';

		// если в классе используется кэш, то берём его id
		if (isset($this->CacheTraitFromCache)) {
			$id = $this->getCacheID($cacheID);
		} else {
			$id = $this->getErrorID($cacheID);
		}

		$this->ErrorTraitErrors[$id][] = [
			'type' => 'debug',
			'message' => $message,
		];
		\Logger::debug($message);
	}

	/**
	 * Устанавливает ошибку уровня info
	 * Ошибка передастся в трейт ResultTrait
	 * А так же добавится в глобальный Logger
	 *
	 * Если в методе формируется cacheID, то надо передать вторым параметром
	 *
	 * @param string $message
	 * @param string $cacheID
	 * @return void
	 */
	public function info(string $message = '', string $cacheID = ''): void
	{
		$id = '';

		// если в классе используется кэш, то берём его id
		if (isset($this->CacheTraitFromCache)) {
			$id = $this->getCacheID($cacheID);
		} else {
			$id = $this->getErrorID($cacheID);
		}

		$this->ErrorTraitErrors[$id][] = [
			'type' => 'info',
			'message' => $message,
		];
		\Logger::info($message);
	}

	/**
	 * Устанавливает ошибку уровня logicError
	 * Ошибка передастся в трейт ResultTrait
	 *
	 * Если в методе формируется cacheID, то надо передать вторым параметром
	 *
	 * @param string $message
	 * @param string $cacheID
	 * @return void
	 */
	public function logicError(mixed $message = '', string $cacheID = ''): void
	{
		$id = '';

		// если в классе используется кэш, то берём его id
		if (isset($this->CacheTraitFromCache)) {
			$id = $this->getCacheID($cacheID);
		} else {
			$id = $this->getErrorID($cacheID);
		}

		$this->ErrorTraitErrors[$id][] = [
			'type' => 'logicError',
			'message' => $message,
		];
	}

	/**
	 * Устанавливает ошибку уровня logicError, но для полей формы
	 * Ошибка передастся в трейт ResultTrait
	 * Разница с logicError() в том, что тут вторым параметром передаётся имя инпута
	 *
	 * Если в методе формируется cacheID, то надо передать третьим параметром
	 *
	 * @param string $message
	 * @param string $cacheID
	 * @return void
	 */
	public function formError(mixed $message = '', string $name = '', string $cacheID = ''): void
	{
		$id = '';

		// если в классе используется кэш, то берём его id
		if (isset($this->CacheTraitFromCache)) {
			$id = $this->getCacheID($cacheID);
		} else {
			$id = $this->getErrorID($cacheID);
		}

		$this->ErrorTraitErrors[$id][] = [
			'type' => 'formError',
			'name' => $name,
			'message' => $message,
		];
	}
}
