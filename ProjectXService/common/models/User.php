<?php 
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class User extends ActiveRecord 
{
    
    public static function tableName()
    {
        return '{{%User}}';
    }
    
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }
    
    public static function findByUsername($username)
    {
        $qry = "select * from Collaborators where Email='".$username."'";
        error_log($qry);
        $data = Yii::$app->db->createCommand($qry)->queryAll();
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
        $qry = "INSERT INTO `Techo2_ProjectX_Testing`.`Collaborators`
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