<?php 
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace common\models\mysql;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class Bucket extends ActiveRecord 
{
    
    public static function tableName()
    {
        return '{{%Bucket}}';
    }
    
    public function behaviors()
    {
        return [
            //TimestampBehavior::className(),
        ];
    }  
    /**
     * @author Moin Hussain
     * @param type $bucketId
     * @param type $projectId
     * @return type
     */
    public static function getBucketName($bucketId,$projectId)
    {
        try{
        $query = "select Id,Name from Bucket where Id=".$bucketId." and ProjectId=".$projectId;
        $data = Yii::$app->db->createCommand($query)->queryOne();
        return $data;  
        } catch (Exception $ex) {
     Yii::log("Bucket:getBucketName::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
       
    }
    
    /**
     * @author Moin Hussain
     * @param type $projectId
     * @return type
     */
    public static function getBucketsList($projectId)
    {
        try{
        $query = "select Id,Name from Bucket where ProjectId=".$projectId;
        $data = Yii::$app->db->createCommand($query)->queryAll();
        return $data;  
        } catch (Exception $ex) {
     Yii::log("Bucket:getBucketName::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
       
    }
    
     /**
     * @author Moin Hussain
     * @param type $projectId
     * @return type
     */
    public static function getBackLogBucketId($projectId)
    {
        try{
        $query = "select Id,Name from Bucket where BucketStatus=0 and ProjectId=".$projectId;
        $data = Yii::$app->db->createCommand($query)->queryOne();
        if(is_array($data)){
            return $data;
        }else{
            return "failure";
        }
        } catch (Exception $ex) {
     Yii::log("Bucket:getBucketName::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
       
    }
    
    /**
     * @author Anand Singh
     * @param type $projectId
     * @return string
     */
     public static function getActiveBucketId($projectId)
    {
        try{
        $query = "select Id,Name from Bucket where BucketStatus=1 and ProjectId=".$projectId;
        $data = Yii::$app->db->createCommand($query)->queryOne();
        if(is_array($data)){
            return $data;
        }else{
            return "failure";
        }
        } catch (Exception $ex) {
     Yii::log("Bucket:getActiveBucketId::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
       
    }
    
    /**
     * @author Praveen
     * @param type $projectId,$status
     * @return type
     */
    public function getBucketDetails($bucketData)
    {
        try{
             //  $pageLength = 10;
                   $pageLength=Yii::$app->params['pageLength'];
                    if ($bucketData->page == 1) {
                        $offset = $bucketData->page - 1;
                        $limit = $pageLength;   
                    } else {
                        $offset = ($bucketData->page - 1) * $pageLength;
                        $limit = $pageLength;
                    }
                    error_log($offset."--page---".$limit);
                //error_log("----bucquery----".print_r($bucketData,1));
            if($bucketData->bucketStatus==1){
              $bucketsQuery = "SELECT * FROM Bucket WHERE BucketType=2 AND BucketStatus=0 AND Projectid=$bucketData->projectId limit $offset,$limit"; 
              
            }
            if($bucketData->bucketStatus==2){
              $bucketsQuery = "SELECT * FROM Bucket WHERE BucketType=2 AND BucketStatus=1 AND Projectid=$bucketData->projectId limit $offset,$limit"; 
              
            }
            if($bucketData->bucketStatus==0){
              $bucketsQuery = "SELECT * FROM Bucket WHERE BucketType=1 AND Projectid=$bucketData->projectId limit $offset,$limit"; 
              
            }
            $bucketDetails = Yii::$app->db->createCommand($bucketsQuery)->queryAll();
            return $bucketDetails;
        } catch (Exception $ex) {
            Yii::log("Bucket:getBucketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
       
    }
    
    
    
    
    /**
     * @author Praveen
     * @param type $projectId,$type
     * @return type
     */
    public function getBucketTypeFilter($projectId,$type)
    {
        try{
        if($type="New"){
            $checkCurrentBucketQuery = "SELECT Name FROM Bucket WHERE BucketType=2 AND BucketStatus=0 AND Projectid=$projectId"; 
            $checkCurrentBucket = Yii::$app->db->createCommand($checkCurrentBucketQuery)->queryOne();
            if(empty($checkCurrentBucket)){
                $qry = "SELECT * FROM BucketType";
            }else{
                $qry = "SELECT * FROM BucketType WHERE Id NOT IN (2)";
                
            }
            $data = Yii::$app->db->createCommand($qry)->queryAll();
        }else{
          $qry = "SELECT * FROM BucketType";
         $data = Yii::$app->db->createCommand($qry)->queryAll();  
        }
         return $data;
            
        } catch (Exception $ex) {
            Yii::log("Bucket:getBucketTypeFilter::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
       
    }
    
    /**
     * @author Praveen
     * @description This is to check the Bucket name 
     * @param type $bucketName, $projectId
     * @return type 
     * 
     */   
    public static function checkBucketName($bucketName,$projectId){
        try{
            $returnValue='failure';
            $qry = "SELECT * FROM Bucket WHERE ProjectId=$projectId AND Name='".$bucketName."'";
            $bucketData = Yii::$app->db->createCommand($qry)->queryAll();
             if(sizeof($bucketData)>0){
                $returnValue='Yes';
            }
            return $returnValue;

        } catch (Exception $ex) {
             Yii::log("Bucket:checkBucketName::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
      
    }
    
    public function saveBucketDetails($bucketDetails) {
        try {
            $startDate = date("Y-m-d H:i:s", strtotime('+23 hours +59 minutes', strtotime($bucketDetails->data->startDateVal)));
            $notifyEmail=0;
            $sendReminder=0;
            if(count($bucketDetails->data->notifyEmail)==1){$notifyEmail=1;}
            if(count($bucketDetails->data->sendReminder)==1){$sendReminder=1;}
            $returnValue = 'failure';
            $bucket = new Bucket();
            $bucket->ProjectId = (int)$bucketDetails->projectId;
            $bucket->Name = $bucketDetails->data->title;
            $bucket->Description = $bucketDetails->data->description;
            $bucket->StartDate = $startDate;
            $bucket->DueDate = $bucketDetails->data->dueDateVal;
            $bucket->Responsible = (int)$bucketDetails->data->selectedUserFilter;
            $bucket->BucketType = (int)$bucketDetails->data->selectedBucketTypeFilter;
            $bucket->BucketStatus = (int)0;
            $bucket->EmailNotify = (int)$notifyEmail;
            $bucket->EmailReminder = (int)$sendReminder;
            $bucket->save();
            //if ($bucket->save()) {
            //    $returnValue = $bucket->Id;
            //}
            return $returnValue;
        } catch (Exception $ex) {
            Yii::log("Bucket:saveBucketDetails::".$ex->getMessage()."--".$ex->getTraceAsString(), 'error', 'application');
            $returnValue = 'failure';
        }
    }
    
    
    
    /**
     * @author Praveen
     * @description This is to check the Bucket name for Edit Bucket 
     * @param type $bucketName, $projectId, $bucketId
     * @return type 
     * 
     */   
    public static function checkUpdateBucketName($bucketName,$bucketId,$projectId){
        try{
            $returnValue='failure';
            $qry = "SELECT * FROM Bucket WHERE ProjectId=$projectId AND Id !=$bucketId AND Name='".$bucketName."'";
            $bucketData = Yii::$app->db->createCommand($qry)->queryAll();
             if(sizeof($bucketData)>0){
                $returnValue='Yes';
            }
            return $returnValue;

        } catch (Exception $ex) {
             Yii::log("Bucket:checkBucketName::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
      
    }
    
    public function updateBucketDetails($bucketDetails) {
        try {
            $startDate = date("Y-m-d H:i:s", strtotime('+23 hours +59 minutes', strtotime($bucketDetails->data->startDateVal)));
            $notifyEmail=0;
            $sendReminder=0;
            if(count($bucketDetails->data->notifyEmail)==1){$notifyEmail=1;}
            if(count($bucketDetails->data->sendReminder)==1){$sendReminder=1;}
            $bucket=Bucket::findOne($bucketDetails->data->Id);
            $bucket->ProjectId = (int)$bucketDetails->projectId;
            $bucket->Name = $bucketDetails->data->title;
            $bucket->Description = $bucketDetails->data->description;
            $bucket->StartDate = $startDate;
            $bucket->DueDate = $bucketDetails->data->dueDateVal;
            $bucket->Responsible = (int)$bucketDetails->data->selectedUserFilter;
            $bucket->BucketType = (int)$bucketDetails->data->selectedBucketTypeFilter;
            //$bucket->BucketStatus = (int)0;
            $bucket->EmailNotify = (int)$notifyEmail;
            $bucket->EmailReminder = (int)$sendReminder;
            $bucket->update();
            if ($bucket->update() !== false) {
                $result = "success";
            } else {
                $result = "failed";
            }
            return $result;
        } catch (Exception $ex) {
            Yii::log("Bucket:updateBucketDetails::".$ex->getMessage()."--".$ex->getTraceAsString(), 'error', 'application');
            $returnValue = 'failure';
        }
    }
 
}



?>