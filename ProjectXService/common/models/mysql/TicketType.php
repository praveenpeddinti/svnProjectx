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


class TicketType extends ActiveRecord 
{
    
    public static function tableName()
    {
        return '{{%TicketType}}';
    }
    
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }
    /**
     * @author Moin Hussain
     * @param type $id
     * @return type
     * @Description Returns Name and Id of a particular Ticket Type.
     */
    public static function getTicketType($id)
    {
        try{
//        $query = "select Id,Name from TicketType where Id=".$id;
//        $data = Yii::$app->db->createCommand($query)->queryOne();
        $query= new Query();
        $data = $query->select("Id,Name")
              ->from("TicketType")
              ->where("Id=".$id)
              ->one();
        return $data;  
        } catch (\Throwable $ex) {
            Yii::error("TicketType:getTicketType::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        throw new ErrorException($ex->getMessage()); 
         }
        
    }
     /**
     * @author Anand Singh
     * @return type
     * @Description Gets the details of all the Ticket Types in system.
     */
    public static function getTicketTypeList() {
        try {
//            $qry = "select * from TicketType";
//            $data = Yii::$app->db->createCommand($qry)->queryAll();
            $query= new Query();
            $data = $query->select("")
                  ->from("TicketType")
                  ->all();
            return $data;
        } catch (\Throwable $ex) { 
            Yii::error("TicketType:getTicketTypeList::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
            throw new ErrorException($ex->getMessage());}
    }
    
}



?>