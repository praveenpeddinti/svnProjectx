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

class Projects extends ActiveRecord 
{
    
    public static function tableName()
    {
        return '{{%Projects}}';
    }
    
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }
    /**
     * @author Moin Hussain
     * @param type $projectId
     * @return type
     */
    public static function getProjectMiniDetails($projectId)
    {
        try{
        $query = "select PId,ProjectName from Projects where PId=".$projectId;
        $data = Yii::$app->db->createCommand($query)->queryOne();
        return $data;   
        } catch (Exception $ex) {
Yii::log("Projects:getProjectMiniDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
       
//        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }
    
}



?>