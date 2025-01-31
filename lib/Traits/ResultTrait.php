<?php
namespace Techart\BxApp\Traits;

/**
 * Нужен для форматирования возвращаемых данных по API во фронтенд в обговоренном виде:
 *
	status: 'success' | 'fail'
	language: string
	cache?: true | false
	errors?: [
		type: string
		message: string
	]
	logicErrors?: [
		type: string
		name?: string
		message: string
	]
	debug?: [
		type: string
		message: string
	]
	result: [
		title: string
		message: string
		data: any
	]

	Если в классе используется CacheTrait у метода, то его статус будет в cache

	Если в классе используется ErrorTrait у метода, то его сообщения будут в ответе разбиты по ключам:
	errors = critical, error или warning - серьёзная ошибка на бэке
	logicErrors = logicErrors - ошибки логики приложения, скорее всего их надо вывести в интерфейсе на фронте
	debug = info или debug - служебные сообщения

	По дефолту status всегда 'success'
	Но если есть ошибки типа errors, то тогда status равен 'fail'

	$this->result('', $result['message'], $result['data'])
 *
 */


trait ResultTrait
{
	/**
	 * Возвращает результат в обговоренном виде
	 *
	 * @param mixed $title
	 * @param mixed $message
	 * @param mixed $data
	 * @param string $cacheID
	 * @return array
	 */
	public function result(string $title = '', string $message = '', mixed $data = '', string $cacheID = ''): array
	{
		$result = [
			'status' => 'success',
			'site' => BXAPP_SITE_ID,
			'language' => BXAPP_LANGUAGE_ID,
		];

		// если в классе используется кэш, то выводим его статус
		if (isset($this->CacheTraitFromCache)) {
			$cacheID = $this->getCacheID($cacheID);
			$result['cache'] = isset($this->CacheTraitFromCache[$cacheID]) ? $this->CacheTraitFromCache[$cacheID] : false;
		}

		// если в классе используется ErrorTrait, то обрабатываем его ошибки
		if (isset($this->ErrorTraitErrors)) {
			if (isset($this->CacheTraitFromCache)) {
				$errorID = $this->getCacheID($cacheID);
			} else {
				$errorID = $this->getErrorID($cacheID);
			}

			if (isset($this->ErrorTraitErrors[$errorID]) && count($this->ErrorTraitErrors[$errorID]) > 0) {
				foreach ($this->ErrorTraitErrors[$errorID] as $curError) {
					if ($curError['type'] == 'critical' || $curError['type'] == 'error' || $curError['type'] == 'warning') {
						$result['status'] = 'fail';
						$result['errors'][] = [
							'type' => $curError['type'],
							'message' => $curError['message'],
						];
					}
					if ($curError['type'] == 'info' || $curError['type'] == 'debug') {
						$result['debug'][] = [
							'type' => $curError['type'],
							'message' => $curError['message'],
						];
					}
					if ($curError['type'] == 'logicError') {
						$result['logicErrors'][] = [
							'type' => $curError['type'],
							'message' => $curError['message'],
						];
					}
					if ($curError['type'] == 'formError') {
						$result['logicErrors'][] = [
							'name' => $curError['name'],
							'type' => $curError['type'],
							'message' => $curError['message'],
						];
					}
				}
			}
		}

		$result['result'] = [
			'title' => $title,
			'message' => $message,
			'data' => $data,
		];

		return $result;
	}
}
