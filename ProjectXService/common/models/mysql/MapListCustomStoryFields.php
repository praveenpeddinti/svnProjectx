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

class MapListCustomStoryFields extends ActiveRecord 
{
    
    public static function tableName()
    {
        return '{{%MapListCustomStoryFields}}';
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
    public static function getListValue($customFieldId,$valueId)
    {
        try{
        $query = "select Id,ListOption from MapListCustomStoryFields where Id=".$valueId." and StoryFieldId=".$customFieldId;
        $data = Yii::$app->db->createCommand($query)->queryOne();
        return $data;  
        } catch (\Throwable $ex) {
            Yii::error("MapListCustomerStoryFields:getListValue::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException('Something went wrong');
        }
       
    }
    
}



?>