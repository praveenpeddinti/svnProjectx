<?php
namespace common\service;
use common\models\mongo\{TicketCollection,TinyUserCollection,ProjectTicketSequence,TicketTimeLog,TicketComments,TicketArtifacts,NotificationCollection,PersonalizedFilterCollection};
use common\components\{CommonUtility,CommonUtilityTwo,NotificationTrait,EventTrait};
use common\models\mysql\{WorkFlowFields,StoryFields,Priority,PlanLevel,TicketType,Bucket,Collaborators,TaskTypes,Filters,Projects,UserPreferences,AdvanceFilters};
use common\models\bean\FieldBean;
use yii\base\ErrorException;
use Yii;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class StoryService {

    use EventTrait;
    use NotificationTrait;
    
    /**
     * @author Moin Hussain
     * @param type $ticketId
     * @param type $projectId
     * @return type
     * @Description Gets Ticket details of given ticket
     */
    public function getTicketDetails($ticket_data) {
        try {
          $details = "NOTFOUND"; 
          $ticketId = $ticket_data->ticketId;
          $projectId = $ticket_data->projectId ;
          $timezone = $ticket_data->timeZone ;
         $ticketDetails = TicketCollection::getTicketDetails($ticketId,$projectId); 
         if(!empty($ticketDetails)){
          $details =  CommonUtility::prepareTicketDetails($ticketDetails, $projectId,$timezone);   
         }
           
       
         
         return $details;
        } catch (\Throwable $ex) {
            Yii::error("StoryService:getTicketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
       
      }
     /**
     * @author Moin Hussain
     * @param type $ticketId
     * @param type $projectId
     * @return type
      * @Description Gets Ticket details for Full edit
     */
       public function getTicketEditDetails($ticket_data) {
        try {
          $ticketId = $ticket_data->ticketId;
          $projectId = $ticket_data->projectId ;
          $timezone = $ticket_data->timeZone ;
         $editDetails =  CommonUtility::prepareTicketEditDetails($ticketId, $projectId,$timezone);
         return $editDetails;
        } catch (\Throwable $ex) {
            Yii::error("StoryService:getTicketEditDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
       
      } 
      /**
       * 
       * @param type $projectId
       * @return type
       * @throws ErrorException
       * @Description Gets details of all tickets in a project
       */
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
            return $ticketDetails;

         $details =  CommonUtility::prepareTicketDetails($ticketId, $projectId);
        
        } catch (\Throwable $ex) {
            Yii::error("StoryService:getAllTicketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

    /**
     * @author Moin Hussain
     * @return type
     * @Description Gets Story fields for a new ticket
     */
    public function getNewTicketStoryFields() {
        try {
           return StoryFields::getNewTicketStoryFields();
        } catch (\Throwable $ex) {
            Yii::error("StoryService:getNewTicketStoryFields::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

        /**
         * @author Anand Singh
         * @return type
         * @Description Gets Priority List
         */
        public function getPriorityList() {
            try {
           return Priority::getPriorityList();
            } catch (\Throwable $ex) {
            Yii::error("StoryService:getPriorityList::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
        }

    /**
     * @author Anand Singh
     * @return type
     * @Description Gets Plan Levels
     */
    public  function getPlanLevelList() {
        try {
           return PlanLevel::getPlanLevelList();
        } catch (\Throwable $ex) {
            Yii::error("StoryService:getPlanLevelList::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

    /**
     * @author Anand Singh
     * @return type
     * @Description Gets Ticket Types
     */
    public  function getTicketTypeList() {
        try {
            return TicketType::getTicketTypeList();
        } catch (\Throwable $ex) {
            Yii::error("StoryService:getTicketTypeList::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    /**
     * @author Moin Hussain
     * @return type
     * @UpdatedBy Anand
     * @Description Now thsi method has argument $workflowType,$workflowId and gets story work flow list
     */
    public function getStoryWorkFlowList($workflowType,$workflowId){
        try{
           return WorkFlowFields::getStoryWorkFlowList($workflowType,$workflowId);
        } catch (\Throwable $ex) {
            Yii::error("StoryService:getStoryWorkFlowList::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    /**
     * @author Moin Hussain
     * @return type
     * @Description Gets list of all buckets in a project
     */
      public function getBucketsList($projectId){
        try{
           return Bucket::getBucketsList($projectId);
        } catch (\Throwable $ex) {
            Yii::error("StoryService:getBucketsList::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
/**
 * @author Moin Hussain
 * @param type $ticket_data
 * @updated $parentTicNumber by suryaprakash
 * @Description Saves ticket details
 */
       public function saveTicketDetails($ticket_data,$parentTicNumber="") {
        try {
             $newticket_data=$ticket_data; //added by Ryan
             $userdata =  $ticket_data->userInfo;
             $projectId =  $ticket_data->projectId;
             $userId = $userdata->Id;
             $collaboratorData = Collaborators::getCollboratorByFieldType("Id",$userId);
              $ticket_data = $ticket_data->data;
              $dataArray = array();
              $fieldsArray = array();
              $title =  trim($ticket_data->title);
              $title = htmlspecialchars($title);
              $ticket_data->description = str_replace('&nbsp;', ' ', $ticket_data->description);
              $description =  trim($ticket_data->description);
              $workflowType=$ticket_data->WorkflowType;
              $crudeDescription = $description;
              $refinedData = CommonUtility::refineDescription($description);
              $description = $refinedData["description"];
             $plainDescription=trim($ticket_data->description);
             
             
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
                            if($bucket=='failure'){
                               $bucket_by_id=Bucket::getBackLogBucketByProjectId($projectId);
                               $fieldBean->value =(int)$bucket_by_id["Id"];
                               $fieldBean->value_name = $bucket_by_id["Name"];  
                            }else{
                               $fieldBean->value = (int)$bucket["Id"];
                               $fieldBean->value_name = $bucket["Name"];  
                            }
                            
                        
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
                  }

           $ticketModel = new TicketCollection();  
           $ticketModel->WorkflowType=$workflowType;
           $ticketModel->Title = $title;
           $ticketModel->Description = $description;
           $ticketModel->CrudeDescription = $crudeDescription;
           $ticketModel->PlainDescription = (string) strip_tags($plainDescription);
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
                    $newticket_data->ticketId = $ticketNumber;
                    self::saveNotificationsToMentionOnly($newticket_data,$refinedData['UsersList'],'mention');

                }
                           
                
              /* Notification End By Ryan */
                return $ticketNumber;
          }
         
        } catch (\Throwable $ex) {
            Yii::error("StoryService:saveTicketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
       
      }
     /**
     * @author Praveen P
     * @return type
      * @Description Gets details of all stories in a project
     */
    public function getAllStoryDetails($StoryData, $projectId) {
        try {
            $timezone = $StoryData->timeZone;
            $ticketDetails = TicketCollection::getAllTicketDetails($StoryData, $projectId,$select=['TicketId', 'Title','Fields','ProjectId']);
            $finalData = array();
            $fieldsOrderArray = [5,6,7,3,10];
            $fitlerOption=null;
              if($StoryData->filterOption !=null || $StoryData->filterOption != 0){
                   $fitlerOption=$StoryData->filterOption;
              }
          
            foreach ($ticketDetails as $ticket) {
                $details = CommonUtility::prepareDashboardDetails($ticket, $projectId,$timezone,$fieldsOrderArray,"part",$fitlerOption);
                array_push($finalData, $details);
                
               } 
            
            return $finalData;
        } catch (\Throwable $ex) {
            Yii::error("StoryService:getAllStoryDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
    /**
     * @author Praveen P
     * @Description getting total count of tickets for a user.
     * @return type  $projectId
     */
    public function getMyTicketsCount($userId,$projectId) {
        try {
            $totalCount = TicketCollection::getMyTicketsCount($userId,$projectId);
            
            return $totalCount;
        } catch (\Throwable $ex) {
            Yii::error("StoryService:getMyTicketsCount::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
    public function getAllStoriesCount($StoryData,$projectId) {
        try {
            $totalCount = TicketCollection::getAllStoriesCount($StoryData,$projectId);
            
            return $totalCount;
        } catch (\Throwable $ex) {
            Yii::error("StoryService:getAllStoriesCount::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
            
    /**
     * @author Praveen P
     * @Description This method is used to getting subtask Ids for the particular story.
     * @return type subtasks Ids
     */
    public function getSubTaskIds($StoryData, $projectId) {
        try {
           $finalData = TicketCollection::getSubTaskIds($StoryData,$projectId);
            return $finalData;
        } catch (\Throwable $ex) {
            Yii::error("StoryService:getSubTaskIds::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
    /**
     * @author Praveen P
     * @Description This method is used to getting subtask details for the particular story.
     * @return type subtasks
     */
    public function getSubTaskDetails($subTaskIds, $projectId,$timezone) {
        try {
           $ticketDetails = TicketCollection::getSubTaskDetails($subTaskIds, $projectId,$select=['TicketId', 'Title','Fields','ProjectId']);
            $finalData = array();
            $fieldsOrderArray = [5,6,7,3,10];
            foreach ($ticketDetails as $ticket) {
                $details = CommonUtility::prepareDashboardDetails($ticket, $projectId,$timezone,$fieldsOrderArray);
                array_push($finalData, $details);
            }
            return $finalData;
        } catch (\Throwable $ex) {
            Yii::error("StoryService:getSubTaskDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    /**
         * @author Anand Singh
         * @return type
     * @Description Gets list of all ticket for logged in user.
         */
        public function getMyTickets() {
            try {
               $this->sampleMethod();
               $priorityModel = new TicketCollection();
           return $priorityModel->getMyAssignedTickets();
            } catch (\Throwable $ex) {
            Yii::error("StoryService:getMyTickets::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
        }

    
        /**
 * @author Moin Hussain
 * @param type $ticket_data
         * @Description Updates ticket details on full edit
 */
       public function updateTicketDetails($ticket_data) {
        try {
             $notificationIds=array();
             $editticket=$ticket_data;
             $userdata =  $ticket_data->userInfo;
             $projectId =  $ticket_data->projectId;
             $timezone =  $ticket_data->timeZone;
             $userId = $userdata->Id;
             $saveEvent = false;
             $collaboratorData = Collaborators::getCollboratorByFieldType("Id",$userId);
            $ticket_data = $ticket_data->data;
            $workFlowDetail = array();
            $summary=array();
            $ticketCollectionModel = new TicketCollection();
            $ticketDetails = $ticketCollectionModel->getTicketDetails($ticket_data->ticketId, $projectId);
            $oldTitle=$ticketDetails["Title"];
            $bucketId=$ticketDetails['Fields']['bucket']['value'];
            $oldDescription =$ticketDetails["Description"];
            $oldEsimatedPoints=$ticketDetails['Fields']['estimatedpoints']['value'];
            $oldDueDate=$ticketDetails['Fields']['duedate']['value'];
            $ticketDetails["Title"] = trim($ticket_data->title);
            $ticketDetails["Title"] = htmlspecialchars($ticketDetails["Title"]);
            $slug =  new \MongoDB\BSON\ObjectID();
            $this->saveActivity($ticket_data->ticketId,$projectId,"Title", $ticketDetails["Title"],$userId,$slug,$timezone);
            $notificationTitleIds=$this->saveNotifications($editticket,"Title",$ticketDetails["Title"],'',$slug,$bulkUpdate=1);
            if($notificationTitleIds!=''){
              $saveEvent= true;
              $notificationIds=array_merge($notificationIds,$notificationTitleIds); 
              array_push($summary,array("ActionOn"=>"title","OldValue"=>$oldTitle,"NewValue"=>trim($ticket_data->title)));
            }
            
            $ticket_data->description = str_replace('&nbsp;', ' ', $ticket_data->description);
            $description = $ticket_data->description;
            $ticketDetails["CrudeDescription"] = $description;
            $refiendData = CommonUtility::refineDescription($description);
            $ticketDetails["Description"] = $refiendData["description"];
            $ticketDetails["PlainDescription"] = (string)strip_tags($ticket_data->description);
            $slug =  new \MongoDB\BSON\ObjectID();
            $this->saveActivity($ticket_data->ticketId,$projectId,"Description", $description,$userId,$slug,$timezone);
            $notificationDescIds=$this->saveNotifications($editticket,"Description",$description,'',$slug,$bulkUpdate=1);
            if($notificationDescIds!=''){
                $saveEvent = true;
                $notificationIds=array_merge($notificationIds,$notificationDescIds);
                array_push($summary,array("ActionOn"=>"description","OldValue"=>strip_tags($oldDescription),"NewValue"=>strip_tags(trim($ticket_data->description))));
            }
           
            $newworkflowId = "";
            foreach ($ticketDetails["Fields"] as $key => &$value) {
                 $fieldId =  $value["Id"];
                     if(isset($ticket_data->$key)){
                        $fieldDetails =  StoryFields::getFieldDetails($fieldId);
                        $fieldName =  $fieldDetails["Field_Name"];
                         if(is_numeric($ticket_data->$key) || $fieldName =="estimatedpoints"){
                              $oldvalue = $ticketDetails['Fields'][$key]['value'];
                              $value["value"] = (int)$ticket_data->$key; 
                               if($fieldDetails["Type"] == 6){
                                $collaboratorData = Collaborators::getCollboratorByFieldType("Id",$ticket_data->$key);
                                $value["value_name"] = $collaboratorData["UserName"];
                                $this->followTicket($ticket_data->$key,$ticket_data->ticketId,$projectId,$userId,$fieldDetails["Field_Name"],TRUE,"FullUpdate");
                                if (!empty($ticketDetails['Tasks'])){
                                foreach($ticketDetails['Tasks'] as $childticketId){
                                    $this->followTicket($ticket_data->$key,$childticketId['TaskId'],$projectId,$userId,'follower',FALSE,"FullUpdate");
                                }
                                }  
                                $unfollowId='';
                                $parentTicketDetails=TicketCollection::getTicketDetails($ticketDetails['ParentStoryId'],$projectId);
                                if($fieldDetails["Field_Name"]=='stakeholder' && (int)$oldvalue != (int)$ticket_data->$key){
                                 array_push($summary,array("ActionOn"=>$fieldDetails["Field_Name"],"OldValue"=>(int)$oldvalue,"NewValue"=>(int)$ticket_data->$key));   
                                }
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
                                 }
                                 if((int)$oldvalue != (int)$ticket_data->$key)
                                 array_push($summary,array("ActionOn"=>$fieldDetails["Field_Name"],"OldValue"=>(int)$oldvalue,"NewValue"=>(int)$ticket_data->$key));
                                    }
                                 $this->followTicket($ticket_data->$key,$ticketDetails['ParentStoryId'],$projectId,$userId,$ticketDetails['TicketId'].'-'.$fieldDetails["Field_Name"],FALSE,"FullUpdate");
                               
                                }
                                else if($fieldDetails["Field_Name"] == "workflow"){
                                $workFlowDetail = WorkFlowFields::getWorkFlowDetails($ticket_data->$key);
                                $value["value_name"] = $workFlowDetail["Name"];
                                $newworkflowId = $ticket_data->$key;
                                if((int)$oldvalue != (int)$ticket_data->$key)
                                array_push($summary,array("ActionOn"=>$fieldDetails["Field_Name"],"OldValue"=>(int)$oldvalue,"NewValue"=>(int)$ticket_data->$key));
                                }
                                else if($fieldDetails["Field_Name"] == "priority"){
                                $priorityDetail = Priority::getPriorityDetails($ticket_data->$key);
                                $value["value_name"] = $priorityDetail["Name"];
                                if((int)$oldvalue != (int)$ticket_data->$key)
                                array_push($summary,array("ActionOn"=>$fieldDetails["Field_Name"],"OldValue"=>(int)$oldvalue,"NewValue"=>(int)$ticket_data->$key));
                                }
                                else if($fieldDetails["Field_Name"] == "bucket"){
                                $bucketId=$ticket_data->$key;
                                $bucketDetail =  Bucket::getBucketName($ticket_data->$key,$projectId);
                                $value["value_name"] = $bucketDetail["Name"];
                                if (!empty($ticketDetails['Tasks'])){
                                   $db =  TicketCollection::getCollection();
                                foreach ($ticketDetails["Tasks"] as $task) {
                                   $db->update(array("ProjectId" => (int) $projectId, "TicketId" => (int) $task['TaskId']), array('$set' => array('Fields.bucket.value' => (int) $bucketDetail['Id'], 'Fields.bucket.value_name' => $bucketDetail['Name'])));
                                }
                                }
                                if((int)$oldvalue != (int)$ticket_data->$key)
                                array_push($summary,array("ActionOn"=>$fieldDetails["Field_Name"],"OldValue"=>(int)$oldvalue,"NewValue"=>(int)$ticket_data->$key));
                                }
                                else if($fieldDetails["Field_Name"] == "planlevel"){
                                $planlevelDetail = PlanLevel::getPlanLevelDetails($ticket_data->$key);
                                $value["value_name"] = $planlevelDetail["Name"];
                                if((int)$oldvalue != (int)$ticket_data->$key)
                                array_push($summary,array("ActionOn"=>$fieldDetails["Field_Name"],"OldValue"=>(int)$oldvalue,"NewValue"=>(int)$ticket_data->$key));
                                }
                                else if($fieldDetails["Field_Name"] == "tickettype"){
                                $tickettypeDetail = TicketType::getTicketType($ticket_data->$key);
                                $value["value_name"] = $tickettypeDetail["Name"];
                                if((int)$oldvalue != (int)$ticket_data->$key)
                                array_push($summary,array("ActionOn"=>$fieldDetails["Field_Name"],"OldValue"=>(int)$oldvalue,"NewValue"=>(int)$ticket_data->$key));
                                }
                                else if($fieldDetails["Field_Name"] == "estimatedpoints"){
                                    error_log("@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@----");
                                    if($ticketDetails['IsChild']==0){
                                        $ticketId= $ticket_data->ticketId;
                                    }else{
                                        $ticketId= $ticketDetails['ParentStoryId'];
                                }
                                $updatedEstimatedPts=(int)$ticket_data->$key-(int)$oldEsimatedPoints;
                                if($oldEsimatedPoints != $ticket_data->$key)
                                array_push($summary,array("ActionOn"=>$fieldDetails["Field_Name"],"OldValue"=>(int)$oldEsimatedPoints,"NewValue"=>(int)$ticket_data->$key));
                                TicketCollection::updateTotalEstimatedPoints($projectId,$ticketId,$updatedEstimatedPts); 
                                
                                if($ticket_data->$key == ""){
                                     $value["value"] = ""; 
                                }
                                if($ticketDetails["Fields"]['planlevel']["value"]==1){
                                   $ticketDetails['Fields']['totalestimatepoints']['value'] += $updatedEstimatedPts;
   
                                }
                              
                               
                              }
                                        
                         }else{
                             if($ticket_data->$key != ""){
                                 
                                 if($fieldDetails["Type"] == 4){
                                       $validDate = CommonUtility::validateDate($ticket_data->$key);
                                      if($validDate){
                                      $oldvalue = $value["value"];
                                     $value["value"] = new \MongoDB\BSON\UTCDateTime(strtotime($validDate) * 1000);
                                    
                                 }
                                 if($oldDueDate != $value["value"])
                                array_push($summary,array("ActionOn"=>$fieldDetails["Field_Name"],"OldValue"=>$oldDueDate,"NewValue"=>$value["value"]));
                             }else{
                                 $oldvalue=$value["value"];
                                 if(trim($oldvalue) != trim($ticket_data->$key))
                                 array_push($summary,array("ActionOn"=>$fieldDetails["Field_Name"],"OldValue"=>$oldvalue,"NewValue"=>$ticket_data->$key));
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
                        
                        $slug =  new \MongoDB\BSON\ObjectID();
                        $reportdata=array();
                        if($fieldDetails["Field_Name"] == "workflow"){
                            
                            if(property_exists($ticket_data,'reportData')){
                            $refinedData = CommonUtility::refineDescription($ticket_data->reportData);
                                $plainDescription= strip_tags($ticket_data->reportData);
                                $reportMentionArray = $refinedData['UsersList'];               
                                $reportProcessedDesc = $refinedData["description"];
                                $reportsArtifacts = $refinedData["ArtifactsList"];
                                $reportdata['CrudeDescription']=$ticket_data->reportData;
                                $reportdata['CDescription']=$reportProcessedDesc;
                                $reportdata['PlaneDescription']=$plainDescription;
                                $reportdata['Status']=1;
                            if (!empty($reportsArtifacts)) {
                            TicketArtifacts::saveArtifacts($ticket_data->ticketId, $projectId, $reportsArtifacts, $userId);
                          }
                 }  
                        }
                        
                        $activity=$this->saveActivity($ticket_data->ticketId,$projectId,$fieldName, $value["value"],$userId,$slug,$timezone,$reportdata);
                        if($activity != "noupdate")
                        {
                        $notificationPanelIds=$this->saveNotifications($editticket,$fieldName,$ticket_data->$key,$fieldDetails["Type"],$slug,$bulkUpdate=1);
                        if($notificationPanelIds!='')
                        $notificationIds=array_merge($notificationIds,$notificationPanelIds);
                        }else{
                            if($fieldDetails["Field_Name"] == "workflow"){
                                 $newworkflowId = ""; 
                           }
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
             if(sizeof($summary)!=0){
                $this->saveEvent($projectId,"Ticket",$ticket_data->ticketId,"updated","fulledit",$userId,$summary,array("BucketId"=>(int)$bucketId));   
              }
            self::sendEmailNotification($notificationIds, $projectId,1);
            TicketArtifacts::saveArtifacts($ticket_data->ticketId, $projectId, $refiendData["ArtifactsList"],$userId);
            
        } catch (\Throwable $ex) {
            Yii::error("StoryService:updateTicketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
       
      }
    /**
     * @modified by Moin Hussain
    * @author Padmaja
    * @param type $ticket_data
     * @Description Updates an story field on inline edit
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
            $timezone =$ticket_data->timeZone;
            $ticket_data->value = trim($ticket_data->value);
            $artifacts = array();
            $summary=array();
            $workFlowDetail=array();
            $valueName = "";
            $updatedState=array();
            $selectFields = [];
            $selectFields = ['ProjectId','WorkflowType','TicketId','ParentStoryId', 'IsChild','TotalEstimate','Fields.estimatedpoints.value','Fields.workflow.value','Tasks','Fields'];
            $childticketDetails = TicketCollection::getTicketDetails($ticket_data->ticketId,$ticket_data->projectId,$selectFields); 
            $ticketDetails=TicketCollection::getTicketDetails($ticket_data->ticketId,$ticket_data->projectId);//added by Ryan for Email Purpose
            $oldDueDate=$ticketDetails['Fields']['duedate']['value'];
            $oldTitle=$ticketDetails["Title"];
            $oldDescription =$ticketDetails["Description"];
            
            error_log("updateStoryFieldInline---1");
            if($checkData==0){
                 error_log("updateStoryFieldInline---2");
                 if($ticket_data->id=='Title'){
                     $fieldType = "Title";
                      $field_name = "Title";
                        $ticket_data->value = htmlspecialchars($ticket_data->value);
                    $newData = array('$set' => array("Title" => trim($ticket_data->value)));
                    $condition=array("TicketId" => (int)$ticket_data->ticketId,"ProjectId"=>(int)$ticket_data->projectId);
                    $selectedValue=$ticket_data->value;
                    $activityNewValue = $ticket_data->value;
                     if(trim($oldTitle) != trim($ticket_data->value))
                      array_push($summary,array("ActionOn"=>'title',"OldValue"=>  strip_tags(trim($oldTitle)),"NewValue"=>strip_tags(trim($activityNewValue))));
                              
                }else if($ticket_data->id=='Description'){
                    $field_name = "Description";
                    $fieldType = "Description";
                    $ticket_data->value = str_replace('&nbsp;', ' ', $ticket_data->value);
                    $refinedData = CommonUtility::refineDescription($ticket_data->value);
                    $actualdescription = $refinedData["description"];
                    $plainDescription=strip_tags($ticket_data->value);
                    $artifacts=$refinedData["ArtifactsList"];
                    $newData = array('$set' => array("Description" => $actualdescription,"CrudeDescription" =>$ticket_data->value,"PlainDescription" => (string)$plainDescription));
                    $condition=array("TicketId" => (int)$ticket_data->ticketId,"ProjectId"=>(int)$ticket_data->projectId);
                    $selectedValue=$actualdescription;
                    $activityNewValue = $ticket_data->value;
                     if(trim($oldDescription) != trim($ticket_data->value))
                      array_push($summary,array("ActionOn"=>'description',"OldValue"=>strip_tags(trim($oldDescription)),"NewValue"=>trim($plainDescription)));
                     
                    /* Send Notifications to @mentioned user by Ryan and send mail to @mentioned user*/
                            try
                            {
                                error_log("Notification with Mention");
                                if(!empty($refinedData['UsersList']))
                                {
                                    error_log("===in if mail sending.....==");
                                   self::saveNotificationsToMentionOnly($ticket_data,$refinedData['UsersList'],'mention');
                                    
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
                  $oldvalue =$ticketDetails["Fields"][$fieldName]["value"];
                     if(is_numeric($ticket_data->value)){
                        if($fieldDetails["Type"] == 6 ){
                            $collaboratorData = Collaborators::getCollboratorByFieldType("Id",$ticket_data->value);
                            $valueName = $collaboratorData["UserName"]; 
                            $this->followTicket($ticket_data->value,$ticket_data->ticketId,$ticket_data->projectId,$loggedInUser,$fieldDetails["Field_Name"],true);
                             if((int)$oldvalue != (int)$ticket_data->value)
                                 array_push($summary,array("ActionOn"=>$fieldDetails["Field_Name"],"OldValue"=>(int)$oldvalue,"NewValue"=>(int)$ticket_data->value));
                                
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
                                if((int)$oldvalue != (int)$ticket_data->value)
                                 array_push($summary,array("ActionOn"=>$fieldDetails["Field_Name"],"OldValue"=>(int)$oldvalue,"NewValue"=>(int)$ticket_data->value));
                             
                                error_log("updateStoryFieldInline---6");
                                }
                                else if($fieldDetails["Field_Name"] == "priority"){
                                $priorityDetail = Priority::getPriorityDetails($ticket_data->value);
                                $valueName = $priorityDetail["Name"];
                                if((int)$oldvalue != (int)$ticket_data->value)
                                 array_push($summary,array("ActionOn"=>$fieldDetails["Field_Name"],"OldValue"=>(int)$oldvalue,"NewValue"=>(int)$ticket_data->value));
                             
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
                                if((int)$oldvalue != (int)$ticket_data->value)
                                 array_push($summary,array("ActionOn"=>$fieldDetails["Field_Name"],"OldValue"=>(int)$oldvalue,"NewValue"=>(int)$ticket_data->value));
                             
                                }
                                else if($fieldDetails["Field_Name"] == "planlevel"){
                                $planlevelDetail =  PlanLevel::getPlanLevelDetails($ticket_data->value);
                                $valueName = $planlevelDetail["Name"];
                                if((int)$oldvalue != (int)$ticket_data->value)
                                 array_push($summary,array("ActionOn"=>$fieldDetails["Field_Name"],"OldValue"=>(int)$oldvalue,"NewValue"=>(int)$ticket_data->value));
                             
                                }
                                else if($fieldDetails["Field_Name"] == "tickettype"){
                                $tickettypeDetail = TicketType::getTicketType($ticket_data->value);
                                $valueName = $tickettypeDetail["Name"];
                                if((int)$oldvalue != (int)$ticket_data->value)
                                 array_push($summary,array("ActionOn"=>$fieldDetails["Field_Name"],"OldValue"=>(int)$oldvalue,"NewValue"=>(int)$ticket_data->value));
                             
                                } 
                         $leftsideFieldVal = (int)$ticket_data->value;  
                    }else{
                         error_log("updateStoryFieldInline---8");
                        if($ticket_data->value != ""){
                             error_log("updateStoryFieldInline---9");
                            $validDate = CommonUtility::validateDate($ticket_data->value);
                                if($validDate){
                                     error_log("================++++++++++++++".$validDate);
                                   $ticket_data->value = new \MongoDB\BSON\UTCDateTime(strtotime($validDate) * 1000);
                                   error_log("&&&&&&&&&&&&&&777++++++++++++++".$ticket_data->value);
                                   $leftsideFieldVal = $ticket_data->value; 
                                    if($oldDueDate != $ticket_data->value)
                                    array_push($summary,array("ActionOn"=>$fieldDetails["Field_Name"],"OldValue"=>$oldDueDate,"NewValue"=>$ticket_data->value));
                             
                                }else{
                                    $leftsideFieldVal = $ticket_data->value; 
                                     if(trim($oldvalue) != trim($ticket_data->value))
                                 array_push($summary,array("ActionOn"=>$fieldDetails["Field_Name"],"OldValue"=>$oldvalue,"NewValue"=>$ticket_data->value));
                             
                                }
                               
                        }else{
                             error_log("updateStoryFieldInline---10");
                            $leftsideFieldVal = $ticket_data->value;
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
                    if($fieldDetails["Field_Name"] == "estimatedpoints"){
                                if($childticketDetails['IsChild']==0){
                                    $ticketId= $ticket_data->ticketId;
                                  }else{
                                      $ticketId= $childticketDetails['ParentStoryId'];
                                  }
                                  $updatedEstimatedPts=(int)$ticket_data->value-(int)$childticketDetails['Fields']['estimatedpoints']['value'];
                                  TicketCollection::updateTotalEstimatedPoints($ticket_data->projectId,$ticketId,$updatedEstimatedPts);
                                   if((int)($oldvalue) != (int)($ticket_data->value))
                                  array_push($summary,array("ActionOn"=>$fieldDetails["Field_Name"],"OldValue"=>(int)$oldvalue,"NewValue"=>(int)$ticket_data->value));
                             
                                  }
                    
                    
                    error_log("updateStoryFieldInline---12");
                    $fieldtochange1= "Fields.".$field_name.".value";
                    $fieldtochange2 = "Fields.".$field_name.".value_name";
                    $fieldtochangeId = "Fields.".$field_name.".Id";
                    $newData = array('$set' => array($fieldtochange1 => $leftsideFieldVal,$fieldtochange2 =>$valueName));
                    $condition=array("TicketId" => (int)$ticket_data->ticketId,"ProjectId"=>(int)$ticket_data->projectId,$fieldtochangeId=>(int)$ticket_data->id);
                    $selectedValue=$leftsideFieldVal;
                    $activityNewValue = $leftsideFieldVal;
          if($fieldDetails["Field_Name"] == "estimatedpoints"){
               if($childticketDetails['IsChild'] == 0){
                 $ticketId= $ticket_data->ticketId;
                 $getTicketDetailsForEstimate = TicketCollection::getTicketDetails($ticketId,$ticket_data->projectId,['Fields.totalestimatepoints.value']);
                 $totalEstimatePoints = $getTicketDetailsForEstimate["Fields"]["totalestimatepoints"]; 
                 $selectedValue=$totalEstimatePoints;
                 
               }
         }
   
            }
            $slug =  new \MongoDB\BSON\ObjectID();
            $this->saveNotifications($ticket_data,$field_name,$ticket_data->value,$fieldType,$slug);
                $reportdata=array();
                 if(property_exists($ticket_data,'reportData')){
                    $refinedData = CommonUtility::refineDescription($ticket_data->reportData);
                    $plainDescription= strip_tags($ticket_data->reportData);
                    $reportMentionArray = $refinedData['UsersList'];               
                    $reportProcessedDesc = $refinedData["description"];
                    $reportsArtifacts = $refinedData["ArtifactsList"];
                    $reportdata['CrudeDescription']=$ticket_data->reportData;
                    $reportdata['CDescription']=$reportProcessedDesc;
                    $reportdata['PlaneDescription']=$plainDescription;
                    $reportdata['Status']=1;
                    if (!empty($reportsArtifacts)) {
                    TicketArtifacts::saveArtifacts($ticket_data->ticketId, $ticket_data->projectId, $reportsArtifacts, $loggedInUser);
                  }
                 }
                  if(sizeof($summary)!=0){
                      $newTicketDetails= TicketCollection::getTicketDetails($ticket_data->ticketId,$ticket_data->projectId,['Fields']);
                $this->saveEvent($projectId,"Ticket",$ticket_data->ticketId,"updated","inline",(int)$ticket_data->userInfo->Id,$summary,array("BucketId"=>(int)$newTicketDetails["Fields"]['bucket']["value"]));   
              }
                $activityData = $this->saveActivity($ticket_data->ticketId,$ticket_data->projectId,$fieldName,$activityNewValue,$loggedInUser,$slug,$timezone,$reportdata);
                $updateStaus = $collection->update($condition, $newData);
                if($field_name=='workflow'){
                $updateStatus = $this->updateWorkflowAndSendNotification($childticketDetails,$ticket_data->value,$loggedInUser);
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
                $returnValue=$selectedValue;
                 error_log("updateStoryFieldInline---16");
                 if($ticket_data->editedId == "title"){
                      $returnValue = htmlspecialchars_decode($returnValue);
                 }
                
            $returnValue =  array("updatedFieldData" =>$returnValue,"activityData"=>$activityData,'updatedState'=>$updatedState);
            return $returnValue;

        } catch (\Throwable $ex) {
            Yii::error("StoryService:updateStoryFieldInline::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }

    }
    /**
     * 
     * @param type $commentData
     * @throws ErrorException
     * @Description Deletes a Comment made on a ticket
     */
    public function removeComment($commentData){
        try {
            $res = TicketComments::removeComment($commentData);
            $notify_type = "delete";
            $commentData->Comment->OriginalCommentorId=$commentData->userInfo->Id;
            $this->saveNotificationsForComment($commentData,array(),$notify_type,new \MongoDB\BSON\ObjectID($commentData->Comment->Slug));

        } catch (\Throwable $ex) {
            Yii::error("StoryService:removeComment::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        } 
     
    }
    /**
     * 
     * @param type $commentData
     * @return type
     * @throws ErrorException
     * @Description Saves a comment made on a ticket
     */
    public function saveComment($commentData){
        try{
            $slug="";
            $refinedData = CommonUtility::refineDescription($commentData->Comment->CrudeCDescription);
            $ticketDetails=TicketCollection::getTicketDetails($commentData->ticketId,$commentData->projectId);//added By Ryan
            
            $mentionArray = $refinedData['UsersList'];               
            $processedDesc = $refinedData["description"];
            $artifacts = $refinedData["ArtifactsList"];
            $commentDesc = $commentData->Comment->CrudeCDescription;
            $plainDescription= strip_tags($commentData->Comment->CrudeCDescription);
            $timezone = $commentData->timeZone;
            if (isset($commentData->Comment->Slug)) {
                $commentData->Comment->OriginalCommentorId=$commentData->userInfo->Id;
                $notify_type = "edit";
                $slug= new \MongoDB\BSON\ObjectID($commentData->Comment->Slug);
                $this->saveNotificationsForComment($commentData,$mentionArray,$notify_type,$slug);
                $collection = Yii::$app->mongodb->getCollection('TicketComments');
                $newdata = array('$set' => array("Activities.$.CrudeCDescription" => $commentDesc, "Activities.$.CDescription" => $processedDesc,"Activities.$.PlainDescription" => (string)$plainDescription));
                $collection->update(array("TicketId" => (int) $commentData->ticketId, "ProjectId" => (int) $commentData->projectId, "Activities.Slug" => new \MongoDB\BSON\ObjectID($commentData->Comment->Slug)), $newdata);
                $retData = array("CrudeCDescription" => $commentDesc,"CDescription" => $processedDesc);
                if (!empty($artifacts)) {
                    TicketArtifacts::saveArtifacts($commentData->ticketId, $commentData->projectId, $artifacts, $commentData->userInfo->Id);
                }
                
               
               
                return $retData;
            } else {
                $commentedOn = new \MongoDB\BSON\UTCDateTime(time() * 1000);
                $slug = new \MongoDB\BSON\ObjectID();
                $commentDataArray = array(
                    "Slug" => $slug,
                    "CDescription" => $processedDesc,
                    "CrudeCDescription" => $commentDesc,
                    "PlainDescription"=>(string)$plainDescription,
                    "ActivityOn" => $commentedOn,
                    "ActivityBy" => (int) $commentData->userInfo->Id,
                    "Status" => ($commentData->Comment->ParentIndex == "") ? (int) 1 : (int) 2,
                    "Reply"=>($commentData->Comment->Reply == "") ? (int) 0 : (int) 1,
                    "OrginalCommentor"=>$commentData->Comment->OriginalCommentorId,
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
                $datetime->setTimezone(new \DateTimeZone($timezone));
                $readableDate = $datetime->format('M-d-Y h:i:s A');
                $commentDataArray["ActivityOn"] = $readableDate;
                $mentionArray = $refinedData['UsersList'];
                $notify_type = "comment";
                $actionName = "commented";
                if($commentDataArray["Reply"]==1)
                $notify_type = "repliedOn"; 
                $this->saveNotificationsForComment($commentData,$mentionArray,$notify_type,$slug);

                return $commentDataArray;
            }
        }catch (\Throwable $ex) {
            Yii::error("StoryService:saveComment::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
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
        * @Description add the user to follower list of a ticket
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
          
        } catch (\Throwable $ex) {
            Yii::error("StoryService:followTicket::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        } 
    }
    /**
    * @author Praveen P
    * @param type $collaboratorId
    * @param type $ticketId
    * @param type $projectId
     * @Description Unfollow a user from a ticket
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
           
        } catch (\Throwable $ex) {
            Yii::error("StoryService:unfollowTicket::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
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
     * @Description Gets all ticket of a user based on Page nation
     */
     public function getAllMyTickets($userId,$sortorder,$sortvalue,$offset,$pageLength,$projectId,$timezone) {
        try {
            $ticketDetails = TicketCollection::getMyTickets($userId,$sortorder,$sortvalue,$offset,$pageLength,$projectId,$select=['TicketId', 'Title','Fields','ProjectId']);
            $finalData = array();
            $fieldsOrderArray = [5,6,7,3,9,10];
            foreach ($ticketDetails as $ticket) {
                $details = CommonUtility::prepareDashboardDetails($ticket, $projectId,$timezone,$fieldsOrderArray);
                unset($details['project_name']);
                array_push($finalData, $details);
            }
            return $finalData;
        } catch (\Throwable $ex) {
            Yii::error("StoryService:getAllMyTickets::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        } 
    }
/**
 * @author Moin Hussain
 * @param type $ticketId
 * @param type $projectId
 * @Description gets activity done in a ticket
 */
    public function getTicketActivity($ticket_data){
        try{
          $ticketId = $ticket_data->ticketId;
          $projectId = $ticket_data->projectId ;
          $timezone = $ticket_data->timeZone ;
           $ticketActivity = TicketComments::getTicketActivity($ticketId, $projectId);
         
           if(isset($ticketActivity["Activities"])){
           foreach ($ticketActivity["Activities"] as &$value) {
                 CommonUtility::prepareActivity($value,$projectId,$timezone);
           }
           }
           return $ticketActivity;
            
        } catch (\Throwable $ex) {
            Yii::error("StoryService:getTicketActivity::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        } 
    }
   

      
    /**
     * @author Suryaprakash
     * @param type $parentTicNumber
     * @param type $childTicketIds
     * @Description Updates parent ticket details.
     * @return empty
     */
    public function updateParentTicketTaskField($projectId,$parentTicNumber, $childTicketObjArray) {
        try {
            $ticketDetails = TicketCollection::updateParentTicketTaskField($projectId,$parentTicNumber, $childTicketObjArray);
        } catch (\Throwable $ex) {
            Yii::error("StoryService:updateParentTicketTaskField::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        } 
    }

    
    /**
     * @author Ryan
     * @param type $ticketId
     * @param type $projectId
     * @return empty
     * @Description Gets followers of a ticket
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
        } catch (\Throwable $ex) {
            Yii::error("StoryService:getTicketFollowers::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        } 
    }
    
    /**
     * @author Ryan
     * @param type $follower
     * @return empty
     * @Description Gets details of follower
     */
    public function getFollower($follower)
    {
        try{
            $follower=TinyUserCollection::getProfileOfFollower($follower);
            return $follower;
        } catch (\Throwable $ex) {
            Yii::error("StoryService:getFollower::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        } 
    }    
        /**
     * @author Padmaja
     * @Description This method is used to save child task details.
    * @return type 
    * @ModifiedBy Anand 
    * @Modification Update Task object before saving to the collection baesd on task type.
     */
    public function createChildTask($postData)
            {
    
        try{
            $returnStatus="failure";
            $timezone = $postData->timeZone;
            $ticketCollectionModel = new TicketCollection();
           $loggedInUserId =  $postData->userInfo->Id;
           $ticketDetails = $ticketCollectionModel->getTicketDetails($postData->ticketId, $postData->projectId);
           $bucket=$ticketDetails["Fields"]["bucket"]["Id"]; 
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
                         $fieldBean->value= (int)1; 
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
           $ticketModel->TicketIdString = (string)$ticketNumber;
           
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
               /* Notifications */
               $activityOn="ChildTask";
               $notify_type ='created';
               $slug =  new \MongoDB\BSON\ObjectID();
               $this->saveNotifications($postData,$notify_type,$activityOn,'',$slug,'',$ticketModel->TicketId);
               $activityData= $this->saveActivity($postData->ticketId,$postData->projectId,"ChildTask", $ticketNumber,$postData->userInfo->Id,$slug,$timezone);
               $returnStatus=array('Tasks'=>$subTicketDetails,'activityData'=>$activityData);
               /* end Notifications */
               
               //Padmaja-refreeing id is saving wrongly
               EventTrait::saveEvent($postData->projectId,"Ticket",$postData->ticketId,"created",'create',$loggedInUserId,[array("ActionOn"=>  strtolower("childtask"),"OldValue"=>0,"NewValue"=>(int)$ticketNumber)],array("BucketId"=>(int)$bucket));
            }
             return $returnStatus;
         } catch (\Throwable $ex) {
            Yii::error("StoryService:createChildTask::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        } 
    }

    
    
       /**
     * @author Padmaaja 
     * @return array
     * @updated by suryaprakash
        * @Description Gets all story details based on the search string
     */
    public function getAllStoryDetailsForSearch($projectId, $ticketId, $sortvalue, $searchString) {
        try {
           $ParentTicketInfo = TicketCollection::getTicketDetails($ticketId,$projectId,array("Tasks","RelatedStories") );
            
            $ticketArray = $ParentTicketInfo["Tasks"];
            $subTaskArray=array();
            foreach($ticketArray as $subtickts){
                array_push($subTaskArray,$subtickts['TaskId']);
            }
          
            array_push($subTaskArray, (int)$ticketId);
            if (!empty($ParentTicketInfo["RelatedStories"])) {
                for ($i = 0; $i < sizeof($ParentTicketInfo["RelatedStories"]); $i++) {
                       array_push($subTaskArray,(int)$ParentTicketInfo["RelatedStories"][$i] );
                }
            }
            $finalData = array();
            $ticketDetails = TicketCollection::getAllTicketDetailsForSearch($projectId, $ticketId, $sortvalue, $searchString,$subTaskArray);
            foreach ($ticketDetails as $ticket) {
                $ticket["Title"] = htmlspecialchars_decode($ticket["Title"]);
                array_push($finalData, $ticket);
            }
            return $finalData;
        } catch (\Throwable $ex) {
            Yii::error("StoryService:getAllStoryDetailsForSearch::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        } 
    }

      /**
     * @author suryaprakash reddy 
     * @return array
       * @Description Updates the data changes in a related task
     */
    public function updateRelatedTaskId($projectId,$ticketId,$searchTicketId,$loginUserId=''){
        try{
            $returnStatus="failure";
            TicketCollection::updateRelateTicket($projectId,$ticketId,$searchTicketId); 
        } catch (\Throwable $ex) {
            Yii::error("StoryService:updateRelatedTaskId::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        } 
    }
    
      /**
     * @author suryaprakash reddy 
     * @Description This method is used to insertTimelog
     * @return type array
     */
    public function insertTimeLog($timelog_data) {
        try {
            $timelog_data->addTimelogTime = CommonUtility::validateDate($timelog_data->addTimelogTime);
            $addTimelogTime = date("Y-m-d H:i:s", strtotime($timelog_data->addTimelogTime));
            $projectId = $timelog_data->projectId;
            $ticketId = $timelog_data->ticketId;
            $timezone = $timelog_data->timeZone;
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
            $activityData=$this->saveActivity($ticketId, $projectId,'TotalTimeLog', $total, $userId,$slug,$timezone); //added by Ryan
            $this->saveNotifications($timelog_data, 'TotalTimeLog', $total,'TotalTimeLog',$slug); //added by Ryan
            if ($parenTicketInfo["ParentStoryId"] != "") {
                $updateParentTotalTime = TicketCollection::updateTotalTimeLog($projectId, $parenTicketInfo["ParentStoryId"], $totalWorkHours);
            }
           
                $updateindivisualTotalTimeLog = TicketCollection::updateTotalTimeLog($projectId, $ticketId, $totalWorkHours);
              
                return $activityData;
            }
        } catch (\Throwable $ex) {
            Yii::error("StoryService:insertTimeLog::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        } 
    }

    /**
     * @author suryaprakash reddy 
     * @Description This method is used to getTimeLog
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
        } catch (\Throwable $ex) {
            Yii::error("StoryService:getTimeLog::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        } 
    }

     /**
     * @author Jagadish 
     * @return type array
      * @Description Gets artifacts in a ticket
     */
    public function getTicketAttachments($ticketId,$projectId){
        try {
            $artifacts = TicketArtifacts::getTicketArtifacts($ticketId, $projectId);
            return $artifacts;
        } catch (\Throwable $ex) {
            Yii::error("StoryService:getTicketAttachments::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        } 
    }

    /**
     * @author suryaprakash reddy 
     * @return type array
     * @Description gets all the stories related to a ticket.
     */
    public function getAllRelateStory($projectId, $ticketId) {
        try {
             $ParentTicketInfo = TicketCollection::getTicketDetails($ticketId,$projectId,array("TicketId","RelatedStories") );
            $finalData = array();
            $ticketArray = $ParentTicketInfo["RelatedStories"];
            $ticketDetails = TicketCollection::getAllRelateStory($projectId, $ticketId, $ticketArray);
            foreach ($ticketDetails as $ticket) {
                $ticket["Title"] = htmlspecialchars_decode($ticket["Title"]);
                array_push($finalData, $ticket);
            }
            return $finalData;
        } catch (\Throwable $ex) {
            Yii::error("StoryService:getAllRelateStory::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        } 
    }

    /**
     * @author suryaprakash reddy 
     * @return type array
     * @Description Unrelates a task from parent story
     */
    public function unRelateTask($projectId, $parentTicketId, $unRelateTicketId,$loginUserId='',$timezone) {
        try {
            $slug =  new \MongoDB\BSON\ObjectID();
            $activityData = $this->saveActivity($parentTicketId, $projectId, 'Unrelated', $unRelateTicketId, $loginUserId,$slug,$timezone);
            $unRelateChild = TicketCollection::unRelateTask($projectId, $parentTicketId, $unRelateTicketId);
        return $activityData;
        } catch (\Throwable $ex) {
            Yii::error("StoryService:unRelateTask::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        } 
    }
     /**
     * @author Padmaja
      * @Description updatung follower list for subTask
     * @return type 
     */
       public function updateFollowersForSubTask($ticketId,$projectId,$followerArray){
        try{
            $db =  TicketCollection::getCollection();
            $db->findAndModify( array("ProjectId"=> (int)$projectId ,"TicketId"=> (int)$ticketId), array('$push'=> array('Followers' =>array('$each'=>$followerArray))),array('new' => 1,"upsert"=>1)); 
           
        } catch (\Throwable $ex) {
            Yii::error("StoryService:updateFollowersForSubTask::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        } 
          
    }
  /**
   * @author Anand
   * @Description Get default task types Ex:- UI,QA,Peer
   * @return type
   */
    public function getTaskTypes(){
        try {
           $taskTypes = TaskTypes::getTaskTypes();
           return $taskTypes;
        } catch (\Throwable $ex) {
            Yii::error("StoryService:getTaskTypes::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }    
    }
    
    /**
     * @author Anand 
     * @Description This method is responsible to update ticket status and send the notification to appropriate user.
     * @param type $oldTicketObj
     * @param type $newWorkflowId
     * @return boolean
     */   
    public function updateWorkflowAndSendNotification($oldTicketObj, $newWorkflowId,$loggedInUser) {

        try {
            $collection = TicketCollection::getCollection();
            $projectId = $oldTicketObj['ProjectId'];
            $ticket_array=array('ticketId'=>$oldTicketObj['TicketId'],'projectId'=>$projectId,'userInfo'=>array('Id'=>$loggedInUser));//added by Ryan
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
                            $taskDetails = TicketCollection::getTicketDetails($task['TaskId'], $projectId);
                            $task_array=array('ticketId'=>$taskDetails['TicketId'],'projectId'=>$projectId,'userInfo'=>array('Id'=>$loggedInUser));
                            $ticket_data=json_decode(json_encode($task_array,1));
                            switch($newWorkflowId)
                            {
                            case 1 :
                            case 7 :   // 1-New,7 -- Invalid
                                // send notification to assigned to person
                            $this->saveNotifications($ticket_data,'workflow',$newWorkflowId,'','',$bulkUpdate=1);
                            $collection->update(array("ProjectId" => (int) $oldTicketObj['ProjectId'], "TicketId" => (int) $task['TaskId']), array('$set' => array('Fields.workflow.value' => (int) $workFlowDetail['Id'], 'Fields.workflow.value_name' => $workFlowDetail['Name'], 'Fields.state.value' => (int) $workFlowDetail['StateId'], 'Fields.state.value_name' => $workFlowDetail['State'])));
                            break;

                            case 8 : // 8-Fixed
                            $this->saveNotifications($ticket_data,'workflow',$newWorkflowId,'','',$bulkUpdate=1);
                            $fixedworkflowDetail = WorkFlowFields::getWorkFlowDetails(8);
                            $workFlowDetail = WorkFlowFields::getWorkFlowDetails(14); // Get closed status details
                            if ($taskDetails['WorkflowType'] == 1) {
                                
                                $collection->update(array("ProjectId" => (int) $oldTicketObj['ProjectId'], "TicketId" => (int) $task['TaskId']), array('$set' => array('Fields.workflow.value' => (int) $fixedworkflowDetail['Id'], 'Fields.workflow.value_name' => $fixedworkflowDetail['Name'], 'Fields.state.value' => (int) $fixedworkflowDetail['StateId'], 'Fields.state.value_name' => $fixedworkflowDetail['State'])));
                                
                            } else {
                                $collection->update(array("ProjectId" => (int) $oldTicketObj['ProjectId'], "TicketId" => (int) $task['TaskId']), array('$set' => array('Fields.workflow.value' => (int) $workFlowDetail['Id'], 'Fields.workflow.value_name' => $workFlowDetail['Name'], 'Fields.state.value' => (int) $workFlowDetail['StateId'], 'Fields.state.value_name' => $workFlowDetail['State'])));
                            }
                            break;

                            case 10 : // 10 -Reopen
                                
                            if ($taskDetails['Fields']['workflow']['value'] == 7) {
                                $this->saveNotifications($ticket_data,'workflow',$newWorkflowId,'','',$bulkUpdate=1);
                                $collection->update(array("ProjectId" => (int) $oldTicketObj['ProjectId'], "TicketId" => (int) $task['TaskId']), array('$set' => array('Fields.workflow.value' => (int) $workFlowDetail['Id'], 'Fields.workflow.value_name' => $workFlowDetail['Name'], 'Fields.state.value' => (int) $workFlowDetail['StateId'], 'Fields.state.value_name' => $workFlowDetail['State'])));
                            // send notification to all child ticket
                                
                            } else if ($taskDetails['Fields']['state']['value'] == 6 && ($taskDetails['WorkflowType'] == 3 || $taskDetails['WorkflowType'] == 4)) {
                                  // send notification to Peer and QA
                                $this->saveNotifications($ticket_data,'workflow',$newWorkflowId,'','',$bulkUpdate=1);
                                $collection->update(array("ProjectId" => (int) $oldTicketObj['ProjectId'], "TicketId" => (int) $task['TaskId']), array('$set' => array('Fields.workflow.value' => (int) $workFlowDetail['Id'], 'Fields.workflow.value_name' => $workFlowDetail['Name'], 'Fields.state.value' => (int) $workFlowDetail['StateId'], 'Fields.state.value_name' => $workFlowDetail['State'])));
                                    //send notification to Peer  
                            }
                            
                            break;
                        }

                     }
                    }
                    return true;
                } else {
                    return true;
                }
            } else if ($oldTicketObj['WorkflowType'] == 2 && $newWorkflowId == 5) { // 2-UI
                // Developer should get notification 
                error_log("Developer should get notification about UI completion");
            } else if ($oldTicketObj['WorkflowType'] == 3) { // 3- Peer
                
                $parentTicketDetails = TicketCollection::getTicketDetails($oldTicketObj['ParentStoryId'], $projectId); 
                $ticket_array=array('ticketId'=>$parentTicketDetails['TicketId'],'projectId'=>$projectId,'userInfo'=>array('Id'=>$loggedInUser));//added by Ryan
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
                }
               
            } else if ($oldTicketObj['WorkflowType'] == 4) { // 4--QA
                 error_log("updateWorkflowAndSendNotification-----------------3");
                $parentTicketDetails = TicketCollection::getTicketDetails($oldTicketObj['ParentStoryId'], $projectId);
                $ticket_array=array('ticketId'=>$parentTicketDetails['TicketId'],'projectId'=>$projectId,'userInfo'=>array('Id'=>$loggedInUser));//added by Ryan
                $ticket_data=json_decode(json_encode($ticket_array,1));
                switch($newWorkflowId)
                {
                   case 6:
                        error_log("Send notification to Developer(AssignedTo) and Peer");
                        break;
                
                    case 14 :
                        // 14- Closed
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
                        if($taskDetails['WorkflowType']==1){
                         $workFlowDetail = WorkFlowFields::getWorkFlowDetails(8); 
                         $newWorkflowId = 8;
                        }
                     $this->saveNotifications($child_ticket_data,'workflow',$newWorkflowId,'','',$bulkUpdate=1);
                   $collection->update(array("ProjectId" => (int) $oldTicketObj['ProjectId'], "TicketId" => (int) $task['TaskId']), array('$set' => array('Fields.workflow.value' => (int) $workFlowDetail['Id'], 'Fields.workflow.value_name' => $workFlowDetail['Name'], 'Fields.state.value' => (int) $workFlowDetail['StateId'], 'Fields.state.value_name' => $workFlowDetail['State'])));
                        
                    }
                     
                    $ticketId = $oldTicketObj['ParentStoryId'];
                    $workFlowDetail = WorkFlowFields::getWorkFlowDetails(8); // Fixed parent ticket
                    // Parent ticket status updated to re-open
                     $this->saveNotifications($ticket_data,'workflow',8,'','',$bulkUpdate=1);
                    $collection->update(array("ProjectId" => (int) $oldTicketObj['ProjectId'], "TicketId" => (int) $ticketId), array('$set' => array('Fields.workflow.value' => (int) $workFlowDetail['Id'], 'Fields.workflow.value_name' => $workFlowDetail['Name'], 'Fields.state.value' => (int) $workFlowDetail['StateId'], 'Fields.state.value_name' => $workFlowDetail['State'])));
                    // Send Notification toAll followers.
                   
                    break;
                }
                
            }
        }catch (\Throwable $ex) {
            Yii::error("StoryService:updateWorkflowAndSendNotification::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        } 
    }
    
    
    /**
     * @author Anand
     * @Description Get all active filter options
     * @return type
     */
    
      public function getFilterOptions(){
        try {
           $filters = Filters::getAllActiveFilters();
           return $filters;
        } catch (\Throwable $ex) {
            Yii::error("StoryService:getFilterOptions::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }  
    }
    
   
    /**
     * 
     * @param type $oldTicketObj
     * @param type $type
     * @param type $projectId
     * @return type
     * @throws ErrorException
     * @Description Returns the data of a user to who ticket is assigned
     */
    
    public function getAssignedToUser($oldTicketObj,$type,$projectId)
    {
        try
        {
        foreach($oldTicketObj['Tasks'] as $task) //added By Ryan
            {
                if($task['TaskType']==$type)//peer ticket
                {
                    $taskDetails = TicketCollection::getTicketDetails($task['TaskId'], $projectId);
                    $collaborator=$taskDetails['Fields']['assignedto']['value'];
                    return $collaborator;   
                }
            }
        }catch (\Throwable $ex) {
            Yii::error("StoryService:getAssignedToUser::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        } 
    }
    
    /**
     * @author Anand
     * @param type $projectName
     * @Description gets project details by project name
     */
    
    public function getProjectDetailsByName($projectName){
        try
        {            
            
            $projectDetails = Projects::getProjectDetails($projectName);
            return $projectDetails;
        }catch (\Throwable $ex) {
            Yii::error("StoryService:getProjectDetailsByName::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
    
    /**
     * @author Praveen P
     * @return type
     * @Description gets Story details
     */
    public function getAllStoryDetailsNew($draw,$offset, $projectId) {
        try {error_log("-----service----");
            $ticketDetails = TicketCollection::getAllTicketDetailsNew($draw,$offset, $projectId,$select=['TicketId', 'Title','Fields','ProjectId']);
            $finalData = array("draw"=>$draw,"recordsTotal"=>60,"recordsFiltered"=>60);
            $data = array();
            $fieldsOrderArray = ["assignedto","priority","workflow","bucket","duedate","planlevel"];
            $fitlerOption=null;
          
            foreach ($ticketDetails as $ticket) {
                $details = CommonUtility::prepareDashboardDetailsTemp($ticket, $projectId,$fieldsOrderArray,"part",$fitlerOption);
                array_push($data, $details);
                $tasks = $ticket["Tasks"];
                
               foreach ($tasks as $task) {
                 $task = TicketCollection::getTicketDetails($task["TaskId"],$projectId,$select=['TicketId', 'Title','Fields','ProjectId','ParentStoryId']);
                if(is_array($task)){
                 $details = CommonUtility::prepareDashboardDetailsTemp($task, $projectId,$fieldsOrderArray,"part",$fitlerOption);
                 array_push($data, $details);   
                    
                }
                 
               }
            }
            $finalData["data"]=$data;
            return $finalData;
        } catch (\Throwable $ex) {
            Yii::error("StoryService:getAllStoryDetailsNew::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        } 
    }
     /**
     * @author Padmaja
     * @Description This method is used to get all project details for dashboard
     * @return type
      * 
      * $userId,$page,$pageLength,$projectFlag,$activityPage,$projectId,$activityDropdownFlag
     */
       public function getProjectDetailsForDashboard($postData) {
        try {
            $totalCount = CommonUtilityTwo::getTicketDetailsForDashboard($postData);
            return $totalCount;
        } catch (\Throwable $ex) {
            Yii::error("StoryService:getProjectDetailsForDashboard::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        } 
    }
     /**
     * @author Ryan
     * @Description Saves User Preferences in Story Creation
     * @return type
     */
    public function saveUserPreferences($userid,$tasks)
    {
        try{
            
            UserPreferences::savePreference($userid,$tasks);
        } catch (\Throwable $ex) {
            Yii::error("StoryService:saveUserPreferences::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        } 
    }
    
    /**
     * @author Ryan
     * @Description Gets User Preferences 
     * @return type
     */
    public function getUserPreferences($userid)
    {
        try
        {
           $preference_items=UserPreferences::getPreference($userid);
           return $preference_items;
        } catch (\Throwable $ex) {
            Yii::error("StoryService:getUserPreferences::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        } 
    }
    
    
    /**
     * @author Anand
     * @param type $ticketId
     * @param type $projectId
     * @param type $timeZone
     * @return type
     * @Description Gets ticket detaild after updating
     */
    
    public function getUpdatedTicketDetails($ticketId,$projectId,$timeZone){
        
        try {
            $getNewTicketData = TicketCollection::getTicketDetails($ticketId,$projectId,[]);  
            $details = CommonUtility::prepareDashboardDetails($getNewTicketData, $projectId,$timeZone,[5,6,7,3,10],"part",null);
            return $details; 
        } catch (\Throwable $ex) {
            Yii::error("StoryService:getUpdatedTicketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        } 
       
    }
    /**
     * 
     * @return type
     * @throws ErrorException
     * @Description gets list of states based on filter
     */
    public function getStateListFilters(){
        try{
            $stateFilters=WorkFlowFields::getBucketsListFilters();
            return $stateFilters;
         } catch (Exception $ex) {
             Yii::error("StoryService:getStateListFilters::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
    /**
     * @author Anand
     * @Description Get  advance filter options
     * @return type
     */
    
      public function getAdvanceFilterOptions(){
        try {
           $filters = AdvanceFilters::getAdvanceFilters();
           return $filters;
        } catch (\Throwable $ex) {
            Yii::error("StoryService:getAdvanceFilterOptions::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }  
    }
    
    
    /**
     * @author Anand
     * @Description Get  getWorkflowFields options
     * @return type
     */
     public function getWorkflowFields(){
        try {
           $filters = WorkFlowFields::getWorkflowStatusList();
           return $filters;
        } catch (\Throwable $ex) {
            Yii::error("StoryService:getWorkflowFields::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }  
    }
    
     /**
     * @author Anand
     * @Description Get  data based on adv. filter selection
     * @return type
     */
    public function applyAdvanceFilter($StoryData){
        
        try {
            $timezone = $StoryData->timeZone;
            $projectId = $StoryData->projectId;
            $ticketDetails = TicketCollection::getStoryListByAdvanceFilter($StoryData);
            $finalData = array('ticketData'=>array(),'filterData'=>$ticketDetails['filterData']);
            $fieldsOrderArray = [5,6,7,3,10];
            $obj = array('type'=>'advance','showChild'=>0);
            $fitlerOption= (object)$obj;
          
            if(sizeof($ticketDetails['ticketDetails'])!=0){
              foreach ($ticketDetails['ticketDetails'] as $ticket) {
                $details = CommonUtility::prepareDashboardDetails($ticket, $projectId,$timezone,$fieldsOrderArray,"part",$fitlerOption);
                array_push($finalData['ticketData'], $details);
                
               }  
            }
             
            
            return $finalData;
        } catch (\Throwable $ex) {
            Yii::error("StoryService:applyAdvanceFilter::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
    /**
     * @author Anand Singh
     * @param type $StoryData
     * @return type
     * @throws ErrorException
     * @Description Gets count of the data returned on given filter
     */
      public function advanceFilterDataCount($StoryData){
        
        try {
            $totalCount = TicketCollection::getStoryListCountByAdvanceFilter($StoryData);
            return $totalCount;
        } catch (\Throwable $ex) {
            Yii::error("StoryService:advanceFilterDataCount::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
    /**
     * @author Anand Singh
     * @param type $filterData
     * @return type
     * @throws ErrorException
     * @Description Deletes a filter
     */
    
      public function deleteAdvanceFilter($filterData){
        
        try {
            $deletedRes = PersonalizedFilterCollection::deleteAdvanceFilter($filterData);
            return $deletedRes;
        } catch (\Throwable $ex) {
            Yii::error("StoryService:deleteAdvanceFilter::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
  
      
            
    
   }

