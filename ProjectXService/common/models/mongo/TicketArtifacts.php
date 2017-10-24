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
use yii\mongodb\ActiveRecord;
use yii\mongodb\Query;
use yii\data\ActiveDataProvider;
use yii\web\IdentityInterface;
use common\models\mongo\TicketCollection;
use yii\base\ErrorException;

class TicketArtifacts extends ActiveRecord {

    public static function collectionName() {
        return 'TicketArtifacts';
    }

    public function attributes() {
        return [
            "_id",
            "TicketId",
            "ProjectId",
            "Artifacts",
        ];
    }

    public function behaviors() {
        return [
        ];
    }
/**
 * @author Moin Hussain
 * @param type $ticketNumber
 * @param type $projectId
 * @param type $artifactsList
 * @Description Creates an empty Ticket Artifacts Collection Record on creating a new ticket.
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
            
        } catch (\Throwable $ex) {
            Yii::error("TicketArtifactsCollection:createArtifactsRecord::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
/**
 * @author  Jagadish
 * @param type $ticketId
 * @param type $projectId
 * @return type
 * @Description Gets list of Artifacts uploaded under a ticket.
 */
    public static function getTicketArtifacts($ticketId, $projectId) {
        try{
         $query = new Query();
        $query->from('TicketArtifacts')
                ->select(array("Artifacts"))
                ->where(['TicketId' => (int) $ticketId, "ProjectId" => (int) $projectId]);
        $ticketArtifactsDetails = $query->one();

        return $ticketArtifactsDetails;   
        } catch (\Throwable $ex) {
            Yii::error("TicketArtifactsCollection:getTicketArtifacts::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
        
    }
/**
 * @author Jagadish
 * @param type $ticketNumber
 * @param type $projectId
 * @param type $newArtifactArray
 * @param type $userId
 * @Description Saves Uploaded Artifacts data to Ticket Artifacts Collection.
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
        } catch (\Throwable $ex) {
            Yii::error("TicketArtifactsCollection:saveArtifacts::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

}

?>