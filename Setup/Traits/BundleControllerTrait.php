<?php
/**
 * В этом трейте пишутся все кастомные методы для before и after урлов бандлов.
 * Сделано трейтом ибо для каждого проекта могут дописываться свои методы.
 * И поэтому, чтобы не разводить грязь в самом контроллере, всё вынесено сюда.
 *
 * '{^/api/user/authorization/checkLogin/$}' => [
		'action' => 'checkLogin',
		'controller' => 'Auth',
		'before' => 'checkRecaptchaV3|checkDomain',
		'after' => 'clearSession',
	],

 * Пример оформления методов посмотреть в оргинальном App/Base/Bundle/BaseBundleController.php - метод checkDomain()
 */


trait BundleControllerTrait
{
}
