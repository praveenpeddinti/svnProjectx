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
     * @Description Returns details of all fields for ticket details page.
     */
    public static function getFieldDetails($fieldId)
    {
        try{
//        $query = "select Name from FieldTypes where Id=".$fieldId;
//        $data = Yii::$app->db->createCommand($query)->queryOne();
        $query= new Query();
        $data = $query->select("Name")
              ->from("FieldTypes")
              ->where("Id=".$fieldId)
              ->one();
        return $data;  
        } catch (\Throwable $ex) {
            Yii::error("FieldType:getFieldDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
       
    }
    
        }
       
    

?>