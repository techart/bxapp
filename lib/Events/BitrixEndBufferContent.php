<?php
namespace Techart\BxApp\Events;

/**
 * Класс для обработки OnEndBufferContent эвента битрикса.
 * Регистрируется в App.php
 * Минифицирует контент страницы и записывает html в папку кэша битрикса.
 * В .htaaccess правило для чтения файла кэша, если он есть
 *
 * Кэш чистится:
 * - при очистке из админки
 * - при ./clear.sh и ./update.sh и deployTitan.sh
 * - кроном
 *
 * Локально выключен, но можно включить в энве APP_LOCAL_FORCED_CACHE=true
 */

 use \Bitrix\Main\Application;
 use voku\helper\HtmlMin;
 use WyriHaximus\HtmlCompress\Factory as HtmlCompress;
 
class BitrixEndBufferContent
{
	public static function setEvent()
	{
		AddEventHandler("main", "OnEndBufferContent", ["\Techart\BxApp\Events\BitrixEndBufferContent", "ChangeMyContent"], 1);
	}
 
	public static function ChangeMyContent(&$content)
	{
		if (!\App::core('Protector')->checkAdmin()->go() && !\App::core('Protector')->checkDeveloper()->go()) {
			// если не локалка или если локалка но включён в энве APP_LOCAL_FORCED_CACHE
			if (
				\H::isLocalHost() === false ||
				(\H::isLocalHost() && \Glob::get('APP_SETUP_LOCAL_FORCED_CACHE'))
			) {
				// если GET запрос
				if (Application::getInstance()->getContext()->getRequest()->getRequestMethod() == 'GET') {
					// если нет GET параметров
					if (count(Application::getInstance()->getContext()->getRequest()->getQueryList()) === 0) {
						$curUri = Application::getInstance()->getContext()->getRequest()->getRequestUri();

						// если не апи и не статик запрос
						if (strpos($curUri, '/'.\Config::get('Router.APP_ROUTER_PREFIX').'/') !== 0 && strpos($curUri, '/staticapi/') !== 0) {
							$cachePath = APP_BITRIX_CACHE_DIR.\Config::get('HtmlCache.APP_HTML_CACHE_PATH').$curUri;
							$htaccessFile = APP_BITRIX_CACHE_DIR.\Config::get('HtmlCache.APP_HTML_CACHE_PATH').'/.htaccess';

							if (!is_dir($cachePath)) {
								mkdir($cachePath, 0777, true);
							}
							// если файла кэша нет
							if (!file_exists($htaccessFile)) {
								$htaccessContent = '
								<IfModule mod_headers.c>
									' . \Config::get('HtmlCache.APP_HTML_CACHE_HEADERS') . '
								</IfModule>';
								file_put_contents($htaccessFile, $htaccessContent);
							}
		
							if (!file_exists($cachePath .'data.html')) {
								$htmlMin = new HtmlMin();
								$params = \Config::get('HtmlCache.APP_HTML_CACHE_HTML_MIN_PARAMS');

								if (isset($params['doOptimizeViaHtmlDomParser']) && is_bool($params['doOptimizeViaHtmlDomParser'])) {
									$htmlMin->doOptimizeViaHtmlDomParser($params['doOptimizeViaHtmlDomParser']);
								} else {
									$htmlMin->doOptimizeViaHtmlDomParser(true);
								}
								if (isset($params['doRemoveComments']) && is_bool($params['doRemoveComments'])) {
									$htmlMin->doRemoveComments($params['doRemoveComments']);
								} else {
									$htmlMin->doRemoveComments(true);
								}
								if (isset($params['doSumUpWhitespace']) && is_bool($params['doSumUpWhitespace'])) {
									$htmlMin->doSumUpWhitespace($params['doSumUpWhitespace']);
								} else {
									$htmlMin->doSumUpWhitespace(true);
								}
								if (isset($params['doRemoveWhitespaceAroundTags']) && is_bool($params['doRemoveWhitespaceAroundTags'])) {
									$htmlMin->doRemoveWhitespaceAroundTags($params['doRemoveWhitespaceAroundTags']);
								} else {
									$htmlMin->doRemoveWhitespaceAroundTags(true);
								}
								if (isset($params['doOptimizeAttributes']) && is_bool($params['doOptimizeAttributes'])) {
									$htmlMin->doOptimizeAttributes($params['doOptimizeAttributes']);
								} else {
									$htmlMin->doOptimizeAttributes(true);
								}
								if (isset($params['doRemoveHttpPrefixFromAttributes']) && is_bool($params['doRemoveHttpPrefixFromAttributes'])) {
									$htmlMin->doRemoveHttpPrefixFromAttributes($params['doRemoveHttpPrefixFromAttributes']);
								} else {
									$htmlMin->doRemoveHttpPrefixFromAttributes(true);
								}
								if (isset($params['doRemoveHttpsPrefixFromAttributes']) && is_bool($params['doRemoveHttpsPrefixFromAttributes'])) {
									$htmlMin->doRemoveHttpsPrefixFromAttributes($params['doRemoveHttpsPrefixFromAttributes']);
								} else {
									$htmlMin->doRemoveHttpsPrefixFromAttributes(true);
								}
								if (isset($params['doKeepHttpAndHttpsPrefixOnExternalAttributes']) && is_bool($params['doKeepHttpAndHttpsPrefixOnExternalAttributes'])) {
									$htmlMin->doKeepHttpAndHttpsPrefixOnExternalAttributes($params['doKeepHttpAndHttpsPrefixOnExternalAttributes']);
								} else {
									$htmlMin->doKeepHttpAndHttpsPrefixOnExternalAttributes(true);
								}
								if (isset($params['doMakeSameDomainsLinksRelative']) && is_array($params['doMakeSameDomainsLinksRelative']) && count($params['doMakeSameDomainsLinksRelative']) > 0) {
									$htmlMin->doMakeSameDomainsLinksRelative($params['doMakeSameDomainsLinksRelative']);
								} else {
									$htmlMin->doMakeSameDomainsLinksRelative([]);
								}
								if (isset($params['doRemoveDefaultAttributes']) && is_bool($params['doRemoveDefaultAttributes'])) {
									$htmlMin->doRemoveDefaultAttributes($params['doRemoveDefaultAttributes']);
								} else {
									$htmlMin->doRemoveDefaultAttributes(true);
								}
								if (isset($params['doRemoveDeprecatedAnchorName']) && is_bool($params['doRemoveDeprecatedAnchorName'])) {
									$htmlMin->doRemoveDeprecatedAnchorName($params['doRemoveDeprecatedAnchorName']);
								} else {
									$htmlMin->doRemoveDeprecatedAnchorName(true);
								}
								if (isset($params['doRemoveDeprecatedScriptCharsetAttribute']) && is_bool($params['doRemoveDeprecatedScriptCharsetAttribute'])) {
									$htmlMin->doRemoveDeprecatedScriptCharsetAttribute($params['doRemoveDeprecatedScriptCharsetAttribute']);
								} else {
									$htmlMin->doRemoveDeprecatedScriptCharsetAttribute(true);
								}
								if (isset($params['doRemoveDeprecatedTypeFromScriptTag']) && is_bool($params['doRemoveDeprecatedTypeFromScriptTag'])) {
									$htmlMin->doRemoveDeprecatedTypeFromScriptTag($params['doRemoveDeprecatedTypeFromScriptTag']);
								} else {
									$htmlMin->doRemoveDeprecatedTypeFromScriptTag(true);
								}
								if (isset($params['doRemoveDeprecatedTypeFromStylesheetLink']) && is_bool($params['doRemoveDeprecatedTypeFromStylesheetLink'])) {
									$htmlMin->doRemoveDeprecatedTypeFromStylesheetLink($params['doRemoveDeprecatedTypeFromStylesheetLink']);
								} else {
									$htmlMin->doRemoveDeprecatedTypeFromStylesheetLink(true);
								}
								if (isset($params['doRemoveDeprecatedTypeFromStyleAndLinkTag']) && is_bool($params['doRemoveDeprecatedTypeFromStyleAndLinkTag'])) {
									$htmlMin->doRemoveDeprecatedTypeFromStyleAndLinkTag($params['doRemoveDeprecatedTypeFromStyleAndLinkTag']);
								} else {
									$htmlMin->doRemoveDeprecatedTypeFromStyleAndLinkTag(true);
								}
								if (isset($params['doRemoveDefaultMediaTypeFromStyleAndLinkTag']) && is_bool($params['doRemoveDefaultMediaTypeFromStyleAndLinkTag'])) {
									$htmlMin->doRemoveDefaultMediaTypeFromStyleAndLinkTag($params['doRemoveDefaultMediaTypeFromStyleAndLinkTag']);
								} else {
									$htmlMin->doRemoveDefaultMediaTypeFromStyleAndLinkTag(true);
								}
								if (isset($params['doRemoveDefaultTypeFromButton']) && is_bool($params['doRemoveDefaultTypeFromButton'])) {
									$htmlMin->doRemoveDefaultTypeFromButton($params['doRemoveDefaultTypeFromButton']);
								} else {
									$htmlMin->doRemoveDefaultTypeFromButton(true);
								}
								if (isset($params['doRemoveEmptyAttributes']) && is_bool($params['doRemoveEmptyAttributes'])) {
									$htmlMin->doRemoveEmptyAttributes($params['doRemoveEmptyAttributes']);
								} else {
									$htmlMin->doRemoveEmptyAttributes(true);
								}
								if (isset($params['doRemoveValueFromEmptyInput']) && is_bool($params['doRemoveValueFromEmptyInput'])) {
									$htmlMin->doRemoveValueFromEmptyInput($params['doRemoveValueFromEmptyInput']);
								} else {
									$htmlMin->doRemoveValueFromEmptyInput(true);
								}
								if (isset($params['doSortCssClassNames']) && is_bool($params['doSortCssClassNames'])) {
									$htmlMin->doSortCssClassNames($params['doSortCssClassNames']);
								} else {
									$htmlMin->doSortCssClassNames(true);
								}
								if (isset($params['doSortHtmlAttributes']) && is_bool($params['doSortHtmlAttributes'])) {
									$htmlMin->doSortHtmlAttributes($params['doSortHtmlAttributes']);
								} else {
									$htmlMin->doSortHtmlAttributes(true);
								}
								if (isset($params['doRemoveSpacesBetweenTags']) && is_bool($params['doRemoveSpacesBetweenTags'])) {
									$htmlMin->doRemoveSpacesBetweenTags($params['doRemoveSpacesBetweenTags']);
								} else {
									$htmlMin->doRemoveSpacesBetweenTags(true);
								}
								if (isset($params['doRemoveOmittedQuotes']) && is_bool($params['doRemoveOmittedQuotes'])) {
									$htmlMin->doRemoveOmittedQuotes($params['doRemoveOmittedQuotes']);
								} else {
									$htmlMin->doRemoveOmittedQuotes(true);
								}
								if (isset($params['doRemoveOmittedHtmlTags']) && is_bool($params['doRemoveOmittedHtmlTags'])) {
									$htmlMin->doRemoveOmittedHtmlTags($params['doRemoveOmittedHtmlTags']);
								} else {
									$htmlMin->doRemoveOmittedHtmlTags(true);
								}
		
								$parser = HtmlCompress::constructSmallest()->withHtmlMin($htmlMin);
								$content = $parser->compress($content);
		
								file_put_contents($cachePath .'data.html', $content);
							}
						}
					}
				}
			}
		}
	}
}