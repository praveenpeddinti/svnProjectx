<?php
namespace common\service;
use common\models\mongo\TicketCollection;
use common\components\{CommonUtilityTwo,EventTrait};
use common\models\mysql\WorkFlowFields;
use common\models\mysql\Collaborators;
use common\models\mysql\Bucket;
use common\models\mysql\BucketType;
use common\models\mysql\BucketStatus;
use common\models\mongo\AccessTokenCollection;
use common\models\mysql\ProjectTeam;
use common\models\mysql\Projects;
use common\models\mysql\RepoPermissions;
use Yii;
use yii\base\ErrorException;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class ProjectService {
   /**
     * @author Padmaja
     * @Description This method to verify  the  project.
     * @param type $projectId
     * @return type
     */
    public function verifyProjectName($projectName) {
        try {
            $ProjectModel = new Projects();
            return $ProjectModel->verifyingProjectName($projectName);
        } catch (\Throwable $ex) {
            Yii::error("ProjectService:verifyProjectName::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
        /**
     * @author Padmaja
     * @Description This method to saving  the  project.
     * @param type $projectId
     * @param type $description
     * @param type $userId
     * @param type $projectLogo
     * @return type
     */
    public function savingProjectDetails($projectName, $description, $userId, $projectLogo) {
        try {
            $ProjectModel = new Projects();
            $projectId = $ProjectModel->savingProjectDetails($projectName, $description, $userId, $projectLogo);
            
            $bucket = new Bucket();
            $bucket->ProjectId = (int)$projectId;
            $bucket->Name = "Backlog";
            $bucket->Description = "";
            $bucket->StartDate = "";
            $bucket->DueDate = "";
            $bucket->Responsible = (int)$userId;
            $bucket->BucketStatus = (int)1;
            $bucket->Status = (int)1;
            if($bucket->save()){
               error_log("-------Id--------".$bucket->Id);
               $bucketId = $bucket->Id;
          }
            
            
            
           EventTrait::saveEvent($projectId,"Project",$projectId,"created","create",$userId,[array("ActionOn"=>"projectcreation","OldValue"=>0,"NewValue"=>(int)$projectId)]); 
           EventTrait::saveEvent($projectId,"Bucket",$bucketId,"created","create",$userId,[array("ActionOn"=>"bucketcreation","OldValue"=>0,"NewValue"=>(int)$bucketId)],array("BucketId"=>(int)$bucketId));    
            
            return $projectId;
        } catch (\Throwable $ex) {
            Yii::error("ProjectService:savingProjectDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
       /**
     * @author Padmaja
     * @Description This method to savingProjectTeamDetails.
     * @param type $projectId
     * @return type
     */
    public function savingProjectTeamDetails($projectId, $userId) {
        try {
            error_log($projectId . "=======" . $userId);
            $ProjectModel = new ProjectTeam();
            return $ProjectModel->saveProjectTeamDetails($projectId, $userId);
        } catch (\Throwable $ex) {
            Yii::error("ProjectService:savingProjectTeamDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    /**
     * 
     * @param type $projectName
     * @param type $description
     * @param type $fileExt
     * @param type $projectLogo
     * @param type $projectId
     * @param type $userId
     * @return type
     * @throws ErrorException
     * @Description Updates Project Details on edit
     */
    public function updatingProjectDetails($projectName, $description, $fileExt, $projectLogo, $projectId,$userId='') {
        try {
            if (strpos($projectLogo, 'assets') !== false) {
                $logo = $projectLogo;
            } else {
                $extractUrl = explode('projectlogo/', $projectLogo);
                $projectLogoPath = Yii::$app->params['ProjectRoot'] . Yii::$app->params['projectLogo'];
                if (file_exists($projectLogoPath . "/" . $extractUrl[1])) {
                    error_log("eee-----aaaaaa--" . $fileExt);
                    if (empty($fileExt) || $fileExt == '') {
                        error_log("aaaaaa-------------" . $extractUrl[1]);
                        rename($projectLogoPath . "/" . $extractUrl[1], $projectLogoPath . "/" . $extractUrl[1]);
                        $logo = Yii::$app->params['projectLogo'] . '/' . $extractUrl[1];
                    } else {
                         rename($projectLogoPath . "/" . $extractUrl[1], $projectLogoPath . "/" . $projectName . "_" . $projectId . ".$fileExt");
                        $logo = Yii::$app->params['projectLogo'] . '/' . $projectName . "_" . $projectId . ".$fileExt";
                    }
                } else {
                     $logo = Yii::$app->params['projectLogo'] . '/' . $projectName . "_" . $projectId . ".$fileExt";
                    error_log("not existeddddddddd----");
                }
            }
            error_log("not existeddddddddd@@@@@@@@@@@@@--------" . $logo);
            $summary = array();
            $ProjectModel = new Projects();
            $Oldprojects=Projects::findOne($projectId);
            if(trim($Oldprojects->ProjectName)!= trim($projectName))
                array_push($summary,array("ActionOn"=>  'projectName',"OldValue"=>trim(strip_tags($Oldprojects->ProjectName)) ,"NewValue"=>trim(strip_tags($projectName))));
            if(trim($Oldprojects->Description)!= trim($description))
                array_push($summary,array("ActionOn"=>  'projectDescription',"OldValue"=>trim(strip_tags($Oldprojects->Description)) ,"NewValue"=>trim(strip_tags($description))));

            $updateStatus = $ProjectModel->updateProjectDetails($projectName, $description, $fileExt, $logo, $projectId);
            if($updateStatus == 'success'){
              EventTrait::saveEvent((int)$projectId,"Project",$projectId,"updated",'update',(int)$userId,$summary,array("BucketId"=>""));
            }
            $updateStatus = $ProjectModel->updateProjectDetails($projectName, $description, $fileExt, $logo, $projectId);
            return $updateStatus;
        } catch (\Throwable $ex) {
            Yii::error("ProjectService:updatingProjectDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
      /**
     * @author Padmaja
     * @param type $projectId
     * @param type $projectName
     * @param type $userId
     * @param type $page
     * @Description Gets Project details of given project name and Id 
     */
    public function getProjectDashboardDetails($projectName, $projectId, $userId,$page) {
        try {
            error_log("not existeddddddddd@@@@@@@@@@@@@--------".$page);
            return $projectDetails = CommonUtilityTwo::getProjectDetailsForProjectDashboard($projectId, $userId,$page);
        } catch (\Throwable $ex) {
            Yii::error("ProjectService:getProjectDashboardDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
     /**
     * @author Padmaja
     *  @param type $postData
      * @Description gets all the activities based on the posted criteria
     */
    public function getAllActivities($postData){
        try{
            return $activities=CommonUtilityTwo::getAllProjectActivities($postData);
        } catch (Exception $ex) {
            Yii::error("ProjectService:getAllActivities::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    /**
     * @author Padmaja
     *  @param type $postData
     * @Description Saves User persmissions on a repository
     */
    public function saveUserPermissions($projectId,$userData){
        try{
            error_log("33333333333333333");
            $RepositoryModel = new RepoPermissions();
           return  $status = $RepositoryModel->savingUserPermissions($projectId,$userData);
        } catch (Exception $ex) {
            Yii::error("ProjectService:saveUserPermissions::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
    /**
     * @author Padmaja
     *  @param type $postData
     * @Description gets User Permission on Repository
     */
    public function getUserPermissions($projectId,$userId){
        try{
            error_log("##################==>".$projectId."------>".$userId);
            $RepositoryModel = new RepoPermissions();
            $status = $RepositoryModel->getUserPermissions($projectId,$userId);
            error_log("+++++++getUserPermissions+++++++++>".print_r($status,1));
           return  $status;
        } catch (Exception $ex) {
            Yii::error("ProjectService:getUserPermissions::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
        /**
    * @author Padmaja
    * @Description This is used update the IsRepository column after repository created
    * @param $projectId 
    * @return array
    */
   public function updateRepositoryStatus($projectId){
        try{
            error_log("&&&&&&&&7-------".$projectId);
            $query="update Projects set IsRepository='1' where PId=$projectId";
            $data = Yii::$app->db->createCommand($query)->execute();
            error_log("$$$$$$444".$data);
            return $data;
        } catch (Exception $ex) {
             Yii::error("ProjectService:updateRepositoryStatus::" . $th->getMessage() . "--" . $th->getTraceAsString(), 'application');
        }
   }
   /**
    * 
    * @param type $projectId
    * @param type $userId
    * @return type
    * @Description gets User's Access and permission on a repository
    */
   public function getRepoPermissionsAndAccess($projectId,$userId){
       try{
            $RepositoryModel = new RepoPermissions();
            $data = $RepositoryModel->getRepoPermissionsAndAccess($projectId,$userId);
            return $data;
        } catch (Exception $ex) {
             Yii::error("ProjectService:getRepoPermissionsAndAccess::" . $th->getMessage() . "--" . $th->getTraceAsString(), 'application');
        }
   }
    
}