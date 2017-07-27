<?php
namespace common\models\mongo;
use Yii;
use yii\mongodb\ActiveRecord;
use yii\mongodb\Query;
use yii\base\ErrorException;


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
            throw new ErrorException('Something went wrong');
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
    public function getAllActivities($postData){
         try{
             
             $userId = $postData->userInfo->Id;
            $page=$postData->page;
            $projectFlag=!empty($postData->projectFlag)?$postData->projectFlag:"";
            $pageLength=!empty($postData->limit)?$postData->limit:"";
            $projectId=!empty($postData->ProjectId)?$postData->ProjectId:"";
            $activityDropdownFlag=!empty($postData->activityDropdownFlag)?$postData->activityDropdownFlag:"";
            $activityPage=$postData->activityPage;
            
            $where = ['Status'=>(int)1];//,'ProjectId'=>(int)$projectId,'OccuredIn'=>$occuredIn,'ReferringId'=>(int)$referringId];
            $matchArray = ['Status'=>(int)1];
            $attributes = EventCollection::attributes();
            foreach ($attributes as $attribute){
                if(property_exists($postData, $attribute)){
                    if($postData->$attribute != null && $postData->$attribute != ""){
                        $where[$attribute] = ($attribute == "ProjectId"||$attribute == "ReferringId")?(int)$postData->$attribute:$postData->$attribute;
                        $matchArray[$attribute] = ($attribute == "ProjectId"||$attribute == "ReferringId")?(int)$postData->$attribute:$postData->$attribute;
                    }
                }
            }
             error_log("******************".$page);
             $pL = 3 ;
             $days = $page * $pL; 
             if($page == 1)
             $today = date("Y-m-d",strtotime('1 days')); //less than this
             else{
                $days1 = ($page -1)* $pL; 
                $today = date("Y-m-d",strtotime('-'.($days1).' days'));
             }//less than this
             
             
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
            $queryD = new Query();
            $queryD->from('EventCollection')->select(['CreatedOn'])->orderBy('CreatedOn DESC')
            ->where($where)->distinct('CreatedOn');
//          error_log("+++++query++=====sdzxf=".print_r($query,1));
           $dates = $queryD->all();
           foreach($dates as $key=>$extract){
               $mil = (string)$extract['CreatedOn'];
                        $seconds = $mil / 1000;
             $dates[$key]['date']=  date('m/d/Y',$seconds);
           }
           $dates = array_values(array_unique(array_column($dates, 'date')));
           $strt = $page*$pL;
           $end = $strt + $pL;
           if(count($dates) != 0 && count($dates) <= $pL && $page < 1){
               $ltD = $dates[0];
               $gtD = end($dates);
           }else if($end < count($dates)){
               $ltD = $dates[$strt];
               $gtD = $dates[$end];
           }else if($strt < count($dates)){
               $ltD = $dates[$strt];
               $gtD = end($dates);
           }else{
               $ltD = "";
               $gtD = "";
           }
             error_log("+++++++++++========sdzxf=".print_r($dates,1));
//             error_log("+++++++++ltd++========sdzxf=".($ltD));
//             error_log("++++++++++gtd+========sdzxf=".$gtD);
             if($page == 0){
                 $ltD = date('m/d/Y',strtotime("+1 day", strtotime($ltD)));
             }
             
             //
//                      $matchArray = array('Status'=>(int)1,'CreatedOn' => array('$lt'=>new \MongoDB\BSON\UTCDateTime(strtotime($ltD)*1000),'$gt'=>new \MongoDB\BSON\UTCDateTime(strtotime($gtD)*1000)),'ProjectId'=>(int)$projectId,'OccuredIn'=>$occuredIn);
                      $matchArray['CreatedOn']= array('$lt'=>new \MongoDB\BSON\UTCDateTime(strtotime($ltD)*1000),'$gt'=>new \MongoDB\BSON\UTCDateTime(strtotime($gtD)*1000));//,'ProjectId'=>(int)$projectId,'OccuredIn'=>$occuredIn);
//                      if($projectId == ""){
//                         unset($matchArray['ProjectId'] );
//                      }
//                      switch(count($occuredIn) ){
//                        case 0: unset($matchArray['OccuredIn'] );
//                                unset($matchArray['ReferringId'] );
//                            break;
//                        case 1:
//                            if($occuredIn[0] == 'Bucket' && $referringId !=""){
//                               unset($matchArray['OccuredIn'] );
//                               unset($matchArray['ReferringId'] );
//                               $matchArray['Miscellaneous.BucketId'] =$referringId;
//                            }
//                            break;
//                        default:
//                            unset($matchArray['ReferringId'] );
//                            break;
//
//                       }
//                       error_log("+++++++++++asdsdsafdsfdfdfsd========sdzxf=".print_r($matchArray,1));
                    $query = Yii::$app->mongodb->getCollection('EventCollection');
                    $pipeline = array(
                         array('$match' => $matchArray),
//                         array('$lt' => array('CreatedOn'=>new \MongoDB\BSON\UTCDateTime(strtotime($lessThan)*1000))),
//                         array('$gt' => array('CreatedOn'=>new \MongoDB\BSON\UTCDateTime(strtotime($greaterThan)*1000))),
                         array('$sort' => array('CreatedOn'=>-1)),
//                        array('$limit' => $limit),array('$skip' =>$offset),
                         array(
                            '$group' => array( 
                                '_id' =>  array('CreatedOn'=>'$CreatedOn'),
                             //  "commentData" => array('$push' => array('$ProjectId')),
                             "Data" => array('$push' => '$_id'),
                             ),
                             
                        ),
                        );
                         
                $eventCollectionData = $query->aggregate($pipeline);
             
             
                return $eventCollectionData ;
                
       } catch (\Throwable $ex) {
            Yii::error("EventCollection:getAllActivities::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException('Something went wrong');
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
     public function getActivitiesById($getId){
        try{
            $query = new Query();
            $query->from('EventCollection')
            ->where(['_id' =>$getId, 'Status'=>(int)1]);
         
           $ticketDetails = $query->one();
           return $ticketDetails;  
        } catch (\Throwable $ex) {
            Yii::error("EventCollection:getActivitiesById::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException('Something went wrong');
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
            throw new ErrorException('Something went wrong');
        }
        
    }
}



?>
