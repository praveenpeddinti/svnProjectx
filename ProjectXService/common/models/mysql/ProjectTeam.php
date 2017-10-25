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
use yii\data\ActiveDataProvider;
use yii\base\ErrorException;

class ProjectTeam extends ActiveRecord 
{
    
    public static function tableName()
    {
        return '{{%ProjectTeam}}';
    }
    
    public function behaviors()
    {
        return [
        ];
    }
     /**
     * @author Padmaja
     * @param type $projectId
     * @return type
     * @Description Returns the count of number of members in a Project
     */
    
    public static function getProjectTeamCount($projectId)
    {
        try{
//            $query="select count(CollaboratorId) as TeamCount from ProjectTeam where ProjectId=".$projectId;
//            $count = Yii::$app->db->createCommand($query)->queryOne();
            $query= new Query();
            $count = $query->select("count(CollaboratorId) as TeamCount")
                           ->from("ProjectTeam")
                           ->where("ProjectId=".$projectId)
                           ->one();
            return $count;  
        } catch (\Throwable $ex) {
            Yii::error("ProjectTeam:getProjectTeamCount::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
     /**
     * @author Padmaja
     * @param type $projectId
     * @param type $userId
     * @return type
     * @Description Adds a Member to Project team
     */
    public static function saveProjectTeamDetails($projectId,$userId)
    {
        try{
            $returnValue = 'failure';
            $projects = new ProjectTeam();
            $projects->ProjectId = $projectId;
            $projects->CollaboratorId = $userId;
            $projects->CreatedBy = $userId;
            if($projects->save()){
               $returnValue = 'success';   
            }
           return $returnValue ;
        } catch (\Throwable $ex) {
            Yii::error("ProjectTeam:saveProjectTeamDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
       
   }
         /**
     * @author Padmaja
     * @param type $collabaratorId
     * @return type
     * @Description Returns Count of projects a User involved in.
     */
   public static function getProjectsCountByUserId($collabaratorId){
       try{
//           $query = "select * from ProjectTeam where CollaboratorId=$collabaratorId";
//           $projectcount = Yii::$app->db->createCommand($query)->queryAll();
           $query= new Query();
           $projectcount = $query->select("")
                           ->from("ProjectTeam")
                           ->where("CollaboratorId=".$collabaratorId)
                           ->all();
           return $projectcount;  
       } catch (\Throwable $ex) {
            Yii::error("ProjectTeam:getProjectsCountByUserId::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
   }
    /**
     * @author Padmaja
     * @param type $collabaratorId
     * @param type $limit
     * @return type
     * @Description Get Details of all the projects that a user involved in.
     */
   public static function getAllProjects($collabaratorId,$limit,$page){
       try{
            $DataProvider = new ActiveDataProvider([
        'query' => ProjectTeam::find()->
            where(['CollaboratorId'=>$collabaratorId])->
            orderBy('Id DESC'),
        'pagination' => [
            'pageSize' => $limit,
            'page'=>$page
        ],
             
    ]);
        return  $Model= $DataProvider->getModels();

       } catch (\Throwable $ex) {
            Yii::error("ProjectTeam:getAllProjects::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
   }
   
   /**
     * @author Ryan
     * @param type $search,$projectId
     * @return type
     * @Description Returns details of active users in a project.
     */
   public static function getActiveUsersForAllProjects($search,$projectId)
   {
       try{
           $userTeam=array();
//           $query="select CollaboratorId from ProjectTeam where ProjectId=$projectId";
//           $project_users=Yii::$app->db->createCommand($query)->queryAll();
           $query= new Query();
           $project_users = $query->select("CollaboratorId")
                           ->from("ProjectTeam")
                           ->where("ProjectId=".$projectId)
                           ->all();
           foreach($project_users as $project_user)
           {
               array_push($userTeam,$project_user['CollaboratorId']);
           }
          
           $team=implode(',',$userTeam);
//           $query = "select distinct C.UserName,C.Email from Collaborators C  where C.UserName like '$search%' and C.Status=1 and C.Id not in($team)";
//           $users = Yii::$app->db->createCommand($query)->queryAll();
           $query= new Query();
           $users = $query->select("C.UserName,C.Email")
                           ->distinct(true)
                           ->from("Collaborators C")
                           ->where("C.UserName like '".$search."%'")
                           ->andWhere("C.Status=1")
                           ->andWhere("C.Id not in(".$team.")")
                           ->all();
           return $users;
       } catch (\Throwable $ex) {
            Yii::error("ProjectTeam:getActiveUsersForAllProjects::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
   }
}



?>