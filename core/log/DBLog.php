<?php
/**
 * Created by PhpStorm.
 * User: ataev
 * Date: 30.01.15
 * Time: 16:03
 */

namespace core\log;

use core\Core;
use core\traits\Singleton;
use \PDO;
use \PDOException;
use \PDOStatement;

class DBLog {
    use Singleton;

    /**
     * @var $db PDO
     */
    private $db;
    /**
     * @var $statement PDOStatement
     */
    private $statement;

    private $dsn;
    private $username;
    private $password;
    private $properties; // ������������� ���� ��� ����������

    public $connectionError = null;

    private function __construct() {
        /** @var $core Core */
        $core             = Core::getInstance();
        $config           = $core->getConfig();
        $this->dsn        = $config['db']['dsn'];
        $this->username   = $config['db']['username'];
        $this->password   = $config['db']['password'];
        $this->properties = [];
    }

    public function createConnection()
    {
        try {
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ];
            $this->db = new PDO($this->dsn, $this->username, $this->password, $options);
        } catch (PDOException $exception) {
            $this->connectionError = $exception;
        }
    }

    public function terminateConnection()
    {
        $this->db = null;
        $this->statement = null;
    }

    public function save(array $properties)
    {
        if ( count($this->properties) > 0 )
            $this->properties = array_merge($this->properties, $properties);
        else
            $this->properties = $properties;
    }

    public function saveToDB() {
        try {
            $this->initStatementForInsert();
            $this->statement->execute();
        } catch (PDOException $exception) {
            $this->connectionError = $exception;
        }
        $this->terminateConnection();
    }

    public function getFromDB() {
        try {
            $this->initStatementForSelect();
            $this->statement->execute();
            $result = $this->statement->fetchAll();
        } catch (PDOException $exception) {
            $this->connectionError = $exception;
        }
        $this->terminateConnection();
        return $result;
    }

    private function initStatementForInsert() {
        $keys   = array_keys($this->properties); // �������� ����� �������� ��� ����������� � ����� ������� ����� ������
        $values = array_values($this->properties); // �������� �������� ������� ������ ����� ��������� � ��

        $insertColumns     = implode(', ', $keys); // ��������� ������ ������� ������� ��
        $insertValues  = ':' . implode(', :', $keys); // ��������� ������ ������� ����� ����� ����� ������������ ��� �������� ��������

        $bindParamsArray = explode(', ',$insertValues); // ��������� ����� ��������

        $query = "INSERT INTO log ($insertColumns) VALUES ($insertValues)"; // ��������� ������ �������
        try {
            $this->createConnection();
            $this->statement = $this->db->prepare($query);
            foreach ($bindParamsArray as $key => $value) {
                $this->statement->bindValue($value, $values[$key]); // ����������� ��������
            }
        } catch (PDOException $exception) {
            $this->connectionError = $exception;
        }
    }

    private function initStatementForSelect()
    {
        $query = 'SELECT * FROM log';
        try {
            $this->createConnection();
            $this->statement = $this->db->prepare($query);
        } catch (PDOException $exception) {
            $this->connectionError = $exception;
        }
    }

}