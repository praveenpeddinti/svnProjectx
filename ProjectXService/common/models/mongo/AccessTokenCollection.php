<?php
namespace common\models\mongo;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/*
 * refer : 
 * https://github.com/yiisoft/yii2-mongodb/blob/master/docs/guide/usage-ar.md
 */
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
//use yii\db\ActiveRecord;
use yii\mongodb\ActiveRecord;
use yii\mongodb\Query;
use yii\data\ActiveDataProvider;
use yii\web\IdentityInterface;



class AccessTokenCollection extends ActiveRecord 
{
    public static function collectionName()
    {
        return 'AccessTokenCollection';
    }
    
    public function attributes() {
//        parent::attributes();
        return [
            
          "_id",
          "CollaboratorId",
          "Accesstoken",
          "Status",
          "BrowserType",
          "DeviceType",
          "Rememberme",
          "UpdatedBy",
          "CreatedOn",
          "UpdatedOn"

        ];
    }
    
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => '\yii\behaviors\TimestampBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['CreatedOn','UpdatedOn'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['UpdatedOn'],
                ],
                 'value' => function() { return new \MongoDB\BSON\UTCDateTime(time() * 1000); // unix timestamp 
                 },
            ],
        ];
    }
    
     /**
    * @author Padmaja
    * @description This is to checkCollabaratorStatus
    * @return type 
    * @param type $accesstoken,$collabaratorId,$browserType
     */     
    public function checkCollabaratorStatus($collaboratorId)
    {
        try{
            $query = new Query();
            // compose the query
            $query->select(['CollaboratorId', 'Accesstoken','Status','BrowserType'])
                ->from('AccessTokenCollection')
                    ->orderBy(['CreatedOn'=>SORT_DESC])
                    ->where(['CollaboratorId' => (int)$collaboratorId])
                ->limit(1);
            $models = $query->all();
            return $models;
        } catch (Exception $ex) {
             Yii::log("AccessTokenCollection:checkCollabaratorStatus::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
        

    }
       /**
    * @author Padmaja
    * @description This is to saveAccesstokenData
    * @return type 
    * @param type $accesstoken,$collabaratorId,$browserType
     */ 
    public function saveAccesstokenData($accesstoken,$collabaratorId,$browserType,$remembermeStatus){
        try{
            $returnValue = 'failure';
            $ActiveUsersObj = new AccessTokenCollection();
            $ActiveUsersObj->CollaboratorId = (int)$collabaratorId;
            $ActiveUsersObj->Accesstoken = $accesstoken;
            $ActiveUsersObj->Status = (int)1;
            $ActiveUsersObj->BrowserType = $browserType;
            $ActiveUsersObj->DeviceType="";
            $ActiveUsersObj->Rememberme=$remembermeStatus;
            $ActiveUsersObj->UpdatedBy="";
            //$ActiveUsersObj->insert();  
            //unset($Obj);
           if ($ActiveUsersObj->insert()) {
                error_log("**************");
                $returnValue = $ActiveUsersObj->_id;
            }
            return $returnValue; 
        } catch (Exception $ex) {
            Yii::log("AccessTokenCollection:saveAccesstokenData::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
         
    }
          /**
    * @author Padmaja
    * @description This is to updateStatusByToken
    * @return type 
    * @param type $collabaratortoken
     */ 
   
    public function updateStatusByToken($collabaratortoken){
        try{
            $updateSatus= AccessTokenCollection::getCollection();
            $returnStatus = $updateSatus->update( array("Accesstoken"=> $collabaratortoken ), array('Status' => (int)0,'UpdatedBy'=>(String)'User'));
           // error_log("restul-------------".  print_r($returnStatus,1));
            return $returnStatus;
        } catch (Exception $ex) {
             Yii::log("AccessTokenCollection:updateStatusByToken::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }

    }

}
?>

