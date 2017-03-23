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

class ProjectTicketSequence extends ActiveRecord 
{
    public static function collectionName()
    {
        return 'ProjectTicketSequence';
    }
    
    public function attributes() {
//        parent::attributes();
        return [
            
   "_id",
   "TicketNumber",
   "ProjectId",
    ];
    }
    
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }
     public static function getNextSequence($projectId) {
         $db =  ProjectTicketSequence::getCollection();
         $ret = $db->findAndModify( array("ProjectId"=> (int)$projectId ), array('$inc'=> array('seq' =>1)),array('new' => 1,"upsert"=>1));
         return $ret["seq"];
  
  }
 
}
?>
