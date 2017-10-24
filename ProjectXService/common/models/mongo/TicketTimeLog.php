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
     * @Description This method is used to save timelog data in ticketTimeCollection
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
     * @Description This method is used to save timelog data in ticketTimeCollection
     */
      public static function saveTimeLogData($projectId, $ticketId, $userId, $totalWorkHours,$description,$LoggedOn) {

        try {
            $returnValue = 'failure';
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
     * @Description This method is used to getTimeLogRecords for userbased
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
     * @Description Returns the details of all the Time reports of a story.
     */
    
    public static function getAllTimeReportDetails($StoryData, $projectId) {
        try {
            $toDate = date("Y-m-d H:i:s", strtotime('+23 hours +59 minutes', strtotime($StoryData->toDate)));
            $skip = $StoryData->offset * $StoryData->pagesize;
            $limit = $skip + $StoryData->pagesize; 
            $selectedMembers=array();
            $members=$StoryData->members;
            foreach($members as $member){
                $member=(int)$member;
                array_push($selectedMembers,$member);
            }
            if($skip>0){
            }
            if(empty($StoryData->members)){
                $matchArray = array('TimeLog.CollaboratorId' => (int)$StoryData->userInfo->Id, "ProjectId" => (int) $projectId,'TimeLog.LoggedOn'=>array('$gte' =>new \MongoDB\BSON\UTCDateTime(strtotime($StoryData->fromDate)*1000),'$lte' =>new \MongoDB\BSON\UTCDateTime(strtotime($toDate)*1000)));
            }else{ //added by Ryan for getting the time reports of selected members of Team
                $matchArray = array('TimeLog.CollaboratorId' => array('$in'=>$selectedMembers), "ProjectId" => (int) $projectId,'TimeLog.LoggedOn'=>array('$gte' =>new \MongoDB\BSON\UTCDateTime(strtotime($StoryData->fromDate)*1000),'$lte' =>new \MongoDB\BSON\UTCDateTime(strtotime($toDate)*1000))); 
            }
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
     * @Description Gets the total work logged on a story.
     */
    
    public static function getTimeReportCountAndWorkLog($StoryData, $projectId) {
        try {
            $toDate = date("Y-m-d H:i:s", strtotime('+23 hours +59 minutes', strtotime($StoryData->toDate)));
            $selectedMembers=array();
            $members=$StoryData->members;
            foreach($members as $member){
                $member=(int)$member;
                array_push($selectedMembers,$member);
            }
            if(empty($StoryData->members)){
                $matchArray = array('TimeLog.CollaboratorId' => (int)$StoryData->userInfo->Id, "ProjectId" => (int) $projectId,'TimeLog.LoggedOn'=>array('$gte' =>new \MongoDB\BSON\UTCDateTime(strtotime($StoryData->fromDate)*1000),'$lte' =>new \MongoDB\BSON\UTCDateTime(strtotime($toDate)*1000)));
            }else{ //added for getting the worklogs of selected Members
                 $matchArray = array('TimeLog.CollaboratorId' => array('$in'=>$selectedMembers), "ProjectId" => (int) $projectId,'TimeLog.LoggedOn'=>array('$gte' =>new \MongoDB\BSON\UTCDateTime(strtotime($StoryData->fromDate)*1000),'$lte' =>new \MongoDB\BSON\UTCDateTime(strtotime($toDate)*1000))); 
            }
            $query = Yii::$app->mongodb->getCollection('TicketTimeLog');
            $pipeline = array(
                array('$unwind' => '$TimeLog'),
                array('$match' => $matchArray),
                array(
                    '$group' => array(
                        '_id' => '$TimeLog.CollaboratorId',
                        "count" => array('$sum' => 1),
                        "totalHours" => array('$sum' => '$TimeLog.Time'),
                    ),
                ),
            );
            $Arraytimelog = $query->aggregate($pipeline);
            return $Arraytimelog;
        } catch (\Throwable $ex) {
            Yii::error("TicketTimeLogCollection:getTimeReportCountAndWorkLog::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
  
    /**
     * @author Padmaja
     * @return type
     * @Description Updates the time log record
     */
    public static function updateTimeLogRecords($projectId,$slug,$timelogHours,$ticketId,$autocompleteticketId="",$editableDate,$calendardate="",$userId,$description=""){
        try{
            $returnValue='failure';
            $loggonDate = "";
            if(!empty($calendardate)){
                $loggonDate=$calendardate;
            }
            if(!empty($autocompleteticketId)){
                  if($loggonDate == ""){
                     $loggonDate=$editableDate;
                  }
                $db =  TicketTimeLog::getCollection();
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
     * @Description Deletes a time log
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
    
    /**
     * @authr   Anand Singh
     * @Description  Get current week worklog for any particular user.
     * @param type $userId
     * @param type $projectId
     * @return type
     * @throws ErrorException
     */
    public static function getCurrentWeekTimeLog($userId='',$projectId=''){
   
        try {
            
                $weekFirstDay = date("Y-m-d H:i:s", strtotime('last monday', strtotime('tomorrow'))); 
                $toDate = date("Y-m-d H:i:s");
                $_id='$TimeLog.CollaboratorId';
                $matchArray = array('TimeLog.CollaboratorId' => (int)$userId, "ProjectId" => (int) $projectId,'TimeLog.LoggedOn'=>array('$gte' =>new \MongoDB\BSON\UTCDateTime(strtotime($weekFirstDay)*1000),'$lte' =>new \MongoDB\BSON\UTCDateTime(strtotime($toDate)*1000)));
                if($projectId=='')unset($matchArray['ProjectId']);
                if($userId==''){
                unset($matchArray['TimeLog.CollaboratorId']);
                $_id='null';
                }
                $query = Yii::$app->mongodb->getCollection('TicketTimeLog');
                        $pipeline = array(
                        array('$unwind' => '$TimeLog'),
                        array('$match' => $matchArray),
                        array(
                            '$group' => array(
                                '_id' => $_id,
                               "count" => array('$sum' => 1),
                        "totalHours" => array('$sum' => '$TimeLog.Time'),
                    ),
                ),
            );
            $Arraytimelog = $query->aggregate($pipeline);
            return $Arraytimelog;  
        } catch (\Throwable $ex) {
            Yii::error("TicketTimeLogCollection:getCurrentWeekTimeLog::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
   
       
    }
        /**
     * @authr   Padmaja
     * @Description  Get Total worklog for Project.
     * @param type $projectId
     * @return type
     * @throws ErrorException
     */
    public static function getTotalTimeLogByProject($projectId=''){
   
        try {
             error_log("log--ggggggggggggg----".$projectId);
            $matchArray = array( "ProjectId" => (int) $projectId);  
            if($projectId=='')unset($matchArray['ProjectId']);
                $query = Yii::$app->mongodb->getCollection('TicketTimeLog');
                $pipeline = array(
                array('$unwind' => '$TimeLog'),
                array('$match' => $matchArray),
                array(
                    '$group' => array(
                        '_id' => 'null',
                       "count" => array('$sum' => 1),
                        "totalHours" => array('$sum' => '$TimeLog.Time'),
                    ),
                ),
            );
            $Arraytimelog = $query->aggregate($pipeline);
            return $Arraytimelog;  
        } catch (\Throwable $ex) {
            Yii::error("TicketTimeLogCollection:getTotalTimeLogByProject::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
        
     
      
    }

    }
?>

