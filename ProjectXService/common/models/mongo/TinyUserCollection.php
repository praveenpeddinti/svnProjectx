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

class TinyUserCollection extends ActiveRecord 
{
    public static function collectionName()
    {
        return 'TinyUserCollection';
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
    
    
    public static function getMiniUserDetails($collaboratorId){
        
        $query = new Query();
        // compose the query
          $query->select(['CollaboratorId', 'UserName','ProfilePicture','Email'])
                ->from('TinyUserCollection')
            ->where(['CollaboratorId' => $collaboratorId ]);
        // execute the query
        $userDetails = $query->one();
        error_log("************************".print_r($userDetails,1));
       return $userDetails;
     
    }
}
?>
