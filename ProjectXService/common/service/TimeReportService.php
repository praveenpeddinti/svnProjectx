<?php
namespace common\service;
use common\models\mongo\{TicketCollection,TinyUserCollection,ProjectTicketSequence,TicketTimeLog,TicketComments,TicketArtifacts,NotificationCollection};
use common\models\mysql\{WorkFlowFields,StoryFields,Priority,PlanLevel,TicketType,Bucket,Collaborators,TaskTypes,Filters};
use common\models\bean\FieldBean;
use Yii;
use common\components\{CommonUtility,NotificationTrait};
use yii\base\ErrorException;

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
     * @Description gets total work log for time report
     */
  
    public function getTimeReportCountAndWorkLog($StoryData, $projectId) {
        try {
            $arrayTimelog = TicketTimeLog::getTimeReportCountAndWorkLog($StoryData, $projectId);
            return $arrayTimelog;
        } catch (\Throwable $ex) {
            Yii::error("TimeReportService:getTimeReportCountAndWorkLog::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
    /**
     * @author Praveen P
     * @param type $CollaboratorId
     * @param type $projectId
     * @Description gets work log details for collaborator
     */
    public function getAllTimeReportDetails($StoryData,$projectId) {
        try {
            $finalData = array();
            $timeReportDetails = TicketTimeLog::getAllTimeReportDetails($StoryData,$projectId);
            $timezone = $StoryData->timeZone;
            $desc_limit = 15;
                     
            $TimeLogDataArray= array();
            if(count($timeReportDetails) > 0){
                foreach($timeReportDetails as $reportDetails){
                    $timelogs = $reportDetails["data"];
                foreach($timelogs as $eachOne){ error_log("==Collab Id==".$eachOne['CollaboratorId']."==Ticket Id==".$eachOne['TicketId']);
                    $ticketCollectionModel = new TicketCollection();
                    $userId=Collaborators::getCollaboratorById($eachOne['CollaboratorId']);//added by Ryan
                    $userInfo=Collaborators::getCollaboratorWithProfile($userId['Email']); //added by Ryan
                    $getTicketDetails = $ticketCollectionModel->getTicketDetails($eachOne['TicketId'],$projectId,$selectFields=[]);
                    $desc_limit=50;
                    $ticketDesc= '#'.$getTicketDetails['TicketId']." ".$getTicketDetails['Title'];
                    $ticketDesc= CommonUtility::refineActivityData($ticketDesc,200);
                    $timeDesc= CommonUtility::refineActivityDataTimeDesc($getTicketDetails['Description'],50);
                    $ticketTask = $getTicketDetails["Fields"]['planlevel']['value']; 
                    $datetime = $eachOne['LoggedOn']->toDateTime();  
                    $datetime->setTimezone(new \DateTimeZone($timezone));
                    $LogDate = $datetime->format('M-d-Y');
                    $readableDate =$datetime->format('Y-m-d H:i:s');
                    $eachOne['Time']=number_format((float)$eachOne['Time'], 1, '.', '');
                    $ticketId = array("field_name" => "Id", "value_id" => "", "field_value" => $ticketDesc, "other_data" => $ticketTask, "ticketDesc" => $ticketDesc,"Time"=>$eachOne['Time'],"LogDate"=>$LogDate,"Slug"=>$eachOne['Slug'],"ticketId"=>$getTicketDetails['TicketId'],"description"=>$eachOne['Description'],"readableDate"=>$readableDate,'limit'=>$desc_limit);
                    $time = array("field_name" => "Date", "value_id" => "", "field_value" => $eachOne['Time'], "other_data" => "", "ticketDesc" =>$ticketDesc,"Time"=>$eachOne['Time'],"LogDate"=>$LogDate,"Slug"=>$eachOne['Slug'],"ticketId"=>$getTicketDetails['TicketId'],"description"=>$eachOne['Description'],"readableDate"=>$readableDate,'limit'=>$desc_limit);
                    $date = array("field_name" => "Time", "value_id" => "", "field_value" => $LogDate, "other_data" => "", "ticketDesc" =>$ticketDesc,"Time"=>$eachOne['Time'],"LogDate"=>$LogDate,"Slug"=>$eachOne['Slug'],"ticketId"=>$getTicketDetails['TicketId'],"description"=>$eachOne['Description'],"readableDate"=>$readableDate,'limit'=>$desc_limit);
                    $action = array("field_name" => "action", "value_id" => "", "field_value" => '', "other_data" => "", "ticketDesc" =>$ticketDesc,"Time"=>$eachOne['Time'],"LogDate"=>$LogDate,"Slug"=>$eachOne['Slug'],"ticketId"=>$getTicketDetails['TicketId'],"description"=>$eachOne['Description'],"readableDate"=>$readableDate,'limit'=>$desc_limit);
                    $timeDescription = array("field_name" => "Description", "value_id" => "", "field_value" => $timeDesc, "other_data" => "", "ticketDesc" =>strip_tags($getTicketDetails['Description']),"Time"=>$eachOne['Time'],"LogDate"=>$LogDate,"Slug"=>$eachOne['Slug'],"ticketId"=>$getTicketDetails['TicketId'],"description"=>$eachOne['Description'],"readableDate"=>$readableDate,'limit'=>$desc_limit);
                    $userProfile=array("UserName"=>$userInfo['UserName'],"Profile"=>$userInfo['ProfilePic']);
                    $forTicketComments[0] = $date;
                    $forTicketComments[1] = $userProfile;
                    $forTicketComments[2] = $ticketId;
                    $forTicketComments[3] = $timeDescription;
                    $forTicketComments[4] = $time;
                    $forTicketComments[5] = $action;
                    
                   array_push($TimeLogDataArray,$forTicketComments);
                }
               }
               }
            
            return $TimeLogDataArray;
        } catch (\Throwable $ex) {
            Yii::error("TimeReportService:getAllTimeReportDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
    /**
     * @author Praveen P
     * @param type $CollaboratorId
     * @param type $projectId
     * @Description gets total work hours for collaborator
     */
    
    public function getTotalWorkLogHours($StoryData, $projectId) {
        try {
            $workLogHours = TicketTimeLog::getTotalWorkLogHours($StoryData, $projectId);

            return $workLogHours;
        } catch (\Throwable $ex) {
            Yii::error("TimeReportService:getTotalWorkLogHours::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    /**
     * 
     * @param type $projectId
     * @param type $sortvalue
     * @param type $searchString
     * @return type
     * @throws ErrorException
     * @Description Gets story details for time log
     */
    public function getAllStoryDetailsForTimelog($projectId,$sortvalue, $searchString){
        try{
             $ticketDetails = TicketCollection::getAllStoryDetailsForTimelog($projectId,$sortvalue, $searchString);
          foreach ($ticketDetails as &$value) {
                $value["Title"] = htmlspecialchars_decode($value["Title"]);
            }
            return $ticketDetails;
        } catch (\Throwable $ex) {
            Yii::error("TimeReportService:getAllStoryDetailsForTimelog::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
        
    }
    /**
    * @author Padmaja
    * @Description removing  time log details
    * @return type
    */
    public function RemoveTimelogs($timelog_data){
        try{
         
            $projectId = $timelog_data->projectId;
            $slug = $timelog_data->slug;
            $timelogHours = $timelog_data->workHours;
            $ticketId=$timelog_data->ticketId;
            $timezone=$timelog_data->timeZone;
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
           $activityData= $this->saveActivity($ticketId, $projectId,'TotalTimeLog', $newTotalTime, $collabaratorId,$slug,$timezone);
            $this->saveNotifications($timelog_data, 'TotalTimeLog', $newTotalTime,'TotalTimeLog',$slug); 
            if ($parenTicketInfo["ParentStoryId"] != "") {
                $updateParentTotalTime = TicketCollection::updateTotalTimeLog($projectId, $parenTicketInfo["ParentStoryId"], $total);
            }
                 $updateindivisualTotalTimeLog = TicketCollection::updateTotalTimeLog($projectId, $ticketId, $total);

                return $activityData;
            }
        } catch (\Throwable $ex) {
            Yii::error("TimeReportService:RemoveTimelogs::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
        
    }
   /**
    * @author Padmaja
    * @param type $ticketData
    * @return type
    * @Description Updtes time log
    */
    public function updateTimelog($ticketData){
        try{
        
            $projectId = $ticketData->projectId;
            $slug = $ticketData->slug;
            $totalWorkHours = $ticketData->timelogHours;
            $collabaratorId=$ticketData->userInfo->Id;
            $timezone=$ticketData->timeZone;
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
                if (empty($autocompleteticketId) || $oldTicketId == $autocompleteticketId) { // If old ticketId and New Id are same 
                    if ($oldWorkHours != $totalWorkHours) {
                        $temphours = $totalWorkHours - $oldWorkHours; // newone -old one
                        $parenTicketInfo = TicketCollection::getTicketDetails($oldTicketId, $projectId, array("ParentStoryId", "TotalTimeLog"));
                        $ticketTimeLog = $parenTicketInfo["TotalTimeLog"];
                        $ticketNewTimeLog = $ticketTimeLog + $temphours;
                        $slug = new \MongoDB\BSON\ObjectID();
                        $activityData = $this->saveActivity($oldTicketId, $projectId, 'TotalTimeLog', (float) $ticketNewTimeLog, $collabaratorId, $slug, $timezone);
                        $ticketData->ticketId = $oldTicketId;
                        $this->saveNotifications($ticketData, 'TotalTimeLog', (float) $ticketNewTimeLog, 'TotalTimeLog', $slug);
                        if ($parenTicketInfo["ParentStoryId"] != "") {
                            $updateParentTotalTime = TicketCollection::updateTotalTimeLog($projectId, $parenTicketInfo["ParentStoryId"], $temphours);
                        }
                        $updateindivisualTotalTimeLog = TicketCollection::updateTotalTimeLog($projectId, $oldTicketId, $temphours);
                    }
                } elseif ($autocompleteticketId != $oldTicketId) {
                    if ($autocompleteticketId) { // For the new one
                        $temphours = $totalWorkHours;
                        $parenTicketInfo = TicketCollection::getTicketDetails($autocompleteticketId, $projectId, array("ParentStoryId", "TotalTimeLog"));

                        $ticketTimeLog = $parenTicketInfo["TotalTimeLog"];
                        $ticketNewTimeLog = $ticketTimeLog + $temphours;

                        $activityData = $this->saveActivity($autocompleteticketId, $projectId, 'TotalTimeLog', (float) $ticketNewTimeLog, $collabaratorId, $slug, $timezone);
                        $ticketData->ticketId = $autocompleteticketId;
                        $this->saveNotifications($ticketData, 'TotalTimeLog', (float) $ticketNewTimeLog, 'TotalTimeLog', $slug);

                        if ($parenTicketInfo["ParentStoryId"] != "") {
                            $updateParentTotalTime = TicketCollection::updateTotalTimeLog($projectId, $parenTicketInfo["ParentStoryId"], $temphours);
                        }
                        $updateindivisualTotalTimeLog = TicketCollection::updateTotalTimeLog($projectId, $autocompleteticketId, $temphours);
                    }
                    if ($oldTicketId) { // For the old one
                        $temphours = (-$oldWorkHours);
                        $parenTicketInfo = TicketCollection::getTicketDetails($oldTicketId, $projectId, array("ParentStoryId", "TotalTimeLog"));
                        $ticketTimeLog = $parenTicketInfo["TotalTimeLog"];
                        $ticketNewTimeLog = $ticketTimeLog + $temphours;
                        $activityData = $this->saveActivity($oldTicketId, $projectId, 'TotalTimeLog', (float) $ticketNewTimeLog, $collabaratorId, $slug, $timezone);
                        $ticketData->ticketId = $oldTicketId;
                        $this->saveNotifications($ticketData, 'TotalTimeLog', (float) $ticketNewTimeLog, 'TotalTimeLog', $slug);

                        if ($parenTicketInfo["ParentStoryId"] != "") {
                            $updateParentTotalTime = TicketCollection::updateTotalTimeLog($projectId, $parenTicketInfo["ParentStoryId"], $temphours);
                        }
                        $updateindivisualTotalTimeLog = TicketCollection::updateTotalTimeLog($projectId, $oldTicketId, $temphours);
                    }
                }

                return $slug;

            }
        } catch (\Throwable $ex) {
            Yii::error("TimeReportService:updateTimelog::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    /**
     * 
     * @param type $userId
     * @param type $projectId
     * @return type
     * @throws ErrorException
     * @Description Gets Current weeks time log
     */
    public function getCurrentWeekTimelog($userId,$projectId=''){
        try{
            $totalWorkLogHours = 0;
            $arrayTimelog = TicketTimeLog::getCurrentWeekTimeLog($userId,$projectId);
            if(count($arrayTimelog)>0){
              $totalWorkLogHours =  number_format(round($arrayTimelog[0]["totalHours"],2),2);
           }
            return $totalWorkLogHours;
        } catch (\Throwable $ex) {
            Yii::error("TimeReportService:getCurrentWeekTimelog::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
        
    } 
    /**
     * 
     * @param type $projectId
     * @return type
     * @Description Get total time logged on all tickets based on the project
     */
    public function getTotalTimeLogByProject($projectId=''){
        try{
             $totalWorkLogHours = 0;
            $arrayTimelog = TicketTimeLog::getTotalTimeLogByProject($projectId);
            if(count($arrayTimelog)>0){
              $totalWorkLogHours =  number_format(round($arrayTimelog[0]["totalHours"],2),2);
           }
            return $totalWorkLogHours;
        } catch (Exception $ex) {

        }
    }
}

  