<?php

namespace common\components;
use yii;
use common\models\mongo\EventCollection;
use common\models\bean\EventBean;
use yii\base\ErrorException;

trait EventTrait{
     /**
     * @Description This method is use to save events
     * @param type $eventObj
     * @author Anand & Ryan
     */
    
     public static function saveEvent($projectId,$occuredIn,$refId,$displayAction,$actionType,$actionBy,$summaryArray,$miscellaneousData=array(),$showInActivity=true,$status=1) {
        try {
            $evtobj=new EventBean(); 
            $evtobj->ProjectId=(int)$projectId;
            $evtobj->OccuredIn=$occuredIn;
            $evtobj->ReferringId=(int)$refId;
            $evtobj->DisplayAction=$displayAction;
            $evtobj->ActionType=$actionType;
            $evtobj->ActionBy=(int)$actionBy;
            $evtobj->ChangeSummary=$summaryArray;
            $evtobj->Miscellaneous=$miscellaneousData;
            $evtobj->ShowInActivity=$showInActivity;
            $evtobj->Status=$status;
            EventCollection::saveEvent($evtobj);
        } catch (\Throwable $ex) {
            Yii::error("EventTrait:saveEvent::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
       
      } 
  
}
