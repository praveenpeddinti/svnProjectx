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
use yii\base\ErrorException;

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
        } catch (\Throwable $ex) {
            Yii::error("Bucket:getBucketName::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
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
        } catch (\Throwable $ex) {
            Yii::error("Bucket:getBucketsList::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
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
        } catch (\Throwable $ex) {
            Yii::error("Bucket:getBackLogBucketId::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
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
        } catch (\Throwable $ex) {
            Yii::error("Bucket:getActiveBucketId::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
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

            if($bucketData->bucketStatus=='Current'){
//                $bucketsQuery = "SELECT * FROM Bucket WHERE BucketType=2 AND BucketStatus=0 AND Status=1 AND Projectid=$bucketData->projectId limit $offset,$limit"; 
                $bucketsQuery = "SELECT * FROM Bucket WHERE BucketStatus=2 AND Status=1 AND Projectid=$bucketData->projectId limit $offset,$limit"; 
            }
            if($bucketData->bucketStatus=='Closed'){
//                $bucketsQuery = "SELECT * FROM Bucket WHERE BucketType=2 AND BucketStatus=1 AND Status=1 AND Projectid=$bucketData->projectId limit $offset,$limit"; 
                $bucketsQuery = "SELECT * FROM Bucket WHERE BucketStatus=4 AND Status=1 AND Projectid=$bucketData->projectId limit $offset,$limit"; 
            }
            if($bucketData->bucketStatus=='Backlog'){
//                $bucketsQuery = "SELECT * FROM Bucket WHERE BucketType=1 AND Status=1 AND Projectid=$bucketData->projectId limit $offset,$limit"; 
                $bucketsQuery = "SELECT * FROM Bucket WHERE BucketStatus=1 AND Status=1 AND Projectid=$bucketData->projectId limit $offset,$limit"; 
            }
        $bucketDetails = Yii::$app->db->createCommand($bucketsQuery)->queryAll();
        return $bucketDetails;
        } catch (\Throwable $ex) {
            Yii::error("Bucket:getBucketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
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
        /*if($type="New"){
            $checkCurrentBucketQuery = "SELECT Name FROM Bucket WHERE BucketType=2 AND BucketStatus=0 AND Projectid=$projectId"; 
            $checkCurrentBucket = Yii::$app->db->createCommand($checkCurrentBucketQuery)->queryOne();
            if(empty($checkCurrentBucket)){
                $qry = "SELECT * FROM BucketType";
            }else{
                $qry = "SELECT * FROM BucketType WHERE Id NOT IN (2)";
                
            }
            $data = Yii::$app->db->createCommand($qry)->queryAll();
        }else{*/
          $qry = "SELECT * FROM BucketStatus";
         $data = Yii::$app->db->createCommand($qry)->queryAll();  
        //}
         return $data;
            
        } catch (\Throwable $ex) {
            Yii::error("Bucket:getBucketTypeFilter::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
       
    }
    
    /**
     * @author Praveen
     * @description This is to check the Bucket name 
     * @param type $bucketName, $projectId
     * @return type 
     * 
     */   
    public static function checkBucketName($bucketName,$projectId,$btype){
        try{
            $returnValue='failure';
//            if($btype==2){
//            $checkCurrentBucketQuery = "SELECT Name FROM Bucket WHERE BucketStatus=2 AND Projectid=$projectId"; 
//            $checkCurrentBucket = Yii::$app->db->createCommand($checkCurrentBucketQuery)->queryOne();
//                if(empty($checkCurrentBucket)){error_log("---1----");
//                $qry = "SELECT * FROM Bucket WHERE ProjectId=$projectId AND Name='".$bucketName."'";
//                $bucketData = Yii::$app->db->createCommand($qry)->queryAll();
//                    if(sizeof($bucketData)>0){
//                        $returnValue='Yes';
//                    }else{
//                       $returnValue='failure'; 
//                    }
//                }else{$returnValue='current'; }
//            }else{
                 $qry = "SELECT * FROM Bucket WHERE ProjectId=$projectId AND Name='".$bucketName."'";
                $bucketData = Yii::$app->db->createCommand($qry)->queryAll();
                if(sizeof($bucketData)>0){
                    $returnValue='No';
                }else{
                   $returnValue='Yes'; 
                }  
//            }
            return $returnValue;

        } catch (\Throwable $ex) {
            Yii::error("Bucket:checkBucketName::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
      
    }
    
    public function saveBucketDetails($bucketDetails) {
        try {
            $startDate = date("Y-m-d H:i:s", strtotime('+23 hours +59 minutes', strtotime($bucketDetails->data->startDateVal)));
//            $notifyEmail=0;
//            $sendReminder=0;
//            if(count($bucketDetails->data->notifyEmail)==1){$notifyEmail=1;}
//            if(count($bucketDetails->data->sendReminder)==1){$sendReminder=1;}
            $returnValue = 'failure';
            $bucket = new Bucket();
            $bucket->ProjectId = (int)$bucketDetails->projectId;
            $bucket->Name = $bucketDetails->data->title;
            $bucket->Description = $bucketDetails->data->description;
            $bucket->StartDate = $startDate;
            $bucket->DueDate = $bucketDetails->data->dueDateVal;
            $bucket->Responsible = (int)$bucketDetails->data->selectedUserFilter;
//            $bucket->BucketType = (int)$bucketDetails->data->selectedBucketTypeFilter;
//            $bucket->BucketStatus = (int)$bucketDetails->data->selectedBucketTypeFilter;
            $bucket->BucketStatus = ($bucketDetails->data->setCurrent)?(int)2:(int)1;
//            $bucket->BucketStatus = (int)0;
//            $bucket->EmailNotify = (int)$notifyEmail;
//            $bucket->EmailReminder = (int)$sendReminder;
            $bucket->Status = (int)1;
            $bucket->save();
            $returnValue = $bucket->Id;
            return $returnValue;
        } catch (\Throwable $ex) {
            Yii::error("Bucket:saveBucketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
    
    
    /**
     * @author Praveen
     * @description This is to check the Bucket name for Edit Bucket 
     * @param type $bucketName, $projectId, $bucketId
     * @return type 
     * 
     */   
    public static function checkUpdateBucketName($bucketName,$bucketId,$projectId,$btype,$bucketRole){
        try{
            $returnValue='failure';
            
            if($bucketRole=='Current'){
                $qry = "SELECT * FROM Bucket WHERE ProjectId=$projectId AND Id !=$bucketId AND Name='".$bucketName."'";
            $bucketData = Yii::$app->db->createCommand($qry)->queryAll();
             if(sizeof($bucketData)>0){
                $returnValue='Yes';
            }else{
                   $returnValue='failure'; 
                }  
            }else{
                if($btype==2 ){
            $checkCurrentBucketQuery = "SELECT Name FROM Bucket WHERE BucketStatus=2 AND Projectid=$projectId"; 
            $checkCurrentBucket = Yii::$app->db->createCommand($checkCurrentBucketQuery)->queryOne();
                if(empty($checkCurrentBucket)){error_log("---1----");
                $qry = "SELECT * FROM Bucket WHERE ProjectId=$projectId AND Id !=$bucketId AND Name='".$bucketName."'";
            $bucketData = Yii::$app->db->createCommand($qry)->queryAll();
             if(sizeof($bucketData)>0){
                $returnValue='Yes';
            }else{
                       $returnValue='failure'; 
                    }
                }else{$returnValue='current'; }
            }else{
            $qry = "SELECT * FROM Bucket WHERE ProjectId=$projectId AND Id !=$bucketId AND Name='".$bucketName."'";
            $bucketData = Yii::$app->db->createCommand($qry)->queryAll();
             if(sizeof($bucketData)>0){
                $returnValue='Yes';
            }else{
                   $returnValue='failure'; 
                }  
            }
            }
            
            
            return $returnValue;

        } catch (\Throwable $ex) {
            Yii::error("Bucket:checkUpdateBucketName::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
      
    }
    
    public function updateBucketDetails($bucketDetails) {
        try {
            $startDate = date("Y-m-d H:i:s", strtotime('+23 hours +59 minutes', strtotime($bucketDetails->data->startDateVal)));
            $dueDate = date("Y-m-d H:i:s", strtotime('+23 hours +59 minutes', strtotime($bucketDetails->data->dueDateVal)));
            
            $notifyEmail=0;
            $sendReminder=0;
            if($bucketDetails->data->notifyEmail==1){$notifyEmail=1;}
            if($bucketDetails->data->sendReminder==1){$sendReminder=1;}
            $bucket=Bucket::findOne($bucketDetails->data->Id);
            $bucket->ProjectId = (int)$bucketDetails->projectId;
            $bucket->Name = $bucketDetails->data->title;
            $bucket->Description = $bucketDetails->data->description;
            $bucket->StartDate = $startDate;
            $bucket->DueDate = $dueDate;
            $bucket->Responsible = (int)$bucketDetails->data->selectedUserFilter;
//            $bucket->BucketType = (int)$bucketDetails->data->selectedBucketTypeFilter;
            $bucket->BucketStatus = (int)$bucketDetails->data->selectedBucketTypeFilter;
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
        } catch (\Throwable $ex) {
            Yii::error("Bucket:updateBucketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
    public function getBucketChangeStatus($projectId,$bucketId,$StatusType) {
        try {
            $bucket=Bucket::findOne($bucketId);
            if($StatusType=='Set as Completed')
            {
                $bucket->BucketStatus = (int)3;
                $bucket->CloseDate = date("Y-m-d H:i:s");
            }
            if($StatusType=='Set as Backlog') {
                $bucket->BucketStatus = (int)1;
            }
            if($StatusType=='Delete') {
                $bucket->Status = (int)0;
            }
            if($StatusType=='Re-opened milestone'){
                
            }
            if($StatusType=='Set as Current'){
                $checkCurrentBucketQuery = "SELECT Name FROM Bucket WHERE BucketStatus=2 AND Projectid=$projectId"; 
            $checkCurrentBucket = Yii::$app->db->createCommand($checkCurrentBucketQuery)->queryOne();
                if(empty($checkCurrentBucket)){
                    $bucket->BucketStatus = (int)2;
                }else{$bucket->BucketStatus = (int)1;}
                
            }
            $bucket->update();
            if ($bucket->update() !== false) {
                $result = "success";
            } else {
                $result = "Current Bucket is Exist";
            }
            return $result;
        } catch (\Throwable $ex) {
            Yii::error("Bucket:getBucketChangeStatus::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

    /**
     * @author Anand Singh
     * @param type $projectId
     * @param type $status
     * @param type $type
     * @return string
     * @throws ErrorException
     */
    
     public static function getProjectBucketByAttributes($projectId,$status=2)
    {
        try{
        $query = "select Id,Name,BucketStatus from Bucket where BucketStatus= $status and ProjectId=".$projectId;
        $data = Yii::$app->db->createCommand($query)->queryAll();
        if(is_array($data)){
            return $data;
        }else{
            return "failure";
        }
        } catch (\Throwable $ex) {
            Yii::error("Bucket:getProjectBucketByAttributes::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
       
    }
    
    public function getBucketDetailsById($projectId,$bucketId){
      $bucketsQuery = "SELECT * FROM Bucket WHERE Id=$bucketId AND Status=1 AND Projectid=$projectId";   
      $bucketDetails = Yii::$app->db->createCommand($bucketsQuery)->queryAll();
      return $bucketDetails;
    }
 
}


            
            

?>