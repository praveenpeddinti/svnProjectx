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

class Collaborators extends ActiveRecord 
{
    
    public static function tableName()
    {
        return '{{%Collaborators}}';
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
     * @Description Returns Project team list
     */
    public  function getProjectTeam($projectId)
    {
        try{
//         $qry = "select C.Id,C.UserName as Name,C.Email from ProjectTeam PT join Collaborators C on PT.CollaboratorId = C.Id where PT.ProjectId = $projectId";
//         $data = Yii::$app->db->createCommand($qry)->queryAll();
         $query= new Query();
            $data = $query->select("C.Id,C.UserName as Name,C.Email")
                  ->from("ProjectTeam PT")
                  ->join("join", "Collaborators C", "PT.CollaboratorId = C.Id")
                  ->where("PT.ProjectId=".$projectId)
                  ->all();

         return $data;    
        } catch (\Throwable $ex) {
            Yii::error("Collaborators:getProjectTeam::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
       
    }
    /**
     * 
     * @param type $userData
     * @return type
     * @throws ErrorException
     * @Description Returns logged in user data images
     */

       public  function getProjectTeamImages($projectId,$id)
    {
        try{
//         $qry = "select C.Id,C.UserName as Name,C.Email,cp.ProfilePic from ProjectTeam PT join Collaborators C join CollaboratorProfile cp
//                 on PT.CollaboratorId = C.Id and cp.CollaboratorId = C.Id where  PT.ProjectId = $projectId and C.Id=$id";
//         $data = Yii::$app->db->createCommand($qry)->queryOne();
         $query= new Query();
            $data = $query->select("C.Id,C.UserName as Name,C.Email,cp.ProfilePic")
                  ->from("ProjectTeam PT")
                  ->join("join", "Collaborators C")
                  ->join("join", "CollaboratorProfile cp", "PT.CollaboratorId = C.Id and cp.CollaboratorId = C.Id")
                  ->where("PT.ProjectId=".$projectId)
                  ->andWhere("C.Id=".$id)
                  ->one();
         return $data;    
        } catch (\Throwable $ex) {
            Yii::error("Collaborators:getProjectTeamImages::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
       
    }

    public static function findByUsername($userData)
    {
        try {
//           $qry = "select * from Collaborators where Email='".$userData->username."' And Password='".$userData->password."'";
//           $data = Yii::$app->db->createCommand($qry)->queryAll();
            $query= new Query();
            $data = $query->select("")
                  ->from("Collaborators")
                  ->where("Email='".$userData->username."'")
                  ->andWhere("Password='".$userData->password."'")
                  ->all();
           return $data; 
        } catch (\Throwable $ex) {
            Yii::error("Collaborators:findByUsername::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
        
    }
    
    /**
     * @author Padmaja
     * @Description This is to get the  Collaborator data
     * @return type 
     * 
     */   
    public static function getCollaboratorDetails($userData)
    {
        try{
            $returnValue='failure';
//            $qry = "select * from Collaborators where Email='".$userData->username."' And Password= md5('".$userData->password."')";
//            $collabaratorData = Yii::$app->db->createCommand($qry)->queryAll();;
            $query= new Query();
            $collabaratorData = $query->select("")
                  ->from("Collaborators")
                  ->where("Email='".$userData->username."'")
                  ->andWhere("Password=md5('".$userData->password."')")
                  ->all();
             if(sizeof($collabaratorData)>0){
                $returnValue=$collabaratorData;
            }
            return $returnValue;

        } catch (\Throwable $ex) {
            Yii::error("Collaborators:getCollaboratorDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
      
    }
     /**
      * 
      * @param type $fieldName
      * @param type $value
      * @return type
      * @throws ErrorException
      * @Description returns Collaborator data based on the filed value passed.
      */
    public static function getCollboratorByFieldType($fieldName,$value)
    {
       try {
        $value=(int)$value;
//        $qry = "select * from Collaborators where `". $fieldName."`=$value";
//        $data = Yii::$app->db->createCommand($qry)->queryOne();
        $query= new Query();
            $data = $query->select("")
                  ->from("Collaborators")
                  ->where($fieldName."=".$value)
                  ->one();
        return $data;
       } catch (\Throwable $ex) {
            Yii::error("Collaborators:getCollboratorByFieldType::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
       
    }
    /**
     * 
     * @return type
     * @throws ErrorException
     * @Description Returns the list of all Collaborators details in system.
     */
    public static function getCollabrators()
    {
        try {
//        $qry = "select * from Collaborators";
//        $data = Yii::$app->db->createCommand($qry)->queryAll();
        $query= new Query();
        $data = $query->select("")
               ->from("Collaborators")
               ->all();
        return $data; 
        } catch (\Throwable $ex) {
            Yii::error("Collaborators:getCollabrators::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
       
    }
    /**
     * 
     * @param type $noofrecords
     * @return type
     * @throws ErrorException
     * @Description Inserts a new collaborator
     */
    public static function insertCollabrators($noofrecords)
    {   
        try {
           $comma=  ",";
        $qry = "INSERT INTO `Techo2_ProjectX`.`Collaborators`
            (
            `FirstName`,
            `LastName`,
            `UserName`,
            `Email`,
            `Password`,
            `OrganizationId`,
            `Status`,
            `CreatedOn`,
            `UpdatedOn`)";
            $values = "VALUES";
            for($i=0;$i<=$noofrecords;$i++){
              $fname='Test';
              $lname='User';
              $lname=$lname."_".$i;
              $username=$fname.$lname;
              $email=$username."@techo2.com";
              $values =$values ."(". "'$fname'".","."'$lname'".","."'$username'".","."'$email'".","."md5('test111'),1,1,now(),now())";
              if($i != $noofrecords){
               $values=$values.$comma;
              }

            }
        $final_query=$qry.$values;
        $data = Yii::$app->db->createCommand($final_query)->execute();
        return $data; 
        } catch (\Throwable $ex) {
            Yii::error("Collaborators:insertCollabrators::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
         
    }
    
    /**
     * @author Ryan
     * @param type $projectId,$search_query
     * @return type
     * @Description Gets Collaborators list based on the search query.
     */
    public static function getFilteredProjectTeam($projectId,$search_query)
    {
        try{
//         $qry = "select C.Id,C.UserName as Name,C.Email,concat('".Yii::$app->params['ServerURL']."',CP.ProfilePic) as ProfilePic from ProjectTeam PT join Collaborators C  join CollaboratorProfile CP on PT.CollaboratorId = C.Id and PT.CollaboratorId=CP.CollaboratorId where PT.ProjectId = $projectId and C.UserName like '$search_query%'";
//         $data = Yii::$app->db->createCommand($qry)->queryAll();
         $query= new Query();
            $data = $query->select("C.Id,C.UserName as Name,C.Email,concat('".Yii::$app->params['ServerURL']."',CP.`ProfilePic`) as ProfilePic")
                  ->from("ProjectTeam PT")
                  ->join("join", "Collaborators C")
                  ->join("join", "CollaboratorProfile CP", "PT.CollaboratorId = C.Id and PT.CollaboratorId=CP.CollaboratorId")
                  ->where("PT.ProjectId=".$projectId)
                  ->andWhere("C.UserName like '".$search_query."%'")
                  ->all();
         return $data;
            
        } catch (\Throwable $ex) {
            Yii::error("Collaborators:getFilteredProjectTeam::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
       
    }
    
    /**
     * @author Ryan
     * @param type $user
     * @return type
     * @Description Checks if the user exists or not
     */
    public function checkMatchedUsers($user)
    {
        try{
//            $qry="select UserName,Email from Collaborators where UserName = '$user'";
//            $data = Yii::$app->db->createCommand($qry)->queryOne();
            $query= new Query();
            $data = $query->select("UserName,Email")
                  ->from("Collaborators")
                  ->where("UserName='".$user."'")
                  ->one();
            if(!empty($data))
            {
                return $user;
            }
            
        } catch (\Throwable $ex) {
            Yii::error("Collaborators:checkMatchedUsers::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
    
    /**
     * @author Praveen P
     * @Description This method is used to getting User details for add followers in the story/Task.
     * @return user List
     */
    public function getCollaboratorsForFollow($dafaultUserList,$searchValue, $projectId) {
        try {
            $DefaultCollaboratorIds = implode(',', $dafaultUserList);
            if(!empty($DefaultCollaboratorIds)){
                $condition = "C.Id not in ($DefaultCollaboratorIds) and";
            }else{
               $condition=''; 
            }
            $qry = "select C.Id,C.UserName as Name,C.Email,concat('".Yii::$app->params['ServerURL']."',CP.`ProfilePic`) as ProfilePic from ProjectTeam PT join Collaborators C  join CollaboratorProfile CP on PT.CollaboratorId = C.Id and PT.CollaboratorId=CP.CollaboratorId where PT.ProjectId = $projectId and ".$condition." C.UserName like '$searchValue%'";
            $data = Yii::$app->db->createCommand($qry)->queryAll();
            return $data;
        } catch (\Throwable $ex) {
            Yii::error("Collaborators:getCollaboratorsForFollow::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
    /**
     * @author Ryan 
     * @Description This method is used to getting User details By Collab Id.
     * @return user List
     */
    public static function getCollaboratorById($id)
    {
        try
        {
            $id=(int)$id;
//            $qry = "select * from Collaborators where Id=$id";
//            $data = Yii::$app->db->createCommand($qry)->queryOne();
            $query= new Query();
            $data = $query->select("")
                  ->from("Collaborators")
                  ->where("Id=".$id)
                  ->one();
            return $data;
        }catch (\Throwable $ex) {
            Yii::error("Collaborators:getCollaboratorById::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
    /**
     * @author Ryan 
     * @Description This method is used to getting User details Profile.
     * @return user List
     */
    public static function getCollaboratorWithProfile($user)
    {
        try{
//            $qry = "select concat('".Yii::$app->params['ServerURL']."',CP.ProfilePic) as ProfilePic,C.UserName,C.Id from CollaboratorProfile CP join Collaborators C on CP.CollaboratorId=C.Id where C.Email='$user'";
//            $data = Yii::$app->db->createCommand($qry)->queryOne();     
            $query= new Query();
            $data = $query->select("concat('".Yii::$app->params['ServerURL']."',CP.`ProfilePic`) as ProfilePic,C.UserName,C.Id")
                  ->from("CollaboratorProfile CP")
                  ->join("join", "Collaborators C", "CP.CollaboratorId=C.Id")
                  ->where("C.Email='".$user."'")
                  ->one();
            return $data;
            
        } catch (\Throwable $ex) {
            Yii::error("Collaborators:getCollaboratorWithProfile::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    /**
     * 
     * @param type $user
     * @return type
     * @throws ErrorException
     * @Description Returns the ID of the user based on the user name
     */
    public static function getCollaboratorId($user)
    {
        try{
//            $qry = "select Id from Collaborators where UserName='$user'";
//            $data = Yii::$app->db->createCommand($qry)->queryOne();     
            $query= new Query();
            $data = $query->select("Id")
                  ->from("Collaborators")
                  ->where("UserName='".$user."'")
                  ->one();
            return $data;
            
        } catch (\Throwable $ex) {
            Yii::error("Collaborators:getCollaboratorId::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
    /**
     * @author Praveen
     * @param type $projectId,$role
     * @return type
     * @Description Returns the list of users responsible for a project.
     */
    public function getResponsibleProjectTeam($projectId,$role)
    {
        try{
//         $qry = "select C.Id,C.UserName as Name,C.Email,concat('".Yii::$app->params['ServerURL']."',CP.ProfilePic) as ProfilePic from ProjectTeam PT join Collaborators C  join CollaboratorProfile CP on PT.CollaboratorId = C.Id and PT.CollaboratorId=CP.CollaboratorId where PT.ProjectId = $projectId";// and PT.Role in (".$role.")";
//         $data = Yii::$app->db->createCommand($qry)->queryAll();
            $query= new Query();
            $data = $query->select("C.Id,C.UserName as Name,C.Email,concat('".Yii::$app->params['ServerURL']."', CP.`ProfilePic`) as ProfilePic")
                  ->from("ProjectTeam PT")
                  ->join("join", "Collaborators C")
                  ->join("join", "CollaboratorProfile CP", "PT.CollaboratorId = C.Id and PT.CollaboratorId=CP.CollaboratorId")
                  ->where("PT.ProjectId=".$projectId)
                  ->all();
         return $data;
            
        } catch (\Throwable $ex) {
            Yii::error("Collaborators:getResponsibleProjectTeam::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
       
    }
    
     /**
     * @author Ryan
     * @param type $email
     * @return type array
     * @Description Returns the Collaborators data based on Email Id.
     */
    public static function getCollaboratorByEmail($email)
    {
        try{
//            $qry = "select Id from Collaborators where Email='$email'";
//            $data = Yii::$app->db->createCommand($qry)->queryOne();   
            $query= new Query();
            $data = $query->select("Id")
                  ->from("Collaborators")
                  ->where("Email='".$email."'")
                  ->one();
            return $data;
            
        } catch (\Throwable $ex) {
            Yii::error("Collaborators:getCollaboratorByEmail::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
    /**
     * @author Ryan
     * @param type $user
     * @return type int 
     * @Description Creates a New Collaborator
     */
    public static function createUser($user){
        try{error_log("in create user");
            $collaborator=new Collaborators();
            $collaborator->FirstName=$user->firstName;
            $collaborator->LastName=$user->lastName;
            $collaborator->UserName=$user->displayName;
            $collaborator->Email=$user->email;
            $collaborator->Password=md5($user->password);
            $collaborator->OrganizationId=1;
            $collaborator->save();
            return  $collaborator->primaryKey;
            
        } catch (\Throwable $ex) {
            Yii::error("Collaborators:createUser::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
    /**
     * @author Ryan
     * @param type $projectId,$userid
     * @return type boolean 
     * @Description Adds User to Project team
     */
    public static function addToTeam($projectId,$userid){
        try{
            $team = new ProjectTeam();
            $team->ProjectId=$projectId;
            $team->CollaboratorId=$userid;
            $team->CreatedBy=$userid;
            $team->Role=2;
            $team->Status=1;
            $team->save();
            return true;
        } catch (\Throwable $ex) {
            Yii::error("Collaborators:addToTeam::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    /**
     * 
     * @param type $userid
     * @param type $profilepic
     * @throws ErrorException
     * @Description Inserts Collaborators profile data.
     */
    public static function saveUserProfile($userid,$profilepic){
        try{
            $qry="insert into CollaboratorProfile(`CollaboratorId`,`ProfilePic`) values($userid,'$profilepic');";
            Yii::$app->db->createCommand($qry)->execute();
        } catch (\Throwable $ex) {
            Yii::error("Collaborators:saveUserProfile::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
    /**
     * @author Ryan
     * @param type $projectId,$userid
     * @return type array 
     * @Description Returns Collaborator's role
     */
    public static function getUserRole($projectId,$userId){
        try{
//            $qry="select R.Id from ProjectRoles R join ProjectTeam PT on PT.Role=R.Id where PT.CollaboratorId=$userId and PT.ProjectId=$projectId";
//            $role=Yii::$app->db->createCommand($qry)->queryOne();
            $query= new Query();
            $role = $query->select("R.Id")
                  ->from("ProjectRoles R")
                  ->join("join", "ProjectTeam PT", "PT.Role=R.Id")
                  ->where("PT.CollaboratorId=".$userId)
                  ->andWhere("PT.ProjectId=".$projectId)
                  ->one();
            return $role;
        } catch (\Throwable $ex) {
            Yii::error("Collaborators:getUserRole::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
    /**
     * @author Ryan
     * @param type $projectId,$email
     * @return type array 
     * @Description Returns the data of the user in a Project team.
     */
    public static function findUserInTeam($projectId,$email){
        try{
//            $qry="select C.Id from Collaborators C join ProjectTeam PT on PT.CollaboratorId=C.Id where C.Email='$email' and PT.ProjectId=$projectId";
//            $user=Yii::$app->db->createCommand($qry)->queryOne();
            $query= new Query();
            $user = $query->select("C.Id")
                  ->from("Collaborators C")
                  ->join("join", "ProjectTeam PT", "PT.CollaboratorId=C.Id")
                  ->where("C.Email='".$email."'")
                  ->andWhere("PT.ProjectId=".$projectId)
                  ->one();
            return $user;
        } catch (\Throwable $ex) {
            Yii::error("Collaborators:findUserInTeam::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
}
?>