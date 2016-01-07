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

class CancelAction {
    private $_provider;
    private $_receipt;

    public function __construct()
    {
        $this->_provider = new Provider;
    }

    public function run()
    {
        /** @var $error Error */
        $error = Error::getInstance();

        $this->getReceiptInformation();

        if ( !$this->isReceiptExist() )
            $error->catchError(Error::INCORRECT_RECEIPT);

        if ( !$this->isCancelAvailable() )
            $error->catchError(Error::PAYMENT_CAN_NOT_BE_CANCELED);

        if ( $this->isCanceled() )
            $error->catchError(Error::RECEIPT_CANCEL, ['authcode'=>$this->_receipt['authcode']]);

        $this->cancelPayment();
        $this->initCallback();
        Response::getInstance()->giveXML(['authcode'=>$this->_receipt['authcode']]);
    }

    private function getReceiptInformation()
    {
        $this->_receipt = $this->_provider->getReceiptInformation(Request::getInstance());
    }

    private function isReceiptExist()
    {
        return !empty( $this->_receipt );
    }

    private function isCancelAvailable()
    {
        return $this->_receipt['may_be_cancel'];
    }

    private function isCanceled()
    {
        return $this->_receipt['canceled'];
    }

    private function cancelPayment()
    {
        $this->_provider->cancelPayment(Request::getInstance());
    }

    private function initCallback()
    {
        $this->_provider->initCallback(Request::getInstance());
    }
} 