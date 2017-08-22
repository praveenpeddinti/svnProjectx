<?php
namespace frontend\controllers;

use Yii;
use yii\base\ErrorException;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\components\{CommonUtility,ServiceFactory,EventTrait};
use common\models\bean\ResponseBean;
use common\models\mongo\TinyUserCollection;
use common\models\mongo\NotificationCollection;
use common\models\mysql\Projects;
/**
 * 
 * Story Controller
 */
class CollaboratorController extends Controller
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
    'components' => [
        'errorHandler' => [
            'maxSourceLines' => 20,
        ],
    ],
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
     * @author  Anand
     * @return type
     */
    
    public function  actionGetUserDashboardDetails(){
       
    try {
         $postData = json_decode(file_get_contents("php://input"));
         $dashboardDetails = ServiceFactory::getCollaboratorServiceInstance()->getUserDashboardDetails($postData);
         $responseBean = new ResponseBean();
         $responseBean->statusCode = ResponseBean::SUCCESS;
         $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
         $responseBean->data = $dashboardDetails;
         $response = CommonUtility::prepareResponse($responseBean,"json");
         return $response;      
    } catch (\Throwable $th) {
            Yii::error("StoryController:actionGetUserDashboardDetails::" . $th->getMessage() . "--" . $th->getTraceAsString(), 'application');
             $responseBean = new ResponseBean();
             $responseBean->statusCode = ResponseBean::SERVER_ERROR_CODE;
             $responseBean->message = $th->getMessage() ;// ResponseBean::SERVER_ERROR_MESSAGE;
             $responseBean->data = [];
             $response = CommonUtility::prepareResponse($responseBean,"json");
             return $response;
        }
    }
    
    /*
     * @author Ryan
     * Saves a New User in the System
     */
    public function actionSaveUser(){
        try{
            
            $userData = json_decode(file_get_contents("php://input"));
            $projectId=$userData->projectId;
            $user=$userData->user;
            //$profilepic=$userData->userProfileImage;
            $code=$userData->code;
            $userid=ServiceFactory::getCollaboratorServiceInstance()->saveNewUser($projectId,$user,$code);
            $userDetails=ServiceFactory::getCollaboratorServiceInstance()->getUserDetails($userid);
            $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = $userDetails;
            $response = CommonUtility::prepareResponse($responseBean,"json");
            return $response; 
            
        } catch (\Throwable $th) {
            Yii::error("StoryController:actionSaveUser::" . $th->getMessage() . "--" . $th->getTraceAsString(), 'application');
             $responseBean = new ResponseBean();
             $responseBean->statusCode = ResponseBean::SERVER_ERROR_CODE;
             $responseBean->message = $th->getMessage() ;// ResponseBean::SERVER_ERROR_MESSAGE;
             $responseBean->data = [];
             $response = CommonUtility::prepareResponse($responseBean,"json");
             return $response;
        }
    }
    
    /*
     * @author Ryan
     * Gets Users Not yet Invited in Project
     */
    public function  actionGetInviteUsers(){
       
    try {
         $searchData = json_decode(file_get_contents("php://input"));
         $searchTerm=$searchData->query;
         $projectId=$searchData->projectId;
         $userData = ServiceFactory::getCollaboratorServiceInstance()->getUsersToInvite($searchTerm,$projectId);
         $responseBean = new ResponseBean();
         $responseBean->statusCode = ResponseBean::SUCCESS;
         $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
         $responseBean->data = $userData;
         $response = CommonUtility::prepareResponse($responseBean, "json");
         return $response;      
    } catch (\Throwable $th) {
            Yii::error("StoryController:actionGetUpdatedTicketDetails::" . $th->getMessage() . "--" . $th->getTraceAsString(), 'application');
             $responseBean = new ResponseBean();
             $responseBean->statusCode = ResponseBean::SERVER_ERROR_CODE;
             $responseBean->message = $th->getMessage();
             $responseBean->data = [];
             $response = CommonUtility::prepareResponse($responseBean,"json");
             return $response;
        }
    }
    
    /*
     * @author Ryan
     * Sends Mail Invitation
     */
    public function  actionSendInvite(){
       
    try {
         $inviteData = json_decode(file_get_contents("php://input"));
         $invited_users=$inviteData->recepients;
         $projectName=$inviteData->projectName;
         $userid=$inviteData->userInfo->Id;
         $email_status = ServiceFactory::getCollaboratorServiceInstance()->sendMailInvitation($invited_users,$projectName,$userid);
         $responseBean = new ResponseBean();
         $responseBean->statusCode = ResponseBean::SUCCESS;
         $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
         $responseBean->data = $email_status;
         $response = CommonUtility::prepareResponse($responseBean, "json");
         return $response;      
    } catch (\Throwable $th) {
            Yii::error("StoryController:actionSendInvite::" . $th->getMessage() . "--" . $th->getTraceAsString(), 'application');
             $responseBean = new ResponseBean();
             $responseBean->statusCode = ResponseBean::SERVER_ERROR_CODE;
             $responseBean->message = $th->getMessage();
             $responseBean->data = [];
             $response = CommonUtility::prepareResponse($responseBean,"json");
             return $response;
        }
    }
    
    /*
     * @author Ryan
     * Used for adding existing user to Team
     */
    public function actionAddToTeam(){
        try{
             $userData = json_decode(file_get_contents("php://input"));
             $projectId=$userData->projectId;
             $userid=$userData->userid;
             $status=ServiceFactory::getCollaboratorServiceInstance()->addUserToTeam($projectId,$userid);
             $responseBean = new ResponseBean();
             $responseBean->statusCode = ResponseBean::SUCCESS;
             $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
             $responseBean->data = $status;
             $response = CommonUtility::prepareResponse($responseBean, "json");
         return $response;  
        } catch (\Throwable $th) {
            Yii::error("StoryController:actionAddToTeam::" . $th->getMessage() . "--" . $th->getTraceAsString(), 'application');
             $responseBean = new ResponseBean();
             $responseBean->statusCode = ResponseBean::SERVER_ERROR_CODE;
             $responseBean->message = $th->getMessage();
             $responseBean->data = [];
             $response = CommonUtility::prepareResponse($responseBean,"json");
             return $response;
        }
    }
    
    /*
     * @author Ryan
     * Used for verfiying invitation code
     */
    public function actionVerifyInvitationCode(){
        try{
            $inviteData = json_decode(file_get_contents("php://input"));
            $invite_code=$inviteData->inviteCode;
           // $projectId=$inviteData->projectId;
            $userData=ServiceFactory::getCollaboratorServiceInstance()->verifyCode($invite_code);
            $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = $userData;
            $response = CommonUtility::prepareResponse($responseBean, "json");
         return $response;      
        } catch (\Throwable $th) {
            Yii::error("StoryController:actionVerifyInvitationCode::" . $th->getMessage() . "--" . $th->getTraceAsString(), 'application');
             $responseBean = new ResponseBean();
             $responseBean->statusCode = ResponseBean::SERVER_ERROR_CODE;
             $responseBean->message = $th->getMessage();
             $responseBean->data = [];
             $response = CommonUtility::prepareResponse($responseBean,"json");
             return $response;
        }
        
    }
    
    /*
     * @author Ryan
     * Used for Invalidating Invitation Code
     */
    public function actionInvalidateInvitation(){
        try{
             $inviteData = json_decode(file_get_contents("php://input"));
             $invite_email=$inviteData->email;
             $invite_code=$inviteData->inviteCode;
             $status=ServiceFactory::getCollaboratorServiceInstance()->invalidateInvite($invite_email,$invite_code);
             $responseBean = new ResponseBean();
             $responseBean->statusCode = ResponseBean::SUCCESS;
             $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
             $responseBean->data = $status;
             $response = CommonUtility::prepareResponse($responseBean, "json");
         return $response;      
        } catch (\Throwable $th) {
            Yii::error("StoryController:actionInvalidateInvitation::" . $th->getMessage() . "--" . $th->getTraceAsString(), 'application');
             $responseBean = new ResponseBean();
             $responseBean->statusCode = ResponseBean::SERVER_ERROR_CODE;
             $responseBean->message = $th->getMessage();
             $responseBean->data = [];
             $response = CommonUtility::prepareResponse($responseBean,"json");
             return $response;
        }
    }
    
    public function actionGetUserEmail(){
        try{
            $userData = json_decode(file_get_contents("php://input"));
            $invite_code=$userData->code;
            $result = ServiceFactory::getCollaboratorServiceInstance()->getEmailFromCode($invite_code);
            $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = $result;
            $response = CommonUtility::prepareResponse($responseBean, "json");
            return $response;
        } catch (\Throwable $th) {
            Yii::error("StoryController:actionInvalidateInvitation::" . $th->getMessage() . "--" . $th->getTraceAsString(), 'application');
             $responseBean = new ResponseBean();
             $responseBean->statusCode = ResponseBean::SERVER_ERROR_CODE;
             $responseBean->message = $th->getMessage();
             $responseBean->data = [];
             $response = CommonUtility::prepareResponse($responseBean,"json");
             return $response;
        }
    }
    /**
    * @author Padmaja
    *  @description This is used for verifying projects is exists or not
    * @param type 
    * @return array
    */
    public function actionVerifyingProjectName(){
        try{
            $postData = json_decode(file_get_contents("php://input"));
            $getProjectDetails=ServiceFactory::getProjectServiceInstance()->verifyProjectName($postData->projectName);
           if(!empty($getProjectDetails) || empty($getProjectDetails)){
                $responseBean = new ResponseBean;
                $responseBean->statusCode = ResponseBean::SUCCESS;
                $responseBean->message = "success";
                $responseBean->data = $getProjectDetails;
                $response = CommonUtility::prepareResponse($responseBean,"json"); 
            }else{
                $responseBean = new ResponseBean;
                $responseBean->statusCode = ResponseBean::FAILURE;
                $responseBean->message = "failure";
                $responseBean->data = $getProjectDetails;
                $response = CommonUtility::prepareResponse($responseBean,"json"); 
            }
            return $response;
         } catch (\Throwable $th) { 
             Yii::error("CollabaratorController:actionVerifyingProjectName::" . $th->getMessage() . "--" . $th->getTraceAsString(), 'application');
             $responseBean = new ResponseBean();
             $responseBean->statusCode = ResponseBean::SERVER_ERROR_CODE;
             $responseBean->message =  $th->getMessage();
             $responseBean->data = [];
             $response = CommonUtility::prepareResponse($responseBean,"json");
             return $response;
        }
    }
      /**
    * @author Padmaja
    * @description This is used for saving project details
    * @param type 
    * @return array
    */
    public function actionSaveProjectDetails(){
        try{
            $postData = json_decode(file_get_contents("php://input")); 
            $fileExt=!empty($postData->fileExtention)?$postData->fileExtention:"";
            $returnId=ServiceFactory::getProjectServiceInstance()->savingProjectDetails($postData->projectName,$postData->description,$postData->userInfo->Id,$postData->projectLogo);
            if($returnId!='failure'){
                $projectId=$returnId;
                if (strpos($postData->projectLogo,'assets') !== false) {
                  $logo=$postData->projectLogo;
                } else {
                   $extractUrl= explode('projectlogo/',$postData->projectLogo);
                   $projectLogoPath = Yii::$app->params['ProjectRoot']. Yii::$app->params['projectLogo'] ;
                    if (file_exists($projectLogoPath."/".$extractUrl[1])) {
                        rename($projectLogoPath . "/" . $extractUrl[1],$projectLogoPath . "/" . $postData->projectName."_".$returnId.".$fileExt");
                        $logo=$postData->projectName."_".$returnId.".$fileExt";
                    } else {
                        error_log("not existeddddddddd");
                    }
                
                }
                ServiceFactory::getCollaboratorServiceInstance()->updateProjectlogo($projectId,$logo);
                $getStatus=ServiceFactory::getProjectServiceInstance()->savingProjectTeamDetails($projectId,$postData->userInfo->Id);
               // $getlastIdDetails= CommonUtilityTwo::getLastProjectDetails($projectId,$postData->userInfo->Id);
             }
             if($getStatus == 'failure' || $returnId=='failure'){
                $responseBean = new ResponseBean;
                $responseBean->statusCode = ResponseBean::FAILURE;
                $responseBean->message = "failure";
                $responseBean->data = $projectId;
                $response = CommonUtility::prepareResponse($responseBean,"json"); 
            }else{
                $responseBean = new ResponseBean;
                $responseBean->statusCode = ResponseBean::SUCCESS;
                $responseBean->message = "success";
                $responseBean->data = $projectId;
                $response = CommonUtility::prepareResponse($responseBean,"json");
         
            }
            return $response;
        } catch (\Throwable $th) { 
             Yii::error("CollabaratorController:actionSaveProjectDetails::" . $th->getMessage() . "--" . $th->getTraceAsString(), 'application');
             $responseBean = new ResponseBean();
             $responseBean->statusCode = ResponseBean::SERVER_ERROR_CODE;
             $responseBean->message = $th->getMessage();
             $responseBean->data = [];
             $response = CommonUtility::prepareResponse($responseBean,"json");
             return $response;
        }
    }
        /**
    * @author Padmaja
    * @description This is used for updating project Details
    * @param type 
    * @return array
    */
    public function actionUpdateProjectDetails(){
        try{
            $postData = json_decode(file_get_contents("php://input")); 
            $fileExt=!empty($postData->fileExtention)?$postData->fileExtention:"";
            $projectId=!empty($postData->projectId)?$postData->projectId:"";
            $updateStatus=ServiceFactory::getProjectServiceInstance()->updatingProjectDetails($postData->projectName,$postData->description,$fileExt,$postData->projectLogo,$projectId);
            if($updateStatus=='success'){
                $responseBean = new ResponseBean;
                $responseBean->statusCode = ResponseBean::SUCCESS;
                $responseBean->message = "success";
                $responseBean->data = $updateStatus;
                $response = CommonUtility::prepareResponse($responseBean,"json"); 
            }else{
                $responseBean = new ResponseBean;
                $responseBean->statusCode = ResponseBean::FAILURE;
                $responseBean->message = "failure";
                $responseBean->data = $updateStatus;
                $response = CommonUtility::prepareResponse($responseBean,"json"); 
            }
             return $response;
        } catch (Exception $ex) {
            Yii::log("CollabaratorController:actionUpdateProjectDetails::".$ex->getMessage()."--".$ex->getTraceAsString(), 'error', 'application');
    }
        
    }
       /**
    * @author Padmaja
    * @description This is used for get project dashboard details
    * @param type 
    * @return array
    */
    public function actionGetProjectDashboardDetails(){
        try{
            $postData = json_decode(file_get_contents("php://input"));
            error_log("111111111111111111111--------");
            $projectdetails=ServiceFactory::getProjectServiceInstance()->getProjectDashboardDetails($postData->projectName,$postData->projectId,$postData->userInfo->Id,$postData->page);
           // $projectInfo = ServiceFactory::getStoryServiceInstance()->getProjectDetailsForDashboard($postData);
            if(!empty($projectdetails)){
                $responseBean = new ResponseBean;
                $responseBean->statusCode = ResponseBean::SUCCESS;
                $responseBean->message = "success";
                $responseBean->data = $projectdetails;
                $response = CommonUtility::prepareResponse($responseBean,"json"); 
            }else{
                $responseBean = new ResponseBean;
                $responseBean->statusCode = ResponseBean::FAILURE;
                $responseBean->message = "failure";
                $responseBean->data = $projectdetails;
                $response = CommonUtility::prepareResponse($responseBean,"json"); 
            }
             return $response;
             
        } catch (\Throwable $th) { 
             Yii::error("SiteController:actionGetProjectDashboardDetails::" . $th->getMessage() . "--" . $th->getTraceAsString(), 'application');
             $responseBean = new ResponseBean();
             $responseBean->statusCode = ResponseBean::SERVER_ERROR_CODE;
             $responseBean->message = $th->getMessage();
             $responseBean->data = [];
             $response = CommonUtility::prepareResponse($responseBean,"json");
             return $response;
        } 
    }
        /**
    * @author Padmaja
    * @description This is used for get all activities for project dashboard
    * @param type 
    * @return array
    */
    public function actionGetAllActivitiesForProjectDashboard(){
        try{
            $postData = json_decode(file_get_contents("php://input"));
            $projectdetails=ServiceFactory::getProjectServiceInstance()->getAllActivities($postData);
            if(!empty($projectdetails)){
                $responseBean = new ResponseBean;
                $responseBean->statusCode = ResponseBean::SUCCESS;
                $responseBean->message = "success";
                $responseBean->data = $projectdetails;
                $response = CommonUtility::prepareResponse($responseBean,"json"); 
            }else{
                $responseBean = new ResponseBean;
                $responseBean->statusCode = ResponseBean::FAILURE;
                $responseBean->message = "failure";
                $responseBean->data = $projectdetails;
                $response = CommonUtility::prepareResponse($responseBean,"json"); 
            }
             return $response; 
        }  catch (\Throwable $th) { 
             Yii::error("SiteController:getAllActivitiesForProjectDashboard::" . $th->getMessage() . "--" . $th->getTraceAsString(), 'application');
             $responseBean = new ResponseBean();
             $responseBean->statusCode = ResponseBean::SERVER_ERROR_CODE;
             $responseBean->message = $th->getMessage();
             $responseBean->data = [];
             $response = CommonUtility::prepareResponse($responseBean,"json");
             return $response;
        } 
        
    }
    
}  
    ?>
