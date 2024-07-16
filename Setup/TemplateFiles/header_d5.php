<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$block = App::frontend()->block('layout');
?>
<!DOCTYPE html>
<html lang="<?= LANGUAGE_ID ?>">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="format-detection" content="telephone=no">
	<title><?= $APPLICATION->ShowTitle() ?></title>
	<?php
	$APPLICATION->ShowCSS(); // Подключение основных файлов стилей template_styles.css и styles.css
	$APPLICATION->ShowHeadStrings(); // Отображает специальные стили, JavaScript
	$APPLICATION->ShowHeadScripts(); // Вывода служебных скриптов
	?>
	<?php $APPLICATION->ShowHead() ?>
	<?=App::core('Assets')->showFontFace()?>
	<?=App::core('Assets')->showFaviconHtmlCode()?>
</head>

<body class="<?= $block ?> <?= $block ?>--<?= $APPLICATION->ShowProperty('layout_page_mode', 'default') ?>">
	<?php
	$APPLICATION->ShowPanel();
	?>
	<header class="<?= $block->elem('header') ?>">
		<?=App::frontend()->renderBlock('layout/header', [
		])?>
	</header>
	<main class="<?= $block->elem('main')  ?>">
