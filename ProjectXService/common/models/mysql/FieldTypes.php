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

class FieldTypes extends ActiveRecord 
{
    
    public static function tableName()
    {
        return '{{%FieldTypes}}';
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
        $query = "select Name from FieldTypes where Id=".$fieldId;
        $data = Yii::$app->db->createCommand($query)->queryOne();
        return $data;  
        } catch (\Throwable $ex) {
            Yii::error("FieldType:getFieldDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
       
    }
    
        }
       
    

?>