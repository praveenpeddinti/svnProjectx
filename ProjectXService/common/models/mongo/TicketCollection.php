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
            "ParentStoryId",
            "ProjectId",
            "RelatedStories",
            "Tasks",
            "TicketId",
            "TicketIdString",
            "TotalEstimate",
            "TotalTimeLog",
            "IsChild",
            "WorkflowType"
            
           
          
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
    public static function getAllStoriesCount($projectId) {
        try {
            $query = new Query();
            $query->from('TicketCollection')
                    ->where(["ProjectId" => $projectId,"IsChild" => (int)0]);
            $totalCount = $query->count();
            return $totalCount;
        } catch (Exception $ex) {
            Yii::log("TicketCollection:getAllStoriesCount::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
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
            $taskDetails = $query->one();
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
     * @param type $$childTicketIds
     * @return type
     * @use used for inserting childtickets into Tasks(array) of parentticket.
     */
    
    public static function updateParentTicketTaskField($projectId,$parentTicNumber, $childTicketObjArray) {
        try {
            $collection = Yii::$app->mongodb->getCollection('TicketCollection');
            $tasksNew = array('$addToSet' => array("Tasks" => array('$each' => $childTicketObjArray)));
            $collection->update(array("TicketId" => (int)$parentTicNumber,"ProjectId"=>(int)$projectId), $tasksNew);
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
    public static function updateChildTaskObject($parentTicketId,$projectId,$newTasksList){
      try{
          $collection = Yii::$app->mongodb->getCollection('TicketCollection');
          $newdata = array('$set' => array('Tasks' =>$newTasksList));
          $collection->update(array("TicketId" => (int)$parentTicketId,"ProjectId"=>(int)$projectId), $newdata);
            
       } catch (Exception $ex) {
      Yii::log("TicketCollection:updateChildTaskObject::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');

      }  
      

    }
     /**
     * @author Padmaja 
     * @return array
     * @updated by suryaprakash reddy
     */
    public static function getAllTicketDetailsForSearch($projectId,$ticketId,$sortvalue, $searchString,$ticketArray) {
        try {
            $collection = Yii::$app->mongodb->getCollection('TicketCollection');
            $cursor =  $collection->find(array('$or'=>array( array( "TicketIdString"=>array('$regex'=>$searchString),"ProjectId" => (int)$projectId,"TicketId"=>array('$nin'=>$ticketArray)),array("Title"=>array('$regex'=>$searchString),"ProjectId" => (int)$projectId,"TicketId"=>array('$nin'=>$ticketArray)))));
            $ticketDetails = iterator_to_array($cursor);            
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

            if ($totalWorkHours > 0) {
                $ticketCollection = Yii::$app->mongodb->getCollection('TicketCollection');
                $updateTotalTimeLog = array('$inc' => array("TotalTimeLog" => $totalWorkHours));
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
    public static function updateRelateTicket($projectId, $ticketId, $searchTicketId) {
        try {
            $collection = Yii::$app->mongodb->getCollection('TicketCollection');
            $newdata = array('$addToSet' => array('RelatedStories' => (int) $searchTicketId));
            $collection->update(array("TicketId" => (int) $ticketId, "ProjectId" => $projectId), $newdata);
        } catch (Exception $ex) {
            Yii::log("TicketCollection:updateChiledTaskObject::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

    /**
     * @author suryaprakash reddy 
     * @description This method is used to get allrelated stories
     * @return type array
     */
    public static function getAllRelateStory($projectId, $ticketId, $ticketArray) {
        try {
            $query = new Query();
            $query->from('TicketCollection')
                    ->where(["TicketId" => array('$in' => $ticketArray), "ProjectId" => (int) $projectId]);
            $ticketDetails = $query->all();
            return $ticketDetails;
        } catch (Exception $ex) {
            Yii::log("TicketCollection:getAllRelateStory::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

    /**
     * @author suryaprakash reddy 
     * @description This method is used to unrelate task from parent ticket
     * @return type array
     */
    public static function unRelateTask($projectId, $parentTicketId, $unRelateTicketId) {
        try {
            $collection = Yii::$app->mongodb->getCollection('TicketCollection');
            $newdata = array('$pull' => array('RelatedStories' => (int) $unRelateTicketId));
            $collection->update(array("TicketId" => (int) $parentTicketId, "ProjectId" => $projectId), $newdata);
        } catch (Exception $ex) {
            Yii::log("TicketCollection:unRelateTask::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
        /**
     * @author Padmaja
     * @description This method is used to updated the Total Estimated points
     * @return type array
     */
       public static function updateTotalEstimatedPoints($projectId,$parentticketId, $totalEstmatedPts) {
        try {
            $ticketCollection = Yii::$app->mongodb->getCollection('TicketCollection');
            $updateTotalEstimatedPts = array('$inc' => array("TotalEstimate" => $totalEstmatedPts));
            $ticketCollection->update(array("TicketId" => (int) $parentticketId, "ProjectId" => (int) $projectId), $updateTotalEstimatedPts);
          
        } catch (Exception $ex) {
            Yii::log("TicketCollection:updateTotalEstimatedPoints::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
//    
//      public function getAllDetailsForSearch($searchString){
//           try {
//                $collection = Yii::$app->mongodb->getCollection('TicketCollection');
//                $cursor =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString),"ProjectId" => (int)1),array("Description"=>array('$regex'=>$searchString),"ProjectId" => (int)1),array("TicketId"=>array('$regex'=>$searchString),"ProjectId" => (int)1))));
//                $searchDetails = iterator_to_array($cursor);
//                $collection = Yii::$app->mongodb->getCollection('TicketCollection');
//                $cursor =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString),"ProjectId" => (int)1),array("Description"=>array('$regex'=>$searchString),"ProjectId" => (int)1),array("TicketId"=>array('$regex'=>$searchString),"ProjectId" => (int)1))));
//                $searchDetails = iterator_to_array($cursor);
//                return $searchDetails;
//        } catch (Exception $ex) {
//            Yii::log("TicketCollection:getAllTicketDetailsForSearch::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
//        }
//    }
          /**
     * @author Padmaja
     * @description This method is used to get Ticket details by User
     * @return type array
     */
     public static function getTicketDetailsByUser($collabaratorId,$projectId,$selectFields=[]){
      try{
            $query = new Query();
            if(count($selectFields)>0){
                $query->select($selectFields) ;
            }
            $query->from('TicketCollection')
            ->where(['Fields.assignedto.value' => (int)$collabaratorId, "ProjectId" =>(int) $projectId ]);
            $ticketDetails = $query->all();
            return $ticketDetails;  
      } catch (Exception $ex) {
      Yii::log("TicketCollection:getTicketDetailsByUser::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');

      }  
    }
   

    }  
?>
