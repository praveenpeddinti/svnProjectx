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

class Priority extends ActiveRecord 
{
    
    public static function tableName()
    {
        return '{{%Priority}}';
    }
    
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }
    /**
     * @author Moin Hussain
     * @param type $priorityId
     * @return type
     */
    public static function getPriorityDetails($priorityId)
    {
         try{
         $query = "select * from Priority where Id=".$priorityId;
         $data = Yii::$app->db->createCommand($query)->queryOne();
        return $data;  
         } catch (\Throwable $ex) {
            Yii::error("Priority:getPriorityDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException('Something went wrong');
        }
       
    }
     /**
     * @author Anand Singh
     * @return type
     */
    public static function getPriorityList() {
        try {
            $qry = "select * from Priority";
            $data = Yii::$app->db->createCommand($qry)->queryAll();
            return $data;
        }catch (\Throwable $ex) {
            Yii::error("Priority:getPriorityList::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException('Something went wrong');
        }
    }
    
}



?>