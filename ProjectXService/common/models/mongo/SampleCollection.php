<?php
namespace common\models\mongo;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/*
 * refer : 
 * https://github.com/yiisoft/yii2-mongodb/blob/master/docs/guide/usage-ar.md
 */
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
//use yii\db\ActiveRecord;
use yii\mongodb\ActiveRecord;
use yii\mongodb\Query;
use yii\data\ActiveDataProvider;
use yii\web\IdentityInterface;

class SampleCollection extends ActiveRecord 
{
    public static function collectionName()
    {
        return 'Test';
    }
    
    public function attributes() {
//        parent::attributes();
        return [
            
   "_id",
   "Id",
   "Name",
   "date",
   "rank" 


        ];
    }
    
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }
    
    
    public static function testMongo(){
        
//        error_log("++++++++++++++++++".print_r(Yii::$app->get('mongodb'),1));
        $query = new Query();
        $query->from('Test');
        $provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ]
        ]);
        $models = $provider->getModels();
        return $models;
//        error_log("************************".print_r($models,1));

    }
}
?>
