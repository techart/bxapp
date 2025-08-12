<?php
namespace Techart\BxApp;

class AppGlobals
{

	public static function setGlobals()
	{
		// сборка
		\Glob::set('APP_ENV', strval(\Env::get('APP_ENV')));
		// нужно делать статик кэш или нет
		\Glob::set('APP_SETUP_STATIC_API_ACTIVE', boolval(\Env::get('APP_STATIC_API_ACTIVE')));
		// нужно делать HTML-кеш или нет
		\Glob::set('APP_SETUP_HTML_PAGE_CACHE', boolval(\Env::get('APP_HTML_PAGE_CACHE')));
		// принудительно использовать кэш локально
		\Glob::set('APP_SETUP_LOCAL_FORCED_CACHE', boolval(\Env::get('APP_LOCAL_FORCED_CACHE')));
		// включено использования кэша в трейте или нет
		\Glob::set('APP_SETUP_CACHE_TRAIT_USE_CACHE', boolval(\Env::get('APP_TRAIT_CACHE')));
		// выводить ли лог бар на сайт?
		\Glob::set('APP_SETUP_DEBUG_BAR', boolval(\Env::get('APP_DEBUG_BAR')));
		// отправлять лог на почту?
		\Glob::set('APP_SETUP_LOG_TO_EMAIL', boolval(\Env::get('APP_LOG_TO_EMAIL')));
		// записывать в файл лога?
		\Glob::set('APP_SETUP_LOG_TO_FILE', boolval(\Env::get('APP_LOG_TO_FILE')));
		// с какого уровня сообщений отображать лог в дебагбаре
		\Glob::set('APP_SETUP_LOG_LEVEL_DEBUGBAR', strval(\Env::get('APP_LOG_LEVEL_DEBUGBAR')));
		// с какого уровня сообщений отправлять лог на почту
		\Glob::set('APP_SETUP_LOG_LEVEL_EMAIL', strval(\Env::get('APP_LOG_LEVEL_EMAIL')));
		// с какого уровня сообщений писать в файл лога
		\Glob::set('APP_SETUP_LOG_LEVEL_FILE', strval(\Env::get('APP_LOG_LEVEL_FILE')));
		// отправлять лог фронтенда на почту?
		Glob::set('APP_SETUP_LOG_FRONTEND_TO_EMAIL', boolval(Env::get('APP_LOG_FRONTEND_TO_EMAIL', false)));
		// записывать ошибки фронтенда в файл лога?
		Glob::set('APP_SETUP_LOG_FRONTEND_TO_FILE', boolval(Env::get('APP_LOG_FRONTEND_TO_FILE', false)));
		// почта для отправки лога
		\Glob::set('APP_SETUP_LOG_EMAILS', strval(\Env::get('APP_LOG_SEND_TO')));
		// роутер для API активны или нет?
		\Glob::set('APP_SETUP_API_ROUTER_ACTIVE', boolval(\Env::get('APP_ROUTER_ACTIVE')));
		// роуту надо делать кэш или нет?
		\Glob::set('APP_SETUP_API_ROUTER_CACHE_ACTIVE', boolval(\Env::get('APP_ROUTER_CACHE_ACTIVE')));
		// роутер должен работать только по https протоколу?
		\Glob::set('APP_SETUP_API_ROUTER_CHECK_HTTPS', boolval(\Env::get('APP_ROUTER_CHECK_HTTPS')));
		// если true, то капчи локально всегда правильные (не проверяется)
		\Glob::set('APP_CAPTCHA_CHECK_LOCAL', boolval(\Env::get('APP_CAPTCHA_CHECK_LOCAL')));
		// секретный ключ рекапчи
		\Glob::set('APP_RECAPTCHA_SECRET_KEY', strval(\Env::get('APP_RECAPTCHA_SECRET_KEY')));
		// секретный ключ смарткапчи
		\Glob::set('APP_SMARTCAPTCHA_SECRET_KEY', strval(\Env::get('APP_SMARTCAPTCHA_SECRET_KEY')));
		// shop id юкассы
		\Glob::set('APP_YOOKASSA_SHOP_ID', strval(\Env::get('APP_YOOKASSA_SHOP_ID')));
		// секретный ключ юкассы
		\Glob::set('APP_YOOKASSA_SECRET_KEY', strval(\Env::get('APP_YOOKASSA_SECRET_KEY')));
		// домены серверов для Open API файла
		\Glob::set('APP_OPENAPI_DOMAIN', strval(\Env::get('APP_OPENAPI_DOMAIN')));
	}
}
