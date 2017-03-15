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
use common\components\CommonUtility;

class TicketCollection extends ActiveRecord 
{
    public static function collectionName()
    {
        return 'TicketCollection';
    }
    
    public function attributes() {
//        parent::attributes();
        return [
            "_id",
            "Title",
            "Description",
            "CrudeDescription",
            "Fields",
            "CreatedOn",
            "UpdatedOn",
            "ArtifactsRef",
            "CommentsRef",
            "FollowersRef",
            "ParentStoryId",
            "ProjectId",
            "RelatedStories",
            "Tasks",
            "TicketId",
            "TicketIdString",
            "TotalEstimate",
            "TotalTimeLog",
            "IsChild"
           
          
          
        ];
    }
    
      public function behaviors()
    {
            return [
            'timestamp' => [
                'class' => '\yii\behaviors\TimestampBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['CreatedOn', 'UpdatedOn'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['UpdatedOn'],
                ],
                 'value' => function() { return new \MongoDB\BSON\UTCDateTime(time() * 1000); // unix timestamp 
                 },
            ],
        ];
    }
    
    public static function saveTicketDetails($ticket_data) {
        try {
            $returnValue = "failure";
            $collection = Yii::$app->mongodb->getCollection('TicketCollection');
            $result = $ticket_data->save();
            if($result){
               $returnValue = $ticket_data->_id;
            }
            return $returnValue;
            
        } catch (Exception $ex) {
                error_log($ex->getMessage());
            Yii::log("TicketCollection:saveTicketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
       
      }
    
    /**
     * @author Moin Hussain
     * @param type $ticketId
     * @param type $projectId
     * @return type
     */
    public static function getTicketDetails($ticketId,$projectId,$selectFields=[]){
      try{
            $query = new Query();
            if(count($selectFields)>0){
                $query->select($selectFields) ;
            }
            
            $query->from('TicketCollection')
            ->where(['TicketId' => (int)$ticketId, "ProjectId" =>(int) $projectId ]);
         
           $ticketDetails = $query->one();
           return $ticketDetails;  
      } catch (Exception $ex) {
      Yii::log("TicketCollection:getTicketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');

      }  
    }

    
     /**
     * @author Moin Hussain
     * @param type $ticketId
     * @param type $projectId
     * @return type
     */
    public static function getMyAssignedTickets($selectFields=[]){
      try{
           
           
             $collection = Yii::$app->mongodb->getCollection('TicketCollection');
         $cursor =  $collection->find(array('$or'=>array( array( "Fields.assignedto.Id"=>5 ,"Fields.assignedto.value"=>11),array("FollowersRef"=>array('$in'=>array(6))))));
         //error_log("count------------------".$cursor); 
         $mergedChatUsers = iterator_to_array($cursor);
//         foreach ($cursor as $doc) {
//            print_r($doc);
//}
           
           return $mergedChatUsers;  
      } catch (Exception $ex) {
      Yii::log("TicketCollection:getTicketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');

      }  
        
     
    }
    
     /**
     * @author Moin Hussain
     * @param type $ticketId
     * @param type $projectId
     * @return type
     */
    public static function updateTicketField(){
      try{
           
          $collection = Yii::$app->mongodb->getCollection('TicketCollection');
//}
          $newdata = array('$set' => array("Fields.$.value" => (int)2));
          $collection->update(array("TicketId" => 1,"Fields.Id"=>(int)5), $newdata); 
         
         
           return "";  
      } catch (Exception $ex) {
      Yii::log("TicketCollection:getTicketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');

      }  
        
     
    }

     
    /**
     * @author Praveen P
     * @return type
     */
        public static function getAllTicketDetails($StoryData, $projectId, $select = []) {
        try {
            if ($StoryData->sortorder == 'desc')
                $order = -1;
            if ($StoryData->sortorder == 'asc')
                $order = 1;
            if ($StoryData->sortvalue == 'Id')
                $sortData = "TicketId";
            else if ($StoryData->sortvalue == 'Title')
                $sortData = "Title";
            else
                $sortData = "Fields." . $StoryData->sortvalue . ".value_name";
            $options = array(
                "sort" => array($sortData => $order),
                "limit" => $StoryData->pagesize,
                "skip" => $StoryData->offset * $StoryData->pagesize
            );
            $options=array();
            $collection = Yii::$app->mongodb->getCollection('TicketCollection');
           $cursor = $collection->find(array("ProjectId" => (int)$projectId,"IsChild" => (int)0), array(), $options);
            $ticketDetails = iterator_to_array($cursor);
            return $ticketDetails;
        } catch (Exception $ex) {
            Yii::log("TicketCollection:getAllTicketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

    /**
     * @author Praveen P
     * getting total count.
     * @return type  $projectId
     */
    public static function getTotalTicketsCount($projectId) {
        try {
            $query = new Query();
            $query->from('TicketCollection')
                    ->where(["ProjectId" => $projectId]);
            $totalCount = $query->count();
            return $totalCount;
        } catch (Exception $ex) {
            Yii::log("TicketCollection:getTotalTicketsCount::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

     /**
     * @author Praveen P
     * This method is used to getting subtask Ids by passing particular story Id.
     * @return type  $projectId $storyId
     */
    public static function getSubTaskIds($storyId,$projectId) {
        try {
            $query = new Query();
            $query->from('TicketCollection')
                     ->where(["ProjectId" => $projectId ,"TicketId" => $storyId]);
            $taskDetails = $query->all();
            return $taskDetails;
        } catch (Exception $ex) {
            Yii::log("TicketCollection:getTotalTicketsCount::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    /**
     * @author Praveen P
     * @description This method is used to getting subtask details for the particular story.
     * @return typesubtasks
     */
    public static function getSubTaskDetails($subTaskIds, $projectId, $select = []) {
        try {
            $query = new Query();
            $query->from('TicketCollection')
                  ->where(["ProjectId" => $projectId ,"TicketId" => $subTaskIds]);
        $ticketDetails = $query->all();
        return $ticketDetails;  
        } catch (Exception $ex) {
            Yii::log("TicketCollection:getAllTicketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    /**
     * @author Moin Hussain
     * @param type $userId
     * @param type $projectId
     * @param type $selectFields
     * @return type
     */
       public static function getMyTicketsCount($userId,$projectId,$selectFields=[]){
      try{
           
           $collection = Yii::$app->mongodb->getCollection('TicketCollection');
           $cursor =  $collection->count(array('$or'=>array( array( "Fields.assignedto.value"=>(int)$userId,"ProjectId"=>(int)$projectId),array("Followers.FollowerId"=>array('$in'=>array((int)$userId)),"ProjectId"=>(int)$projectId))));
           return $cursor;  
      } catch (Exception $ex) {
      Yii::log("TicketCollection:getMyTicketsCount::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');

      }  
        
     
    }
    /**
     * @author Moin Hussain
     * @param type $userId
     * @param type $sortorder
     * @param type $sortvalue
     * @param type $offset
     * @param type $pageLength
     * @param type $projectId
     * @param type $select
     * @return type
     */
       public static function getMyTickets($userId,$sortorder,$sortvalue,$offset,$pageLength,$projectId, $select = []) {
        try {
           $skip = $offset * $pageLength;
            if ($sortorder == 'desc')
                $order = -1;
            if ($sortorder == 'asc')
                $order = 1;
             if ($sortvalue == 'Id')
                $sortData = "TicketId";
             else if ($sortvalue == 'Title')
                $sortData = "Title";
            else
                $sortData = "Fields." . $sortvalue . ".value_name";
            
            $options = array(
                "sort" => array($sortData => $order),
                "limit" => $pageLength,
                "skip" => $skip
            );
            $collection = Yii::$app->mongodb->getCollection('TicketCollection');
            $cursor = $collection->find(array('$or'=>array( array( "Fields.assignedto.value"=>(int)$userId,"ProjectId"=>(int)$projectId),array("Followers.FollowerId"=>array('$in'=>array((int)$userId)),"ProjectId"=>(int)$projectId))), array(), $options);
            $ticketDetails = iterator_to_array($cursor);
            return $ticketDetails;
        } catch (Exception $ex) {
            Yii::log("TicketCollection:getMyTickets::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
    /**
     * @author Suryaprakash reddy
     * @param type $parentTicNumber
     * @param type $ticketnoArray
     * @return type
     * @use used for inserting childtickets into Tasks(array) of parentticket.
     */
    
    public static function updateParentTicketTaskField($parentTicNumber, $ticketnoArray) {
        try {
            $collection = Yii::$app->mongodb->getCollection('TicketCollection');
            $tasksNew = array('$set' => array("Tasks" => $ticketnoArray));
            $collection->update(array("TicketId" => $parentTicNumber), $tasksNew);
        } catch (Exception $ex) {
            Yii::log("TicketCollection:updateParentTicketTask::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
           /**
     * @author Padmaja
     * @param type $parentTicketId
     * @param type $newTasksList
     * @return type
     */
    public static function updateChiledTaskObject($parentTicketId,$newTasksList){
      try{
          $collection = Yii::$app->mongodb->getCollection('TicketCollection');
          $newdata = array('$set' => array('Tasks' =>$newTasksList));
          $collection->update(array("TicketId" => (int)$parentTicketId), $newdata);
            
       } catch (Exception $ex) {
      Yii::log("TicketCollection:updateChiledTaskObject::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
            
    }
    
  
    }
     /**
     * @author Padmaja 
     * @return type
     */
    public static function getAllTicketDetailsForSearch($StoryData, $projectId, $selectFields = []) {
        try {
            $options=array($StoryData->sortvalue);
            $query = new Query();
            $query->from('TicketCollection')
                    ->where(["ProjectId" => (int)1]);
             $query->andWhere(['like','TicketIdString', '618']);
              $query->orWhere(['like','Title', '618']);
             error_log(print_r($query,1));
            $ticketDetails = $query->all();
            
            return $ticketDetails;
        } catch (Exception $ex) {
            Yii::log("TicketCollection:getAllTicketDetailsForSearch::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
      /**
     * @author suryaprakash reddy 
     * @description This method is used to update TotalTime log in collection
     * @return type mongoId
     */
      public function updateTotalTimeLog($projectId, $ticketId, $totalWorkHours) {
        try {
            $query = new Query();
            $query->from('TicketCollection')
                    ->where(['TicketId' => (int) $ticketId, "ProjectId" => $projectId]);
            $ticketDetails = $query->one();
            $totalTimeLog = (float) ($ticketDetails["TotalTimeLog"] + $totalWorkHours);
            if ($totalTimeLog > 0) {
                $ticketCollection = Yii::$app->mongodb->getCollection('TicketCollection');
                $updateTotalTimeLog = array('$set' => array("TotalTimeLog" => $totalTimeLog));
                $ticketCollection->update(array("TicketId" => (int) $ticketId, "ProjectId" => (int) $projectId), $updateTotalTimeLog);
            }
        } catch (Exception $ex) {
            Yii::log("TicketCollection:updateParentTicketTask::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
  /**
     * @author suryaprakash reddy 
     * @description This method is used to get TicketId,TotalTimeLog,Tasks,ParentStoryId from ticket collection
     * @return type array
     */
    public function getTimeLog($projectId, $ticketId) {
        try {
            $query = new Query();
            $query->select(array("TicketId", "TotalTimeLog", "Tasks", "ParentStoryId"));
            $query->from('TicketCollection')
                    ->where(['TicketId' => (int) $ticketId, "ProjectId" => $projectId]);
            $ticketDetails = $query->one();

            return $ticketDetails;
        } catch (Exception $ex) {
            Yii::log("TicketCollection:getTimeLog::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

}
?>
