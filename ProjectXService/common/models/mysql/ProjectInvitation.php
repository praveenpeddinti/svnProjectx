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

class ProjectInvitation extends ActiveRecord 
{
    
    public static function tableName()
    {
        return '{{%ProjectInvitation}}';
    }
    
    public function behaviors()
    {
        return [
        ];
    }
    /**
     * 
     * @param type $recipient_id
     * @param type $invite_users
     * @param string $invite_code
     * @param type $projectId
     * @param type $invited_by
     * @return string
     * @throws ErrorException
     * @Description Saves the Invite Code sent to User.
     */
    public static function insertInviteCode($recipient_id,$invite_users,$invite_code,$projectId,$invited_by)
    {
        try{ 
            if(!empty($recipient_id)){$invite_code=$invite_code.$recipient_id['Id'];};
         
                $invitation=new ProjectInvitation();
                $invitation->ProjectId=$projectId;
                $invitation->Email=$invite_users;
                (empty($recipient_id))? ($invitation->UserId=0) :($invitation->UserId=$recipient_id['Id']);
                $invitation->InvitationCode=$invite_code;
                $invitation->InviteFrom=$invited_by;
                $invitation->SentDate=date("Y-m-d H:i:s");
                $invitation->save();
                return $invite_code;
        } catch (\Throwable $ex) {
            Yii::error("ProjectInvitation:insertInviteCode::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    /**
     * 
     * @param type $email
     * @param type $projectId
     * @return type
     * @throws ErrorException
     * @Description Checks if the Invite is sent to user or not.
     */
    public static function checkInviteSent($email,$projectId)
    {
        try{
//             $qry = "select * from ProjectInvitation where Email='$email' and ProjectId=$projectId";
//             $invite_data = Yii::$app->db->createCommand($qry)->queryOne(); 
             $query= new Query();
             $invite_data = $query->select("")
                           ->from("ProjectInvitation")
                           ->where("Email='".$email."'")
                           ->andWhere("ProjectId=".$projectId)
                           ->one();
             return $invite_data;
        } catch (\Throwable $ex) {
            Yii::error("ProjectInvitation:checkInviteSent::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    /**
     * 
     * @param type $recipient_id
     * @param string $invite_code
     * @param type $recipient_email
     * @param type $projectId
     * @return string
     * @throws ErrorException
     * @Description Update the Invite Code sent to a user.
     */
    public static function updateInviteCode($recipient_id,$invite_code,$recipient_email,$projectId)
    {
        error_log("==Recipient Id==".$recipient_id['Id']);
        try{
            if(isset($recipient_id)){$invite_code=$invite_code.$recipient_id['Id'];}
            $qry = "update ProjectInvitation set InvitationCode='$invite_code' where Email='$recipient_email' and ProjectId=$projectId";
            Yii::$app->db->createCommand($qry)->execute();
            return $invite_code;
        } catch (\Throwable $ex) {
            Yii::error("ProjectInvitation:updateInviteCode::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    /**
     * 
     * @param type $invite_code
     * @return type
     * @throws ErrorException
     * @Description Verifies the Invite Code on User coming from Link
     */
    public static function verifyCode($invite_code)
    {
        try{
//            $qry="select PI.ProjectId,PI.Email,PI.IsValid,PI.UserId,P.ProjectName from ProjectInvitation PI join Projects P on PI.ProjectId = P.PId where InvitationCode='$invite_code'";
//            $invite_data = Yii::$app->db->createCommand($qry)->queryOne();
             $query= new Query();
             $invite_data = $query->select("PI.ProjectId,PI.Email,PI.IsValid,PI.UserId,P.ProjectName")
                           ->from("ProjectInvitation PI")
                           ->join("join", "Projects P", "PI.ProjectId = P.PId")
                           ->where("InvitationCode='".$invite_code."'")
                           ->one();
            return $invite_data;
        } catch (\Throwable $ex) {
            Yii::error("ProjectInvitation:verifyCode::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    /**
     * 
     * @param type $invite_email
     * @param type $invite_code
     * @throws ErrorException
     * @Description Disables the invite code
     */
   public static function disableInvite($invite_email,$invite_code)
   {
       try{
           $qry="update ProjectInvitation set IsValid=0,AcceptedDate=now() where InvitationCode='$invite_code' and Email='$invite_email'";
           Yii::$app->db->createCommand($qry)->execute();
       } catch (\Throwable $ex) {
            Yii::error("ProjectInvitation:disableInvite::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
   }
   /**
    * 
    * @param type $code
    * @return type
    * @throws ErrorException
    * @Description Returns the details of the Invite Code.
    */
   public static function getMiniInvitationDetails($code){
        try{
           $invitationDetails = ProjectInvitation::verifyCode($code);
            return $invitationDetails;
        } catch (\Throwable $ex) {
            Yii::error("Collaborators:getNewUserEmail::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
}
