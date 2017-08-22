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
        } catch (\Throwable $ex) {
            Yii::error("WorkFlowFields:getWorkFlowDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
            throw new ErrorException($ex->getMessage()); 
       }
    }
    
      public static function getStoryWorkFlowList($workflowType,$workflowId){
        try{
        $query = "select wf.Id,wf.Name,wf.Status,ws.Name as State,wr.ConfigType,wr.CaptureMessage from WorkFlowFields wf join WorkFlowReportConfiguration wr on wr.WorkFlowId=wf.Id 
 join WorkFlowMapping wm on wf.Id=wm.MappedWorkFlowId join WorkFlowState ws on wf.State=ws.Id where wm.WorkFlowType=$workflowType and wm.WorkFlowId=$workflowId order by wm.Position asc ;";
        $data = Yii::$app->db->createCommand($query)->queryAll();
        return $data;   
        } catch (\Throwable $ex) {
        Yii::error("WorkFlowFields:getStoryWorkFlowList::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
       throw new ErrorException($ex->getMessage()); 
        }
    }
    public static function getBucketsListFilters(){
        try{
            $query="select * from WorkFlowState";
            $data = Yii::$app->db->createCommand($query)->queryAll();
            return $data;
        } catch (Exception $ex) {
 Yii::error("WorkFlowFields:getBucketsListFilters::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
       throw new ErrorException($ex->getMessage()); 
        }
    }
    
}



?>