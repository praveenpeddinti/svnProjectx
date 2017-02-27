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
use common\models\mysql\Bucket;
use common\models\bean\FieldBean;
use common\models\mongo\ProjectTicketSequence;
use common\models\mysql\Collaborators;
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
             $ticketCollectionModel = new TicketCollection();
         $ticketDetails = $ticketCollectionModel->getTicketDetails($ticketId,$projectId);  
         $details =  CommonUtility::prepareTicketDetails($ticketDetails, $projectId);
         return $details;
        } catch (Exception $ex) {
            Yii::log("StoryService:getTicketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
       
      }
     /**
     * @author Moin Hussain
     * @param type $ticketId
     * @param type $projectId
     * @return type
     */
       public function getTicketEditDetails($ticketId, $projectId) {
        try {
         $editDetails =  CommonUtility::prepareTicketEditDetails($ticketId, $projectId);
         return $editDetails;
        } catch (Exception $ex) {
            Yii::log("StoryService:getTicketEditDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
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
     * @author Moin Hussain
     * @return type
     */
    public function getNewTicketStoryFields() {
        try {
           $storyFieldModel = new StoryFields();
           return $storyFieldModel->getNewTicketStoryFields();
        } catch (Exception $exc) {
            Yii::log("StoryService:getNewTicketStoryFields::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
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
    /**
     * @author Moin Hussain
     * @return type
     */
    public function getStoryWorkFlowList(){
        try{
           $workFlowModel = new WorkFlowFields();
           return $workFlowModel->getStoryWorkFlowList();
        } catch (Exception $ex) {
Yii::log("StoryService:getWorkFlowDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    /**
     * @author Moin Hussain
     * @return type
     */
      public function getBucketsList($projectId){
        try{
           $bucketModel = new Bucket();
           return $bucketModel->getBucketsList($projectId);
        } catch (Exception $ex) {
Yii::log("StoryService:getWorkFlowDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
/**
 * @author Moin Hussain
 * @param type $ticket_data
 */
       public function saveTicketDetails($ticket_data) {
        try {

             $userdata =  $ticket_data->userInfo;
             $projectId =  $ticket_data->projectId;
             $userId = $userdata->Id;
             $collaboratorData = Collaborators::getCollboratorByFieldType("Id",$userId);
            // error_log(print_r($collaboratorData,1));
              $ticket_data = $ticket_data->data;
              $dataArray = array();
              $fieldsArray = array();
              $title =  $ticket_data->title;
              $description =  $ticket_data->description;
              $crudeDescription = $description;
              $description = CommonUtility::refineDescription($description);
            
              
             
              unset($ticket_data->title);
              unset($ticket_data->description);
              
               $storyField = new StoryFields();
                  $standardFields = $storyField->getStoryFieldList();
                  foreach ($standardFields as $field) {
                     $fieldBean = new FieldBean();
                     $fieldId =  $field["Id"];
                     $fieldType =  $field["Type"];
                     $fieldTitle =  $field["Title"];
                      $fieldName =  $field["Field_Name"];
                     $fieldBean->Id = (int)$field["Id"];
                     $fieldBean->title = $fieldTitle;
                     
                     if($fieldType == 6 && $fieldName == "reportedby"){
                         $fieldBean->value= (int)$collaboratorData["Id"]; 
                         $fieldBean->value_name= $collaboratorData["UserName"]; 
                     }
                     else if($fieldName == "tickettype"){
                         $fieldBean->value= (int)1; 
                     }
                     else if($fieldName == "tickettype"){
                         $fieldBean->value= (int)1; 
                     }
                     else if($fieldName == "workflow"){
                         $fieldBean->value= (int)1; 
                     }
                     else if($fieldName == "estimatedpoints"){
                         $fieldBean->value= (int)0; 
                     }
                     else if($fieldType == 4 || $fieldType == 5){
                         if($fieldName == "duedate"){
                              $fieldBean->value= "";
                         }else{
                            $fieldBean->value= new \MongoDB\BSON\UTCDateTime(time() * 1000);   
                         }
                            
                         }
                     else if($fieldType == 8){
                        $bucketId = Bucket::getBackLogBucketId($projectId);
                        $fieldBean->value = (int)$bucketId;
                     }
                     else{
                          $fieldBean->value=""; 
                     }
                     if(isset($ticket_data->$fieldId)){
                          if(is_numeric($ticket_data->$fieldId)){
                              $fieldBean->value= (int)$ticket_data->$fieldId;
                          }else{
                              $fieldBean->value= $ticket_data->$fieldId;
                          }
                          
                     }
                     $dataArray[$fieldName]= $fieldBean;
                      //array_push($dataArray, $fieldBean);
                  }

           $ticketModel = new TicketCollection();
           $ticketModel->Title = $title;
           $ticketModel->Description = $description;
           $ticketModel->CrudeDescription = $crudeDescription;
           $ticketModel->Fields = $dataArray;
           $ticketModel->ArtifactsRef = "";
           $ticketModel->CommentsRef = "";
           $ticketModel->FollowersRef = "";
           $ticketModel->ProjectId = 1;
           $ticketModel->RelatedStories= [];
           $ticketModel->Tasks= [];
           $ticketNumber = ProjectTicketSequence::getNextSequence($projectId);
           $ticketModel->TicketId = (int)$ticketNumber;
           $ticketModel->TotalEstimate = 0;
           $ticketModel->TotalTimeLog = 0;
                  
             
           
           TicketCollection::saveTicketDetails($ticketModel);
            
        } catch (Exception $ex) {
             error_log($ex->getMessage());
            
            Yii::log("StoryService:saveTicketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
       
      }
     /**
     * @author Praveen P
     * @return type
     */
    public function getAllStoryDetails($StoryData, $projectId) {
        try {
            $ticketModel = new TicketCollection();
            $ticketDetails = $ticketModel->getAllTicketDetails($StoryData, $projectId,$select=['TicketId', 'Title','Fields','ProjectId']);
            $finalData = array();
            $fieldsOrderArray = [5,6,7,3,10];
           //  $fieldsOrderArray = [10,11,12,3,4,5,6,7,8,9];
            foreach ($ticketDetails as $ticket) {
                $details = CommonUtility::prepareDashboardDetails($ticket, $projectId,$fieldsOrderArray);
                array_push($finalData, $details);
            }
            return $finalData;
        } catch (Exception $ex) {
            Yii::log("StoryService:getAllTicketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
    /**
     * @author Praveen P
     * getting total count.
     * @return type  $projectId
     */
    public function getTotalStorys($projectId) {
        try {
            $model = new TicketCollection();
            $totalCount = $model->getTotalStorys($projectId);
            
            return $totalCount;
        } catch (Exception $ex) {
            Yii::log("StoryService:getAllTicketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
    /**
         * @author Anand Singh
         * @return type
         */
        public function getMyTickets() {
            try {
               $priorityModel = new TicketCollection();
           //return $priorityModel->getMyAssignedTickets();
          return $priorityModel->updateTicketField();
            } catch (Exception $exc) {
                Yii::log("StoryService:getPriority::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
            }
        }

    
        /**
 * @author Moin Hussain
 * @param type $ticket_data
 */
       public function updateTicketDetails($ticket_data) {
        try {
            $workflowModel = new WorkFlowFields();
            $priorityModel = new Priority();
            $bucketModel = new Bucket();
            $planlevelModel = new PlanLevel();
            $tickettypeModel = new TicketType();
             $userdata =  $ticket_data->userInfo;
             $projectId =  $ticket_data->projectId;
             $userId = $userdata->Id;
             $collaboratorData = Collaborators::getCollboratorByFieldType("Id",$userId);
            // error_log(print_r($collaboratorData,1));
              $ticket_data = $ticket_data->data;
            
                $ticketCollectionModel = new TicketCollection();
               $ticketDetails = $ticketCollectionModel->getTicketDetails($ticket_data->TicketId,$projectId);
        $ticketDetails["Title"] = $ticket_data->title;
              $description = $ticket_data->description;
              $ticketDetails["CrudeDescription"] = $description;
            $ticketDetails["Description"] = CommonUtility::refineDescription($description);
          //  error_log("data---".print_r($ticket_data,1));
             // unset($ticket_data->title);
             // unset($ticket_data->description);
               foreach ($ticketDetails["Fields"] as &$value) {
                 $fieldId =  $value["Id"];
                //$value["value_name"]="";
                     if(isset($ticket_data->$fieldId)){
                         
                        $fieldDetails =  StoryFields::getFieldDetails($fieldId);
                         if(is_numeric($ticket_data->$fieldId)){
                              $value["value"] = (int)$ticket_data->$fieldId; 
                               if($fieldDetails["Type"] == 6){
                                $collaboratorData = Collaborators::getCollboratorByFieldType("Id",$ticket_data->$fieldId);
                                $value["value_name"] = $collaboratorData["UserName"];
                                }
                                else if($fieldDetails["Field_Name"] == "workflow"){
                                $workFlowDetail =  $workflowModel->getWorkFlowDetails($ticket_data->$fieldId);
                                $value["value_name"] = $workFlowDetail["Name"];
                                }
                                else if($fieldDetails["Field_Name"] == "priority"){
                                $priorityDetail =  $priorityModel->getPriorityDetails($ticket_data->$fieldId);
                                $value["value_name"] = $priorityDetail["Name"];
                                }
                                else if($fieldDetails["Field_Name"] == "bucket"){
                                $bucketDetail =  $bucketModel->getBucketName($ticket_data->$fieldId,$projectId);
                                $value["value_name"] = $bucketDetail["Name"];
                                }
                                else if($fieldDetails["Field_Name"] == "planlevel"){
                                $planlevelDetail =  $planlevelModel->getPlanLevelDetails($ticket_data->$fieldId);
                                $value["value_name"] = $planlevelDetail["Name"];
                                }
                                else if($fieldDetails["Field_Name"] == "tickettype"){
                                $tickettypeDetail =  $tickettypeModel->getTicketType($ticket_data->$fieldId);
                                $value["value_name"] = $tickettypeDetail["Name"];
                                }
                                        
                         }else{
                             if($ticket_data->$fieldId != ""){
                                 
                                 if($fieldDetails["Type"] == 4){
                                       $validDate = CommonUtility::validateDate($ticket_data->$fieldId);
                                      if($validDate){
                                     $value["value"] = new \MongoDB\BSON\UTCDateTime(strtotime($validDate) * 1000); 
                                 }
                                
                             }else{
                                 
                                 $value["value"] = $ticket_data->$fieldId; 
                              
                             } 
                             }
                            
                             
                         }
                       
                     }
                   
                
             }
             //error_log(print_r($ticketDetails["Fields"],1));
             $collection = Yii::$app->mongodb->getCollection('TicketCollection');
            $collection->save($ticketDetails); 
            
        } catch (Exception $ex) {
             error_log($ex->getMessage());
            
            Yii::log("StoryService:saveTicketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
       
      }
        /*
    * @author Padmaja
    * @param type $fieldData
     */
    public function getUpdateStoryDetails($fieldData){
        try{
            $storyFieldModel = new TicketCollection();
            //return $priorityModel->getMyAssignedTickets();
            return $storyFieldModel->updateStoryField($fieldData);
        } catch (Exception $ex) {
              Yii::log("StoryService:getUpdateStoryDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }    

        
      
}

  