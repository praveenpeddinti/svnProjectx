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
    * @author Praveen P
    * @Description This method is used to get all data for Work log details.
    * @return type
    */
   public function actionGetTimeReportDetails() {
        try { 
            $StoryData = json_decode(file_get_contents("php://input"));
            $projectId = $StoryData->projectId; 
            $data = ServiceFactory::getTimeReportServiceInstance()->getAllTimeReportDetails($StoryData, $projectId);
            $arrayTimelog = ServiceFactory::getTimeReportServiceInstance()->getTimeReportCountAndWorkLog($StoryData,$projectId);
            $totalCount = 0;
            $totalWorkLogHours = 0;
            $totalHoursOfEachMember=array();
            $totalHours=0;
            if(!empty($arrayTimelog)){
                foreach($arrayTimelog as $timeLog){ error_log("in for each==");
                    if(count($timeLog)>0){
                     $totalCount+=  $timeLog["count"];
                     $totalWorkLogHours =  number_format(round($timeLog["totalHours"],2),2);
                     $totalHours+=$totalWorkLogHours;
                  }
                }
                array_push($totalHoursOfEachMember,$totalHours);
            }
            $fromDate = CommonUtility::convert_date_zone(strtotime($StoryData->fromDate),$StoryData->timeZone);
            $toDate = CommonUtility::convert_date_zone(strtotime($StoryData->toDate),$StoryData->timeZone);
            $finalData = array("fromDate"=>$fromDate,"toDate"=>$toDate,"data"=>$data,"totalHours"=>$totalHoursOfEachMember);
            $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = $finalData;
            $responseBean->totalCount = $totalCount;
            $response = CommonUtility::prepareResponse($responseBean, "json");
            return $response;
        } catch (\Throwable $th) { 
             Yii::error("TimeReportController:actionGetTimeReportDetails::" . $th->getMessage() . "--" . $th->getTraceAsString(), 'application');
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
    * @Description updating timelog details
    * @return type
    */
    public function actionUpdateTimelog(){
        try{
            $ticketData = json_decode(file_get_contents("php://input"));
            $updatedData=ServiceFactory::getTimeReportServiceInstance()->updateTimelog($ticketData);
            $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = $updatedData;
            $response = CommonUtility::prepareResponse($responseBean, "json");
            return $response;
            
        } catch (\Throwable $th) { 
             Yii::error("TimeReportController:actionUpdateTimelog::" . $th->getMessage() . "--" . $th->getTraceAsString(), 'application');
             $responseBean = new ResponseBean();
             $responseBean->statusCode = ResponseBean::SERVER_ERROR_CODE;
             $responseBean->message = ResponseBean::SERVER_ERROR_MESSAGE;
             $responseBean->data = [];
             $response = CommonUtility::prepareResponse($responseBean,"json");
             return $response;
        } 

    }
    /**
    * @author Padmaja
    * @Description get dearchdetails for timelog 
    * @return type
    */
    public function actionGetStoryDetailsForTimelog(){
        try{
            $searchData = json_decode(file_get_contents("php://input"));
            $projectId = $searchData->projectId;
            $sortvalue = $searchData->sortvalue;
            $searchString = $searchData->searchString;
            $getSearchDetails=ServiceFactory::getTimeReportServiceInstance()->getAllStoryDetailsForTimelog($projectId,$sortvalue, $searchString);
                $responseBean = new ResponseBean();
                $responseBean->statusCode = ResponseBean::SUCCESS;
                $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
                $responseBean->data = $getSearchDetails;
                $response = CommonUtility::prepareResponse($responseBean, "json");
        return $response;
         } catch (\Throwable $th) { 
             Yii::error("TimeReportController:actionGetStoryDetailsForTimelog::" . $th->getMessage() . "--" . $th->getTraceAsString(), 'application');
             $responseBean = new ResponseBean();
             $responseBean->statusCode = ResponseBean::SERVER_ERROR_CODE;
             $responseBean->message = ResponseBean::SERVER_ERROR_MESSAGE;
             $responseBean->data = [];
             $response = CommonUtility::prepareResponse($responseBean,"json");
             return $response;
        } 
    }
    /**
    * @author Padmaja
    * @Description adding timelog details
    * @return type
    */
    public function actionAddTimelog(){
        try{
            $timelogData = json_decode(file_get_contents("php://input"));
            $getTimelogData=ServiceFactory::getStoryServiceInstance()->insertTimeLog($timelogData);
            $responseBean = new ResponseBean();
            $responseBean->statusCode = ResponseBean::SUCCESS;
            $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
            $responseBean->data = $getTimelogData;
            $response = CommonUtility::prepareResponse($responseBean, "json");
            return $response; 
        } catch (\Throwable $th) { 
             Yii::error("TimeReportController:actionAddTimelog::" . $th->getMessage() . "--" . $th->getTraceAsString(), 'application');
             $responseBean = new ResponseBean();
             $responseBean->statusCode = ResponseBean::SERVER_ERROR_CODE;
             $responseBean->message = ResponseBean::SERVER_ERROR_MESSAGE;
             $responseBean->data = [];
             $response = CommonUtility::prepareResponse($responseBean,"json");
             return $response;
        } 

    }
    /**
    * @author Padmaja
    * @Description adding removing details
    * @return type
    */
    public function actionRemoveTimelog(){
        try{
             $timelogData = json_decode(file_get_contents("php://input"));
             $getTimelogData= ServiceFactory::getTimeReportServiceInstance()->RemoveTimelogs($timelogData);
             $responseBean = new ResponseBean();
             $responseBean->statusCode = ResponseBean::SUCCESS;
             $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
             $responseBean->data = $getTimelogData;
             $response = CommonUtility::prepareResponse($responseBean, "json");
             return $response;

        } catch (\Throwable $th) { 
             Yii::error("TimeReportController:actionRemoveTimelog::" . $th->getMessage() . "--" . $th->getTraceAsString(), 'application');
             $responseBean = new ResponseBean();
             $responseBean->statusCode = ResponseBean::SERVER_ERROR_CODE;
             $responseBean->message = ResponseBean::SERVER_ERROR_MESSAGE;
             $responseBean->data = [];
             $response = CommonUtility::prepareResponse($responseBean,"json");
             return $response;
        } 

    }
    /**
     * 
     * @return type
     * @Description Gets time log details by activity ID(Slug)
     */
    public function actionGetTimelogDetailsBySlug(){
      try{ 
         $timelogData = json_decode(file_get_contents("php://input"));
        $timelogDetails=  TicketTimeLog::getTimeLogRecordsBySlug($timelogData->projectId,$timelogData->ticketId,$timelogData->slug);
     }catch (\Throwable $th) { 
             Yii::error("TimeReportController:actionGetTimelogDetailsBySlug::" . $th->getMessage() . "--" . $th->getTraceAsString(), 'application');
             $responseBean = new ResponseBean();
             $responseBean->statusCode = ResponseBean::SERVER_ERROR_CODE;
             $responseBean->message = ResponseBean::SERVER_ERROR_MESSAGE;
             $responseBean->data = [];
             $response = CommonUtility::prepareResponse($responseBean,"json");
             return $response;
        } 
    }
}



?>
