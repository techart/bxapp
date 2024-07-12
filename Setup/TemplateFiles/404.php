<?
include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/urlrewrite.php');

CHTTP::SetStatus("404 Not Found");
@define("ERROR_404","Y");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle('404');

$APPLICATION->IncludeComponent("bitrix:page.content",'error404',
[
	"CACHE_TYPE"=>'A',
	"CACHE_TIME"=> 3600*24,
	"CACHE_GROUPS"=>'N',
	"USE_SEO" => 'Y', // меты из админки
	"SEO_IBLOCK_CODE" => 'Metas', // инфоблок с метами (Элемент code = последний path. main для главной) + 3 свойства (title, description, keywords)
	"ENTRY_POINT" => "error404" // название entry point для данной страницы
], false);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
