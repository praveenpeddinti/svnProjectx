<?php
namespace frontend\service;
use common\models\mongo\TicketCollection;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class StoryService {
    
        public function getTicketDetails($ticketId) {
        try {
            $model = new TicketCollection();
            $model->getTicketDetails(104);
           
        } catch (Exception $ex) {
            Yii::log("SkiptaUserService:saveToUserCollection::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
}

