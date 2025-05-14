<?php
/**
 * APP_EXTRA_AUTH_URL - урл по которому должна производиться оффисная авторизация
 */
return [
	'APP_EXTRA_AUTH_URL' => '', // можно вынести в .env, а тут написать Env::get('APP_EXTRA_AUTH_URL')
	'APP_SESSION_ACTIVE_TIME_AFTER_DESTROYED' => 60, // время активности сессии, которая помечена как destroyed (в секундах)
];
