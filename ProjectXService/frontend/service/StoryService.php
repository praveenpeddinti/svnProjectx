<?php
namespace frontend\service;
use common\models\mongo\TicketCollection;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class StoryService {
    /**
     * 
     * @param type $ticketId
     */
        public function StoryService($ticketId) {
        try {
            $model = new TicketCollection();
            $model->getTicketDetails($ticketId);
           
        } catch (Exception $ex) {
            Yii::log("StoryService:StoryService::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
}

