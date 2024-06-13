<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

ob_start();
$APPLICATION->IncludeComponent(
	"bitrix:menu",
	".default",
	array(
		"ROOT_MENU_TYPE" => "top",
	),
	false
);
$topMenu = ob_get_clean();

$block = App::frontend()->block('layout');
?>
		</main>
		<header class="<?= $block->elem('header') ?>">
		</header>
		<footer class="<?= $block->elem('footer')  ?>">
		</footer>
	</div>
</body>
</html>
