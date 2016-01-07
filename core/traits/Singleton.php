<?php
/**
 * Created by PhpStorm.
 * User: ataev
 * Date: 03.02.15
 * Time: 12:59
 */

namespace core\traits;

trait Singleton {
    static private $instance;

    private function __construct() {}
    private function __clone() {}
    private function __wakeup() {}

    static public function getInstance() {
        if (empty(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }
}