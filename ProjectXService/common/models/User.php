<?php 
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class User extends ActiveRecord 
{
    
    public static function tableName()
    {
        return '{{%User}}';
    }
    
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }
    
    public static function findByUsername($username)
    {
        $qry = "select * from Collaborators where Email='".$username."'";
        error_log($qry);
        $data = Yii::$app->db->createCommand($qry)->queryAll();
        return $data;
//        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }
    
}



?>