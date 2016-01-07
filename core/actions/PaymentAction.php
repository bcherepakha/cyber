<?php
/**
 * Created by PhpStorm.
 * User: ataev
 * Date: 02.02.15
 * Time: 12:51
 */

namespace core\actions;

use core\error\Error;
use core\request\Request;
use core\response\Response;
use provider\Provider;

class PaymentAction {
    /** @var $_provider Provider */
    private $_provider;
    private $_user;
    private $_receipt;

    public function __construct()
    {
        $this->_provider = new Provider;
    }

    public function run()
    {
        /** @var $error Error */
        $error   = Error::getInstance();
        /** @var $request Request */
        $request = Request::getInstance();

        $this->getUserInformation();

        if ( !$this->isUserExist() )
            $error->catchError(Error::USER_NOT_FOUND);

        if ( !$this->isAvailablePaymentType() )
            $error->catchError(Error::INCORRECT_VALUE_PAYMENT_TYPE);

        if ( !$this->isCorrectAmount() )
            $error->catchError(Error::INCORRECT_PAYMENT);

        $this->getReceiptInformation();

        if ( $this->isReceiptExist() ) {
            if ( $this->isCanceled() )
                $error->catchError(Error::RECEIPT_CANCEL, ['authcode'=>$this->_receipt['authcode']]);
            else
                $error->catchError(Error::INCORRECT_RECEIPT);
        }

        $this->_provider->savePayment($request);
        $this->_provider->initCallback($request);
        $this->getReceiptInformation();

        Response::getInstance()->giveXML(['authcode'=>$this->_receipt['authcode']]);
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

    private function getReceiptInformation()
    {
        $this->_receipt = $this->_provider->getReceiptInformation(Request::getInstance());
    }

    private function isReceiptExist()
    {
        return !empty( $this->_receipt );
    }

    private function isCanceled()
    {
        return $this->_receipt['canceled'];
    }
} 