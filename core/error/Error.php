<?php
/**
 * Created by PhpStorm.
 * User: ataev
 * Date: 03.02.15
 * Time: 13:04
 */

namespace core\error;

use core\traits\Singleton;
use core\response\Response;

class Error {
    use Singleton;

    const ERROR_SIGNATURE_VERIFICATION = -4; //������ ����������� �������� ���������� � ����� �� ���������� ��������� � ���������������� ��������.
    const INTERNAL_ERROR_PROVIDER      = -3; //������������ ��� �������� �������� ������ ���� type (��������� ���������� ���������� �� ����� ���������� ������� � �������� ��������� ���� �������)
    const INCORRECT_VALUE_PAYMENT_TYPE = -2; //������������ ��� �������� �������� ������ ���� type (��������� ���������� ���������� �� ����� ���������� ������� � �������� ��������� ���� �������)
    const INCORRECT_VALUE_ADDITIONAL   = -1; //������������ ��� �������� �������� ������ ���� additional. �������� �������� ������� ���� ����������� �� ���������� � ��� ������� ��������������� � �����������.
    const NO_ERRORS                    =  0; //�������� ������ ������� (������� ������, ��� ������ ��������, ��� ������ �������, ��� �������� ������ ������)
    const UNKNOWN_ACTION               =  1; //����������� �������� ���� action
    const USER_NOT_FOUND               =  2; //���������� ���������� ������������� �������� �� ��������������� � ������� ����������. ������ ��� action = check � payment.
    const INCORRECT_PAYMENT            =  3; //������������ �������� ����� �������. ������ ��� action = payment, check.
    const INCORRECT_RECEIPT            =  4; //������������ �������� ������ �������. ������ ��� action = cancel, payment, status.
    const INCORRECT_DATE               =  5; //������������ �������� ���� ��������. ������ ��� action = payment.
    const NOT_FOUND_SUCCESS_RECEIPT    =  6; //������������� ����� �� �������� ������� (������� �� ���� ��� ������ �� ������). ������ ��� action = status.
    const RECEIPT_CANCEL               =  7; //������������� ����� �� �������� ������� (������ ���, �� �������). ������ ��� action = status, payment.
    const UNKNOWN_RECEIPT_STATUS       =  8; //��������� ������� ���������� (���������� ��������� �������). ������ ��� action = status.
    const PAYMENT_CAN_NOT_BE_CANCELED  =  9; //������������� ����� �� ������ �������. ������ ��� action = cancel.
    const WRONG_SOURCE                 = 10; //�� ������ �������� �������.
    const WRONG_MES                    = 11; //�� ������ ��� ��������� ��� ������.
    private $messages = array(
        '-4' => '������ �������� ��� ��� �������� ����������',
        '-3' => '���������� ������ ����������',
        '-2' => '�������� �������� ���� ������� (type)',
        '-1' => '�������� ������ ��������������� ��������� (additional)',
        '0'  => '��� ������ (�����)',
        '1'  => '����������� ��� �������',
        '2'  => '������� �� ������',
        '3'  => '�������� ����� �������',
        '4'  => '�������� �������� ������ �������',
        '5'  => '�������� �������� ����',
        '6'  => '�������� ������ � ����� ������� �� ������',
        '7'  => '������ � ����� ������� �������',
        '8'  => '��������� ������� �������������',
        '9'  => '������ �� ����� ���� �������',
        '10' => '�� ������ �������� �������',
        '11' => '�� ������ ��� �������'
    );

    public $currentErrorCode = self::NO_ERRORS;

    public function getErrorMessage( $name ) {
        if ( is_numeric($name) ) {
            $error_code = $name;
        } else {
            $error_code = constant('self::' . $name);
        }

        $messages = self::getInstance()->messages ;
        if ( !isset($messages[$error_code]) ) return false;

        return $messages[$error_code];
    }

    public function catchError( $name, $params = array() ) {
        if ( is_numeric($name) ) {
            $error_code = $name;
        } else {
            $error_code = constant('self::' . $name);
        }

        $this->currentErrorCode = $error_code;
        if (self::NO_ERRORS<>$error_code) {
            //��������� ����������
            Response::getInstance()->giveXML( $params );
        }
    }
}