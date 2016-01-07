<?php
/**
 * Created by PhpStorm.
 * User: cherepakha
 * Date: 02.02.15
 * Time: 12:32
 */

namespace core\signature;

use core\Core;
use core\traits\Singleton;

class Signature {
    use Singleton;

    private $_private;
    private $_public;

    private $privatePemFile;
    private $publicPemFile;

    private function __construct() {
        $this->init();
    }

    private function init() {
        $config = Core::getInstance()->getConfig()['keys'];
        $this->publicPemFile = $config['system']['public'];
        $this->privatePemFile = $config['ours']['private'];
        $this->getKeys();
        return true;
    }

    public function createKeys($private_key_bits = 1024) {
        $new_key_pair = openssl_pkey_new(array(
            "private_key_bits" => $private_key_bits,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ));
        openssl_pkey_export($new_key_pair, $private_key_pem);

        $details = openssl_pkey_get_details($new_key_pair);

        $public_key_pem = $details['key'];

        $config = Core::getInstance()->getConfig()['keys'];
        $publicPemFile = $config['ours']['public'];

        return ( file_put_contents($this->privatePemFile, $private_key_pem) && file_put_contents($publicPemFile, $public_key_pem) );
    }

    public function getKeys() {
        $this->_private = file_get_contents($this->privatePemFile);
        $this->_public = file_get_contents($this->publicPemFile);

        return true;
    }

    public function sign( $data ) {
        if ( null == $this->_private ) $this->init();
        $signature = '';
        openssl_sign($data, $signature, $this->_private, OPENSSL_ALGO_SHA1);
        return $signature;
    }

    public function verifySign( $data, $sign ) {
        return openssl_verify($data, $sign, $this->_public, OPENSSL_ALGO_SHA1);
    }
}