<?php
namespace frontend\controllers;

use Yii;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\filters\Cors;
use yii\helpers\ArrayHelper;
use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use common\models\mongo\SampleCollection;
use common\models\mongo\ProjectTicketSequence;
use common\models\mongo\AccessTokenCollection;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;
use common\components\{CommonUtility,EventTrait};
use common\components\CommonUtilityTwo;
use common\models\bean\ResponseBean;
use common\models\mysql\Projects;//testing
use frontend\service\AccesstokenService;
use common\components\ServiceFactory;
use common\models\mysql\Collaborators;
use common\models\mongo\TinyUserCollection;
use common\models\mysql\ProjectTeam;
use common\service\CollaboratorService;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
         
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('index');
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
   
    public function actionTestAjax()
    {

        error_log("----------------dddddddddddd");
        $model = new Projects();
       $userdata = $model->listUserData();
     //   $userData=User::model()->getAllUser();
      $data = SampleCollection::testMongo();
        //print_r($userdata,1);

        error_log("----------------");
       // $data = SampleCollection::testMongo();
       $model = new ProjectTicketSequence();
       $model->getNextSequence(2);
      // $db->getNextSequence(1);
      // $db->insert(array("TicketNumber" => $this->getNextSequence(2),"name" => "Sarah C."));

       // error_log("+++++++++++++actionTestAjax+++++++++++++++++++".print_r($data,1));
        //error_log("+++++++++++++actionTestAjax@@@@@@@@@@@@@@@@@+++++++++++++++++++".print_r($userdata,1));
    
    }
    /**
     * @author Moin Hussain
     * @description This is sample method to demonstrate the response
     * @return type
     * Try this in browser http://10.10.73.33/site/sample-response
     */
    public function actionSampleResponse(){
        try{
        $data = ["firstName" => "Moin", "lastName" => "Hussain"];
        $responseBean = new ResponseBean;
        $responseBean->statusCode = ResponseBean::SUCCESS;
        $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
        $responseBean->data = $data;
        $response = CommonUtility::prepareResponse($responseBean,"xml");
        return $response;   
        } catch (Exception $ex) {
         Yii::log("SiteController:actionSampleResponse::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
       
    
    }
    
    public function actionLogin()
    {

        error_log("sssssssssssssssssssssssssssss");
        foreach ($_SERVER as $name => $value) {
   // error_log($name."----".$value,"---");
        }
        //error_log("@@@---**".print_r($_SERVER,1));

        error_log("actionLogin------");

        $user_data = json_decode(file_get_contents("php://input"));
       //error_log("request aprams-----------".print_r($user_data,1));
        $model = new LoginForm();
        $userData = $model->loginAjax($user_data);error_log("34444444444".print_r($userData,1));
        //error_log("use dat---".print_r($userData,1));
        $responseBean = new ResponseBean;
        $responseBean->status = ResponseBean::SUCCESS;
        $responseBean->message = "success";
        $responseBean->data = $userData;
        $response = CommonUtility::prepareResponse($responseBean,"json");
        return $response;
            
        }
   /**
     * @author Padmaja
     * @description This is authenticate the  Collaborator data
     * @return type json object
     * 
     */    

    public function actionUserAuthentication(){
        try{
            $CollabaratorData = json_decode(file_get_contents("php://input"));
            $model = new LoginForm();
            $getcollaboratorData = $model->checkLoginData($CollabaratorData);
              if(count($getcollaboratorData)==1 && $getcollaboratorData !='failure'){
                $collabaratorId=$getcollaboratorData[0]['Id'];
                $collaboratorProfileData = TinyUserCollection::getMiniUserDetails($collabaratorId);
                $remembermeStatus=isset($CollabaratorData->rememberme)?1:0;
                $collabaratorTokenData = ServiceFactory::getCollaboratorServiceInstance()->getCollabaratorAccesstoken($collabaratorId);
                if(count($collabaratorTokenData)==0){
                    $accesstoken =  $this->GeneratingAccesstoken($collabaratorId);
                    $collabaratorArr=array('Id'=>$collabaratorId,'username'=>$getcollaboratorData[0]['UserName'],"token"=>$accesstoken,"ProfilePicture"=>$collaboratorProfileData['ProfilePicture'],"Email"=>$collaboratorProfileData['Email']);
                    $browserType=$_SERVER['HTTP_USER_AGENT'];
                    $getLastId = ServiceFactory::getCollaboratorServiceInstance()->saveCollabaratortokenData($accesstoken,$collabaratorId,$browserType,$remembermeStatus);
                    $responseBean = new ResponseBean;
                    $responseBean->status = ResponseBean::SUCCESS;
                    $responseBean->message = "success";
                    $responseBean->data =    $collabaratorArr;
                    return  $response = CommonUtility::prepareResponse($responseBean,"json");
                }else if(count($collabaratorTokenData)>0 && $collabaratorTokenData[0]['Status']==1) {
                    $collabaratorLastToken= $collabaratorTokenData[0]['Accesstoken'];
                    $collabaratorArr=array('Id'=>$collabaratorId,'username'=>$getcollaboratorData[0]['UserName'],"token"=>$collabaratorLastToken,"ProfilePicture"=>$collaboratorProfileData['ProfilePicture'],"Email"=>$collaboratorProfileData['Email']);
                    $accesstoken="response";
                    $responseBean = new ResponseBean;
                    $responseBean->status = ResponseBean::SUCCESS;
                    $responseBean->message = "success";
                    $responseBean->data =    $collabaratorArr;
                    return  $response = CommonUtility::prepareResponse($responseBean,"json");
                }else if(count($collabaratorTokenData)>0 && $collabaratorTokenData[0]['Status']==0){
                    $accesstoken =  $this->GeneratingAccesstoken($collabaratorId);
                    $collabaratorArr=array('Id'=>$collabaratorId,'username'=>$getcollaboratorData[0]['UserName'],"token"=>$accesstoken,"ProfilePicture"=>$collaboratorProfileData['ProfilePicture'],"Email"=>$collaboratorProfileData['Email']);
                    $browserType=$_SERVER['HTTP_USER_AGENT'];
                    $getLastId = ServiceFactory::getCollaboratorServiceInstance()->saveCollabaratortokenData($accesstoken,$collabaratorId,$browserType,$remembermeStatus);
                    $responseBean = new ResponseBean;
                    $responseBean->status = ResponseBean::SUCCESS;
                    $responseBean->message = "success";
                    $responseBean->data =    $collabaratorArr;
                    return  $response = CommonUtility::prepareResponse($responseBean,"json");
                }
                   
           }else{
                    $response='failure';
                    $responseBean = new ResponseBean;
                    $responseBean->status = ResponseBean::FAILURE;
                    $responseBean->message = "FAILURE";
                    $responseBean->data =    $response;
                    return  $response = CommonUtility::prepareResponse($responseBean,"json");
           }
       
            
        } catch (Exception $ex) {
            Yii::log("SiteController:UserAuthentication::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
 
    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {  
       Yii::$app->user->logout();
        return $this->goHome();
    }
    
    /**
     * @author Padmaja
     * @description This is update active Collabarator status to inActive when  logout
     * @return type json object
     * 
     */ 
    public function actionUpdateCollabaratorStatus(){
        try{
//          $headerff=Yii::$app->request->getHeaders()->get('Authorization');
            $collabaratorJson = json_decode(file_get_contents("php://input"));
            $collabaratorToken=$collabaratorJson->userInfo->token;
            $updateStatus  = ServiceFactory::getCollaboratorServiceInstance()->updateStatusCollabarator($collabaratorToken);
            $responseBean     = new ResponseBean;
            $responseBean->status = ResponseBean::SUCCESS;
            $responseBean->message = "success";
            $responseBean->data =    $updateStatus;
            return  $response = CommonUtility::prepareResponse($responseBean,"json");
        } catch (Exception $ex) {
             Yii::log("SiteController:UpdateCollabaratorStatus::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
       
        
    } 
    /**
     * Displays contact page.
     *
     * @return mixed
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
            } else {
                Yii::$app->session->setFlash('error', 'There was an error sending email.');
            }

            return $this->refresh();
        } else {
            return $this->render('contact', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Displays about page.
     *
     * @return mixed
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
                if (Yii::$app->getUser()->login($user)) {
                    return $this->goHome();
                }
            }
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            } else {
                Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for email provided.');
            }
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password was saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }
    /**
     * Get Collaborators from sql table and insert into mongo document.
     *
     * @author Anand Singh
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionCollaborators(){
          try{
            $coll =  new Collaborators();
        $collaborators = $coll->getCollabrators();
        $response=  TinyUserCollection::createUsers($collaborators);
        $responseBean = new ResponseBean;
        $responseBean->status = ResponseBean::SUCCESS;
        $responseBean->message = "success";
        $responseBean->data = $collaborators;
        $response = CommonUtility::prepareResponse($responseBean,"json");
        return $response;   
        } catch (Exception $ex) {
         Yii::log("SiteController:actionCollaborators::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
    public function actionInsertCollaborators(){
          try{
        $collaborators = User::insertCollabrators(10000);
        $responseBean = new ResponseBean;
        $responseBean->status = ResponseBean::SUCCESS;
        $responseBean->message = "success";
        $responseBean->data = $collaborators;
        $response = CommonUtility::prepareResponse($responseBean,"json");
        return $response;   
        } catch (Exception $ex) {
         Yii::log("SiteController:actionCollaborators::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
      /**
     * @author Padmaja
     * @description This is for getSearch results
     * @return type json object
     * 
     */ 
    public function actionGlobalSearch(){
        try{
            $postData = json_decode(file_get_contents("php://input"));
            $searchFlag=!empty($postData->searchFlag)?$postData->searchFlag:""; 
            $projectId=!empty($postData->projectId)?$postData->projectId:"";
            $userId=!empty($postData->userInfo->Id)?$postData->userInfo->Id:"";
            $pageLength=Yii::$app->params['pageLength'];
            $searchData = CommonUtility::getAllDetailsForSearch($postData->searchString,$postData->page,$searchFlag,$projectId,$pageLength,$userId); 
            if(empty($searchData['ticketCollection']) && empty($searchData['ticketComments']) && empty($searchData['ticketArtifacts'])&& empty($searchData['tinyUserData'])){
                $responseBean = new ResponseBean;
                $responseBean->status = ResponseBean::FAILURE;
                $responseBean->message = "failure";
                $responseBean->data = $searchData;
                $response = CommonUtility::prepareResponse($responseBean,"json"); 
           
            }else{
                $responseBean = new ResponseBean;
                $responseBean->status = ResponseBean::SUCCESS;
                $responseBean->message = "success";
                $responseBean->data = $searchData;
                $response = CommonUtility::prepareResponse($responseBean,"json");
            }
            return $response;
        } catch (Exception $ex) {
              Yii::log("SiteController:actionGlobalSearch::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
   /**
    * @author Padmaja
    *  @description This is used for verifying projects is exists or not
    * @param type 
    * @return array
    */
    public function actionVerifyingProjectName(){
        try{
            $postData = json_decode(file_get_contents("php://input"));
            error_log("prjectctcccccc@@@@@@@@@@@@-------".$postData->projectName);
            $getProjectDetails=ServiceFactory::getCollaboratorServiceInstance()->verifyProjectName($postData->projectName);
            error_log("prjectctcccccc".print_r($getProjectDetails,1));
            if(!empty($getProjectDetails)){
                $responseBean = new ResponseBean;
                $responseBean->status = ResponseBean::SUCCESS;
                $responseBean->message = "success";
                $responseBean->data = $getProjectDetails;
                $response = CommonUtility::prepareResponse($responseBean,"json"); 
            }else{
                $responseBean = new ResponseBean;
                $responseBean->status = ResponseBean::FAILURE;
                $responseBean->message = "failure";
                $responseBean->data = $getProjectDetails;
                $response = CommonUtility::prepareResponse($responseBean,"json"); 
            }
            return $response;
            error_log("resonse------------".print_r($response,1));
        } catch (Exception $ex) {
             Yii::log("SiteController:actionVerifyingProjectName::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
     /**
    * @author Padmaja
    * @description This is used for saving project details
    * @param type 
    * @return array
    */
    public function actionSaveProjectDetails(){
        try{
            $postData = json_decode(file_get_contents("php://input"));
            $savingStatus=ServiceFactory::getCollaboratorServiceInstance()->savingProjectDetails($postData->projectName,$postData->description,$postData->userInfo->Id);
            if($savingStatus!='failure'){
                error_log("ssssssssssssssss");
                $projectId=$savingStatus;
                $getStatus=ServiceFactory::getCollaboratorServiceInstance()->savingProjectTeamDetails($projectId,$postData->userInfo->Id);
                $getlastIdDetails= CommonUtilityTwo::getLastProjectDetails($projectId,$postData->userInfo->Id);
                // error_log("status-----------".$savingStatus);
            }
             if($getStatus == 'failure' || $savingStatus=='failure'){
                $responseBean = new ResponseBean;
                $responseBean->status = ResponseBean::FAILURE;
                $responseBean->message = "failure";
                $responseBean->data = $getlastIdDetails;
               // $responseBean->lastProjectDetails =$getlastIdDetails;
                $response = CommonUtility::prepareResponse($responseBean,"json"); 
            }else{
                EventTrait::saveEvent($projectId,"Project",$projectId,"created","create",$postData->userInfo->Id,[array("ActionOn"=>"projectcreation","OldValue"=>0,"NewValue"=>(int)$projectId)]); 
                $responseBean = new ResponseBean;
                $responseBean->status = ResponseBean::SUCCESS;
                $responseBean->message = "success";
                $responseBean->data = $getlastIdDetails;
               // $responseBean->lastProjectDetails =$getlastIdDetails;
                $response = CommonUtility::prepareResponse($responseBean,"json");
         
            }
            return $response;
        } catch (Exception $ex) {
            Yii::log("SiteController:actionsaveProjectDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
        /**
    * @author Padmaja
     * @description This is used for get all project details
    * @param type 
    * @return array
    */
    public function actionGetAllProjectsByUser(){
        try{
            $postData = json_decode(file_get_contents("php://input"));
            $projectFlag=!empty($postData->projectFlag)?$postData->projectFlag:"";
            $limit=!empty($postData->limit)?$postData->limit:"";
            $projectId=!empty($postData->projectId)?$postData->projectId:"";
            $activityDropdownFlag=!empty($postData->activityDropdownFlag)?$postData->activityDropdownFlag:"";
            $projectInfo = ServiceFactory::getStoryServiceInstance()->getProjectDetailsForDashboard($postData->userInfo->Id,$postData->page,$limit,$projectFlag,$postData->activityPage,$projectId,$activityDropdownFlag);
            $totalProjectCount=ServiceFactory::getCollaboratorServiceInstance()->getTotalProjectCount($postData->userInfo->Id);
           // error_log("---tttt----".print_r($projectInfo,1));
            if(!empty($projectInfo['ProjectwiseInfo']) || empty($projectInfo['ProjectwiseInfo'])){
                $responseBean = new ResponseBean;
                $responseBean->status = ResponseBean::SUCCESS;
                $responseBean->message = "success";
//                error_log("------tttt-------".print_r($projectInfo,1));
                if(!empty($projectInfo['ProjectwiseInfo']) || !empty($projectInfo['ActivityData'])){
                    $responseBean->data = $projectInfo;
                }else{
                     $responseBean->data = "No Results Found";
                }
                $responseBean->totalProjectCount = $totalProjectCount;
                $response = CommonUtility::prepareResponse($responseBean,"json"); 
            }else{
                $responseBean = new ResponseBean;
                $responseBean->status = ResponseBean::FAILURE;
                $responseBean->message = "failure";
                $responseBean->data = $projectInfo;
                $responseBean->totalProjectCount = $totalProjectCount;
                $response = CommonUtility::prepareResponse($responseBean,"json"); 
            }
            return $response;
            
        } catch (Exception $ex) {
            Yii::log("SiteController:actionScrollProjectsByUser::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
        
    }
         /**
    * @author Padmaja
   * @description This is used for showing list of projects in dropdoown
    * @param type 
    * @return array
    */
    public function actionGetProjectNameByUserid(){
       try{
            $postData = json_decode(file_get_contents("php://input"));
            $userId=!empty($postData->userId)?$postData->userId:"";
            $projectsInfo=ServiceFactory::getCollaboratorServiceInstance()->getProjectNameByUserId($userId);
              if(!empty($projectsInfo)){
                $responseBean = new ResponseBean;
                $responseBean->status = ResponseBean::SUCCESS;
                $responseBean->message = "success";
                $responseBean->data = $projectsInfo;
                $response = CommonUtility::prepareResponse($responseBean,"json"); 
            }else{
                $responseBean = new ResponseBean;
                $responseBean->status = ResponseBean::FAILURE;
                $responseBean->message = "failure";
                $responseBean->data = $projectsInfo;
                $response = CommonUtility::prepareResponse($responseBean,"json"); 
            }
            return $response;
       } catch (Exception $ex) {
            Yii::log("SiteController:actionGetProjectNameByUserid::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
       } 
    }
    
//    public function actionGetActivitiesByProjectid(){
//        try{
//             $postData = json_decode(file_get_contents("php://input"));
//             $projectsInfo=ServiceFactory::getCollaboratorServiceInstance()->getProjectNameByUserId($userId);
//             
//        } catch (Exception $ex) {
//             Yii::log("SiteController:actionGetActivitiesByProjectid::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
//        }
//    }
           
}
?>