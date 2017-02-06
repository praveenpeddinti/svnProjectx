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

class Bucket extends ActiveRecord 
{
    
    public static function tableName()
    {
        return '{{%Bucket}}';
    }
    
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }
    /**
     * @author Moin Hussain
     * @param type $bucketId
     * @param type $projectId
     * @return type
     */
    public static function getBucketName($bucketId,$projectId)
    {
        try{
        $query = "select Name from Bucket where Id=".$bucketId." and ProjectId=".$projectId;
        $data = Yii::$app->db->createCommand($query)->queryOne();
        return $data;  
        } catch (Exception $ex) {
     Yii::log("Bucket:getBucketName::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
       
    }
    
    /**
     * @author Moin Hussain
     * @param type $projectId
     * @return type
     */
    public static function getBucketsList($projectId)
    {
        try{
        $query = "select Id,Name from Bucket where ProjectId=".$projectId;
        $data = Yii::$app->db->createCommand($query)->queryAll();
        return $data;  
        } catch (Exception $ex) {
     Yii::log("Bucket:getBucketName::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
       
    }
    
}



?>