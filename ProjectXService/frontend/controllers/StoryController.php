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
    * @description This method is used to get all data for stories/tasks.
    * @return type
    */
   public function actionGetAllStoryDetails() {
        try {
            $StoryData = json_decode(file_get_contents("php://input"));
            //$projectId=1;
            $projectId = $StoryData->projectId;
            $totalCount = ServiceFactory::getStoryServiceInstance()->getAllStoriesCount($projectId);
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
            $projectId = $StoryData->projectId;
            $getSubTaskIds = ServiceFactory::getStoryServiceInstance()->getSubTaskIds($StoryData->storyId,$projectId);
            $data = ServiceFactory::getStoryServiceInstance()->getSubTaskDetails($getSubTaskIds[0]['Tasks'], $projectId);
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
            error_log("++++++++++++++++++++++++dfsdfsdfsdf+++++++++++++".$ticket_data->ticketId);
        $data = ServiceFactory::getStoryServiceInstance()->getTicketEditDetails($ticket_data->ticketId,1);
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
            error_log("actionSaveTicketDetails--");
            $ticket_data = json_decode(file_get_contents("php://input"));
            
            $title = $ticket_data->data->title;
            $description = $ticket_data->data->description;
            $parentTicNumber = ServiceFactory::getStoryServiceInstance()->saveTicketDetails($ticket_data);

            $planLevelNumber = $ticket_data->data->{'4'}; //story-1 or task-2
            if ($planLevelNumber == 1) {
                $defualtTicketsArray = $ticket_data->data->UI;
                $childTicketnoArray = array();
                for ($i = 0; $i < sizeof($defualtTicketsArray); $i++) {
                    $ticket_data->data->title = $defualtTicketsArray[$i]."-" . $title;
                    $ticket_data->data->description = "Please provide description here";
                    $ticket_data->data->{'4'} = 2;
                    $ticketNumber = ServiceFactory::getStoryServiceInstance()->saveTicketDetails($ticket_data, $parentTicNumber);
                    array_push($childTicketnoArray, $ticketNumber);
                    }
                $updateParentTaskArray = ServiceFactory::getStoryServiceInstance()->updateParentTicketTaskField($parentTicNumber, $childTicketnoArray);     
                }
            $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = "success";
            $response = CommonUtility::prepareResponse($responseBean,"json");
            return $response;
        } catch (Exception $ex) {
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
                $responseData['getFieldDetails'] = ServiceFactory::getStoryServiceInstance()->getStoryWorkFlowList();
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
           $data = ServiceFactory::getStoryServiceInstance()->updateTicketDetails($ticket_data);
           $responseBean = new ResponseBean();
           $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = "success";
            $response = CommonUtility::prepareResponse($responseBean,"json");
             return $response;
        } catch (Exception $ex) {
        Yii::log("StoryController:saveTicketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
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
     * @author Padmaja
     * @description This method is used to save child task details.
    * @return type Json
     */
    public function actionSaveChiledTask(){
        try{
            $postData= json_decode(file_get_contents("php://input")); 
            $task = ServiceFactory::getStoryServiceInstance()->SaveChiledTask($postData);
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
             Yii::log("StoryController:actionSaveChiledTask::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
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

}


?>
