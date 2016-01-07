<?php
/**
 * Created by PhpStorm.
 * User: cherepakha
 * Date: 06.02.15
 * Time: 12:14
 */
spl_autoload_extensions(".php");
spl_autoload_register();

$config = __DIR__ . '/config/config.php';
use core\Core;
use core\signature\Signature;

Core::getInstance()->setConfig($config);
//var_dump($_SERVER);
$var = $_SERVER['HTTP_HOST'] . '/index.php?' . $_SERVER['QUERY_STRING'];
$sign = Signature::getInstance()->sign($var);
echo urlencode($sign);