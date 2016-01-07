<?php
/**
 * Created by PhpStorm.
 * User: ataev
 * Date: 02.02.15
 * Time: 12:18
 */
/**
 * @property string $_url - Полученный URL из запроса.
 * @property string $_remoteAddr
 * @property string $_serverProtocol
 * @property string $_requestMethod - Метод запроса (GET, POST).
 * @property float $_requestTimeFloat
 * @property integer $_requestTime
 * @property string $_referer
 * @property string $_origin
 * @property integer $_urlLength
 *
 * @property string $_signReceived  - Полученная подпись из запроса
 * @property string $_signGenerated - Сгенирированная подпись на сервере.
 *
 * @property string $_action  - Параметр определяющий действие над данными.
 * @property string $_number  - ID клиента.
 * @property string $_type    - ID типа платежа (газ, вода, интернет).
 * @property string $_amount  - Сумма платежа.
 * @property string $_receipt - Уникальный ID платежа.
 * @property string $_date    - Дата и время платежа.
 * @property string $_mes     - ID причины для отмены платежа.
 */
namespace core\request;

use core\Core;
use core\error\Error;
use core\log\DBLog;
use core\signature\Signature;
use core\traits\Singleton;
use DateTime;

class Request {
    use Singleton;

    private $_url;
    private $_sign;

    private $_remoteAddr;
    private $_serverProtocol;
    private $_requestMethod;
    private $_requestTimeFloat;
    private $_requestTime;
    private $_referer = null;
    private $_origin = null;
    private $_urlLength;

    private $_action;
    private $_number;
    private $_type;
    private $_amount;
    private $_receipt;
    private $_date;
    private $_mes;

    private function __construct()
    {
        $this->initParams();
        $this->savePropertiesToLog();
    }

    private function initParams()
    {
        $this->setURLAndSign();
        $this->_urlLength        = strlen($this->_url);
        $this->_remoteAddr       = $_SERVER['REMOTE_ADDR'];
        $this->_serverProtocol   = $_SERVER['SERVER_PROTOCOL'];
        $this->_requestMethod    = $_SERVER['REQUEST_METHOD'];
        $this->_requestTimeFloat = $_SERVER['REQUEST_TIME_FLOAT'];
        $this->_requestTime      = $_SERVER['REQUEST_TIME'];
        $this->_action           = isset($_REQUEST['action'])  ? $_REQUEST['action']  : null;
        $this->_number           = isset($_REQUEST['number'])  ? $_REQUEST['number']  : null;
        $this->_type             = isset($_REQUEST['type'])    ? $_REQUEST['type']    : null;
        $this->_amount           = isset($_REQUEST['amount'])  ? $_REQUEST['amount']  : null;
        $this->_receipt          = isset($_REQUEST['receipt']) ? $_REQUEST['receipt'] : null;
        $this->_date             = isset($_REQUEST['date'])    ? $_REQUEST['date']    : null;
        $this->_mes              = isset($_REQUEST['mes'])     ? $_REQUEST['mes']     : null;
    }

    public function __get($name)
    {
        return $this->$name;
    }

    private function getPropertiesForLog()
    {
        return [
            'url' => $this->_url,
            'url_length' => $this->_urlLength,
            'action' => $this->_action,
            'number' => $this->_number,
            'type' => $this->_type,
            'amount' => $this->_amount,
            'receipt' => $this->_receipt,
            'date' => $this->_date,
            'mes' => $this->_mes,
            'remote_addr' => $this->_remoteAddr,
            'server_protocol' => $this->_serverProtocol,
            'request_method' => $this->_requestMethod,
            'request_time_date' => date('Y-m-d H:i:s'),
            'request_time_float' => $this->_requestTimeFloat,
            'request_signature' => $this->_sign,
        ];
    }

    private function savePropertiesToLog()
    {
        $logConfig = Core::getInstance()->getConfig()['log'];
        if ( $logConfig['saveToDB'] and $logConfig['saveRequest'] )
            DBLog::getInstance()->save($this->getPropertiesForLog());
    }

    private function setURLAndSign()
    {
        $url  = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $data = explode('&sign=', $url);
        $this->_url  = isset($data[0]) ? $data[0] : null;
        $this->_sign = isset($data[1]) ? $data[1] : null;
    }

    private function isCorrectAction()
    {
        if ( isset($_REQUEST['action']) and ($_REQUEST['action'] == 'check' or $_REQUEST['action'] == 'payment' or $_REQUEST['action'] == 'cancel' or $_REQUEST['action'] == 'status') )
            return true;
        else
            return false;
    }

    private function isCorrectNumber()
    {
        if ( isset($_REQUEST['number']) and !empty($_REQUEST['number']) and strlen($_REQUEST['number']) <= 30 )
            return true;
        else
            return false;
    }

    private function isCorrectType()
    {
        if ( isset($_REQUEST['type']) and !empty($_REQUEST['type']) and $this->isInt($_REQUEST['type']) )
            return true;
        else
            return false;
    }

