<?
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');

$APPLICATION->IncludeComponent("bitrix:page.content",'main',
[
	"CACHE_TYPE"=>'A',
	"CACHE_TIME"=> 3600*24,
	"CACHE_GROUPS"=>'N',
	"USE_SEO" => 'Y', // меты из админки
	"SEO_IBLOCK_CODE" => 'Metas', // инфоблок с метами (Элемент code = последний path. main для главной) + 3 свойства (title, description, keywords)
	"ENTRY_POINT" => "mainPage" // название entry point для данной страницы
], false);

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
