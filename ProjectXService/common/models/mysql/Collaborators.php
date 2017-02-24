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

class Collaborators extends ActiveRecord 
{
    
    public static function tableName()
    {
        return '{{%Collaborators}}';
    }
    
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
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
        } catch (Exception $ex) {
Yii::log("Collaborators:getProjectTeam::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
       
    }

    public static function findByUsername($userData)
    {
         error_log("findByUsername---".print_r($userData,1));
        $qry = "select * from Collaborators where Email='".$userData->username."' And Password='".$userData->password."'";
        error_log("queryyyyyyyyyyyyyyy".$qry);
        $data = Yii::$app->db->createCommand($qry)->queryAll();
        return $data;
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

        } catch (Exception $ex) {
             Yii::log("Collabarator:getCollaboratorDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
      
    }
     public static function getCollboratorByFieldType($fieldName,$value)
    {
        $qry = "select * from Collaborators where ".$fieldName."=".$value;
        $data = Yii::$app->db->createCommand($qry)->queryOne();
        return $data;
//        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }
    public static function getCollabrators()
    {
        $qry = "select * from Collaborators";
        $data = Yii::$app->db->createCommand($qry)->queryAll();
        return $data;
//        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }
    public static function insertCollabrators($noofrecords)
    {   
        
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
    }
}
?>