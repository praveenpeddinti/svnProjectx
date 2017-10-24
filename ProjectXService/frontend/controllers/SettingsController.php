<?php
namespace frontend\controllers;

use Yii;
use yii\base\ErrorException;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\bean\ResponseBean;
use common\components\{CommonUtility,ServiceFactory};
use common\models\mongo\TinyUserCollection;
use common\models\mongo\NotificationCollection;
/**
 * 
 * Story Controller
 */
class SettingsController extends Controller
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
     * @author Lakshmi
     * @params none
     * @return $response
     * @Description gets notification settings
     */
    public function actionNotificationPreferences(){
          try{
        $details=array();
        $postData = json_decode(file_get_contents("php://input"));
        $userId=!empty($postData->userInfo->Id)?$postData->userInfo->Id:"";
        $details = ServiceFactory::getCollaboratorServiceInstance()->getAllNotificationTypes($userId);
        $responseBean = new ResponseBean();
         $responseBean->statusCode = ResponseBean::SUCCESS;
         $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
         $responseBean->data = $details;
         $response = CommonUtility::prepareResponse($responseBean,"json");
         return $response;
          }  catch (\Throwable $th)  {
                Yii::error("SiteController:actionEmailPreferences::" . $th->getMessage() . "--" . $th->getTraceAsString(), 'application');
             $responseBean = new ResponseBean();
             $responseBean->statusCode = ResponseBean::SERVER_ERROR_CODE;
             $responseBean->message = $th->getMessage();
             $responseBean->data = [];
             $response = CommonUtility::prepareResponse($responseBean,"json");
             return $response;
        }
    }
    
    /**
     * @author Lakshmi
     * @params none
     * @return $response
     * @Description Updates status of a notification
     */
    public function actionNotificationsSettingsStatusUpdate(){
        try{
         $postData = json_decode(file_get_contents("php://input"));
        $userId=!empty($postData->userInfo->Id)?$postData->userInfo->Id:"";
        $type=$postData->type;
        $activityId=$postData->id;
        $isChecked=$postData->isChecked;
        $details = ServiceFactory::getCollaboratorServiceInstance()->NotificationsSetttingsStatusUpdate($userId,$type,$activityId,$isChecked);
         $responseBean = new ResponseBean();
         $responseBean->statusCode = ResponseBean::SUCCESS;
         $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
         $responseBean->data = $details;
         $response = CommonUtility::prepareResponse($responseBean,"json");
         return $response;
        }  catch (\Throwable $th)  {
                Yii::error("SiteController:actionUserAuthentication::" . $th->getMessage() . "--" . $th->getTraceAsString(), 'application');
             $responseBean = new ResponseBean();
             $responseBean->statusCode = ResponseBean::SERVER_ERROR_CODE;
             $responseBean->message = $th->getMessage();
             $responseBean->data = [];
             $response = CommonUtility::prepareResponse($responseBean,"json");
             return $response;
        }
      
    }
     /**
     * @author Lakshmi
     * @params none
     * @return $response
      * @Description Updates status of all notifications
     */
        public function actionNotificationsSettingsStatusUpdateAll(){
        try{
         $postData = json_decode(file_get_contents("php://input"));
        $userId=!empty($postData->userInfo->Id)?$postData->userInfo->Id:"";
        $type=$postData->NotificationType;
        $isChecked=$postData->isChecked;
        $details =ServiceFactory::getCollaboratorServiceInstance()->NotificationsSetttingsStatusUpdateAll($userId,$type,$isChecked);
         $responseBean = new ResponseBean();
         $responseBean->statusCode = ResponseBean::SUCCESS;
         $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
         $responseBean->data = $details;
         $response = CommonUtility::prepareResponse($responseBean,"json");
         return $response;
        }  catch (\Throwable $th)  {
                Yii::error("SiteController:actionUserAuthentication::" . $th->getMessage() . "--" . $th->getTraceAsString(), 'application');
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
