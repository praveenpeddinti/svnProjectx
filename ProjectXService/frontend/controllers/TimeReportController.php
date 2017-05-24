<?php
namespace frontend\controllers;

use Yii;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;

use common\models\mongo\ProjectTicketSequence;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;
use common\components\CommonUtility;
use common\models\bean\ResponseBean;
use common\components\ServiceFactory;
use common\models\mongo\TicketCollection;
use common\models\User;
use common\models\mongo\TinyUserCollection;
use common\models\mysql\Collaborators;
use common\components\ApiClient; //only for testing purpose
use common\components\Email; //only for testing purpose
use common\models\mongo\NotificationCollection;
use common\models\mongo\TicketTimeLog;
include_once '../../common/components/ElasticEmailClient.php';
/**
 * TimeReport Controller
 */
class TimeReportController extends Controller
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
        $data = ServiceFactory::getStoryServiceInstance()->getTicketDetails($ticket_data->ticketId,1);
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
    

    /**
    * @author Praveen P
    * @description This method is used to get all data for Work log details.
    * @return type
    */
   public function actionGetTimeReportDetails() {
        try { 
            $StoryData = json_decode(file_get_contents("php://input"));
            $projectId = $StoryData->projectId; 
            $data = ServiceFactory::getTimeReportServiceInstance()->getAllTimeReportDetails($StoryData, $projectId);
            $totalCount = ServiceFactory::getTimeReportServiceInstance()->getTimeReportCount($StoryData,$projectId);
            $totalWorkLogHours = ServiceFactory::getTimeReportServiceInstance()->getTotalWorkLogHours($StoryData,$projectId);
         
            $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = $data;
            $responseBean->totalCount = $totalCount;
            $responseBean->timehours = $totalWorkLogHours;
            $response = CommonUtility::prepareResponse($responseBean, "json");
            return $response;
        } catch (Exception $ex) {
            Yii::log("StoryController:actionGetTimeReportDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
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
        $data['ticket_details'] = ServiceFactory::getStoryServiceInstance()->getTicketEditDetails($ticket_data->ticketId,1);
        $data['task_types'] = ServiceFactory::getStoryServiceInstance()->getTaskTypes();

        $responseBean = new ResponseBean();
        $responseBean->statusCode = ResponseBean::SUCCESS;
        $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
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
        $projectId = 1;
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
             $storyField['DefaultValue'] = CommonUtility::convert_date_zone(strtotime(date("m-d-Y H:i:s")), "Asia/Kolkata");
           }
          else if($fieldType == 5){
             $storyField['DefaultValue'] = CommonUtility::convert_time_zone(strtotime(date("m-d-Y H:i:s")), "Asia/Kolkata");
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
            if($postFieldData->FieldId == 5 || $postFieldData->FieldId == 11){
            // get all assigned to details,stakeholeders
                $responseData['getFieldDetails'] = ServiceFactory::getCollaboratorServiceInstance()->getProjectTeam($postFieldData->ProjectId);//$projectId
            }else if($postFieldData->FieldId == 3){
                // get all Bucket details
                $responseData['getFieldDetails'] = ServiceFactory::getStoryServiceInstance()->getBucketsList($postFieldData->ProjectId);//$projectId
            }else if($postFieldData->FieldId == 4){
            //get all planlevel details
                $responseData['getFieldDetails'] = ServiceFactory::getStoryServiceInstance()->getPlanLevelList();
            }else if($postFieldData->FieldId == 7){
            //get all status details
                $responseData['getFieldDetails'] = ServiceFactory::getStoryServiceInstance()->getStoryWorkFlowList($postFieldData->WorkflowType,$postFieldData->StatusId);
            }else if($postFieldData->FieldId == 6){
            //get all priority details
                $responseData['getFieldDetails'] = ServiceFactory::getStoryServiceInstance()->getPriorityList();
            }else if($postFieldData->FieldId == 12){
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
            $parentTicNumber=$ticket_data->data->TicketId;
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
             $pageLength = $StoryData->offset;
             $offset = $StoryData->pagesize;
            
//            $userId=11;
//            $projectId=1;
//            $sortorder =  "desc";
//            $sortvalue = "Id";
            
            $totalCount = ServiceFactory::getStoryServiceInstance()->getMyTicketsCount($userId,$projectId);
            $data = ServiceFactory::getStoryServiceInstance()->getAllMyTickets($userId,$sortorder,$sortvalue,$offset,$pageLength,$projectId);

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
            $team=ServiceFactory::getCollaboratorServiceInstance()->getFilteredProjectTeam($post_data->ProjectId,$post_data->search_term);
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
            $projectId = $StoryData->ProjectId;
            $searchValue = $StoryData->SearchValue;
            $ticketId = $StoryData->TicketId;
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
            $responseBean->data = $returnData;
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
            $data = ServiceFactory::getStoryServiceInstance()->getTicketActivity($post_data->ticketId,$post_data->projectId);
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
          
               ServiceFactory::getStoryServiceInstance()->followTicket($post_data->collaboratorId, $post_data->TicketId, $post_data->projectId, $post_data->userInfo->Id, "follower");
               $collaboratorData =  TinyUserCollection::getMiniUserDetails($post_data->collaboratorId);
               $followerData = array();
               $followerData["ProfilePicture"] = $collaboratorData["ProfilePicture"];
               $followerData["UserName"] = $collaboratorData["UserName"];
               $followerData["FollowerId"] =$post_data->collaboratorId;
               $followerData["CreatedBy"] =$post_data->userInfo->Id;
               $followerData["Flag"] ="follower";
               $followerData["DefaultFollower"] =0;
              //  array_push($followers_pics, $followerData);
//               if($followerData["UserName"]!='') // added by Ryan for email purpose
//               {
//                   try
//                   {
//                    CommonUtility::sendMail(null, $followerData["UserName"]);
//                   }
//                   catch(Exception $ex)
//                   {
//                      Yii::log("CommonUtility::sendMail::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application'); 
//                   }
//               } //end by Ryan
               
               /* added by Ryan for notifications */
               
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
            ServiceFactory::getStoryServiceInstance()->unfollowTicket($post_data->collaboratorId, $post_data->TicketId, $post_data->projectId);
            
            $collaboratorData =  TinyUserCollection::getMiniUserDetails($post_data->collaboratorId);//added by Ryan
//            if($collaboratorData['UserName']!='') //added by Ryan for email purpose
//            {
//               try
//                   {
//                    CommonUtility::sendMail(null, $followerData["UserName"]);
//                   }
//                   catch(Exception $ex)
//                   {
//                      Yii::log("CommonUtility::sendMail::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application'); 
//                   } 
//            }//end by Ryan
            
            /* added by Ryan for notifications */
               
              ServiceFactory::getStoryServiceInstance()->saveNotifications($post_data, 'remove', $post_data->collaboratorId,"FollowObj");
               /* notifications end */
               
            $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = $post_data->collaboratorId;
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
            $task = ServiceFactory::getStoryServiceInstance()->createChildTask($postData);
            $responseData = array();
            $responseData = array("Tasks"=>$task);
            if($task !='failure'){
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
                $responseBean->data =    $task;
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
          $pdateRelateTask = ServiceFactory::getStoryServiceInstance()->updateRelatedTaskId($projectId,$ticketId,$searchTicketId);
          $ticketData = ServiceFactory::getStoryServiceInstance()->getAllRelateStory($projectId,$ticketId);
            $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = $ticketData;
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
            $timelog_data = json_decode(file_get_contents("php://input"));
            $insertTimelog = ServiceFactory::getStoryServiceInstance()->insertTimeLog($timelog_data);
            $projectId = $timelog_data->projectId;
            $parentTicketId = $timelog_data->TicketId;
            $updateTimeLog = ServiceFactory::getStoryServiceInstance()->getTimeLog($projectId, $parentTicketId);
            $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = $updateTimeLog;
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
            $Artifacts = ServiceFactory::getStoryServiceInstance()->getTicketAttachments($post_data->ticketId, $post_data->projectId);
            $tinyUserModel = new TinyUserCollection();
            $Artifacts = $Artifacts["Artifacts"];
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
                    $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                    $readableDate = $datetime->format('M-d-Y H:i:s');
                    $Artifacts[$key]["UploadedOn"] = $readableDate;
                } else {
                    $Artifacts[$key]["UploadedOn"] = "";
                }
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
            $ticketData = json_decode(file_get_contents("php://input"));
            $projectId = $ticketData->projectId;
            $parentTicketId = $ticketData->ticketId;
            $unRelateTicketId = $ticketData->unRelateTicketId;
            $removeUnrelateTask = ServiceFactory::getStoryServiceInstance()->unRelateTask($projectId, $parentTicketId, $unRelateTicketId);
            $relatedTicketsInfo = ServiceFactory::getStoryServiceInstance()->getAllRelateStory($projectId, $parentTicketId);
            $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = $relatedTicketsInfo;
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
     * @author Ryan Marshal
     * @description Send Mail for AssignedTo and StakeHolder dropdown select in the story-edit page
     * @return 
     */
    public function actionSendMail()
    {
        try
        {
            error_log("==in send mail==");
            $ticketData=json_decode(file_get_contents("php://input"));
            $loggedinuser=$ticketData->userInfo->username;
            error_log("logged in".$loggedinuser);
            $collaboratorData=Collaborators::getCollboratorByFieldType("Id",$ticketData->collaborator);
            $assigned_member=$collaboratorData['UserName'];
            error_log("==assigned member==".$assigned_member);
            $ticketDetails = TicketCollection::getTicketDetails($ticketData->ticketId,$ticketData->projectId); 
            error_log("==Recepients==".print_r($ticketDetails,1));
            
             try
                {
                 CommonUtility::sendMail($loggedinuser, $assigned_member, $ticketDetails); 		
                }
                catch (Exception $e)
                {
                    echo 'Something went wrong with email sending: ', $e->getMessage(), '\n';

                    return;
                }		
            
        } catch (Exception $ex) {
            
            Yii::log("StoryController:actionSendMail::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
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
        $options['general'] = ServiceFactory::getStoryServiceInstance()->getFilterOptions();
        $options['bucket'] = ServiceFactory::getStoryServiceInstance()->getBucketsList($projectId);
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
            //$result_data=NotificationCollection::getNotifications($notified_username,$projectId);
            $result_data=NotificationCollection::getNotifications($notified_userid,$projectId,0,5);
            $count = NotificationCollection::getNotificationsCount($notified_userid,$projectId);
            $result =  count($result_data)>0 ? $result_data : "nodata";
            
            $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
             $responseBean->totalCount = $count;
            $responseBean->data =  array('notify_result'=>$result);
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
        error_log("actionUploadCommentArtifacts---------------------");
        $postData = array();
        $data_array = array();
        $originalname = '';
        try{
            if(is_array($_POST) && !empty($_POST)){
                $postData = $_POST;
                error_log("comments post data : ".print_r($postData,1));
                error_log("comments files data : ".print_r($_FILES,1));
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
    
    public function actionGetTicketdetailsForEdit(){
            $ticketData = json_decode(file_get_contents("php://input"));
            $projectId = $ticketData->projectId;
            $ticketId = $ticketData->ticketId;
            $ticketArry=array($ticketId);
         error_log("ticketTimeLog33333333333".$ticketId.$projectId);
        $ticketTimeLog = TicketTimeLog::getTimeLogRecords($projectId, $ticketArry);
        error_log("ticketTimeLog".print_r($ticketTimeLog));
    }
    
        /**
     * @author Padmaja
     * @uses updating timelog details
     * @return type
     */
    public function actionUpdateTimelogForEdit(){
        try{
            error_log("asssssssssssssssssssd@@@@@@@@@@@@@@@@@@");
            $ticketData = json_decode(file_get_contents("php://input"));
           error_log("calendarrrrrrrrrrrr".$ticketData->toDate);
            $totalCount = ServiceFactory::getTimeReportServiceInstance()->getTimeReportCount($ticketData,$ticketData->projectId);
            $last7DaysWorkLog = ServiceFactory::getTimeReportServiceInstance()->getTotalWorkLogHours($ticketData,$ticketData->projectId);
            $updatedData=ServiceFactory::getTimeReportServiceInstance()->updateDataForTimeLog($ticketData);
             error_log("###############".$totalCount);
            $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = $updatedData;
            $responseBean->totalCount = $totalCount;
            $responseBean->timehours = $last7DaysWorkLog;
            $response = CommonUtility::prepareResponse($responseBean, "json");
            return $response;
            
        } catch (Exception $ex) {
             Yii::log("TimeReportController:actionUpdateTimelogForEdit::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }

    }
    
    public function actionGetStoryDetailsForTimelog(){
        $searchData = json_decode(file_get_contents("php://input"));
        $projectId = $searchData->projectId;
       // $ticketId = $searchData->ticketId;
        $sortvalue = $searchData->sortvalue;
        $searchString = $searchData->searchString;
        $getSearchDetails = TicketCollection::getAllStoryDetailsForTimelog($projectId,$sortvalue, $searchString);
        $responseBean = new ResponseBean();
        $responseBean->statusCode = ResponseBean::SUCCESS;
        $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
        $responseBean->data = $getSearchDetails;
        $response = CommonUtility::prepareResponse($responseBean, "json");
        return $response;
    }
         /**
     * @author Padmaja
     * @uses adding timelog details
     * @return type
     */
    public function actionAddTimelog(){
        try{
            $timelogData = json_decode(file_get_contents("php://input"));
            $getTimelogData= ServiceFactory::getTimeReportServiceInstance()->addTimelog($timelogData);
            $totalCount = ServiceFactory::getTimeReportServiceInstance()->getTimeReportCount($timelogData,$timelogData->projectId);
            $last7DaysWorkLog = ServiceFactory::getTimeReportServiceInstance()->getTotalWorkLogHours($timelogData,$timelogData->projectId);
            // $getTimelogData= ServiceFactory::getTimeReportServiceInstance()->addTimelog($timelogData);
            $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = $getTimelogData;
            $responseBean->totalCount = $totalCount;
            $responseBean->timehours = $last7DaysWorkLog;
            $response = CommonUtility::prepareResponse($responseBean, "json");
            return $response; 
        } catch (Exception $ex) {
             Yii::log("TimeReportController:actionAddTimelog::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }

    }
          /**
     * @author Padmaja
     * @uses adding removing details
     * @return type
     */
   public function actionRemoveTimelog(){
       try{
            $timelogData = json_decode(file_get_contents("php://input"));
            $projectId = $timelogData->projectId;
            $slug = $timelogData->slug;
            $timelogHours = $timelogData->timelogHours;
            $ticketDesc= explode(".",$timelogData->ticketDesc);
            $ticketId=str_replace('#','',$ticketDesc[0]);
            $userId=$timelogData->userInfo->Id;
            $getTimelogData= ServiceFactory::getTimeReportServiceInstance()->RemoveTimelogs($projectId,$ticketId,$slug,$timelogHours,$userId);
            $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = $getTimelogData;
            $response = CommonUtility::prepareResponse($responseBean, "json");
            return $response;
           
       } catch (Exception $ex) {
            Yii::log("TimeReportController:actionRemoveTimelog::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
       }

   }
}



?>
