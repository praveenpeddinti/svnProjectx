<?php
namespace frontend\service;
use common\models\mongo\TicketCollection;
use common\components\CommonUtility;
use common\models\mysql\Priority;
use common\models\mysql\Projects;
use common\models\mysql\WorkFlowFields;
use common\models\mongo\TinyUserCollection;
use common\models\mysql\Bucket;
use common\models\mysql\TicketType;
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
            $model = new TicketCollection();
            $ticketDetails = $model->getTicketDetails($ticketId,$projectId);
            
            $tinyUser =  new TinyUserCollection();
            $assignedToDetails = $tinyUser->getMiniUserDetails($ticketDetails["AssignedTo"]);
            $reportedByDetails = $tinyUser->getMiniUserDetails($ticketDetails["ReportedBy"]);
            
            $bucketObject = new Bucket();
            $bucketName = $bucketObject->getBucketName($ticketDetails["Bucket"],$ticketDetails["ProjectId"]);
            $ticketTypeObject = new TicketType();
            $ticketTypeDetails = $ticketTypeObject->getTicketType($ticketDetails["TicketType"]);
            
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
        } catch (Exception $ex) {
            Yii::log("StoryService:getTicketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
}

