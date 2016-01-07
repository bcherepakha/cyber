<?php
/**
 * Created by PhpStorm.
 * User: ataev
 * Date: 02.02.15
 * Time: 14:36
 */

namespace core\response;

use core\Core;
use core\log\DBLog;
use core\traits\Singleton;
use core\error\Error;
use core\signature\Signature;

class Response {
    use Singleton;

    private $_encoding = 'windows-1251';
    private $_xmlSign;
    private $_unSignedXml;
    private $_signedXml;

    public function generate( $fields=array() ) {

        $unSignedXML = '<?xml version="1.0" encoding="' . $this->_encoding . '"?><response>';

        // Устанавливаем переменные
        foreach($fields as $field=>$val) {
            $unSignedXML .= "<$field>$val</$field>";
        }

        // Генерация XML по шаблону
        $unSignedXML .='</response>';

        $this->_unSignedXml = $unSignedXML;
    }

    public function giveXML( $fields=array() ) {
        /** @var $error Error */
        $error = Error::getInstance();
        $fields['code']     = $error->currentErrorCode;
        $fields['message']  = $error->getErrorMessage( $fields['code'] );

        $this->generate( $fields );
        $this->_xmlSign = Signature::getInstance()->sign($this->_unSignedXml);
        $this->_signedXml = str_replace("</response>", '<sign>' . urlencode($this->_xmlSign) . '</sign></response>', $this->_unSignedXml);

        $this->savePropertiesToLog();
        DBLog::getInstance()->saveToDB();

        header('Content-Type: application/xml; charset=' . $this->_encoding);
        echo $this->_signedXml;
//        $file = __DIR__ . '/log/' . time() . '.xml';
//        file_put_contents( $file, $this->_signedXml );
        die();
    }

    private function getPropertiesForLog()
    {
        return [
            'response_time_date' => date('Y-m-d H:i:s'),
            'response_time_float' => microtime(true),
            'response_signature' => $this->_xmlSign,
            'xml' => $this->_signedXml,
            'error_code' => Error::getInstance()->currentErrorCode
        ];
    }

    private function savePropertiesToLog()
    {
        $logConfig = Core::getInstance()->getConfig()['log'];
        if ( $logConfig['saveToDB'] and $logConfig['saveResponse'] )
            DBLog::getInstance()->save($this->getPropertiesForLog());
    }
}