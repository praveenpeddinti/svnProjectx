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
            $page=$notification_data->page;
            $viewAll=$notification_data->viewAll;
            if($viewAll==1) {
              $limit=15; 
              $offset= $page-1;
            }else if(isset ($notification_data->clientType) && $notification_data->clientType=='mobile'){
               $limit = 10; 
               $offset=($page-1) * $limit;
            }
            $projectId=$notification_data->projectId;
            $notified_userid=$notification_data->userInfo->Id;
            $notified_username=$notification_data->userInfo->username;
            //$result_data=NotificationCollection::getNotifications($notified_username,$projectId);
            $result_data = ServiceFactory::getStoryServiceInstance()->getNotifications($notified_userid,$projectId,$offset,$limit,$viewAll);
           // $result_data=NotificationCollection::getNotifications($notified_userid,$projectId,$offset,$limit);
            
            echo json_encode(array('notify_result'=>$result_data));
            
        } catch (\Throwable $ex) {
            echo "Exception in NotificationsController::actionGetAllNotifications ".$ex->getMessage();
            Yii::error("NotificationsController:actionGetAllNotifications::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
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
            $result_count=NotificationCollection::getNotificationsCount($notified_userid);
            echo json_encode(array('count'=>$result_count));
            
        } catch (\Throwable $ex) {
            echo "Exception in NotificationsController::actionGetAllNotificationsCount ".$ex->getMessage();
            Yii::error("NotificationsController:actionGetAllNotificationsCount::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
        
    }/**
     * @author Moin Hussain
     * @param type $notificationIds
     * @param type $projectId
     */
      public function actionForkEmailNotificationProcess($notificationIds,$projectId,$bulkUpdate){
          try{
          // echo("\n".date("Y-m-d H:i:s T")."****************************************************************\n");
          echo("Sending email background job has startedSSSSS\n=====".$bulkUpdate."********mailFlag");
         // echo "actionSendEmailNotification--".$notificationIds;
           $notificationIds = json_decode($notificationIds,true);
            // echo "actionSendEmailNotification-)))****-".$notificationIds;
           $notificationArray = array();
           foreach($notificationIds as $v){
               array_push($notificationArray, new \MongoDB\BSON\ObjectID($v['$oid']));
           }
           echo ("CAME___");
            //echo "actionSendEmailNotification-- after".print_r($notificationArray,1);
          if($bulkUpdate==1){
              echo ("!!!!!!!!!!!!!!!");
            $result_data = ServiceFactory::getStoryServiceInstance()->sendBulkEmailNotification($notificationArray,$projectId,$bulkUpdate);
          }else{
              echo ("!!!@@@@@@@@@~!!!!!!!!!!!!!!1__SIZE".  sizeof($notificationArray));
            $result_data = ServiceFactory::getStoryServiceInstance()->sendEmailNotificationFromBackground($notificationArray,$projectId);
          }
          } catch (\Throwable $ex) {
            echo "Exception in NotificationsController::actionForkEmailNotificationProcess ".$ex->getMessage();
            Yii::error("NotificationsController:actionForkEmailNotificationProcess::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
          
         
      }
      
      /**
       * @author Anand
       * @descrption Get latest activity
       * @param type $data
       */
      
      public function actionGetLatestActivity($data)
    {
        $activityFrom=array();
        //logic for getting all the notifications for a particular loggedIn user
        try
        {
            $post_data = json_decode($data);
            $response = ServiceFactory::getStoryServiceInstance()->getTicketActivity($post_data);
            echo json_encode(array('activityData'=>$response));
            
        } catch (\Throwable $ex) {
            echo "Exception in NotificationsController::actionGetLatestActivity ".$ex->getMessage();
            Yii::error("NotificationsController:actionGetLatestActivity::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
        
    }
    
     /**
       * @author Ryan
       * @descrption send email for Invitation
       * @param type $mailingName
       * @param type $invite_list
       * @param type $text_message
       * @param type $subject
       */
    public function actionForkSingleEmailProcess($mailingName,$invite_list,$text_message,$subject){
        try{
            ServiceFactory::getCollaboratorServiceInstance()->sendSingleMailToInvite($mailingName,$invite_list,$text_message,$subject);
        } catch (\Throwable $ex) {
            echo "Exception in NotificationsController::actionForkSingleEmailProcess ".$ex->getMessage();
            Yii::error("NotificationsController:actionForkSingleEmailProcess::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
}
