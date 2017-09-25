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

class RepoPermissions extends ActiveRecord 
{
    
    public static function tableName()
    {
        return '{{%RepoPermissions}}';
    }
    
    public function behaviors()
    {
        return [
       //   TimestampBehavior::className(),
        ];
    }
     /**
     * @author Padmaja
     * @param type $postData
     * @return type
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
   
   public function getUserPermissions($projectId,$userId){
       try{
           error_log("getUserPermissions****repo");
       $query = "select Permissions from RepoPermissions where ProjectId=$projectId and UserId=$userId";
       error_log("getUserPermissions****qry==".$query);
       $data = Yii::$app->db->createCommand($query)->queryOne();
       error_log("getUserPermissions****data==". print_r($data,1));
       return $data;
       }catch(\Throwable $ex){
           Yii::error("RepoPermissions:getUserPermissions::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
             throw new ErrorException($ex->getMessage());
       }
   }
}
