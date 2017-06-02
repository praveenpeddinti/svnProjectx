<?php
namespace common\service;
use common\models\mongo\{TicketCollection,TinyUserCollection,ProjectTicketSequence,TicketTimeLog,TicketComments,TicketArtifacts,NotificationCollection};
use common\models\mysql\{WorkFlowFields,StoryFields,Priority,PlanLevel,TicketType,Bucket,Collaborators,TaskTypes,Filters};
use common\models\bean\FieldBean;
use Yii;
use common\components\{CommonUtility};
use common\service\NotificationTrait;

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
            $slug =  new \MongoDB\BSON\ObjectID();
            error_log("$$$$$$$$$$$$$".$ticketId."###".$projectId."asss".$total.$collabaratorId.$slug);
           $activityData= $this->saveActivity($ticketId, $projectId,'TotalTimeLog', $total, $collabaratorId,$slug);
           // $this->saveNotifications($timelog_data, 'TotalTimeLog', $total,'TotalTimeLog',$slug); 
            if ($parenTicketInfo["ParentStoryId"] != "") {
                $updateParentTotalTime = TicketCollection::updateTotalTimeLog($projectId, $parenTicketInfo["ParentStoryId"], $timelogHours);
            }
                 $updateindivisualTotalTimeLog = TicketCollection::updateTotalTimeLog($projectId, $ticketId, $timelogHours);
//                $ticketInfo=TicketCollection::getTicketDetails($ticketId,$projectId,array("Followers","Title","TotalTimeLog"));
//                $newTimeLog=$ticketInfo['TotalTimeLog'];
//                $oldTimeLog==0?$action='set to '.$newTimeLog : $action='changed from '. $oldTimeLog. 'to '. $newTimeLog;
//                foreach($ticketInfo['Followers'] as $follower) 
//                {
//                    $collaborator=TinyUserCollection::getMiniUserDetails($follower['FollowerId']);
//                    array_push($recipient_list,$collaborator['Email']);
//                }

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
            $ticketId=$ticketData->ticketId;
            $collabaratorId=$ticketData->userInfo->Id;
            $description=str_replace('.','',$ticketData->description);
            $autocompleteticketId="";
            $oldWorkHours = $ticketData->oldWorkHours;
            $calendardate="";
            if(isset($ticketData->autocompleteTask)){
                $autocompleteTask= explode(" ",$ticketData->autocompleteTask);
                $autocompleteticketId=str_replace('#','',$autocompleteTask[0]);
            }
            $editableDate = $ticketData->editableDate;
             $editableDate = CommonUtility::convert_time_zone(strtotime($editableDate),"UTC",$ticketData->timeZone);   
            if(isset($ticketData->calendardate)){
                $calendardate = $ticketData->calendardate;
            }
            $ticketTimeLog = TicketTimeLog::updateTimeLogRecords($projectId,$slug,$totalWorkHours,$ticketId,$autocompleteticketId,$editableDate,$calendardate,$collabaratorId,$description);
            
            $recipient_list=array();
            $action='';
            $ticketIdforlog=$ticketId;
            if ($ticketTimeLog != "failure") {
               error_log("old hoursssssssss".$oldWorkHours."newww hoursssssssss".$totalWorkHours);
               error_log("old ticketId".$ticketId."autocompletedId".$autocompleteticketId);
               if(empty($autocompleteticketId)){ // If old ticketId and New Id are same 
                   if($oldWorkHours != $totalWorkHours){
                       $temphours=$totalWorkHours-$oldWorkHours;// newone -old one
                       error_log("tempppp".$temphours);
                       $parenTicketInfo = TicketCollection::getTicketDetails($ticketId,$projectId,array("ParentStoryId","TotalTimeLog") );
                        if ($parenTicketInfo["ParentStoryId"] != "") {
                            $updateParentTotalTime = TicketCollection::updateTotalTimeLog($projectId, $parenTicketInfo["ParentStoryId"], $temphours);
                        }
                        $updateindivisualTotalTimeLog = TicketCollection::updateTotalTimeLog($projectId, $ticketId, $temphours);
                   }
                   
               }elseif ($autocompleteticketId != $ticketId) {
                   if($autocompleteticketId){ // For the new one
                         $temphours=$totalWorkHours;
                        error_log("autocompletett@@@@@@@".$temphours."temptt".$autocompleteticketId);
                        $parenTicketInfo = TicketCollection::getTicketDetails($autocompleteticketId,$projectId,array("ParentStoryId","TotalTimeLog") );
                        if ($parenTicketInfo["ParentStoryId"] != "") {
                             $updateParentTotalTime = TicketCollection::updateTotalTimeLog($projectId, $parenTicketInfo["ParentStoryId"], $temphours);
                          }
                        $updateindivisualTotalTimeLog = TicketCollection::updateTotalTimeLog($projectId, $autocompleteticketId, $temphours);
                   }
                   if($ticketId){ // For the old one
                        $temphours=(-$oldWorkHours);
                        error_log($ticketId."@@@@@@@@@@@@".$temphours);
                        $parenTicketInfo = TicketCollection::getTicketDetails($ticketId,$projectId,array("ParentStoryId","TotalTimeLog") );
                        if ($parenTicketInfo["ParentStoryId"] != "") {
                             $updateParentTotalTime = TicketCollection::updateTotalTimeLog($projectId, $parenTicketInfo["ParentStoryId"], $temphours);
                          }
                        $updateindivisualTotalTimeLog = TicketCollection::updateTotalTimeLog($projectId, $ticketId, $temphours);
                   }
                    error_log("old ticketIdasddddddddd".$temphours);
                }
                return $slug;
//                 if(!empty($autocompleteticketId)){
//                    $ticketId=$autocompleteticketId;
//               }
//                
//            $parenTicketInfo = TicketCollection::getTicketDetails($ticketId,$projectId,array("ParentStoryId","TotalTimeLog") );
//            $oldTimeLog=$parenTicketInfo['TotalTimeLog'];
//            if(isset($autocompleteticketId)){
//                if($ticketIdforlog==$autocompleteticketId)
//                {
//                   $temphours=$totalWorkHours-$oldWorkHours;
//                }else {
//                    $temphours=$totalWorkHours;
//                }
//            }else{
//                $temphours=$totalWorkHours-$oldWorkHours;
//            }
//            
//            $total=($oldTimeLog + $temphours);
//            $slug =  new \MongoDB\BSON\ObjectID();
//           // $activityData= $this->saveActivity($ticketId, $projectId,'TotalTimeLog', (float)$total, $collabaratorId,$slug);
//           // $this->saveNotifications($ticketData, 'TotalTimeLog', $temphours,'TotalTimeLog',$slug); 
//              if ($parenTicketInfo["ParentStoryId"] != "") {
//                $updateParentTotalTime = TicketCollection::updateTotalTimeLog($projectId, $parenTicketInfo["ParentStoryId"], $temphours);
//            }
//            $updateindivisualTotalTimeLog = TicketCollection::updateTotalTimeLog($projectId, $ticketId, $temphours);
//            return $activityData;
            }
        } catch (Exception $ex) {
            Yii::log("TimeReportService:updateTimelog::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
}

  