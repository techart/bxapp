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

			$blade = new BladeOne(
				[$frontendBlocksPath,
				APP_VIEWS_PDF_DIR],
				SITE_ROOT_DIR.'/local/cache/blade'
			);
			$blade->setCompileTypeFileName('md5');

			// возможность редактировать кэш шаблона до его сохранения
			/*$blade->compileCallbacks[] = static function (&$content, $templatename = null) {
				$content = '<?PHP Glob::set("BLADE_TEMPLATE_NAME", "'.$templatename.'++"); ?>'.$content;
			};*/

			self::$blade = $blade;
		}

		return self::$blade;
	}
}
