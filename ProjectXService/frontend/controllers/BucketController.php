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
use common\models\mysql\Bucket;
use common\components\ApiClient; //only for testing purpose
use common\components\Email; //only for testing purpose
use common\models\mongo\NotificationCollection;
use common\models\mongo\TicketTimeLog;
use common\components\CommonUtilityTwo;
//use common\models\mongo\TicketCollection;
/**
 * Bucket Controller
 */
class BucketController extends Controller
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
    * @author Praveen P
    * @description This method is used to get all data for Buckets details.
    * @return type
    */
   public function actionGetAllBucketDetails() {
        try { 
            $bucketData = json_decode(file_get_contents("php://input"));
            $bucketDetails=ServiceFactory::getBucketServiceInstance()->getBucketDetails($bucketData);
            $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            //$responseBean->totalCount = $totalCount;
            $responseBean->data = $bucketDetails;
            $response = CommonUtility::prepareResponse($responseBean, "json");
            return $response;
        } catch (Exception $ex) {
            Yii::log("BucketController:actionGetAllBucketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
    /**
     * @author Praveen
     * @uses Get collaborators role as Admin for current project
     * @return type
     */
    public function actionGetResponsibleCollaborators(){
        try{
        $postData = json_decode(file_get_contents("php://input"));
        $bucketCreationTeam=ServiceFactory::getCollaboratorServiceInstance()->getResponsibleProjectTeam($postData->projectId,$postData->role);
            $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = $bucketCreationTeam;
            $response = CommonUtility::prepareResponse($responseBean,"json");
      return $response;  
        } catch (Exception $ex) {
            Yii::log("BucketController:actionGetFilterOptions::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
    /**
     * @author Praveen
     * @uses Get buckets for current project
     * @return type
     */
    public function actionGetBucketFilters(){
        try{
        $postData = json_decode(file_get_contents("php://input"));
        $bucketFilterData=ServiceFactory::getBucketServiceInstance()->getBucketTypeFilter($postData->projectId,$postData->Type);
        $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = $bucketFilterData;
            $responseBean->totalCount = 'New';
            $response = CommonUtility::prepareResponse($responseBean,"json");
      return $response;  
        } catch (Exception $ex) {
            Yii::log("BucketController:actionGetFilterOptions::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
    /**
     * @author Praveen
     * @uses Saving the bucket
     * @return type
     */
    public function actionSaveBucketDetails(){
        try{
            $postData = json_decode(file_get_contents("php://input"));
            $checkbucket=ServiceFactory::getBucketServiceInstance()->checkBucketName($postData->data->title,$postData->projectId,$postData->data->selectedBucketTypeFilter);
            if($checkbucket=='failure'){
                $saveBucketDetails=ServiceFactory::getBucketServiceInstance()->getSaveBucketDetails($postData);
                $response='success';
                $responseBean = new ResponseBean();
                $responseBean->statusCode = ResponseBean::SUCCESS;
                $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
                $responseBean->data = $response;
                return $response = CommonUtility::prepareResponse($responseBean,"json");
            }else if($checkbucket=='current'){
                $response='current';
                $responseBean = new ResponseBean;
                $responseBean->status = ResponseBean::FAILURE;
                $responseBean->message = "Current bucket is exist";
                $responseBean->data =    $response;
                return $response = CommonUtility::prepareResponse($responseBean,"json");
            }else{
                $response='failure';
                $responseBean = new ResponseBean;
                $responseBean->status = ResponseBean::FAILURE;
                $responseBean->message = "FAILURE";
                $responseBean->data =    $response;
                return $response = CommonUtility::prepareResponse($responseBean,"json");
            }
           
        //    error_log(count($postData->data->notifyEmail)."==save bucket details ------".count($postData->data->sendReminder));
        } catch (Exception $ex){
           Yii::log("BucketController:actionSaveBucketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application'); 
        }
    }
    
    
    /**
     * @author Praveen
     * @uses Updating the bucket
     * @return type
     */
    public function actionUpdateBucketDetails(){
        try{
            $postData = json_decode(file_get_contents("php://input"));
            $checkbucket=ServiceFactory::getBucketServiceInstance()->checkupdateBucketName($postData->data->title,$postData->data->Id,$postData->projectId,$postData->data->selectedBucketTypeFilter,$postData->bucketRole);
            if($checkbucket=='failure'){
                $saveBucketDetails=ServiceFactory::getBucketServiceInstance()->updateBucketDetails($postData);
                $response='success';
                $responseBean = new ResponseBean();
                $responseBean->statusCode = ResponseBean::SUCCESS;
                $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
                $responseBean->data = $response;
                return $response = CommonUtility::prepareResponse($responseBean,"json");
            }else if($checkbucket=='current'){
                $response='current';
                $responseBean = new ResponseBean;
                $responseBean->statusCode = ResponseBean::FAILURE;
                $responseBean->message = "Current bucket is exist";
                $responseBean->data =    $response;
                return $response = CommonUtility::prepareResponse($responseBean,"json");
            }else{
                $response='failure';
                $responseBean = new ResponseBean;
                $responseBean->statusCode = ResponseBean::FAILURE;
                $responseBean->message = "FAILURE";
                $responseBean->data =    $response;
                return $response = CommonUtility::prepareResponse($responseBean,"json");
            }
           
        //    error_log(count($postData->data->notifyEmail)."==save bucket details ------".count($postData->data->sendReminder));
        } catch (Exception $ex){
           Yii::log("BucketController:actionSaveBucketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application'); 
        }
    }
    
    /**
     * @author Praveen
     * @uses Get bucket change status
     * @return type
     */
    public function actionGetBucketChangeStatus(){
        try{
        $postData = json_decode(file_get_contents("php://input"));
        $bucketFilterData=ServiceFactory::getBucketServiceInstance()->getBucketChangeStatus($postData->projectId,$postData->bucketId,$postData->changeStatus);
            if($bucketFilterData=='success'){
            $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = "FAILURE";
            $responseBean->data = $bucketFilterData;
            $response = CommonUtility::prepareResponse($responseBean,"json");
            }else{
            $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::FAILURE;
            $responseBean->message = "FAILURE";
            $responseBean->data = $bucketFilterData;
            $response = CommonUtility::prepareResponse($responseBean,"json");
            }
        
      return $response;  
        } catch (Exception $ex) {
            Yii::log("BucketController:actionGetBucketChangeStatus::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
}

?>
