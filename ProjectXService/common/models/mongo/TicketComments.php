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
//use yii\db\ActiveRecord;
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
//        parent::attributes();
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
//            TimestampBehavior::className(),
        ];
    }
    
    public static function createCommentsRecord($ticketNumber,$projectId){
        try{
            $tktCommentsColl = new TicketComments();
            $tktCommentsColl->TicketId = $ticketNumber;
            $tktCommentsColl->ProjectId = $projectId;
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
           
           error_log("========createCommentsRecord==========".print_r($ticketCommentDetails,1));
//           TicketCollection::updateRefFields("CommentsRef", $ticketCommentDetails,$ticketNumber,$projectId);
            }
            
        }catch(Exception $ex){
            
        }
    }
    
    public static function getTicketComments($ticketId,$projectId){
        error_log("======getTicketComments=========".$ticketId."-------------".$projectId);
                    $query = new Query();
            $query->from('TicketComments')
         ->select(array("Activities"))
            ->where(['TicketId' => (int)$ticketId, "ProjectId" =>(int)$projectId ]);
         
           $ticketCommentDetails = $query->one();
//           error_log("#############".print_r($ticketCommentDetails,1));
           return $ticketCommentDetails;
    }
    
    public static function saveComment($ticketNumber,$projectId,$newCommentArray=array()){
        try{
            if(!empty($newCommentArray)){
//            $query = new Query();
//            $query->from('TicketComments')
//            ->where(['TicketId' => (int)$ticketNumber, "ProjectId" =>(int)$projectId ]);
//         
//           $ticketCommentDetails = $query->one();
//            error_log("+++++++ticketcommentcoll+++++++++".print_r($ticketCommentDetails,1));
//            array_push($ticketCommentDetails["Activities"],$newCommentArray);
            
            $collection = Yii::$app->mongodb->getCollection('TicketComments');
            $newdata = array('$addToSet' => array('Activities' => $newCommentArray));
            $res = $collection->findAndModify(array("TicketId" => (int)$ticketNumber,"ProjectId"=>(int)$projectId), $newdata,array('new' => 1,"upsert"=>1)); 
            if($newCommentArray["Status"] == 2){
                $newdata = array('$inc' => array('Activities.'.$newCommentArray["ParentIndex"].'.repliesCount' =>1));
            $res = $collection->update(array("TicketId" => (int)$ticketNumber,"ProjectId"=>(int)$projectId), $newdata,array('new' => 1,"upsert"=>1)); 
            }
//            error_log("+++++sdad+++++++".$res);
//            $tktCommentsColl = new TicketComments();
//            $tktCommentsColl->TicketId = $ticketNumber;
//            $tktCommentsColl->ProjectId = $projectId;
//            $tktCommentsColl->Comments = [];
//            $tktCommentsColl->insert();
            }
        }catch(Exception $ex){
            
        }
    }
    
    /**
     * @author Moin Hussain
     * @param type $ticketId
     * @param type $projectId
     * @return type
     */
       public static function getTicketActivity($ticketId, $projectId){
        try{
         error_log("getTicketActivity-----".$ticketId."---".$projectId);
         $query = new Query();
            $query->from('TicketComments')
                    ->select(array("Activities"))
                     ->where(["ProjectId" => (int)$projectId ,"TicketId" => (int)$ticketId]);
          $ticketActivity = $query->one();
            return $ticketActivity;
        } catch (Exception $ex) {
Yii::log("TicketComments:getTicketActivity::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
    public static function removeComment($commentData){
        $collection = Yii::$app->mongodb->getCollection('TicketComments');
//},,
        error_log("slug----".$commentData->Comment->Slug);
//        if(isset($commentData->Comment->ParentIndex)){
//          $newdata = array('$pull' =>array("Activities"=> array("Slug"=>new \MongoDB\BSON\ObjectID($commentData->Comment->Slug))),'$inc'=>array('Activities.'.$commentData->Comment->ParentIndex.'repliesCount'=>-1));
//        }else{
          $newdata = array('$pull' =>array("Activities"=> array("Slug"=>new \MongoDB\BSON\ObjectID($commentData->Comment->Slug))));
//        }
          
        $res = $collection->update(array("TicketId" => (int)$commentData->TicketId,"ProjectId"=>(int)$commentData->projectId), $newdata);
        if(isset($commentData->Comment->ParentIndex)){
          $newdata = array('$inc'=>array('Activities.'.$commentData->Comment->ParentIndex.'.repliesCount'=>-1));
          $res = $collection->update(array("TicketId" => (int)$commentData->TicketId,"ProjectId"=>(int)$commentData->projectId), $newdata);
              
        }
          error_log("**************".$res);
          return $res;
    }
}
?>