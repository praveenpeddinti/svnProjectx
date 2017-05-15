<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace console\controllers;
use Yii;
use yii\console\Controller;
use common\models\mongo\NotificationCollection;
use common\components\ServiceFactory; //only for testing purpose
 //only for testing purpose
//include_once 'ElasticEmailClient.php';

class NotificationsController extends Controller
{
    
//    public function actionMentionedNotify()
//    {
//        //logic for sending the notifications to all the notified people
//    }
    
    /**
     * @author Ryan
     * @param type $data
     * @return type json
     */
    public function actionGetAllNotifications($data)
    {
        $activityFrom=array();
        //logic for getting all the notifications for a particular loggedIn user
        try
        { 
            
            $offset = 0;
            $limit = 5;
            $notification_data = json_decode($data);
           // error_log("Post__DATA".print_r($notification_data,1));
            $page=$notification_data->page;
            $viewAll=$notification_data->viewAll;
            if($viewAll==1) {
              $limit=15; 
              $offset=($page-1) * $limit;
            }
            $projectId=$notification_data->projectId;
            $notified_userid=$notification_data->userInfo->Id;
            $notified_username=$notification_data->userInfo->username;
            //$result_data=NotificationCollection::getNotifications($notified_username,$projectId);
            $result_data = ServiceFactory::getStoryServiceInstance()->getNotifications($notified_userid,$projectId,$offset,$limit,$viewAll);
           // $result_data=NotificationCollection::getNotifications($notified_userid,$projectId,$offset,$limit);
            
            echo json_encode(array('notify_result'=>$result_data));
            
        } catch (Exception $ex) {
            Yii::log("NotificationsController:actionGetAllNotifications::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');

        }
        
    }
    
    /**
     * @author Ryan
     * @param type $data
     * @return type json
     */
    public function actionGetAllNotificationsCount($data)
    {
        $activityFrom=array();
        //logic for getting all the notifications for a particular loggedIn user
        try
        {
            $notification_data = json_decode($data);
            $projectId=$notification_data->projectId;
            $notified_userid=$notification_data->userInfo->Id;
            $notified_username=$notification_data->userInfo->username;
            //$result_data=NotificationCollection::getNotifications($notified_username,$projectId);
            $result_count=NotificationCollection::getNotificationsCount($notified_userid,$projectId);
            echo json_encode(array('count'=>$result_count));
            
        } catch (Exception $ex) {
            Yii::log("NotificationsController:actionGetAllNotifications::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');

        }
        
    }
      public function actionSend($notificationIds,$projectId){
         // echo "actionSendEmailNotification--".$notificationIds;
           $notificationIds = json_decode($notificationIds,true);
            // echo "actionSendEmailNotification-)))****-".$notificationIds;
           $notificationArray = array();
           foreach($notificationIds as $v){
               array_push($notificationArray, new \MongoDB\BSON\ObjectID($v['$oid']));
           }
            //echo "actionSendEmailNotification-- after".print_r($notificationArray,1);
           $result_data = ServiceFactory::getStoryServiceInstance()->sendEmailNotificationFromBackground($notificationArray,$projectId);
         
      }
    

}
