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
use common\models\mysql\StoryFields;
use common\models\mysql\StoryCustomFields;
use common\models\mysql\FieldTypes;
use common\models\mysql\MapListCustomStoryFields;
use common\models\mysql\PlanLevel;
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
            $ticketCollectionModel = new TicketCollection();
            $ticketDetails = $ticketCollectionModel->getTicketDetails($ticketId,$projectId);
            $storyFieldsModel = new StoryFields();
            $storyCustomFieldsModel = new StoryCustomFields();
            $tinyUserModel =  new TinyUserCollection();
            $bucketModel = new Bucket();
            $priorityModel = new Priority();
            $mapListModel = new MapListCustomStoryFields();
            $planlevelModel = new PlanLevel();
            $workFlowModel = new WorkFlowFields();
            foreach ($ticketDetails["Fields"] as &$value) {
              //  echo $value["Id"]."---".$value["value"]."\n";
               if(isset($value["custom_field_id"] )){
                $storyFieldDetails = $storyCustomFieldsModel->getFieldDetails($value["Id"]);
                 if($storyFieldDetails["Name"] == "List"){
                
                    $listDetails = $mapListModel->getListValue($value["Id"],$value["value"]);
                    $value["readable_value"] = $listDetails; 
                }
                
                
                
               }else{
                 $storyFieldDetails = $storyFieldsModel->getFieldDetails($value["Id"]);
   
               }
                $value["title"] = $storyFieldDetails["Title"];
                $value["required"] = $storyFieldDetails["Required"];
                $value["readonly"] = $storyFieldDetails["ReadOnly"];
                $value["field_type"] = $storyFieldDetails["Name"];
                $value["field_name"] = $storyFieldDetails["Field_Name"];
                if($storyFieldDetails["Type"] == 6){
                  $assignedToDetails = $tinyUserModel->getMiniUserDetails($value["value"]);
                  $value["readable_value"] = $assignedToDetails;  
                }
                 if($storyFieldDetails["Type"] == 8){
                
                 $bucketName = $bucketModel->getBucketName($value["value"],$ticketDetails["ProjectId"]);
                 $value["readable_value"] = $bucketName;  
                }
                if($storyFieldDetails["Field_Name"] == "priority"){
                
//                    $priorityDetails = $priorityModel->getPriorityDetails($value["value"]);
//                    $value["readable_value"] = $priorityDetails; 
                }
                 if($storyFieldDetails["Field_Name"] == "planlevel"){
                
//                    $planlevelDetails = $planlevelModel->getPlanLevelDetails($value["value"]);
//                    $value["readable_value"] = $planlevelDetails; 
                }
                 if($storyFieldDetails["Field_Name"] == "workflow"){
                
                   
                    $workFlowDetails = $workFlowModel->getWorkFlowDetails($value["value"]);
                     $value["readable_value"] = $workFlowDetails; 
                }
               
               
                
                
            }
            print_r($ticketDetails["Fields"]);
            
            
//            $tinyUser =  new TinyUserCollection();
//            $assignedToDetails = $tinyUser->getMiniUserDetails($ticketDetails["AssignedTo"]);
//            $reportedByDetails = $tinyUser->getMiniUserDetails($ticketDetails["ReportedBy"]);
//            
//            $bucketObject = new Bucket();
//            $bucketName = $bucketObject->getBucketName($ticketDetails["Bucket"],$ticketDetails["ProjectId"]);
//            $ticketTypeObject = new TicketType();
//            $ticketTypeDetails = $ticketTypeObject->getTicketType($ticketDetails["TicketType"]);
//            
//            $priorityObj = new Priority();
//            $priorityDetails = $priorityObj->getPriorityDetails($ticketDetails["Priority"]);
//            $projectObj = new Projects();
//            $projectDetails = $projectObj->getProjectMiniDetails($ticketDetails["ProjectId"]);
//            $workFlowObj = new WorkFlowFields();
//            $workFlowDetails = $workFlowObj->getWorkFlowDetails($ticketDetails["Status"]);
//            $ticketDetails["Priority"] = $priorityDetails;
//            $ticketDetails["Project"] = $projectDetails;
//            $ticketDetails["Status"] = $workFlowDetails;
//            $ticketDetails["AssignedTo"] = $assignedToDetails;
//            $ticketDetails["ReportedBy"] = $reportedByDetails;
//            $ticketDetails["Bucket"] = $bucketName;
//            $ticketDetails["TicketType"] = $ticketTypeDetails;
            //error_log(print_r($priorityDetails)."-----".print_r($projectDetails)."--".print_r($workFlowDetails)."--".print_r($tinyUserDetails));
           // error_log(print_r($ticketDetails));
           // return $ticketDetails;
        } catch (Exception $ex) {
            Yii::log("StoryService:getTicketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
}

