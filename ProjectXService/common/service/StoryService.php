<?php
namespace common\service;
use common\models\mongo\TicketCollection;
use common\models\mongo\TinyUserCollection;
use common\components\CommonUtility;
use common\models\mysql\WorkFlowFields;
use common\models\mysql\StoryFields;
use common\models\mysql\Priority;
use common\models\mysql\PlanLevel;
use common\models\mysql\TicketType;
use Yii;

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
         return $details;
        } catch (Exception $ex) {
            Yii::log("StoryService:getTicketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
       
      } 
      public function getAllTicketDetails($projectId) {
        try {

            $priorityObj = new Priority();
            $priorityDetails = $priorityObj->getPriorityDetails($ticketDetails["Priority"]);
            $projectObj = new Projects();
            $projectDetails = $projectObj->getProjectMiniDetails($ticketDetails["ProjectId"]);
            $workFlowObj = new WorkFlowFields();
            $workFlowDetails = $workFlowObj->getWorkFlowDetails($ticketDetails["Status"]);
            $ticketDetails["Priority"] = $priorityDetails;
            $ticketDetails["Project"] = $projectDetails;
            $ticketDetails["Status"] = $workFlowDetails;
            $ticketDetails["AssignedTo"] = $assignedToDetails;
            $ticketDetails["ReportedBy"] = $reportedByDetails;
            $ticketDetails["Bucket"] = $bucketName;
            $ticketDetails["TicketType"] = $ticketTypeDetails;
            //error_log(print_r($priorityDetails)."-----".print_r($projectDetails)."--".print_r($workFlowDetails)."--".print_r($tinyUserDetails));
            // error_log(print_r($ticketDetails));
            return $ticketDetails;

         $details =  CommonUtility::prepareTicketDetails($ticketId, $projectId);
         print_r($details);

        } catch (Exception $ex) {
            Yii::log("StoryService:getTicketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

    /**
     * @author Anand Singh
     * @param type $projectId
     * @return type
     */
    public function getStoryFieldList() {
        try {
           $storyFieldModel = new StoryFields();
           return $storyFieldModel->getStoryFieldList();
        } catch (Exception $exc) {
            Yii::log("StoryService:getStoryFields::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

        /**
         * @author Anand Singh
         * @return type
         */
        public function getPriorityList() {
            try {
               $priorityModel = new Priority();
           return $priorityModel->getPriorityList();
            } catch (Exception $exc) {
                Yii::log("StoryService:getPriority::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
            }
        }

    /**
     * @author Anand Singh
     * @return type
     */
    public  function getPlanLevelList() {
        try {
           $planlevelModel = new PlanLevel();
           return $planlevelModel->getPlanLevelList();
        } catch (Exception $exc) {
            Yii::log("StoryService:getPlanLevel::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

    /**
     * @author Anand Singh
     * @return type
     */
    public  function getTicketTypeList() {
        try {
            $ticketTypeModel = new TicketType();
            return $ticketTypeModel->getTicketTypeList();
        } catch (Exception $exc) {
            Yii::log("StoryService:getTicketType::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
    public function getStoryWorkFlowList(){
        try{
           $workFlowModel = new WorkFlowFields();
           return $workFlowModel->getStoryWorkFlowList();
        } catch (Exception $ex) {
Yii::log("StoryService:getWorkFlowDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

       public function saveTicketDetails() {
        try {
        
        } catch (Exception $ex) {
            Yii::log("StoryService:saveTicketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
       
      } 
}

  