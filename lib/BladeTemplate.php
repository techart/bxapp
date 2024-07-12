<?php
namespace Techart\BxApp;


use eftec\bladeone\BladeOne;


class BladeTemplate
{
	protected static $blade = false;


	/**
	 * Возвращает блейд инстанс
	 *
	 * @return object
	 */
	public static function getBlade(): object
	{
		if (!self::$blade) {
			$frontendBlocksPath = SITE_ROOT_DIR.'/'.trim($GLOBALS['APPLICATION']->GetTemplatePath('frontend'), '/').'/src/block/';

			self::$blade = new BladeOne(
				[$frontendBlocksPath,
				APP_VIEWS_PDF_DIR],
				SITE_ROOT_DIR.'/local/cache/blade'
			);
		}

		return self::$blade;
	}
}
