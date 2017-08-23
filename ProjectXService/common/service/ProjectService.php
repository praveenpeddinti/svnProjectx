<?php
namespace common\service;
use common\models\mongo\TicketCollection;
use common\components\{CommonUtilityTwo, SVNUtility,EventTrait};
use common\models\mysql\WorkFlowFields;
use common\models\mysql\Collaborators;
use common\models\mysql\Bucket;
use common\models\mysql\BucketType;
use common\models\mysql\BucketStatus;
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
class ProjectService {
   /**
     * @authorPadmaja
     * @description This method to verify  the  project.
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
     * @authorPadmaja
     * @description This method to saving  the  project.
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
           // SVNUtility::createRepository($projectName);
            
            $bucket = new Bucket();
            $bucket->ProjectId = (int)$projectId;
            $bucket->Name = "Backlog";
            $bucket->Description = "";
            $bucket->StartDate = "";
            $bucket->DueDate = "";
            $bucket->Responsible = (int)$userId;
//            $bucket->BucketType = (int)$bucketDetails->data->selectedBucketTypeFilter;
//            $bucket->BucketStatus = (int)$bucketDetails->data->selectedBucketTypeFilter;
            $bucket->BucketStatus = 1;
//            $bucket->BucketStatus = (int)0;
           // $bucket->EmailNotify = (int)1;
           // $bucket->EmailReminder = (int)1;
            $bucket->Status = (int)1;
            $bucket->save();
           // $returnValue = $bucket->Id;
            
            
           EventTrait::saveEvent($projectId,"Project",$projectId,"created","create",$userId,[array("ActionOn"=>"projectcreation","OldValue"=>0,"NewValue"=>(int)$projectId)]); 
   
            
            return $projectId;
        } catch (\Throwable $ex) {
            Yii::error("ProjectService:savingProjectDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
       /**
     * @authorPadmaja
     * @description This method to savingProjectTeamDetails.
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
     public function updatingProjectDetails($projectName, $description, $fileExt, $projectLogo, $projectId) {
        try {
            if (strpos($projectLogo, 'assets') !== false) {
                $logo = $projectLogo;
            } else {
                $extractUrl = explode('projectlogo/', $projectLogo);
                $projectLogoPath = Yii::$app->params['ProjectRoot'] . Yii::$app->params['projectLogo'];
                error_log("eee-------" . $extractUrl[1]);
                if (file_exists($projectLogoPath . "/" . $extractUrl[1])) {
                    error_log("eee-----aaaaaa--" . $fileExt);
                    if (empty($fileExt) || $fileExt == '') {
                        error_log("aaaaaa-------------" . $extractUrl[1]);
                        rename($projectLogoPath . "/" . $extractUrl[1], $projectLogoPath . "/" . $extractUrl[1]);
                        $logo = Yii::$app->params['projectLogo'] . '/' . $extractUrl[1];
                    } else {
                        error_log("aaaa6666666666aa-----------" . $extractUrl[1]);
                        rename($projectLogoPath . "/" . $extractUrl[1], $projectLogoPath . "/" . $projectName . "_" . $projectId . ".$fileExt");
                        $logo = Yii::$app->params['projectLogo'] . '/' . $projectName . "_" . $projectId . ".$fileExt";
                    }
                } else {
                     $logo = Yii::$app->params['projectLogo'] . '/' . $projectName . "_" . $projectId . ".$fileExt";
                    error_log("not existeddddddddd----");
                }
            }
            error_log("not existeddddddddd@@@@@@@@@@@@@--------" . $projectLogo);
            $ProjectModel = new Projects();
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
         /*
     * @autor Padmaja
     *  @param type $postData
     */
    public function getAllActivities($postData){
        try{
            return $activities=CommonUtilityTwo::getAllProjectActivities($postData);
        } catch (Exception $ex) {
            Yii::error("ProjectService:getAllActivities::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
}