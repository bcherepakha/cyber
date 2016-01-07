<?php
/**
 * Created by PhpStorm.
 * User: ataev
 * Date: 02.02.15
 * Time: 12:53
 */

namespace core\actions;

use core\error\Error;

class ActionFactory {
    public function getAction($action)
    {
        switch ($action) :
            case 'check':
                return new CheckAction;
                break;
            case 'payment':
                return new PaymentAction;
                break;
            case 'cancel':
                return new CancelAction;
                break;
            case 'status':
                return new StatusAction;
                break;
            default:
                Error::getInstance()->catchError(Error::UNKNOWN_ACTION);
                break;
        endswitch;
    }
}