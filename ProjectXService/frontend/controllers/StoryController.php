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
     * Get StoryFields from sql table .
     *
     * @author Anand Singh
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionStoryFields(){
        try{
        $status=json_decode('"status":[{"Id":"1","Name":"New"},{"Id":"2","Name":"Accepted"},{"Id":"3","Name":"Specification"}]');
        $post_data = json_decode(file_get_contents("php://input"));
        $responseBean = new ResponseBean;
        $response_data['story_fields'] = ServiceFactory::getStoryServiceInstance()->getStoryFields(1);
        $response_data['plane_level'] = ServiceFactory::getStoryServiceInstance()->getPlanLevel();
        $response_data['priority'] = ServiceFactory::getStoryServiceInstance()->getPriority();
        $response_data['ticket_type'] = ServiceFactory::getStoryServiceInstance()->getTicketType();
        $response_data['collaborators'] = User::getCollabrators();
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
    
  public function actionSaveStory(){
        try{
        $post_data = json_decode(file_get_contents("php://input"));
        $responseBean=new ResponseBean();
        $response_data=$post_data;
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
         Yii::log("SiteController:actionSaveStory::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }  
    
}
?>