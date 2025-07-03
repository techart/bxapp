<?php
namespace Router\BxappDefault\Controllers;

class Actions extends \BaseRouterController
{
	public function logger()
	{
		$props = $this->getValues('post');
		// тут договориться и в $props сделать массив с типами ошибок и обрабатывать их тут

		if (isset($props['type']) && isset($props['text'])) {
			\Logger::add($props['type'], $props['text'], 'frontendError');
		}

		return $this->result('', '', []);
	}

	public function getSessionData()
	{
		$data = null;
		$props = $this->getValues();
		$session = \Bitrix\Main\Application::getInstance()->getSession();

		if ($props['store'])
		{
			if ($session->has($props['store']))
			{
				$data = $session->get($props['store']);
			}
		} else {
			if ($session->has('stores'))
			{
				$stores = $session->get('stores');
				$data = [];

				foreach($stores as $store)
				{
					$data[$store] = $session->get($store);
				}
			}
		}

		return $this->result('', '', $data);
	}

	public function updateSessionData()
	{
		$props = $this->getValues();
		$session = \Bitrix\Main\Application::getInstance()->getSession();

		if (isset($props['key']) && isset($props['data'])) {
			if (!$session->has($props['key']))
			{
				$stores = $session->get('stores');
				$stores[] = $props['key'];
				$session->set('stores', $stores);
			}
	
			$session->set($props['key'], $props['data']);
	
			return $this->result('', 'ok', true);
		} else {
			\App::core('main')->do404();
		}
	}

	public function removeSessionData()
	{
		$props = $this->getValues();
		$session = \Bitrix\Main\Application::getInstance()->getSession();

		if ($props['store'])
		{
			if ($session->has($props['store']))
			{
				$stores = $session->get('stores');
				unset($stores[array_search($props['store'], $stores)]);
				$session->set('stores', $stores);

				$session->remove($props['store']);
			} else {
				return $this->result('', 'store not found', false);
			}
		} else {
			if ($session->has('stores'))
			{
				$stores = $session->get('stores');
				$session->remove('stores');

				foreach($stores as $store)
				{
					$session->remove($store);
				}
			} else {
				return $this->result('', 'store not found', false);
			}
		}

		return $this->result('', 'ok', true);
	}

	public function createNextSession()
	{
		$data = ['action' => 'createSession', 'PHPSESSID' => session_id()];
		return $this->result('', '', $data);
	}

	public function checkNextSession()
	{
		$data = [];

		if (isset($_COOKIE['PHPSESSID']) && !empty($_COOKIE['PHPSESSID'])) {
			// if (\App::core('Protector')->checkNextSessionID()) {
			// 	session_regenerate_id(true);
			// }

			$data = ['action' => 'updateSession', 'PHPSESSID' => session_id()];
		}

		return $this->result('', '', $data);
	}
}