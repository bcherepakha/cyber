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

    const ERROR_SIGNATURE_VERIFICATION = -4; //Ошибка формируется сервером Поставщика в ответ на полученное сообщение с недействительной подписью.
    const INTERNAL_ERROR_PROVIDER      = -3; //Используется при неверном значении данных поля type (некоторые реализации дилерского ПО могут отправлять запросы с неверным указанием типа платежа)
    const INCORRECT_VALUE_PAYMENT_TYPE = -2; //Используется при неверном значении данных поля type (некоторые реализации дилерского ПО могут отправлять запросы с неверным указанием типа платежа)
    const INCORRECT_VALUE_ADDITIONAL   = -1; //Используется при неверном значении данных поля additional. Проверка значений данного поля возлагается на Поставщика и его наличие согласовывается с Поставщиком.
    const NO_ERRORS                    =  0; //Операция прошла успешно (абонент найден, или платеж зачислен, или платеж отменен, или успешный платеж найден)
    const UNKNOWN_ACTION               =  1; //Неизвестное значение поля action
    const USER_NOT_FOUND               =  2; //Переданный уникальный идентификатор абонента не зарегистрирован в системе Поставщика. Только для action = check и payment.
    const INCORRECT_PAYMENT            =  3; //Недопустимое значение суммы платежа. Только для action = payment, check.
    const INCORRECT_RECEIPT            =  4; //Недопустимое значение номера платежа. Только для action = cancel, payment, status.
    const INCORRECT_DATE               =  5; //Недопустимое значение даты операции. Только для action = payment.
    const NOT_FOUND_SUCCESS_RECEIPT    =  6; //Отрицательный ответ на проверку статуса (платежа не было или платеж не прошел). Только для action = status.
    const RECEIPT_CANCEL               =  7; //Отрицательный ответ на проверку статуса (платеж был, но отменен). Только для action = status, payment.
    const UNKNOWN_RECEIPT_STATUS       =  8; //Состояние платежа неизвестно (необходимо повторить попытку). Только для action = status.
    const PAYMENT_CAN_NOT_BE_CANCELED  =  9; //Отрицательный ответ на отмену платежа. Только для action = cancel.
    const WRONG_SOURCE                 = 10; //Не верный источник запроса.
    const WRONG_MES                    = 11; //Не верный тип сообщения при отмене.
    private $messages = array(
        '-4' => 'Ошибка проверки АСП под принятым сообщением',
        '-3' => 'Внутренняя ошибка Поставщика',
        '-2' => 'Неверное значение типа платежа (type)',
        '-1' => 'Неверный формат дополнительного параметра (additional)',
        '0'  => 'Нет ошибки (успех)',
        '1'  => 'Неизвестный тип запроса',
        '2'  => 'Абонент не найден',
        '3'  => 'Неверная сумма платежа',
        '4'  => 'Неверное значение номера платежа',
        '5'  => 'Неверное значение даты',
        '6'  => 'Успешный платеж с таким номером не найден',
        '7'  => 'Платеж с таким номером отменен',
        '8'  => 'Состояние платежа неопределенно',
        '9'  => 'Платеж не может быть отменен',
        '10' => 'Не верный источник запроса',
        '11' => 'Не верный тип события'
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
            //завершаем приложение
            Response::getInstance()->giveXML( $params );
        }
    }
}