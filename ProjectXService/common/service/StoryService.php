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

       public function saveTicketDetails($ticket_data) {
        try {

            error_log("@@@@@@@@@@@@@@@@@@@@@2----------------".print_r($ticket_data,1));
            
              $ticket_data = $ticket_data->storyData;

           // error_log("@@@@@@@@@@@@@@@@@@@@@2----------------".print_r($ticket_data,1));
             $userdata =  $ticket_data->userInfo;
             $projectId =  $ticket_data->projectId;
             //error_log("projectId------------".$projectId);
             $userId = $userdata->Id;
             $collaboratorData = Collaborators::getCollboratorByFieldType("Id",$userId);
             error_log(print_r($collaboratorData,1));
              $ticket_data = $ticket_data->data;
              $dataArray = array();
              $fieldsArray = array();
              $title =  $ticket_data->title;
              $description =  $ticket_data->description;
              
              //error_log("%%%%%%%%%%%%%%%%5 ---------------- ".$description);
              $matches=[];
              //preg_match_all("/\[\[\w+:\w+(\|[A-Z0-9\s-_+#$%^&()*a-z]+\.\w+)*\]\]/",$str,$matches); // after uploades/
              preg_match_all("/\[\[\w+:\w+\/\w+(\|[A-Z0-9\s-_+#$%^&()*a-z]+\.\w+)*\]\]/", $str, $matches);
              error_log("the descriptoin pregmatchcount ".count($matches[0]." --- ". print_r($matches, 1)));
//              for($i = 0; $i< count($matches); $i++){
//                  
//              }
              
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
                         $fieldBean->value= new \MongoDB\BSON\UTCDateTime(time() * 1000); 
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
                      array_push($dataArray, $fieldBean);
                  }

           $ticketModel = new TicketCollection();
           $ticketModel->Title = $title;
           $ticketModel->Description = $description;
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
      public function getAllStoryDetails($projectId) {
        try {
         $model = new TicketCollection();
         $ticketDetails = $model->getAllTicketDetails($projectId);
        // $ticketDetails =array(101,200,201,202,203,204,205,206,207,208,209,210);
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
}

  