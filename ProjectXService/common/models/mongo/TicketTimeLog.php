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
          "CollaboratorId",
          "Time",
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
      public function saveTimeLogData($projectId, $ticketId, $userId, $totalWorkHours) {

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
           $currentDate = new \MongoDB\BSON\UTCDateTime(time() * 1000);
          $returnValue =  $db->findAndModify( array("ProjectId"=> (int)$projectId ,"TicketId"=> (int)$ticketId), array('$addToSet'=> array('TimeLog' =>array("Slug" => new \MongoDB\BSON\ObjectID(),"Time"=>$totalWorkHours,"CollaboratorId" => (int)$userId,"LoggedOn" => $currentDate ))),array('new' => 1,"upsert"=>1));
            
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
    public function getTimeLogRecords($projectId, $ticketsList) {
        try {
          
            $matchArray = array("TicketId" => array('$in' => $ticketsList), "ProjectId" => (int) $projectId);
            $query = Yii::$app->mongodb->getCollection('TicketTimeLog');
            $pipeline = array(
                array('$match' => $matchArray),
                array('$unwind'=> '$TimeLog'),
                array(
                    '$group' => array(
                        '_id' => '$TimeLog.CollaboratorId',
                        "sum" => array('$sum' => '$TimeLog.Time'),
                    ),
                ),
            );
            $Arraytimelog = $query->aggregate($pipeline);
          //  error_log(print_r($Arraytimelog,1));
            return $Arraytimelog;
        } catch (Exception $ex) {
            Yii::log("TicketTimeLog:getTimeLogRecords::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

}
?>

