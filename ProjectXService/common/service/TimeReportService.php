<?php
namespace common\service;
use common\models\mongo\{TicketCollection,TinyUserCollection,ProjectTicketSequence,TicketTimeLog,TicketComments,TicketArtifacts,NotificationCollection};
use common\models\mysql\{WorkFlowFields,StoryFields,Priority,PlanLevel,TicketType,Bucket,Collaborators,TaskTypes,Filters};
use common\models\bean\FieldBean;
use Yii;
use common\components\{CommonUtility,NotificationTrait};

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class TimeReportService {

          use NotificationTrait;
    /**
     * @author Praveen P
     * @param type $CollaboratorId
     * @param type $projectId
     * @return type 
     */
  
    public function getTimeReportCountAndWorkLog($StoryData, $projectId) {
        try {
            $arrayTimelog = TicketTimeLog::getTimeReportCountAndWorkLog($StoryData, $projectId);
            return $arrayTimelog;
        } catch (Exception $ex) {
            Yii::log("TimeReportService:getTimeReportCountAndWorkLog::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
    /**
     * @author Praveen P
     * @param type $CollaboratorId
     * @param type $projectId
     * @return type work log details for collaborator
     */
    public function getAllTimeReportDetails($StoryData,$projectId) {
        try {
            $finalData = array();
            $timeReportDetails = TicketTimeLog::getAllTimeReportDetails($StoryData,$projectId);
            return $timeReportDetails;
        } catch (Exception $ex) {
            Yii::log("TimeReportService:getAllTimeReportDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
    /**
     * @author Praveen P
     * @param type $CollaboratorId
     * @param type $projectId
     * @return type work hours for collaborator
     */
    
    public function getTotalWorkLogHours($StoryData, $projectId) {
        try {
            $workLogHours = TicketTimeLog::getTotalWorkLogHours($StoryData, $projectId);

            return $workLogHours;
        } catch (Exception $ex) {
            Yii::log("TimeReportService:getTotalWorkLogHours::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
    public function getAllStoryDetailsForTimelog($projectId,$ticketId,$sortvalue, $searchString){
        try{
             $ticketDetails = TicketCollection::getAllStoryDetailsForAutoSearch($projectId,$ticketId,$sortvalue, $searchString);

            return $ticketDetails;
        } catch (Exception $ex) {
            Yii::log("TimeReportService:getAllStoryDetailsForTimelog::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
        
    }
    /**
    * @author Padmaja
    * @uses removing  details
    * @return type
    */
    public function RemoveTimelogs($timelog_data){
        try{
         
            $projectId = $timelog_data->projectId;
            $slug = $timelog_data->slug;
            $timelogHours = $timelog_data->workHours;
            $ticketId=$timelog_data->ticketId;
            $collabaratorId=$timelog_data->userInfo->Id;
            $autocompleteticketId=$ticketId;
            $returnSlug=  TicketTimeLog::removeTimelogData($projectId,$ticketId,$slug,$timelogHours,$collabaratorId);
            $recipient_list=array();
            $action='';
            if ($returnSlug != "failure") {
                 if(!empty($autocompleteticketId)){
                    $ticketId=$autocompleteticketId;
                }
                
            $parenTicketInfo = TicketCollection::getTicketDetails($ticketId,$projectId,array("ParentStoryId","TotalTimeLog") );
            $oldTimeLog=$parenTicketInfo['TotalTimeLog'];
            $total=(-$timelogHours);
            $newTotalTime = $oldTimeLog + $total;
            $slug =  new \MongoDB\BSON\ObjectID();
           $activityData= $this->saveActivity($ticketId, $projectId,'TotalTimeLog', $newTotalTime, $collabaratorId,$slug);
            $this->saveNotifications($timelog_data, 'TotalTimeLog', $newTotalTime,'TotalTimeLog',$slug); 
            if ($parenTicketInfo["ParentStoryId"] != "") {
                $updateParentTotalTime = TicketCollection::updateTotalTimeLog($projectId, $parenTicketInfo["ParentStoryId"], $total);
            }
                 $updateindivisualTotalTimeLog = TicketCollection::updateTotalTimeLog($projectId, $ticketId, $total);

                return $activityData;
            }
        } catch (Exception $ex) {
             Yii::log("TimeReportService:RemoveTimelogs::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
        
    }
   /**
    * @author Padmaja
    * @param type $ticketData
    * @return type
    */
    public function updateTimelog($ticketData){
        try{
        
            $projectId = $ticketData->projectId;
            $slug = $ticketData->slug;
            $totalWorkHours = $ticketData->timelogHours;
            //$ticketDesc= explode(".",$ticketData->ticketDesc);
           // $ticketId=$ticketData->ticketId;
            $collabaratorId=$ticketData->userInfo->Id;
            $description=str_replace('.','',$ticketData->description);
            $autocompleteticketId="";
            $oldWorkHours = $ticketData->oldWorkHours;
            $calendardate="";
            if(isset($ticketData->autocompleteTask)){
                $autocompleteTask= explode(" ",$ticketData->autocompleteTask);
                $autocompleteticketId=str_replace('#','',$autocompleteTask[0]);
            }
            if(isset($ticketData->oldTicketDesc)){
                $oldTask= explode(" ",$ticketData->oldTicketDesc);
                $oldTicketId=str_replace('#','',$oldTask[0]);
            } 
            
            
            $editableDate = $ticketData->editableDate;
             $editableDate = CommonUtility::convert_time_zone(strtotime($editableDate),"UTC",$ticketData->timeZone);   
            if(isset($ticketData->calendardate)){
                $calendardate = $ticketData->calendardate;
            }
            $ticketTimeLog = TicketTimeLog::updateTimeLogRecords($projectId,$slug,$totalWorkHours,$oldTicketId,$autocompleteticketId,$editableDate,$calendardate,$collabaratorId,$description);
            
            $recipient_list=array();
            $action='';
            if ($ticketTimeLog != "failure") {
               error_log("old hoursssssssss----".$oldWorkHours."newww hoursssssssss----".$totalWorkHours);
               error_log("old ticketId----".$oldTicketId."---autocompletedId----".$autocompleteticketId);
               if(empty($autocompleteticketId) || $oldTicketId == $autocompleteticketId ){ // If old ticketId and New Id are same 
                   error_log("iffffffffffffffff------------------------1");
                   if($oldWorkHours != $totalWorkHours){
                         error_log("iffffffffffffffff------------------------2");
                       $temphours=$totalWorkHours-$oldWorkHours;// newone -old one
                       error_log("tempppp".$temphours);
                       $parenTicketInfo = TicketCollection::getTicketDetails($oldTicketId,$projectId,array("ParentStoryId","TotalTimeLog") );
                     $ticketTimeLog = $parenTicketInfo["TotalTimeLog"];
                       $ticketNewTimeLog = $ticketTimeLog + $temphours;
                    $slug =  new \MongoDB\BSON\ObjectID();
                    error_log("ticketId-----------".$oldTicketId."---".$projectId."----".$ticketNewTimeLog);
                    $activityData= $this->saveActivity($oldTicketId, $projectId,'TotalTimeLog', (float)$ticketNewTimeLog, $collabaratorId,$slug);
                   $ticketData->ticketId = $oldTicketId;
                    $this->saveNotifications($ticketData, 'TotalTimeLog', (float)$ticketNewTimeLog,'TotalTimeLog',$slug);            
                     if ($parenTicketInfo["ParentStoryId"] != "") {
                          $updateParentTotalTime = TicketCollection::updateTotalTimeLog($projectId, $parenTicketInfo["ParentStoryId"], $temphours);
                      }
                      $updateindivisualTotalTimeLog = TicketCollection::updateTotalTimeLog($projectId, $oldTicketId, $temphours);
                 }
          
           }elseif ($autocompleteticketId != $oldTicketId) {
                   if($autocompleteticketId){ // For the new one
                         $temphours=$totalWorkHours;
                        error_log("autocompletett@@@@@@@=-----".$temphours."temptt-----".$autocompleteticketId);
                        $parenTicketInfo = TicketCollection::getTicketDetails($autocompleteticketId,$projectId,array("ParentStoryId","TotalTimeLog") );
                       
                       $ticketTimeLog = $parenTicketInfo["TotalTimeLog"];
                       $ticketNewTimeLog = $ticketTimeLog + $temphours;
                        
                        $activityData= $this->saveActivity($autocompleteticketId, $projectId,'TotalTimeLog', (float)$ticketNewTimeLog, $collabaratorId,$slug);
                       $ticketData->ticketId = $autocompleteticketId;
                        $this->saveNotifications($ticketData, 'TotalTimeLog', (float)$ticketNewTimeLog,'TotalTimeLog',$slug);            

                        if ($parenTicketInfo["ParentStoryId"] != "") {
                             $updateParentTotalTime = TicketCollection::updateTotalTimeLog($projectId, $parenTicketInfo["ParentStoryId"], $temphours);
                          }
                        $updateindivisualTotalTimeLog = TicketCollection::updateTotalTimeLog($projectId, $autocompleteticketId, $temphours);
                   }
                   if($oldTicketId){ // For the old one
                        $temphours=(-$oldWorkHours);
                        error_log($oldTicketId."------@@@@@@@@@@@@-----".$temphours);
                        $parenTicketInfo = TicketCollection::getTicketDetails($oldTicketId,$projectId,array("ParentStoryId","TotalTimeLog") );
                       $ticketTimeLog = $parenTicketInfo["TotalTimeLog"];
                         $ticketNewTimeLog = $ticketTimeLog + $temphours;
                         $activityData= $this->saveActivity($oldTicketId, $projectId,'TotalTimeLog', (float)$ticketNewTimeLog, $collabaratorId,$slug);
                         $ticketData->ticketId = $oldTicketId;
                         $this->saveNotifications($ticketData, 'TotalTimeLog', (float)$ticketNewTimeLog,'TotalTimeLog',$slug);            
      
                        if ($parenTicketInfo["ParentStoryId"] != "") {
                             $updateParentTotalTime = TicketCollection::updateTotalTimeLog($projectId, $parenTicketInfo["ParentStoryId"], $temphours);
                          }
                        $updateindivisualTotalTimeLog = TicketCollection::updateTotalTimeLog($projectId, $oldTicketId, $temphours);
                   }
                    error_log("old ticketIdasddddddddd------".$temphours);
                }
                
                return $slug;

            }
        } catch (Exception $ex) {
            Yii::log("TimeReportService:updateTimelog::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
}

  