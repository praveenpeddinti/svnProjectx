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
            $cursor = $collection->find(array(), array(), $options);
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
    public static function getTotalStorys($projectId) {
        try {
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
     * @param type $ticket_data
     */
    public static function updateStoryField($ticket_data){ 
        try{
            $returnValue = 'failure';
            $collection = Yii::$app->mongodb->getCollection('TicketCollection');
            $checkData = $ticket_data->isLeftColumn;
            $field_name = $ticket_data->EditedId;
            $field_id = $ticket_data->id;
            $fieldDetails =  \common\models\mysql\StoryFields::getFieldDetails($field_id);
            $workflowModel = new \common\models\mysql\WorkFlowFields();
            $priorityModel = new \common\models\mysql\Priority();
            $bucketModel = new \common\models\mysql\Bucket();
            $planlevelModel = new \common\models\mysql\PlanLevel();
            $tickettypeModel = new \common\models\mysql\TicketType();
            $valueName = "";
            if($checkData==0){
                 if($ticket_data->id=='Title'){
                    $newData = array('$set' => array("Title" => $ticket_data->value));
                    $condition=array("TicketId" => (int)$ticket_data->TicketId,"ProjectId"=>(int)$ticket_data->projectId);
                    $selectedValue=$ticket_data->value;
                }else if($ticket_data->id=='Description'){
                    $actualdescription = CommonUtility::refineDescription($ticket_data->value);
                    $newData = array('$set' => array("Description" => $actualdescription,"CrudeDescription" =>$ticket_data->value ));
                    $condition=array("TicketId" => (int)$ticket_data->TicketId,"ProjectId"=>(int)$ticket_data->projectId);
                    $selectedValue=$actualdescription;
                }
            }else{
                error_log("ticket updat-----------");
                     if(is_numeric($ticket_data->value)){
                          error_log("ticket updat----in-------".$ticket_data->value);
                           error_log("field details----------".$fieldDetails["Type"]."-------------".$fieldDetails["Field_Name"]);
                         if($fieldDetails["Type"] == 6 ){
                            $collaboratorData = \common\models\mysql\Collaborators::getCollboratorByFieldType("Id",$ticket_data->value);
                            $valueName = $collaboratorData["UserName"]; 
                        }
                        
                             else if($fieldDetails["Field_Name"] == "workflow"){
                                $workFlowDetail =  $workflowModel->getWorkFlowDetails($ticket_data->value);
                                $valueName = $workFlowDetail["Name"];
                                }
                                else if($fieldDetails["Field_Name"] == "priority"){
                                $priorityDetail =  $priorityModel->getPriorityDetails($ticket_data->value);
                                $valueName = $priorityDetail["Name"];
                                }
                                else if($fieldDetails["Field_Name"] == "bucket"){
                                $bucketDetail =  $bucketModel->getBucketName($ticket_data->value,(int)$ticket_data->projectId);
                                $valueName = $bucketDetail["Name"];
                                }
                                else if($fieldDetails["Field_Name"] == "planlevel"){
                                $planlevelDetail =  $planlevelModel->getPlanLevelDetails($ticket_data->value);
                                $valueName = $planlevelDetail["Name"];
                                }
                                else if($fieldDetails["Field_Name"] == "tickettype"){
                                $tickettypeDetail =  $tickettypeModel->getTicketType($ticket_data->value);
                                $valueName = $tickettypeDetail["Name"];
                                } 
                        
                       
                         $leftsideFieldVal = (int)$ticket_data->value;  
                    }else{
                        if($ticket_data->value != ""){
                            $validDate = CommonUtility::validateDate($ticket_data->value);
                            if($validDate){
                                $leftsideFieldVal = new \MongoDB\BSON\UTCDateTime(strtotime($validDate) * 1000); 
                            }else{
                                $leftsideFieldVal = $ticket_data->value; 
                            } 
                        }else{
                            error_log("elseeeeeeeeeeeeeeee");
                            $leftsideFieldVal = $ticket_data->value;
                        }
                    }
                    $fieldtochange1= "Fields.".$field_name.".value";
                    $fieldtochange2 = "Fields.".$field_name.".value_name";
                    $fieldtochangeId = "Fields.".$field_name.".Id";
      //$abc = "Fields.assignedto.value";
     //  $xyz = "Fields.assignedto.value_name";
                    $newData = array('$set' => array($fieldtochange1 => $leftsideFieldVal,$fieldtochange2 =>$valueName));
                    $condition=array("TicketId" => (int)$ticket_data->TicketId,"ProjectId"=>(int)$ticket_data->projectId,$fieldtochangeId=>(int)$ticket_data->id);
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
