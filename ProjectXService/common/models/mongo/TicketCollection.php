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
            
 "TicketId",
"ProjectId",
"CreatedOn",
"UpdateOn",
"ReportedBy",
"PlanLevel",
"Title",
"Description",
"Status",
"AssignedTo",
"Priority",
"Bucket",
"GivenEstimate",
"TotalEstimate",
"DueDate",
"TicketType",
"TaskId",
"ParentStoryId",
"RelatedStoriesId",
"Followers",
"CommentsId",
"ArtifactsId",
"TotalTimeLog"


        ];
    }
    
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }
    
    /**
     * @author Moin Hussain
     * @param type $ticketId
     * @param type $projectId
     * @return type
     */
    public static function getTicketDetails($ticketId,$projectId){
      try{
            $query = new Query();
           //$query->select(['name', 'status'])
            $query->from('TicketCollection')
            ->where(['TicketId' => $ticketId, "ProjectId" => $projectId ]);
           $ticketDetails = $query->one();
           return $ticketDetails;  
      } catch (Exception $ex) {
      Yii::log("TicketCollection:getTicketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');

      }  
        
     
    }
}
?>
