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

class PlanLevel extends ActiveRecord 
{
    
    public static function tableName()
    {
        return '{{%PlanLevel}}';
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
     * @Description Returns the details of a Plan level
     */
    public static function getPlanLevelDetails($priorityId)
    {
         try{
//         $query = "select * from PlanLevel where Id=".$priorityId;
//         $data = Yii::$app->db->createCommand($query)->queryOne();
         $query= new Query();
         $data = $query->select("")
                       ->from("PlanLevel")
                       ->where("Id=".$priorityId)
                       ->one();
        return $data;  
         } catch (\Throwable $ex) {
            Yii::error("PlanLevel:getPlanLevelDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
       
    }
      /**
     * @author Anand Singh
     * @return type
     * @Description Returns the list of all the Plan level in the system
     */
    public static function getPlanLevelList() {
        try {
//            $qry = "select * from PlanLevel";
//            $data = Yii::$app->db->createCommand($qry)->queryAll();
            $query= new Query();
            $data = $query->select("")
                          ->from("PlanLevel")
                          ->all();
            return $data;
        } catch (\Throwable $ex) {
            Yii::error("PlanLevel:getPlanLevelList::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
}



?>