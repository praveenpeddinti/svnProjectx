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
use yii\db\Query;
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
        ];
    }  

     /**
     * @author Lakshmi
     * @params $userId
     * @return $data
     * @Description Returns all the notification types
     */
    public static function getAllNotificationTypes($userId){
        $query= new Query();
        $data = $query->select("")
                      ->from("CollaboratorNotificationsSettings cns")
                      ->join("join", "Notifications ns", "ns.Id=cns.ActivityId")
                      ->where("cns.CollaboratorId=".$userId)
                      ->orderBy("ActivityTitle")
                      ->all();
        return $data;
    }
      /**
     * @author Lakshmi
     * @params $userId
     * @return $data
     * @Description gets the data of the Notification status of a user
     */
    public static function getAllNotificationsStatus($userId){
//        $query="select * from CollaboratorNotificationsSettings where CollaboratorId=$userId";                
//        $data = Yii::$app->db->createCommand($query)->queryAll();
        $query= new Query();
        $data = $query->select("")
                      ->from("CollaboratorNotificationsSettings")
                      ->where("CollaboratorId=".$userId)
                      ->all();

        return $data; 
    }
     
    /**
     * @author Lakshmi
     * @params $userId,$status,$type,$activityId
     * @return $data
     * @Description Updates a particular notification for given User.
     */
        public static function notificationsSetttingsStatusUpdate($userId,$type,$activityId,$isChecked){
            
            if($isChecked==1){
              $query="update CollaboratorNotificationsSettings set $type=1 where CollaboratorId=$userId and ActivityId=$activityId"; 
            }else{
             $query="update CollaboratorNotificationsSettings set $type=0 where CollaboratorId=$userId and ActivityId=$activityId";    
            }
        $data = Yii::$app->db->createCommand($query)->execute();
        return $data; 
    }
    /**
     * 
     * @param type $fieldName
     * @param type $userId
     * @return type
     * @Description Gets Notification setttings data of a User.
     */
        public static function getNotificationSettingsStatus($fieldName,$userId){
//           $query="select * from Notifications ns join CollaboratorNotificationsSettings cns 
//                    on ns.ActivityOn='$fieldName' and cns.CollaboratorId=$userId and ns.Id=cns.ActivityId"; 
//             $data = Yii::$app->db->createCommand($query)->queryAll();
            $query= new Query();
            $data = $query->select("")
                          ->from("Notifications ns")
                          ->join("join", "CollaboratorNotificationsSettings cns", "ns.ActivityOn='".$fieldName."' and cns.CollaboratorId=".$userId." and ns.Id=cns.ActivityId")
                          ->all();
             return $data; 
    }
        /**
         * 
         * @param type $userId
         * @param type $type
         * @param type $isChecked
         * @return type
         * @Description Upadres all the notification status for a given user
         */
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
   /**
    * @author Moin Hussain
    * @param type $userId
    * @return type
    * @Description Saves the notification settings for a user.
    */
        public static function saveNotificationsSettingsForUser($userId){
          try{
//             $query="select Id from Notifications where status=1"; 
//             $data = Yii::$app->db->createCommand($query)->queryAll();
            $query= new Query();
            $data = $query->select("Id")
                          ->from("Notifications")
                          ->where("status=1")
                          ->all();
             $insertQuery = "INSERT INTO CollaboratorNotificationsSettings(CollaboratorId,ActivityId,SystemNotification,EmailNotification,PushNotification) VALUES";
             $dataArray = array();
               foreach ($data as $value) {
                  $activityId = $value['Id'];
                  array_push($dataArray,"($userId,$activityId,1,1,0)");
                
             }
            $queryString =  implode(",",$dataArray);
            $insertQuery = $insertQuery.$queryString;
             error_log($insertQuery);
            Yii::$app->db->createCommand($insertQuery)->execute(); 
          } catch (Exception $ex) {
      Yii::error("Settings:saveNotificationsSettingsForUser::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
          }

     }  
    
     
}


            
            

?>