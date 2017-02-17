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
            $collection = Yii::$app->mongodb->getCollection('TicketCollection');
            $ticket_data->insert();
            
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
         $cursor =  $collection->find( array( "Fields" => array('$elemMatch'=> array( "value"=> 1,  "Id"=> 5 ))));
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
    public static function getAllTicketDetails($StoryData,$projectId){
      try{
            $query = new Query();
            $query->from('TicketCollection')
                    ->where(["ProjectId" => $projectId])
                    ->limit($StoryData->pagesize)
                    ->offset(($StoryData->offset*$StoryData->pagesize))
                    ->orderBy(['TicketId' => SORT_DESC]);
        $ticketDetails = $query->all();
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
    public static function getTotalStorys($projectId){
      try{
            $query = new Query();
            $query->from('TicketCollection')
                     ->where(["ProjectId" => $projectId]);
            $totalCount = $query->count();
            return $totalCount;
        } catch (Exception $ex) {
      Yii::log("TicketCollection:getAllTicketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');

      }  
    }
        /*
     * @author Padmaja
     * @param type $fieldData
     */
    public static function updateStoryField($fieldData){ 
        try{
            $returnValue = 'failure';
            $collection = Yii::$app->mongodb->getCollection('TicketCollection');
            $checkData=$fieldData->isLeftColumn;
            if($checkData==0){
                 if($fieldData->id=='Title'){
                    $newData = array('$set' => array("Title" => $fieldData->value));
                    $condition=array("TicketId" => (int)$fieldData->TicketId,"ProjectId"=>(int)$fieldData->projectId);
                    $selectedValue=$fieldData->value;
                }else if($fieldData->id=='Description'){
                    $actualdescription = CommonUtility::refineDescription($fieldData->value);
                    $newData = array('$set' => array("Description" => $actualdescription,"CrudeDescription" =>$fieldData->value ));
                    $condition=array("TicketId" => (int)$fieldData->TicketId,"ProjectId"=>(int)$fieldData->projectId);
                    $selectedValue=$actualdescription;
                }
            }else{
                    if(is_numeric($fieldData->value)){
                         $leftsideFieldVal = (int)$fieldData->value;  
                    }else{
                        if($fieldData->value != ""){
                            if(CommonUtility::validateDate($fieldData->value)){
                                $leftsideFieldVal = new \MongoDB\BSON\UTCDateTime(strtotime($fieldData->value) * 1000); 
                            }else{
                                $leftsideFieldVal = $fieldData->value; 
                            } 
                        }else{
                            $leftsideFieldVal = $fieldData->value;
                        }
                    }

                    $newData = array('$set' => array("Fields.$.value" => $leftsideFieldVal));
                    $condition=array("TicketId" => (int)$fieldData->TicketId,"ProjectId"=>(int)$fieldData->projectId,"Fields.Id"=>(int)$fieldData->id);
                    //$selectedValue=CommonUtility::getEditableTextByFieldId($condition, $leftsideFieldVal);
                    $selectedValue=$leftsideFieldVal;

            }
            $updateStaus = $collection->update($condition, $newData); 
            if($updateStaus==0){
                $returnValue=$selectedValue;
            }
            return $returnValue;

        } catch (Exception $ex) {
            Yii::log("TicketCollection:updateStoryField::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
}
?>
