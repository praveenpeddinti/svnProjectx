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

class NotificationsController extends Controller
{
    
    public function actionAssignedNotify($collaborator)
    {
        
        error_log("===Collaborator Id==".$collaborator);
        //logic for sending the notifications to  all the followers and the stakeholders
        
        //save all the followers for ticket in mongo collection
        //TicketCollection::getAllTicketDetails($StoryData, $projectId);
    }
    
    public function actionFollowNotify()
    {
        //logic for sending the notifications to all the stakeholders
    }
    
    public function actionMentionedNotify()
    {
        //logic for sending the notifications to all the notified people
    }
}
