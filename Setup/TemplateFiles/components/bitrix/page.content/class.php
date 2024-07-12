<?
use Bitrix\Main\Localization\Loc;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

Loc::loadMessages(__FILE__);

class PageContentClass extends \CBitrixComponent
{
    public function onPrepareComponentParams($arParams)
    {
        $result = [
            "CACHE_TYPE" => isset($arParams["CACHE_TYPE"]) ? $arParams["CACHE_TYPE"] : "A",
            "CACHE_TIME" => isset($arParams["CACHE_TIME"]) ? $arParams["CACHE_TIME"] : 3600, // one hour
            "CACHE_GROUPS" => isset($arParams["CACHE_GROUPS"]) ? $arParams["CACHE_GROUPS"] : "N",
            "H1" => isset($arParams["H1"]) ? $arParams["H1"] : "N",
            "TEASER_TITLE" => isset($arParams["TEASER_TITLE"]) ? $arParams["TEASER_TITLE"] : "N",
            "CODE" => isset($arParams["CODE"]) ? $arParams["CODE"] : false,
            'USE_SEO' => isset($arParams["USE_SEO"]) ? $arParams["USE_SEO"] : false,
            'SEO_IBLOCK_CODE' => isset($arParams["SEO_IBLOCK_CODE"]) ? $arParams["SEO_IBLOCK_CODE"] : false,
            'ENTRY_POINT' => isset($arParams["ENTRY_POINT"]) ? $arParams["ENTRY_POINT"] : false
        ];
        return $result;
    }

    private function setMetas($code, $entryPoint = '')
    {
        if($code) {
			$ep = !empty($entryPoint) ? $entryPoint : $this->arParams["ENTRY_POINT"];
            App::core('Seo')->setMetas($this->arParams["SEO_IBLOCK_CODE"], $this->arParams["ENTRY_POINT"]);
        } else {
            throw new LogicException("Не указан инфоблок для SEO");
            exit();
        }
    }

    public function executeComponent()
    {
        if($this->arParams["USE_SEO"] == 'Y')
        {
            $this->setMetas($this->arParams["SEO_IBLOCK_CODE"], $this->arParams["ENTRY_POINT"]);
        }

        if($this->arParams['ENTRY_POINT']) {
            App::core('Assets')->setEntryPoints($this->arParams['ENTRY_POINT']);
        }

        if ($this->StartResultCache(false, [($this->arParams["CACHE_GROUPS"] === "N" ? false : $USER->GetGroups()), $this->arParams]))
        {
            $this->arResult['H1'] = $this->arParams["H1"];

            $this->setResultCacheKeys([
                "H1",
            ]);

            $this->includeComponentTemplate();
        }
    }
}
