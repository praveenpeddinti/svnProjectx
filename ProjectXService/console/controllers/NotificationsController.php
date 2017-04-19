<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace console\controllers;
use Yii;
use yii\console\Controller;
use yii\helpers\Console;
use common\models\bean\FieldBean;
use frontend\controllers;
use common\models\mongo\TicketCollection;
use common\models\mongo\NotificationCollection;
use common\models\bean\ResponseBean;
use common\components\CommonUtility;
use common\models\mongo\TinyUserCollection;

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
            $notification_data = json_decode($data);
            $projectId=$notification_data->projectId;
            $notified_userid=$notification_data->userInfo->Id;
            $notified_username=$notification_data->userInfo->username;
            //$result_data=NotificationCollection::getNotifications($notified_username,$projectId);
            $result_data=NotificationCollection::getNotifications($notified_userid,$projectId);
            
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
    

}
