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

class Projects extends ActiveRecord 
{
    
    public static function tableName()
    {
        return '{{%Projects}}';
    }
    
    public function behaviors()
    {
        return [
            //TimestampBehavior::className(),
        ];
    }
    /**
     * @author Moin Hussain
     * @param type $projectId
     * @return type
     */
    public static function getProjectMiniDetails($projectId)
    {
        try{
        $query = "select PId,ProjectName,CreatedBy,CreatedOn from Projects where PId=".$projectId;
        $data = Yii::$app->db->createCommand($query)->queryOne();
        return $data;   
        } catch (\Throwable $ex) {
       Yii::error("Projects:getProjectMiniDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
       throw new ErrorException($ex->getMessage()); 
            }
       
    }
    
    /**
     * @author Anand
     * @param type $projectName
     * @return type
     */
     public static function getProjectDetails($projectName)
    {
        try{
        $query = "select PId,ProjectName from Projects where ProjectName='$projectName';";
        $data = Yii::$app->db->createCommand($query)->queryOne();
        return $data;   
        } catch (\Throwable $ex) {
             Yii::error("Projects:getProjectDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
             throw new ErrorException($ex->getMessage());
            
        }
       
    }
        /**
     * @author Padmaja
     * @param type $projectId
     * @return type
     */
    public static function savingProjectDetails($projectName,$description,$userId)
    {
        try{
            $returnValue = 'failure';
            $projects = new Projects();
            $projects->ProjectName = $projectName;
            $projects->Description = $description;
            $projects->CreatedBy = $userId;
            if($projects->save()){
               error_log("-------Id--------".$projects->PId);
               $returnValue = $projects->PId;   
            }
           return $returnValue ;
        } catch (\Throwable $ex) {
             Yii::error("Projects:savingProjectDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
             throw new ErrorException($ex->getMessage());
            
        }
       
   }
   /**
     * @author Padmaja
     * @description This method to verify  the  project.
     * @param type $projectId
     * @return type
     */
    public static function  verifyingProjectName($projectName){
        try{
            $query = "select * from Projects where ProjectName='$projectName'";
            $data = Yii::$app->db->createCommand($query)->queryOne();
            return $data;   
        } catch (\Throwable $ex) {
             Yii::error("Projects:verifyingProjectName::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
             throw new ErrorException($ex->getMessage());
            
        }
    }
    /**
     * @author Padmaja
     * @param type $projectId
     * @return type
     */
    public static function getProjectNameByUserId($userId)
    {
        try{
            $query = "select P.PId,P.ProjectName,P.CreatedBy from Projects P join ProjectTeam PT on PT.ProjectId =P.PId where PT.CollaboratorId = $userId";
           // $query = "select PId,ProjectName,CreatedBy from Projects where CreatedBy=$userId";
            $data = Yii::$app->db->createCommand($query)->queryAll();
           return $data;   
        } catch (\Throwable $ex) {
             Yii::error("Projects:getProjectNameByUserId::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
             throw new ErrorException($ex->getMessage());
            
        }
       
//        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }
}



?>