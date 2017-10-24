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

class Projects extends ActiveRecord 
{
    
    public static function tableName()
    {
        return '{{%Projects}}';
    }
    
    public function behaviors()
    {
        return [
        ];
    }
    /**
     * @author Moin Hussain
     * @param type $projectId
     * @return type
     * @Description Returns Name, CreatedBy, CreatedOn of requested project
     */
    public static function getProjectMiniDetails($projectId)
    {
        try{
//        $query = "select PId,ProjectName,CreatedBy,CreatedOn from Projects where PId=".$projectId;
//        $data = Yii::$app->db->createCommand($query)->queryOne();
        $query= new Query();
        $data = $query->select("PId,ProjectName,CreatedBy,CreatedOn")
              ->from("Projects")
              ->where("PId=".$projectId)
              ->one();
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
     * @Description Returns of detaild of a Project By name
     */
     public static function getProjectDetails($projectName)
    {
        try{
//        $query = "select PId,ProjectName,Description,ProjectLogo from Projects where ProjectName='$projectName';";
//        $data = Yii::$app->db->createCommand($query)->queryOne();
        $query= new Query();
        $data = $query->select("PId,ProjectName,Description,ProjectLogo")
              ->from("Projects")
              ->where("ProjectName='".$projectName."'")
              ->one();
        $data["ProjectLogo"] = Yii::$app->params['ServerURL'].$data["ProjectLogo"];
        error_log("logg----".$data["ProjectLogo"]);
        if (strpos($data["ProjectLogo"],'assets') !== false) {
            $data['setLogo']=true;
        }else{
            $data['setLogo']=false;  
        }
        $data["Description"] = !empty($data["Description"])?$data["Description"]:'';
        $data["PId"] = !empty($data["PId"])?$data["PId"]:'';
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
     * @Description Created a New Project
     */
    public static function savingProjectDetails($projectName,$description,$userId,$projectLogo)
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
     * @param type $projectId
     * @param type $logo
     * @return type
     * @Description Update project logo
     */
   public static function updatingProjectLog($projectId,$projectLogo)
   {
       try{
           error_log("2222---------".$projectLogo);
            $query="update Projects set ProjectLogo='$projectLogo' where PId=$projectId";
            $data = Yii::$app->db->createCommand($query)->execute();
       } catch (\Throwable $ex) {
             Yii::error("Projects:updatingProjectLog::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
             throw new ErrorException($ex->getMessage());
            
        }
   }
    /**
     * @author Padmaja
     * @description This method to verify  the  project.
     * @param type $projectId
     * @return type
     * @Description Checks the project name to avoid dupliactes
     */
    public static function  verifyingProjectName($projectName){
        try{
//            $query = "select * from Projects where ProjectName='$projectName'";
//            $data = Yii::$app->db->createCommand($query)->queryOne();
            $query= new Query();
            $data = $query->select("")
                  ->from("Projects")
                  ->where("ProjectName='".$projectName."'")
                  ->one();
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
     * @Description Returns the Project Name based on UserId
     */
    public static function getProjectNameByUserId($userId)
    {
        try{
//            $query = "select P.PId,P.ProjectName,P.CreatedBy from Projects P join ProjectTeam PT on PT.ProjectId =P.PId where PT.CollaboratorId = $userId";
//            $data = Yii::$app->db->createCommand($query)->queryAll();
            $query= new Query();
            $data = $query->select("P.PId,P.ProjectName,P.CreatedBy")
                  ->from("Projects P")
                  ->join("join", "ProjectTeam PT", "PT.ProjectId =P.PId")
                  ->where("PT.CollaboratorId=".$userId)
                  ->all();
           return $data;   
        } catch (\Throwable $ex) {
             Yii::error("Projects:getProjectNameByUserId::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
             throw new ErrorException($ex->getMessage());
            
        }
       
    }
    /**
     * 
     * @param type $projectName
     * @param type $description
     * @param type $fileExt
     * @param type $logo
     * @param type $projectId
     * @return string
     * @throws ErrorException
     * @Description Updates Project Details on edit
     */
    public static function updateProjectDetails($projectName,$description,$fileExt,$logo,$projectId)
    {
        try{
              error_log("status-------".$projectName."desc".$description."file--".$fileExt."logo------".$logo."project---".$projectId);
            $projects=Projects::findOne($projectId);
            $projects->ProjectName=$projectName;
            $projects->Description=$description;
            $projects->ProjectLogo=$logo;
            $projects->update();
            if ($projects->update() !== false) {
                $result = "success";
            } else {
                $result = "failure";
            }
             error_log("status-------".$result);
            return $result;
           
        }catch (\Throwable $ex) {
             Yii::error("Projects:updateProjectDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
             throw new ErrorException($ex->getMessage());
            
        }
        
    }
}



?>