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

class RepoPermissions extends ActiveRecord 
{
    
    public static function tableName()
    {
        return '{{%RepoPermissions}}';
    }
    
    public function behaviors()
    {
        return [
        ];
    }
    /**
     * @author Padmaja
     * @param type $postData
     * @return type
     * @Description Saves the Permissions for Project Repository for the given list of users.
     */
    public static function savingUserPermissions($projectId,$userData)
    {
        try{
             $returnValue = 'failure';
             error_log("====savingUserPermissions=====>>>".print_r($userData,1));
            if(!empty($userData)){
                 foreach($userData as $extractRepo){
                    $userId=$extractRepo->userId;
                    $permissions=$extractRepo->role;
                    $userName=$extractRepo->userName;
                    $query = "select * from RepoPermissions where ProjectId='$projectId' and UserId=$userId";
                    $data = Yii::$app->db->createCommand($query)->queryOne();
                    error_log("$$$$$-----------".print_r($data['Id'],1));
                    if(empty($data)){
                        error_log("======in emprty=============");
                     $repoPermissions = new RepoPermissions();
                    $repoPermissions->UserId = $userId;
                    $repoPermissions->ProjectId = $projectId;
                    $repoPermissions->Permissions = $permissions;
                    $repoPermissions->UserName = $userName;
                    date_default_timezone_set('Asia/Kolkata');
                    $repoPermissions->CreatedOn = date("Y-m-d H:i:s") ;
                    if($repoPermissions->save()){
                       error_log("-------Id--------".$repoPermissions->Id);
                       $returnValue = $repoPermissions->Id;
                    }
                   
                  } else{
                      error_log("@@@@@@@@@@@");
                     $repoPermissions=RepoPermissions::findOne($data['Id']);
                    $repoPermissions->Permissions = $permissions;
                    
                    $repoPermissions->update();
                    if ($repoPermissions->update() !== false) {
                        $returnValue = "success";
                    } else {
                        $returnValue = "failure";
                    } 
                  }
                
                }
                   return $returnValue ;
            }
       
        } catch (\Throwable $ex) {
             Yii::error("RepoPermissions:savingProjectDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
             throw new ErrorException($ex->getMessage());
            
        }
       
   }
   /**
    * 
    * @param type $projectId
    * @param type $userId
    * @return type
    * @throws ErrorException
    * @Description Returns the Permission that a particular user has.
    */
   public function getUserPermissions($projectId,$userId){
       try{
//       $query = "select Permissions from RepoPermissions where ProjectId=$projectId and UserId=$userId";
//       $data = Yii::$app->db->createCommand($query)->queryOne();
       $query= new Query();
       $data = $query->select("Permissions")
                           ->from("RepoPermissions")
                           ->where("ProjectId=".$projectId)
                           ->andWhere("UserId=".$userId)
                           ->one();
       return $data;
       }catch(\Throwable $ex){
           Yii::error("RepoPermissions:getUserPermissions::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
             throw new ErrorException($ex->getMessage());
       }
   }
   /**
    * 
    * @param type $projectId
    * @param type $userId
    * @return type
    * @throws ErrorException
    * @Description returns the permissions and the flag to check the creation of repository for a given userId and ProjectId
    */
   public function getRepoPermissionsAndAccess($projectId,$userId){
       try{
//       $query = "select Permissions from RepoPermissions where ProjectId=$projectId and UserId=$userId";
//       $permissionsData = Yii::$app->db->createCommand($query)->queryOne();
       $query= new Query();
       $permissionsData = $query->select("Permissions")
                           ->from("RepoPermissions")
                           ->where("ProjectId=".$projectId)
                           ->andWhere("UserId=".$userId)
                           ->one();
//       $query = "select IsRepository from Projects where PId=$projectId";
//       $repoCreated = Yii::$app->db->createCommand($query)->queryOne();
       $query= new Query();
       $repoCreated = $query->select("IsRepository")
                           ->from("Projects")
                           ->where("PId=".$projectId)
                           ->one();
       $returnData = array(
           "Permissions"=>(isset($permissionsData["Permissions"]))?$permissionsData["Permissions"]:"",
           "IsRepository"=>$repoCreated["IsRepository"]
               );
               
       return $returnData;
       }  catch (\Throwable $ex){
           Yii::error("RepoPermissions:getRepoPermissionsAndAccess::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
             throw new ErrorException($ex->getMessage());
       }
   }
}
