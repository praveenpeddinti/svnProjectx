<?php
namespace common\models\mongo;
use Yii;
use yii\mongodb\ActiveRecord;
use yii\mongodb\Query;
use yii\base\ErrorException;
use yii\data\ActiveDataProvider;

class EventCollection extends ActiveRecord 
{
    public static function collectionName()
    {
        return 'EventCollection';
    }
    
    public function attributes() {
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
        ];
    }
    
    /**
     * 
     * @param type $eventBean
     * @return type
     * @throws ErrorException
     * @Description Saves an activity into event collection.
     */
    
    public static function saveEvent($eventBean){
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
              
        } catch (\Throwable $ex) {
            Yii::error("EventCollection:saveEvent::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
         
    }
       /**
     * @author Padmaja
     * @Description This method is used to get Ticket details for dashboard
     * @return type $userId
     * @return type $page
     * @return type $pageLength
     * @return type $projectFlag
        * $occuredIn=['Bucket','Project','Ticket']
     */
    public static function getAllActivities($postData){
         try{
            $matchArray = ['Status'=>(int)1];
          
            $attributes = self::getEventAttribute();
            foreach ($attributes as $attribute){
                if(property_exists($postData->attributes, $attribute)){
                    if($postData->attributes->$attribute != null && $postData->attributes->$attribute != ""){
                        if($attribute == "Miscellaneous"){
                            $misAttr = $postData->attributes->$attribute;
                            $misCond = array();
                            foreach($misAttr as $key=>$value){
                                $misCond[$key] = ($key == "BucketId")?(int)$value:$value;
                            }
                            $matchArray[$attribute] =$misCond;
                            
                        }else{
                            
                        $matchArray[$attribute] = ($attribute == "ProjectId"||$attribute == "ReferringId")?(int)$postData->attributes->$attribute:$postData->attributes->$attribute;
                        }
                    }
                }
            }
            $query=new Query();
            $query->from('EventCollection')->where($matchArray)
            ->orderBy(["CreatedOn"=>SORT_DESC]);
            $provider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                'pageSize' => (int)$postData->pageLength,
                'page'=>(int)$postData->page
                ]
            ]);
           return  $eventCollectionData = $provider->getModels();
                
       } catch (\Throwable $ex) {
            Yii::error("EventCollection:getAllActivities::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
     /**
      * 
      * @return type
      * @throws ErrorException
      * @Description Gets the list of attributes in Event Collection.Used for building query in getAllActivities()
      */
    public static function getEventAttribute() {
        try{
             error_log("$$$$$$$$$-----------");
             $eventCollection = new EventCollection();
             return $collectionData=    $eventCollection->attributes();
        } catch (\Throwable $ex) {
            Yii::error("EventCollection:getEventAttribute::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
       
    }
    /**
     * @author Padmaja
     * @Description This method is used to get activities
     * @param type $projectId
     * @param type $page
  
     */
    public static function getAllActivitiesByProject($page,$projectId){
         try{
            $where = ['ProjectId'=>(int)$projectId];
             $query=new Query();
            $query->from('EventCollection')->where($where)
            ->orderBy(["_id"=>SORT_DESC]);
            $provider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                'pageSize' => 10,
                'page'=>(int)$page
                ]
            ]);
           return  $eventCollectionData = $provider->getModels();
          } catch (\Throwable $ex) {
            Yii::error("EventCollection:getAllActivitiesByProject::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
   
       /**
     * @author Padmaja
     * @Description This method is used to get Ticket details for dashboard
     * @return type $userId
     * @return type $page
     * @return type $pageLength
     * @return type $projectFlag
     */
     public static function getActivitiesById($getId){
        try{
            $query = new Query();
            $query->from('EventCollection')
            ->where(['_id' =>$getId, 'Status'=>(int)1]);
         
           $ticketDetails = $query->one();
           return $ticketDetails;  
        } catch (\Throwable $ex) {
            Yii::error("EventCollection:getActivitiesById::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
       /**
     * @author Padmaja
     * @Description This method is used to get Ticket details for dashboard
     * @return type $userId
     * @return type $page
     * @return type $pageLength
     * @return type $projectFlag
     */
        public function getActivitiesByProjectName($projectId,$page,$pageLength){
        try{
                if ($page == 1) {
                    $offset = $page - 1;
                    $limit = $pageLength;   
                } else {
                    $offset = ($page - 1) * $pageLength;
                    $limit = $offset + $pageLength;
                }

                error_log("------33333333333----".$limit."------".$offset);
                   $query = new Query();
                   $sort = ['CreatedOn' => -1];
                    $query->from('EventCollection')
                   ->orderBy($sort)
                  ->limit($limit) 
                  ->offset($offset)
                  ->where(['Status'=>(int)1,'ProjectId'=>(int)$projectId]);
        $ticketDetails = $query->all();
        return $ticketDetails; 

        } catch (\Throwable $ex) {
            Yii::error("EventCollection:getActivitiesByProjectName::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
        
    }
       /**
     * @author Padmaja
     * @Description This method is used to get current week users
     * @return type $userId
    */
    public static function getCurrentWeekActiveUsers($projectId){
        try{
            error_log("############---333-----");
              $weekFirstDay = date("Y-m-d H:i:s", strtotime('last monday', strtotime('tomorrow'))); 
              $toDate = date("Y-m-d H:i:s");
              $matchArray = array("ProjectId" => (int) $projectId,'CreatedOn'=>array('$gte' =>new \MongoDB\BSON\UTCDateTime(strtotime($weekFirstDay)*1000),'$lte' =>new \MongoDB\BSON\UTCDateTime(strtotime($toDate)*1000)));
                $pipeline = array(
                        array('$match' => $matchArray),
                        array(
                            '$group' => array(
                                '_id' => 'null',
                                 "data" => array('$push' => '$ActionBy'),
                             ),
                        ),
                    );
                  $query = Yii::$app->mongodb->getCollection('EventCollection');
                  $ArraycurrentUserId = $query->aggregate($pipeline);
                  return $ArraycurrentUserId;
        } catch (\Throwable $ex) {
            Yii::error("EventCollection:getCurrentWeekActiveUsers::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
    /**
     * @author Ryan
     * @Description This method is used to get current week active buckets
     * @return type $projectId
    */
    public static function getCurrentWeekActiveBuckets($projectId){
        try{
            $weekFirstDay = date("Y-m-d H:i:s", strtotime('last monday', strtotime('tomorrow'))); 
            $toDate = date("Y-m-d H:i:s");
            $matchArray = array("ProjectId" => (int) $projectId,'CreatedOn'=>array('$gte' =>new \MongoDB\BSON\UTCDateTime(strtotime($weekFirstDay)*1000),'$lte' =>new \MongoDB\BSON\UTCDateTime(strtotime($toDate)*1000)));
             $pipeline = array(
                        array('$match' => $matchArray),
                        array(
                            '$group' => array(
                                '_id' => '$Miscellaneous.BucketId',
                                "count" => array('$sum' => 1),
                             ),
                        ),
                    );
             $query = Yii::$app->mongodb->getCollection('EventCollection');
             $activityBucketCount=$query->aggregate($pipeline);
             return $activityBucketCount;
        }catch (\Throwable $ex) {
            Yii::error("EventCollection:getCurrentWeekActiveBuckets::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
    /**
     * @author Ryan
     * @Description This method is used to get other buckets (which are not current) and their count
     * @return type $projectId
    */
    public static function getOtherBucketsCount($projectId,$isData=0){
        try{
            $count=0;
            $weekFirstDay = date("Y-m-d H:i:s", strtotime('last monday', strtotime('tomorrow'))); 
            $matchArray = array("ProjectId" => (int) $projectId,'CreatedOn'=>array('$lt' =>new \MongoDB\BSON\UTCDateTime(strtotime($weekFirstDay)*1000)));
            $pipeline = array(
                        array('$match' => $matchArray),
                        array(
                            '$group' => array(
                                '_id' => '$Miscellaneous.BucketId',
                                "count" => array('$sum' => 1),
                             ),
                        ),
                    );
            $query = Yii::$app->mongodb->getCollection('EventCollection');
            $otherBuckets=$query->aggregate($pipeline);
            return $otherBuckets;
            
        } catch (\Throwable $ex) {
            Yii::error("EventCollection:getOtherBucketsCount::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
}


?>
