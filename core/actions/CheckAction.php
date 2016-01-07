<?php
/**
 * Created by PhpStorm.
 * User: ataev
 * Date: 02.02.15
 * Time: 12:50
 */

namespace core\actions;

use core\error\Error;
use core\request\Request;
use core\response\Response;
use provider\Provider;

class CheckAction {
    /** @var $_provider Provider */
    private $_provider;
    private $_user;

    public function __construct()
    {
        $this->_provider = new Provider;
    }

    public function run()
    {
        /** @var $error Error */
        $error = Error::getInstance();

        $this->getUserInformation();

        if ( !$this->isUserExist() )
            $error->catchError(Error::USER_NOT_FOUND);

        if ( !$this->isAvailablePaymentType() )
            $error->catchError(Error::INCORRECT_VALUE_PAYMENT_TYPE);

        if ( !$this->isCorrectAmount() )
            $error->catchError(Error::INCORRECT_PAYMENT);

        Response::getInstance()->giveXML();
    }

    private function getUserInformation()
    {
        $this->_user = $this->_provider->getUserInformation(Request::getInstance());
    }

    private  function isUserExist()
    {
        return !empty( $this->_user );
    }

    private function isAvailablePaymentType()
    {
        /** @var $request Request */
        $request = Request::getInstance();

        return in_array( $request->_type, $this->_user['availablePaymentTypes'] );
    }

    private function isCorrectAmount()
    {
        return $this->_provider->isCorrectAmount(Request::getInstance());
    }
} 