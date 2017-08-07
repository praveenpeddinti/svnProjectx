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
    
}  
    ?>
