<?php
/**
 * Конфиг для настроек HTML-кеша.
 * 
 * Включается в .env файле параметром APP_HTML_PAGE_CACHE
 * Кеш работает только на обычных страницах.
 * Если сайт открыт на локалке, то для работы кеша в .env файле должен быть включено APP_LOCAL_FORCED_CACHE.
 * Кеш не генерируется если пройдена авторизация под администратором или с группой Разработчик
 * 
 * В файле www/.htaccess между настройкой редиректов без www на www и битрикс urlrewrite должен быть прописан следующий блок:
 * 
	# === HTML кэш страниц
		# только для GET
		RewriteCond %{REQUEST_METHOD} GET [NC]
		# без параметров
		RewriteCond %{QUERY_STRING} ^$
		# не для файлов
		RewriteCond %{REQUEST_URI} !\.[a-zA-Z0-9]{2,4}$ [NC]
		# не API
		RewriteCond %{REQUEST_URI} ^(?!.*(siteapi|staticapi|restapi|bitrix)).*$ [NC]
		# если файл кэша существует
		RewriteCond %{DOCUMENT_ROOT}/bitrix/cache/htmlPages%{REQUEST_URI}data.html -f
		RewriteRule ^ /bitrix/cache/htmlPages%{REQUEST_URI}data.html [NE,L]
	# ===
 * 
 * 
 * В папке, заданной параметром APP_HTML_CACHE_PATH генерируется HTML-кеш каждой страницы и .htaccess файл к каждому кешу, 
 * устанавливающий хедеры, заданные параметром APP_HTML_CACHE_HEADERS.
 * Контент страницы сохраняется в минифицированном виде при помощи пакета voku/HtmlMin.
 * Параметры для создания кеша указаны в параметре APP_HTML_CACHE_HTML_MIN_PARAMS.
*/

return [
	'APP_HTML_CACHE_HEADERS' => 'Header set Cache-Control "public, max-age=8640550, s-maxage=86400, stale-while-revalidate=300"', // хедеры в .htaccess кеша
	'APP_HTML_CACHE_PATH' => '/htmlPages', // папка куда сохраняется HTML-кеш страниц (относительно /bitrix/cache)
	'APP_HTML_CACHE_HTML_MIN_PARAMS' => [ // параметры формирования кеша через пакет HtmlMin
		'doOptimizeViaHtmlDomParser' => true,
		'doRemoveComments' => true,
		'doSumUpWhitespace' => true,
		'doRemoveWhitespaceAroundTags' => true,
		'doOptimizeAttributes' => true,
		'doRemoveHttpPrefixFromAttributes' => true,
		'doRemoveHttpsPrefixFromAttributes' => true,
		'doKeepHttpAndHttpsPrefixOnExternalAttributes' => true,
		'doMakeSameDomainsLinksRelative' => [],
		'doRemoveDefaultAttributes' => true,
		'doRemoveDeprecatedAnchorName' => true,
		'doRemoveDeprecatedScriptCharsetAttribute' => true,
		'doRemoveDeprecatedTypeFromScriptTag' => true,
		'doRemoveDeprecatedTypeFromStylesheetLink' => true,
		'doRemoveDeprecatedTypeFromStyleAndLinkTag' => true,
		'doRemoveDefaultMediaTypeFromStyleAndLinkTag' => true,
		'doRemoveDefaultTypeFromButton' => true,
		'doRemoveEmptyAttributes' => true,
		'doRemoveValueFromEmptyInput' => true,
		'doSortCssClassNames' => true,
		'doSortHtmlAttributes' => true,
		'doRemoveSpacesBetweenTags' => true,
		'doRemoveOmittedQuotes' => true,
		'doRemoveOmittedHtmlTags' => true,
	]
];
