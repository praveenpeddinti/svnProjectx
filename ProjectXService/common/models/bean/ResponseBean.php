<?php
namespace common\models\bean;
/* @description This bean is used to prepare response to be send to client.
 * @author Moin Hussain
 */
class ResponseBean {
    
    const SUCCESS = 200;
    const FAILURE = 401;
    const NOTFOUND = 404;
    const SUCCESS_MESSAGE = "success";
    const FAILURE_MESSAGE = "failure";
    const NOTFOUND_MESSAGE = "Page Not Found";
    public $statusCode = "";
    public $message = "";
    public $data = "";
    public $totalCount=0;
    public $timehours=0;
   
    
     static function getConstant($constant)
    {
        return constant('self::'. $constant);
    }
    
}
?>
