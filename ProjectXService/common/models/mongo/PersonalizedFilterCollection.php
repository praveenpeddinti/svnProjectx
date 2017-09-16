<?php

namespace common\models\mongo;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
//use yii\db\ActiveRecord;
use yii\mongodb\ActiveRecord;
use yii\mongodb\Query;
use yii\data\ActiveDataProvider;
use yii\web\IdentityInterface;
use common\models\mongo\TicketCollection;
use yii\base\ErrorException;

class PersonalizedFilterCollection extends ActiveRecord {

    public static function collectionName() {
        return 'PersonalizedFilterCollection';
    }

    public function attributes() {
//        parent::attributes();
        return [
            "_id",
            "ProjectId",
            "Name",
            "Id",
            "Conditions",
            "UserId",
            "CreatedOn"
        ];
    }

    public function behaviors() {
        return [
//            TimestampBehavior::className(),
        ];
    }
    
    /**
     * 
     * @param type $name
     * @param type $userId
     * @param type $projectId
     * @param type $condition
     * @return type
     * @throws ErrorException
     * @author Anand
     */
    
    public static function savePersonalizedFilter($name,$userId,$projectId,$condition){
         try {
            $returnValue = "failure";
            
            $db = PersonalizedFilterCollection::getCollection();
            $record = $db->remove(array("ProjectId" => (int) $projectId, "UserId" => (int) $userId,"Id"=>preg_replace('/\s+/', '', $name)));
            $filterObj = new PersonalizedFilterCollection();
            $filterObj->ProjectId=(int)$projectId;
            $filterObj->Name=$name;
            $filterObj->Id=preg_replace('/\s+/', '', $name);
            $filterObj->UserId=(int)$userId;
            $filterObj->Conditions=$condition;
            $filterObj->CreatedOn = new \MongoDB\BSON\UTCDateTime(time() * 1000);
            $result = $filterObj->save();
            if($result){
               $returnValue = $filterObj;
            } 
            return $returnValue;
              
        } catch (\Throwable $ex) {
            Yii::error("PersonalizedFilterCollection:savePersonalizedFilter::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
    /**
     * 
     * @param type $userId
     * @param type $projectId
     * @param type $id
     * @return type
     * @throws ErrorException
     * @author Anand
     */
    public static function getPersonalizedFilter($userId,$projectId,$id=''){
         try {
             
             $where = ['ProjectId'=>(int)$projectId,"UserId"=>(int)$userId];
             if($id!=''){
               $where['Id']= $id; 
             }
             $query=new Query();
             $query->from('PersonalizedFilterCollection')->where($where)
             ->orderBy(["_id"=>SORT_DESC]);
             $provider = new ActiveDataProvider(['query' => $query ]);
             return  $filterCollectionData = $provider->getModels();
              
        } catch (\Throwable $ex) {
            Yii::error("PersonalizedFilterCollection:getPersonalizedFilter::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
    
     /**
     * @author Anand Singh
     * @param type $filterDta
     * @throws ErrorException
     */
    
    public static function deleteAdvanceFilter($filterDta){
        try{
              $collection = Yii::$app->mongodb->getCollection('PersonalizedFilterCollection');
              error_log("deleteAdvanceFilter__".print_r($filterDta,1));
              $conditions = array("ProjectId" => (int)$filterDta->projectId,"Id" => $filterDta->filterId,'UserId'=>(int)$filterDta->userInfo->Id);
             return $collection->remove($conditions);
        } catch (\Throwable $ex) {
            Yii::error("TicketCollection:deleteAdvanceFilter::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

}

?>