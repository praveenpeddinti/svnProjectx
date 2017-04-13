<?php

namespace common\models\mongo;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
//use yii\db\ActiveRecord;
use yii\mongodb\ActiveRecord;
use yii\mongodb\Query;
use yii\data\ActiveDataProvider;
use yii\web\IdentityInterface;
use common\models\mongo\TicketCollection;

class TicketArtifacts extends ActiveRecord {

    public static function collectionName() {
        return 'TicketArtifacts';
    }

    public function attributes() {
//        parent::attributes();
        return [
            "_id",
            "TicketId",
            "ProjectId",
            "Artifacts",
        ];
    }

    public function behaviors() {
        return [
//            TimestampBehavior::className(),
        ];
    }
/**
 * @author Moin Hussain
 * @param type $ticketNumber
 * @param type $projectId
 * @param type $artifactsList
 */
    public static function createArtifactsRecord($ticketNumber, $projectId, $artifactsList = array()) {
        try {
          $collection = Yii::$app->mongodb->getCollection('TicketArtifacts');
          if(count($artifactsList)>0){
           foreach ($artifactsList as $artifactData){
                 $newdata = array('$push' => array("Artifacts" => $artifactData ));
                  $collection->findAndModify(array("TicketId" => (int)$ticketNumber,"ProjectId"=>(int)$projectId), $newdata,array('new' => 1,"upsert"=>1));              
            }   
          }else{
               $newdata = array('$set' => array("Artifacts" => array() ));
              $collection->findAndModify(array("TicketId" => (int)$ticketNumber,"ProjectId"=>(int)$projectId), $newdata,array('new' => 1,"upsert"=>1));              
          }
            
        } catch (Exception $ex) {
        Yii::log("TicketArtifacts:createArtifactsRecord::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');    
        }
    }
/**
 * @author  Jagadish
 * @param type $ticketId
 * @param type $projectId
 * @return type
 */
    public static function getTicketArtifacts($ticketId, $projectId) {
        try{
         $query = new Query();
        $query->from('TicketArtifacts')
                ->select(array("Artifacts"))
                ->where(['TicketId' => (int) $ticketId, "ProjectId" => (int) $projectId]);
        $ticketArtifactsDetails = $query->one();

        return $ticketArtifactsDetails;   
        } catch (Exception $ex) {
Yii::log("TicketArtifacts:getTicketArtifacts::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');    
        }
        
    }
/**
 * @author Jagadish
 * @param type $ticketNumber
 * @param type $projectId
 * @param type $newArtifactArray
 * @param type $userId
 */
    public static function saveArtifacts($ticketNumber, $projectId, $newArtifactArray = array(), $userId) {
        try {
            if (!empty($newArtifactArray)) {
                foreach ($newArtifactArray as $artifact) {
                    $artifact["UploadedBy"] = (int) $userId;
                    $collection = Yii::$app->mongodb->getCollection('TicketArtifacts');
                    $newdata = array('$addToSet' => array('Artifacts' => $artifact));
                    $res = $collection->update(array("TicketId" => (int) $ticketNumber, "ProjectId" => (int) $projectId), $newdata, array('new' => 1, "upsert" => 1));
                }
            }
        } catch (Exception $ex) {
        Yii::log("TicketArtifacts:saveArtifacts::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');    
    
        }
    }

}

?>