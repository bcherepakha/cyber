<?php
/**
 * Created by PhpStorm.
 * User: ataev
 * Date: 02.02.15
 * Time: 15:35
 */
spl_autoload_extensions(".php");
spl_autoload_register();

$config = __DIR__ . '/../config/config.php';
use core\Core;

Core::getInstance()->setConfig($config)->run();
