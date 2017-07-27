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
         //   TimestampBehavior::className(),
        ];
    }
//    /**
//     * @author Padmaja
//     * @param type $collabaratorId
//     * @return type
//     */
//    public static function getProjectTeamDetailsByRole($collabaratorId,$limit,$skip)
//    {
//        try{
//            error_log("limitttttttt-----".$limit."-------".$skip);
//            $query = "select * from ProjectTeam where CollaboratorId=$collabaratorId ORDER BY Id desc limit $skip,$limit";
//            error_log("qrryyyyyyyyyyyyy---------".$query);
//            $data = Yii::$app->db->createCommand($query)->queryAll();
//            return $data;   
//        } catch (Exception $ex) {
//        Yii::log("ProjectTeam:getProjectMiniDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
//        }
//    }
     /**
     * @author Padmaja
     * @param type $projectId
     * @return type
     */
    
    public static function getProjectTeamCount($projectId)
    {
        try{
            $query="select count(CollaboratorId) as TeamCount from ProjectTeam where ProjectId=".$projectId;
            $count = Yii::$app->db->createCommand($query)->queryOne();
            return $count;  
        } catch (\Throwable $ex) {
            Yii::error("ProjectTeam:getProjectTeamCount::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException('Something went wrong');
        }
    }
          /**
     * @author Padmaja
     * @param type $projectId
     * @return type
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
            throw new ErrorException('Something went wrong');
        }
       
   }
         /**
     * @author Padmaja
     * @param type $collabaratorId
     * @return type
     */
   public static function getProjectsCountByUserId($collabaratorId){
       try{
           $query = "select * from ProjectTeam where CollaboratorId=$collabaratorId";
           $projectcount = Yii::$app->db->createCommand($query)->queryAll();
           return $projectcount;  
       } catch (\Throwable $ex) {
            Yii::error("ProjectTeam:getProjectsCountByUserId::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException('Something went wrong');
        }
   }
    /**
     * @author Padmaja
     * @param type $collabaratorId
     * @param type $limit
     * @return type
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
            throw new ErrorException('Something went wrong');
        }
   } 
}



?>