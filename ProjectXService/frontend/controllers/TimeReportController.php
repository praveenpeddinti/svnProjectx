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
            Yii::log("TimeReportController:actionGetTimeReportDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
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
           /**
     * @author Padmaja
     * @uses get dearchdetails for timelog 
     * @return type
     */
    public function actionGetStoryDetailsForTimelog(){
        try{
            $searchData = json_decode(file_get_contents("php://input"));
            $projectId = $searchData->projectId;
           // $ticketId = $searchData->ticketId;
            $sortvalue = $searchData->sortvalue;
            $searchString = $searchData->searchString;
            $getSearchDetails = TicketCollection::getAllStoryDetailsForTimelog($projectId,$sortvalue, $searchString);
            if(!empty($getSearchDetails)){
                $responseBean = new ResponseBean();
                $responseBean->statusCode = ResponseBean::SUCCESS;
                $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
                $responseBean->data = $getSearchDetails;
                $response = CommonUtility::prepareResponse($responseBean, "json");
            }else{
                $responseBean = new ResponseBean;
                $responseBean->status = ResponseBean::FAILURE;
                $responseBean->message = "failure";
                $responseBean->data = $getSearchDetails;
                $response = CommonUtility::prepareResponse($responseBean,"json"); 
                }
        return $response;
         } catch (Exception $ex) {
             Yii::log("TimeReportController:actionGetStoryDetailsForTimelog::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
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
