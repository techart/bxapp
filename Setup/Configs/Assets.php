<?php
/**
 * В данный момент два ключа
 *
 * APP_ASSETS_FONT_FACE_CODE - вставляет код  шрифтов в хэд через <?=App::core('Assets')->showFontFace()?>
 * APP_ASSETS_FAVICON_CODE - вставляет код фавиконов в хэд через <?=App::core('Assets')->showFaviconHtmlCode()?>
 *
 * Файлы фавиконов хранить в /local/favicon-files/
 */

return [
	'APP_ASSETS_FONT_FACE_CODE' => '
		<style>
			@font-face {
				font-family: Inter;
				font-style: normal;
				font-display: swap;
				font-weight: 400;
				src: url("/local/templates/site/assets/fonts/Inter/inter-cyrillic-400-normal.woff2") format("woff2");
			}
		</style>
	',
	'APP_ASSETS_FAVICON_CODE' => '
	',
];
