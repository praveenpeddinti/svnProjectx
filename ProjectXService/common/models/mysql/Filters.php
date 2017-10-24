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
    /**
     * @author Anand
     * @Description Get all active filters
     * @return type
     */
    public static function getAllActiveFilters()
    {
        try{
//        $query = "select Id,Name,Type,ShowChild from Filters where Status = 1 Order by Position asc";
//        $data = Yii::$app->db->createCommand($query)->queryAll();
        $query= new Query();
        $data = $query->select("Id,Name,Type,ShowChild")
              ->from("Filters")
              ->where("Status = 1")
              ->orderBy("Position ASC")
              ->all();
        return $data;  
        } catch (\Throwable $ex) {
            Yii::error("Filters:getAllActiveFilters::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
       
    }
}