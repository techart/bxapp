<?php
/**
 * В before и after пишутся массивы, где
 * ключ - имя роута для которого применяется middleware
 * значение - массив с именами файла/класса из папки Middleware и соответствующей подпапки
 *
 * А specialBefore и specialAfter отличаются тем, что в ключе указывается урл.
 * Может использоваться для задания middleware конкрутному урлу из динамического роута.
 *
 * Например:
 *
 * 'before' => [
 * 	'route-name' => ['ChangeHeaders',]
 * ],
 */
return [
	'before' => [
	],
	'after' => [
	],
	'specialBefore' => [
	],
	'specialAfter' => [
	],
];
