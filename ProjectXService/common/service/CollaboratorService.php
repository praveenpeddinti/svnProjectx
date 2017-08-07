<?php
namespace common\service;
use common\models\mongo\TicketCollection;
use common\components\{CommonUtility,CommonUtilityTwo,ServiceFactory};
use common\models\mysql\WorkFlowFields;
use common\models\mysql\Collaborators;
use common\models\mongo\AccessTokenCollection;
use common\models\mysql\ProjectTeam;
use common\models\mysql\Projects;
use Yii;
use yii\base\ErrorException;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class CollaboratorService {

    /**
     * @author Moin Hussain
     * @description This method to get the collaborators of a project.
     * @param type $ticketId
     * @param type $projectId
     * @return type
     */
   
    
    public function getProjectTeam($projectId){
        try{
         $collaboratorModel = new Collaborators();
         return $collaboratorModel->getProjectTeam($projectId);
        } catch (\Throwable $ex) {
            Yii::error("CollaboratorService:getProjectTeam::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
     /**
    * @author Padmaja
      * @return type 
      * @param type $collabaratorId
     */ 
    public function getCollabaratorAccesstoken($collabaratorId) {
        try {
            $model = new AccessTokenCollection();
            $remembermeStatus= $model->checkCollabaratorStatus($collabaratorId);
            return $remembermeStatus;
        } catch (\Throwable $ex) {
            Yii::error("CollaboratorService:getCollabaratorAccesstoken::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
      /**
    * @author Padmaja
    * @description This is to save the Collabarator accesstoken details 
    * @return type 
    * @param type $accesstoken,$collabaratorId,$browserType,$remembermeStatus
     */     
    public function saveCollabaratortokenData($accesstoken="",$collabaratorId=0,$browserType,$remembermeStatus=""){
        try{
            $model = new AccessTokenCollection();
            return $tokenData= $model->saveAccesstokenData($accesstoken,$collabaratorId,$browserType,$remembermeStatus);
        } catch (\Throwable $ex) {
            Yii::error("CollaboratorService:saveCollabaratortokenData::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
          /**
    * @author Padmaja
    * @description This is to updateStatusCollabarator 
    * @return type 
    * @param type $collabaratortoken
     */      
    public function updateStatusCollabarator($collabaratortoken){
        try{
            $model = new AccessTokenCollection();
            return $tokenData= $model->updateStatusByToken($collabaratortoken);
        } catch (\Throwable $ex) {
            Yii::error("CollaboratorService:updateStatusCollabarator::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
     /**
    * @author Ryan
    * @description This is to get filtered team list in @mention 
    * @return type 
    * @param type $projectId, $search_query
     */      
    public function getFilteredProjectTeam($projectId,$search_query){
        try{
         $collaboratorModel = new Collaborators();
         return $collaboratorModel->getFilteredProjectTeam($projectId,$search_query);
        } catch (\Throwable $ex) {
            Yii::error("CollaboratorService:getFilteredProjectTeam::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
    /**
    * @author Ryan
    * @description This is to get matched user in @mention 
    * @return type 
    * @param type $user
     */   
    public function getMatchedCollaborator($user)
    {
        try{
            $collaboratorModel = new Collaborators();
            return  $collaboratorModel->checkMatchedUsers($user);
            
        } catch (\Throwable $ex) {
            Yii::error("CollaboratorService:getMatchedCollaborator::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
     /**
    * @author Praveen P
    * @description This is to get filtered team list in Followerlist 
    * @return type 
    * @param type $projectId, $search_query,$defaultusers
     */      
    public function getCollaboratorsForFollow($ticketId,$searchValue, $projectId) {
        try {
            $collaboratorModel = new Collaborators();
             $matchArray = array("TicketId" =>(int)$ticketId, "ProjectId" => (int) $projectId);
            $query = Yii::$app->mongodb->getCollection('TicketCollection');
            $pipeline = array(
                array('$match' => $matchArray),
                array(
                    '$group' => array(
                        '_id' => '$TicketId',
                        "followerData" => array('$push' => '$Followers.FollowerId'),
                    ),
                ),
            );
            $Arraytimelog = $query->aggregate($pipeline);
           ;
           // error_log("--data--------".print_r($Arraytimelog,1));
            $dafaultUserList =  $Arraytimelog[0]["followerData"][0];
            return $collaboratorModel->getCollaboratorsForFollow($dafaultUserList,$searchValue, $projectId);
        } catch (\Throwable $ex) {
            Yii::error("CollaboratorService:getCollaboratorsForFollow::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
    /**
    * @author Praveen P
    * @description This method is to used to show the selected user (Stake Holder, Assigned to and Reproted by) in Follower list.
    * @param type $ticketId
    * @param type $projectId
    * @return type
    */      
    public function getTicketFollowersList($ticketId, $projectId) {
        try {
            $ticketDetails = TicketCollection::getTicketDetails($ticketId,$projectId); 
            if(!empty($ticketDetails)){
                $details =  CommonUtility::prepareFollowerDetails($ticketDetails);   
            }
            return $details;
        } catch (\Throwable $ex) {
            Yii::error("CollaboratorService:getTicketFollowersList::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
       /**
     * @authorPadmaja
     * @description This method to get the collaborators of a project.
     * @param type $ticketId
     * @param type $projectId
     * @return type
     */
   
    
    public function getProjectTeamDetailsByRole($projectId){
        try{
         $ProjectTeamModel = new ProjectTeam();
         return $ProjectTeamModel->getProjectTeamDetailsByRole($projectId);
        } catch (\Throwable $ex) {
            Yii::error("CollaboratorService:getProjectTeamDetailsByRole::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
     /**
    * @author Praveen
    * @description This is to get filtered team list in creating buckets 
    * @return type 
    * @param type $projectId, $role
     */      
    public function getResponsibleProjectTeam($projectId,$role){
        try{
         $collaboratorModel = new Collaborators();
         return $collaboratorModel->getResponsibleProjectTeam($projectId,$role);
        } catch (\Throwable $ex) {
            Yii::error("CollaboratorService:getResponsibleProjectTeam::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
    
    /**
     * @authorPadmaja
     * @description This method to verify  the  project.
     * @param type $projectId
     * @return type
     */
   
    
    public function verifyProjectName($projectName){
        try{
         $ProjectModel = new Projects();
         return $ProjectModel->verifyingProjectName($projectName);
        } catch (\Throwable $ex) {
            Yii::error("CollaboratorService:verifyProjectName::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
     /**
     * @authorPadmaja
     * @description This method to verify  the  project.
     * @param type $projectId
     * @return type
     */
    public function savingProjectDetails($projectName,$description,$userId,$projectLogo){
        try{
            $ProjectModel = new Projects();
            $projectId=$ProjectModel->savingProjectDetails($projectName,$description,$userId,$projectLogo);
            return $projectId;
            
        } catch (\Throwable $ex) {
            Yii::error("CollaboratorService:savingProjectDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
      /**
     * @authorPadmaja
     * @description This method to updateProjectlogo
     * @param type $projectId
     * @return type
     */
    public function updateProjectlogo($projectId,$logo){
        try{
            $ProjectModel = new Projects();
            $projectId=$ProjectModel->updatingProjectLog($projectId,$logo);
        }catch (\Throwable $ex) {
            Yii::error("CollaboratorService:updateProjectlogo::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
     /**
     * @authorPadmaja
     * @description This method to verify  the  project.
     * @param type $projectId
     * @return type
     */
     public function savingProjectTeamDetails($projectId,$userId){
        try{
            error_log($projectId."=======".$userId);
            $ProjectModel = new ProjectTeam();
            return $ProjectModel->saveProjectTeamDetails($projectId,$userId);
            
        } catch (\Throwable $ex) {
            Yii::error("CollaboratorService:savingProjectTeamDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
     /**
     * @authorPadmaja
     * @description This method to   project count.
     * @param type $userId
     * @return type
     */
    public function getTotalProjectCount($userId){
        try{
            $ProjectModel = new ProjectTeam();
            $total= $ProjectModel->getProjectsCountByUserId($userId);
            return count($total);
        } catch (\Throwable $ex) {
            Yii::error("CollaboratorService:getTotalProjectCount::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
     /**
     * @authorPadmaja
     * @description This method to   project Names by UserId
     * @param type $userId
     * @return type
     */
    public function getProjectNameByUserId($userId){
        try{
            $ProjectModel = new Projects();
            return $ProjectModel->getProjectNameByUserId($userId);
        } catch (\Throwable $ex) {
            Yii::error("CollaboratorService:getProjectNameByUserId::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
     /**
     * @authorPadmaja
     * @description This method to   save projectlog
     * @param type $userId
     * @return type
     */
    public function saveProjectLogo($logoName){
        try{
                $firstArray =  explode("/", $logoName);
                $secondArray = explode("|", $firstArray[1]);
                $tempFileName = $secondArray[0];
                $originalFileName = $secondArray[1];
                $originalFileName = str_replace("]]", "", $originalFileName);
                $projectLogoPath = Yii::$app->params['ProjectRoot']. Yii::$app->params['projectLogo'] ;
                if(!is_dir($projectLogoPath)){
                             if(!mkdir($projectLogoPath, 0775,true)){
                                 Yii::log("CollaboratorService:saveProjectLog::Unable to create folder--" . $ex->getTraceAsString(), 'error', 'application');
                             }
                }
                $newPath = Yii::$app->params['ServerURL'].Yii::$app->params['projectLogo']."/".$tempFileName."-".$originalFileName;
                if (file_exists("/usr/share/nginx/www/ProjectXService/node/uploads/$tempFileName")) {
                        rename("/usr/share/nginx/www/ProjectXService/node/uploads/$tempFileName",Yii::$app->params['ProjectRoot']. Yii::$app->params['projectLogo']."/".$tempFileName."-".$originalFileName);
                    }
               
                $description= Yii::$app->params['ServerURL'].Yii::$app->params['projectLogo']."/". $tempFileName ."-".$originalFileName;
                return $description;
                } catch (\Throwable $ex) {
                    Yii::error("CollaboratorService:saveProjectLog::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
                    throw new ErrorException($ex->getMessage());
            }
    }
        
    

    /**
     * 
     * @param type $params
     * @return type
     * @throws ErrorException
     * @author  Anand Singh
     * @uses Get user dashboard details.
     */
    public function getUserDashboardDetails($params){
        try{
            $preparedDahboard=array();
            $userId=$params->userInfo->Id;
            $pageLength=$params->projectLimit;
            $pageNo=$params->projectOffset;
            $activityOffset=$params->activityOffset;
            $activityLimit=$params->activityLimit;
            $projectDetails = ProjectTeam::getAllProjects($userId,$pageLength,$pageNo);
            $preparedDahboard['weeklyTimeLog'] =  ServiceFactory::getTimeReportServiceInstance()->getCurrentWeekTimeLog($userId);
            $preparedDahboard['projects'] = CommonUtilityTwo::prepareProjectDetails($projectDetails,$userId);
            $preparedDahboard['activities']= ServiceFactory::getStoryServiceInstance()->getNotifications($userId,0,$activityOffset,$activityLimit,1);
            return $preparedDahboard;
        } catch (\Throwable $ex) {
            Yii::error("CollaboratorService:getUserDashboardDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
}


