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
            //error_log("asssssssssssssssss".print_r($remembermeStatus,1));
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
            //error_log("bbbbbbbbb###########".$accesstoken.$collabaratorId);
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

      
}

  