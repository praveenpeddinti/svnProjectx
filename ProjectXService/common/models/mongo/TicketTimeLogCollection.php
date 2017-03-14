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



class TicketTimeLogCollection extends ActiveRecord 
{
    public static function collectionName()
    {
        return 'TicketTimeLogCollection';
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
    
    /*@surya
     * 
     */
    public function saveTimeLogData($projectId,$ticketId,$userId,$totalWorkHours){
        
        try{
             $returnValue = 'failure';
            $timelogObj = new TicketTimeLogCollection();
            $timelogObj->ProjectId = (int)$projectId;
            $timelogObj->TicketId = (int)$ticketId;
            $timelogObj->CollaboratorId = (int)$userId;
            $timelogObj->Time = (float)$totalWorkHours;
            if ($timelogObj->insert()) {
                $returnValue = $timelogObj->_id;
            }
            return $returnValue; 
            
        } catch (Exception $ex) {
            Yii::log("TicketTimeLogCollection:saveTimeLogData::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
        
        
        
    }
    
    
    
  public  function getTimeLogRecords($projectId,$ticketId){
        try{
            
            error_log("+++++++++++++getimelog+++++++++======".$projectId."____".$ticketId);
               $matchArray = array("TicketId" => (int)497, "ProjectId" =>(int) 1);
               
                $query = Yii::$app->mongodb->getCollection('TicketTimeLogCollection');
                  error_log("+++++++++++++collectio+++++++++======");
//                $Arraytimelog = $query->aggregate(
//                    array('$match' => $matchArray
//                    ), array('$group' => array(
//                    '_id' => '$CollaboratorId',
//                    "sum" => array('$sum' => '$Time'),
//                ))
//            );
                
 $pipeline = array(
    array(
        '$group' => array(
            '_id' => '$CollaboratorId',
                    "sum" => array('$sum' => '$Time'),
        ),
    ),
    array('$match' => $matchArray),
   
);
                
                
                $options = array("allowDiskUse"=>true,"explain" => true);
            $Arraytimelog = $query->aggregate($pipeline,$options);      
                
                
            error_log("quert************************************");
                            error_log("+++++++++++++++++++++++timelogaray+++++=".print_r($Arraytimelog,1)) ;

        } catch (Exception $ex) {
            Yii::log("TicketTimeLogCollection:updateParentTicketTask::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
    
    
    
    
}
?>

