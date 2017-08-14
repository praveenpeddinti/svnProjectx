<?php 
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace common\models\mysql;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\base\ErrorException;

class Settings extends ActiveRecord 
{
    
    public static function tableName()
    {
        return '{{%Notifications}}';
    }
    
    public function behaviors()
    {
        return [
            //TimestampBehavior::className(),
        ];
    }  

     /**
     * @author Lakshmi
     * @params $userId
     * @return $data
     */
    public static function getAllNotificationTypes($userId){
        $query="select * from CollaboratorNotificationsSettings cns join
                Notifications ns on ns.Id=cns.ActivityId where cns.CollaboratorId=$userId";
        $data = Yii::$app->db->createCommand($query)->queryAll();
        error_log("+++++++++++++++++++++".sizeof($data));
        return $data;
    }
      /**
     * @author Lakshmi
     * @params $userId
     * @return $data
     */
    public static function getAllNotificationsStatus($userId){
//        $query="select * from CollaboratorNotificationsSettings cns join
//                Notifications ns on ns.Id=cns.ActivityId where CollaboratorId=$userId";
$query="select * from CollaboratorNotificationsSettings where CollaboratorId=$userId";                
        $data = Yii::$app->db->createCommand($query)->queryAll();
               error_log("++++++++++++++++++++++".sizeof($data)."======KKKKKKKKKKkkk=======".print_r($data,1));     

        return $data; 
    }
     
    /**
     * @author Lakshmi
     * @params $userId,$status,$type,$activityId
     * @return $data
     */
        public static function notificationsSetttingsStatusUpdate($userId,$status,$type,$activityId,$isChecked){
            
            if($isChecked==1){
              $query="update CollaboratorNotificationsSettings set $type=1 where CollaboratorId=$userId and ActivityId=$activityId"; 
            }else{
             $query="update CollaboratorNotificationsSettings set $type=0 where CollaboratorId=$userId and ActivityId=$activityId";    
            }
        $data = Yii::$app->db->createCommand($query)->execute();
        return $data; 
    }
    
            public static function getNotificationSettingsStatus($fieldName,$userId){
           $query="select * from Notifications ns join CollaboratorNotificationsSettings cns 
                    on ns.ActivityOn='$fieldName' and cns.CollaboratorId=$userId and ns.Id=cns.ActivityId"; 
           error_log("%%%%%%%%%%%%%%%%".$query);

             $data = Yii::$app->db->createCommand($query)->queryAll();
         //    $status="(".$data[0]['SystemNotification'].','.$data[0]['EmailNotification'].','.$data[0]['PushNotification'].")";
             return $data; 
    }
         public static function notificationsSetttingsStatusUpdateAll($userId,$type,$isChecked){
            
            if($isChecked==1){
              $query="update CollaboratorNotificationsSettings set $type=1 where CollaboratorId=$userId and $type=0"; 
            }else{
             $query="update CollaboratorNotificationsSettings set $type=0 where CollaboratorId=$userId and $type=1";    
            }

        $data = Yii::$app->db->createCommand($query)->execute();
                   error_log("======+++++++=======111111======".$data);

        return $data; 
    }
}


            
            

?>