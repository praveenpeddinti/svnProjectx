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
use common\components\{CommonUtilityTwo,EventTrait};
use common\models\mongo\TinyUserCollection;

class Bucket extends ActiveRecord 
{
    
    public static function tableName()
    {
        return '{{%Bucket}}';
    }
    
    public function behaviors()
    {
        return [
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
        $query = "select Id,Name from Bucket where BucketStatus=1 and ProjectId=".$projectId." order by Id asc limit 1";
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
           
            $bucketsQuery = "SELECT b.*,bs.Name as BucketStatusName FROM Bucket b,BucketStatus bs WHERE b.Id=$bucketData->bucketId AND b.Status=1 AND b.Projectid=$bucketData->projectId and b.BucketStatus = bs.Id"; 
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

          $qry = "SELECT * FROM BucketStatus where Id NOT IN ($type) and Id>1"; //this query was modified by Ryan for fixing the Backlog Bucket Issue,added condition as Id>1
         $data = Yii::$app->db->createCommand($qry)->queryAll();  
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
    public static function checkBucketName($bucketName,$projectId,$bucketId=""){
        try{
            $returnValue='failure';
            $appendQry = '';
            if($bucketId != ""){
                $appendQry = " AND Id != $bucketId";
            }
                 $qry = "SELECT * FROM Bucket WHERE ProjectId=$projectId AND Name='".$bucketName."'".$appendQry;
                 error_log("====================".$qry);
                $bucketData = Yii::$app->db->createCommand($qry)->queryAll();
                if(sizeof($bucketData)>0){
                    $returnValue='No';
                }else{
                   $returnValue='Yes'; 
                }  
            return $returnValue;

        } catch (\Throwable $ex) {
            Yii::error("Bucket:checkBucketName::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
      
    }
    
    public function saveBucketDetails($bucketDetails) {
        try {
            $startDate = date("Y-m-d H:i:s", strtotime('+23 hours +59 minutes', strtotime($bucketDetails->data->startDateVal)));

            $returnValue = 'failure';
            $bucket = new Bucket();
            $bucket->ProjectId = (int)$bucketDetails->projectId;
            $bucket->Name = $bucketDetails->data->title;
            $bucket->Description = $bucketDetails->data->description;
            $bucket->StartDate = $startDate;
            $bucket->DueDate = $bucketDetails->data->dueDateVal;
            $bucket->Responsible = (int)$bucketDetails->data->selectedUserFilter;
            $bucket->BucketStatus = ($bucketDetails->data->setCurrent)?(int)2:(int)1;
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
            $summary=array();
            $bucket=Bucket::findOne($bucketDetails->data->Id);
            $bucket->ProjectId = (int)$bucketDetails->projectId;
            if($bucket->Name != $bucketDetails->data->title)
             array_push($summary,array("ActionOn"=>  'bucketTitle',"OldValue"=>$bucket->Name,"NewValue"=>$bucketDetails->data->title));
            $bucket->Name = $bucketDetails->data->title;
            if($bucket->Description != $bucketDetails->data->description)
             array_push($summary,array("ActionOn"=>  'bucketDescription',"OldValue"=>strip_tags($bucket->Description),"NewValue"=>  strip_tags($bucketDetails->data->description)));
            $bucket->Description = $bucketDetails->data->description;
            if($bucket->StartDate != $startDate){
            
                array_push($summary,array("ActionOn"=>  'bucketStartDate',"OldValue"=>$bucket->StartDate,"NewValue"=>$startDate));

            }
           
            $bucket->StartDate = $startDate;
            
            if($bucket->DueDate != $dueDate){
                array_push($summary,array("ActionOn"=>  'bucketDueDate',"OldValue"=>$bucket->DueDate,"NewValue"=>$dueDate));

            }
            $bucket->DueDate = $dueDate;
            if($bucket->Responsible != (int)$bucketDetails->data->selectedUserFilter){
                array_push($summary,array("ActionOn"=>  'bucketOwner',"OldValue"=>(int)$bucket->Responsible ,"NewValue"=>(int)$bucketDetails->data->selectedUserFilter));

            }
            $bucket->Responsible = (int)$bucketDetails->data->selectedUserFilter;
            
            $bucket->update();
            if ($bucket->update() !== false) {
                EventTrait::saveEvent((int)$bucketDetails->projectId,"Bucket",$bucketDetails->data->Id,"updated",'update',(int)$bucketDetails->userInfo->Id,$summary,array("BucketId"=>(int)$bucketDetails->data->Id));
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
    
    public function getBucketChangeStatus($projectId,$bucketId,$Status) {
        try {
            $bucket=Bucket::findOne($bucketId);
            $bucket->BucketStatus = (int)$Status;

            $bucket->update();
            if ($bucket->update() !== false) {
                $result = "success";
            } else {
                $result = "failure";
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
    
     /**
     * @author Ryan
     * @return array
     * @throws ErrorException
     */
   public static function getTotalBuckets($projectId){
       try{
          $query="select count(*) as count from Bucket where ProjectId=$projectId";
          $bucketsCount = Yii::$app->db->createCommand($query)->queryAll();
          return $bucketsCount;
       } catch (\Throwable $ex) {
            Yii::error("Bucket:getTotalBuckets::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
   }
   
   /**
     * @author Ryan
     * @return array
     * @throws ErrorException
     */
   public static function getBucketsCountByType($projectId){
       try{
          $query="select BucketStatus,count(*) as Totallog from Bucket where BucketStatus IN (1,2,3,4) and ProjectId=$projectId GROUP BY BucketStatus";
          $bucketsCount = Yii::$app->db->createCommand($query)->queryAll();
          $count_array=array();
          $count_array=array("Backlog"=>0,"Current"=>0,"Completed"=>0,"Closed"=>0,"Total"=>0);
          if(isset($bucketsCount) && count($bucketsCount)>0){
              foreach ($bucketsCount as $key => $value) {
                           switch ($value['BucketStatus']) {
                               case 1:
                                    $count_array['Backlog'] = $value['Totallog'];
                                break;
                                case 2:
                                    $count_array['Current'] = $value['Totallog'];
                                break;
                                case 3:
                                    $count_array['Completed'] = $value['Totallog'];
                                break;
                                case 4:
                                    $count_array['Closed'] = $value['Totallog'];
                                break;
                               default:
                                   break;
                           }
                           $count_array["Total"]=(int)$count_array["Total"]+(int)$value['Totallog'];
              }
              
          }      
          return $count_array;
       } catch (\Throwable $ex) {
            Yii::error("Bucket:getBucketsCountByType::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
   }
   
   public static function getBucketsInfo($projectId,$type=0){
       try{
           $current_bucket_details=array();
           $closed_bucket_details=array();
           $backlog_bucket_details=array();
           $completed_bucket_details=array();
           $query="select Id,Name,Description,StartDate,DueDate,Responsible,BucketStatus from Bucket where ProjectId=$projectId and BucketStatus=$type";
           $bucket_data=Yii::$app->db->createCommand($query)->queryAll();
           error_log("==Bucket Data Count==".count($bucket_data));
           if(isset($bucket_data) && count($bucket_data)>0){
           foreach($bucket_data as $bucket_details){ 
               switch($bucket_details['BucketStatus']){
                   case 1:
                       $backlogStats=CommonUtilityTwo::getTopTicketsStats($projectId,'',$bucket_details['Id']);
                       $mergedBacklogInfo=array_merge($bucket_details,$backlogStats);
                       array_push($backlog_bucket_details,$mergedBacklogInfo);
                       break;
                   case 2:
                       $currentStats=CommonUtilityTwo::getTopTicketsStats($projectId,'',$bucket_details['Id']);
                       $bucket_owner=TinyUserCollection::getMiniUserDetails($bucket_details['Responsible']);
                       //$bucketDescription= CommonUtilityTwo::truncateHtml($bucket_details['Description'],500);//added newly for truncate
                       //$bucket_details['Description']=$bucketDescription;//added newly for truncate
                       $mergedCurrentInfo=array_merge($bucket_details,$currentStats);
                       $finalCurrentInfo=array_merge($mergedCurrentInfo,$bucket_owner);
                       array_push($current_bucket_details,$finalCurrentInfo);
                       break;
                   case 3:
                       $completedStats=CommonUtilityTwo::getTopTicketsStats($projectId,'',$bucket_details['Id']);
                       $mergedCompletedInfo=array_merge($bucket_details,$completedStats);
                       array_push($completed_bucket_details,$mergedCompletedInfo);
                       break;
                   case 4:
                       $closedStats=CommonUtilityTwo::getTopTicketsStats($projectId,'',$bucket_details['Id']);
                       $mergedClosedInfo=array_merge($bucket_details,$closedStats);
                       array_push($closed_bucket_details,$mergedClosedInfo);
                       
                       break;
                   default:
                       break;
               }
           }
               error_log("==Current Bucket Count==".count($current_bucket_details));
               if($type==2){
                   $totalStats=array('Current'=>$current_bucket_details);
               }else{
               $totalStats=array('Backlog'=>$backlog_bucket_details,'Current'=>$current_bucket_details,'Completed'=>$completed_bucket_details,'Closed'=>$closed_bucket_details);
               }
               return $totalStats;
          
        }
           
       } catch (\Throwable $ex) {
            Yii::error("Bucket:getBucketsInfo::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
   }
   
   public static function getCurrentWeekBucketsInfo($currentWeekBuckets,$projectId){
       try{
           
            $bucketIds=array(); 
            $activityCount=array();
            $currentWeekBucketDetailsByMaxActivity=array();
            foreach($currentWeekBuckets as $buckets){
                if($buckets["_id"]!=0 && $buckets["_id"]!='' && !empty($buckets["_id"])){
                    array_push($bucketIds,$buckets['_id']);
                    array_push($activityCount,$buckets['count']);
                 }
                }
          
               usort($currentWeekBuckets, function($a,$b){
                   return $a['count']<=$b['count'];
               });
               
               if(!empty($bucketIds[0])){ 
                    $ids= implode(",",$bucketIds);
                    error_log("==Ids==".$ids);
                    $query="select Id,Name,Description,StartDate,DueDate,Responsible,BucketStatus from Bucket where  Id in (".$ids.") and BucketStatus!=2 and ProjectId=$projectId order by DueDate desc";
                    $bucket_data=Yii::$app->db->createCommand($query)->queryAll();
            
                    foreach($currentWeekBuckets as $key=>$value){
                        foreach($bucket_data as $bucketData){
                            if($bucketData['Id']==$value['_id']){
                                 error_log("---buckkkk--".$bucketData['Id']);
                                $bucketDescription= CommonUtilityTwo::truncateHtml($bucketData['Description'],500);//added newly for truncate
                                $bucketData['Description']=$bucketDescription;//added newly for truncate
                                $owner=TinyUserCollection::getMiniUserDetails($bucketData['Responsible']);
                                $mergedBucketInfo=array_merge($bucketData,$owner);
                                array_push($currentWeekBucketDetailsByMaxActivity,$mergedBucketInfo);
                            }
                        }       
                    }
               }
                      
            return $currentWeekBucketDetailsByMaxActivity;
           
       } catch (\Throwable $ex) {
            Yii::error("Bucket:getCurrentWeekBucketsInfo::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
   }
   
   public function getBucketStatusNameById($bucketStatusId){
       $qry = "SELECT Name FROM BucketStatus where Id = $bucketStatusId";
         $data = Yii::$app->db->createCommand($qry)->queryAll();  
         return $data;
   }
   
    public static function getBackLogBucketByProjectId($projectId){
         $qry = "SELECT Id,Name FROM Bucket where ProjectId = $projectId and Name='Backlog'";
         $data = Yii::$app->db->createCommand($qry)->queryOne();  
         return $data;
    }
 
}

?>