    private function isCorrectAmount()
    {
        if ( isset($_REQUEST['amount']) and !empty($_REQUEST['amount']) and strlen($_REQUEST['amount']) <= 10 and $this->isFloat($_REQUEST['amount']) )
            return true;
        else
            return false;
    }

    private function isCorrectReceipt()
    {
        if ( isset($_REQUEST['receipt']) and !empty($_REQUEST['receipt']) and strlen($_REQUEST['receipt']) <= 15 and $this->isInt($_REQUEST['receipt']) )
            return true;
        else
            return false;
    }

    private function isCorrectDate()
    {
        if ( isset($_REQUEST['date']) and !empty($_REQUEST['date']) and $this->isCorrectDateFormat($_REQUEST['date']) )
            return true;
        else
            return false;
    }

    private function isCorrectMes()
    {
        if ( isset($_REQUEST['mes']) and !empty($_REQUEST['mes']) and strlen($_REQUEST['mes']) <= 3 and $this->isInt($_REQUEST['mes']) )
            return true;
        else
            return false;
    }

    private function isInt($value)
    {
        return (boolean)preg_match('/^[0-9]+$/',trim($value));
    }

    private function isFloat($value)
    {
        return (boolean)preg_match('/^([0-9]*\.)?[0-9]{1,2}$/',trim($value));
    }

    private function isCorrectDateFormat($value)
    {
        $interval = Core::getInstance()->getConfig()['interval'];
        $serverOlderDate = new DateTime( $interval['not older'] );
        $serverUnderDate = new DateTime( $interval['not under'] );
        $requestDate = DateTime::createFromFormat('Y-m-d\TH:i:s', $value);
        if ( (boolean)$requestDate )
            if ($requestDate <= $serverOlderDate)
                if ( $requestDate > $serverUnderDate )
                    return true;

        return false;
    }

    public function checkRequest()
    {
        $config = Core::getInstance()->getConfig();

        if ( $this->_remoteAddr != $config['ps']['address'] ) {
            Error::getInstance()->сatchError('WRONG_SOURCE');
        } else {
            $this->checkSign();
            $this->checkRequestByAction();
        }
    }

    public function checkRequestByAction()
    {
        /**
        * @var $error Error
        */
        $error = Error::getInstance();

        if ( $this->isCorrectAction() ) {
            switch ($this->_action) :
                case 'check':
                    if ( !$this->isCorrectNumber() )
                        $error->catchError(Error::USER_NOT_FOUND);
                    elseif ( !$this->isCorrectType() )
                        $error->catchError(Error::INCORRECT_VALUE_PAYMENT_TYPE);
                    elseif ( !$this->isCorrectAmount() )
                        $error->catchError(Error::INCORRECT_PAYMENT);
                    elseif ( $this->_receipt or $this->_date or $this->_mes)
                        $error->catchError(Error::UNKNOWN_ACTION);
                    break;
                case 'payment':
                    if ( !$this->isCorrectNumber() )
                        $error->catchError(Error::USER_NOT_FOUND);
                    elseif ( !$this->isCorrectType() )
                        $error->catchError(Error::INCORRECT_VALUE_PAYMENT_TYPE);
                    elseif ( !$this->isCorrectAmount() )
                        $error->catchError(Error::INCORRECT_PAYMENT);
                    elseif ( !$this->isCorrectReceipt() )
                        $error->catchError(Error::INCORRECT_RECEIPT);
                    elseif ( !$this->isCorrectDate() )
                        $error->catchError(Error::INCORRECT_DATE);
                    elseif ( $this->_mes)
                        $error->catchError(Error::UNKNOWN_ACTION);
                    break;
                case 'cancel':
                    if ( !$this->isCorrectReceipt() )
                        $error->catchError(Error::INCORRECT_RECEIPT);
                    elseif ( !$this->isCorrectMes() )
                        $error->catchError(Error::WRONG_MES);
                    elseif ( $this->_date or $this->_number or $this->_amount or $this->_type )
                        $error->catchError(Error::UNKNOWN_ACTION);
                    break;
                case 'status':
                    if ( !$this->isCorrectReceipt() )
                        $error->catchError(Error::INCORRECT_RECEIPT);
                    elseif ( $this->_date or $this->_number or $this->_amount or $this->_type or $this->_mes )
                        $error->catchError(Error::UNKNOWN_ACTION);
                    break;
                default:
                    break;
            endswitch;
        } else {
            $error->catchError(Error::UNKNOWN_ACTION);
        }
    }

    public function checkSign()
    {
        /**
         * @var $signature Signature
         * @var $error Error
         */
        $signature = Signature::getInstance();
        if ( !$signature->verifySign($this->_url, urldecode($this->_sign)) ) {
            $error = Error::getInstance();
            $error->catchError(Error::ERROR_SIGNATURE_VERIFICATION);
        }
    }
}