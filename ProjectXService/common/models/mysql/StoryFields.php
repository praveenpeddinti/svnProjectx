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
        $query = "select sf.*,ft.Name from StoryFields sf join FieldTypes ft on sf.Type=ft.Id  where sf.Id=".$fieldId;
        $data = Yii::$app->db->createCommand($query)->queryOne();
        return $data;  
        } catch (Exception $ex) {
     Yii::log("StoryFields:getFieldDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
       
    }
    
    
    
    /**
     * @description This method is to get all the storyfields
     * @author Moin Hussain
     * @return type
     */
     public function getStoryFieldList() {
        try {
           
            $qry = "select sf.*,ft.Name from StoryFields sf join FieldTypes ft on sf.Type=ft.Id";
            $data = Yii::$app->db->createCommand($qry)->queryAll();
            return $data;
        } catch (Exception $exc) {
            Yii::log("StoryFields:getStoryFieldList::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    /**
     * @description This method is to get storyfields to be shown in new ticket creation
     * @author Moin Hussain
     * @return type
     */
     public function getNewTicketStoryFields() {
        try {
            $qry = "select sf.*,ft.`Name` from StoryFields sf join FieldTypes ft on sf.Type=ft.Id where sf.Id in (4,6)";
            $data = Yii::$app->db->createCommand($qry)->queryAll();
            return $data;
        } catch (Exception $exc) {
            Yii::log("StoryFields:getNewTicketStoryFields::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
}



?>