<?php
/**
 * Created by PhpStorm.
 * User: ataev
 * Date: 03.02.15
 * Time: 12:45
 */

namespace provider;


use core\Core;
use core\request\Request;
use PDO;
use PDOException;
use PDOStatement;

class Provider {

    private $dsn;
    private $username;
    private $password;
    public  $connectionError;
    /**
     * @var $db PDO
     */
    private $db;
    /**
     * @var $statement PDOStatement
     */
    private $statement;

    public function __construct()
    {
        $dbConfig         = Core::getInstance()->getConfig()['db'];
        $this->dsn        = $dbConfig['dsn'];
        $this->username   = $dbConfig['username'];
        $this->password   = $dbConfig['password'];
    }

    private function createConnection()
    {
        try {
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ];
            $this->db = new PDO($this->dsn, $this->username, $this->password, $options);
        } catch (PDOException $exception) {
            $this->connectionError = $exception->getMessage();
        }
    }

    private function terminateConnection()
    {
        $this->db = null;
        $this->statement = null;
    }

    private function initStatementForSelectUserInformation($number)
    {
        $query = 'SELECT cs.client_number, cs.service_type_id, st.name FROM client_service cs INNER JOIN service_type st ON cs.service_type_id = st.id WHERE cs.client_number = :client_number';
        try {
            $this->createConnection();
            $this->statement = $this->db->prepare($query);
            $this->statement->bindValue(':client_number', $number);
        } catch (PDOException $exception) {
            $this->connectionError = $exception->getMessage();
        }
    }

    private function initStatementForSelectReceiptInformation($receipt)
    {
        $query = 'SELECT * FROM receipt r WHERE r.receipt = :receipt';
        try {
            $this->createConnection();
            $this->statement = $this->db->prepare($query);
            $this->statement->bindValue(':receipt', $receipt);
        } catch (PDOException $exception) {
            $this->connectionError = $exception->getMessage();
        }
    }

    private function initStatementForCancelReceipt($receipt)
    {
        $query = 'UPDATE receipt r SET r.canceled = 1, r.cancel_date = NOW() WHERE r.receipt = :receipt';
        try {
            $this->createConnection();
            $this->statement = $this->db->prepare($query);
            $this->statement->bindValue(':receipt', $receipt);
        } catch (PDOException $exception) {
            $this->connectionError = $exception->getMessage();
        }
    }

    private function initStatementForSaveReceipt($request)
    {
        /** @var $request Request */
        $query = 'INSERT INTO receipt (receipt, client_number, amount, pay_date, service_type_id) VALUES (:receipt, :client_number, :amount, :pay_date, :service_type)';
        try {
            $this->createConnection();
            $this->statement = $this->db->prepare($query);
            $this->statement->bindValue(':receipt', $request->_receipt);
            $this->statement->bindValue(':client_number', $request->_number);
            $this->statement->bindValue(':amount', $request->_amount);
            $this->statement->bindValue(':pay_date', $request->_date);
            $this->statement->bindValue(':service_type', $request->_type);
        } catch (PDOException $exception) {
            $this->connectionError = $exception->getMessage();
        }
    }

    public function getUserInformation($request)
    {
        try {
            $this->initStatementForSelectUserInformation($request->_number);
            $this->statement->execute();
            $result = $this->statement->fetchAll();
        } catch (PDOException $exception) {
            $this->connectionError = $exception;
            $result = null;
        }
        $this->terminateConnection();
        $userInfo = [];
        foreach ($result as $key => $value) {
            $userInfo['availablePaymentTypes'][] = $value['service_type_id'];
        }
        return $userInfo;
    }

    public function getReceiptInformation($request)
    {
        try {
            $this->initStatementForSelectReceiptInformation($request->_receipt);
            $this->statement->execute();
            $result = $this->statement->fetch();
        } catch (PDOException $exception) {
            $this->connectionError = $exception;
            $result = null;
        }
        $this->terminateConnection();
        return $result;
    }

    public function cancelPayment($request)
    {
        try {
            $this->initStatementForCancelReceipt($request->_receipt);
            $this->statement->execute();
        } catch (PDOException $exception) {
            $this->connectionError = $exception;
        }
        $this->terminateConnection();
    }

    public function savePayment($request)
    {
        try {
            $this->initStatementForSaveReceipt($request);
            $this->statement->execute();
        } catch (PDOException $exception) {
            $this->connectionError = $exception;
        }
        $this->terminateConnection();
    }

    public function isCorrectAmount($request)
    {
        return true;
    }

    public function initCallback($request)
    {

    }
} 