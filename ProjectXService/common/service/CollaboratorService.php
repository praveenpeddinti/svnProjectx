<?php
namespace common\service;
use common\models\mongo\TicketCollection;
use common\components\CommonUtility;
use common\models\mysql\WorkFlowFields;
use common\models\mysql\Collaborators;
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

      
}

  