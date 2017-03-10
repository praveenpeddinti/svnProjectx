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
                    $query = new Query();
            $query->from('TicketComments')
            ->where(['TicketId' => (int)$ticketId, "ProjectId" =>(int)$ticketId ]);
         
           $ticketCommentDetails = $query->one();
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
}
?>