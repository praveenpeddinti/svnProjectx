<?php
namespace frontend\service;

use common\components\CommonUtility;
use common\models\mongo\TicketCollection;
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
         //print_r($details);
        } catch (Exception $ex) {
            Yii::log("StoryService:getTicketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
       
      } 
      public function getAllTicketDetails() {
        try {
         $model = new TicketCollection();
         $ticketDetails = $model->getAllTicketDetails();
         $ticketDetails =array(101,200,201,202,203,204,205,206,207,208,209,210);
         //$ticketDetails =array(209);
         $finalData = array();
         foreach ($ticketDetails as $ticket){
             //print_r($ticket);
             //echo $ticket["TicketId"];
             $details =  CommonUtility::prepareTicketDetails($ticket, 1);
              //print_r($details);
             array_push($finalData,$details);
             //break;
         }
        // $details =  CommonUtility::prepareTicketDetails(101,1);
         
         return $finalData;
        } catch (Exception $ex) {
            Yii::log("StoryService:getAllTicketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }

    }
} 
