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
use yii\db\Query;
use yii\web\IdentityInterface;
use yii\base\ErrorException;

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
     * @Description Gets the details of a Field.
     */
    public static function getFieldDetails($fieldId,$col="Id")
    {
        try{
//        $query = "select sf.*,ft.Name from StoryFields sf join FieldTypes ft on sf.Type=ft.Id  where sf.".$col."='".$fieldId."'";
//        $data = Yii::$app->db->createCommand($query)->queryOne();
        $query= new Query();
        $data = $query->select("sf.*,ft.Name")
              ->from("StoryFields sf")
              ->join("join", "FieldTypes ft", "sf.Type=ft.Id")
              ->where("sf.".$col."='".$fieldId."'")
              ->one();
        return $data;  
        } catch (\Throwable $ex) {
            Yii::error("StoryFields:getFieldDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
            throw new ErrorException($ex->getMessage());
         }
       
    }
    
    
    
    /**
     * @Description This method is to get all the storyfields
     * @author Moin Hussain
     * @return type
     */
     public function getStoryFieldList() {
        try {
           
//            $qry = "select sf.*,ft.Name from StoryFields sf join FieldTypes ft on sf.Type=ft.Id";
//            $data = Yii::$app->db->createCommand($qry)->queryAll();
            $query= new Query();
            $data = $query->select("sf.*,ft.Name")
                  ->from("StoryFields sf")
                  ->join("join", "FieldTypes ft", "sf.Type=ft.Id")
                  ->all();
            return $data;
        }catch (\Throwable $ex) {
            Yii::error("StoryFields:getStoryFieldList::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
       throw new ErrorException($ex->getMessage()); 
       }
    }
    /**
     * @Description This method is to get storyfields to be shown in new ticket creation
     * @author Moin Hussain
     * @return type
     */
     public static function getNewTicketStoryFields() {
        try {
//            $qry = "select sf.*,ft.`Name` from StoryFields sf join FieldTypes ft on sf.Type=ft.Id where sf.Id in (4,6)";
//            $data = Yii::$app->db->createCommand($qry)->queryAll();
            $query= new Query();
            $data = $query->select("sf.*,ft.Name")
                  ->from("StoryFields sf")
                  ->join("join", "FieldTypes ft", "sf.Type=ft.Id")
                  ->where("sf.Id in (4,6)")
                  ->all();
            return $data;
        } catch (\Throwable $ex) {
            Yii::error("StoryFields:getNewTicketStoryFields::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
            throw new ErrorException($ex->getMessage());
       }
    }
    
        }
    


?>