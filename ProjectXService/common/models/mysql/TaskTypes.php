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

class TaskTypes extends ActiveRecord 
{
    
    public static function tableName()
    {
        return '{{%TaskTypes}}';
    }
    
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }
    /**
     * @author Anand
     * @param type
     * @return type
     */
    public static function getTaskTypes()
    {
        try{
        $query = "select * from TaskTypes where IsDefault = 1";
        $data = Yii::$app->db->createCommand($query)->queryAll();
        return $data;  
        } catch (Exception $ex) {
Yii::log("TaskType:getTaskTypes::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');

        }
    }

    
}



?><?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

