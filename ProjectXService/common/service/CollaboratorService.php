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
        } catch (Exception $ex) {
            Yii::log("CollaboratorService:getCollaboratorsForFollow::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
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
        } catch (Exception $ex) {
            Yii::log("CollaboratorService:getTicketFollowersList::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
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
        } catch (Exception $ex) {
            Yii::log("CollaboratorService:getResponsibleProjectTeam::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

}

  