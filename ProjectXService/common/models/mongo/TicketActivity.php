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

class TicketActivity extends ActiveRecord 
{
    public static function collectionName()
    {
        return 'TicketActivity';
    }
    
    public function attributes() {
//        parent::attributes();
        return [
      "_id",      
     "TicketId",
     "ProjectId",
     "Activity",
    'CreatedOn',
   
        ];
    }
    
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }
    
    public static function saveActivity(){
       try{
           error_log("saveActivity--");
             //error_log($projectId."---".$ticketId."---".$collaboratorId);
           $db =  TicketActivity::getCollection();
           $currentDate = new \MongoDB\BSON\UTCDateTime(time() * 1000);
           $db->findAndModify( array("ProjectId"=> (int)1 ,"TicketId"=> (int)1), array('$addToSet'=> array('Activity' =>array("Slug" => new \MongoDB\BSON\ObjectID(),"ActivityBy" => (int)1,"ActivityDate" => $currentDate ,"ActionField" =>"Assigned to","ActionMessage"=>"set to","ActionValue"=>"moin hussain" ))),array('new' => 1,"upsert"=>1));
        } catch (Exception $ex) {
 Yii::log("TicketActivity:saveActivity::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
       } 
    }
    
 
}
?>
