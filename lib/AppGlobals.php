<?php
namespace Techart\BxApp;

class AppGlobals
{

	public static function setGlobals()
	{
		// сборка
		\Glob::set('APP_ENV', strval(\Env::get('APP_ENV', 'prod')));
		// нужно делать статик кэш или нет
		\Glob::set('APP_SETUP_STATIC_API_ACTIVE', boolval(\Env::get('APP_STATIC_API_ACTIVE', true)));
		// принудительно использовать кэш локально
		\Glob::set('APP_SETUP_LOCAL_FORCED_CACHE', boolval(\Env::get('APP_LOCAL_FORCED_CACHE', false)));
		// включено использования кэша в трейте или нет
		\Glob::set('APP_SETUP_CACHE_TRAIT_USE_CACHE', boolval(\Env::get('APP_TRAIT_CACHE', true)));
		// выводить ли лог бар на сайт?
		\Glob::set('APP_SETUP_LOG_BAR', boolval(\Env::get('APP_LOG_BAR', true)));
		// отправлять лог на почту?
		\Glob::set('APP_SETUP_LOG_TO_EMAIL', boolval(\Env::get('APP_LOG_TO_EMAIL', true)));
		// записывать в файл лога?
		\Glob::set('APP_SETUP_LOG_TO_FILE', boolval(\Env::get('APP_LOG_TO_FILE', true)));
		// с какого уровня сообщений отправлять лог на почту
		\Glob::set('APP_SETUP_LOG_LEVEL_EMAIL', strval(\Env::get('APP_LOG_LEVEL_EMAIL', 'warning')));
		// с какого уровня сообщений писать в файл лога
		\Glob::set('APP_SETUP_LOG_LEVEL_FILE', strval(\Env::get('APP_LOG_LEVEL_FILE', 'debug')));
		// отправлять лог фронтенда на почту?
		Glob::set('APP_SETUP_LOG_FRONTEND_TO_EMAIL', boolval(Env::get('APP_LOG_FRONTEND_TO_EMAIL', false)));
		// записывать ошибки фронтенда в файл лога?
		Glob::set('APP_SETUP_LOG_FRONTEND_TO_FILE', boolval(Env::get('APP_LOG_FRONTEND_TO_FILE', false)));
		// почта для отправки лога
		\Glob::set('APP_SETUP_LOG_EMAILS', strval(\Env::get('APP_LOG_SEND_TO', '')));
		// роутер для API активны или нет?
		\Glob::set('APP_SETUP_API_ROUTER_ACTIVE', boolval(\Env::get('APP_ROUTER_ACTIVE', true)));
		// роуту надо делать кэш или нет?
		\Glob::set('APP_SETUP_API_ROUTER_CACHE_ACTIVE', boolval(\Env::get('APP_ROUTER_CACHE_ACTIVE', true)));
		// роутер должен работать только по https протоколу?
		\Glob::set('APP_SETUP_API_ROUTER_CHECK_HTTPS', boolval(\Env::get('APP_ROUTER_CHECK_HTTPS', false)));
		// если true, то капчи локально всегда правильные (не проверяется)
		\Glob::set('APP_CAPTCHA_CHECK_LOCAL', boolval(\Env::get('APP_CAPTCHA_CHECK_LOCAL', true)));
	}
}
