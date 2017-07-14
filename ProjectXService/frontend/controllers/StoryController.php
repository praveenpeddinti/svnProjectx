<?php
namespace frontend\controllers;

use Yii;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\components\CommonUtility;
use common\models\bean\ResponseBean;
use common\components\ServiceFactory;
use common\models\mongo\TinyUserCollection;
use common\models\mongo\NotificationCollection;
/**
 * Story Controller
 */
class StoryController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
           
        ];
    }

    

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function beforeAction($action) {
    $this->enableCsrfValidation = false;
    return parent::beforeAction($action);
    }
   /**
    * @author Moin Hussain
    * @description This method to get a ticket details.
    * @return type
    */
    public function actionGetTicketDetails(){
        try{
        $ticket_data = json_decode(file_get_contents("php://input"));
        $data = ServiceFactory::getStoryServiceInstance()->getTicketDetails($ticket_data);
        $responseBean = new ResponseBean();
        if(is_array($data)){
            $responseBean->statusCode = ResponseBean::SUCCESS;
           $responseBean->message = ResponseBean::SUCCESS_MESSAGE; 
        }else{
             $message = $data."_MESSAGE";
             $responseBean->statusCode = ResponseBean::getConstant($data);
             $responseBean->message = ResponseBean::getConstant($message);
        }
       
        $responseBean->data = $data;
        $response = CommonUtility::prepareResponse($responseBean,"json");
        return $response;
        } catch (Exception $ex) {
     Yii::log("StoryController:actionGetTicketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    

    /**
    * @author Praveen P
    * @description This method is used to get all data for stories/tasks.
    * @return type
    */
   public function actionGetAllStoryDetails() {
        try {
            $StoryData = json_decode(file_get_contents("php://input"));
            //$projectId=1;
            $projectId = $StoryData->projectId;
            $totalCount = ServiceFactory::getStoryServiceInstance()->getAllStoriesCount($StoryData,$projectId);
            $data = ServiceFactory::getStoryServiceInstance()->getAllStoryDetails($StoryData, $projectId);

            $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = $data;
            $responseBean->totalCount = $totalCount;
            $response = CommonUtility::prepareResponse($responseBean, "json");
            return $response;
        } catch (Exception $ex) {
            Yii::log("StoryController:actionGetAllStoryDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

    /**
    * @author Praveen P
    * @description This method is used to getting subtask details for the particular story.
    * @return type subtasks
    */
   public function actionGetSubTaskDetails() {
        try {
            $StoryData = json_decode(file_get_contents("php://input"));
            //$projectId=1;
            $subtaskIds=array();
            $projectId = $StoryData->projectId;
             $timezone = $StoryData->timeZone;
            $getSubTaskIds = ServiceFactory::getStoryServiceInstance()->getSubTaskIds($StoryData->storyId,$projectId);
            foreach($getSubTaskIds['Tasks'] as $task){
               array_push($subtaskIds,$task['TaskId']);
            }
            $data = ServiceFactory::getStoryServiceInstance()->getSubTaskDetails($subtaskIds, $projectId,$timezone);
            $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = $data;
            $response = CommonUtility::prepareResponse($responseBean, "json");
            return $response;
        } catch (Exception $ex) {
            Yii::log("StoryController:actionGetTicketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    /**
    * @author Moin Hussain
    * @description This method is used to get data for edit mode.
    * @return type
    */
    public function actionEditTicket(){
        try{

        $ticket_data = json_decode(file_get_contents("php://input"));
        $data['ticket_details'] = ServiceFactory::getStoryServiceInstance()->getTicketEditDetails($ticket_data);
        if(!empty($data['ticket_details']))
        $data['task_types'] = ServiceFactory::getStoryServiceInstance()->getTaskTypes();
        else
         $data='NOTFOUND';   
        $responseBean = new ResponseBean();
        if(is_array($data)){
            $responseBean->statusCode = ResponseBean::SUCCESS;
           $responseBean->message = ResponseBean::SUCCESS_MESSAGE; 
        }else{
             $message = $data."_MESSAGE";
             $responseBean->statusCode = ResponseBean::getConstant($data);
             $responseBean->message = ResponseBean::getConstant($message);
        }
        $responseBean->data = $data;
        $response = CommonUtility::prepareResponse($responseBean,"json");
        return $response;
        } catch (Exception $ex) {
     Yii::log("StoryController:actionEditTicket::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    

   /**
    * @author Moin Hussain
    * @return string
    * @updated suryaprakash for defualt child ticket insertions
    */
    public function actionSaveTicketDetails() {
        try {
            $ticket_data = json_decode(file_get_contents("php://input"));
            $title = $ticket_data->data->title;
            $description = $ticket_data->data->description;
            $planLevelNumber = $ticket_data->data->planlevel;
            $ticket_data->data->WorkflowType = (int)1;
            //story-1 or task-2
            $parentTicNumber = ServiceFactory::getStoryServiceInstance()->saveTicketDetails($ticket_data);
            $projectId=$ticket_data->projectId;
            if ($planLevelNumber == 1) {
                $WorkflowType=(int)1;  
                $defaultTicketsArray = $ticket_data->data->default_task;
                $childTicketObjArray = array();
                foreach($defaultTicketsArray as $value){
                    $ticket_data->data->description = "<p>Please provide description here</p>";
                    $ticket_data->data->planlevel = 2;
                    $ticket_data->data->title = $value->Name."-" . $title;
                    $ticket_data->data->WorkflowType = (int)$value->Id;
                    $ticketNumber = ServiceFactory::getStoryServiceInstance()->saveTicketDetails($ticket_data,$parentTicNumber);
                    array_push($childTicketObjArray, array("TaskId"=>$ticketNumber,"TaskType"=>(int)$value->Id));
                    }
                $updateParentTaskArray = ServiceFactory::getStoryServiceInstance()->updateParentTicketTaskField($projectId,$parentTicNumber,$childTicketObjArray);
                ServiceFactory::getStoryServiceInstance()->saveUserPreferences($ticket_data->userInfo->Id,$defaultTicketsArray); //added by Ryan
                }
            $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = "success";
            $response = CommonUtility::prepareResponse($responseBean,"json");
            return $response;
        } catch (Exception $ex) {
            error_log("the save ticket error " . ($ex->getMessage()));
            Yii::log("StoryController:saveTicketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

    /**
     * @description This method to get a template for story creation
     * @modified Moin Hussain
     * @author Anand Singh
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionNewStoryTemplate(){
        try{
        $post_data = json_decode(file_get_contents("php://input"));
        $timezone = $post_data->timeZone;
        $projectId = $post_data->projectId;
        $response_data['story_fields'] = ServiceFactory::getStoryServiceInstance()->getNewTicketStoryFields();
        $response_data['task_types'] = ServiceFactory::getStoryServiceInstance()->getTaskTypes();
        foreach ($response_data['story_fields'] as &$storyField){
           $fieldType = $storyField["Type"];
            $fieldName= $storyField["Field_Name"];
          if($fieldName == "bucket"){
              
              $storyField["data"] = ServiceFactory::getStoryServiceInstance()->getBucketsList($projectId);
           }
          else if($fieldName == "priority"){
              
              $storyField["data"] = ServiceFactory::getStoryServiceInstance()->getPriorityList();
           }
          else if($fieldName == "planlevel"){
                
             $storyField['data'] = ServiceFactory::getStoryServiceInstance()->getPlanLevelList();
           }
          else if($fieldName == "workflow"){
                $storyField['data'] = ServiceFactory::getStoryServiceInstance()->getStoryWorkFlowList();
            }
          else if($fieldName == "tickettype"){
                   
             $storyField['data'] = ServiceFactory::getStoryServiceInstance()->getTicketTypeList();
           }
          else if($fieldType == 4){
             $storyField['DefaultValue'] = CommonUtility::convert_date_zone(strtotime(date("m-d-Y H:i:s")), $timezone);
           }
          else if($fieldType == 5){
             $storyField['DefaultValue'] = CommonUtility::convert_time_zone(strtotime(date("m-d-Y H:i:s")), $timezone);
           }
            $storyField["Field_Type"] =  $storyField["Name"];
            
        }
       // $response_data['collaborators'] = ServiceFactory::getCollaboratorServiceInstance()->getProjectTeam($projectId);

        $responseBean = new ResponseBean;
        $responseBean->statusCode = ResponseBean::SUCCESS;
        $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
        $responseBean->data =$response_data;
        $response = CommonUtility::prepareResponse($responseBean,"json");
        return $response;   
       
        } catch (Exception $ex) {
         Yii::log("StoryController:actionNewStoryTemplate::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

 /**
    * @author Moin Hussain
    * @description This method to get a ticket details.
    * @return type
    */
    public function actionGetMyTickets(){
        try{
        $data = ServiceFactory::getStoryServiceInstance()->getMyTickets(101,1);
        $responseBean = new ResponseBean();
        $responseBean->statusCode = ResponseBean::SUCCESS;
        $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
        $responseBean->data = $data;
        $response = CommonUtility::prepareResponse($responseBean,"json");
        return $response;
        } catch (Exception $ex) {
     Yii::log("StoryController:actionGetTicketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    /*
    * @author Padmaja
    * @description This method to get Project details by FieldId.
    * @return type Json
    */

    public function actionGetFieldDetailsByFieldId(){
        try{
            $postFieldData = json_decode(file_get_contents("php://input"));
            $responseBean = new ResponseBean();
          //  $responseData['story_fields'] = ServiceFactory::getStoryServiceInstance()->getStoryFieldDataById(5);
            if($postFieldData->fieldId == 5 || $postFieldData->fieldId == 11){
            // get all assigned to details,stakeholeders
                $responseData['getFieldDetails'] = ServiceFactory::getCollaboratorServiceInstance()->getProjectTeam($postFieldData->projectId);//$projectId
            }else if($postFieldData->fieldId == 3){
                // get all Bucket details
                $responseData['getFieldDetails'] = ServiceFactory::getStoryServiceInstance()->getBucketsList($postFieldData->projectId);//$projectId
            }else if($postFieldData->fieldId == 4){
            //get all planlevel details
                $responseData['getFieldDetails'] = ServiceFactory::getStoryServiceInstance()->getPlanLevelList();
            }else if($postFieldData->fieldId == 7){
            //get all status details
                $responseData['getFieldDetails'] = ServiceFactory::getStoryServiceInstance()->getStoryWorkFlowList($postFieldData->workflowType,$postFieldData->statusId);
            }else if($postFieldData->fieldId == 6){
            //get all priority details
                $responseData['getFieldDetails'] = ServiceFactory::getStoryServiceInstance()->getPriorityList();
            }else if($postFieldData->fieldId == 12){
            //get all ticket type details
             $responseData['getFieldDetails'] = ServiceFactory::getStoryServiceInstance()->getTicketTypeList();       
            }
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = $responseData;
            $response = CommonUtility::prepareResponse($responseData,"json");
            return $response;
        } catch (Exception $ex) {
            Yii::log("StoryController:actionGetAssignedCollabarators::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
   /**
    * @author Moin Hussain
    * @return string
    */
    public function actionUpdateTicketDetails(){
        try{
            $ticket_data = json_decode(file_get_contents("php://input"));
            $title=$ticket_data->data->title;
            $projectId=$ticket_data->projectId;
            $parentTicNumber=$ticket_data->data->ticketId;
            $data = ServiceFactory::getStoryServiceInstance()->updateTicketDetails($ticket_data);
            if($ticket_data->data->default_task != null){
            $defaultTicketsArray = $ticket_data->data->default_task;
                $childTicketObjArray = array();
                 foreach($defaultTicketsArray as $value){
                    $default_ticket_data = array();
                    $default_ticket_data['userInfo']=$ticket_data->userInfo;
                    $default_ticket_data['projectId']=$ticket_data->projectId;
                    $default_ticket_data['data']['priority']=$ticket_data->data->priority;
                    $default_ticket_data['data']['description']= "<p>Please provide description here</p>";
                    $default_ticket_data['data']['planlevel'] = 2;
                    $default_ticket_data['data']['WorkflowType'] = (int)$value->Id; 
                    $default_ticket_data['data']['title'] = $value->Name."-" . $title;
                    $default_ticket_data= json_decode(json_encode($default_ticket_data,true));
                    $ticketNumber = ServiceFactory::getStoryServiceInstance()->saveTicketDetails($default_ticket_data, $parentTicNumber);
                    array_push($childTicketObjArray, array("TaskId"=>$ticketNumber,"TaskType"=>(int)$value->Id));
                    }
                $updateParentTaskArray = ServiceFactory::getStoryServiceInstance()->updateParentTicketTaskField($projectId,$parentTicNumber,$childTicketObjArray);     
                  
            }      
           $responseBean = new ResponseBean();
           $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = "success";
            $response = CommonUtility::prepareResponse($responseBean,"json");
             return $response;
        } catch (Exception $ex) {
        Yii::log("StoryController:actionUpdateTicketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
    /*
     * @author Padmaja
     * @description This method to update the story feilds.
    * @return type Json
     */
    public function actionUpdateStoryFieldInline(){
        try{
            $fieldData = json_decode(file_get_contents("php://input"));
            $getUpdateStatus = ServiceFactory::getStoryServiceInstance()->updateStoryFieldInline($fieldData);
            if($getUpdateStatus !='failure'){
                $responseBean = new ResponseBean();
                $responseBean->statusCode = ResponseBean::SUCCESS;
                $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
                $responseBean->data = $getUpdateStatus;
                $response = CommonUtility::prepareResponse($responseBean,"json");
            }else{
                $response='failure';
                $responseBean = new ResponseBean;
                $responseBean->status = ResponseBean::FAILURE;
                $responseBean->message = "FAILURE";
                $responseBean->data =    $getUpdateStatus;
                $response = CommonUtility::prepareResponse($responseBean,"json");
            }        
            return $response;
        } catch (Exception $ex) {
            Yii::log("StoryController:actionUpdateStoryFieldInline::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }

        
         
    }

    
    /**
    * @author Moin Hussain
    * @description This method is used to get all data for stories/tasks.
    * @return type
    */
   public function actionGetMyTicketsDetails() {
        try {
            $StoryData = json_decode(file_get_contents("php://input"));
             $userdata =  $StoryData->userInfo;
             $userId = $userdata->Id;
             $projectId =  $StoryData->projectId;
             $sortorder =  $StoryData->sortorder;
             $sortvalue = $StoryData->sortvalue;
             $pageLength = $StoryData->pagesize;
             $offset = $StoryData->offset;
             $timezone = $StoryData->timeZone;
            
            $totalCount = ServiceFactory::getStoryServiceInstance()->getMyTicketsCount($userId,$projectId);
            $data = ServiceFactory::getStoryServiceInstance()->getAllMyTickets($userId,$sortorder,$sortvalue,$offset,$pageLength,$projectId,$timezone);

            $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = $data;
            $responseBean->totalCount = $totalCount;
            $response = CommonUtility::prepareResponse($responseBean, "json");
            return $response;
        } catch (Exception $ex) {
            Yii::log("StoryController:actionGetMyTicketsDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');

        }

    }
    
       
     
    
    /*
     * @author Ryan
     * @description This method is used to get all the collaborators.
    * @return type Json
     */
    public function actionGetCollaborators()
    {
        try
        {
            $post_data=json_decode(file_get_contents("php://input"));
            $team=ServiceFactory::getCollaboratorServiceInstance()->getFilteredProjectTeam($post_data->projectId,$post_data->search_term);
            $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = $team;
            $response = CommonUtility::prepareResponse($responseBean,"json");
             return $response;
        } catch (Exception $ex) {
             Yii::log("StoryController:actionGetCollaborators::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }

    }


    
    /*
    * @author Praveen P
    * @description This method to get Follower list.
    * @return type Json
    */
    public function actionGetCollaboratorsForFollow() {
        try {
            $StoryData = json_decode(file_get_contents("php://input"));
            //$projectId=1;
            $projectId = $StoryData->projectId;
            $searchValue = $StoryData->searchValue;
            $ticketId = $StoryData->ticketId;
            $followerlist=ServiceFactory::getCollaboratorServiceInstance()->getCollaboratorsForFollow($ticketId,$searchValue,$projectId);
            $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = $followerlist;
            $response = CommonUtility::prepareResponse($responseBean, "json");
            return $response;
        } catch (Exception $ex) {
            Yii::log("StoryController:actionGetCollaboratorsforFollow::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }


   public function actionSubmitComment(){
       $comment_post_data=json_decode(file_get_contents("php://input"));
       error_log(print_r($comment_post_data,1));
       
                if(isset($comment_post_data->Comment->OrigianalCommentorId)){
                $comment_post_data->Comment->OriginalCommentorId=$comment_post_data->Comment->OrigianalCommentorId;
            }
       $returnData = ServiceFactory::getStoryServiceInstance()->saveComment($comment_post_data);
       $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = $returnData;
            $response = CommonUtility::prepareResponse($responseBean,"json");
             return $response;
   }
   
   public function actionDeleteComment(){
       $comment_post_data=json_decode(file_get_contents("php://input"));
       error_log(print_r($comment_post_data,1));
       $returnData = ServiceFactory::getStoryServiceInstance()->removeComment($comment_post_data);
       $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = "success";
            $response = CommonUtility::prepareResponse($responseBean,"json");
             return $response;
   }
    /**
     * @author Moin Hussain
     * @return type
     */
    public function actionGetTicketActivity(){
        try{
            $post_data=json_decode(file_get_contents("php://input"));
            $data = ServiceFactory::getStoryServiceInstance()->getTicketActivity($post_data);
            $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = $data;
            $response = CommonUtility::prepareResponse($responseBean,"json");
             return $response;
        } catch (Exception $ex) {
 Yii::log("StoryController:actionGetTicketActivity::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

    /*
    * @author Praveen P
    * @description This method to add and remove followers in Story Details.
    * @return type Json
    */
   public function actionFollowTicket() {
        try {
            $post_data = json_decode(file_get_contents("php://input"));
            $followers_pics = array();
            //save followers to Ticket
          
               ServiceFactory::getStoryServiceInstance()->followTicket($post_data->collaboratorId, $post_data->ticketId, $post_data->projectId, $post_data->userInfo->Id, "follower");
               $activityData= ServiceFactory::getStoryServiceInstance()->saveActivity($post_data->ticketId, $post_data->projectId, 'Followed', $post_data->collaboratorId, $post_data->userInfo->Id,"",$post_data->timeZone);
               $collaboratorData =  TinyUserCollection::getMiniUserDetails($post_data->collaboratorId);
               $followerData = array();
               $followerData["ProfilePicture"] = $collaboratorData["ProfilePicture"];
               $followerData["UserName"] = $collaboratorData["UserName"];
               $followerData["FollowerId"] =$post_data->collaboratorId;
               $followerData["CreatedBy"] =$post_data->userInfo->Id;
               $followerData["Flag"] ="follower";
               $followerData["DefaultFollower"] =0;
               $followerData['activityData']=$activityData;
              
              ServiceFactory::getStoryServiceInstance()->saveNotifications($post_data, 'add',  $post_data->collaboratorId,"FollowObj");
               /* notifications end */
            
            $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = $followerData;
            $response = CommonUtility::prepareResponse($responseBean, "json");
            return $response;
        } catch (Exception $ex) {
            Yii::log("StoryController:actionAddFollower::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

    public function actionUnfollowTicket() {
        try {
            $post_data = json_decode(file_get_contents("php://input"));
            //remove followers to Ticket
            ServiceFactory::getStoryServiceInstance()->unfollowTicket($post_data->collaboratorId, $post_data->ticketId, $post_data->projectId);
            $activitydata =  ServiceFactory::getStoryServiceInstance()->saveActivity($post_data->ticketId, $post_data->projectId, 'Unfollowed', $post_data->collaboratorId, $post_data->userInfo->Id,"",$post_data->timeZone);
            $collaboratorData =  TinyUserCollection::getMiniUserDetails($post_data->collaboratorId);//added by Ryan
            ServiceFactory::getStoryServiceInstance()->saveNotifications($post_data, 'remove', $post_data->collaboratorId,'',"FollowObj",$taskId=0);
               /* notifications end */
            $UnfollowData['collaboratorId']  = $post_data->collaboratorId;
            $UnfollowData['activityData']  = $activitydata;
            $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = $UnfollowData;
            $response = CommonUtility::prepareResponse($responseBean, "json");
            return $response;
        } catch (Exception $ex) {
            Yii::log("StoryController:actionAddFollower::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
    /*
     * @author Padmaja
     * @description This method is used to save child task details.
    * @return type Json
     */
    public function actionCreateChildTask(){
        try{
            $postData= json_decode(file_get_contents("php://input")); 
            $responseData = ServiceFactory::getStoryServiceInstance()->createChildTask($postData);
            if($responseData !='failure'){
                $responseBean = new ResponseBean();
                $responseBean->statusCode = ResponseBean::SUCCESS;
                $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
                $responseBean->data = $responseData;
                $response = CommonUtility::prepareResponse($responseBean,"json");
            }else{
                $response='failure';
                $responseBean = new ResponseBean;
                $responseBean->status = ResponseBean::FAILURE;
                $responseBean->message = "FAILURE";
                $responseBean->data =    $responseData;
                $response = CommonUtility::prepareResponse($responseBean,"json");
            } 
             return $response;
        } catch (Exception $ex) {
             Yii::log("StoryController:actionCreateChildTask::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

 
    
      /**
    * @author Padmaja 
    * @updated  suryaprakash 
    * @description This method is used to get all data for stories/tasks.
    * @return type
    */
   public function actionGetAllTicketDetailsForSearch() {
        try {
            $storyData = json_decode(file_get_contents("php://input"));
            $projectId = $storyData->projectId;
            $ticketId = $storyData->ticketId;
            $sortvalue = $storyData->sortvalue;
            $searchString = $storyData->searchString;
            
           // $totalCount = ServiceFactory::getStoryServiceInstance()->getTotalTicketsCount($projectId);
            $data = ServiceFactory::getStoryServiceInstance()->getAllStoryDetailsForSearch($projectId,$ticketId,$sortvalue, $searchString);

            $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = $data;
           // $responseBean->totalCount = $totalCount;
            $response = CommonUtility::prepareResponse($responseBean, "json");
            return $response;
        } catch (Exception $ex) {
            Yii::log("StoryController:actionGetTicketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
      /**
    * @author  suryaprakash 
    * @description This method is used to get all data for stories/tasks.
    * @return type
    */
    public function actionUpdateRelatedTasks(){
        try{
          $storyData = json_decode(file_get_contents("php://input"));
          $projectId = $storyData->projectId;
          $ticketId = $storyData->ticketId;
          $searchTicketId = $storyData->relatedSearchTicketId;
          $loginUserId=$storyData->userInfo->Id;
          $timezone = $storyData->timeZone;
          $bucketId = $storyData->bucketId;
          $pdateRelateTask = ServiceFactory::getStoryServiceInstance()->updateRelatedTaskId($projectId,$ticketId,$searchTicketId,$loginUserId);
          $activityData= ServiceFactory::getStoryServiceInstance()->saveActivity($ticketId, $projectId,'Related', (int)$searchTicketId, $loginUserId,"",$timezone);
          $notifyType="Relate";
          $slug =  new \MongoDB\BSON\ObjectID();
          ServiceFactory::getStoryServiceInstance()->saveNotifications($storyData, $notifyType,'','',$slug,'',(int)$searchTicketId);
          ServiceFactory::getStoryServiceInstance()->saveEvent($projectId,"Ticket",$ticketId,"related",'relate',$loginUserId,array("ActionOn"=>  strtolower("relatetask"),"OldValue"=>0,"NewValue"=>(int)$searchTicketId),array("BucketId"=>(int)$bucketId));
          $ticketData = ServiceFactory::getStoryServiceInstance()->getAllRelateStory($projectId,$ticketId);
          $responseData=array('ticketData'=>$ticketData,'activityData'=>$activityData);
          $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = $responseData;
            $response = CommonUtility::prepareResponse($responseBean, "json");
             return $response;
        } catch (Exception $ex) {
            Yii::log("StoryController:actionUpdateRelatedTasks::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }

    }
    
      /**
     * @author suryaprakash reddy 
     * @description This method is used to insertTimelog
     * @return type array
     */
    public function actionInsertTimeLog() {
        try {
            $response=array();
            $timelog_data = json_decode(file_get_contents("php://input"));
            $insertTimelog = ServiceFactory::getStoryServiceInstance()->insertTimeLog($timelog_data);
            $projectId = $timelog_data->projectId;
            $parentTicketId = $timelog_data->ticketId;
            $updateTimeLog = ServiceFactory::getStoryServiceInstance()->getTimeLog($projectId, $parentTicketId);
            $response['timeLogData']=$updateTimeLog;
            $response['activityData']=$insertTimelog;
            $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = $response;
            $response = CommonUtility::prepareResponse($responseBean, "json");
            return $response;
        } catch (Exception $ex) {
            Yii::log("StoryController:actionInsertTimelog::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

    /**
     * @author suryaprakash reddy 
     * @description This method is used to getworklog
     * @return type array
     */
    public function actionGetWorkLog() {
        try {
            $timelog_data = json_decode(file_get_contents("php://input"));
            $projectId = $timelog_data->projectId;
            $parentTicketId = $timelog_data->ticketId;
//             $projectId = 1;
//            $parentTicketId = 497;
            $getTimelog = ServiceFactory::getStoryServiceInstance()->getTimeLog($projectId, $parentTicketId);
            $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = $getTimelog;
            $response = CommonUtility::prepareResponse($responseBean, "json");
            return $response;
        } catch (Exception $ex) {
            Yii::log("StoryController:actionGetWorkLog::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
            /**
     * @author Jagadish
     * @description This method is used to get all attachments for stories/tasks.
     * @return Attachmets
     */
    public function actionGetMyTicketAttachments() {
        try {
            $post_data = json_decode(file_get_contents("php://input"));
            $timezone = $post_data->timeZone;
            $Artifacts = ServiceFactory::getStoryServiceInstance()->getTicketAttachments($post_data->ticketId, $post_data->projectId);
            $tinyUserModel = new TinyUserCollection();
            $Artifacts = $Artifacts["Artifacts"];
            if(!empty($Artifacts)){ // For newly created child task if no artifacts.
              foreach ($Artifacts as $key => $Artifact) {
                if($Artifact["FileName"] != ""){
                  $Artifacts[$key]["FileName"] = Yii::$app->params['ServerURL'].Yii::$app->params['StoryArtifactPath']."/".$Artifact["FileName"].".".$Artifact["Extension"];
                }
                if ($Artifact["UploadedBy"] != "") {
                    $userName = $tinyUserModel->getMiniUserDetails($Artifact["UploadedBy"]);
                    $Artifacts[$key]["UploadedBy"] = $userName["UserName"];
                } else {
                    $Artifacts[$key]["UploadedBy"] = "";
                }
                if ($Artifact["UploadedOn"] != "") {
                    $datetime = $Artifact["UploadedOn"]->toDateTime();
                    $datetime->setTimezone(new \DateTimeZone($timezone));
                    $readableDate = $datetime->format('M-d-Y H:i:s');
                    $Artifacts[$key]["UploadedOn"] = $readableDate;
                } else {
                    $Artifacts[$key]["UploadedOn"] = "";
                }
            }  
            }else{
             $Artifacts=array();
            }
            $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = $Artifacts;
            $response = CommonUtility::prepareResponse($responseBean, "json");
            return $response;
        } catch (Exception $ex) {
            Yii::log("StoryController:actionGetMyTicketAttachments::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
      /**
     * @author suryaprakash reddy
     * @description unrelate ticket from Parent story
     * @return relatedTicketsInfo
     */
    public function actionUnRelateTask() {


        try {
            $response=array();
            $ticketData = json_decode(file_get_contents("php://input"));
            $projectId = $ticketData->projectId;
            $parentTicketId = $ticketData->ticketId;
            $unRelateTicketId = $ticketData->unRelateTicketId;
            $loginUserId=$ticketData->userInfo->Id;
            $timezone=$ticketData->timeZone;
            $bucketId=$ticketData->bucketId;
            $response['activityData'] = ServiceFactory::getStoryServiceInstance()->unRelateTask($projectId, $parentTicketId, $unRelateTicketId,$loginUserId,$timezone);
            $notifyType="UnRelate";
            $slug =  new \MongoDB\BSON\ObjectID();
            ServiceFactory::getStoryServiceInstance()->saveNotifications($ticketData, $notifyType,'','',$slug,'',$unRelateTicketId);
            ServiceFactory::getStoryServiceInstance()->saveEvent($projectId,"Ticket",$parentTicketId,"unrelated",'unrelate',$loginUserId,array("ActionOn"=>  strtolower("unrelatetask"),"OldValue"=>0,"NewValue"=>(int)$unRelateTicketId),array("BucketId"=>(int)$bucketId));
            $response['ticketInfo'] = ServiceFactory::getStoryServiceInstance()->getAllRelateStory($projectId, $parentTicketId);
            $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = $response;
            $response = CommonUtility::prepareResponse($responseBean, "json");
            return $response;
        } catch (Exception $ex) {
            Yii::log("StoryController:actionUnRelateTask::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
       /**
     * @author suryaprakash reddy
     * @description getall related tasks for Parent
     * @return relatedTicketsInfo
     */
    public function actionGetAllRelatedTasks() {
        try {
            $ticketData = json_decode(file_get_contents("php://input"));
            $projectId = $ticketData->projectId;
            $parentTicketId = $ticketData->ticketId;
            $relatedTicketsInfo = ServiceFactory::getStoryServiceInstance()->getAllRelateStory($projectId, $parentTicketId);
            $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = $relatedTicketsInfo;
            $response = CommonUtility::prepareResponse($responseBean, "json");
            return $response;
        } catch (Exception $ex) {
            Yii::log("StoryController:actionGetAllRelatedTasks::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

   
    /**
     * @author Anand
     * @uses Get all bucket details for current project
     * @return type
     */
    
    public function actionGetFilterOptions(){
        try{
        $postData = json_decode(file_get_contents("php://input"));
        $projectId = $postData->projectId;
        $options=array();
        $options['Filters'] = ServiceFactory::getStoryServiceInstance()->getFilterOptions();
        $options['Buckets'] = ServiceFactory::getStoryServiceInstance()->getBucketsList($projectId);
        $preparedFilters = CommonUtility::prepareFilterOption($options);
        $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = $preparedFilters;
            $response = CommonUtility::prepareResponse($responseBean, "json");
      return $response;  
        } catch (Exception $ex) {
            Yii::log("StoryController:actionGetFilterOptions::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
        
    }
 

        
    
    /**
     * @author Ryan
     * @uses Deleting Specific Notification
     * @return type
     */
    public function actionDeleteNotification()
    {
        $notifyData=json_decode(file_get_contents("php://input"));
        
        try
        {
            NotificationCollection::deleteNotification($notifyData);
            
            $projectId=$notifyData->projectId;
            $notified_userid=$notifyData->userInfo->Id;
            $notified_username=$notifyData->userInfo->username;
            $viewAll=$notifyData->viewAll;
            $page=$notifyData->page;
            $limit=5;
            if($viewAll==0){
             
          
            //$result_data=NotificationCollection::getNotifications($notified_username,$projectId);
            $result_data=ServiceFactory::getStoryServiceInstance()->getNotifications($notified_userid,$projectId,0,$limit);

            $count = NotificationCollection::getNotificationsCount($notified_userid,$projectId);
            $result =  count($result_data)>0 ? $result_data : "nodata";
             $responseData = array('notify_result'=>$result);
            }else{
                  $count = 0;
                  $responseData = "";
              }
            $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
             $responseBean->totalCount = $count;
            $responseBean->data = $responseData ;
            $response = CommonUtility::prepareResponse($responseBean, "json");
            return $response;
        } catch (Exception $ex) {
            Yii::log("StoryController:actionDeleteNotification::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
     /**
     * @author Ryan
     * @uses Deleting all Notifications
     * @return type
     */
    public function actionDeleteNotifications()
    {
        $notifyData=json_decode(file_get_contents("php://input"));
        
        try
        {
            NotificationCollection::deleteAllNotifications($notifyData);
            $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = true;
            $response = CommonUtility::prepareResponse($responseBean, "json");
            return $response;
        } catch (Exception $ex) {
            Yii::log("StoryController:actionDeleteNotification::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
//    Ticket #91
    /**
     * @author Kavya
     * @uses Upload functionality
     * @return type
     */
    
    public function actionUploadCommentArtifacts(){
        $postData = array();
        $data_array = array();
        $originalname = '';
        try{
            if(is_array($_POST) && !empty($_POST)){
                $postData = $_POST;
                $directory = $postData['directory'];
                $location = (__DIR__) . '/../../node/'.$directory;
                $uploadfile = $postData['filename'];
                $uploadfilename = $_FILES['commentFile']['tmp_name'];
                $originalname = $postData['originalname'];
                if(move_uploaded_file($uploadfilename, $location.$uploadfile)){
                    chmod($location.$uploadfile, 0777);
                    $data_array['status'] = '1';
                    $data_array['statusMessage'] = 'File successfully uploaded!';
                    $data_array['originalname'] = $originalname;
                    $data_array['path'] = $directory.$uploadfile;
                } else {
                    $data_array['status'] = '0';
                    $data_array['statusMessage'] = 'Upload error!';
                } 
            } 
            else{
                $data_array['status'] = '0';
                $data_array['statusMessage'] = 'No image uploaded';
            }
            return json_encode($data_array);
        } catch (Exception $ex) {
            error_log("catch".$ex->getMessage(). "--" . $ex->getTraceAsString());
            Yii::log("StoryController:actionUploadCommentArtifacts::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
//    Ticket #91 ended
  
    /**
     * @author Anand
     * @uses Get project details by project name.
     * @return type
     */
      public function actionGetProjectDetails(){
        try{
        $project_data = json_decode(file_get_contents("php://input"));
        $data = ServiceFactory::getStoryServiceInstance()->getProjectDetailsByName($project_data->projectName);
            $responseBean = new ResponseBean();
        if(is_array($data)){
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
        }else{
             $responseBean->statusCode = ResponseBean::NOTFOUND;
             $responseBean->message = ResponseBean::NOTFOUND_MESSAGE;
        }
        $responseBean->data = $data;
        $response = CommonUtility::prepareResponse($responseBean,"json");
        return $response;
        } catch (Exception $ex) {
     Yii::log("StoryController:actionGetProjectDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
    /*
    * @author Praveen P
    * @description This method is to used to show the selected user (Stake Holder, Assigned to and Reproted by) in Follower list.
    * @return type Json
    */
    public function actionGetTicketFollowersList() {
        try {
            $StoryData = json_decode(file_get_contents("php://input"));
            $projectId = $StoryData->projectId;
            $ticketId = $StoryData->ticketId;
            $followerlist=ServiceFactory::getCollaboratorServiceInstance()->getTicketFollowersList($ticketId,$projectId);
            $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = $followerlist;
            $response = CommonUtility::prepareResponse($responseBean, "json");
            return $response;
        } catch (Exception $ex) {
            Yii::log("StoryController:actionGetTicketFollowersList::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    

    public function actionTestCheck(){
       $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = "hi";
            $response = CommonUtility::prepareResponse($responseBean, "json");
            return $response;
    }

     /*
    * @author Ryan Marshal
    * @description This method is used to get the preferences of prechecked tasks in Story Creation.
    * @return type Json
    */
    public function actionGetPreference()
    {
        try{
            $ticketData = json_decode(file_get_contents("php://input"));
            $loginUserId=$ticketData->userInfo->Id;
            $preference_items = ServiceFactory::getStoryServiceInstance()->getUserPreferences($loginUserId);
            $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = $preference_items;
            $response = CommonUtility::prepareResponse($responseBean, "json");
            return $response;
            
        } catch (Exception $ex) {
            Yii::log("StoryController:actionGetPreference::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
            
        }
    }

}


?>
