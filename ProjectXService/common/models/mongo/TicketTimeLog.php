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
//use yii\db\ActiveRecord;
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
            
        } catch (Exception $ex) {
            Yii::log("TicketTimeLog:saveTimeLogData::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
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
      public static function saveTimeLogData($projectId, $ticketId, $userId, $totalWorkHours,$description='') {

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
          $currentDate = new \MongoDB\BSON\UTCDateTime(time() * 1000);error_log("current dataeee".$currentDate);
          $returnValue =  $db->findAndModify( array("ProjectId"=> (int)$projectId ,"TicketId"=> (int)$ticketId), array('$addToSet'=> array('TimeLog' =>array("Slug" => new \MongoDB\BSON\ObjectID(),"TicketId"=> (int)$ticketId,"Time"=>$totalWorkHours,"CollaboratorId" => (int)$userId,"LoggedOn" => $currentDate,"Description"=> $description))),array('new' => 1,"upsert"=>1));
            
            return $returnValue;
            
        } catch (Exception $ex) {
            Yii::log("TicketTimeLog:saveTimeLogData::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
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
          error_log("@@@@@@@@@@@@@".print_r($Arraytimelog,1));
            return $Arraytimelog;
        } catch (Exception $ex) {
            Yii::log("TicketTimeLog:getTimeLogRecords::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
    /**
     * @author Praveen P
     * @param type $projectId
     * @param type $collaboratorId
     * @param type $gettimelogdetailsforcollaboratorId
     * @return type
     */
    
    public static function getAllTimeReportDetails($StoryData, $projectId) {$StoryData->pagesize=2;
        try {
            error_log("getAllTimeReportDetails--");
            $StoryData->toDate = date("Y-m-d H:i:s", strtotime('+23 hours +59 minutes', strtotime($StoryData->toDate)));
            $skip = $StoryData->offset * $StoryData->pagesize;
           
           
            $limit = $skip + $StoryData->pagesize;
             if($skip>0){
                //$skip =$skip-1; 
            }
              error_log("getAllTimeReportDetails--**----".$limit."---".$skip);
            $matchArray = array('TimeLog.CollaboratorId' => (int)$StoryData->userInfo->Id, "ProjectId" => (int) $projectId,'TimeLog.LoggedOn'=>array('$gte' =>new \MongoDB\BSON\UTCDateTime(strtotime($StoryData->fromDate)*1000),'$lte' =>new \MongoDB\BSON\UTCDateTime(strtotime($StoryData->toDate)*1000)));
            $query = Yii::$app->mongodb->getCollection('TicketTimeLog');
            $pipeline = array(
                array('$unwind' => '$TimeLog'),
                array('$match' => $matchArray),
                array(
                    '$group' => array(
                        '_id' => '$_id',
                         "data" => array('$push' => '$TimeLog'),
                    )),

                array('$limit' => $limit),array('$skip' => $skip),

             //   array('$limit' => $StoryData->pagesize),array('$skip' => $StoryData->offset),
                );
             error_log("--cccc---");
            $timeReportDetails = $query->aggregate($pipeline);
            $TimeLogDetailsFinalArray = array();
            $TimeLogDataArray= array();
           // error_log("--size---".count($timeReportDetails));
              error_log("--size---".print_r($timeReportDetails,1));
              $timelogs = $timeReportDetails[0]["data"];
            //foreach($timeReportDetails as $extractTimeLog){
                
                $TimeLogDatafinalArray =array();
                foreach($timelogs as $eachOne){
                    
                   $ticketCollectionModel = new TicketCollection();
                $getTicketDetails = $ticketCollectionModel->getTicketDetails($eachOne['TicketId'],$projectId,$selectFields=[]);
                $ticketDesc= '#'.$getTicketDetails['TicketId'].".".$getTicketDetails['Title'];
                error_log("ticket dexc---".$ticketDesc);
                $ticketTask = $getTicketDetails["Fields"]['planlevel']['value']; 
                    
                    
                    $datetime = $eachOne['LoggedOn']->toDateTime();  error_log("%%%%%%%%%%%%%%".$eachOne['LoggedOn']);
                    $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                    $LogDate = $datetime->format('M-d-Y');
                    $readableDate =$datetime->format('Y-m-d');
                    error_log("ssssssss##########".$readableDate);
                    $ticketId = array("field_name" => "Id", "value_id" => "", "field_value" => $ticketDesc, "other_data" => $ticketTask, "ticketDesc" => $ticketDesc,"Time"=>$eachOne['Time'],"LogDate"=>$LogDate,"Slug"=>$eachOne['Slug'],"ticketId"=>$getTicketDetails['TicketId'],"description"=>$eachOne['Description'],"readableDate"=>$readableDate);
                    $time = array("field_name" => "Date", "value_id" => "", "field_value" => $eachOne['Time'], "other_data" => "", "ticketDesc" =>$ticketDesc,"Time"=>$eachOne['Time'],"LogDate"=>$LogDate,"Slug"=>$eachOne['Slug'],"ticketId"=>$getTicketDetails['TicketId'],"description"=>$eachOne['Description'],"readableDate"=>$readableDate);
                    $date = array("field_name" => "Time", "value_id" => "", "field_value" => $LogDate, "other_data" => "", "ticketDesc" =>$ticketDesc,"Time"=>$eachOne['Time'],"LogDate"=>$LogDate,"Slug"=>$eachOne['Slug'],"ticketId"=>$getTicketDetails['TicketId'],"description"=>$eachOne['Description'],"readableDate"=>$readableDate);
                    $action = array("field_name" => "action", "value_id" => "", "field_value" => '', "other_data" => "", "ticketDesc" =>$ticketDesc,"Time"=>$eachOne['Time'],"LogDate"=>$LogDate,"Slug"=>$eachOne['Slug'],"ticketId"=>$getTicketDetails['TicketId'],"description"=>$eachOne['Description'],"readableDate"=>$readableDate);
                    $forTicketComments[0] = $date;
                    $forTicketComments[1] =  $ticketId;
                    $forTicketComments[2] = $time;
                    $forTicketComments[3] = $action;
                    
                   array_push($TimeLogDataArray,$forTicketComments);
                }
          //  }
            return $TimeLogDataArray;
        } catch (Exception $ex) {
            Yii::log("TicketTimeLog:getAllTimeReportDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
    /**
     * @author Praveen P
     * @param type $projectId
     * @param type $collaboratorId
     * @param type $totalTimeLogReportforCollaborator
     * @return type
     */
    
    public static function getTimeReportCount($StoryData, $projectId) {
        try {
            $StoryData->toDate = date("Y-m-d H:i:s", strtotime('+23 hours +59 minutes', strtotime($StoryData->toDate)));
            $matchArray = array('TimeLog.CollaboratorId' => (int)$StoryData->userInfo->Id, "ProjectId" => (int) $projectId,'TimeLog.LoggedOn'=>array('$gte' =>new \MongoDB\BSON\UTCDateTime(strtotime($StoryData->fromDate)*1000),'$lte' =>new \MongoDB\BSON\UTCDateTime(strtotime($StoryData->toDate)*1000)));
            
            $query = Yii::$app->mongodb->getCollection('TicketTimeLog');
            $pipeline = array(
                array('$unwind' => '$TimeLog'),
                array('$match' => $matchArray),
                //array('Time.LoggedOn'=>array('$lt' => new MongoDate($date))),
                array(
                    '$group' => array(
                        '_id' => '$TimeLog.CollaboratorId',
                        "count" => array('$sum' => 1),
                    ),
                ),
            );
            $Arraytimelog = $query->aggregate($pipeline);
            $Arraytimelog[0]['count']=!empty($Arraytimelog[0]['count'])?$Arraytimelog[0]['count']:0;
            return $Arraytimelog[0]['count'];
        } catch (Exception $ex) {
            Yii::log("TicketCollection:getTimeReportCount::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
    
    public static function getTimeLogRecordsForLast7Days($StoryData,$projectId) {
        try {
            $StoryData->toDate = date("Y-m-d H:i:s", strtotime('+23 hours +59 minutes', strtotime($StoryData->toDate)));
            $matchArray = array('TimeLog.CollaboratorId' => (int)$StoryData->userInfo->Id, "ProjectId" => (int) $projectId,'TimeLog.LoggedOn'=>array('$gte' =>new \MongoDB\BSON\UTCDateTime(strtotime($StoryData->fromDate)*1000),'$lte' =>new \MongoDB\BSON\UTCDateTime(strtotime($StoryData->toDate)*1000)));
            $query = Yii::$app->mongodb->getCollection('TicketTimeLog');
            $pipeline = array(
                array('$unwind'=> '$TimeLog'),
                array('$match' => $matchArray),
                array(
                    '$group' => array(
                        '_id' => '$TimeLog.CollaboratorId',
                        "totalHours" => array('$sum' => '$TimeLog.Time'),
                    ),
                ),
            );
            $last7DaysTimelog = $query->aggregate($pipeline);
            error_log("count----------".print_r($last7DaysTimelog,1));
            $last7DaysTimelog[0]['totalHours']=!empty($last7DaysTimelog[0]['totalHours'])?$last7DaysTimelog[0]['totalHours']:0;
            return $last7DaysTimelog[0]['totalHours'];
        } catch (Exception $ex) {
            Yii::log("TicketTimeLog:getTimeLogRecords::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    /**
     * @author Padmaja
     * @return type
     */
    public static function updateTimeLogRecords($projectId,$slug,$timelogHours,$ticketId,$autocompleteticketId="",$editableDate,$calendardate="",$userId,$description=""){
        try{
            $TimeLogDataArray=array();
                if(!empty($calendardate)){
                    $loggonDate=$calendardate;
                }else{
                    $loggonDate=$editableDate;
                }
            if(!empty($autocompleteticketId)){
                error_log("autocompleteeeeeeeeeeee");
                $db =  TicketTimeLog::getCollection();
                $currentDate =$loggonDate;
              //  $currentDate = new \MongoDB\BSON\UTCDateTime(strtotime($loggonDate) * 1000);
                $returnValue =  $db->findAndModify( array("ProjectId"=> (int)$projectId ,"TicketId"=> (int)$autocompleteticketId), array('$addToSet'=> array('TimeLog' =>array("Slug" => new \MongoDB\BSON\ObjectID(),"Time"=>$timelogHours,"CollaboratorId" => (int)$userId,"LoggedOn" => $currentDate,"Description"=> $description))),array('new' => 1,"upsert"=>1));
                
                $collection = Yii::$app->mongodb->getCollection('TicketTimeLog');
                $newdata = array('$pull'=> array('TimeLog' =>array("Time" => $timelogHours,"Slug"=>new \MongoDB\BSON\ObjectID($slug))));
                $collection->update(array("TicketId" => (int) $ticketId, "ProjectId" => $projectId), $newdata);
                $ticketCollectionModel = new TicketCollection();
                $getTicketDetails = $ticketCollectionModel->getTicketDetails($autocompleteticketId,$projectId,$selectFields=[]);
                $ticketDesc= '#'.$getTicketDetails['TicketId'].".".$getTicketDetails['Title'];
                $ticketTask = $getTicketDetails["Fields"]['planlevel']['value'];
                $datetime = strtotime($currentDate);
                $LogDate = date('M-d-Y',$datetime);
                $ticketId = array("field_name" => "Id", "value_id" => "", "field_value" => $ticketDesc, "other_data" => $ticketTask, "ticketDesc" => "","Time"=>"","LogDate"=>"","Slug"=>"","ticketId"=>"");
                $time = array("field_name" => "Date", "value_id" => "", "field_value" => $timelogHours, "other_data" => "", "ticketDesc" =>"","Time"=>"","LogDate"=>"","Slug"=>"","ticketId"=>"");
                $date = array("field_name" => "Time", "value_id" => "", "field_value" =>$LogDate, "other_data" => "", "ticketDesc" =>"","Time"=>"","LogDate"=>"","Slug"=>"","ticketId"=>"");
                $action = array("field_name" => "action", "value_id" => "", "field_value" => '', "other_data" => "", "ticketDesc" =>"","Time"=>"","LogDate"=>"","Slug"=>"","ticketId"=>"");
                $forTicketComments[0] = $date;
                $forTicketComments[1] =  $ticketId;
                $forTicketComments[2] = $time;
                $forTicketComments[3] = $action;
                array_push($TimeLogDataArray,$forTicketComments);
                  
            }else{
                 error_log("autocompleteeeeeeeeeeee@@@@@@@@@@@@@@@@@@@@@".$timelogHours.$loggonDate);
                 $collection =  TicketTimeLog::getCollection();
                 $newdata = array('$set' => array('TimeLog.$.Time' => $timelogHours,'TimeLog.$.Description' => $description));
                 $collection->update(array("ProjectId"=> (int)$projectId ,"TicketId"=> (int)$ticketId,"TimeLog.Slug"=>new \MongoDB\BSON\ObjectID($slug)), $newdata); 
                 $ticketCollectionModel = new TicketCollection();
                 $getTicketDetails = $ticketCollectionModel->getTicketDetails($ticketId,$projectId,$selectFields=[]);
                 $ticketDesc= '#'.$getTicketDetails['TicketId'].".".$getTicketDetails['Title'];
                 $ticketTask = $getTicketDetails["Fields"]['planlevel']['value'];
                $getTicketDetails = $ticketCollectionModel->getTicketDetails($ticketId,$projectId,$selectFields=[]);
                $ticketDesc= '#'.$getTicketDetails['TicketId'].".".$getTicketDetails['Title'];
                $ticketTask = $getTicketDetails["Fields"]['planlevel']['value'];
                $currentDate =$loggonDate;
                $datetime = strtotime($currentDate);
                $LogDate = date('M-d-Y',$datetime);
                $ticketId = array("field_name" => "Id", "value_id" => "", "field_value" => $ticketDesc, "other_data" => $ticketTask, "ticketDesc" => "","Time"=>"","LogDate"=>"","Slug"=>"","ticketId"=>"");
                $time = array("field_name" => "Date", "value_id" => "", "field_value" => $timelogHours, "other_data" => "", "ticketDesc" =>"","Time"=>"","LogDate"=>"","Slug"=>"","ticketId"=>"");
                $date = array("field_name" => "Time", "value_id" => "", "field_value" =>$LogDate, "other_data" => "", "ticketDesc" =>"","Time"=>"","LogDate"=>"","Slug"=>"","ticketId"=>"");
                $action = array("field_name" => "action", "value_id" => "", "field_value" => '', "other_data" => "", "ticketDesc" =>"","Time"=>"","LogDate"=>"","Slug"=>"","ticketId"=>"");
                $forTicketComments[0] = $date;
                $forTicketComments[1] =  $ticketId;
                $forTicketComments[2] = $time;
                $forTicketComments[3] = $action;
                array_push($TimeLogDataArray,$forTicketComments); 
            }
            return $TimeLogDataArray;
        } catch (Exception $ex) {
            Yii::log("TicketTimeLog:updateTimeLogRecords::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
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
            $newdata = array('$pull'=> array('TimeLog' =>array("Time" => $timelogHours,"Slug"=>new \MongoDB\BSON\ObjectID($slug)),"multi"=>FALSE));

            if($collection->update(array("TicketId" => (int) $ticketId, "ProjectId" => (int)$projectId), $newdata)){
                return $returnValue=$slug;
            }

            return $returnValue;
       
        } catch (Exception $ex) {
             Yii::log("TicketTimeLog:removeTimelogData::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
        
    }
}
?>

