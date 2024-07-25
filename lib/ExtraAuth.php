<?php
namespace Techart\BxApp;

use Bitrix\Main\Web\HttpClient;

class ExtraAuth
{
	protected static $url;

	public static function setup()
	{
		if ($url = Config::get('Auth.APP_EXTRA_AUTH_URL')) {
			self::$url = $url;
			\AddEventHandler("main", "OnUserLoginExternal", ['Techart\BxApp\ExtraAuth', 'onUserLoginExternal'], 100);
			\AddEventHandler("main", "OnExternalAuthList", array('Techart\BxApp\ExtraAuth', 'onExternalAuthList'), 100);
		}
	}

	public static function onUserLoginExternal(&$arParams)
	{
		$login = $arParams['LOGIN'];
		$password = $arParams['PASSWORD'];
		$email = self::guessEmailFor($login);
		$authId = self::guessAuthId();

		$user = \CUser::GetList($by = "timestamp_x", $order = "desc", ["LOGIN_EQUAL_EXACT" => $login])->Fetch();

		if (!$user) {
			$user = \CUser::GetList($by = "timestamp_x", $order = "desc", ["LOGIN_EQUAL_EXACT" => $email])->Fetch();
		}

		if ($user && ($authId !== $user['EXTERNAL_AUTH_ID'])) {
			return null;
		}

		$granted = self::attemptAuth($login, $password);

		if ($granted) {
			$fields = array(
				"LOGIN" => $email,
				"NAME" => $login,
				"PASSWORD" => $password,
				"EMAIL" => $email,
				"ACTIVE" => "Y",
				"EXTERNAL_AUTH_ID" => $authId,
				"LID" => SITE_ID,
			);

			if ($user) {
				$id = $user["ID"];
				(new \CUser())->Update($id, $fields);
			}

			else {
				$id = (new \CUser())->Add($fields);
			}

			if ($id > 0) {
				$groups = \CUser::GetUserGroup($id);
				if (!in_array(1, $groups)) {
					$groups[] = 1;
					\CUser::SetUserGroup($id, $groups);
				}
			}

			$arParams["store_password"] = "N";
			return $id;
		}
	}

	public static function onExternalAuthList()
	{
		return [
			'ID' => self::guessAuthId(),
			'NAME' => self::getDomain(),
		];
	}

	protected static function getDomain()
	{
		$domain = preg_replace('{^https?://}', '', self::$url);
		return preg_replace('{/.*$}', '', $domain);
	}

	protected static function guessEmailFor($login)
	{
		$domain = self::getDomain();

		if (preg_match('{\.([a-z0-9_-]+\.[a-z]+)$}', $domain, $m)) {
			$domain = $m[1];
		}
		return "{$login}@{$domain}";
	}

	protected static function guessAuthId()
	{
		$domain = preg_replace('{^https?://}', '', self::$url);
		$domain = preg_replace('{\..*$}', '', $domain);
		return ucfirst($domain);
	}

	protected static function attemptAuth($login, $password)
	{
		try {
			$http = new HttpClient();
			$http->setAuthorization($login, $password);
			$authStatus = trim($http->get(self::$url));
			if ($http->getStatus() != '200') {
				return false;
			}
			return ($authStatus == 'ok' || $authStatus == 'ok:' || preg_match('{^ok:(.+)$}', $authStatus));
		} catch (\Exception $e) {
			error_log('External auth error: ' . $e->getMessage());
		}
		return false;
	}
}
