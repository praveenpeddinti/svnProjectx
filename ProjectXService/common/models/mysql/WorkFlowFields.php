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

class WorkFlowFields extends ActiveRecord 
{
    
    public static function tableName()
    {
        return '{{%WorkFlowFields}}';
    }
    
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }
    /**
     * @author Moin Hussain
     * @param type $workFlowId
     * @return type
     */
    public static function getWorkFlowDetails($workFlowId)
    {
        try{
        $query = "select wf.Id,wf.Name,ws.Name as State , ws.Id as StateId from WorkFlowFields wf join WorkFlowState ws on wf.State=ws.Id where wf.Id=".$workFlowId;
        $data = Yii::$app->db->createCommand($query)->queryOne();
        return $data;   
        } catch (Exception $ex) {
Yii::log("WorkFlowFields:getWorkFlowDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');

        }
    }
    
      public static function getStoryWorkFlowList($workflowType,$workflowId){
        try{
        $query = "select   wf.Id,wf.Name,wf.Status,ws.Name as State from WorkFlowFields wf
 join WorkFlowMapping wm on wf.Id=wm.MappedWorkFlowId join WorkFlowState ws on wf.State=ws.Id where wm.WorkFlowType=$workflowType and wm.WorkFlowId=$workflowId order by wf.Id=$workflowId desc ;";
        $data = Yii::$app->db->createCommand($query)->queryAll();
        return $data;   
        } catch (Exception $ex) {
Yii::log("WorkFlowFields:getStoryWorkFlowList::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');

        }
    }
    
}



?>