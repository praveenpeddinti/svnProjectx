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

class StoryFields extends ActiveRecord 
{
    
    public static function tableName()
    {
        return '{{%StoryFields}}';
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
        $query = "select * from StoryFields sf join FieldTypes ft on sf.Type=ft.Id  where sf.Id=".$fieldId;
        error_log($query);
        $data = Yii::$app->db->createCommand($query)->queryOne();
        return $data;  
        } catch (Exception $ex) {
     Yii::log("StoryFields:getFieldDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
       
    }
    /**
     * @author Anand Singh
     * @param type $projectId
     * @return type
     */
     public function getStoryFieldList() {
        try {
            $qry = "select * from StoryFields";
            $data = Yii::$app->db->createCommand($qry)->queryAll();
            return $data;
        } catch (Exception $exc) {
            Yii::log("StoryFields:getStoryFieldList::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
}



?>