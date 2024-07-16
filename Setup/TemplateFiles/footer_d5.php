<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$block = App::frontend()->block('layout');
?>
	<?=App::frontend()->renderBlock('layout/cookies-disclaimer')?>
	</main>
	<footer class="<?= $block->elem('footer')  ?>">
		<?=App::frontend()->renderBlock('layout/footer', [
		])?>
	</footer>
</body>
</html>
