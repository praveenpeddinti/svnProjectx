<?php 
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace common\models\mysql;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class WorkFlowFields extends ActiveRecord 
{
    
    public static function tableName()
    {
        return '{{%WorkFlowFields}}';
    }
    
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }
    /**
     * @author Moin Hussain
     * @param type $workFlowId
     * @return type
     */
    public static function getWorkFlowDetails($workFlowId)
    {
        try{
        $query = "select Id,Name from WorkFlowFields where Id=".$workFlowId;
        $data = Yii::$app->db->createCommand($query)->queryOne();
        return $data;   
        } catch (Exception $ex) {
Yii::log("WorkFlowFields:getWorkFlowDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');

        }
       
//        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }
    
      public static function getStoryWorkFlowList(){
        try{
        $query = "select Id,Name,Status from WorkFlowFields";
        $data = Yii::$app->db->createCommand($query)->queryAll();
        return $data;   
        } catch (Exception $ex) {
Yii::log("WorkFlowFields:getStoryWorkFlowList::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');

        }
       
//        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }
    
}



?>