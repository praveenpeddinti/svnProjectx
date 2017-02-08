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
            error_log("TicketCollection--##-saveTicketDetails----------------");
            $collection = Yii::$app->mongodb->getCollection('TicketCollection');
           
            $ticket_data->insert();
          // $collection->insert($ticket_data);
            
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
            ->where(['TicketId' => $ticketId, "ProjectId" => $projectId ]);
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
    public static function getAllTicketDetails(){
      try{
            $query = new Query();
           //$query->select(['name', 'status'])
            $query->from('TicketCollection');
        $ticketDetails = $query->all();
        return $ticketDetails;  
      } catch (Exception $ex) {
      Yii::log("TicketCollection:getAllTicketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');

      }  
    }
}
?>
