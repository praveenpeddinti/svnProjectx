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
     * @Description Returns Details of a Given State
     */
    public static function getWorkFlowDetails($workFlowId)
    {
        try{
//        $query = "select wf.Id,wf.Name,ws.Name as State , ws.Id as StateId from WorkFlowFields wf join WorkFlowState ws on wf.State=ws.Id where wf.Id=".$workFlowId;
//        $data = Yii::$app->db->createCommand($query)->queryOne();
        $query= new Query();
        $data = $query->select("wf.Id,wf.Name,ws.Name as State , ws.Id as StateId")
              ->from("WorkFlowFields wf")
              ->join("join", "WorkFlowState ws", "wf.State=ws.Id")
              ->where("wf.Id=".$workFlowId)
              ->one();
        return $data;   
        } catch (\Throwable $ex) {
            Yii::error("WorkFlowFields:getWorkFlowDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
            throw new ErrorException($ex->getMessage()); 
       }
    }
    /**
     * 
     * @param type $workflowType
     * @param type $workflowId
     * @return type
     * @throws ErrorException
     * @Description Returns list of states for a story
     */
    public static function getStoryWorkFlowList($workflowType,$workflowId){
        try{
//        $query = "select wf.Id,wf.Name,wf.Status,ws.Name as State,wr.ConfigType,wr.CaptureMessage from WorkFlowFields wf join WorkFlowReportConfiguration wr on wr.WorkFlowId=wf.Id 
// join WorkFlowMapping wm on wf.Id=wm.MappedWorkFlowId join WorkFlowState ws on wf.State=ws.Id where wm.WorkFlowType=$workflowType and wm.WorkFlowId=$workflowId order by wm.Position asc ;";
//        $data = Yii::$app->db->createCommand($query)->queryAll();
        $query= new Query();
        $data = $query->select("wf.Id,wf.Name,wf.Status,ws.Name as State,wr.ConfigType,wr.CaptureMessage")
              ->from("WorkFlowFields wf")
              ->join("join", "WorkFlowReportConfiguration wr", "wr.WorkFlowId=wf.Id")
              ->join("join", "WorkFlowMapping wm", "wf.Id=wm.MappedWorkFlowId")
              ->join("join", "WorkFlowState ws", "wf.State=ws.Id")
              ->where("wm.WorkFlowType=".$workflowType)
              ->andWhere("wm.WorkFlowId=".$workflowId)
              ->orderBy("wm.Position ASC")
              ->all();
        return $data;   
        } catch (\Throwable $ex) {
        Yii::error("WorkFlowFields:getStoryWorkFlowList::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
       throw new ErrorException($ex->getMessage()); 
        }
    }
    /**
     * 
     * @return type
     * @throws ErrorException
     * @Description Returns list of Buckets under a filter
     */
    public static function getBucketsListFilters(){
        try{
//            $query="select * from WorkFlowState";
//            $data = Yii::$app->db->createCommand($query)->queryAll();
            $query= new Query();
            $data = $query->select("")
                          ->from("WorkFlowState")
                          ->all();
            return $data;
        } catch (Exception $ex) {
 Yii::error("WorkFlowFields:getBucketsListFilters::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
       throw new ErrorException($ex->getMessage()); 
        }
    }
    /**
     * 
     * @return type
     * @throws ErrorException
     * @Description Returns the list of all the Status under a state.
     */
    public static function getWorkflowStatusList(){
        try{
//            $query="select Id,Name,State from WorkFlowFields";
//            $data = Yii::$app->db->createCommand($query)->queryAll();
            $query= new Query();
            $data = $query->select("Id,Name,State")
                          ->from("WorkFlowFields")
                          ->all();
            return $data;
        } catch (Exception $ex) {
 Yii::error("WorkFlowFields:getWorkflowStatusList::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
       throw new ErrorException($ex->getMessage()); 
        }
    }
    
}


?>
