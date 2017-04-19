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


class Filters extends ActiveRecord 
{
    
    public static function tableName()
    {
        return '{{%Filters}}';
    }
    
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }
    
    public static function getAllActiveFilters()
    {
        try{
        $query = "select Id,Name,Type,ShowChild from Filters where Status = 1 Order by Position asc";
        $data = Yii::$app->db->createCommand($query)->queryAll();
        return $data;  
        } catch (Exception $ex) {
     Yii::log("Bucket:getAllActiveFilters::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
       
    }
}