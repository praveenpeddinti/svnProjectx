<?php
namespace common\models\mongo;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\base\ErrorException;
use yii\mongodb\ActiveRecord;
use yii\mongodb\Query;
use yii\data\ActiveDataProvider;
use yii\web\IdentityInterface;
use common\models\mongo\TicketCollection;

class TicketComments extends ActiveRecord 
{
    public static function collectionName()
    {
        return 'TicketComments';
    }
    
    public function attributes() {
        return [
      "_id",      
     "TicketId",
     "ProjectId",
     "Activities",
     "RecentActivityUser",
     "RecentActivitySlug"
    
   
        ];
    }
    
    public function behaviors()
    {
        return [
        ];
    }
    /**
     * 
     * @param type $ticketNumber
     * @param type $projectId
     * @throws ErrorException
     * @Description Creates an Empty Comments Record on creating a ticket.
     */
    public static function createCommentsRecord($ticketNumber,$projectId){
        try{
            $tktCommentsColl = new TicketComments();
            $tktCommentsColl->TicketId = (int)$ticketNumber;
            $tktCommentsColl->ProjectId = (int)$projectId;
            $tktCommentsColl->Activities = [];
            $tktCommentsColl->RecentActivityUser = "";
            $tktCommentsColl->RecentActivitySlug = "";
            
            $res = $tktCommentsColl->insert();
            if($res){
                $query = new Query();
            $query->from('TicketComments')
            ->select(array("_id"))
            ->where(['TicketId' => (int)$ticketNumber, "ProjectId" =>(int)$projectId ]);
           $ticketCommentDetails = $query->one();
            }
            
        }catch (\Throwable $ex) {
            Yii::error("TicketCommentsCollection:createCommentsRecord::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    /**
     * 
     * @param type $ticketId
     * @param type $projectId
     * @return type
     * @throws ErrorException
     * @Description Retruns the Comments under a given ticket.
     */
    public static function getTicketComments($ticketId,$projectId){
         try{
          $query = new Query();
          $query->from('TicketComments')
         ->select(array("Activities"))
            ->where(['TicketId' => (int)$ticketId, "ProjectId" =>(int)$projectId ]);
         
           $ticketCommentDetails = $query->one();
           return $ticketCommentDetails;
         }catch (\Throwable $ex) {
            Yii::error("TicketCommentsCollection:getTicketComments::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    /**
     * 
     * @param type $ticketNumber
     * @param type $projectId
     * @param type $newCommentArray
     * @throws ErrorException
     * @Description Saves the Comments made on a ticket.
     */
    public static function saveComment($ticketNumber,$projectId,$newCommentArray=array()){
        try{
            if(!empty($newCommentArray)){
            $collection = Yii::$app->mongodb->getCollection('TicketComments');
            $newdata = array('$addToSet' => array('Activities' => $newCommentArray));
            $res = $collection->findAndModify(array("TicketId" => (int)$ticketNumber,"ProjectId"=>(int)$projectId), $newdata,array('new' => 1,"upsert"=>1)); 
            if($newCommentArray["Status"] == 2){
                $newdata = array('$inc' => array('Activities.'.$newCommentArray["ParentIndex"].'.repliesCount' =>1));
            $res = $collection->update(array("TicketId" => (int)$ticketNumber,"ProjectId"=>(int)$projectId), $newdata,array('new' => 1,"upsert"=>1)); 
            }

            }
        }catch (\Throwable $ex) {
            Yii::error("TicketCommentsCollection:saveComment::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
    /**
     * @author Moin Hussain
     * @param type $ticketId
     * @param type $projectId
     * @return type
     * @Description Gets Activity done on a ticket.
     */
       public static function getTicketActivity($ticketId, $projectId){
        try{
         $query = new Query();
            $query->from('TicketComments')
                    ->select(array("Activities"))
                     ->where(["ProjectId" => (int)$projectId ,"TicketId" => (int)$ticketId]);
          $ticketActivity = $query->one();
            return $ticketActivity;
        } catch (\Throwable $ex) {
            Yii::error("TicketCommentsCollection:getTicketActivity::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    /**
     * 
     * @param type $commentData
     * @return type
     * @throws ErrorException
     * @Description Deletes a Comment.
     */
    public static function removeComment($commentData){
        try{
        $collection = Yii::$app->mongodb->getCollection('TicketComments');
        $newdata =array('$set'=> array('Activities.$.Status' =>(int)0));
        $res = $collection->update(array("TicketId" => (int)$commentData->ticketId,"ProjectId"=>(int)$commentData->projectId,"Activities.Slug"=> new \MongoDB\BSON\ObjectID($commentData->Comment->Slug)), $newdata);
        if(isset($commentData->Comment->ParentIndex)){
          $newdata = array('$inc'=>array('Activities.'.$commentData->Comment->ParentIndex.'.repliesCount'=>-1));
          $res = $collection->update(array("TicketId" => (int)$commentData->ticketId,"ProjectId"=>(int)$commentData->projectId), $newdata);
              
        }
        return $res;
        } catch (\Throwable $ex) {
            Yii::error("TicketCommentsCollection:removeComment::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
       
    }
}
?>