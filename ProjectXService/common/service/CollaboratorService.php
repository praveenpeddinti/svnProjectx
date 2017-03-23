<?php
namespace common\service;
use common\models\mongo\TicketCollection;
use common\components\CommonUtility;
use common\models\mysql\WorkFlowFields;
use common\models\mysql\Collaborators;
use common\models\mongo\AccessTokenCollection;
use Yii;

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
        } catch (Exception $ex) {
Yii::log("CollaboratorService:getWorkFlowDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
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
        } catch (Exception $ex) {
            Yii::log("AccesstokenService:getCollabaratorAccesstoken::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
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
        } catch (Exception $ex) {
            Yii::log("AccesstokenService:saveCollabaratortokenData::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
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
        } catch (Exception $ex) {
                  Yii::log("AccesstokenService:updateStatusCollabarator::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
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
        } catch (Exception $ex) {
            Yii::log("CollaboratorService:getFilteredProjectTeam::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
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
            
        } catch (Exception $ex) {
            Yii::log("CollaboratorService:getMatchedCollaborator::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
     /**
    * @author Praveen P
    * @description This is to get filtered team list in Followerlist 
    * @return type 
    * @param type $projectId, $search_query,$defaultusers
     */      
    public function getFilteredFollowersDetailsProjectTeam($StoryData, $projectId) {
        try {
            $collaboratorModel = new Collaborators();
            return $collaboratorModel->getFilteredFollowersDetailsProjectTeam($StoryData, $projectId);
        } catch (Exception $ex) {
            Yii::log("CollaboratorService:getFilteredFollowersDetailsProjectTeam::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

}

  