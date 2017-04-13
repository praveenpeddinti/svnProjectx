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
 "_id" ,      
     "CollaboratorId",
     "UserName",
     "ProfilePicture",
     "Email",
    "OrganizationId",
    'CreatedOn',
    'UpdatedOn',
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
            ->where(['CollaboratorId' => (int)$collaboratorId ]);
        // execute the query
        $userDetails = $query->one();
        $userDetails["ProfilePicture"] = Yii::$app->params['ServerURL'].$userDetails["ProfilePicture"];
       return $userDetails;
     
    }
      public static function createUsers($data){
        
        try {
//        $collection = Yii::$app->mongodb->getCollection('TinyUserCollection');
//        $collection->batchInsert($data);
        foreach ($data as $value) {
        $userObj= new TicketCollection();
         $userObj->Title="hi";
        $userObj->CollaboratorId=(int)$value['Id'];
        $userObj->UserName=$value['UserName'];
        $userObj->ProfilePicture='';
        $userObj->Email=$value['Email'];
        $userObj->OrganizationId=  (int)$value['OrganizationId'];
        $userObj->insert();  
        unset($userObj);
        }
        } catch (Exception $exc) {
           error_log("error occured in model" . $exc->getMessage());
        }
    }
    
    public static function getProfileOfFollower($follower) {
        try {
            $query = new Query();
            $query->from('TinyUserCollection')
                    ->where(['CollaboratorId' => (int) $follower]);
            $profile_pic = $query->one();
            return $profile_pic["ProfilePicture"];
        } catch (Exception $ex) {
            error_log("Error in Profile");
        }
    }

}
?>
