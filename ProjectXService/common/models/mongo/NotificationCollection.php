<?php
namespace common\models\mongo;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/*
 * refer : 
 * https://github.com/yiisoft/yii2-mongodb/blob/master/docs/guide/usage-ar.md
 */
use Yii;
//use yii\db\ActiveRecord;
use yii\mongodb\ActiveRecord;
use yii\mongodb\Query;
use common\models\mysql\Collaborators;

class NotificationCollection extends ActiveRecord 
{
    public static function collectionName()
    {
        return 'NotificationCollection';
    }
    
    public function attributes() {
//        parent::attributes();
        return [
      "_id",      
     "TicketId",
     "ProjectId",
     "CollaboratorUsers",
     "Notification_Type",
     "ActivityFrom",
     "Status",
     "ActivityOn",
     "NotificationDate",
     "OldValue",
     "NewValue",
     "CommentSlug",
     "TargetTicketId"
    
   
        ];
    }
    
//    public function behaviors()
//    {
//        return [
//            TimestampBehavior::className(),
//        ];
//    }

    /**
     * @author Ryan Marshal  
     * @description This method is used for getting all the notifications for user.
     * @param type $user
     * @param type $projectId
     * @return array
     */ 
    
    public static function getNotificationDetails($notificationIds){
        try{
           $query=new Query();
            $query->from('NotificationCollection')
            ->where(["_id" => array('$in'=>$notificationIds)]);
            $notifications = $query->all();
            return $notifications;  
        } catch (Exception $ex) {
            Yii::log("NotificationCollection:getNotificationDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');

        }
    }
     public static function getNotificationsCount($user,$projectId)
    { 
         try{
         error_log("getNotificationsCount----UserId---".$user."*******projectId******".$projectId);
          $query=new Query();
            $query->from('NotificationCollection')
            ->where(["CollaboratorUsers" =>["CollaboratorId" =>(int) $user,"IsRead" =>(int)0],'ProjectId'=>(int)$projectId])
            ->andWhere(['!=','ActivityFrom', (int)$user]);
            $notificationsCount=$query->count();
            error_log("getNotificationsCount----".$notificationsCount);
            return $notificationsCount;
         } catch (Exception $ex) {
            Yii::log("NotificationCollection:getNotificationsCount::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');

        }
     }
    
      public static function getNotifications($user,$projectId,$offset=0,$limit=5,$viewAll=0)
    { 
           try{
          $cond=["CollaboratorUsers" =>["CollaboratorId" =>(int) $user,"IsRead" =>(int)0],'ProjectId'=>(int)$projectId] ; 
          if($viewAll==1){
            $cond=["CollaboratorUsers.CollaboratorId" =>(int) $user,'ProjectId'=>(int)$projectId] ; 
          }
         $query=new Query();
            $query->from('NotificationCollection')
            ->where($cond)
            ->andWhere(['!=','ActivityFrom', (int)$user])
            ->orderBy(["_id"=>SORT_DESC])
             ->offset($offset)      
            ->limit($limit);
            $notifications=$query->all();
            return $notifications;
          } catch (Exception $ex) {
            Yii::log("NotificationCollection:getNotifications::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');

        }
     }
    
 
    /**
     * @author Ryan Marshal
     * @param type $notifyid,$user
     * @return type 
     * @description  Used for deleting the notification 
     */
    public static function deleteNotification($notify)
    {
        $user=$notify->userInfo->Id;
        $notifyid=$notify->notifyid;
        try
        {
            $notifications=NotificationCollection::getCollection();
            $notifications->update(array('_id'=>$notifyid,'CollaboratorUsers.CollaboratorId'=>(int)$user), array('$set'=>array('CollaboratorUsers.$.IsRead'=>1)));
            return;
        }catch(Exception $ex)
        {
            Yii::log("NotificationCollection:deleteNotification::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
    /**
     * @author Ryan Marshal
     * @param type $notifyid,$user
     * @return type 
     * @description  Used for deleting all notifications of the user 
     */
    public static function deleteAllNotifications($notify)
    {
        $user=$notify->userInfo->Id;
        try
        {
            $notification=NotificationCollection::getCollection();
            foreach($notification as $notify)
            {
                $notification->update(array('CollaboratorUsers.CollaboratorId'=>(int)$user), array('$set'=>array('CollaboratorUsers.$.IsRead'=>1)));
            }
            return;
        }catch(Exception $ex)
        {
            Yii::log("NotificationCollection:deleteAllNotifications::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    /**
     * @author Moin Hussain
     * @param type $notificationId
     * @param type $ticketId
     * @param type $projectId
     * @return type
     */
        public static function getNotificationDetail($notificationId,$ticketId,$projectId){
        try{
           $query=new Query();
            $query->from('NotificationCollection')
            ->where(["CommentSlug" => $notificationId,"TicketId" => (int)$ticketId,"ProjectId" => (int)$projectId])
                     ->orderBy(["_id"=>SORT_DESC]);
           //  ->where(["ProjectId" => (int)$projectId]);
            $notification = $query->one();
            return $notification;  
        } catch (Exception $ex) {
            Yii::log("NotificationCollection:getNotificationDetail::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');

        }
    }
        }
