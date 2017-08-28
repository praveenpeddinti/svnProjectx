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
use common\models\mongo\EventCollection;
use Yii;
use yii\base\ErrorException;

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
            foreach ($bucketDetails as $bucket) {
                $details = CommonUtilityTwo::prepareBucketDashboardDetails($bucket,$bucketData->projectId,$timezone,$bucketData->bucketStatus);
                array_push($finalData, $details);
                //break;
            } 
            return $finalData;
        } catch (\Throwable $ex) {
            Yii::error("BucketService:getBucketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
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
        } catch (\Throwable $ex) {
            Yii::error("BucketService:getBucketTypeFilter::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
    /**
    * @author Praveen
    * @description This method is used to check the bucket is existing in the project before saving the bucket details  
    * @return type 
    * @param type $bucketData
     */      
    public function checkBucketName($bucketName,$projectId,$btype=""){
        try{
         $bucketModel = new Bucket();
         $availability = array("available"=>"");
         $availability["available"] = $bucketModel->checkBucketName($bucketName,$projectId,$btype);
         return $availability;
        } catch (\Throwable $ex) {
            Yii::error("BucketService:checkBucketName::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
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
        } catch (\Throwable $ex) {
            Yii::error("BucketService:getSaveBucketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
    
    /**
    * @author Praveen
    * @description This method is used to check the bucket is existing in the project before updating the bucket details  
    * @return type 
    * @param type $bucketData
     */      
    public function checkUpdateBucketName($bucketName,$bucketId,$projectId,$bType,$bucketRole){
        try{
         $bucketModel = new Bucket();
         return $bucketModel->checkUpdateBucketName($bucketName,$bucketId,$projectId,$bType,$bucketRole);
        } catch (\Throwable $ex) {
            Yii::error("BucketService:checkUpdateBucketName::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
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
         $summary=array();
         $response = $bucketModel->updateBucketDetails($bucketData);
          $Oldbucket=Bucket::findOne($bucketData->data->Id);
          error_log("OLd_bucket_details___".print_r($Oldbucket,1));
          return $response;
          
        } catch (\Throwable $ex) {
            Yii::error("BucketService:updateBucketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    /**
    * @author Praveen
    * @description This is to change the status for bucket 
    * @return type 
    * @param type $projectId,$bucketId,statusType
     */      
    public function getBucketChangeStatus($projectId,$bucketId,$StatusType){
        try{
         $bucketModel = new Bucket();
         return $bucketModel->getBucketChangeStatus($projectId,$bucketId,$StatusType);
        } catch (\Throwable $ex) {
            Yii::error("BucketService:getBucketChangeStatus::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
    /**
    * @author Ryan
    * @description Used for getting Count of Buckets with Types
    * @return type 
     */  
    public function getBucketsCount($projectId){
        try{
                $bucket_types_count=Bucket::getBucketsCountByType($projectId);
                return array('BucketTypesCount'=>$bucket_types_count);
            
        } catch (\Throwable $ex) {
            Yii::error("BucketService:getBucketsCount::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
    /**
    * @author Ryan
    * @description Used for getting Count of Buckets with Types
    * @return type 
     */  
    public function getBucketsForProject($projectId,$type){
        try{
                $bucketsInfo=Bucket::getBucketsInfo($projectId,$type);
                return array('BucketInfo'=>$bucketsInfo);
            
        } catch (\Throwable $ex) {
            Yii::error("BucketService:getBucketsForProject::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
     /**
    * @author Ryan
    * @description Used for getting Current Week Buckets with more count
    * @return type array
     */  
    public function getCurrentWeekBuckets($projectId){
        try{
            $currentBucketsItems=array();
            $currentWeekBucketsId=  EventCollection::getCurrentWeekActiveBuckets($projectId);
            $currentWeekBuckets=Bucket::getCurrentWeekBucketsInfo($currentWeekBucketsId);
            foreach($currentWeekBuckets as $currentBucketInfo){
                    $currentBucketDetails=CommonUtilityTwo::getTopTicketsStats($projectId, '', $currentBucketInfo['Id']);
                    $merged_other_bucket=array_merge($currentBucketDetails,$currentBucketInfo);
                    array_push($currentBucketsItems,$merged_other_bucket);
                }
            return $currentBucketsItems;
        } catch (\Throwable $ex) {
            Yii::error("BucketService:getCurrentWeekBuckets::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
     /**
    * @author Ryan
    * @description Used for getting Count of Buckets with Types
    * @return type 
     */  
    public function getMoreCountBuckets($projectId,$isData=0){
        try{
            $otherBucketsItems=array();
            $otherBuckets= EventCollection::getOtherBucketsCount($projectId,$isData);
            $otherBucketsData=Bucket::getCurrentWeekBucketsInfo($otherBuckets);
            if($isData==0){
                 error_log("==count otherrrr==".count($otherBucketsData));
                 return count($otherBucketsData);
            }else{
                foreach($otherBucketsData as $otherBucketInfo){
                    $otherBucketDetails=CommonUtilityTwo::getTopTicketsStats($projectId, '', $otherBucketInfo['Id']);
                    $merged_other_bucket=array_merge($otherBucketDetails,$otherBucketInfo);
                    array_push($otherBucketsItems,$merged_other_bucket);
                }
                return $otherBucketsItems;
            }
           
        } catch (\Throwable $ex) {
            Yii::error("BucketService:getMoreCountBuckets::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
}

