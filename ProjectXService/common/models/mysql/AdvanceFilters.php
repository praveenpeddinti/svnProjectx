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


class AdvanceFilters extends ActiveRecord 
{
    
    public static function tableName()
    {
        return '{{%AdvanceFilters}}';
    }
    
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }
    /**
     * @author Anand
     * @uses Get  advance filters
     * @return type
     */
    public static function getAdvanceFilters()
    {
        try{
        $query = "select aft.Id as Id,aft.TagValue,aft.TagName as Name,af.Name as Type,af.DisplayLabel as DisplayName from Techo2_ProjectX.AdvanceFilterTags aft
join Techo2_ProjectX.AdvanceFilters af on af.Id=aft.FilterId";
        $data = Yii::$app->db->createCommand($query)->queryAll();
        return $data;  
        } catch (\Throwable $ex) {
            Yii::error("Filters:getAdvanceFilters::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
       
    }
}