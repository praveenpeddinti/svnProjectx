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

class Collaborators extends ActiveRecord 
{
    
    public static function tableName()
    {
        return '{{%Collaborators}}';
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
    public  function getProjectTeam($projectId)
    {
        try{
         $qry = "select C.Id,C.UserName as Name,C.Email from ProjectTeam PT join Collaborators C on PT.CollaboratorId = C.Id where PT.ProjectId = $projectId";
         $data = Yii::$app->db->createCommand($qry)->queryAll();
         return $data;    
        } catch (\Throwable $ex) {
            Yii::error("Collaborators:getProjectTeam::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
       
    }

    public static function findByUsername($userData)
    {
        try {
           $qry = "select * from Collaborators where Email='".$userData->username."' And Password='".$userData->password."'";
           $data = Yii::$app->db->createCommand($qry)->queryAll();
           return $data; 
        } catch (\Throwable $ex) {
            Yii::error("Collaborators:findByUsername::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
        
//        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }
    
    /**
     * @author Padmaja
     * @description This is to get the  Collaborator data
     * @return type 
     * 
     */   
    public static function getCollaboratorDetails($userData)
    {
        try{
            $returnValue='failure';
            $qry = "select * from Collaborators where Email='".$userData->username."' And Password= md5('".$userData->password."')";
            $collabaratorData = Yii::$app->db->createCommand($qry)->queryAll();
             if(sizeof($collabaratorData)>0){
                $returnValue=$collabaratorData;
            }
            return $returnValue;

        } catch (\Throwable $ex) {
            Yii::error("Collaborators:getCollaboratorDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
      
    }
     public static function getCollboratorByFieldType($fieldName,$value)
    {
       // error_log("==Value==".$value);
       try {
        $value=(int)$value;
        $qry = "select * from Collaborators where `". $fieldName."`=$value";
        $data = Yii::$app->db->createCommand($qry)->queryOne();
        return $data;
       } catch (\Throwable $ex) {
            Yii::error("Collaborators:getCollboratorByFieldType::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
       
//        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }
    public static function getCollabrators()
    {
        try {
        $qry = "select * from Collaborators";
        $data = Yii::$app->db->createCommand($qry)->queryAll();
        return $data; 
        } catch (\Throwable $ex) {
            Yii::error("Collaborators:getCollabrators::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
       
//        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }
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
     */
    public static function getFilteredProjectTeam($projectId,$search_query)
    {
        try{
         $qry = "select C.Id,C.UserName as Name,C.Email,concat('".Yii::$app->params['ServerURL']."',CP.ProfilePic) as ProfilePic from ProjectTeam PT join Collaborators C  join CollaboratorProfile CP on PT.CollaboratorId = C.Id and PT.CollaboratorId=CP.CollaboratorId where PT.ProjectId = $projectId and C.UserName like '$search_query%'";
         $data = Yii::$app->db->createCommand($qry)->queryAll();
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
     */
    public function checkMatchedUsers($user)
    {
        try{
            $qry="select UserName,Email from Collaborators where UserName = '$user'";
            $data = Yii::$app->db->createCommand($qry)->queryOne();
            if(!empty($data))
            {
//                try
//                {
//          
//                    $recipients=json_decode(file_get_contents("php://input"));
//                    error_log('==Email=='.print_r($data['Email'],1));
//                    $email_recepients=[$data['Email']];
//                    ApiClient::SetApiKey("9d55f483-0501-4005-8ada-3335f666e731");
//                    $EEemail = new Email();
//                    try
//                       {
//                           $subject="Ticket Status";
//                           $from="marshal.ryan@techo2.com";
//                           $fromName="techo2";
//                           $html="<h1> Test Email </h1>";
//                           $text="Hi,This is a Test Email";
//                           $response = $EEemail->Send($subject, $from, $fromName, null, null, null, null, null, null, $email_recepients, array(), array(), array(), array(), array(), null, null, $html, $text);		
//                       }
//                       catch (Exception $e)
//                       {
//                           echo 'Something went wrong: ', $e->getMessage(), '\n';
//
//                           return;
//                       }		
//            
//                } catch (Exception $ex) {
//
//                    }
                return $user;
            }
            
        } catch (\Throwable $ex) {
            Yii::error("Collaborators:checkMatchedUsers::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
    
    /**
     * @author Praveen P
     * @description This method is used to getting User details for add followers in the story/Task.
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
            $qry = "select C.Id,C.UserName as Name,C.Email,concat('".Yii::$app->params['ServerURL']."',CP.ProfilePic) as ProfilePic from ProjectTeam PT join Collaborators C  join CollaboratorProfile CP on PT.CollaboratorId = C.Id and PT.CollaboratorId=CP.CollaboratorId where PT.ProjectId = $projectId and ".$condition." C.UserName like '$searchValue%'";
            $data = Yii::$app->db->createCommand($qry)->queryAll();
            return $data;
        } catch (\Throwable $ex) {
            Yii::error("Collaborators:getCollaboratorsForFollow::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
    /**
     * @author Ryan 
     * @description This method is used to getting User details By Collab Id.
     * @return user List
     */
    public static function getCollaboratorById($id)
    {
        try
        {
            $id=(int)$id;
            $qry = "select * from Collaborators where Id=$id";
            error_log("==query==".$qry);
            $data = Yii::$app->db->createCommand($qry)->queryOne();
            return $data;
        }catch (\Throwable $ex) {
            Yii::error("Collaborators:getCollaboratorById::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
    /**
     * @author Ryan 
     * @description This method is used to getting User details Profile.
     * @return user List
     */
    public static function getCollaboratorWithProfile($user)
    {
        try{
            $qry = "select concat('".Yii::$app->params['ServerURL']."',CP.ProfilePic) as ProfilePic,C.UserName,C.Id from CollaboratorProfile CP join Collaborators C on CP.CollaboratorId=C.Id where C.Email='$user'";
            error_log("getCollaboratorWithProfile---".$qry);
            $data = Yii::$app->db->createCommand($qry)->queryOne();   
            return $data;
            
        } catch (\Throwable $ex) {
            Yii::error("Collaborators:getCollaboratorWithProfile::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    public static function getCollaboratorId($user)
    {
        try{
            $qry = "select Id from Collaborators where UserName='$user'";
            $data = Yii::$app->db->createCommand($qry)->queryOne();   
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
     */
    public function getResponsibleProjectTeam($projectId,$role)
    {
        try{
         $qry = "select C.Id,C.UserName as Name,C.Email,concat('".Yii::$app->params['ServerURL']."',CP.ProfilePic) as ProfilePic from ProjectTeam PT join Collaborators C  join CollaboratorProfile CP on PT.CollaboratorId = C.Id and PT.CollaboratorId=CP.CollaboratorId where PT.ProjectId = $projectId";// and PT.Role in (".$role.")";
         $data = Yii::$app->db->createCommand($qry)->queryAll();
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
     */
    public static function getCollaboratorByEmail($email)
    {
        try{
            error_log("Log___________________________".$email);
            $qry = "select Id from Collaborators where Email='$email'";
            $data = Yii::$app->db->createCommand($qry)->queryOne();   
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
     */
    public static function createUser($user){
        try{error_log("in create user");
            $collaborator=new Collaborators();
            $collaborator->FirstName=$user->firstName;
            $collaborator->LastName=$user->lastName;
            //$collaborator->DisplayName=$user->displayName;
          //  $collaborator->UserName=$user->firstName.'.'.$user->lastName;
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
     */
    public static function getUserRole($projectId,$userId){
        try{
            $qry="select R.Id from ProjectRoles R join ProjectTeam PT on PT.Role=R.Id where PT.CollaboratorId=$userId and PT.ProjectId=$projectId";
            $role=Yii::$app->db->createCommand($qry)->queryOne();
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
     */
    public static function findUserInTeam($projectId,$email){
        try{
            $qry="select C.Id from Collaborators C join ProjectTeam PT on PT.CollaboratorId=C.Id where C.Email='$email' and PT.ProjectId=$projectId";
            $user=Yii::$app->db->createCommand($qry)->queryOne();
            return $user;
        } catch (\Throwable $ex) {
            Yii::error("Collaborators:findUserInTeam::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
}
?>