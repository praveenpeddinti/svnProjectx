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
use common\models\mongo\TicketComments;
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
     */
    public function getStoryWorkFlowList(){
        try{
           return WorkFlowFields::getStoryWorkFlowList();
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
                         $fieldBean->value= (int)0; 
                         $fieldBean->value_name= (int)0; 
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
                     if(isset($ticket_data->$fieldId)){
                          if(is_numeric($ticket_data->$fieldId)){
                               $fieldBean->value= (int)$ticket_data->$fieldId;
                              if($fieldId == 4){
                                $details =  PlanLevel::getPlanLevelDetails($ticket_data->$fieldId);
                              }
                              else if($fieldId == 6){
                                    $details = Priority::getPriorityDetails($ticket_data->$fieldId);
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
    public function getMyTicketsCount($userId,$projectId) {
        try {
            $totalCount = TicketCollection::getMyTicketsCount($userId,$projectId);
            
            return $totalCount;
        } catch (Exception $ex) {
            Yii::log("StoryService:getMyTicketsCount::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
       public function getTotalTicketsCount($projectId) {
        try {
            $totalCount = TicketCollection::getTotalTicketsCount($projectId);
            
            return $totalCount;
        } catch (Exception $ex) {
            Yii::log("StoryService:getTotalTicketsCount::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
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
             $userdata =  $ticket_data->userInfo;
             $projectId =  $ticket_data->projectId;
             $userId = $userdata->Id;
             $collaboratorData = Collaborators::getCollboratorByFieldType("Id",$userId);
            // error_log(print_r($collaboratorData,1));
            $ticket_data = $ticket_data->data;
           // error_log(print_r($ticket_data,1));
           // return;
            $ticketCollectionModel = new TicketCollection();
            $ticketDetails = $ticketCollectionModel->getTicketDetails($ticket_data->TicketId, $projectId);
            $ticketDetails["Title"] = trim($ticket_data->title);
            $description = $ticket_data->description;
            $ticketDetails["CrudeDescription"] = $description;
            $ticketDetails["Description"] = CommonUtility::refineDescription($description);

            foreach ($ticketDetails["Fields"] as $key => &$value) {
                 $fieldId =  $value["Id"];
               
                     if(isset($ticket_data->$key)){
                         
                        $fieldDetails =  StoryFields::getFieldDetails($fieldId);
                         if(is_numeric($ticket_data->$key)){
                              $value["value"] = (int)$ticket_data->$key; 
                               if($fieldDetails["Type"] == 6){
                                $collaboratorData = Collaborators::getCollboratorByFieldType("Id",$ticket_data->$key);
                                $value["value_name"] = $collaboratorData["UserName"];
                                $this->followTicket($ticket_data->$key,$ticket_data->TicketId,$projectId,$userId,$fieldDetails["Field_Name"],TRUE);
                                }
                                else if($fieldDetails["Field_Name"] == "workflow"){
                                $workFlowDetail = WorkFlowFields::getWorkFlowDetails($ticket_data->$key);
                                $value["value_name"] = $workFlowDetail["Name"];
                                }
                                else if($fieldDetails["Field_Name"] == "priority"){
                                $priorityDetail = Priority::getPriorityDetails($ticket_data->$key);
                                $value["value_name"] = $priorityDetail["Name"];
                                }
                                else if($fieldDetails["Field_Name"] == "bucket"){
                                $bucketDetail =  Bucket::getBucketName($ticket_data->$key,$projectId);
                                $value["value_name"] = $bucketDetail["Name"];
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
                             }
                            
                             
                         }
                       
                     }
                   
                
             }
             //error_log(print_r($ticketDetails["Fields"],1));
             $collection = Yii::$app->mongodb->getCollection('TicketCollection');
            $collection->save($ticketDetails); 
            
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
            $field_name = $ticket_data->EditedId;
            $field_id = $ticket_data->id;
            $loggedInUser = $ticket_data->userInfo->Id;
            $valueName = "";
            if($checkData==0){
                 if($ticket_data->id=='Title'){
                    $newData = array('$set' => array("Title" => trim($ticket_data->value)));
                    $condition=array("TicketId" => (int)$ticket_data->TicketId,"ProjectId"=>(int)$ticket_data->projectId);
                    $selectedValue=$ticket_data->value;
                }else if($ticket_data->id=='Description'){
                    $actualdescription = CommonUtility::refineDescription($ticket_data->value);
                    $newData = array('$set' => array("Description" => $actualdescription,"CrudeDescription" =>$ticket_data->value ));
                    $condition=array("TicketId" => (int)$ticket_data->TicketId,"ProjectId"=>(int)$ticket_data->projectId);
                    $selectedValue=$actualdescription;
                }
            }else{
                  $fieldDetails =  StoryFields::getFieldDetails($field_id);
                     if(is_numeric($ticket_data->value)){
                         if($fieldDetails["Type"] == 6 ){
                            $collaboratorData = Collaborators::getCollboratorByFieldType("Id",$ticket_data->value);
                            $valueName = $collaboratorData["UserName"]; 
                            $this->followTicket($ticket_data->value,$ticket_data->TicketId,$ticket_data->projectId,$loggedInUser,$fieldDetails["Field_Name"],true);
                            $this->saveActivity($ticket_data->TicketId,$ticket_data->projectId,$fieldDetails["Field_Name"],"",$ticket_data->value,$loggedInUser);
                            }
                        
                             else if($fieldDetails["Field_Name"] == "workflow"){
                                $workFlowDetail = WorkFlowFields::getWorkFlowDetails($ticket_data->value);
                                $valueName = $workFlowDetail["Name"];
                                }
                                else if($fieldDetails["Field_Name"] == "priority"){
                                $priorityDetail = Priority::getPriorityDetails($ticket_data->value);
                                $valueName = $priorityDetail["Name"];
                                }
                                else if($fieldDetails["Field_Name"] == "bucket"){
                                $bucketDetail =  Bucket::getBucketName($ticket_data->value,(int)$ticket_data->projectId);
                                $valueName = $bucketDetail["Name"];
                                }
                                else if($fieldDetails["Field_Name"] == "planlevel"){
                                $planlevelDetail =  PlanLevel::getPlanLevelDetails($ticket_data->value);
                                $valueName = $planlevelDetail["Name"];
                                }
                                else if($fieldDetails["Field_Name"] == "tickettype"){
                                $tickettypeDetail = TicketType::getTicketType($ticket_data->value);
                                $valueName = $tickettypeDetail["Name"];
                                } 
                        
                       
                         $leftsideFieldVal = (int)$ticket_data->value;  
                    }else{
                        if($ticket_data->value != ""){
                            $validDate = CommonUtility::validateDate($ticket_data->value);
                            if($validDate){
                                $leftsideFieldVal = new \MongoDB\BSON\UTCDateTime(strtotime($validDate) * 1000); 
                            }else{
                                $leftsideFieldVal = $ticket_data->value; 
                            } 
                        }else{
                            $leftsideFieldVal = $ticket_data->value;
                        }
                    }
                    $fieldtochange1= "Fields.".$field_name.".value";
                    $fieldtochange2 = "Fields.".$field_name.".value_name";
                    $fieldtochangeId = "Fields.".$field_name.".Id";
                    $newData = array('$set' => array($fieldtochange1 => $leftsideFieldVal,$fieldtochange2 =>$valueName));
                    $condition=array("TicketId" => (int)$ticket_data->TicketId,"ProjectId"=>(int)$ticket_data->projectId,$fieldtochangeId=>(int)$ticket_data->id);
                    $selectedValue=$leftsideFieldVal;

            }
            $updateStaus = $collection->update($condition, $newData); 
           // if($updateStaus==1){
                $returnValue=$selectedValue;
           // }
            return $returnValue;

        } catch (Exception $ex) {
              Yii::log("StoryService:updateStoryFieldInline::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
    public function saveComment($commentData){
        try{
        
        $processedDesc = CommonUtility::refineDescription($commentData->Comment->CrudeCDescription);
        $commentDesc = $commentData->Comment->CrudeCDescription;
//                 $validDate = CommonUtility::validateDate($commentData->Comment->CommentedOn);
//                 error_log("--------------validadte----------".$validDate);
//                            if($validDate){
//                                error_log("in if");
//                                $commentedOn = new \MongoDB\BSON\UTCDateTime(strtotime($validDate) * 1000); 
//                            }else{
//                                error_log("in else");
//                                $commentedOn = $commentData->Comment->CommentedOn; 
//                            }
                            $commentedOn = new \MongoDB\BSON\UTCDateTime(time() * 1000);
//                            error_log("++++++++++++++");
        $commentDataArray=array(
            "Slug"=>new \MongoDB\BSON\ObjectID(),
            "CDescription"=>  $processedDesc,
            "CrudeCDescription"=>$commentDesc,
            "ActivityOn"=>$commentedOn,
            "ActivityBy"=>(int)$commentData->userInfo->Id,
            "Status"=>(int)1,
            "PropertyChanges"=>[],
            "ParentIndex"=>(int)$commentData->Comment->ParentIndex
            
        );
        
        TicketComments::saveComment($commentData->TicketId, $commentData->projectId,$commentDataArray);
        $commentDataArray["userName"]=$commentData->userInfo->username;
        $datetime = $commentDataArray["ActivityOn"]->toDateTime();
        $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
        $readableDate = $datetime->format('m-d-Y H:i:s');
        $commentDataArray["readableDate"]=$readableDate;
        
        return $commentDataArray;
//        error_log("==================");
//        $ticketComment->Comments=[];
//        $populateComment = $ticketComment->Comments;
//        array_push($populateComment, $commentDataArray);
//        $ticketComment->Comments=$populateComment;
//        error_log("++++++++++++++++".print_r($ticketComment,1));
//        $ticketComment->insert();
    }catch(Exception $ex){
        error_log("===========>".$ex->getMessage()."--------->".$ex->getTraceAsString());
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
     public function followTicket($collaboratorId,$ticketId,$projectId,$loggedInUser,$fieldName,$defaultFollower=FALSE){
        
        try {
            //error_log($projectId."---".$ticketId."---".$collaboratorId);
            $db =  TicketCollection::getCollection();
           $currentDate = new \MongoDB\BSON\UTCDateTime(time() * 1000);
           
            $cursor1 =  $db->count( array("ProjectId"=> (int)$projectId ,"TicketId"=> (int)$ticketId, "Followers" => array('$elemMatch'=> array( "FollowerId"=> (int)$collaboratorId))));   
          
         if($cursor1 == 0){
            if($fieldName == "assignedto" || $fieldName == "stakeholder"){
               $cursor =  $db->count( array("ProjectId"=> (int)$projectId ,"TicketId"=> (int)$ticketId, "Followers" => array('$elemMatch'=> array("Flag" =>$fieldName)))); 
           }else{
              $cursor =  $db->count( array("ProjectId"=> (int)$projectId ,"TicketId"=> (int)$ticketId, "Followers" => array('$elemMatch'=> array( "FollowerId"=> (int)$collaboratorId ,"Flag" =>$fieldName))));  
           }
            
             if($cursor == 0){
               $db->findAndModify( array("ProjectId"=> (int)$projectId ,"TicketId"=> (int)$ticketId), array('$addToSet'=> array('Followers' =>array("FollowerId" => (int)$collaboratorId,"FollowedOn" =>$currentDate,"CreatedBy"=>(int)$loggedInUser,"Flag"=>$fieldName,"DefaultFollower"=>(int)$defaultFollower ))),array('new' => 1,"upsert"=>1));
             
             }else{
                 if($fieldName == "assignedto" || $fieldName == "stakeholder"){
                   $newdata = array('$set' => array("Followers.$.FollowerId" => (int)$collaboratorId,"Followers.$.FollowedOn" => $currentDate,"Followers.$.CreatedBy" => (int)$loggedInUser));
                  $db->update(array("ProjectId"=> (int)$projectId ,"TicketId"=> (int)$ticketId,"Followers.Flag"=>$fieldName), $newdata); 
                  }
                 
             } 
             
         }else{
              if($fieldName == "assignedto" || $fieldName == "stakeholder"){
                   $newdata = array('$pull' => array("Followers" => array("Flag"=>$fieldName)));
                  $db->update(array("ProjectId"=> (int)$projectId ,"TicketId"=> (int)$ticketId,"Followers.Flag"=>$fieldName), $newdata,array('new' => 0,"upsert"=>0)); 
              }
             
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
            $fieldsOrderArray = [5,6,7,3,10];
           //  $fieldsOrderArray = [10,11,12,3,4,5,6,7,8,9];
            foreach ($ticketDetails as $ticket) {
                $details = CommonUtility::prepareDashboardDetails($ticket, $projectId,$fieldsOrderArray);
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
           error_log("+=+=+=+=+=+=+=+=+".print_r($ticketActivity,1));
           $tinyUserModel =  new TinyUserCollection();
           foreach ($ticketActivity["Activities"] as &$value) {
               $userProfile = $tinyUserModel->getMiniUserDetails($value["ActivityBy"]);
                $value["userName"] = $userProfile["UserName"];
                $datetime = $value["ActivityOn"]->toDateTime();
                $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                $readableDate = $datetime->format('m-d-Y H:i:s');
                $value["readableDate"]=$readableDate;
           }
           return $ticketActivity;
            
        } catch (Exception $ex) {
    Yii::log("StoryService:getTicketActivity::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
    public function saveActivity($ticketId,$projectId,$actionfieldName,$oldValue,$newValue,$activityUserId){
        error_log("saveActivity---------");
        
        $ticketDetails = TicketCollection::getTicketDetails($ticketId,$projectId);  
        $oldValue = $ticketDetails["Fields"][$actionfieldName]["value"];
        error_log("old value--------".$oldValue);
        if($oldValue == ""){
            $action = "set to";
        }else{
            $action = "changed from"; 
        }
           $db =  TicketComments::getCollection();
           $currentDate = new \MongoDB\BSON\UTCDateTime(time() * 1000);
          $record = $db->findOne( array("ProjectId"=> (int)$projectId ,"TicketId"=> (int)$ticketId));
         //  $record = iterator_to_array($record);
         //  error_log(print_r($record,1));
         $slug =  new \MongoDB\BSON\ObjectID();
         if($record["RecentActivityUser"] != $activityUserId ){
            error_log("iffffffffffffff----------------");
            // $dataArray = array();
             $commentDataArray=array(
            "Slug"=>$slug,
            "CDescription"=>  "",
            "CrudeCDescription"=>"",
            "ActivityOn"=>$currentDate,
            "ActivityBy"=>(int)$activityUserId,
            "Status"=>(int)1,
            "PropertyChanges"=>array(array("ActionFieldName" => $actionfieldName,"Action" => $action ,"PreviousValue" =>(int)$oldValue,"NewValue"=>(int)$newValue,"CreatedOn" => $currentDate)),
            "ParentIndex"=>(int)0
            
        );

            $v = $db->findAndModify( array("ProjectId"=> (int)$projectId ,"TicketId"=> (int)$ticketId), array('$addToSet'=> array('Activities' =>$commentDataArray)));  
            $v = $db->update( array("ProjectId"=> (int)$projectId ,"TicketId"=> (int)$ticketId), array("RecentActivitySlug"=>$slug,"RecentActivityUser"=>(int)$activityUserId));  
         }else{
             error_log("elseeeeeeeeeee----------------");
             $recentSlug = $record["RecentActivitySlug"];
             $v = $db->findAndModify( array("ProjectId"=> (int)$projectId ,"TicketId"=> (int)$ticketId,"Activities.Slug"=>$recentSlug), array('$addToSet'=> array('Activities.$.PropertyChanges' =>array("ActionFieldName" => $actionfieldName,"Action" => $action ,"PreviousValue" =>(int)$oldValue,"NewValue"=>(int)$newValue,"CreatedOn" => $currentDate ))),array('new' => 1,"upsert"=>1));
 
         }
          //error_log("response-------".$v);
    }
      

    /**
     * @author Suryaprakash
     * @param type $parentTicNumber
     * @param type $ticketnoArray
     * @return empty
     */
    public function updateParentTicketTaskField($parentTicNumber, $ticketnoArray) {
        try {
            $ticketDetails = TicketCollection::updateParentTicketTaskField($parentTicNumber, $ticketnoArray);
        } catch (Exception $ex) {
            Yii::log("StoryService:updateParentticketTask::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

}

  