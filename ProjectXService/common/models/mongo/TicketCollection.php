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
use yii\base\ErrorException;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
//use yii\db\ActiveRecord;
use yii\mongodb\ActiveRecord;
use yii\mongodb\Query;
use yii\data\ActiveDataProvider;
use yii\web\IdentityInterface;
use common\components\CommonUtility;
use common\models\mysql\Bucket;
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
            "PlainDescription",
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
            
        } catch (\Throwable $ex) {
            Yii::error("TicketCollection:saveTicketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
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
      } catch (\Throwable $ex) {
            Yii::error("TicketCollection:getTicketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
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
      } catch (\Throwable $ex) {
            Yii::error("TicketCollection:getMyAssignedTickets::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
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
      } catch (\Throwable $ex) {
            Yii::error("TicketCollection:updateTicketField::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
        
     
    }

     
    /**
     * @author Praveen P
     * @return type
     */
        public static function getAllTicketDetails($StoryData, $projectId, $select = []) {
        try {
            $conditions = array("ProjectId" => (int)$projectId,"IsChild" => (int)0);
            if($StoryData->filterOption !=null || $StoryData->filterOption != 0){
                if($StoryData->filterOption->type=='general'){
                switch((int)$StoryData->filterOption->id){
               case 7:
                   $bucket=Bucket::getActiveBucketId($projectId);
                   if($bucket!='failure'){
                    $conditions['Fields.bucket.value']=(int)$bucket['Id'];   
                   }
                  break;
              case 8:$conditions['Fields.state.value']=(int)6;break; //all closed 
               case 9:
                   //  unset($conditions['Fields.duedate.value']);
                 $conditions["IsChild"] = array('$in' => array(0,1));
                 $yesterday = date("Y-m-d H:i:s", strtotime('yesterday'));
                 $conditions['Fields.duedate.value'] = array('$lte' => new \MongoDB\BSON\UTCDateTime(strtotime($yesterday) * 1000));break;                 //  $conditions['Fields.duedate.value']=(int)$StoryData->userInfo->Id;break;
                case 10:
//                      unset($conditions['Fields.duedate.value']);
                  $lastDayOfweek = date("Y-m-d H:i:s", strtotime('next sunday', strtotime('tomorrow')));
                  $todayDate = date("Y-m-d H:i:s");
                  $conditions['Fields.duedate.value'] = array('$gte' => new \MongoDB\BSON\UTCDateTime(strtotime($todayDate) * 1000), '$lte' => new \MongoDB\BSON\UTCDateTime(strtotime($lastDayOfweek) * 1000));
                  break;
               default:$conditions = array("ProjectId" => (int)$projectId,"IsChild" => (int)0);
            }      
           } else if($StoryData->filterOption->type=='individual'){
             
                switch((int)$StoryData->filterOption->id){
                     case 2:
                   $conditions["IsChild"] = array('$in' => array(0,1));
                   $conditions['$or']=[['Fields.assignedto.value'=>(int)$StoryData->userInfo->Id],['Followers.FollowerId'=>(int)$StoryData->userInfo->Id]];break;
               case 3:
                   $conditions["IsChild"] = array('$in' => array(0,1));
                   $conditions['Fields.assignedto.value']=(int)$StoryData->userInfo->Id;break;
               case 4:
                   $conditions["IsChild"] = array('$in' => array(0,1));
                   $conditions['$or']=[['Fields.assignedto.value'=>(int)$StoryData->userInfo->Id],['Followers.FollowerId'=>(int)$StoryData->userInfo->Id]];
                   $conditions['Fields.state.value']=(int)3;break; // in progress
               case 5:
                   $conditions["IsChild"] = array('$in' => array(0,1));
                   $conditions['$or']=[['Fields.assignedto.value'=>(int)$StoryData->userInfo->Id],['Followers.FollowerId'=>(int)$StoryData->userInfo->Id ]];
                   $conditions['Fields.state.value']=(int)6;break; // my closed
               case 6:
                   $conditions["IsChild"] = array('$in' => array(0,1));
                   $conditions['Followers.FollowerId']=(int)$StoryData->userInfo->Id;break;
             // case 8:$conditions['Fields.state.value']=(int)6;break; //all closed 
              case 11:
                   $conditions["IsChild"] = array('$in' => array(0,1));
                   $conditions['$or']=[['Fields.assignedto.value'=>(int)$StoryData->userInfo->Id],['Followers.FollowerId'=>(int)$StoryData->userInfo->Id ]];
                   $yesterday = date("Y-m-d H:i:s", strtotime('yesterday'));
                   $conditions['Fields.duedate.value'] = array('$lte' => new \MongoDB\BSON\UTCDateTime(strtotime($yesterday) * 1000)); break;
                case 12:
//                  $lastDayOfweek = date("Y-m-d H:i:s", strtotime('next sunday', strtotime('tomorrow')));
//                  $todayDate = date("Y-m-d H:i:s");
                    $conditions["IsChild"] = array('$in' => array(0,1));
                    $conditions['$or']=[['Fields.assignedto.value'=>(int)$StoryData->userInfo->Id],['Followers.FollowerId'=>(int)$StoryData->userInfo->Id ]];
                    $monday = strtotime("last monday");
                    $monday = date('w', $monday)==date('w') ? $monday+7*86400 : $monday;
                    $sunday = strtotime(date("Y-m-d",$monday)." +6 days");
                    $todayDate = date("Y-m-d",$monday);
                    $lastDayOfweek = date("Y-m-d",$sunday);
                    $conditions['Fields.duedate.value'] = array('$gte' => new \MongoDB\BSON\UTCDateTime(strtotime($todayDate) * 1000), '$lte' => new \MongoDB\BSON\UTCDateTime(strtotime($lastDayOfweek) * 1000));
                  break;
               default:$conditions = array("ProjectId" => (int)$projectId,"IsChild" => (int)0);
            }      
           }
           else if($StoryData->filterOption->type=='bucket'){
            $conditions['Fields.bucket.value']=(int)$StoryData->filterOption->id;
           }
           else if($StoryData->filterOption->type=='state'){
            $conditions['Fields.state.value']=(int)$StoryData->filterOption->id;
           }
        }
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
           
           $cursor = $collection->find($conditions, array(), $options);
            $ticketDetails = iterator_to_array($cursor);
            return $ticketDetails;
        } catch (\Throwable $ex) {
            Yii::error("TicketCollection:getAllTicketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

    /**
     * @author Praveen P
     * getting total count.
     * @return type  $projectId
     */
    public static function getAllStoriesCount($StoryData,$projectId) {
        try {
            $query = new Query();
            $conditions = array("ProjectId" => (int)$projectId,"IsChild" => (int)0);
           if($StoryData->filterOption !=null || $StoryData->filterOption != 0){
                if($StoryData->filterOption->type=='general'){
                  switch((int)$StoryData->filterOption->id){
               case 2:$conditions["IsChild"] = array('$in' => array(0,1));
                   $conditions['$or']=[['Fields.assignedto.value'=>(int)$StoryData->userInfo->Id],['Followers.FollowerId'=>(int)$StoryData->userInfo->Id]];break;
               case 3:
                   $conditions["IsChild"] = array('$in' => array(0,1));
                   $conditions['Fields.assignedto.value']=(int)$StoryData->userInfo->Id;break;
               case 4:
                   $conditions["IsChild"] = array('$in' => array(0,1));
                   $conditions['$or']=[['Fields.assignedto.value'=>(int)$StoryData->userInfo->Id],['Followers.FollowerId'=>(int)$StoryData->userInfo->Id]];
                      $conditions['Fields.state.value']=(int)3;break;
               case 5:$conditions["IsChild"] = array('$in' => array(0,1));
                   $conditions['$or']=[['Fields.assignedto.value'=>(int)$StoryData->userInfo->Id],['Followers.FollowerId'=>(int)$StoryData->userInfo->Id ]];
                       $conditions['Fields.state.value']=(int)6;break;
               case 6:$conditions["IsChild"] = array('$in' => array(0,1));
                   $conditions['Followers.FollowerId']=(int)$StoryData->userInfo->Id;break;
               case 7:
                   $bucket=Bucket::getActiveBucketId($projectId);
                   
                   if($bucket!='failure'){
                    $conditions['Fields.bucket.value']=(int)$bucket['Id'];   
                   }
                   break;
               case 8:$conditions['Fields.state.value']=(int)6;break;    
               default:$conditions = array("ProjectId" => (int)$projectId,"IsChild" => (int)0);
            }   
           }else if($StoryData->filterOption->type=='bucket'){
            $conditions['Fields.bucket.value']=(int)$StoryData->filterOption->id;
           }
        }
            $query->from('TicketCollection')
                    ->where($conditions);
            $totalCount = $query->count();
            return $totalCount;
        } catch (\Throwable $ex) {
            Yii::error("TicketCollection:getAllStoriesCount::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
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
                     ->where(["ProjectId" => (int)$projectId ,"TicketId" => (int)$storyId]);
            $taskDetails = $query->one();
            return $taskDetails;
        } catch (\Throwable $ex) {
            Yii::error("TicketCollection:getSubTaskIds::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
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
                  ->where(["ProjectId" => (int)$projectId ,"TicketId" => $subTaskIds]);
        $ticketDetails = $query->all();
        return $ticketDetails;  
        } catch (\Throwable $ex) {
            Yii::error("TicketCollection:getSubTaskDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
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
      } catch (\Throwable $ex) {
            Yii::error("TicketCollection:getMyTicketsCount::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
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
        } catch (\Throwable $ex) {
            Yii::error("TicketCollection:getMyTickets::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
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
        } catch (\Throwable $ex) {
            Yii::error("TicketCollection:updateParentTicketTaskField::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
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
            
       } catch (\Throwable $ex) {
            Yii::error("TicketCollection:updateChildTaskObject::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
      

    }
     /**
     * @author Padmaja 
     * @return array
     * @updated by suryaprakash reddy
     */
    public static function getAllTicketDetailsForSearch($projectId,$ticketId,$sortvalue, $searchString,$subTaskArray) {
        try {
            $collection = Yii::$app->mongodb->getCollection('TicketCollection');
            $cursor =  $collection->find(array('$or'=>array( array( "TicketIdString"=>array('$regex'=>$searchString),"ProjectId" => (int)$projectId,"TicketId"=>array('$nin'=>$subTaskArray)),array("Title"=>array('$regex'=>$searchString),"ProjectId" => (int)$projectId,"TicketId"=>array('$nin'=>$subTaskArray)))));
            $ticketDetails = iterator_to_array($cursor);
            return $ticketDetails;
        } catch (\Throwable $ex) {
            Yii::error("TicketCollection:getAllTicketDetailsForSearch::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
      /**
     * @author suryaprakash reddy 
     * @description This method is used to update TotalTime log in collection
     * @return type mongoId
     */
      public static function updateTotalTimeLog($projectId, $ticketId, $totalWorkHours) {
        try {

            //if ($totalWorkHours > 0) {
                $ticketCollection = Yii::$app->mongodb->getCollection('TicketCollection');
                $updateTotalTimeLog = array('$inc' => array("TotalTimeLog" =>(float)$totalWorkHours));
                $ticketCollection->update(array("TicketId" => (int) $ticketId, "ProjectId" => (int) $projectId), $updateTotalTimeLog);
            //}
        } catch (\Throwable $ex) {
            Yii::error("TicketCollection:updateTotalTimeLog::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
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
            $collection->update(array("TicketId" => (int) $ticketId, "ProjectId" => (int)$projectId), $newdata);
        } catch (\Throwable $ex) {
            Yii::error("TicketCollection:updateRelateTicket::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
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
        } catch (\Throwable $ex) {
            Yii::error("TicketCollection:getAllRelateStory::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
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
            $collection->update(array("TicketId" => (int) $parentTicketId, "ProjectId" => (int)$projectId), $newdata);
        } catch (\Throwable $ex) {
            Yii::error("TicketCollection:unRelateTask::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
        /**
     * @author Padmaja
     * @description This method is used to updated the Total Estimated points
     * @return type array
     */
       public static function updateTotalEstimatedPoints($projectId,$parentticketId, $totalEstmatedPts) {
        try {
            error_log("update here".$totalEstmatedPts."ticketId".$parentticketId."prokjeccc".$projectId);
            $ticketCollection = Yii::$app->mongodb->getCollection('TicketCollection');
            $updateTotalEstimatedPts = array('$inc' => array("Fields.totalestimatepoints.value" => $totalEstmatedPts));
            $ticketCollection->update(array("TicketId" => (int) $parentticketId, "ProjectId" => (int) $projectId), $updateTotalEstimatedPts);
          
        }catch (\Throwable $ex) {
            Yii::error("TicketCollection:updateTotalEstimatedPoints::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

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
      } catch (\Throwable $ex) {
            Yii::error("TicketCollection:getTicketDetailsByUser::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        } 
    }
           /**
     * @author Padmaja
     * @description This method is used to get Ticket details for time log
     * @return type array
     */
    public static function getAllStoryDetailsForTimelog($projectId,$sortvalue, $searchString){
        try{
            $collection = Yii::$app->mongodb->getCollection('TicketCollection');
            $cursor =  $collection->find(array('$or'=>array( array( "TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId))));
            $ticketDetails = iterator_to_array($cursor);
            return $ticketDetails;
        } catch (\Throwable $ex) {
            Yii::error("TicketCollection:getAllStoryDetailsForTimelog::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
               /**
     * @author Padmaja
     * @description This method is used to get Ticket details for dashboard
     * @return type array
     */
    public static function getTicketDetailsForDashboard($userId){
        try{
            $collection = Yii::$app->mongodb->getCollection('TicketCollection');
            $assignedtoDetails =  $collection->count(array('$or'=>array( array( "Fields.assignedto.value"=>(int)$userId))));
            $followersDetails =  $collection->count(array('$or'=>array(array("Followers.FollowerId"=>(int)$userId))));
            return array('AssignedToData'=>$assignedtoDetails,'FollowersDetails'=>$followersDetails);  
        } catch (\Throwable $ex) {
            Yii::error("TicketCollection:getTicketDetailsForDashboard::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
         
    }
        /**
     * @author Padmaja
     * @param type $userId
     * @param type $projectId
     * @return type
     */
    public static function getClosedTicketsCount($projectId,$userId,$FieldName,$value,$selectFields=[]){
      try{
            $query = new Query();
            if(count($selectFields)>0){
                $query->select($selectFields) ;
            }
            
            $query->from('TicketCollection')
            ->where(['Fields.assignedto.value' => (int)$userId, "ProjectId" =>(int)$projectId,$FieldName=>(int)$value ]);
         
           $ticketDetails = $query->count();
           return $ticketDetails;  
      } catch (\Throwable $ex) {
            Yii::error("TicketCollection:getClosedTicketsCount::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        } 
    }
           /**
     * @author Padmaja
     * @param type $userId
     * @param type $projectId
     * @return type
     */
    public static function getActiveOrClosedTicketsCount($projectId,$userId,$FieldName,$value,$selectFields=[]){
      try{
            $query = new Query();
            if(count($selectFields)>0){
                $query->select($selectFields);
            }
             $query->from('TicketCollection')
            ->where(['Fields.assignedto.value' => (int)$userId, "ProjectId" =>(int)$projectId])
            ->orWhere(['Followers.FollowerId'=>(int)$userId, "ProjectId" =>(int)$projectId])
            ->andWhere([$FieldName=>(int)$value, "ProjectId" =>(int)$projectId]);
            $ticketDetails = $query->count();
           return $ticketDetails;  
      } catch (\Throwable $ex) {
            Yii::error("TicketCollection:getActiveOrClosedTicketsCount::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }


    public static function getAllTicketsCount($projectId,$value,$FieldBucket,$FieldState,$taskFlag){
     try{
            $query = new Query();
            if($taskFlag=='All')
                $condition = [$FieldBucket=>(int)$value, "ProjectId" =>(int)$projectId];
            if($taskFlag=='Closed')
                $condition = [$FieldBucket=>(int)$value, "ProjectId" =>(int)$projectId, $FieldState=>array('$in' => array(6,7))];
            if($taskFlag=='Open')
                $condition = [$FieldBucket=>(int)$value, "ProjectId" =>(int)$projectId, $FieldState=>array('$in' => array(1,2,3,4,5))];
            
            $query->from('TicketCollection')
                    ->where($condition);
           //->where(['Fields.assignedto.value' => (int)$userId, "ProjectId" =>(int)$projectId])
           //->orWhere(['Followers.FollowerId'=>(int)$userId, "ProjectId" =>(int)$projectId])
           //->andWhere([$FieldName=>(int)$value, "ProjectId" =>(int)$projectId]);
           $ticketDetails = $query->count();
          return $ticketDetails;  
     }catch (\Throwable $ex) {
            Yii::error("TicketCollection:getAllTicketsCount::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
 
   }
   
   
   public static function getTotalWorkHoursForBucket($projectId,$value,$FieldBucket){
    try{
    $query = Yii::$app->mongodb->getCollection('TicketCollection');
    $pipeline = array(
    array( '$match' => array('Fields.bucket.value' => (int)$value, "ProjectId" => (int) $projectId,'IsChild'=>(int)0)),
    array( '$group' => array(
            '_id' => null,
            'totalHours' => array( '$sum' => '$TotalTimeLog' ),
            ),
        ),
    );
    $result = $query->aggregate($pipeline);
    return $result[0]['totalHours'];    
           
     } catch (\Throwable $ex) {
            Yii::error("TicketCollection:getTotalWorkHoursForBucket::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
   }
   
    public static function checkTicketsinBuckets($projectId,$bucketId){
        try{
            $query = new Query();
            // compose the query
            $query->select(['TicketId'])
                ->from('TicketCollection')
                    ->where(['Fields.bucket.value'=>(int)$bucketId ,'ProjectId'=>(int)$projectId])
                ->limit(1);
            $models = $query->all();
           
//             $collection = Yii::$app->mongodb->getCollection('TicketCollection');
//         $cursor =  $collection->find(array('$or'=>array( array( "Fields.bucket.value"=>(int)$bucketId ,"ProjectId"=>(int)$projectId))));
//         error_log("count------------------".$cursor); 
//         $mergedChatUsers = iterator_to_array($cursor);
           
           return $models;  
      } catch (\Throwable $ex) {
            Yii::error("TicketCollection:checkTicketsinBuckets::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
        
     
    }
               /**
     * @author Padmaja
     * @param type $FieldName
     * @param type $projectId
     * @return type
     */
    public static function getTicketsCountByStatus($projectId,$FieldName,$value){
        try{
             if($value!="New"){
                  error_log("666666666--------------");
             $where=   [$FieldName => (int)$value,"ProjectId" =>(int)$projectId];
            }else{
                error_log("new333333333--------------");
                $where=   [$FieldName =>$value,"ProjectId" =>(int)$projectId]; 
            }
             $query = new Query();
             $query->from('TicketCollection')
             ->where($where);
              $ticketDetails = $query->count();
           return $ticketDetails;  
                    
        } catch (\Throwable $ex) {
            Yii::error("TicketCollection:getTicketsCountByStatus::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
        
    }
               /**
     * @author Padmaja
     * @param type $FieldName
     * @param type $projectId
     * @return type
     */
    public static function getAllTicketsCountByProject($projectId){
        try{
            $where=   ["ProjectId" =>(int)$projectId];
            $query = new Query();
             $query->from('TicketCollection')
             ->where($where);
            return  $ticketDetails = $query->count();
        }  catch (\Throwable $ex) {
            Yii::error("TicketCollection:getAllTicketsCountByProject::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
       
        
    }

}  
?>
