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
use Yii;
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
    /**
     * @author Moin Hussain
     * @param type $str
     * @return string
     */
     public static function getExtension($str) {
  try{
    $i = strrpos($str, ".");
    if (!$i) {
        return "";
    }

    $l = strlen($str) - $i;
    $ext = substr($str, $i + 1, $l);
    //$ext .= '_'.$_SESSION['user']->id;
    return $ext;
    } catch (Exception $ex) {
            Yii::log("CommonUtility:getExtension::".$ex->getMessage()."--".$ex->getTraceAsString(), 'error', 'application');
        }
}
    /**
     * @author Moin Hussain
     * @param type $sec
     * @param type $to_tz
     * @param type $from_tz
     * @param type $type
     * @return type
     */
     static function convert_time_zone($sec, $to_tz, $from_tz = "", $type = "") {
        try {
            $date_time = date("Y-m-d H:i:s", $sec);
            if ($from_tz == "" || $from_tz == "undefined") {
                $from_tz = date_default_timezone_get();
            }
            if ($to_tz == "" || $to_tz == "undefined") {
                $to_tz = date_default_timezone_get();
            }
            $time_object = new \DateTime($date_time, new \DateTimeZone($from_tz));
            $time_object->setTimezone(new \DateTimeZone($to_tz));
            if ($type == "sec") {
                return strtotime($time_object->format('m-d-Y H:i:s'));
            } else {
                return $time_object->format('d-m-Y H:i:s');
            }
        } catch (Exception $ex) {
            Yii::log("CommonUtility:convert_time_zone::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    /**
     * @author Moin Hussain
     * @param type $sec
     * @param type $to_tz
     * @param type $from_tz
     * @param type $type
     * @return type
     */
      static function convert_date_zone($sec, $to_tz, $from_tz = "", $type = "") {
        try {
            $date_time = date("Y-m-d H:i:s", $sec);
            if ($from_tz == "" || $from_tz == "undefined") {
                $from_tz = date_default_timezone_get();
            }
            if ($to_tz == "" || $to_tz == "undefined") {
                $to_tz = date_default_timezone_get();
            }
            $time_object = new \DateTime($date_time, new \DateTimeZone($from_tz));
            $time_object->setTimezone(new \DateTimeZone($to_tz));
           if ($type == "sec") {
                return strtotime($time_object->format('Y-m-d H:i:s'));
            } else {
                return $time_object->format('d-m-Y');
            }
        } catch (Exception $ex) {
            Yii::log("CommonUtility:convert_date_zone::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    /**
     * @description This method is to prepare ticket details
     * @author Moin Hussain
     * @param type $ticketId
     * @param type $projectId
     * @return type
     */
    public static function prepareTicketDetails($ticketDetails,$projectId,$flag = "part"){
        try{
             $ticketCollectionModel = new TicketCollection();
           // $ticketDetails = $ticketCollectionModel->getTicketDetails($ticketId,$projectId);
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
               if(isset($value["custom_field_id"] )){
                $storyFieldDetails = $storyCustomFieldsModel->getFieldDetails($value["Id"]);
                 if($storyFieldDetails["Name"] == "List"){
                
                    $listDetails = $mapListModel->getListValue($value["Id"],$value["value"]);
                    $value["readable_value"] = $listDetails; 
                }
                
                
                
               }else{
                 $storyFieldDetails = $storyFieldsModel->getFieldDetails($value["Id"]);
   
               }
                $value["position"] = $storyFieldDetails["Position"];
                $value["title"] = $storyFieldDetails["Title"];
                $value["required"] = $storyFieldDetails["Required"];
                $value["readonly"] = $storyFieldDetails["ReadOnly"];
                $value["field_type"] = $storyFieldDetails["Name"];
                $value["field_name"] = $storyFieldDetails["Field_Name"];
                if($storyFieldDetails["Type"] == 4 || $storyFieldDetails["Type"] == 5){
                       if($value["value"] != ""){
                             $datetime = $value["value"]->toDateTime();
                     if($storyFieldDetails["Type"] == 4){
                        $readableDate = $datetime->format('Y-m-d');
                     }else{
                         $readableDate = $datetime->format('Y-m-d H:i:s');
                     }
                     $value["readable_value"] =   $readableDate; 
                       }else{
                            $value["readable_value"] = "";
                       }
                   
                 }
                if($storyFieldDetails["Type"] == 6){
                    if($value["value"] != ""){
                         $assignedToDetails = $tinyUserModel->getMiniUserDetails($value["value"]);
                        $assignedToDetails["ProfilePicture"] = Yii::$app->params['ServerURL'].$assignedToDetails["ProfilePicture"];
                       $value["readable_value"] = $assignedToDetails;  
                    }
                 $value["readable_value"]="";
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
            usort($ticketDetails["Fields"], function($a, $b)
            {
               // echo $a["position"]."\n";
                return $a["position"] >= $b["position"];
            });
         //  return $ticketDetails["Fields"];
            
           // $ticketDetails["Fields"]="";
            $projectObj = new Projects();
            $projectDetails = $projectObj->getProjectMiniDetails($ticketDetails["ProjectId"]);
            $ticketDetails["Project"] = $projectDetails;
            
            $selectFields = [];
            if($flag == "part"){
               $selectFields = ['Title', 'TicketId'];

            }
            foreach ($ticketDetails["Tasks"] as &$task) {
                 $taskDetails = $ticketCollectionModel->getTicketDetails($task,$projectId,$selectFields);
                 $task = $taskDetails;
            }
            foreach ($ticketDetails["RelatedStories"] as &$relatedStory) {
                 $relatedStoryDetails = $ticketCollectionModel->getTicketDetails($relatedStory,$projectId,$selectFields);
                 $relatedStory = $relatedStoryDetails;
            }
            
            
            unset( $ticketDetails["CreatedOn"]);
            unset($ticketDetails["UpdatedOn"]);
          

            return $ticketDetails;
        } catch (Exception $ex) {
Yii::log("CommonUtility:prepareTicketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
     /**
     * @description This method is to prepare ticket edit details
     * @author Moin Hussain
     * @param type $ticketId
     * @param type $projectId
     * @return type
     */
    public static function prepareTicketEditDetails($ticketId,$projectId){
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
               if(isset($value["custom_field_id"] )){
                $storyFieldDetails = $storyCustomFieldsModel->getFieldDetails($value["Id"]);
                 if($storyFieldDetails["Name"] == "List"){
                
                    $listDetails = $mapListModel->getListValue($value["Id"],$value["value"]);
                    $value["readable_value"] = $listDetails; 
                }
                
                
                
               }else{
                 $storyFieldDetails = $storyFieldsModel->getFieldDetails($value["Id"]);
   
               }
                $value["position"] = $storyFieldDetails["Position"];
                $value["title"] = $storyFieldDetails["Title"];
                $value["required"] = $storyFieldDetails["Required"];
                $value["readonly"] = $storyFieldDetails["ReadOnly"];
                $value["field_type"] = $storyFieldDetails["Name"];
                $value["field_name"] = $storyFieldDetails["Field_Name"];
                
                 
                   if($storyFieldDetails["Type"] == 4 || $storyFieldDetails["Type"] == 5){
                       if($value["value"] != ""){
                             $datetime = $value["value"]->toDateTime();
                     if($storyFieldDetails["Type"] == 4){
                        $readableDate = $datetime->format('Y-m-d');
                     }else{
                         $readableDate = $datetime->format('Y-m-d H:i:s');
                     }
                     $value["readable_value"] =   $readableDate; 
                       }else{
                            $value["readable_value"] = "";
                       }
                   
                 }
                 
                 
                 
                 
                 
                if($storyFieldDetails["Type"] == 6){
                  $assignedToDetails = $tinyUserModel->getMiniUserDetails($value["value"]);
                  $value["readable_value"] = $assignedToDetails;  
                  
                }
                 if($storyFieldDetails["Type"] == 8){
                
                 $bucketName = $bucketModel->getBucketName($value["value"],$ticketDetails["ProjectId"]);
                 $value["readable_value"] = $bucketName;  
                  $value["meta_data"] = $bucketModel->getBucketsList($projectId);
                }
                if($storyFieldDetails["Field_Name"] == "priority"){
                
                    $priorityDetails = $priorityModel->getPriorityDetails($value["value"]);
                    $value["readable_value"] = $priorityDetails; 
                    $value["meta_data"] = $priorityModel->getPriorityList();
                }
                 if($storyFieldDetails["Field_Name"] == "planlevel"){
                
                    $planlevelDetails = $planlevelModel->getPlanLevelDetails($value["value"]);
                    $value["readable_value"] = $planlevelDetails; 
                    $value["meta_data"] = $planlevelModel->getPlanLevelList();
                }
                 if($storyFieldDetails["Field_Name"] == "workflow"){
                
                   
                    $workFlowDetails = $workFlowModel->getWorkFlowDetails($value["value"]);
                    $value["readable_value"] = $workFlowDetails; 
                    $value["meta_data"] = $workFlowModel->getStoryWorkFlowList();
                }
                 if($storyFieldDetails["Field_Name"] == "tickettype"){
                   
                 $ticketTypeDetails = $ticketTypeModel->getTicketType($value["value"]);
                 $value["readable_value"] = $ticketTypeDetails; 
                 $value["meta_data"] = $ticketTypeModel->getTicketTypeList();
                }
               
               
                
                
            }
            $ticketDetails['collaborators'] = ServiceFactory::getCollaboratorServiceInstance()->getProjectTeam($projectId);
            usort($ticketDetails["Fields"], function($a, $b)
            {
               // echo $a["position"]."\n";
                return $a["position"] >= $b["position"];
            });
         //  return $ticketDetails["Fields"];
            
           // $ticketDetails["Fields"]="";
            $projectObj = new Projects();
            $projectDetails = $projectObj->getProjectMiniDetails($ticketDetails["ProjectId"]);
            $ticketDetails["Project"] = $projectDetails;
            
           
            
            unset( $ticketDetails["CreatedOn"]);
            unset($ticketDetails["UpdatedOn"]);
            unset( $ticketDetails["ArtifactsRef"]);
            unset($ticketDetails["CommentsRef"]);
          

            return $ticketDetails;
        } catch (Exception $ex) {
Yii::log("CommonUtility:prepareTicketEditDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
  
   
}

?>
