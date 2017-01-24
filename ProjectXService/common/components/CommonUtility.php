<?php
namespace  common\components;
/* 
 *
 * @author Moin Hussain
 */
class CommonUtility {
   /**
    * @author Moin Hussain
    * @param type $object
    * @param type $type
    * @return type
    */
    public static function prepareResponse($object,$type = "json"){
        if($type == "json"){
           \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        }else{
            \Yii::$app->response->format = \yii\web\Response::FORMAT_XML;
        }
        return $object;
    }
   
}

?>
