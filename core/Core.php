<?php
/**
 * Created by PhpStorm.
 * User: ataev
 * Date: 30.01.15
 * Time: 12:30
 */
namespace core;

use core\actions\ActionFactory;
use core\request\Request;
use core\traits\Singleton;

class Core {
    use Singleton;

    private $_config;

    public function setConfig($config)
    {
        $this->_config = require_once $config;
        return $this;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->_config;
    }

    public function run()
    {
        /**
         * @var $request Request
         */
        $request = Request::getInstance();
        $request->checkRequest();
//        $request->checkSign();

        $actionFactory = new ActionFactory();
        $action = $actionFactory->getAction($request->_action);
        $action->run();
    }
}

