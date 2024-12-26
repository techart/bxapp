<?php
chdir('../..');
$_SERVER['DOCUMENT_ROOT'] = getcwd();
define('BXAPP_CLI_ARGV', $argv);
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
App::cli($argv);
