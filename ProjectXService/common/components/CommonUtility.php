<?php
namespace  common\components;

use common\models\mongo\TicketCollection;
use common\models\mysql\Priority;
use common\models\mysql\Projects;
use common\models\mysql\WorkFlowFields;
use common\models\mongo\TinyUserCollection;
use common\models\mysql\Bucket;
use common\models\mysql\TicketType;
use common\models\mysql\StoryFields;
use common\models\mysql\StoryCustomFields;
use common\models\mysql\FieldTypes;
use common\models\mysql\MapListCustomStoryFields;
use common\models\mysql\PlanLevel;
/* 
 *
 * @author Moin Hussain
 */
class CommonUtility {
   /**
    * @author Moin Hussain
    * @param type $object
    * @param type $type
    * @return type
    */
    public static function prepareResponse($object,$type = "json"){
        if($type == "json"){
           \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        }else{
            \Yii::$app->response->format = \yii\web\Response::FORMAT_XML;
        }
        return $object;
    }
    public static function prepareTicketDetails($ticketId,$projectId){
        try{
             $ticketCollectionModel = new TicketCollection();
            $ticketDetails = $ticketCollectionModel->getTicketDetails($ticketId,$projectId);
            $storyFieldsModel = new StoryFields();
            $storyCustomFieldsModel = new StoryCustomFields();
            $tinyUserModel =  new TinyUserCollection();
            $bucketModel = new Bucket();
            $priorityModel = new Priority();
            $mapListModel = new MapListCustomStoryFields();
            $planlevelModel = new PlanLevel();
            $workFlowModel = new WorkFlowFields();
            $ticketTypeModel = new TicketType();
            foreach ($ticketDetails["Fields"] as &$value) {
              //  echo $value["Id"]."---".$value["value"]."\n";
               if(isset($value["custom_field_id"] )){
                $storyFieldDetails = $storyCustomFieldsModel->getFieldDetails($value["Id"]);
                 if($storyFieldDetails["Name"] == "List"){
                
                    $listDetails = $mapListModel->getListValue($value["Id"],$value["value"]);
                    $value["readable_value"] = $listDetails; 
                }
                
                
                
               }else{
                 $storyFieldDetails = $storyFieldsModel->getFieldDetails($value["Id"]);
   
               }
                $value["title"] = $storyFieldDetails["Title"];
                $value["required"] = $storyFieldDetails["Required"];
                $value["readonly"] = $storyFieldDetails["ReadOnly"];
                $value["field_type"] = $storyFieldDetails["Name"];
                $value["field_name"] = $storyFieldDetails["Field_Name"];
                 if($storyFieldDetails["Type"] == 4 || $storyFieldDetails["Type"] == 5){
                     $datetime = $value["value"]->toDateTime();
                     $value["readable_value"] = $datetime; 
                 }
                if($storyFieldDetails["Type"] == 6){
                  $assignedToDetails = $tinyUserModel->getMiniUserDetails($value["value"]);
                  $value["readable_value"] = $assignedToDetails;  
                }
                 if($storyFieldDetails["Type"] == 8){
                
                 $bucketName = $bucketModel->getBucketName($value["value"],$ticketDetails["ProjectId"]);
                 $value["readable_value"] = $bucketName;  
                }
                if($storyFieldDetails["Field_Name"] == "priority"){
                
                    $priorityDetails = $priorityModel->getPriorityDetails($value["value"]);
                    $value["readable_value"] = $priorityDetails; 
                }
                 if($storyFieldDetails["Field_Name"] == "planlevel"){
                
                    $planlevelDetails = $planlevelModel->getPlanLevelDetails($value["value"]);
                    $value["readable_value"] = $planlevelDetails; 
                }
                 if($storyFieldDetails["Field_Name"] == "workflow"){
                
                   
                    $workFlowDetails = $workFlowModel->getWorkFlowDetails($value["value"]);
                     $value["readable_value"] = $workFlowDetails; 
                }
                 if($storyFieldDetails["Field_Name"] == "tickettype"){
                   
                 $ticketTypeDetails = $ticketTypeModel->getTicketType($value["value"]);
                 $value["readable_value"] = $ticketTypeDetails; 
                }
               
               
                
                
            }
          // return $ticketDetails["Fields"];
            
           // $ticketDetails["Fields"]="";
            $projectObj = new Projects();
            $projectDetails = $projectObj->getProjectMiniDetails($ticketDetails["ProjectId"]);

             $ticketDetails["Project"] = $projectDetails;

            return $ticketDetails;
        } catch (Exception $ex) {
Yii::log("CommonUtility:prepareTicketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
  
   
}

?>
