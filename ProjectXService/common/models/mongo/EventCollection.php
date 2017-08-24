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
              
        } catch (\Throwable $ex) {
            Yii::error("EventCollection:saveEvent::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
         
    }
       /**
     * @author Padmaja
     * @description This method is used to get Ticket details for dashboard
     * @return type $userId
     * @return type $page
     * @return type $pageLength
     * @return type $projectFlag
        * $occuredIn=['Bucket','Project','Ticket']
     */
//    public function getAllActivities($page,$pageLength,$projectId="",$occuredIn=[],$referringId=""){
    public static function getAllActivities($postData){
         try{
//            $userId = $postData->userInfo->Id;
//            $page=$postData->page;
//            $projectFlag=!empty($postData->projectFlag)?$postData->projectFlag:"";
//            $pageLength=!empty($postData->limit)?$postData->limit:"";
//            $projectId=!empty($postData->ProjectId)?$postData->ProjectId:"";
//            $activityDropdownFlag=!empty($postData->activityDropdownFlag)?$postData->activityDropdownFlag:"";
//            $activityPage=$postData->activityPage;
//            $where = ['Status'=>(int)1];//,'ProjectId'=>(int)$projectId,'OccuredIn'=>$occuredIn,'ReferringId'=>(int)$referringId];
            $matchArray = ['Status'=>(int)1];
          
            $attributes = self::getEventAttribute();
            error_log("$$$$$+++++++++++++++$$$$".print_r($postData,1));
            foreach ($attributes as $attribute){
                if(property_exists($postData->attributes, $attribute)){
                    if($postData->attributes->$attribute != null && $postData->attributes->$attribute != ""){
//                        $where[$attribute] = ($attribute == "ProjectId"||$attribute == "ReferringId")?(int)$postData->attributes->$attribute:$postData->attributes->$attribute;
                        $matchArray[$attribute] = ($attribute == "ProjectId"||$attribute == "ReferringId")?(int)$postData->attributes->$attribute:$postData->attributes->$attribute;
                    }
                }
            }
//             error_log("******************".$page);
//             $pL = 3 ;
//             $days = $page * $pL; 
//             if($page == 1)
//             $today = date("Y-m-d",strtotime('1 days')); //less than this
//             else{
//                $days1 = ($page -1)* $pL; 
//                $today = date("Y-m-d",strtotime('-'.($days1).' days'));
//             }//less than this
             
             
//             if($projectId == ""){
//                 unset($where['ProjectId'] );
//               }
//             switch(count($occuredIn) ){
//                case 0: unset($where['OccuredIn'] );
//                        unset($where['ReferringId'] );
//                    break;
//                case 1:
//                    if($occuredIn[0] == 'Bucket' && $referringId !=""){
//                        unset($where['OccuredIn'] );
//                        unset($where['ReferringId'] );
////                       $where['Miscellaneous'] =array("BucketId"=>$referringId);
//                       $where['Miscellaneous.BucketId'] =$referringId;
//                    }
//                    break;
//                default:
//                    unset($where['ReferringId'] );
//                    break;
//                
//               }
//               error_log("++++++aawewwqead++======sdzxf=".print_r($where,1));
//            $queryD = new Query();
//            $queryD->from('EventCollection')->select(['CreatedOn'])->orderBy('CreatedOn DESC')
//            ->where($where)->distinct('CreatedOn');
////          error_log("+++++query++=====sdzxf=".print_r($query,1));
//           $dates = $queryD->all();
//           foreach($dates as $key=>$extract){
//               $mil = (string)$extract['CreatedOn'];
//                        $seconds = $mil / 1000;
//             $dates[$key]['date']=  date('m/d/Y',$seconds);
//           }
//           $dates = array_values(array_unique(array_column($dates, 'date')));
//           $strt = $page*$pL;
//           $end = $strt + $pL;
//           if(count($dates) != 0 && count($dates) <= $pL && $page < 1){
//               $ltD = $dates[0];
//               $gtD = end($dates);
//           }else if($end < count($dates)){
//               $ltD = $dates[$strt];
//               $gtD = $dates[$end];
//           }else if($strt < count($dates)){
//               $ltD = $dates[$strt];
//               $gtD = end($dates);
//           }else{
//               $ltD = "";
//               $gtD = "";
//           }
//             error_log("+++++++++++========sdzxf=".print_r($dates,1));
////             error_log("+++++++++ltd++========sdzxf=".($ltD));
////             error_log("++++++++++gtd+========sdzxf=".$gtD);
//             if($page == 0){
//                 $ltD = date('m/d/Y',strtotime("+1 day", strtotime($ltD)));
//             }
             
             //
//                      $matchArray = array('Status'=>(int)1,'CreatedOn' => array('$lt'=>new \MongoDB\BSON\UTCDateTime(strtotime($ltD)*1000),'$gt'=>new \MongoDB\BSON\UTCDateTime(strtotime($gtD)*1000)),'ProjectId'=>(int)$projectId,'OccuredIn'=>$occuredIn);
//                      $matchArray['CreatedOn']= array('$lt'=>new \MongoDB\BSON\UTCDateTime(strtotime($ltD)*1000),'$gt'=>new \MongoDB\BSON\UTCDateTime(strtotime($gtD)*1000));//,'ProjectId'=>(int)$projectId,'OccuredIn'=>$occuredIn);
//              $where = ['ProjectId'=>(int)$projectId];
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
//                    $query = Yii::$app->mongodb->getCollection('EventCollection');
//                    $pipeline = array(
//                         array('$match' => $matchArray),
//                        array('$sort' => array('CreatedOn'=>-1)),
//                         array(
//                            '$group' => array( 
//                                '_id' =>  array('CreatedOn'=>'$CreatedOn'),
//                             //  "commentData" => array('$push' => array('$ProjectId')),
//                             "Data" => array('$push' => '$_id'),
//                             ),
//                             
//                        ),
//                        );
//                         
//                $eventCollectionData = $query->aggregate($pipeline);
//             
//             
//                return $eventCollectionData ;
                
       } catch (\Throwable $ex) {
            Yii::error("EventCollection:getAllActivities::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
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
     * @description This method is used to get activities
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
     * @description This method is used to get Ticket details for dashboard
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
     * @description This method is used to get Ticket details for dashboard
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
     * @description This method is used to get current week users
     * @return type $userId
    */
    public static function getCurrentWeekActiveUsers($projectId){
        try{
            error_log("############---333-----");
              $weekFirstDay = date("Y-m-d H:i:s", strtotime('last monday', strtotime('tomorrow'))); 
              $toDate = date("Y-m-d H:i:s");
              $matchArray = array("ProjectId" => (int) $projectId,'CreatedOn'=>array('$gte' =>new \MongoDB\BSON\UTCDateTime(strtotime($weekFirstDay)*1000),'$lte' =>new \MongoDB\BSON\UTCDateTime(strtotime($toDate)*1000)));
                $pipeline = array(
                       // array('$unwind' => '$TimeLog'),
                        array('$match' => $matchArray),
                        array(
                            '$group' => array(
                                '_id' => 'null',
                              // "count" => array('$sum' => 1),
                              //  "totalHours" => array('$sum' => '$TimeLog.Time')
                                 "data" => array('$push' => '$ActionBy'),
                             ),
                        ),
                    );
                  $query = Yii::$app->mongodb->getCollection('EventCollection');
                  $ArraycurrentUserId = $query->aggregate($pipeline);
                 // error_log("@@@@@444442342@@@@---".print_r(array_unique($Arraytimelog)));
                  return $ArraycurrentUserId;
        } catch (\Throwable $ex) {
            Yii::error("EventCollection:getCurrentWeekActiveUsers::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
    /**
     * @author Ryan
     * @description This method is used to get current week active buckets
     * @return type $projectId
    */
    public static function getCurrentWeekActiveBuckets($projectId){
        try{
            $weekFirstDay = date("Y-m-d H:i:s", strtotime('last monday', strtotime('tomorrow'))); 
            $toDate = date("Y-m-d H:i:s");
            $matchArray = array("ProjectId" => (int) $projectId,'CreatedOn'=>array('$gte' =>new \MongoDB\BSON\UTCDateTime(strtotime($weekFirstDay)*1000),'$lte' =>new \MongoDB\BSON\UTCDateTime(strtotime($toDate)*1000)));
             $pipeline = array(
                       // array('$unwind' => '$TimeLog'),
                        array('$match' => $matchArray),
                        array(
                            '$group' => array(
                                '_id' => '$Miscellaneous.BucketId',
                                "count" => array('$sum' => 1),
                              //  "totalHours" => array('$sum' => '$TimeLog.Time')
                                // "data" => array('$push' => '$ActionBy'),
                             ),
                        ),
                    );
             $query = Yii::$app->mongodb->getCollection('EventCollection');
             $activityBucketCount=$query->aggregate($pipeline);
             error_log("==Activity Bucket Count==".print_r($activityBucketCount,1));
             return $activityBucketCount;
        }catch (\Throwable $ex) {
            Yii::error("EventCollection:getCurrentWeekActiveBuckets::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
}



?>
