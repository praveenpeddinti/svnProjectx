<?php
namespace common\service;
use common\models\mongo\{TicketCollection,TinyUserCollection,ProjectTicketSequence,TicketTimeLog,TicketComments,TicketArtifacts,NotificationCollection};
use common\components\{CommonUtility};
use common\models\mysql\{WorkFlowFields,StoryFields,Priority,PlanLevel,TicketType,Bucket,Collaborators,TaskTypes,Filters,Projects};
use common\models\bean\FieldBean;
use Yii;
use common\service\NotificationTrait;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class StoryService {

    use NotificationTrait;
    /**
     * @author Moin Hussain
     * @param type $ticketId
     * @param type $projectId
     * @return type
     */
    public function getTicketDetails($ticketId, $projectId) {
        try {
          $details = "NOTFOUND";  
         $ticketDetails = TicketCollection::getTicketDetails($ticketId,$projectId); 
         if(!empty($ticketDetails)){
          $details =  CommonUtility::prepareTicketDetails($ticketDetails, $projectId);   
         }
           
       
         
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
            $workFlowDetails = WorkFlowFields::getWorkFlowDetails($ticketDetails["Status"]);
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
           return StoryFields::getNewTicketStoryFields();
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
           return Priority::getPriorityList();
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
           return PlanLevel::getPlanLevelList();
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
            return TicketType::getTicketTypeList();
        } catch (Exception $exc) {
            Yii::log("StoryService:getTicketType::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    /**
     * @author Moin Hussain
     * @return type
     * @modification -ANAND - Now thsi method has argument $workflowType,$workflowId
     */
    public function getStoryWorkFlowList($workflowType,$workflowId){
        try{
           return WorkFlowFields::getStoryWorkFlowList($workflowType,$workflowId);
        } catch (Exception $ex) {
Yii::log("StoryService:getStoryWorkFlowList::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    /**
     * @author Moin Hussain
     * @return type
     */
      public function getBucketsList($projectId){
        try{
           return Bucket::getBucketsList($projectId);
        } catch (Exception $ex) {
Yii::log("StoryService:getBucketsList::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
/**
 * @author Moin Hussain
 * @param type $ticket_data
 * @updated $parentTicNumber by suryaprakash
 */
       public function saveTicketDetails($ticket_data,$parentTicNumber="") {
        try {
             $newticket_data=$ticket_data; //added by Ryan
             $userdata =  $ticket_data->userInfo;
             $projectId =  $ticket_data->projectId;
             $userId = $userdata->Id;
             $collaboratorData = Collaborators::getCollboratorByFieldType("Id",$userId);
            // error_log(print_r($collaboratorData,1));
              $ticket_data = $ticket_data->data;
              $dataArray = array();
              $fieldsArray = array();
              $title =  trim($ticket_data->title);
              $description =  trim($ticket_data->description);
              $workflowType=$ticket_data->WorkflowType;
              $crudeDescription = $description;
              $refinedData = CommonUtility::refineDescription($description);
              $description = $refinedData["description"];
            
             
             
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
//                     else if($fieldName == "tickettype"){
//                         $fieldBean->value= (int)1; 
//                     }
                     else if($fieldName == "tickettype"){
                            $fieldBean->value= (int)1; 
                            $tickettypeDetail = TicketType::getTicketType($fieldBean->value);
                            $fieldBean->value_name = $tickettypeDetail["Name"];
                          }
                     else if($fieldName == "workflow"){
                            $fieldBean->value= (int)1; 
                            $workFlowDetail = WorkFlowFields::getWorkFlowDetails($fieldBean->value);
                            $fieldBean->value_name= $workFlowDetail["Name"]; 
                          }
                     else if($fieldName == "estimatedpoints"){
                         $fieldBean->value= ""; 
                         $fieldBean->value_name= ""; 
                     }
                      else if($fieldName == "totalestimatepoints"){
                         $fieldBean->value= (int)0; 
                         $fieldBean->value_name= ""; 
                     }
                     else if($fieldName == "state"){
                         $fieldBean->value= (int)1; 
                         $fieldBean->value_name= "New"; 
                     }
                     else if($fieldType == 4 || $fieldType == 5){
                         if($fieldName == "duedate"){
                              $fieldBean->value= "";
                         }else{
                            $fieldBean->value= new \MongoDB\BSON\UTCDateTime(time() * 1000);   
                         }
                            $fieldBean->value_name= $fieldBean->value; 
                         }
                     else if($fieldType == 10){
                            $bucket = Bucket::getBackLogBucketId($projectId);
                            $fieldBean->value = (int)$bucket["Id"];
                            $fieldBean->value_name = $bucket["Name"];
                         }
                     else{
                          $fieldBean->value=""; 
                     }
                     if(isset($ticket_data->$fieldName)){
                          if(is_numeric($ticket_data->$fieldName)){
                               $fieldBean->value= (int)$ticket_data->$fieldName;
                              if($fieldName == "planlevel"){
                                $details =  PlanLevel::getPlanLevelDetails($ticket_data->$fieldName);
                                }
                              else if($fieldName == "priority"){
                                    $details = Priority::getPriorityDetails($ticket_data->$fieldName);
                                }  
                               $fieldBean->value_name= $details["Name"];
                             
                          }else{
                              $fieldBean->value= $ticket_data->$fieldId;
                              $fieldBean->value_name= $ticket_data->$fieldId;
                          }
                          
                     }
                     $dataArray[$fieldName]= $fieldBean;
                      //array_push($dataArray, $fieldBean);
                  }

           $ticketModel = new TicketCollection();  
           $ticketModel->WorkflowType=$workflowType;
           $ticketModel->Title = $title;
           $ticketModel->Description = $description;
           $ticketModel->CrudeDescription = $crudeDescription;
           $ticketModel->Fields = $dataArray;
           $ticketModel->ProjectId = (int)$projectId;
           $ticketModel->RelatedStories= [];
           $ticketModel->Tasks= [];
           $ticketNumber = ProjectTicketSequence::getNextSequence($projectId);
           $ticketModel->TicketId = (int)$ticketNumber;
           $ticketModel->TicketIdString = (string)$ticketNumber;
           $ticketModel->TotalEstimate = 0;
           $ticketModel->TotalTimeLog = (float)0.0;          
           $ticketModel->ParentStoryId = "";
           $ticketModel->IsChild=(int)0;
           if($parentTicNumber !=""){
               $ticketModel->ParentStoryId=(int)$parentTicNumber;
               $ticketModel->IsChild=(int)1;
           }
          
                  
                       
          $returnValue = TicketCollection::saveTicketDetails($ticketModel);
          if($returnValue != "failure"){
              $this->followTicket($userId,$ticketNumber,$projectId,$userId,"reportedby",true);
              TicketComments::createCommentsRecord($ticketNumber,$projectId);
              foreach ($refinedData["ArtifactsList"] as &$artifact) {
                    $artifact["UploadedBy"] = (int) $userId;
                }
                TicketArtifacts::createArtifactsRecord($ticketNumber, $projectId, $refinedData["ArtifactsList"]);
                /* Send Notifications to @mentioned user by Ryan and send mail to @mentioned user*/
                          
                if(!empty($refinedData['UsersList']))
                {
                    error_log("===in if mail sending.....==");
                    $newticket_data->ticketId = $ticketNumber;
                    self::saveNotificationsToMentionOnly($newticket_data,$refinedData['UsersList'],'mention');

                }
                           

              /* Notification End By Ryan */
                return $ticketNumber;
          }
         
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
           // $ticketModel = new TicketCollection();
            $ticketDetails = TicketCollection::getAllTicketDetails($StoryData, $projectId,$select=['TicketId', 'Title','Fields','ProjectId']);
            $finalData = array();
            $fieldsOrderArray = [5,6,7,3,10];
           //  $fieldsOrderArray = [10,11,12,3,4,5,6,7,8,9];
            $fitlerOption=null;
              if($StoryData->filterOption !=null || $StoryData->filterOption != 0){
                   //error_log("fitler-------ss----".$StoryData->filterOption);
                   $fitlerOption=$StoryData->filterOption;
              }
          
            foreach ($ticketDetails as $ticket) {
                $details = CommonUtility::prepareDashboardDetails($ticket, $projectId,$fieldsOrderArray,"part",$fitlerOption);
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
    public function getMyTicketsCount($userId,$projectId) {
        try {
            $totalCount = TicketCollection::getMyTicketsCount($userId,$projectId);
            
            return $totalCount;
        } catch (Exception $ex) {
            Yii::log("StoryService:getMyTicketsCount::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
       public function getAllStoriesCount($StoryData,$projectId) {
        try {
            $totalCount = TicketCollection::getAllStoriesCount($StoryData,$projectId);
            
            return $totalCount;
        } catch (Exception $ex) {
            Yii::log("StoryService:getAllStoriesCount::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
            
    /**
     * @author Praveen P
     * @description This method is used to getting subtask Ids for the particular story.
     * @return type subtasks Ids
     */
    public function getSubTaskIds($StoryData, $projectId) {
        try {
           $finalData = TicketCollection::getSubTaskIds($StoryData,$projectId);
            return $finalData;
        } catch (Exception $ex) {
            Yii::log("StoryService:getAllTicketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
    /**
     * @author Praveen P
     * @description This method is used to getting subtask details for the particular story.
     * @return type subtasks
     */
    public function getSubTaskDetails($subTaskIds, $projectId) {
        try {
           $ticketDetails = TicketCollection::getSubTaskDetails($subTaskIds, $projectId,$select=['TicketId', 'Title','Fields','ProjectId']);
            $finalData = array();
            $fieldsOrderArray = [5,6,7,3,10];
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
         * @author Anand Singh
         * @return type
         */
        public function getMyTickets() {
            try {
               $this->sampleMethod();
               $priorityModel = new TicketCollection();
           return $priorityModel->getMyAssignedTickets();
          //return $priorityModel->updateTicketField();
            } catch (Exception $exc) {
                Yii::log("StoryService:getMyTickets::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
            }
        }

    
        /**
 * @author Moin Hussain
 * @param type $ticket_data
 */
       public function updateTicketDetails($ticket_data) {
        try {
             $editticket=$ticket_data;
             $userdata =  $ticket_data->userInfo;
             $projectId =  $ticket_data->projectId;
             $userId = $userdata->Id;
             $collaboratorData = Collaborators::getCollboratorByFieldType("Id",$userId);
            $ticket_data = $ticket_data->data;
            $workFlowDetail = array();
            $ticketCollectionModel = new TicketCollection();
            $ticketDetails = $ticketCollectionModel->getTicketDetails($ticket_data->ticketId, $projectId);
            $ticketDetails["Title"] = trim($ticket_data->title);
            $this->saveActivity($ticket_data->ticketId,$projectId,"Title", $ticketDetails["Title"],$userId);
            $description = $ticket_data->description;
            $ticketDetails["CrudeDescription"] = $description;
            $refiendData = CommonUtility::refineDescription($description);
            $ticketDetails["Description"] = $refiendData["description"];
            $this->saveActivity($ticket_data->ticketId,$projectId,"Description", $description,$userId);
            $newworkflowId = "";
            foreach ($ticketDetails["Fields"] as $key => &$value) {
                 $fieldId =  $value["Id"];
                     if(isset($ticket_data->$key)){
                        $fieldDetails =  StoryFields::getFieldDetails($fieldId);
                        $fieldName =  $fieldDetails["Field_Name"];
                         if(is_numeric($ticket_data->$key)){
                              $value["value"] = (int)$ticket_data->$key; 
                               if($fieldDetails["Type"] == 6){
                                $collaboratorData = Collaborators::getCollboratorByFieldType("Id",$ticket_data->$key);
                                $value["value_name"] = $collaboratorData["UserName"];
                                $this->followTicket($ticket_data->$key,$ticket_data->ticketId,$projectId,$userId,$fieldDetails["Field_Name"],TRUE,"FullUpdate");
                                if (!empty($ticketDetails['Tasks'])){
                                foreach($ticketDetails['Tasks'] as $childticketId){
                                    $this->followTicket($ticket_data->$key,$childticketId['TaskId'],$projectId,$userId,$fieldDetails["Field_Name"]='follower',FALSE,"FullUpdate");
                                }
                                }else{
                                $unfollowId='';
                                $parentTicketDetails=TicketCollection::getTicketDetails($ticketDetails['ParentStoryId'],$projectId);
                                
                                 if($fieldDetails["Field_Name"]=='assignedto'){
                                   if($ticketDetails['Fields'][$key]['value']!='' && $ticketDetails['Fields'][$key]['value']!= $ticket_data->$key ){
                                     $unfollowId=$ticketDetails['Fields'][$key]['value'];
                                    }
                                    $follow_ticket_id='';
                                    switch($ticketDetails['WorkflowType']){
                                        case 3:
                                            $follow_ticket_id=  array_values(array_filter($parentTicketDetails['Tasks'],function($val){
                                             return $val['TaskType']==4; 
                                            }));
                                            break;
                                        case 4:
                                            $follow_ticket_id=array_values(array_filter($parentTicketDetails['Tasks'],function($val){
                                                   return $val['TaskType']==3;
                                            }));
                                            break;
                                    }
                                    if(!empty($follow_ticket_id)){
                                  if(!empty($unfollowId))
                                  $this->unfollowTicket($unfollowId,$follow_ticket_id[0]['TaskId'],$projectId,'follower',0);   
                                  $this->followTicket($ticket_data->$key,$follow_ticket_id[0]['TaskId'],$projectId,$userId,$fieldDetails["Field_Name"]='follower',FALSE);  
                                 }}
                                 $this->followTicket($ticket_data->$key,$ticketDetails['ParentStoryId'],$projectId,$userId,$ticketDetails['TicketId'].'-'.$fieldDetails["Field_Name"],FALSE,"FullUpdate");
                                } 
                                }
                                else if($fieldDetails["Field_Name"] == "workflow"){
                                $workFlowDetail = WorkFlowFields::getWorkFlowDetails($ticket_data->$key);
                                $value["value_name"] = $workFlowDetail["Name"];
                                $newworkflowId = $ticket_data->$key;
                                }
                                else if($fieldDetails["Field_Name"] == "priority"){
                                $priorityDetail = Priority::getPriorityDetails($ticket_data->$key);
                                $value["value_name"] = $priorityDetail["Name"];
                                }
                                else if($fieldDetails["Field_Name"] == "bucket"){
                                $bucketDetail =  Bucket::getBucketName($ticket_data->$key,$projectId);
                                $value["value_name"] = $bucketDetail["Name"];
                                if (!empty($ticketDetails['Tasks'])){
                                   $db =  TicketCollection::getCollection();
                                foreach ($ticketDetails["Tasks"] as $task) {
                                   $db->update(array("ProjectId" => (int) $projectId, "TicketId" => (int) $task['TaskId']), array('$set' => array('Fields.bucket.value' => (int) $bucketDetail['Id'], 'Fields.bucket.value_name' => $bucketDetail['Name'])));
                                }
                                }
                                }
                                else if($fieldDetails["Field_Name"] == "planlevel"){
                                $planlevelDetail = PlanLevel::getPlanLevelDetails($ticket_data->$key);
                                $value["value_name"] = $planlevelDetail["Name"];
                                }
                                else if($fieldDetails["Field_Name"] == "tickettype"){
                                $tickettypeDetail = TicketType::getTicketType($ticket_data->$key);
                                $value["value_name"] = $tickettypeDetail["Name"];
                                }
                                        
                         }else{
                             if($ticket_data->$key != ""){
                                 
                                 if($fieldDetails["Type"] == 4){
                                       $validDate = CommonUtility::validateDate($ticket_data->$key);
                                      if($validDate){
                                     $value["value"] = new \MongoDB\BSON\UTCDateTime(strtotime($validDate) * 1000);
                                    
                                 }
                                
                             }else{
                                 
                                 $value["value"] = $ticket_data->$key; 
                              
                             } 
                             }else{
                                if($fieldDetails["Type"] == 6 ){
                                     $value["value"] = "";
                                     $value["value_name"] = "";
                                    $this->unfollowTicket($ticket_data->$key,$ticket_data->ticketId,$projectId,$fieldDetails["Field_Name"]);
                                   
                                     if($ticketDetails['IsChild'] == 0){
                                    if (!empty($ticketDetails['Tasks'])){
                                        foreach($ticketDetails['Tasks'] as $childticketId){
                                            $this->unfollowTicket($ticket_data->$key,$childticketId['TaskId'],$projectId,$fieldDetails["Field_Name"]='follower');
                                        }
                                    }
                                  
                                }  else{
                                    $fieldName =  $fieldDetails["Field_Name"];
                                    if($fieldName == "assignedto" || $fieldName == "stakeholder" || strpos($fieldName, "assignedto")>0 ||  strpos($fieldName, "stakeholder")>0 ){
                                       $fieldDetails["Field_Name"]= $ticket_data->ticketId."-".$fieldName;  
                                    }else{
                                        $fieldDetails["Field_Name"]='follower';
                                    }
                                    $this->unfollowTicket($ticket_data->$key,$ticketDetails['ParentStoryId'],$projectId,$fieldDetails["Field_Name"]); 
                                
                                    
                                    
                                    
                                }
                                }
                            }
                            
                             
                         }
                        
                        error_log($fieldName."----------activtiyr reuls---------------");
                        $slug =  new \MongoDB\BSON\ObjectID();
                        $activity=$this->saveActivity($ticket_data->ticketId,$projectId,$fieldName, $value["value"],$userId,$slug);
                        if($activity != "noupdate")
                        {
                            
                        $this->saveNotifications($editticket,$fieldName,$ticket_data->$key,$fieldDetails["Type"],$slug);
                        
                        }     
                        
                       }else{
                           if($key == "state"){
                                $value["value"] = (int)$workFlowDetail["StateId"];
                                $value["value_name"] = $workFlowDetail["State"];
                                }
                     }
                   
                
             }
             if($newworkflowId != ""){
                $updateStatus = $this->updateWorkflowAndSendNotification($ticketDetails,$newworkflowId,$userId);

             }
             $newTicketDetails = TicketCollection::getTicketDetails($ticket_data->ticketId,$projectId);
             $ticketDetails["Followers"] = $newTicketDetails["Followers"];
             $collection = Yii::$app->mongodb->getCollection('TicketCollection');
            $collection->save($ticketDetails);
            TicketArtifacts::saveArtifacts($ticket_data->ticketId, $projectId, $refiendData["ArtifactsList"],$userId);
            
        } catch (Exception $ex) {
             error_log($ex->getMessage());
            
            Yii::log("StoryService:updateTicketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
       
      }
    /*@modified by Moin Hussain
    * @author Padmaja
    * @param type $ticket_data
     */
    public function updateStoryFieldInline($ticket_data){
           try{
            $returnValue = 'failure';
            $collection = Yii::$app->mongodb->getCollection('TicketCollection');
            $checkData = $ticket_data->isLeftColumn;
            $field_name = $ticket_data->editedId;
            $field_id = $ticket_data->id;
            $loggedInUser = $ticket_data->userInfo->Id;
            $projectId=$ticket_data->projectId;
            $artifacts = array();
            $workFlowDetail=array();
            $valueName = "";
            $updatedState=array();
            $selectFields = [];
            $selectFields = ['ProjectId','WorkflowType','TicketId','ParentStoryId', 'IsChild','TotalEstimate','Fields.estimatedpoints.value','Fields.workflow.value','Tasks','Fields'];
            $childticketDetails = TicketCollection::getTicketDetails($ticket_data->ticketId,$ticket_data->projectId,$selectFields); 
            $ticketDetails=TicketCollection::getTicketDetails($ticket_data->ticketId,$ticket_data->projectId);//added by Ryan for Email Purpose
            error_log("updateStoryFieldInline---1");
            if($checkData==0){
                 error_log("updateStoryFieldInline---2");
                 if($ticket_data->id=='Title'){
                     $fieldType = "Title";
                      $field_name = "Title";
                    $newData = array('$set' => array("Title" => trim($ticket_data->value)));
                    $condition=array("TicketId" => (int)$ticket_data->ticketId,"ProjectId"=>(int)$ticket_data->projectId);
                    $selectedValue=$ticket_data->value;
                    $activityNewValue = $ticket_data->value;
                }else if($ticket_data->id=='Description'){
                    $field_name = "Description";
                    $fieldType = "Description";
                    $refinedData = CommonUtility::refineDescription($ticket_data->value);
                    $actualdescription = $refinedData["description"];
                    $artifacts=$refinedData["ArtifactsList"];
                    $newData = array('$set' => array("Description" => $actualdescription,"CrudeDescription" =>$ticket_data->value ));
                    $condition=array("TicketId" => (int)$ticket_data->ticketId,"ProjectId"=>(int)$ticket_data->projectId);
                    $selectedValue=$actualdescription;
                    $activityNewValue = $ticket_data->value;
                    
                    /* Send Notifications to @mentioned user by Ryan and send mail to @mentioned user*/
                            try
                            {
                                error_log("Notification with Mention");
                                if(!empty($refinedData['UsersList']))
                                {
                                    error_log("===in if mail sending.....==");
                                   self::saveNotificationsToMentionOnly($ticket_data,$refinedData['UsersList'],'mention');
                                  //  CommonUtility::sendMail($ticket_data->userInfo->username, $refinedData['UsersList'],$ticketDetails);
                                    
                                }
                            } catch (Exception $ex) {
                                Yii::log("NotificationCollection::saveNotifications:" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
                            }
                            
                            /* Send Notification End By Ryan */
                }
                $fieldName = $ticket_data->id;
            }else{
                 error_log("updateStoryFieldInline---3");
                  $fieldDetails =  StoryFields::getFieldDetails($field_id);
                  $fieldName = $fieldDetails["Field_Name"];
                  $fieldType =  $fieldDetails["Type"];
                     if(is_numeric($ticket_data->value)){
                        if($fieldDetails["Type"] == 6 ){
                            $collaboratorData = Collaborators::getCollboratorByFieldType("Id",$ticket_data->value);
                            $valueName = $collaboratorData["UserName"]; 
                          
                            $this->followTicket($ticket_data->value,$ticket_data->ticketId,$ticket_data->projectId,$loggedInUser,$fieldDetails["Field_Name"],true);
                           //  $this->saveNotifications($ticket_data,$fieldDetails["Field_Name"],$ticket_data->value);

                            /*Send Mail to Assigned to User by Ryan*/
                            try
                            {
                                
                            } 
                            catch (Exception $ex) {
                                Yii::log("CommonUtility::sendMail::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
                            }
                            /* Send Mail End By Ryan */
                            
                            /* Send Notifications */
                            error_log("coming in notifications");
                            /* Notifications End */
                            
                            if (!empty($childticketDetails['Tasks'])){error_log("----follow if subTask");
                                foreach($childticketDetails['Tasks'] as $childticketId){
                                    $this->followTicket($ticket_data->value,$childticketId['TaskId'],$ticket_data->projectId,$loggedInUser,$fieldDetails["Field_Name"]='follower',FALSE);
                                }
                            }else{error_log("----follow elseStory");
                                $unfollowId='';
                                $parentTicketDetails=TicketCollection::getTicketDetails($childticketDetails['ParentStoryId'],$ticket_data->projectId);
                                
                                 if($fieldDetails["Field_Name"]=='assignedto'){
                                   if($childticketDetails['Fields'][$fieldDetails["Field_Name"]]['value']!='' && $childticketDetails['Fields'][$fieldDetails["Field_Name"]]['value']!= $ticket_data->value ){
                                     $unfollowId=$childticketDetails['Fields'][$fieldDetails["Field_Name"]]['value'];
                                    }
                                    $follow_ticket_id='';
                                    switch($childticketDetails['WorkflowType']){
                                        case 3:
                                            $follow_ticket_id=  array_values(array_filter($parentTicketDetails['Tasks'],function($val){
                                             return $val['TaskType']==4; 
                                            }));
                                            break;
                                        case 4:
                                            $follow_ticket_id=array_values(array_filter($parentTicketDetails['Tasks'],function($val){
                                                   return $val['TaskType']==3;
                                            }));
                                            break;
                                    }
                                if(!empty($follow_ticket_id))
                                 {
                                  if(!empty($unfollowId))
                                  $this->unfollowTicket($unfollowId,$follow_ticket_id[0]['TaskId'],$ticket_data->projectId,'follower',0);   
                                  $this->followTicket($ticket_data->value,$follow_ticket_id[0]['TaskId'],$ticket_data->projectId,$loggedInUser,$fieldDetails["Field_Name"]='follower',TRUE);  
                                 }
                                 
                                    }
                                 $this->followTicket($ticket_data->value,$childticketDetails['ParentStoryId'],$ticket_data->projectId,$loggedInUser,$childticketDetails['TicketId'].'-'.$fieldDetails["Field_Name"],TRUE);

                            }
                        }
                        
                             else if($fieldDetails["Field_Name"] == "workflow"){
                                error_log("updateStoryFieldInline---5");
                                 $workFlowDetail = WorkFlowFields::getWorkFlowDetails($ticket_data->value);
                                $valueName = $workFlowDetail["Name"];
                                error_log("updateStoryFieldInline---6");
                                }
                                else if($fieldDetails["Field_Name"] == "priority"){
                                $priorityDetail = Priority::getPriorityDetails($ticket_data->value);
                                $valueName = $priorityDetail["Name"];
                                }
                                else if($fieldDetails["Field_Name"] == "bucket"){
                                $bucketDetail =  Bucket::getBucketName($ticket_data->value,(int)$ticket_data->projectId);
                                $valueName = $bucketDetail["Name"];
                                 if (!empty($ticketDetails['Tasks'])){
                                   $db =  TicketCollection::getCollection();
                                foreach ($ticketDetails["Tasks"] as $task) {
                                   $db->update(array("ProjectId" => (int) $projectId, "TicketId" => (int) $task['TaskId']), array('$set' => array('Fields.bucket.value' => (int) $bucketDetail['Id'], 'Fields.bucket.value_name' => $bucketDetail['Name'])));
                                }
                                }
                                }
                                else if($fieldDetails["Field_Name"] == "planlevel"){
                                $planlevelDetail =  PlanLevel::getPlanLevelDetails($ticket_data->value);
                                $valueName = $planlevelDetail["Name"];
                                }
                                else if($fieldDetails["Field_Name"] == "tickettype"){
                                $tickettypeDetail = TicketType::getTicketType($ticket_data->value);
                                $valueName = $tickettypeDetail["Name"];
                                } 
                              else if($fieldDetails["Field_Name"] == "estimatedpoints"){
                                if($childticketDetails['IsChild']==0){
                                    $ticketId= $ticket_data->ticketId;
                                  }else{
                                      $ticketId= $childticketDetails['ParentStoryId'];
                                  }
                                  $updatedEstimatedPts=(int)$ticket_data->value-(int)$childticketDetails['Fields']['estimatedpoints']['value'];
                                  TicketCollection::updateTotalEstimatedPoints($ticket_data->projectId,$ticketId,$updatedEstimatedPts);
                                }
                        error_log("updateStoryFieldInline---7");

                         $leftsideFieldVal = (int)$ticket_data->value;  
                    }else{
                         error_log("updateStoryFieldInline---8");
                        if($ticket_data->value != ""){
                             error_log("updateStoryFieldInline---9");
                            $validDate = CommonUtility::validateDate($ticket_data->value);
                            if($validDate){
                               $ticket_data->value = new \MongoDB\BSON\UTCDateTime(strtotime($validDate) * 1000);
                               $leftsideFieldVal = $ticket_data->value; 
                            }else{
                                //error_log("===Field Name==".$leftsideFieldVal);
                                $leftsideFieldVal = $ticket_data->value; 
                            } 
                        }else{
                             error_log("updateStoryFieldInline---10");
                            $leftsideFieldVal = $ticket_data->value;
                            //error_log("===Ticket Data Value==".$leftsideFieldVal);
                                if($fieldDetails["Type"] == 6 ){
                                    $this->unfollowTicket($ticket_data->value,$ticket_data->ticketId,$ticket_data->projectId,$fieldDetails["Field_Name"]);
                                    if($childticketDetails['IsChild'] == 0){
                                    if (!empty($childticketDetails['Tasks'])){
                                        foreach($childticketDetails['Tasks'] as $childticketId){
                                            $this->unfollowTicket($ticket_data->value,$childticketId['TaskId'],$ticket_data->projectId,$fieldDetails["Field_Name"]='follower');
                                        }
                                    }
                                   
                                } else{
                                   $fieldName =  $fieldDetails["Field_Name"];
                                    if($fieldName == "assignedto" || $fieldName == "stakeholder" || strpos($fieldName, "assignedto")>0 ||  strpos($fieldName, "stakeholder")>0 ){
                                        $fieldNameModified = $ticket_data->ticketId."-".$fieldName;  
                                    }else{
                                        $fieldNameModified ='follower';
                                    }
                                    $this->unfollowTicket($ticket_data->value,$childticketDetails['ParentStoryId'],$ticket_data->projectId,$fieldNameModified); 
                                  }
                                }
                        }
                    }
                     error_log("updateStoryFieldInline---12");
                    $fieldtochange1= "Fields.".$field_name.".value";
                    $fieldtochange2 = "Fields.".$field_name.".value_name";
                    $fieldtochangeId = "Fields.".$field_name.".Id";
                    $newData = array('$set' => array($fieldtochange1 => $leftsideFieldVal,$fieldtochange2 =>$valueName));
                    $condition=array("TicketId" => (int)$ticket_data->ticketId,"ProjectId"=>(int)$ticket_data->projectId,$fieldtochangeId=>(int)$ticket_data->id);
                    $selectedValue=$leftsideFieldVal;
                    $activityNewValue = $leftsideFieldVal;
             error_log("==call check==".$ticket_data->value);       
          if($fieldDetails["Field_Name"] == "estimatedpoints"){
               if($childticketDetails['IsChild'] == 0){
                 $ticketId= $ticket_data->ticketId;
                 $getTicketDetailsForEstimate = TicketCollection::getTicketDetails($ticketId,$ticket_data->projectId,['Fields.totalestimatepoints.value']);
                 error_log($ticket_data->ticketId."@@@@-------------------".print_r($getTicketDetailsForEstimate,1));
                 $totalEstimatePoints = $getTicketDetailsForEstimate["Fields"]["totalestimatepoints"]; 
                 $selectedValue=$totalEstimatePoints;
                 
               }
         }
   
            }
            $slug =  new \MongoDB\BSON\ObjectID();
            $this->saveNotifications($ticket_data,$field_name,$ticket_data->value,$fieldType,$slug);
    error_log("updateStoryFieldInline---13");
                $activityData = $this->saveActivity($ticket_data->ticketId,$ticket_data->projectId,$fieldName,$activityNewValue,$loggedInUser,$slug);
                $updateStaus = $collection->update($condition, $newData);
                if($field_name=='workflow'){
                $updateStatus = $this->updateWorkflowAndSendNotification($childticketDetails,$ticket_data->value,$loggedInUser);
 error_log("updateStoryFieldInline---14");
                $collection->findAndModify( array("ProjectId"=> (int)$ticket_data->projectId ,"TicketId"=>(int)$ticket_data->ticketId),array('$set' => array('Fields.state.value' => (int)$workFlowDetail['StateId'],'Fields.state.value_name' =>$workFlowDetail['State']))); 
                $updatedState['field_name'] ='state';
                $updatedState['state']=$workFlowDetail['State'];
                }
                if(!empty($artifacts)){
                TicketArtifacts::saveArtifacts($ticket_data->ticketId, $ticket_data->projectId, $artifacts,$loggedInUser);
                }
                if($childticketDetails['IsChild']==0){
                  $ticketId= $ticket_data->ticketId;
                }else{
                    $ticketId= $childticketDetails['ParentStoryId'];
                }
            error_log("updateStoryFieldInline---15");
           // if($updateStaus==1){
                $returnValue=$selectedValue;
           // }
                 error_log("updateStoryFieldInline---16");
            $returnValue =  array("updatedFieldData" =>$returnValue,"activityData"=>$activityData,'updatedState'=>$updatedState);
            return $returnValue;

        } catch (Exception $ex) {
              Yii::log("StoryService:updateStoryFieldInline::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }

    }
    
    public function removeComment($commentData){
     $res = TicketComments::removeComment($commentData);
     $notify_type = "delete";
    // $refinedData = CommonUtility::refineDescription($commentData->Comment->CrudeCDescription);
    // $mentionArray = $refinedData['UsersList']; 
     $commentData->Comment->OriginalCommentorId=$commentData->userInfo->Id;
     $this->saveNotificationsForComment($commentData,array(),$notify_type,new \MongoDB\BSON\ObjectID($commentData->Comment->Slug));
     
    }
  
    public function saveComment($commentData){
        try{
            error_log("saveComment-------------1");
            $slug="";
            $refinedData = CommonUtility::refineDescription($commentData->Comment->CrudeCDescription);
            $ticketDetails=TicketCollection::getTicketDetails($commentData->ticketId,$commentData->projectId);//added By Ryan
            
            $mentionArray = $refinedData['UsersList'];               
            $processedDesc = $refinedData["description"];
            $artifacts = $refinedData["ArtifactsList"];
            $commentDesc = $commentData->Comment->CrudeCDescription;
            if (isset($commentData->Comment->Slug)) {
                 error_log("saveComment-------------2");
                $slug=$commentData->Comment->Slug;
                $collection = Yii::$app->mongodb->getCollection('TicketComments');
//}
                $newdata = array('$set' => array("Activities.$.CrudeCDescription" => $commentDesc, "Activities.$.CDescription" => $processedDesc));
                $collection->update(array("TicketId" => (int) $commentData->ticketId, "ProjectId" => (int) $commentData->projectId, "Activities.Slug" => new \MongoDB\BSON\ObjectID($commentData->Comment->Slug)), $newdata);
                $retData = array("CrudeCDescription" => $commentDesc,
                    "CDescription" => $processedDesc);
                if (!empty($artifacts)) {
                    TicketArtifacts::saveArtifacts($commentData->ticketId, $commentData->projectId, $artifacts, $commentData->userInfo->Id);
                }
                 $notify_type = "edit";
                 $commentData->Comment->OriginalCommentorId=$commentData->userInfo->Id;
                $this->saveNotificationsForComment($commentData,$mentionArray,$notify_type,$slug);
                return $retData;
            } else {
 error_log("saveComment-------------3***");
 
                $commentedOn = new \MongoDB\BSON\UTCDateTime(time() * 1000);

                $slug = new \MongoDB\BSON\ObjectID();
                error_log("saveComment-------------444**");
                $commentDataArray = array(
                    "Slug" => $slug,
                    "CDescription" => $processedDesc,
                    "CrudeCDescription" => $commentDesc,
                    "ActivityOn" => $commentedOn,
                    "ActivityBy" => (int) $commentData->userInfo->Id,
                    "Status" => ($commentData->Comment->ParentIndex == "") ? (int) 1 : (int) 2,
                    "PropertyChanges" => [],
                    "PoppedFromChild" => "",
                    "ParentIndex" => ($commentData->Comment->ParentIndex == "") ? "" : (int) $commentData->Comment->ParentIndex,
                    "repliesCount" => (int) 0
                );
                $db = TicketComments::getCollection();
                $v = $db->update(array("ProjectId" => (int) $commentData->projectId, "TicketId" => (int) $commentData->ticketId), array("RecentActivitySlug" => $slug, "RecentActivityUser" => (int) $commentData->userInfo->Id, "Activity" => "Comment"));
                TicketComments::saveComment($commentData->ticketId, $commentData->projectId, $commentDataArray);
                if (!empty($artifacts)) {
                    TicketArtifacts::saveArtifacts($commentData->ticketId, $commentData->projectId, $artifacts, $commentData->userInfo->Id);
                }
                $tinyUserModel = new TinyUserCollection();
                $userProfile = $tinyUserModel->getMiniUserDetails($commentDataArray["ActivityBy"]);
                $commentDataArray["ActivityBy"] = $userProfile;
                $datetime = $commentDataArray["ActivityOn"]->toDateTime();
                $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                $readableDate = $datetime->format('M-d-Y H:i:s');
                $commentDataArray["ActivityOn"] = $readableDate;
                
                /* Send Notifications to @mentioned user by Ryan and send email in the comment section*/

                            try
                            {
                                
                                $link=Yii::$app->params['AppURL']."/#/story-detail/".$ticketDetails['TicketId']."?Slug=".$slug;
                                $mentionArray = $refinedData['UsersList'];
                                   // if($commentData->Comment->Reply==false)
                                   // {
                                       $notify_type = "comment";
                                       $actionName = "commented";
                                   // }
//                                    else
//                                    {
//                                       $notify_type = "reply";
//                                        $actionName = "replied";
//                                    }
                               $this->saveNotificationsForComment($commentData,$mentionArray,$notify_type,$slug);

                            } catch (Exception $ex) {
                                Yii::log("$this->saveNotifications:" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
                            }

                            
                            /* Send Notification End By Ryan */
 error_log("saveComment-------------4");
                return $commentDataArray;
            }
        }catch(Exception $ex){
        Yii::log("StoryService:saveComment::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
    }
        
        
    }


       /**
  * @author Moin Hussain
  * @param type $collaboratorId
  * @param type $ticketId
  * @param type $projectId
  * @param type $loggedInUser
  * @param type $fieldName
  * @param type $defaultFollower
  */
     public function followTicket($collaboratorId,$ticketId,$projectId,$loggedInUser,$fieldName,$defaultFollower=FALSE,$from=""){
        
        try {
            $db =  TicketCollection::getCollection();
           $currentDate = new \MongoDB\BSON\UTCDateTime(time() * 1000);
           
            if($fieldName == "assignedto" || $fieldName == "stakeholder" || strpos($fieldName, "assignedto")>0 ||  strpos($fieldName, "stakeholder")>0 ){
                $cursor =  $db->count( array("ProjectId"=> (int)$projectId ,"TicketId"=> (int)$ticketId, "Followers" => array('$elemMatch'=> array("Flag" =>$fieldName)))); 
           }else{
              $cursor =  $db->count( array("ProjectId"=> (int)$projectId ,"TicketId"=> (int)$ticketId, "Followers" => array('$elemMatch'=> array( "FollowerId"=> (int)$collaboratorId ,"Flag" =>$fieldName))));  
           }
            if($cursor == 0){
               $db->findAndModify( array("ProjectId"=> (int)$projectId ,"TicketId"=> (int)$ticketId), array('$addToSet'=> array('Followers' =>array("FollowerId" => (int)$collaboratorId,"FollowedOn" =>$currentDate,"CreatedBy"=>(int)$loggedInUser,"Flag"=>$fieldName,"DefaultFollower"=>(int)$defaultFollower ))),array('new' => 1,"upsert"=>1));
             
             }else{
                 if($fieldName == "assignedto" || $fieldName == "stakeholder" || strpos($fieldName, "assignedto")>0 ||  strpos($fieldName, "stakeholder")>0 ){
                  $newdata = array('$set' => array("Followers.$.FollowerId" => (int)$collaboratorId,"Followers.$.FollowedOn" => $currentDate,"Followers.$.CreatedBy" => (int)$loggedInUser));
                  $db->update(array("ProjectId"=> (int)$projectId ,"TicketId"=> (int)$ticketId,"Followers.Flag"=>$fieldName), $newdata); 
                 }
                 
             } 
          
        } catch (Exception $ex) {
          Yii::log("TicketFollowers:followTicket::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    /**
    * @author Praveen P
    * @param type $collaboratorId
    * @param type $ticketId
    * @param type $projectId
    */
    public function unfollowTicket($collaboratorId,$ticketId,$projectId,$fieldType = "",$is_default=1){
        try {
           $db =  TicketCollection::getCollection();
           if($fieldType!=""){
               if($is_default==0){
                 $db->update( array("ProjectId"=> (int)$projectId ,"TicketId"=> (int)$ticketId), array('$pull'=> array('Followers' =>array("Flag" => $fieldType,"DefaultFollower"=>(int)$is_default),"multi"=>FALSE)));    
               }else{
             $db->update( array("ProjectId"=> (int)$projectId ,"TicketId"=> (int)$ticketId), array('$pull'=> array('Followers' =>array("Flag" => $fieldType))));  
               }     
           }else{
            $db->update( array("ProjectId"=> (int)$projectId ,"TicketId"=> (int)$ticketId), array('$pull'=> array('Followers' =>array("FollowerId" => (int)$collaboratorId))));   
           }
           
        } catch (Exception $ex) {
          Yii::log("TicketFollowers:followTicket::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    } 
    /**
     * @author Moin Hussain
     * @param type $userId
     * @param type $sortorder
     * @param type $sortvalue
     * @param type $offset
     * @param type $pageLength
     * @param type $projectId
     * @return array
     */
     public function getAllMyTickets($userId,$sortorder,$sortvalue,$offset,$pageLength,$projectId) {
        try {
           // $ticketModel = new TicketCollection();
            $ticketDetails = TicketCollection::getMyTickets($userId,$sortorder,$sortvalue,$offset,$pageLength,$projectId,$select=['TicketId', 'Title','Fields','ProjectId']);
            $finalData = array();
            $fieldsOrderArray = [5,6,7,3,9,10];
           //  $fieldsOrderArray = [10,11,12,3,4,5,6,7,8,9];
            foreach ($ticketDetails as $ticket) {
                $details = CommonUtility::prepareDashboardDetails($ticket, $projectId,$fieldsOrderArray);
                unset($details['project_name']);
                array_push($finalData, $details);
            }
            return $finalData;
        } catch (Exception $ex) {
            Yii::log("StoryService:getAllMyTickets::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
/**
 * @author Moin Hussain
 * @param type $ticketId
 * @param type $projectId
 */
    public function getTicketActivity($ticketId, $projectId){
        try{
           $ticketActivity = TicketComments::getTicketActivity($ticketId, $projectId);
         
           if(isset($ticketActivity["Activities"])){
           foreach ($ticketActivity["Activities"] as &$value) {
                 CommonUtility::prepareActivity($value,$projectId);
           }
           }
           return $ticketActivity;
            
        } catch (Exception $ex) {
    Yii::log("StoryService:getTicketActivity::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    /**
     * @author Moin Hussain
     * @param type $ticketId
     * @param type $projectId
     * @param type $actionfieldName
     * @param type $newValue
     * @param type $activityUserId
     */
    public function saveActivity($ticketId,$projectId,$actionfieldName,$newValue,$activityUserId,$slug=""){
        $oldValue = "";
         $action = "";
         $returnValue="noupdate";
         if(empty($slug))
         $slug =  new \MongoDB\BSON\ObjectID();
        $ticketDetails = TicketCollection::getTicketDetails($ticketId,$projectId);  
        if($actionfieldName == "Title" || $actionfieldName == "Description" || $actionfieldName=="TotalTimeLog"){ //added actionFieldName for TotalTimeLog By Ryan
            $oldValue = $ticketDetails[$actionfieldName]; 
        }else if($actionfieldName=='Followed' || $actionfieldName=='Unfollowed' || $actionfieldName=='Related' || $actionfieldName=='ChildTask' || $actionfieldName=='Unrelated'){
          $oldValue = "";
          switch($actionfieldName){
              case 'Followed':$action="added to";break;
              case 'Unfollowed':$action="removed from";break;
              case 'Related':$action="has related";break;
              case 'ChildTask':$action="created";break; 
              case 'Unrelated':$action="has unrelated";break; 
          }
          
        }else{
           $oldValue = $ticketDetails["Fields"][$actionfieldName]["value"];  
        }
       if($action!="" || trim($oldValue) != trim($newValue)){
           if($action == ""){
                if($oldValue == ""){
                      $action = "set to";
                }else{
                      $action = "changed from"; 
                 }
           }
       
           $db =  TicketComments::getCollection();
           $currentDate = new \MongoDB\BSON\UTCDateTime(time() * 1000);
          $record = $db->findOne( array("ProjectId"=> (int)$projectId ,"TicketId"=> (int)$ticketId));
         //  $record = iterator_to_array($record);
         //  error_log(print_r($record,1));
         //$slug =  new \MongoDB\BSON\ObjectID();
         if($record["RecentActivityUser"] != $activityUserId || $record["Activity"] == "Comment" || $record["Activity"] == "PoppedFromChild" ){
            // $dataArray = array();
             $commentDataArray=array(
            "Slug"=>$slug,
            "CDescription"=>  "",
            "CrudeCDescription"=>"",
            "ActivityOn"=>$currentDate,
            "ActivityBy"=>(int)$activityUserId,
            "Status"=>(int)1,
            "PropertyChanges"=>array(array("Slug"=>$slug,"ActionFieldName" => $actionfieldName,"Action" => $action ,"PreviousValue" =>$oldValue,"NewValue"=>$newValue,"CreatedOn" => $currentDate)),
            "ParentIndex"=>"",
            "PoppedFromChild"=>""
            
        );
            $v = $db->findAndModify( array("ProjectId"=> (int)$projectId ,"TicketId"=> (int)$ticketId), array('$addToSet'=> array('Activities' =>$commentDataArray)),array('new' => 1,"upsert"=>1));  
            $v = $db->update( array("ProjectId"=> (int)$projectId ,"TicketId"=> (int)$ticketId), array("RecentActivitySlug"=>$slug,"RecentActivityUser"=>(int)$activityUserId,"Activity"=>"PropertyChange"));  
            CommonUtility::prepareActivity($commentDataArray,$projectId);
            $returnValue = array("referenceKey"=>-1,"data"=>$commentDataArray);
            
         }else{
             $recentSlug = $record["RecentActivitySlug"];
             $property = array("Slug"=>$slug,"ActionFieldName" => $actionfieldName,"Action" => $action ,"PreviousValue" =>$oldValue,"NewValue"=>$newValue,"CreatedOn" => $currentDate );

             $v = $db->findAndModify( array("ProjectId"=> (int)$projectId ,"TicketId"=> (int)$ticketId,"Activities.Slug"=>$recentSlug), array('$addToSet'=> array('Activities.$.PropertyChanges' =>$property)),array('new' => 1,"upsert"=>1));
            
             $activitiesCount = count($v["Activities"]);
             if($activitiesCount>0){
                 $activitiesCount = $activitiesCount-1;
             }
             CommonUtility::prepareActivityProperty($property,$projectId);
             $returnValue = array("referenceKey"=>$activitiesCount,"data"=>$property);
         }
           if($ticketDetails["IsChild"] == 1 && $actionfieldName == "workflow"){
            //    $slug =  new \MongoDB\BSON\ObjectID();
            $commentDataArray=array(
            "Slug"=>$slug,
            "CDescription"=>  "",
            "CrudeCDescription"=>"",
            "ActivityOn"=>$currentDate,
            "ActivityBy"=>(int)$activityUserId,
            "Status"=>(int)1,
            "PropertyChanges"=>array(array("Slug"=>$slug,"ActionFieldName" => $actionfieldName,"Action" => $action ,"PreviousValue" =>$oldValue,"NewValue"=>$newValue,"CreatedOn" => $currentDate)),
            "ParentIndex"=>"",
            "PoppedFromChild" => (int)$ticketId
            
        );   
           $parentStoryId =  $ticketDetails["ParentStoryId"];
            $v = $db->findAndModify( array("ProjectId"=> (int)$projectId ,"TicketId"=> (int)$parentStoryId), array('$addToSet'=> array('Activities' =>$commentDataArray)),array('new' => 1,"upsert"=>1));   
         $v = $db->update( array("ProjectId"=> (int)$projectId ,"TicketId"=> (int)$parentStoryId), array("RecentActivitySlug"=>$slug,"RecentActivityUser"=>(int)$activityUserId,"Activity"=>"PoppedFromChild"));  
            
           }
       
    }
     return $returnValue;
          //error_log("response-------".$v);
    }

      
    /**
     * @author Suryaprakash
     * @param type $parentTicNumber
     * @param type $childTicketIds
     * @return empty
     */
    public function updateParentTicketTaskField($projectId,$parentTicNumber, $childTicketObjArray) {
        try {
            $ticketDetails = TicketCollection::updateParentTicketTaskField($projectId,$parentTicNumber, $childTicketObjArray);
        } catch (Exception $ex) {
            Yii::log("StoryService:updateParentticketTask::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

    
    /**
     * @author Ryan
     * @param type $ticketId
     * @param type $projectId
     * @return empty
     */
    public function getTicketFollowers($ticketId,$projectId)
    {
        try{
            //get all followers of a ticket....
            $ticketobj=  TicketCollection::getTicketDetails($ticketId,$projectId);
            $followers=array();
            foreach ($ticketobj["Followers"] as &$value) {
                array_push($followers,$value["FollowerId"]);
           }
           return $followers;
        } catch (Exception $ex) {
            Yii::log("StoryService:getTicketFollowers::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
    /**
     * @author Ryan
     * @param type $follower
     * @return empty
     */
    public function getFollower($follower)
    {
        try{
            $follower=TinyUserCollection::getProfileOfFollower($follower);
            return $follower;
        } catch (Exception $ex) {
        }
    }    
        /*
     * @author Padmaja
     * @description This method is used to save child task details.
    * @return type 
         * Modified by Anand 
         * Modification Update Task object before saving to the collection baesd on task type.
     */
    public function createChildTask($postData)
            {
    
        try{
            $returnStatus="failure";
            $ticketCollectionModel = new TicketCollection();
           $loggedInUserId =  $postData->userInfo->Id;
           $ticketDetails = $ticketCollectionModel->getTicketDetails($postData->ticketId, $postData->projectId);
             // $ticketDetails = $ticketCollectionModel->getTicketDetails1($postData->TicketId, $postData->projectId);
            $storyField = new StoryFields();
            $standardFields = $storyField->getStoryFieldList();
            $description = "<p>Please provide description here</p>";
            $newFollowersArray = array();
            foreach ($standardFields as $field) {
                     $fieldBean = new FieldBean();
                     $fieldId =  $field["Id"];
                     $fieldType =  $field["Type"];
                     $fieldTitle =  $field["Title"];
                      $fieldName =  $field["Field_Name"];
                     $fieldBean->Id = (int)$field["Id"];
                     $fieldBean->title = $fieldTitle;
                    if($fieldName == "bucket"){
                        $fieldBean->value = (int)$ticketDetails['Fields']['bucket']['value'];
                        $fieldBean->value_name = $ticketDetails['Fields']['bucket']['value_name'];
                     }
                    else if($fieldName == "reportedby"){
                         $fieldBean->value= (int)$loggedInUserId; 
                          $collaboratorData = TinyUserCollection::getMiniUserDetails($loggedInUserId);
                         $fieldBean->value_name= $collaboratorData["UserName"]; 
                     }
                     else if($fieldName == "tickettype"){
                          $fieldBean->value= (int)1; 
                           $tickettypeDetail = TicketType::getTicketType($fieldBean->value);
                            $fieldBean->value_name = $tickettypeDetail["Name"];// New
                     }
                     else if($fieldName == "workflow"){
                         $fieldBean->value= (int)1; 
                         $workFlowDetail = WorkFlowFields::getWorkFlowDetails($fieldBean->value);
                          $fieldBean->value_name= $workFlowDetail["Name"];  // New
                     }
                     else if($fieldName == "state"){
                         $fieldBean->value= 'New'; 
                          $fieldBean->value_name= 'New';  // New
                     }
                     else if($fieldName == "planlevel"){
                       
                        $fieldBean->value = (int)2;
                        $details =  PlanLevel::getPlanLevelDetails($fieldBean->value );
                        $fieldBean->value_name = $details["Name"]; // Task
                     }
                    else if($fieldName == "priority"){
                        $fieldBean->value =(int)$ticketDetails['Fields']['priority']['value'];
                        $fieldBean->value_name =$ticketDetails['Fields']['priority']['value_name'];
                     }
                     else if($fieldType == 4 || $fieldType == 5){
                         if($fieldName == "duedate"){
                              $fieldBean->value= "";
                         }else{
                            $fieldBean->value= new \MongoDB\BSON\UTCDateTime(time() * 1000);   
                         }
                            $fieldBean->value_name= $fieldBean->value; 
                         }  
                         
                     else{
                          $fieldBean->value=""; 
                     }
                     $dataArray[$fieldName]= $fieldBean;
                     if($fieldType == 6){
                        $collaboratorId = $ticketDetails["Fields"][$fieldName]["value"];
                        if($collaboratorId != ""){
                            $currentDate = new \MongoDB\BSON\UTCDateTime(time() * 1000);
                            $follower =  array("FollowerId" => (int)$collaboratorId,"FollowedOn" =>$currentDate,"CreatedBy"=>(int)$loggedInUserId,"Flag"=>$fieldName,"DefaultFollower"=>(int)1 );
                            array_push($newFollowersArray, $follower);
                        }
                      
                         
                     }
                   }
           $ticketModel = new TicketCollection();
           $ticketModel->WorkflowType = (int)1;
           $ticketModel->Title = trim($postData->title);
           $ticketModel->Description = trim($description);
           $ticketModel->CrudeDescription = trim($description);
           $ticketModel->Fields = $dataArray;
           $ticketModel->ProjectId = (int)$postData->projectId;
           $ticketModel->RelatedStories= [];
           $ticketModel->Tasks= [];
           $ticketNumber = ProjectTicketSequence::getNextSequence($postData->projectId);
           $ticketModel->TicketId = (int)$ticketNumber;
           $ticketModel->TotalEstimate = 0;
           $ticketModel->TotalTimeLog = 0;
           $ticketModel->ParentStoryId = (int)$postData->ticketId;
           $ticketModel->IsChild = 1;
          
           $returnValue = TicketCollection::saveTicketDetails($ticketModel);
           if($returnValue != "failure"){
               $lastChiledTicket['TaskId']=  $ticketModel->TicketId;
               $lastChiledTicket['TaskType']=  (int)1;
               $parentTasks = $ticketDetails['Tasks'];
               array_push($parentTasks,$lastChiledTicket);
               TicketCollection::updateChildTaskObject($postData->ticketId,$postData->projectId,$parentTasks);
               TicketComments::createCommentsRecord($ticketNumber,$postData->projectId);
               error_log("--newFollowersArray--".count($newFollowersArray));
               if(!empty($newFollowersArray)){
                  $this->updateFollowersForSubTask($ticketNumber,$postData->projectId,$newFollowersArray);
               }
               $selectFields = [];
               $selectFields = ['Title', 'TicketId','Fields.priority','Fields.assignedto','Fields.assignedto','Fields.workflow'];
               $subTicketDetails = $ticketCollectionModel->getTicketDetails($ticketNumber,$postData->projectId,$selectFields);
               //$returnStatus=$subTicketDetails;
               /* Notifications */
               $notifyType="Create Task";
               $slug =  new \MongoDB\BSON\ObjectID();
               $this->saveNotifications($postData, $notifyType,'','',$slug,$ticketModel->TicketId);
               $activityData= $this->saveActivity($postData->ticketId,$postData->projectId,"ChildTask", $ticketNumber,$postData->userInfo->Id,$slug);
               $returnStatus=array('Tasks'=>$subTicketDetails,'activityData'=>$activityData);
               /* end Notifications */
            }
             return $returnStatus;
         } catch (Exception $ex) {
              Yii::log("StoryService:createChildTask::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

    
    
       /**
     * @author Padmaaja 
     * @return array
     * @updated by suryaprakash
     */
    public function getAllStoryDetailsForSearch($projectId, $ticketId, $sortvalue, $searchString) {
        try {
           $ParentTicketInfo = TicketCollection::getTicketDetails($ticketId,$projectId,array("Tasks","RelatedStories") );
            
            $ticketArray = $ParentTicketInfo["Tasks"];
            array_push($ticketArray, (int)$ticketId);
            if (!empty($ParentTicketInfo["RelatedStories"])) {
                for ($i = 0; $i < sizeof($ParentTicketInfo["RelatedStories"]); $i++) {
                     array_push($ticketArray,(int)$ParentTicketInfo["RelatedStories"][$i] );
                }
            }
            $finalData = array();
            $ticketDetails = TicketCollection::getAllTicketDetailsForSearch($projectId, $ticketId, $sortvalue, $searchString,$ticketArray);
            foreach ($ticketDetails as $ticket) {
                array_push($finalData, $ticket);
            }
            return $finalData;
        } catch (Exception $ex) {
            Yii::log("StoryService:getAllStoryDetailsForSearch::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

      /**
     * @author suryaprakash reddy 
     * @return array
     */
    public function updateRelatedTaskId($projectId,$ticketId,$searchTicketId,$loginUserId=''){
        try{
            $returnStatus="failure";
            TicketCollection::updateRelateTicket($projectId,$ticketId,$searchTicketId); 
        } catch (Exception $ex) {
            Yii::log("StoryService:updateRelatedTaskId::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
      /**
     * @author suryaprakash reddy 
     * @description This method is used to insertTimelog
     * @return type array
     */
    public function insertTimeLog($timelog_data) {
        try {
            
            $addTimelogTime = date("Y-m-d H:i:s", strtotime($timelog_data->addTimelogTime));
            $projectId = $timelog_data->projectId;
            $ticketId = $timelog_data->ticketId;
            $userId = $timelog_data->userInfo->Id;
            $totalWorkHours = (float) $timelog_data->workHours;
            $description = $timelog_data->addTimelogDesc;
            $LoggedOn = $addTimelogTime;
            $ticketDetails = TicketTimeLog::saveTimeLogData($projectId, $ticketId, $userId, $totalWorkHours,$description,$LoggedOn);
            $recipient_list=array();//added By Ryan
            $action='';//added By Ryan
            if ($ticketDetails != "failure") {
            $parenTicketInfo = TicketCollection::getTicketDetails($ticketId,$projectId,array("ParentStoryId","TotalTimeLog") ); //added by Ryan
            $oldTimeLog=$parenTicketInfo['TotalTimeLog']; //added by Ryan
            $total=($oldTimeLog + $totalWorkHours); //added by Ryan
            $slug =  new \MongoDB\BSON\ObjectID();
            $activityData=$this->saveActivity($ticketId, $projectId,'TotalTimeLog', $total, $userId,$slug); //added by Ryan
            $this->saveNotifications($timelog_data, 'TotalTimeLog', $total,'TotalTimeLog',$slug); //added by Ryan
            if ($parenTicketInfo["ParentStoryId"] != "") {
                $updateParentTotalTime = TicketCollection::updateTotalTimeLog($projectId, $parenTicketInfo["ParentStoryId"], $totalWorkHours);
            }
           
                $updateindivisualTotalTimeLog = TicketCollection::updateTotalTimeLog($projectId, $ticketId, $totalWorkHours);
                $ticketInfo=TicketCollection::getTicketDetails($ticketId,$projectId,array("Followers","Title","TotalTimeLog")); //added by Ryan
                $newTimeLog=$ticketInfo['TotalTimeLog']; //added by Ryan
                $oldTimeLog==0?$action='set to '.$newTimeLog : $action='changed from '. $oldTimeLog. 'to '. $newTimeLog; //added by Ryan
                foreach($ticketInfo['Followers'] as $follower) //added by Ryan
                {
                    $collaborator=TinyUserCollection::getMiniUserDetails($follower['FollowerId']);
                    array_push($recipient_list,$collaborator['Email']);
                }
                return $activityData;
            }
        } catch (Exception $ex) {
            Yii::log("StoryService:insertTimeLog::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
}
    }

    /**
     * @author suryaprakash reddy 
     * @description This method is used to getTimeLog
     * @return type array
     */
    public function getTimeLog($projectId, $parentTicketId) {
        try {
            $ticketDetails = TicketCollection::getTicketDetails($parentTicketId,$projectId,array("TicketId", "TotalTimeLog", "Tasks", "ParentStoryId","RelatedStories") );
            $taskArray = array();
            array_push($taskArray, (int) $parentTicketId);
            if (!empty($ticketDetails["Tasks"])) {
                foreach ($ticketDetails["Tasks"] as $task){
                   array_push($taskArray, (int) $task["TaskId"]); 
                }
             //   $taskArray = $ticketDetails["Tasks"];
                array_push($taskArray, (int) $parentTicketId);
            }
            $ticketTimeLog = TicketTimeLog::getTimeLogRecords($projectId, $taskArray);

            foreach ($ticketTimeLog as &$log) {
                $collaboratorData = TinyUserCollection::getMiniUserDetails($log["_id"]);
                 $log["sum"] =  number_format(round($log["sum"],2),2);                 
                $log["readable_value"] = $collaboratorData;
            }
            $ticketDetails["individualLog"] = $ticketTimeLog;
              $ticketDetails["TotalTimeLog"] = number_format(round($ticketDetails["TotalTimeLog"],2), 2);             
            return $ticketDetails;
        } catch (Exception $ex) {
            Yii::log("StoryService:getTimeLog::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

     /**
     * @author Jagadish 
     * @return type array
     */
    public function getTicketAttachments($ticketId,$projectId){
        try {
            $artifacts = TicketArtifacts::getTicketArtifacts($ticketId, $projectId);
            return $artifacts;
        } catch (Exception $ex) {
            Yii::log("StoryService:getTicketAttachments::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

    /**
     * @author suryaprakash reddy 
     * @return type array
     */
    public function getAllRelateStory($projectId, $ticketId) {
        try {
            // $ticketModel = new TicketCollection();
             $ParentTicketInfo = TicketCollection::getTicketDetails($ticketId,$projectId,array("TicketId","RelatedStories") );
            $finalData = array();
            $ticketArray = $ParentTicketInfo["RelatedStories"];
            $ticketDetails = TicketCollection::getAllRelateStory($projectId, $ticketId, $ticketArray);
            foreach ($ticketDetails as $ticket) {
                array_push($finalData, $ticket);
            }
            return $finalData;
        } catch (Exception $ex) {
            Yii::log("StoryService:getAllRelateStory::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

    /**
     * @author suryaprakash reddy 
     * @return type array
     */
    public function unRelateTask($projectId, $parentTicketId, $unRelateTicketId,$loginUserId='') {
        try {
            $slug =  new \MongoDB\BSON\ObjectID();
            $activityData = $this->saveActivity($parentTicketId, $projectId, 'Unrelated', $unRelateTicketId, $loginUserId,$slug);
            $unRelateChild = TicketCollection::unRelateTask($projectId, $parentTicketId, $unRelateTicketId);
        return $activityData;
        } catch (Exception $ex) {
            Yii::log("StoryService:unRelateTask::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
     /**
     * @author Padmaja
      * @description updatung follower list for subTask
     * @return type 
     */
       public function updateFollowersForSubTask($ticketId,$projectId,$followerArray){
        try{
            $db =  TicketCollection::getCollection();
            $db->findAndModify( array("ProjectId"=> (int)$projectId ,"TicketId"=> (int)$ticketId), array('$push'=> array('Followers' =>array('$each'=>$followerArray))),array('new' => 1,"upsert"=>1)); 
           
        } catch (Exception $ex) {
            Yii::log("StoryService:updateFollowersForSubTask::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
          
    }
  /**
   * @author Anand
   * @description Get default task types Ex:- UI,QA,Peer
   * @return type
   */
    public function getTaskTypes(){
        try {
           $taskTypes = TaskTypes::getTaskTypes();
           return $taskTypes;
        } catch (Exception $ex) {
        Yii::log("StoryService:getTaskTypes::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
           }   
    }
    
    /**
     * @author Anand 
     * @description This method is responsible to update ticket status and send the notification to appropriate user.
     * @param type $oldTicketObj
     * @param type $newWorkflowId
     * @return boolean
     */   
    public function updateWorkflowAndSendNotification($oldTicketObj, $newWorkflowId,$loggedInUser) {

        try {
            $collection = TicketCollection::getCollection();
            $projectId = $oldTicketObj['ProjectId'];
            $ticket_array=array('TicketId'=>$oldTicketObj['TicketId'],'projectId'=>$projectId,'userInfo'=>array('Id'=>$loggedInUser));//added by Ryan
            $ticket_data=json_decode(json_encode($ticket_array,1));//added by Ryan
            if ($oldTicketObj['WorkflowType'] == 1) { // 1-Story,General Task,
                if ($newWorkflowId == 5) {
                    // 5 --Code complete
                    // Send notification to Peer person

                    //Send notification end.....
                    return true;
                } else if ($newWorkflowId == 10 || $newWorkflowId == 7 || $newWorkflowId == 1 || $newWorkflowId == 8) {  // 10 --Re-open  7 -- Invalid 1--New 8--Fixed
                    if (sizeof($oldTicketObj['Tasks']) != 0 || $oldTicketObj['Tasks'] != NULL) {
                        error_log("updateWorkflowAndSendNotification-----------------2");
                        $workFlowDetail = WorkFlowFields::getWorkFlowDetails($newWorkflowId);
                        foreach ($oldTicketObj['Tasks'] as $task) {
                            //  error_log("Task type------------".$task['TaskType']);
                            $taskDetails = TicketCollection::getTicketDetails($task['TaskId'], $projectId);
                            $task_array=array('ticketId'=>$taskDetails['TicketId'],'projectId'=>$projectId,'userInfo'=>array('Id'=>$loggedInUser));
                            $ticket_data=json_decode(json_encode($task_array,1));
                            switch($newWorkflowId)
                            {
                            case 1 :
                            case 7 :   // 1-New,7 -- Invalid
                                // send notification to assigned to person
                            $this->saveNotifications($ticket_data,'workflow',$newWorkflowId);
                            $collection->update(array("ProjectId" => (int) $oldTicketObj['ProjectId'], "TicketId" => (int) $task['TaskId']), array('$set' => array('Fields.workflow.value' => (int) $workFlowDetail['Id'], 'Fields.workflow.value_name' => $workFlowDetail['Name'], 'Fields.state.value' => (int) $workFlowDetail['StateId'], 'Fields.state.value_name' => $workFlowDetail['State'])));
                            //$this->saveNotifications($ticket_data,'workflow',$newWorkflowId);
                            break;

                            case 8 : // 8-Fixed
                            $this->saveNotifications($ticket_data,'workflow',$newWorkflowId);
                            $fixedworkflowDetail = WorkFlowFields::getWorkFlowDetails(8);
                            $workFlowDetail = WorkFlowFields::getWorkFlowDetails(14); // Get closed status details
                            if ($taskDetails['WorkflowType'] == 1) {
                                
                                $collection->update(array("ProjectId" => (int) $oldTicketObj['ProjectId'], "TicketId" => (int) $task['TaskId']), array('$set' => array('Fields.workflow.value' => (int) $fixedworkflowDetail['Id'], 'Fields.workflow.value_name' => $fixedworkflowDetail['Name'], 'Fields.state.value' => (int) $fixedworkflowDetail['StateId'], 'Fields.state.value_name' => $fixedworkflowDetail['State'])));
                              //  $this->saveNotifications($ticket_data,'workflow',$newWorkflowId);
                                
                            } else {
                                $collection->update(array("ProjectId" => (int) $oldTicketObj['ProjectId'], "TicketId" => (int) $task['TaskId']), array('$set' => array('Fields.workflow.value' => (int) $workFlowDetail['Id'], 'Fields.workflow.value_name' => $workFlowDetail['Name'], 'Fields.state.value' => (int) $workFlowDetail['StateId'], 'Fields.state.value_name' => $workFlowDetail['State'])));
                               // $this->saveNotifications($ticket_data,'workflow',14);
                            }
                            break;

                            case 10 : // 10 -Reopen
                                
                            if ($taskDetails['Fields']['workflow']['value'] == 7) {
                                $this->saveNotifications($ticket_data,'workflow',$newWorkflowId);
                                $collection->update(array("ProjectId" => (int) $oldTicketObj['ProjectId'], "TicketId" => (int) $task['TaskId']), array('$set' => array('Fields.workflow.value' => (int) $workFlowDetail['Id'], 'Fields.workflow.value_name' => $workFlowDetail['Name'], 'Fields.state.value' => (int) $workFlowDetail['StateId'], 'Fields.state.value_name' => $workFlowDetail['State'])));
                            // send notification to all child ticket
                                
                            } else if ($taskDetails['Fields']['state']['value'] == 6 && ($taskDetails['WorkflowType'] == 3 || $taskDetails['WorkflowType'] == 4)) {
                                  // send notification to Peer and QA
                                $this->saveNotifications($ticket_data,'workflow',$newWorkflowId);
                                $collection->update(array("ProjectId" => (int) $oldTicketObj['ProjectId'], "TicketId" => (int) $task['TaskId']), array('$set' => array('Fields.workflow.value' => (int) $workFlowDetail['Id'], 'Fields.workflow.value_name' => $workFlowDetail['Name'], 'Fields.state.value' => (int) $workFlowDetail['StateId'], 'Fields.state.value_name' => $workFlowDetail['State'])));
                                    //send notification to Peer  
                            }
                            
                            break;
                        }

                     }
                    } error_log("Send notification to AssignedTo person and Peer person");
                    return true;
                } else {
                    return true;
                }
            } else if ($oldTicketObj['WorkflowType'] == 2 && $newWorkflowId == 5) { // 2-UI
                // Developer should get notification 
              //  $this->saveNotifications($ticket_data,'workflow',$newWorkflowId);
                error_log("Developer should get notification about UI completion");
            } else if ($oldTicketObj['WorkflowType'] == 3) { // 3- Peer
                
                $parentTicketDetails = TicketCollection::getTicketDetails($oldTicketObj['ParentStoryId'], $projectId); 
                $ticket_array=array('TicketId'=>$parentTicketDetails['TicketId'],'projectId'=>$projectId,'userInfo'=>array('Id'=>$loggedInUser));//added by Ryan
                $ticket_data=json_decode(json_encode($ticket_array,1));
                switch($newWorkflowId)
                {
                    case 6:
                       // 6 --Paused - 
                        $ticketId = $oldTicketObj['ParentStoryId'];
                        // Send notification to Developer(AssignedTo)
                        error_log("Send notification to Developer(AssignedTo)");
                        break;
                
                    case 14 :
                        // 14- Closed
                        // Send Notification to QA person to start the QA for this parent ticket.
                        error_log("Send Notification to QA person to start the QA for this parent ticket");
                        break;
                   //  $this->saveNotifications($ticket_data,'workflow',$newWorkflowId);
                }
               
            } else if ($oldTicketObj['WorkflowType'] == 4) { // 4--QA
                 error_log("updateWorkflowAndSendNotification-----------------3");
                $parentTicketDetails = TicketCollection::getTicketDetails($oldTicketObj['ParentStoryId'], $projectId);
                $ticket_array=array('TicketId'=>$parentTicketDetails['TicketId'],'projectId'=>$projectId,'userInfo'=>array('Id'=>$loggedInUser));//added by Ryan
                $ticket_data=json_decode(json_encode($ticket_array,1));
                switch($newWorkflowId)
                {
                   case 6:
                        error_log("Send notification to Developer(AssignedTo) and Peer");
                      //  $this->saveNotifications($ticket_data,'workflow',$newWorkflowId);  
                        break;
                
                    case 14 :
                        // 14- Closed
                   // $parentTicketDetails = TicketCollection::getTicketDetails($oldTicketObj['ParentStoryId'], $projectId);
                    $workFlowDetail = WorkFlowFields::getWorkFlowDetails($newWorkflowId);
                    foreach ($parentTicketDetails['Tasks'] as $task) {
                       
                       $taskDetails = TicketCollection::getTicketDetails($task['TaskId'], $projectId);
                       error_log("old ticket id----".$oldTicketObj['TicketId']."----".$task['TaskId']."**************************".$taskDetails['Fields']["workflow"]["value"]);
                        if($oldTicketObj['TicketId'] == $task['TaskId'] || $taskDetails['Fields']["workflow"]["value"]==14){ //QA task
                            error_log("contineu------------****");
                            continue;
                        }
                       $task_array=array('ticketId'=>$taskDetails['TicketId'],'projectId'=>$projectId,'userInfo'=>array('Id'=>$loggedInUser)); 
                       $child_ticket_data=json_decode(json_encode($task_array,1));
                     
                       // Peer UI QA status updated to Close
                       // $collection->update(array("ProjectId" => (int) $oldTicketObj['ProjectId'], "TicketId" => (int) $task['TaskId']), array('$set' => array('Fields.workflow.value' => (int) $workFlowDetail['Id'], 'Fields.workflow.value_name' => $workFlowDetail['Name'], 'Fields.state.value' => (int) $workFlowDetail['StateId'], 'Fields.state.value_name' => $workFlowDetail['State'])));
                        if($taskDetails['WorkflowType']==1){
                         $workFlowDetail = WorkFlowFields::getWorkFlowDetails(8); 
                         $newWorkflowId = 8;
                        }
                     $this->saveNotifications($child_ticket_data,'workflow',$newWorkflowId);
                   $collection->update(array("ProjectId" => (int) $oldTicketObj['ProjectId'], "TicketId" => (int) $task['TaskId']), array('$set' => array('Fields.workflow.value' => (int) $workFlowDetail['Id'], 'Fields.workflow.value_name' => $workFlowDetail['Name'], 'Fields.state.value' => (int) $workFlowDetail['StateId'], 'Fields.state.value_name' => $workFlowDetail['State'])));
                        
                    }
                     
                    $ticketId = $oldTicketObj['ParentStoryId'];
                    $workFlowDetail = WorkFlowFields::getWorkFlowDetails(8); // Fixed parent ticket
                    // Parent ticket status updated to re-open
                     $this->saveNotifications($ticket_data,'workflow',8);
                    $collection->update(array("ProjectId" => (int) $oldTicketObj['ProjectId'], "TicketId" => (int) $ticketId), array('$set' => array('Fields.workflow.value' => (int) $workFlowDetail['Id'], 'Fields.workflow.value_name' => $workFlowDetail['Name'], 'Fields.state.value' => (int) $workFlowDetail['StateId'], 'Fields.state.value_name' => $workFlowDetail['State'])));
                    // Send Notification toAll followers.
                    error_log("Send Notification to All followers");
                   
                    break;
                }
                
            }
        }catch (Exception $ex) {
            Yii::log("StoryService:updateWorkflowAndSendNotification::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
    
    /**
     * @author Anand
     * @uses Get all active filter options
     * @return type
     */
    
      public function getFilterOptions(){
        try {
           $filters = Filters::getAllActiveFilters();
           return $filters;
        } catch (Exception $ex) {
        Yii::log("StoryService:getFilterOptions::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
           }   
    }
    
   
    
    
    public function getAssignedToUser($oldTicketObj,$type,$projectId)
    {
        try
        {
        foreach($oldTicketObj['Tasks'] as $task) //added By Ryan
            {
                if($task['TaskType']==$type)//peer ticket
                {
                    error_log("==Type==".$type);
                    //get peer task details
                    $taskDetails = TicketCollection::getTicketDetails($task['TaskId'], $projectId);
                    error_log("==Task details==".print_r($taskDetails,1));
                    $collaborator=$taskDetails['Fields']['assignedto']['value'];
                    error_log("==collaborator==".$collaborator);
                    return $collaborator;   
                }
            }
        }catch(Exception $ex)
        {
            Yii::log("StoryService:getQA::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
    /**
     * @author Anand
     * @param type $projectName
     */
    
    public function getProjectDetailsByName($projectName){
        try
        {
            $projectDetails = Projects::getProjectDetails($projectName);
            return $projectDetails;
        }catch(Exception $ex)
        {
            Yii::log("StoryService:getProjectDetailsByName::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }  
    }

}

  