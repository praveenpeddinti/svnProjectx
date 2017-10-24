<?php 
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace common\models\mysql;

use Yii;
use yii\base\NotSupportedException;
use yii\base\ErrorException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\web\IdentityInterface;

class UserPreferences extends ActiveRecord 
{
    
    public static function tableName()
    {
        return '{{%UserPreferences}}';
    }
    
    public function behaviors()
    {
        return [
        ];
    }
    
     /**
     * @author Ryan Marshal
     * @param type $userid
     * @param type $tasks
     * @return type
      * @Description Saves User Preferences
     */
    public static function savePreference($userid,$tasks)
    {
       
        $prefered_list=$prefered_items='';
       
        try
        {
             foreach($tasks as $preference_tasks)
             {
                  $prefered_list .= $prefered_items  . $preference_tasks->Id ;
                  $prefered_items = ',';
             }

//             $query = "select * from UserPreferences where CollaboratorId=$userid";
//             $data = Yii::$app->db->createCommand($query)->queryAll();
                $query= new Query();
                $data = $query->select("")
                      ->from("UserPreferences")
                      ->where("CollaboratorId=".$userid)
                      ->all();
             if(sizeof($data)>0) // if there is preference existing
             {
                 $query="update UserPreferences set PreferenceItems='$prefered_list' where CollaboratorId=$userid";
                 $data = Yii::$app->db->createCommand($query)->execute();
             }
             else // no preference yet
             {
                $preference=new UserPreferences();
                $preference->CollaboratorId=$userid;
                $preference->Action="Story Creation";
                $preference->PreferenceItems=$prefered_list;
                $preference->save();
             }
        } catch (\Throwable $ex) {
            Yii::error("UserPreferences:savePreference::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
         throw new ErrorException($ex->getMessage()); 
            
        }
    }
    
    /**
     * @author Ryan Marshal
     * @param type $userid
     * @return type
     * @Description Gets User Preferences
     */
    public static function getPreference($userid)
    {
        $userid=(int)$userid;
        try{
//            $query = "select PreferenceItems from UserPreferences where CollaboratorId=$userid";
//            $data = Yii::$app->db->createCommand($query)->queryOne();
            $query= new Query();
            $data = $query->select("PreferenceItems")
                          ->from("UserPreferences")
                          ->where("CollaboratorId=".$userid)
                          ->one();
            return $data;    
        } catch (\Throwable $ex) {
           
            Yii::error("UserPreferences:getPreference::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        throw new ErrorException($ex->getMessage()); 
            }
    }
}