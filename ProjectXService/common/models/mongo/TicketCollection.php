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
            "TotalEstimate",
            "TotalTimeLog"
            
           
          
          
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
            ->where(['TicketId' => (int)$ticketId, "ProjectId" => $projectId ]);
         
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
            $cursor = $collection->find(array("ProjectId" => (int)$projectId), array(), $options);
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
    

}
?>
