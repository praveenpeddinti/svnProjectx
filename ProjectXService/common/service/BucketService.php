<?php
namespace common\service;
use common\models\mongo\TicketCollection;
use common\components\CommonUtility;
use common\models\mysql\WorkFlowFields;
use common\models\mysql\Collaborators;
use common\models\mysql\Bucket;
use common\models\mysql\BucketType;
use common\models\mysql\BucketStatus;
use common\models\mongo\AccessTokenCollection;
use Yii;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class BucketService {

    /**
    * @author Praveen
    * @description This is to get filtered bucket list in creating buckets 
    * @return type 
    * @param type $projectId
     */      
    public function getBucketTypeFilter($projectId,$type){
        try{error_log("---bservice");
         $bucketModel = new Bucket();
         return $bucketModel->getBucketTypeFilter($projectId,$type);
        } catch (Exception $ex) {
            Yii::log("BucketService:getBucketTypeFilter::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    /**
    * @author Praveen
    * @description This is to saving the bucket details (creating bucket) 
    * @return type 
    * @param type $bucketData
     */      
    public function getSaveBucketDetails($bucketData){
        try{
         $bucketModel = new Bucket();
         return $bucketModel->saveBucketDetails($bucketData);
        } catch (Exception $ex) {
            Yii::log("BucketService:getSaveBucketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

}

  