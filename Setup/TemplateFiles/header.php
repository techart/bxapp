<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<!DOCTYPE html>
<html lang="<?= TBA_LANGUAGE_ID ?>">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="format-detection" content="telephone=no">
	<title><?= $APPLICATION->ShowTitle() ?></title>
	<?php $APPLICATION->ShowHead() ?>
	<?=App::core('Assets')->showFontFace()?>
	<?=App::core('Assets')->showFaviconHtmlCode()?>
</head>
<body>
<?php
$APPLICATION->ShowPanel();
$block = App::frontend()->block('layout');
?>

<div class="<?= $block ?> <?= $block ?>--<?= $APPLICATION->ShowProperty('page_class', 'default') ?>">
	<main class="<?= $block->elem('content')  ?>">
		<!-- если pageProperty WITHOUT_CONTAINER не задан или равен false, то добавляет открывающий тег div с классом b-layout__page-container -->
		<?php $APPLICATION->AddBufferContent(array('H', 'addPageContainerIfNecessary')); ?>
