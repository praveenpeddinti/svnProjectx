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
use common\models\bean\ResponseBean;
use common\components\CommonUtility;
class NotificationsController extends Controller
{
    
    public function actionHello($data)
    {
        $tic = new TicketCollection();
        $tic->Title = $data;
        $tic->save();
        echo json_encode(array("status" => "success"));

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
