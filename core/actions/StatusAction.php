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

class StatusAction {
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

        if ( !$this->isStatusExist() )
            $error->catchError(Error::UNKNOWN_RECEIPT_STATUS);

        if ( $this->isCanceled() )
            $error->catchError(Error::RECEIPT_CANCEL, ['authcode'=>$this->_receipt['authcode']]);

        if ( !$this->isReceiptPaid() )
            $error->catchError(Error::NOT_FOUND_SUCCESS_RECEIPT);

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

    private function isStatusExist()
    {
        return ($this->isCanceled() or $this->isReceiptPaid());
    }

    private function isCanceled()
    {
        return $this->_receipt['canceled'];
    }

    private function isReceiptPaid()
    {
        return $this->_receipt['paid'];
    }
} 