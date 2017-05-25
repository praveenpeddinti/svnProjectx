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
     * @uses adding  details
     * @return type
     */
    public function addTimelog($timelogData){
        try{
            $ticketDesc= explode(" ",$timelogData->addTimelogTask);
            $ticketDetails = TicketTimeLog::saveTimeLogData($timelogData->projectId,$ticketDesc[0],$timelogData->userInfo->Id,$timelogData->addTimelogTime,$timelogData->addTimelogDesc);
            if($ticketDetails != 'failure'){
                $ticketCollectionModel = new TicketCollection();
                $getTicketDetails = $ticketCollectionModel->getTicketDetails($ticketDesc[0],$timelogData->projectId,$selectFields=[]);
                $ticketDesc= '#'.$getTicketDetails['TicketId'].".".$getTicketDetails['Title'];
                $ticketTask = $getTicketDetails["Fields"]['planlevel']['value'];
                $TimeLogDataArray= array();
                $datetime = strtotime($timelogData->addTimelogDate);
                $loggedTime=date('M-d-Y', $datetime);
                $ticketId = array("field_name" => "Id", "value_id" => "", "field_value" => '#'.$timelogData->addTimelogTask, "other_data" => $ticketTask, "ticketDesc" => "","Time"=>"","LogDate"=>"","Slug"=>"","ticketId"=>"");
                $time = array("field_name" => "Date", "value_id" => "", "field_value" => $timelogData->addTimelogTime, "other_data" => "", "ticketDesc" =>"","Time"=>"","LogDate"=>"","Slug"=>"","ticketId"=>"");
                $date = array("field_name" => "Time", "value_id" => "", "field_value" =>$loggedTime, "other_data" => "", "ticketDesc" =>"","Time"=>"","LogDate"=>"","Slug"=>"","ticketId"=>"");
                $action = array("field_name" => "action", "value_id" => "", "field_value" => '', "other_data" => "", "ticketDesc" =>"","Time"=>"","LogDate"=>"","Slug"=>"","ticketId"=>"");
                $forTicketComments[0] = $date;
                $forTicketComments[1] =  $ticketId;
                $forTicketComments[2] = $time;
                $forTicketComments[3] = $action;
                $TimeLogDataArray= array();
                array_push($TimeLogDataArray,$forTicketComments);
                return $TimeLogDataArray;
            }       
          
        } catch (Exception $ex) {
            Yii::log("TimeReportService:addTimelog::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
            /**
     * @author Padmaja
     * @uses removing  details
     * @return type
     */
    public function RemoveTimelogs($projectId,$ticketId,$slug,$timelogHours,$userId){
        try{
          return $returnSlug=  TicketTimeLog::removeTimelogData($projectId,$ticketId,$slug,$timelogHours,$userId);
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
            $ticketDesc= explode(".",$ticketData->ticketDesc);
            $ticketId=str_replace('#','',$ticketDesc[0]);
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
             // ServiceFactory::getStoryServiceInstance()->saveNotifications($timelog_data, 'TotalTimeLog', $total,'TotalTimeLog',$slug); 
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

  