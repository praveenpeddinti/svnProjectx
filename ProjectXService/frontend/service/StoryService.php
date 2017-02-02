<?php
namespace frontend\service;

use common\components\CommonUtility;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class StoryService {
    /**
     * @author Moin Hussain
     * @param type $ticketId
     * @param type $projectId
     * @return type
     */
        public function getTicketDetails($ticketId, $projectId) {
        try {
         $details =  CommonUtility::prepareTicketDetails($ticketId, $projectId);
         print_r($details);
        } catch (Exception $ex) {
            Yii::log("StoryService:getTicketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
       
      } 
      public function getAllTicketDetails($projectId) {
        try {
            
         $details =  CommonUtility::prepareTicketDetails($ticketId, $projectId);
         print_r($details);
        } catch (Exception $ex) {
            Yii::log("StoryService:getTicketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }

      }
}
