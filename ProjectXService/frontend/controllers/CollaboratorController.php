<?php
namespace frontend\controllers;

use Yii;
use yii\base\ErrorException;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\components\{CommonUtility,ServiceFactory};
use common\models\bean\ResponseBean;
use common\models\mongo\TinyUserCollection;
use common\models\mongo\NotificationCollection;
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
            $profilepic=$userData->profile;
            $userid=ServiceFactory::getCollaboratorServiceInstance()->saveNewUser($projectId,$user,$profilepic);
            $userDetails=ServiceFactory::getCollaboratorServiceInstance()->getUserDetails($userid);
            error_log("==User Details==".print_r($userDetails,1));
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
    
    
    
}  
    ?>
