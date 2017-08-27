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
use common\components\{CommonUtility,EventTrait};
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
        } catch (\Throwable $th) { 
             Yii::error("BucketController:actionGetAllBucketDetails::" . $th->getMessage() . "--" . $th->getTraceAsString(), 'application');
             $responseBean = new ResponseBean();
             $responseBean->statusCode = ResponseBean::SERVER_ERROR_CODE;
             $responseBean->message = ResponseBean::SERVER_ERROR_MESSAGE;
             $responseBean->data = [];
             $response = CommonUtility::prepareResponse($responseBean,"json");
             return $response;
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
        } catch (\Throwable $th) { 
             Yii::error("BucketController:actionGetResponsibleCollaborators::" . $th->getMessage() . "--" . $th->getTraceAsString(), 'application');
             $responseBean = new ResponseBean();
             $responseBean->statusCode = ResponseBean::SERVER_ERROR_CODE;
             $responseBean->message = ResponseBean::SERVER_ERROR_MESSAGE;
             $responseBean->data = [];
             $response = CommonUtility::prepareResponse($responseBean,"json");
             return $response;
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
        } catch (\Throwable $th) { 
             Yii::error("BucketController:actionGetBucketFilters::" . $th->getMessage() . "--" . $th->getTraceAsString(), 'application');
             $responseBean = new ResponseBean();
             $responseBean->statusCode = ResponseBean::SERVER_ERROR_CODE;
             $responseBean->message = ResponseBean::SERVER_ERROR_MESSAGE;
             $responseBean->data = [];
             $response = CommonUtility::prepareResponse($responseBean,"json");
             return $response;
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
            $checkbucket=ServiceFactory::getBucketServiceInstance()->checkBucketName($postData->data->title,$postData->projectId,"");
            if($checkbucket["available"]=='Yes'){
                $lastBucketId=ServiceFactory::getBucketServiceInstance()->getSaveBucketDetails($postData);
                if($lastBucketId!='failure'){
                  EventTrait::saveEvent($postData->projectId,"Bucket",$lastBucketId,"created","create",$postData->userInfo->Id,[array("ActionOn"=>"projectcreation","OldValue"=>0,"NewValue"=>(int)$lastBucketId)],array("BucketId"=>(int)$lastBucketId));    
                }
               
                $response='success';
                $responseBean = new ResponseBean();
                $responseBean->statusCode = ResponseBean::SUCCESS;
                $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
                $responseBean->data = $response;
                return $response = CommonUtility::prepareResponse($responseBean,"json");
            }else if($checkbucket["available"]=='No'){
                $response='current';
                $responseBean = new ResponseBean;
                $responseBean->status = ResponseBean::FAILURE;
                $responseBean->message = "Bucket already exists";
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
        } catch (\Throwable $th) { 
             Yii::error("BucketController:actionSaveBucketDetails::" . $th->getMessage() . "--" . $th->getTraceAsString(), 'application');
             $responseBean = new ResponseBean();
             $responseBean->statusCode = ResponseBean::SERVER_ERROR_CODE;
             $responseBean->message = ResponseBean::SERVER_ERROR_MESSAGE;
             $responseBean->data = [];
             $response = CommonUtility::prepareResponse($responseBean,"json");
             return $response;
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
               // EventTrait::saveEvent($postData->projectId,"Bucket",$postData->data->Id,"updated","update",$postData->userInfo->Id,[array("ActionOn"=>"bucketupdation","OldValue"=>0,"NewValue"=>(int)$lastBucketId)],array("BucketId"=>(int)$lastBucketId));    
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
        } catch (\Throwable $th) { 
             Yii::error("BucketController:actionUpdateBucketDetails::" . $th->getMessage() . "--" . $th->getTraceAsString(), 'application');
             $responseBean = new ResponseBean();
             $responseBean->statusCode = ResponseBean::SERVER_ERROR_CODE;
             $responseBean->message = ResponseBean::SERVER_ERROR_MESSAGE;
             $responseBean->data = [];
             $response = CommonUtility::prepareResponse($responseBean,"json");
             return $response;
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
        } catch (\Throwable $th) { 
             Yii::error("BucketController:actionGetBucketChangeStatus::" . $th->getMessage() . "--" . $th->getTraceAsString(), 'application');
             $responseBean = new ResponseBean();
             $responseBean->statusCode = ResponseBean::SERVER_ERROR_CODE;
             $responseBean->message = ResponseBean::SERVER_ERROR_MESSAGE;
             $responseBean->data = [];
             $response = CommonUtility::prepareResponse($responseBean,"json");
             return $response;
        } 
    }
   
    
    /**
     * @author Ryan
     * @uses gets total bucket counts
     * @return type
     */
    public function actionGetTotalBucketStats(){
        try{
            $postData = json_decode(file_get_contents("php://input"));
            $totalBucketStats=ServiceFactory::getBucketServiceInstance()->getBucketsCount($postData->projectId);
            $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = $totalBucketStats;
            $response = CommonUtility::prepareResponse($responseBean,"json");
            return $response;
        }catch (\Throwable $th) { 
             Yii::error("BucketController:actionGetTotalBucketStats::" . $th->getMessage() . "--" . $th->getTraceAsString(), 'application');
             $responseBean = new ResponseBean();
             $responseBean->statusCode = ResponseBean::SERVER_ERROR_CODE;
             $responseBean->message = $th->getMessage();
             $responseBean->data = [];
             $response = CommonUtility::prepareResponse($responseBean,"json");
             return $response;
        } 
    } 
    
    /**
     * @author Ryan
     * @uses gets total bucket counts
     * @return type
     */
    public function actionGetBuckets(){
        try{
            $postData = json_decode(file_get_contents("php://input"));
            $bucketsInfo=ServiceFactory::getBucketServiceInstance()->getBucketsForProject($postData->projectId,$postData->type);
            $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = $bucketsInfo;
            $response = CommonUtility::prepareResponse($responseBean,"json");
            return $response;
        }catch (\Throwable $th) { 
             Yii::error("BucketController:actionGetBuckets::" . $th->getMessage() . "--" . $th->getTraceAsString(), 'application');
             $responseBean = new ResponseBean();
             $responseBean->statusCode = ResponseBean::SERVER_ERROR_CODE;
             $responseBean->message = $th->getMessage();
             $responseBean->data = [];
             $response = CommonUtility::prepareResponse($responseBean,"json");
             return $response;
        } 
    }
    
    public function actionGetCurrentWeekBuckets(){
        try{
            $postData = json_decode(file_get_contents("php://input"));
            $currentWeekBucketsInfo=ServiceFactory::getBucketServiceInstance()->getCurrentWeekBuckets($postData->projectId);
            $count=ServiceFactory::getBucketServiceInstance()->getMoreCountBuckets($postData->projectId);
            $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = $currentWeekBucketsInfo;
            $responseBean->totalCount=$count;
            $response = CommonUtility::prepareResponse($responseBean,"json");
            return $response;
        } catch (\Throwable $th) { 
             Yii::error("BucketController:actionGetCurrentWeekBuckets::" . $th->getMessage() . "--" . $th->getTraceAsString(), 'application');
             $responseBean = new ResponseBean();
             $responseBean->statusCode = ResponseBean::SERVER_ERROR_CODE;
             $responseBean->message = $th->getMessage();
             $responseBean->data = [];
             $response = CommonUtility::prepareResponse($responseBean,"json");
             return $response;
        } 
    }

    public function actionGetBucketStoryActivities(){
        $postData = json_decode(file_get_contents("php://input"));
        $storyActivitiesData=ServiceFactory::getBucketServiceInstance()->getBucketStoryActivities($postData->projectId,$postData->bucketId);
        
        error_log("++++++++++actionGetBucketStoryActivities++++++++++++".print_r($postData,1));
        $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = $storyActivitiesData;
            $response = CommonUtility::prepareResponse($responseBean,"json");
      return $response;
    }
    
    public function actionCheckBucketName(){
        $postData = json_decode(file_get_contents("php://input"));
        $availablity=ServiceFactory::getBucketServiceInstance()->checkBucketName($postData->bucketName,$postData->projectId);
        
        error_log("++++++++++actionCheckBucketName++++++++++++".print_r($postData,1));
        $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            if($availablity["available"] == "Yes"){
                $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            }else{
                $responseBean->message = "Bucket already exists..!";
            }
            $responseBean->data = $availablity;
            $response = CommonUtility::prepareResponse($responseBean,"json");
      return $response;
    }
    
    public function actionGetOtherBuckets(){
        try{
        $postData = json_decode(file_get_contents("php://input"));
        $otherBuckets=ServiceFactory::getBucketServiceInstance()->getMoreCountBuckets($postData->projectId,1);
        $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = $otherBuckets;
            $response = CommonUtility::prepareResponse($responseBean,"json");
            return $response;
        }catch (\Throwable $th) { 
             Yii::error("BucketController:actionGetOtherBuckets::" . $th->getMessage() . "--" . $th->getTraceAsString(), 'application');
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
