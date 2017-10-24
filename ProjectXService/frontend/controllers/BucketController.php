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
    * @Description This method is used to get all data for Buckets details.
    * @return type
    */
   public function actionGetAllBucketDetails() {
        try { 
            $bucketData = json_decode(file_get_contents("php://input"));
            $bucketDetails=ServiceFactory::getBucketServiceInstance()->getBucketDetails($bucketData);
            $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
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
     * @Description Get collaborators role as Admin for current project
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
     * @Description Get buckets for current project
     * @return type
     */
    public function actionGetBucketFilters(){
        try{
        $postData = json_decode(file_get_contents("php://input"));
        $bucketFilterData=ServiceFactory::getBucketServiceInstance()->getBucketTypeFilter($postData->projectId,$postData->Type,$postData->bucketId);
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
     * @Description Saving the bucket
     * @return type
     */
    public function actionSaveBucketDetails(){
        try{
            $postData = json_decode(file_get_contents("php://input"));
            $checkbucket=ServiceFactory::getBucketServiceInstance()->checkBucketName($postData->data->title,$postData->projectId,"");
            if($checkbucket["available"]=='Yes'){
                $lastBucketId=ServiceFactory::getBucketServiceInstance()->getSaveBucketDetails($postData);
                if($lastBucketId!='failure'){
                  EventTrait::saveEvent($postData->projectId,"Bucket",$lastBucketId,"created","create",$postData->userInfo->Id,[array("ActionOn"=>"bucketcreation","OldValue"=>0,"NewValue"=>(int)$lastBucketId)],array("BucketId"=>(int)$lastBucketId));    
                }
               
                $response=array("status"=>'success',"BucketId"=>$lastBucketId);
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
     * @Description Updating the bucket
     * @return type
     */
    public function actionUpdateBucketDetails(){
        try{
            $postData = json_decode(file_get_contents("php://input"));
            $checkbucket=ServiceFactory::getBucketServiceInstance()->checkBucketName($postData->data->title,$postData->projectId,$postData->bucketId);
            if($checkbucket["available"]=='Yes'){
                $saveBucketDetails=ServiceFactory::getBucketServiceInstance()->updateBucketDetails($postData);
                if($saveBucketDetails["Status"]=="success"){
                        $response=$saveBucketDetails;
                        $responseBean = new ResponseBean();
                        $responseBean->statusCode = ResponseBean::SUCCESS;
                        $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
                        $responseBean->data = $response["data"];
                        return $response = CommonUtility::prepareResponse($responseBean,"json");
                }else{
                    $responseBean = new ResponseBean;
                    $responseBean->statusCode = ResponseBean::FAILURE;
                    $responseBean->message = "Update Failed";
                    $responseBean->data =    "";
                    return $response = CommonUtility::prepareResponse($responseBean,"json");
                }
            }else if($checkbucket["available"]=='No'){
                $response='No';
                $responseBean = new ResponseBean;
                $responseBean->statusCode = ResponseBean::FAILURE;
                $responseBean->message = "Bucket already exists";
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
     * @Description Get bucket change status
     * @return type
     */
    public function actionGetBucketChangeStatus(){
        try{
        $postData = json_decode(file_get_contents("php://input"));
        $bucketFilterData=ServiceFactory::getBucketServiceInstance()->getBucketChangeStatus($postData->projectId,$postData->bucketId,$postData->changeStatus);
            if($bucketFilterData["Status"]=='success'){
            $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = "SUCCESS";
            $responseBean->data = $bucketFilterData;
            $response = CommonUtility::prepareResponse($responseBean,"json");
            }else if($bucketFilterData["Status"]!='failure'){
                $responseBean = new ResponseBean();
                $responseBean->statusCode = ResponseBean::SUCCESS;
                $responseBean->message = $bucketFilterData["Status"];
                $responseBean->data = "" ;
                $response = CommonUtility::prepareResponse($responseBean,"json");
            }else{
            $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::FAILURE;
            $responseBean->message = "FAILURE";
            $responseBean->data = "";
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
     * @Description gets total bucket counts
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
     * @Description gets total bucket counts
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
    /**
     * 
     * @return type
     * @Description gets current week buckets
     */
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
    /**
     * 
     * @return type
     * @Description Used for verfying Bucket name to avoid duplicates
     */
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
    /**
     * 
     * @return type
     * @Description Gets buckets which are not in Current status
     */
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
