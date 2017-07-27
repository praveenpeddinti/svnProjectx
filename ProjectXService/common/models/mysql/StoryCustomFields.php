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
use yii\base\ErrorException;

class StoryCustomFields extends ActiveRecord 
{
    
    public static function tableName()
    {
        return '{{%StoryCustomFields}}';
    }
    
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }
    /**
     * @author Moin Hussain
     * @param type $bucketId
     * @param type $projectId
     * @return type
     */
    public static function getFieldDetails($fieldId)
    {
        try{
        $query = "select * from StoryCustomFields sf join FieldTypes ft on sf.Type=ft.Id  where sf.Id=".$fieldId;
        error_log($query);
        $data = Yii::$app->db->createCommand($query)->queryOne();
        return $data;  
        } catch (\Throwable $ex) {
            Yii::error("StoryCustomFields:getFieldDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
             throw new ErrorException($ex->getMessage()); 
        }
       
    }
    
}



?>