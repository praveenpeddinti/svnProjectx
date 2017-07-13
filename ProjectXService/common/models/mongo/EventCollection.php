<?php
namespace common\models\mongo;
use Yii;
use yii\mongodb\ActiveRecord;
use yii\mongodb\Query;


class EventCollection extends ActiveRecord 
{
    public static function collectionName()
    {
        return 'EventCollection';
    }
    
    public function attributes() {
//        parent::attributes();
        return [
      "_id",      
     "ProjectId",
     "OccuredIn",
     "ReferringId",
     "DisplayAction",
     "ActionType",
     "CreatedOn",
     "ActionBy",
     "Miscellaneous",
     "ChangeSummary",
     "ShowInActivity",
     "Status"
    
   
        ];
    }
    
    
       public function behaviors()
    {
            return [
//            'timestamp' => [
//                'class' => '\yii\behaviors\TimestampBehavior',
//                'attributes' => [
//                    ActiveRecord::EVENT_BEFORE_INSERT => ['CreatedOn', 'UpdatedOn'],
//                    ActiveRecord::EVENT_BEFORE_UPDATE => ['UpdatedOn'],
//                ],
//                 'value' => function() { return new \MongoDB\BSON\UTCDateTime(time() * 1000); // unix timestamp 
//                 },
//            ],
        ];
    }
    
    
    
    public static function saveEvent($eventBean){
        //error_log("EVENT__BEAN___".print_r($eventBean,1));
        try {
            $returnValue = "failure";
            $eventObj = new EventCollection();
            $eventObj->CreatedOn = new \MongoDB\BSON\UTCDateTime(time() * 1000);
            $eventObj->ProjectId=$eventBean->ProjectId;
            $eventObj->OccuredIn=$eventBean->OccuredIn;
            $eventObj->ReferringId=$eventBean->ReferringId;
            $eventObj->DisplayAction=$eventBean->DisplayAction;
            $eventObj->ActionType=$eventBean->ActionType;
            $eventObj->ActionBy=$eventBean->ActionBy;
            $eventObj->ChangeSummary=$eventBean->ChangeSummary;
            $eventObj->ShowInActivity=$eventBean->ShowInActivity;
            $eventObj->Status=$eventBean->Status;
            $eventObj->Miscellaneous=$eventBean->Miscellaneous;
            $result = $eventObj->save();
            if($result){
               $returnValue = $eventObj->_id;
            }
            return $returnValue;
              
        } catch (Exception $ex) {
          Yii::log("EventCollection:saveEvent::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
  
        }
         
    }
}



?>
