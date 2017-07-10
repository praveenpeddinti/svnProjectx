<?php
namespace common\service;
use common\models\mongo\TicketCollection;
use common\components\CommonUtilityTwo;
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
    * @description This is to get bucket details in bucket table(mysql
    * @return type 
    * @param type $projectId, $bucketStatus
     */      
    public function getBucketDetails($bucketData){
        try{
            $timezone = $bucketData->timeZone;
            $finalData = array();
            $bucketModel = new Bucket();
            $bucketDetails = $bucketModel->getBucketDetails($bucketData);
            //error_log(print_r($bucketDetails,1)."----bucquery--serv--".sizeof($bucketDetails));
            foreach ($bucketDetails as $bucket) {error_log("----1---s---".$bucket['Id']);
                $details = CommonUtilityTwo::prepareBucketDashboardDetails($bucket,$bucketData->projectId,$timezone);
                array_push($finalData, $details);
                //break;
            } 
            //error_log("finalBucketData---".print_r($finalData,1));
            //return $bucketDetails;
            return $finalData;
        } catch (Exception $ex) {
            Yii::log("BucketService:getBucketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
    
    
    /**
    * @author Praveen
    * @description This is to get filtered bucket list in creating buckets 
    * @return type 
    * @param type $projectId
     */      
    public function getBucketTypeFilter($projectId,$type){
        try{
         $bucketModel = new Bucket();
         return $bucketModel->getBucketTypeFilter($projectId,$type);
        } catch (Exception $ex) {
            Yii::log("BucketService:getBucketTypeFilter::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
    /**
    * @author Praveen
    * @description This method is used to check the bucket is existing in the project before saving the bucket details  
    * @return type 
    * @param type $bucketData
     */      
    public function checkBucketName($bucketName,$projectId){
        try{
         $bucketModel = new Bucket();
         return $bucketModel->checkBucketName($bucketName,$projectId);
        } catch (Exception $ex) {
            Yii::log("BucketService:checkBucketName::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    /**
    * @author Praveen
    * @description This is to saving the bucket details 
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
    
    
    /**
    * @author Praveen
    * @description This method is used to check the bucket is existing in the project before updating the bucket details  
    * @return type 
    * @param type $bucketData
     */      
    public function checkUpdateBucketName($bucketName,$bucketId,$projectId){
        try{
         $bucketModel = new Bucket();
         return $bucketModel->checkUpdateBucketName($bucketName,$bucketId,$projectId);
        } catch (Exception $ex) {
            Yii::log("BucketService:checkUpdateBucketName::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    /**
    * @author Praveen
    * @description This is to updating the bucket details 
    * @return type 
    * @param type $bucketData
     */      
    public function updateBucketDetails($bucketData){
        try{
         $bucketModel = new Bucket();
         return $bucketModel->updateBucketDetails($bucketData);
        } catch (Exception $ex) {
            Yii::log("BucketService:updateBucketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    

}

  