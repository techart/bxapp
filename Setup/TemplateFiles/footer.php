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
		<!-- если pageProperty WITHOUT_CONTAINER не задан или равен false, то добавляет закрывающий тег div для div'a с классом b-layout__page-container -->
		<?= H::closePageContainerIfNecessary() ?>

		</main>
		<header class="<?= $block->elem('header') ?>">
		</header>
		<footer class="<?= $block->elem('footer')  ?>">
		</footer>
	</div>
</body>
</html>
