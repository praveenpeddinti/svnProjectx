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
    public function actionGetAllTicketDetails(){
        try{
            $StoryData = json_decode(file_get_contents("php://input"));
            $projectId  =  $StoryData->projectId;
        $data = ServiceFactory::getStoryServiceInstance()->getAllStoryDetails($StoryData,$projectId);
        $responseBean = new ResponseBean();  
        $responseBean->statusCode = ResponseBean::SUCCESS;
        $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
        $responseBean->data = $data;
         $responseBean->totalCount = 30;
        $response = CommonUtility::prepareResponse($responseBean,"json");
        return $response;
        //return "praveen P";
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
    */
    public function actionSaveTicketDetails(){
        try{
            error_log("actionSaveTicketDetails--");
             $ticket_data = json_decode(file_get_contents("php://input"));
            error_log("pintir-------------".print_r($ticket_data,1));
//           $userdata =  $ticket_data->userInfo;
//           error_log("userid------".$userdata->Id);
          
            $data = ServiceFactory::getStoryServiceInstance()->saveTicketDetails($ticket_data);
           return "success";
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
           // $fieldData = '{"FieldId":1,"ProjectId":1,"TicketId":3}';
            $postFieldData = json_decode(file_get_contents("php://input"));
//            error_log("***************************>>".$postFieldData->FieldId);
            $responseBean = new ResponseBean();
          //  $responseData['story_fields'] = ServiceFactory::getStoryServiceInstance()->getStoryFieldDataById(5);
            if($postFieldData->FieldId == 5 || $postFieldData->FieldId == 11){
            // get all assigned to details,stakeholeders
                $responseData['getFieldDetails'] = ServiceFactory::getCollaboratorServiceInstance()->getProjectTeam($postFieldData->ProjectId);//$projectId
                //error_log("test##############");
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
    public function actionEditTicketDetails(){
        try{
            error_log("actionEditTicketDetails--");
             $ticket_data = json_decode(file_get_contents("php://input"));
            error_log("pintir-------------".print_r($ticket_data,1));
//           $userdata =  $ticket_data->userInfo;
//           error_log("userid------".$userdata->Id);
          
            //$data = ServiceFactory::getStoryServiceInstance()->saveTicketDetails($ticket_data);
           return "success";
        } catch (Exception $ex) {
        Yii::log("StoryController:saveTicketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
            
        }
?>
