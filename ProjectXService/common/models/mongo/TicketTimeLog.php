<?php
namespace common\models\mongo;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/*
 * refer : 
 * https://github.com/yiisoft/yii2-mongodb/blob/master/docs/guide/usage-ar.md
 */
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\base\ErrorException;
use yii\mongodb\ActiveRecord;
use yii\mongodb\Query;
use yii\data\ActiveDataProvider;
use yii\web\IdentityInterface;



class TicketTimeLog extends ActiveRecord 
{
    public static function collectionName()
    {
        return 'TicketTimeLog';
    }
    public function attributes() {
//        parent::attributes();
        return [
            
          "_id",
          "TicketId",
          "ProjectId",
          "TimeLog",  
          "CreatedOn",
          "UpdatedOn"

        ];
    }
    
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => '\yii\behaviors\TimestampBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['CreatedOn','UpdatedOn'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['UpdatedOn'],
                ],
                 'value' => function() { return new \MongoDB\BSON\UTCDateTime(time() * 1000); // unix timestamp 
                 },
            ],
        ];
    }
    
      /**
     * @author suryaprakash reddy 
     * @description This method is used to save timelog data in ticketTimeCollection
     * @return type mongoId
     */
    public function saveTimeLogData1($projectId, $ticketId, $userId, $totalWorkHours) {

        try {
            $returnValue = 'failure';
            $timelogObj = new TicketTimeLog();
            $timelogObj->ProjectId = (int) $projectId;
            $timelogObj->TicketId = (int) $ticketId;
            $timelogObj->CollaboratorId = (int) $userId;
            $timelogObj->Time = $totalWorkHours;
            if ($timelogObj->insert()) {
                $returnValue = $timelogObj->_id;
            }
            return $returnValue;
            
        } catch (\Throwable $ex) {
            Yii::error("TicketTimeLogCollection:saveTimeLogData1::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
        
        
        
    }
    /**
     * @author Moin Hussain
     * @param type $projectId
     * @param type $ticketId
     * @param type $userId
     * @param type $totalWorkHours
     * @return type
     */
      public static function saveTimeLogData($projectId, $ticketId, $userId, $totalWorkHours,$description,$LoggedOn) {

        try {
            $returnValue = 'failure';
//            $timelogObj = new TicketTimeLog();
//            $timelogObj->ProjectId = (int) $projectId;
//            $timelogObj->TicketId = (int) $ticketId;
//            $timelogObj->CollaboratorId = (int) $userId;
//            $timelogObj->Time = $totalWorkHours;
//            if ($timelogObj->insert()) {
//                $returnValue = $timelogObj->_id;
//            }
//            
          $db =  TicketTimeLog::getCollection();
          $currentDate = new \MongoDB\BSON\UTCDateTime(strtotime($LoggedOn)*1000);
          $returnValue =  $db->findAndModify( array("ProjectId"=> (int)$projectId ,"TicketId"=> (int)$ticketId), array('$addToSet'=> array('TimeLog' =>array("Slug" => new \MongoDB\BSON\ObjectID(),"TicketId"=> (int)$ticketId,"Time"=>(float)$totalWorkHours,"CollaboratorId" => (int)$userId,"LoggedOn" => $currentDate,"Description"=> $description))),array('new' => 1,"upsert"=>1));
            
            return $returnValue;
            
        } catch (\Throwable $ex) {
            Yii::error("TicketTimeLogCollection:saveTimeLogData::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
        
        
        
    }

    /**
     * @modified by Moin Hussain
     * @author suryaprakash reddy 
     * @description This method is used to getTimeLogRecords for userbased
     * @return type array
     */
    public static function getTimeLogRecords($projectId, $ticketsList) {
        try {
           
            $matchArray = array("TicketId" => array('$in' => $ticketsList), "ProjectId" => (int) $projectId);
            $query = Yii::$app->mongodb->getCollection('TicketTimeLog');
            $pipeline = array(
               
                array('$unwind'=> '$TimeLog'),
                array('$match' => $matchArray),
                array(
                    '$group' => array(
                        '_id' => '$TimeLog.CollaboratorId',
                        "sum" => array('$sum' => '$TimeLog.Time'),
                    ),
                ),
            );
            $Arraytimelog = $query->aggregate($pipeline);
            return $Arraytimelog;
        } catch (\Throwable $ex) {
            Yii::error("TicketTimeLogCollection:getTimeLogRecords::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

    /**
     * @author Praveen P
     * @param type $projectId
     * @param type $collaboratorId
     * @param type $gettimelogdetailsforcollaboratorId
     * @return type
     */
    
    public static function getAllTimeReportDetails($StoryData, $projectId) {
        try {
            $toDate = date("Y-m-d H:i:s", strtotime('+23 hours +59 minutes', strtotime($StoryData->toDate)));
            $skip = $StoryData->offset * $StoryData->pagesize;
            $limit = $skip + $StoryData->pagesize;
            if($skip>0){
                //$skip =$skip-1; 
            }
            $matchArray = array('TimeLog.CollaboratorId' => (int)$StoryData->userInfo->Id, "ProjectId" => (int) $projectId,'TimeLog.LoggedOn'=>array('$gte' =>new \MongoDB\BSON\UTCDateTime(strtotime($StoryData->fromDate)*1000),'$lte' =>new \MongoDB\BSON\UTCDateTime(strtotime($toDate)*1000)));
            $query = Yii::$app->mongodb->getCollection('TicketTimeLog');
            $pipeline = array(
                array('$unwind' => '$TimeLog'),
                array('$match' => $matchArray),
                array('$sort' => array("TimeLog.LoggedOn" => -1)),
                array('$limit' => $limit),array('$skip' => $skip),
                array(
                    '$group' => array(
                        '_id' => '$TimeLog.CollaboratorId',
                         "data" => array('$push' => '$TimeLog'),
                    )),
              
                
                );
            $timeReportDetails = $query->aggregate($pipeline);
            return $timeReportDetails;
        } catch (\Throwable $ex) {
            Yii::error("TicketTimeLogCollection:getAllTimeReportDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
    /**
     * @author Praveen P
     * @param type $projectId
     * @param type $collaboratorId
     * @param type $totalTimeLogReportforCollaborator
     * @return type
     */
    
    public static function getTimeReportCountAndWorkLog($StoryData, $projectId) {
        try {
            $toDate = date("Y-m-d H:i:s", strtotime('+23 hours +59 minutes', strtotime($StoryData->toDate)));
            $matchArray = array('TimeLog.CollaboratorId' => (int)$StoryData->userInfo->Id, "ProjectId" => (int) $projectId,'TimeLog.LoggedOn'=>array('$gte' =>new \MongoDB\BSON\UTCDateTime(strtotime($StoryData->fromDate)*1000),'$lte' =>new \MongoDB\BSON\UTCDateTime(strtotime($toDate)*1000)));
            
            $query = Yii::$app->mongodb->getCollection('TicketTimeLog');
            $pipeline = array(
                array('$unwind' => '$TimeLog'),
                array('$match' => $matchArray),
                //array('Time.LoggedOn'=>array('$lt' => new MongoDate($date))),
                array(
                    '$group' => array(
                        '_id' => '$TimeLog.CollaboratorId',
                        "count" => array('$sum' => 1),
                        "totalHours" => array('$sum' => '$TimeLog.Time'),
                    ),
                ),
            );
            $Arraytimelog = $query->aggregate($pipeline);
           // $Arraytimelog[0]['count']=!empty($Arraytimelog[0]['count'])?$Arraytimelog[0]['count']:0;
            return $Arraytimelog;
        } catch (\Throwable $ex) {
            Yii::error("TicketTimeLogCollection:getTimeReportCountAndWorkLog::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
  
    /**
     * @author Padmaja
     * @return type
     */
    public static function updateTimeLogRecords($projectId,$slug,$timelogHours,$ticketId,$autocompleteticketId="",$editableDate,$calendardate="",$userId,$description=""){
        try{
            $returnValue='failure';
            $loggonDate = "";
            if(!empty($calendardate)){
                $loggonDate=$calendardate;
            }
//            }else{
//                  error_log("else---------------");
//                $loggonDate=$editableDate;
//            }
            if(!empty($autocompleteticketId)){
                  if($loggonDate == ""){
                     $loggonDate=$editableDate;
                  }
                $db =  TicketTimeLog::getCollection();
                // $currentDate =$loggonDate;
                $currentDate = new \MongoDB\BSON\UTCDateTime(strtotime($loggonDate) * 1000);
                $returnValue =  $db->findAndModify( array("ProjectId"=> (int)$projectId ,"TicketId"=> (int)$autocompleteticketId), array('$addToSet'=> array('TimeLog' =>array("Slug" => new \MongoDB\BSON\ObjectID(),"TicketId"=> (int)$autocompleteticketId,"Time"=>(float)$timelogHours,"CollaboratorId" => (int)$userId,"LoggedOn" => $currentDate,"Description"=> $description))),array('new' => 1,"upsert"=>1));
                $collection = Yii::$app->mongodb->getCollection('TicketTimeLog');
                $newdata = array('$pull'=> array('TimeLog' =>array("Slug"=>new \MongoDB\BSON\ObjectID($slug))));
                $collection->update(array("TicketId" => (int) $ticketId, "ProjectId" => (int)$projectId), $newdata);
                $returnValue=$slug;
             }else{
               $newData = array('TimeLog.$.Time' =>(float) $timelogHours,'TimeLog.$.Description' => $description);
                 if($loggonDate != ""){
                      $currentDate = new \MongoDB\BSON\UTCDateTime(strtotime($loggonDate) * 1000);
                       $newData['TimeLog.$.LoggedOn'] = $currentDate;
                 }
               
              
                $collection =  TicketTimeLog::getCollection();
                $newdata = array('$set' =>$newData);
                $collection->update(array("ProjectId"=> (int)$projectId ,"TicketId"=> (int)$ticketId,"TimeLog.Slug"=>new \MongoDB\BSON\ObjectID($slug)), $newdata); 
                $returnValue=$slug;
            }
            return $returnValue;
        } catch (\Throwable $ex) {
            Yii::error("TicketTimeLogCollection:updateTimeLogRecords::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

      /**
     * @author Padmaja
     * @return type
     */
    public static function removeTimelogData($projectId,$ticketId,$slug,$timelogHours,$userId){
        try{
            $returnValue= 'failure'; 
            $collection = Yii::$app->mongodb->getCollection('TicketTimeLog');
            $newdata = array('$pull'=> array('TimeLog' =>array("Time" => (float)$timelogHours,"Slug"=>new \MongoDB\BSON\ObjectID($slug)),"multi"=>FALSE));
            if($collection->update(array("TicketId" => (int) $ticketId, "ProjectId" => (int)$projectId), $newdata)){
                return $returnValue=$slug;
            }

            return $returnValue;
       
        } catch (\Throwable $ex) {
            Yii::error("TicketTimeLogCollection:removeTimelogData::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
        
    }

    }
?>

