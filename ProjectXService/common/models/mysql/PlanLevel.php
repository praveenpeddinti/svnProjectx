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
     */
    public static function getPlanLevelDetails($priorityId)
    {
         try{
         $query = "select * from PlanLevel where Id=".$priorityId;
         $data = Yii::$app->db->createCommand($query)->queryOne();
        return $data;  
         } catch (Exception $ex) {
    Yii::log("PlanLevel:getPlanLevelDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
         }
       
    }
      /**
     * @author Anand Singh
     * @return type
     */
    public static function getPlanLevelList() {
        try {
            $qry = "select * from PlanLevel";
            $data = Yii::$app->db->createCommand($qry)->queryAll();
            return $data;
        } catch (Exception $exc) {
            Yii::log("PlanLevel:getPlanLevel::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
}



?>