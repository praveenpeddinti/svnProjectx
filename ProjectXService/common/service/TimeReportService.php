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
  
    public function getTimeReportCount($StoryData, $projectId) {
        try {
            $totalCount = TicketTimeLog::getTimeReportCount($StoryData, $projectId);
            
            return $totalCount;
        } catch (Exception $ex) {
            Yii::log("TimeReportService:getTimeReportCount::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
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
            $total=($oldTimeLog - $timelogHours);
            $slug =  new \MongoDB\BSON\ObjectID();
            error_log("$$$$$$$$$$$$$".$ticketId."###".$projectId."asss".$total.$collabaratorId.$slug);
            $activityData= $this->saveActivity($ticketId, $projectId,'TotalTimeLog', $total, $collabaratorId,$slug);
            $this->saveNotifications($timelog_data, 'TotalTimeLog', $total,'TotalTimeLog',$slug); 
            if ($parenTicketInfo["ParentStoryId"] != "") {
                $updateParentTotalTime = TicketCollection::updateTotalTimeLog($projectId, $parenTicketInfo["ParentStoryId"], $timelogHours);
            }
                 $updateindivisualTotalTimeLog = TicketCollection::updateTotalTimeLog($projectId, $ticketId, $timelogHours);
                $ticketInfo=TicketCollection::getTicketDetails($ticketId,$projectId,array("Followers","Title","TotalTimeLog"));
                $newTimeLog=$ticketInfo['TotalTimeLog'];
                $oldTimeLog==0?$action='set to '.$newTimeLog : $action='changed from '. $oldTimeLog. 'to '. $newTimeLog;
                foreach($ticketInfo['Followers'] as $follower) 
                {
                    $collaborator=TinyUserCollection::getMiniUserDetails($follower['FollowerId']);
                    array_push($recipient_list,$collaborator['Email']);
                }

                return $activityData;
            }
        } catch (Exception $ex) {
             Yii::log("TimeReportService:RemoveTimelogs::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
        
    }
    /**
    * @author Padmaja
    * @uses updateDataForTimeLog  details
    * @return type
    */
    public function updateDataForTimeLog($ticketData){
        try{
            $projectId = $ticketData->projectId;
            $slug = $ticketData->slug;
            $totalWorkHours = $ticketData->timelogHours;
            //$ticketDesc= explode(".",$ticketData->ticketDesc);
            $ticketId=$ticketData->ticketId;
            $collabaratorId=$ticketData->userInfo->Id;
            $description=str_replace('.','',$ticketData->description);
            $autocompleteticketId="";
            $calendardate="";
            if(isset($ticketData->autocompleteTask)){
               // error_log($ticketData->autocompleteTask."@@@@@@@@");
                $autocompleteTask= explode(" ",$ticketData->autocompleteTask);
                $autocompleteticketId=str_replace('#','',$autocompleteTask[0]);
            }
            $editableDate = $ticketData->editableDate;
            if(isset($ticketData->calendardate)){
                $calendardate = $ticketData->calendardate;
            }
            $ticketTimeLog = TicketTimeLog::updateTimeLogRecords($projectId,$slug,$totalWorkHours,$ticketId,$autocompleteticketId,$editableDate,$calendardate,$collabaratorId,$description);
            
            $recipient_list=array();
            $action='';
            if ($ticketTimeLog != "failure") {
                 if(!empty($autocompleteticketId)){
                    $ticketId=$autocompleteticketId;
                }
                
            $parenTicketInfo = TicketCollection::getTicketDetails($ticketId,$projectId,array("ParentStoryId","TotalTimeLog") );
            $oldTimeLog=$parenTicketInfo['TotalTimeLog'];
            $total=($oldTimeLog + $totalWorkHours);
            $slug =  new \MongoDB\BSON\ObjectID();
            error_log("$$$$$$$$$$$$$".$ticketId."###".$projectId."asss".$total.$collabaratorId.$slug);
            $activityData= $this->saveActivity($ticketId, $projectId,'TotalTimeLog', $total, $collabaratorId,$slug);
            $this->saveNotifications($ticketData, 'TotalTimeLog', $total,'TotalTimeLog',$slug); 
            if ($parenTicketInfo["ParentStoryId"] != "") {
                $updateParentTotalTime = TicketCollection::updateTotalTimeLog($projectId, $parenTicketInfo["ParentStoryId"], $totalWorkHours);
            }
                 $updateindivisualTotalTimeLog = TicketCollection::updateTotalTimeLog($projectId, $ticketId, $totalWorkHours);
                $ticketInfo=TicketCollection::getTicketDetails($ticketId,$projectId,array("Followers","Title","TotalTimeLog"));
                $newTimeLog=$ticketInfo['TotalTimeLog'];
                $oldTimeLog==0?$action='set to '.$newTimeLog : $action='changed from '. $oldTimeLog. 'to '. $newTimeLog;
                foreach($ticketInfo['Followers'] as $follower) 
                {
                    $collaborator=TinyUserCollection::getMiniUserDetails($follower['FollowerId']);
                    array_push($recipient_list,$collaborator['Email']);
                }

                return $activityData;
            }
             
            
        } catch (Exception $ex) {
            Yii::log("TimeReportService:updateDataForTimeLog::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
}

  