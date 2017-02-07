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
        $data = ServiceFactory::getStoryServiceInstance()->getTicketDetails(101,1);
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
    * @author Moin Hussain
    * @description This method is used to get data for edit mode.
    * @return type
    */
    public function actionEditTicket(){
        try{
        $data = ServiceFactory::getStoryServiceInstance()->getTicketEditDetails(101,1);
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
     * 
     * @return string
     */
    public function saveTicketDetails(){
        try{
             $post_data = json_decode(file_get_contents("php://input"));
            error_log("saveTicketDetails-----------") ;
           $data = ServiceFactory::getStoryServiceInstance()->saveTicketDetails();
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
     public function actionStoryFields(){
        try{
        $status=json_decode('"status":[{"Id":"1","Name":"New"},{"Id":"2","Name":"Accepted"},{"Id":"3","Name":"Specification"}]');
        $post_data = json_decode(file_get_contents("php://input"));
        $responseBean = new ResponseBean;
        $response_data['story_fields'] = ServiceFactory::getStoryServiceInstance()->getStoryFieldList();
        $response_data['plane_level'] = ServiceFactory::getStoryServiceInstance()->getPlanLevelList();
        $response_data['priority'] = ServiceFactory::getStoryServiceInstance()->getPriorityList();
        $response_data['ticket_type'] = ServiceFactory::getStoryServiceInstance()->getTicketTypeList();
        $response_data['collaborators'] = ServiceFactory::getCollaboratorServiceInstance()->getProjectTeam(1);;
        $response_data['status'] = $status;
        if(sizeof($response_data)!=0){
        $responseBean->statusCode = ResponseBean::SUCCESS;
        $responseBean->message = "success";
        $responseBean->data = $response_data;   
        }else{
        $responseBean->statusCode = ResponseBean::FAILURE;
        $responseBean->message = "fail";
        $responseBean->data = $response_data; 
        }
        $response = CommonUtility::prepareResponse($responseBean,"json");
        return $response;   
        } catch (Exception $ex) {
         Yii::log("SiteController:actionStoryFields::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
 

}
?>