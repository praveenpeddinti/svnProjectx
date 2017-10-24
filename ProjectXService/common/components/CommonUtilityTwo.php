<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace common\components;
use common\models\mongo\{TicketCollection,TinyUserCollection,TicketArtifacts,EventCollection,TicketTimeLog};
use common\models\mysql\{Priority,Projects,WorkFlowFields,Bucket,TicketType,StoryFields,StoryCustomFields,PlanLevel,MapListCustomStoryFields,ProjectTeam,Collaborators,Filters};
use Yii;
use yii\base\ErrorException;
use common\components\ServiceFactory;

 /*
 * @author Moin Hussain
 */

class CommonUtilityTwo {

    /**
     * @author Moin Hussain
     * @param type $object
     * @param type $type
     * @return type
     */
    public static function prepareResponse($object, $type = "json") {
        if ($type == "json") {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        } else {
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
        try {
            $i = strrpos($str, ".");
            if (!$i) {
                return "";
            }

            $l = strlen($str) - $i;
            $ext = substr($str, $i + 1, $l);
            return $ext;
        } catch (\Throwable $ex) {
            Yii::error("CommonUtilityTwo:getExtension::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
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
        } catch (\Throwable $ex) {
            Yii::error("CommonUtilityTwo:convert_time_zone::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
static function validateDateFormat($date, $format = 'M-d-Y')
{
    $d = \DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}
    /**
     * @author Moin Hussain
     * @param type $date
     * @return type
     */
    public static function validateDate($date) {
        
        if(self::validateDateFormat($date)){
            return FALSE; 
        }
        
        $date = preg_replace("/\([^)]+\)/", "", $date);
        if ((bool) strtotime($date)) {
            return $date;
        } else {
            return FALSE;
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
                return strtotime($time_object->format('M-d-Y H:i:s'));
            } else {
                return $time_object->format('M-d-Y');
            }
        } catch (\Throwable $ex) {
            Yii::error("CommonUtilityTwo:convert_date_zone::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    /**
     * 
     * @param type $bucketDetails
     * @param type $projectId
     * @param type $timezone
     * @param type $bType
     * @return type
     * @throws ErrorException
     * @Description Prepares and returns the Data for rendering Bucket Dashboard.
     */
    public static function prepareBucketDashboardDetails($bucketDetails, $projectId,$timezone,$bType) {
        try {
            $closeDate='';
            $nowDate = date('M-d-Y');
            $milestoneMessage ='';
            if(!empty($bucketDetails["StartDate"])){
            $startDateTime = strtotime($bucketDetails["StartDate"]);
            $startDate = date('M-d-Y',$startDateTime);
            }else{
              $startDate = $bucketDetails["StartDate"];
            }
            if(!empty($bucketDetails["DueDate"])){
            $dueDateTime = strtotime($bucketDetails["DueDate"]);
            $dueDate = date('M-d-Y',$dueDateTime);
            }else{
              $dueDate = $bucketDetails["DueDate"];
            }
            if($bucketDetails["BucketStatusName"]=='Current'){
            if(strtotime($dueDate) > strtotime($nowDate))
                $milestoneMessage = "This milestone have passed for due date.";
            }

            $prepareBucketArray = array();
            $prepareBucketArray["chartDetails"]=array();
            $prepareBucketArray['BucketStatus'] = $bucketDetails['BucketStatus'];
            $prepareBucketArray['BucketId'] = $bucketDetails['Id'];
            $prepareBucketArray['BucketName'] = $bucketDetails['Name'];
            $userDetails = TinyUserCollection::getMiniUserDetails($bucketDetails['Responsible']);
            $prepareBucketArray["ProfilePicture"] = $userDetails["ProfilePicture"];
            $prepareBucketArray["UserName"] = $userDetails["UserName"];
            $prepareBucketArray["StartDate"] = $startDate;
            $prepareBucketArray["DueDate"] = $dueDate;
            $shortBucketDesc= CommonUtilityTwo::truncateHtml($bucketDetails["Description"],50);
            $prepareBucketArray["Description"] = $bucketDetails["Description"];
            $prepareBucketArray["ShortDescription"] = $shortBucketDesc;
            $prepareBucketArray['ResponsibleUser'] =$bucketDetails["Responsible"];
            $prepareBucketArray['DropDownBucket'] ="none";
            $prepareBucketArray['BucketStatusName'] = $bucketDetails["BucketStatusName"];
            $prepareBucketArray['milestoneMessage'] = $milestoneMessage;
            $prepareBucketArray["chartDetails"]["statusCounts"] = CommonUtilityTwo::getStatusCount($projectId, $bucketDetails['Id']);
            $prepareBucketArray["chartDetails"]["stateCounts"] = CommonUtilityTwo::getBucketStatesCount($projectId, $bucketDetails['Id']);
            $prepareBucketArray["topTicketStats"] = CommonUtilityTwo::getTopTicketsStats($projectId,'', $bucketDetails['Id']);
            return $prepareBucketArray;
        } catch (\Throwable $ex) {
            Yii::error("CommonUtilityTwo:prepareBucketDashboardDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

     /**
     * @author Padmaja
     * @Description This method is used to get Ticket details for dashboard
     * @return type $userId
     * @return type $page
     * @return type $pageLength
     * @return type $projectFlag
     */
    public static function getTicketDetailsForDashboard($postData){
        try{
            
            $userId = $postData->userInfo->Id;
            $page=$postData->page;
            $projectFlag=!empty($postData->projectFlag)?$postData->projectFlag:"";
            $pageLength=!empty($postData->limit)?$postData->limit:"";
            $projectId=!empty($postData->ProjectId)?$postData->ProjectId:"";
            $activityDropdownFlag=!empty($postData->activityDropdownFlag)?$postData->activityDropdownFlag:"";
            $activityPage=$postData->activityPage;
            
            
                $collection = Yii::$app->mongodb->getCollection('TicketCollection');
                $assignedtoDetails =  $collection->count(array('$or'=>array( array( "Fields.assignedto.value"=>(int)$userId))));
                $followersDetails =  $collection->count(array('$or'=>array(array("Followers.FollowerId"=>(int)$userId))));
             if($assignedtoDetails !=0 || $followersDetails != 0){
                 $activitiesArray=array();
                 $getActivities=array();
                 $activityDetails=array();
                 $projectData=array();
               $projectDetails = ProjectTeam::getAllProjects($userId,$pageLength,$page);
                if($projectFlag==1){   
                   $projectData= self::prepareProjectsForUserDashboard($collection,$projectDetails,$userId);
                }elseif($projectFlag==2){
                     $activityDetails= self::getAllProjectActivities($postData);
                 }else{
                  $projectData= self::prepareProjectsForUserDashboard($collection,$projectDetails,$userId);
                }
             }
             return array('AssignedToData'=>$assignedtoDetails,'FollowersDetails'=>$followersDetails,'ProjectwiseInfo'=>$projectData,'ActivityData'=>$activityDetails,'projectFlag'=>$projectFlag);  
        } catch (\Throwable $ex) {
            Yii::error("CommonUtilityTwo:getTicketDetailsForDashboard::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
         
    }
    /**
     * @author Padmaja
     * @Description This method is used to prepare Project details for dashboard
     * @return type $collection
     * @return type $projectDetails
     * @return type $assignedtoDetails 
     * @return type $followersDetails
     * @return type $userId
     * @modifiedBy Anand
     */
    public static function prepareProjectsForUserDashboard($projectDetails,$userId){        
        try{   
                $prepareDetails=array();
                $topTicketsArray= array();
                $topTickets='';
                  foreach($projectDetails as $extractDetails){
                     $topTickets='';
                     $projects=Projects::getProjectMiniDetails($extractDetails['ProjectId']);
                     $userDetails=Collaborators::getCollaboratorById($projects['CreatedBy']);
                     $projectInfo['projectId']=$extractDetails['ProjectId'];
                     $projectInfo['createdBy']=$userDetails['UserName'];
                     $projectInfo['projectName']=$projects['ProjectName'];
                     $projectInfo['createdOn'] =$extractDetails['CreatedOn'];
                     $projecTeam=ProjectTeam::getProjectTeamCount($extractDetails['ProjectId']);
                     $projectInfo['team']=$projecTeam['TeamCount'];
                     $projectInfo['topTickets'] = self::getTopTicketsStats($extractDetails['ProjectId'],$userId);
                     $projectInfo['weeklyProjectTimeLog'] =  ServiceFactory::getTimeReportServiceInstance()->getCurrentWeekTimeLog($userId,$extractDetails['ProjectId']);
                     $bucketDetails=Bucket::getProjectBucketByAttributes($extractDetails['ProjectId'],2);
                      if($bucketDetails=='failure'){
                          $projectInfo['currentBucket'] ='';
                     }else{
                      $projectInfo['currentBucket'] =$bucketDetails;
                     }
                     $projectInfo['storyPoints']=  self::getTotalStoryPoints($extractDetails['ProjectId'],$userId);
                      array_push($prepareDetails,$projectInfo);
                    }
                    return $prepareDetails;
             } catch (\Throwable $ex) {
            Yii::error("CommonUtilityTwo:prepareProjectDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
     /**
     * @author Padmaja
     * @Description This method is used to get Last Id Project details for dashboard
     * @return type $projectId
     *  @return type $userId
     */
    public static function getLastProjectDetails($projectId,$userId){
        try{
            $projectInfo=array();
            $prepareDetails=array();
            $projectDetails=Projects::getProjectMiniDetails($projectId);
            $projectInfo['projectName']=$projectDetails['ProjectName'];
            $userDetails=Collaborators::getCollaboratorById($projectDetails['CreatedBy']);
            $projectInfo['createdBy']=$userDetails['UserName'];
            $projectInfo['CreatedOn'] =$projectDetails['CreatedOn'];
            $projectTeamDetails=Collaborators::getFilteredProjectTeam($projectId,$userDetails['UserName']);
            $projectInfo['ProfilePic']=$projectTeamDetails[0]['ProfilePic'];
            $projecTeam=ProjectTeam::getProjectTeamCount($projectId);
            $projectInfo['Team']=$projecTeam['TeamCount'];
            $collection = Yii::$app->mongodb->getCollection('TicketCollection');
            $projectInfo['assignedtoDetails'] =  $collection->count(array('$or'=>array( array( "Fields.assignedto.value"=>(int)$userId,"ProjectId"=>(int)$projectId))));
            $projectInfo['followersDetails'] =  $collection->count(array('$or'=>array(array("Followers.FollowerId"=>(int)$userId,"ProjectId"=>(int)$projectId))));
            $projectInfo['closedTickets'] =TicketCollection::getActiveOrClosedTicketsCount($projectId,$userId,'Fields.state.value',6,array());
            $projectInfo['activeTickets'] =TicketCollection::getActiveOrClosedTicketsCount($projectId,$userId,'Fields.state.value',3,array());
            $bucketDetails=Bucket::getActiveBucketId($projectId);
            if($bucketDetails=='failure'){
                $projectInfo['currentBucket'] ='';
            }else{
               $projectInfo['currentBucket'] =$bucketDetails['Name'];
            }
            array_push($prepareDetails,$projectInfo);
            $assignedtoDetails =  $collection->count(array('$or'=>array( array( "Fields.assignedto.value"=>(int)$userId))));
            $followersDetails =  $collection->count(array('$or'=>array(array("Followers.FollowerId"=>(int)$userId))));
            $totalProjects=ProjectTeam::getProjectsCountByUserId($userId);
            $totalProjectCount=count($totalProjects);
            return array('AssignedToData'=>$assignedtoDetails,'FollowersDetails'=>$followersDetails,'ProjectwiseInfo'=>$prepareDetails,'TotalProjectCount'=>$totalProjectCount);  
            
        } catch (\Throwable $ex) {
            Yii::error("CommonUtilityTwo:getLastProjectDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
        
    }
     /**
     * @author Padmaja
     * @Description This method is used to get Last Id Project details for project dashboard
     * @return type $projectId
     *  @return type $userId
     */
    public static function getProjectDetailsForProjectDashboard($projectId,$userId,$page){
        try{
            $projectInfo=array();
            $prepareDetails=array();
            $userInfo=array();
            $extractUserInfo=array();
            $projectDetails=Projects::getProjectMiniDetails($projectId);
            $projectInfo['projectName']=$projectDetails['ProjectName'];
            
            $projecTeam=ProjectTeam::getProjectTeamCount($projectId);
            $projectInfo['Team']=$projecTeam['TeamCount'];
            $projectInfo['closedTickets'] =TicketCollection::getTicketsCountByStatus($projectId,'Fields.state.value',6);
            $projectInfo['InProgress'] =TicketCollection::getTicketsCountByStatus($projectId,'Fields.state.value',3);
            $projectInfo['New'] =TicketCollection::getTicketsCountByStatus($projectId,'Fields.state.value',"New");
            $projectInfo['weeklyProjectTimeLog']    =  ServiceFactory::getTimeReportServiceInstance()->getCurrentWeekTimeLog('',$projectId);
            $projectInfo['totalProjectTimeLog']     =  ServiceFactory::getTimeReportServiceInstance()->getTotalTimeLogByProject($projectId);
            $projectInfo['topTickets'] = self::getTopTicketsStats($projectId,$userId);
            $projectInfo['allTickets'] =TicketCollection::getAllTicketsCountByProject($projectId);
            $currentActiveUsers      =  EventCollection::getCurrentWeekActiveUsers($projectId);
            if(!empty($currentActiveUsers)){
                $userIdArray= array_unique($currentActiveUsers[0]['data']);
                foreach($userIdArray as $collabaratorId){
                      $userDetails=TinyUserCollection::getMiniUserDetails($collabaratorId);
                      $extractUserInfo['ProfilePicture']=$userDetails['ProfilePicture'];
                      $extractUserInfo['UserName']=$userDetails['UserName'];
                      array_push($userInfo,$extractUserInfo); 
                }
            $projectInfo['userInfo']=$userInfo;
            
            }else{
               $projectInfo['userInfo']=array();  
            }
            array_push($prepareDetails,$projectInfo);
          
            return array('ProjectDetails'=>$prepareDetails);  
            
        } catch (\Throwable $ex) {
            Yii::error("CommonUtilityTwo:getProjectDetailsForProjectDashboard::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
           throw new ErrorException($ex->getMessage());
        }
        
    }

    /**
     * @Descriptiuon show the short desc in that html tags
     */
    static function truncateHtml($text, $length, $ending = 'Read more', $exact = true, $considerHtml = true, $customizedHtml = "") {
        try {
            if ($considerHtml) {
                // if the plain text is shorter than the maximum length, return the whole text
                if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
                    return $text;
                }
                // splits all html-tags to scanable lines
                preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);
                $total_length = strlen($ending);
                $open_tags = array();
                $truncate = '';
                foreach ($lines as $line_matchings) {
                    // if there is any html-tag in this line, handle it and add it (uncounted) to the output
                    if (!empty($line_matchings[1])) {
                        // if it's an "empty element" with or without xhtml-conform closing slash
                        if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
                            // do nothing
                            // if tag is a closing tag
                        } else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
                            // delete tag from $open_tags list
                            $pos = array_search($tag_matchings[1], $open_tags);
                            if ($pos !== false) {
                                unset($open_tags[$pos]);
                            }
                            // if tag is an opening tag
                        } else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
                            // add tag to the beginning of $open_tags list
                            array_unshift($open_tags, strtolower($tag_matchings[1]));
                        }
                        // add html-tag to $truncate'd text
                        $truncate .= $line_matchings[1];
                    }
                    /*FOR Readmore #7699 Issue with Web preview Url - STARTS */
                   if(preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $line_matchings[2], $match)){                    
                        if(count($match[0])>0){
                            $truncateweburl = implode($match[0],"");                    
                            if($truncateweburl !="") 
                            $line_matchings[2] = str_replace($truncateweburl, "", $line_matchings[2]);

                        }
                    }                    
                /*FOR Readmore #7699 Issue with Web preview Url - ENDS */
                    // calculate the length of the plain text part of the line; handle entities as one character
                    $content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
                    if ($total_length + $content_length > $length) {
                        // the number of characters which are left
                        $left = $length - $total_length;
                        $entities_length = 0;
                        // search for html entities
                        if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
                            // calculate the real length of all entities in the legal range
                            foreach ($entities[0] as $entity) {
                                if ($entity[1] + 1 - $entities_length <= $left) {
                                    $left--;
                                    $entities_length += strlen($entity[0]);
                                } else {
                                    // no more characters left
                                    break;
                                }
                            }
                        }
                        $truncate .= substr($line_matchings[2], 0, $left + $entities_length);
                        // maximum lenght is reached, so get off the loop
                        break;
                    } else {
                        $truncate .= $line_matchings[2];
                        $total_length += $content_length;
                    }
                    // if the maximum length is reached, get off the loop
                    if ($total_length >= $length) {
                        break;
                    }
                }
            } else {
                if (strlen($text) <= $length) {
                    return $text;
                } else {
                    $truncate = substr($text, 0, $length - strlen($ending));
                }
            }
                /*FOR Readmore #7699 Issue with Web preview Url - STARTS */
              if(!preg_match('/\s/',$line_matchings[2])){
                  return rtrim($text,'<br>');
               }
                   /*FOR Readmore #7699 Issue with Web preview Url - E */
            // if the words shouldn't be cut in the middle...
            // ...search the last occurance of a space...
            $spacepos = strrpos($truncate, ' ');
            if (isset($spacepos)) {
                // ...and cut the text in this position
                $truncate = substr($truncate, 0, $spacepos);
            }
            // add the defined ending to the text
            if ($considerHtml) {
                // close all unclosed html-tags
                $totalTags = count($open_tags);
                if ($totalTags == 0) {
                    $truncate.=$customizedHtml;
                } else {
                    $i = 0;
                    foreach ($open_tags as $tag) {
                        $i++;
                        if ($i == $totalTags) {
                            $truncate.=$customizedHtml;
                        }
                        $truncate .= '</' . $tag . '>';
                    }
                }
            }
            return $truncate;
        } catch (\Throwable $ex) {
            Yii::error("CommonUtilityTwo:truncateHtml::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    /**
     * @author Padmaja
     * @Description This method is used to get all project Activities
     * @param type $projectData
     * @return type $userId
     */
  public static function getAllProjectActivities($postData){
        try{
                    $activitiesArray=array();
                    $getActivities=array();
                    $activityDetails=array();
                    $getEventDetails= EventCollection::getAllActivities($postData);
                    $timezone=$postData->timeZone;
                    $setNewVal='';
                   foreach($getEventDetails as $extractedEventDetails){
                           $getActivities=array();
                           $activitiesArray= $extractedEventDetails;
                           $getActivities['ProjectId']=$activitiesArray['ProjectId'];
                           $projectDetails = Projects::getProjectMiniDetails($activitiesArray['ProjectId']);
                           $getActivities['ProjectName'] = $projectDetails['ProjectName']; 
                           $getActivities['OccuredIn']=$activitiesArray['OccuredIn'];
                           if($getActivities['OccuredIn']=='Ticket'){
                                $selectFields = ['Title','Fields.planlevel.value_name'];
                                $getTicketDetails = TicketCollection::getTicketDetails($activitiesArray['ReferringId'],$activitiesArray['ProjectId'],$selectFields);
                                $setNewVal=$getTicketDetails['Title'];
                                $getActivities['Title']='#'.$activitiesArray['ReferringId'].' '.$getTicketDetails['Title'];
                                $getActivities['planlevel'] = $getTicketDetails['Fields']['planlevel']['value_name'];
                                $getActivities['occuredIn']=$getActivities['OccuredIn'];
                                error_log("here343--".$getActivities['Title']);
                            }
                            else if($getActivities['OccuredIn']=='Bucket'){
                              $getBucketDetails =Bucket::getBucketName($activitiesArray['ReferringId'],$activitiesArray['ProjectId']);
                              $getActivities['Title']=!empty($getBucketDetails['Name'])?$getBucketDetails['Name']:'';
                              $getActivities['planlevel']='';
                              $setNewVal=$getActivities['Title'];
                              $getActivities['occuredIn']=$getActivities['OccuredIn'];
                            }
                            else if($getActivities['OccuredIn']=='Project'){
                                $getActivities['Title']=!empty($projectDetails['ProjectName'])?$projectDetails['ProjectName']:'';
                                $getActivities['planlevel']='';
                                $setNewVal=$getActivities['Title'];
                                $getActivities['occuredIn']=$getActivities['OccuredIn'];
                            }
                                
                           $getActivities['ReferringId']=$activitiesArray['ReferringId'];
                           $getActivities['DisplayAction']=$activitiesArray['DisplayAction'];
                           $getActivities['ActionType']=$activitiesArray['ActionType'];
                           $getActivities['ActionBy']=$activitiesArray['ActionBy'];
                           $tinyUserDetails=TinyUserCollection::getMiniUserDetails($activitiesArray['ActionBy']);
                           $getActivities['userName']=$tinyUserDetails['UserName'];
                           $getActivities['ProfilePicture']=$tinyUserDetails['ProfilePicture'];
                           $getActivities['Miscellaneous']=$activitiesArray['Miscellaneous'];
                           $datetime1=$activitiesArray['CreatedOn']->toDateTime();
                           $datetime1->setTimezone(new \DateTimeZone($timezone));
                           $getActivities['createdDate']= $datetime1->format('M-d-Y');
                           $Date = $datetime1->format('Y-m-d H:i:s');
                           $getActivities['dateOnly'] =$datetime1->format('Y-m-d');
                          $getActivities['time']= $Date;
                          $getActivities['ChangeSummary'] = array();
                           foreach($activitiesArray['ChangeSummary'] as $changeSummary){
                               $summary = array();
                               $summary['ActionOn']=!empty($changeSummary['ActionOn'])?$changeSummary['ActionOn']:'';
                               $actionOn=$summary['ActionOn'];
                               error_log("aa@@@@@@@@---==========================--"."###".$actionOn);
                                if( $summary['ActionOn']=='duedate'){
                                $prepare_text = Yii::t('app','dueDate');
                                $summary['prepare_text']= $prepare_text;
                                $summary['prepostion']= '';
                                $summary['action']='';
                                if(!empty($changeSummary['OldValue'])){
                                    $datetime1=$changeSummary['OldValue']->toDateTime();
                                    $datetime1->setTimezone(new \DateTimeZone($timezone));
                                    $summary['OldValue']= $datetime1->format('M-d-Y');
                                    $summary['OldValueText']='from';
                                    error_log("@@@@-----". $summary['OldValue']);
                                }else{
                                    $summary['OldValue'] ='';
                                    $summary['OldValueText']='';
                                }
                                 if(!empty($changeSummary['NewValue'])){
                                    $datetime1=$changeSummary['NewValue']->toDateTime();
                                    $datetime1->setTimezone(new \DateTimeZone($timezone));
                                    $summary['NewValue']= $datetime1->format('M-d-Y');
                                    error_log("@@@@-----". $summary['NewValue']);
                                     $summary['NewValueText']='to';
                                }else{
                                    error_log("@@@dgdfg-----");
                                    $summary['NewValue'] ='';
                                    $summary['NewValueText']='';
                                }  
                                $summary['prepostion']= 'for';
                               }else if($summary['ActionOn']=='totaltimelog' ){
                                  $prepare_text = Yii::t('app','TotalTimeLog');
                                  $summary['prepare_text']= $prepare_text;
                                  $summary['action']='Total Time Log';   
                                   if(!empty($changeSummary['OldValue'])){
                                       $summary['OldValue']=number_format(round($changeSummary['OldValue'],2), 1);
                                       $summary['OldValueText']='from';
                                   }else{
                                       $summary['OldValue'] ='';
                                       $summary['OldValueText']='';
                                   }
                                    if(!empty($changeSummary['NewValue'])){
                                       $summary['NewValue']= number_format(round($changeSummary['NewValue'],2), 1);             
                                        $summary['NewValueText']='to';
                                   }else{
                                       $summary['NewValue'] ='';
                                       $summary['NewValueText']='';
                                   }
                                 $summary['prepostion']= 'for';
                               }elseif($summary['ActionOn']=='estimatedpoints' || $summary['ActionOn']=='dod' ){
                                    $prepare_text = (!empty($changeSummary['OldValue']))? Yii::t('app','TotalTimeLog'):Yii::t('app','set');
                                    $summary['action']=Yii::t('app',$summary['ActionOn']);
                                   if(!empty($changeSummary['OldValue'])){
                                       $summary['OldValue']=$changeSummary['OldValue'];
                                       $summary['OldValueText']='from';
                                   }else{
                                       $summary['OldValue'] ='';
                                       $summary['OldValueText']='';
                                   }
                                    if(!empty($changeSummary['NewValue'])){
                                       $summary['NewValue']=$changeSummary['NewValue'];
                                       $summary['NewValueText']='to';
                                   }else{
                                       $summary['NewValue'] ='';
                                       $summary['NewValueText']='';
                                   }
                                    $summary['prepare_text']= $prepare_text;
                                     $summary['prepostion']= 'for';
                               }else if($summary['ActionOn']=='description' || $summary['ActionOn']=='tickettype'||$summary['ActionOn']=='title'){
                                   error_log("@@@------------");
                                    $prepare_text = Yii::t('app','TotalTimeLog');
                                    $summary['prepare_text']= $prepare_text;
                                    $summary['action']=($summary['ActionOn']=='description'||$summary['ActionOn']=='title')?$actionOn:'type';
                                     error_log("@@@----45435435435--------".$summary['action']);
                                    if(!empty($changeSummary['OldValue'])){
                                        if($summary['ActionOn']=='tickettype'){
                                            $tickettypeDetail = TicketType::getTicketType($changeSummary['OldValue']);
                                            $summary['OldValue']= $tickettypeDetail["Name"];
                                            
                                        }else{
                                       $summary['OldValue']=CommonUtility::refineActivityData($changeSummary['OldValue'], 80);
                                        }
                                       $summary['OldValueText']='from';
                                        
                                       
                                    }else{
                                       $summary['OldValue'] ='';
                                       $summary['OldValueText']='';
                                   }
                                    if(!empty($changeSummary['NewValue'])){
                                         if($summary['ActionOn']=='tickettype'){
                                            $tickettypeDetail = TicketType::getTicketType($changeSummary['NewValue']);
                                            $summary['NewValue']= $tickettypeDetail["Name"];
                                        }else{
                                       $summary['NewValue']=CommonUtility::refineActivityData($changeSummary['NewValue'], 80);
                                        }
                                       $summary['NewValueText']='to';
                                       
                                    }else{
                                       $summary['NewValue'] ='';
                                       $summary['NewValueText']='';
                                   }
                                    $summary['prepostion']= 'for';
                               }else if($summary['ActionOn']=='projectcreation' ||$summary['ActionOn']=='bucketcreation'){
                                   error_log("here454545-----".$summary['ActionOn']);
                                  $prepare_text = Yii::t('app','created');
                                  $summary['action']='';
                                    if(!empty($changeSummary['OldValue'])){
                                        
                                       $summary['OldValue']=$setNewVal;
                                       $summary['OldValueText']='from';
                                   }else{
                                       $summary['OldValue'] ='';
                                       $summary['OldValueText']='';
                                   }
                                    if(!empty($changeSummary['NewValue'])){
                                       $summary['NewValue']=$setNewVal;
                                       if($summary['ActionOn']=='projectcreation'){
                                          $summary['NewValueText']= 'a Project';
                                       }else if($summary['ActionOn']=='bucketcreation'){
                                           $summary['NewValueText']= 'a Bucket';
                                       }else{
                                           $summary['NewValueText']='a';
                                       }
                                    }else{
                                       $summary['NewValue'] ='';
                                       $summary['NewValueText']=''; 
                                   }
                                    $summary['prepare_text']= $prepare_text;
                                    $summary['prepostion']= 'for';
                               }else if($summary['ActionOn']=='comment'){
                                   error_log("jhhhk-------------------comment__comment");
                                   error_log("@@@------333333333333--------".$getActivities['ActionType']);
                                    if($getActivities['ActionType']=='comment'){
                                    $prepare_text =(strpos($changeSummary['NewValue'], '@')!==false)? (Yii::t('app','mention')):(Yii::t('app','comment'));
                                    }elseif($getActivities['ActionType']=='repliedOn'){
                                        $prepare_text =(strpos($changeSummary['NewValue'], '@')!==false)? (Yii::t('app','mention')):(Yii::t('app','reply'));
                                    }elseif($getActivities['ActionType']=='edit'){
                                        $prepare_text = Yii::t('app','edit');
                                    }
                                    elseif($getActivities['ActionType']=='delete'){
                                        $prepare_text = Yii::t('app','delete');
                                    }
                                    $summary['action']='';
                                    if($getActivities['ActionType']=='comment'){
                                        error_log("%%%%%%%%%%%%%%%%%%%%%%5");
                                        if(!empty($changeSummary['OldValue'])){
                                            
                                           $summary['OldValue']=CommonUtility::refineActivityData($changeSummary['OldValue'], 80);
                                           $summary['OldValueText']='from';
                                       }else{
                                           $summary['OldValue'] ='';
                                           $summary['OldValueText']='';
                                       }
                                        if(!empty($changeSummary['NewValue'])){
                                           $summary['NewValue']=($getActivities['ActionType']=='repliedOn')?'':CommonUtility::refineActivityData($changeSummary['NewValue'], 80);
                                           $summary['NewValueText']=($getActivities['ActionType']=='repliedOn')?'':'';
                                       }else{
                                           $summary['NewValue'] ='';
                                           $summary['NewValueText']='';
                                       }
                                    }else{
                                          error_log("%%%%%%%%%%%%%%%%%%%%%%5-------------");
                                        $summary['OldValue']='';
                                        $summary['OldValueText']='';
                                        $summary['NewValue'] ='';
                                        $summary['NewValueText']='';
                                    }
                                    $summary['prepare_text']= $prepare_text;
                                    $summary['prepostion']= 'on';
                               }else if($summary['ActionOn']=='bucket'|| $summary['ActionOn']=='workflow' || $summary['ActionOn']=='priority'){
                                   error_log("jhhhk-------------------");
                                     $prepare_text = Yii::t('app','TotalTimeLog');
                                    $summary['action']=($summary['ActionOn']=='workflow'?'work flow':$actionOn);
                                    error_log("changesummery55555555555-------".$changeSummary['OldValue']);
                                   if($summary['ActionOn']=='bucket'){
                                        if(!empty($changeSummary['OldValue'])){
                                           $summary['OldValue']=$changeSummary['OldValue'];
                                           $summary['OldValueText']='from';
                                       }else{
                                           $summary['OldValue'] ='';
                                           $summary['OldValueText']='';
                                       }
                                        if(!empty($changeSummary['NewValue'])){
                                           $summary['NewValue']=CommonUtility::refineActivityData($changeSummary['NewValue'], 80);
                                           $summary['NewValueText']='to';
                                       }else{
                                           $summary['NewValue'] ='';
                                           $summary['NewValueText']='';
                                       }
                                   }else{
                                       if(!empty($changeSummary['OldValue'])){
                                            $priorityDetail = Priority::getPriorityDetails($changeSummary['OldValue']);
                                           error_log("@@@@@@@@@--------helloo---+++--------".$priorityDetail["Name"]);
                                            $extractFields=($summary['ActionOn']=='workflow')?(WorkFlowFields::getWorkFlowDetails($changeSummary['OldValue'])):(Priority::getPriorityDetails($changeSummary['OldValue']));
                                           $summary['OldValue'] =$extractFields["Name"];
                                           $summary['OldValueText']='from';
                                       }else{
                                           $summary['OldValue'] ='';
                                           $summary['OldValueText']='';
                                       }
                                        if(!empty($changeSummary['NewValue'])){
                                            $priorityDetail = Priority::getPriorityDetails($changeSummary['NewValue']);
                                           error_log("@@@@@@@@@--------helloo---+++--------".$priorityDetail["Name"]);
                                           $extractFields=($summary['ActionOn']=='workflow')? WorkFlowFields::getWorkFlowDetails($changeSummary['NewValue']):(Priority::getPriorityDetails($changeSummary['NewValue']));
                                           $summary['NewValue'] =$extractFields["Name"];
                                           $summary['NewValueText']='to';
                                       }else{
                                           $summary['NewValue'] ='';
                                           $summary['NewValueText']='';
                                       } 
                                   }
                                 $summary['prepare_text']= $prepare_text;
                                 $summary['prepostion']= 'for';
                               }else if($summary['ActionOn']=='relatetask'|| $summary['ActionOn']=='unrelatetask'){
                                   error_log("jhhhk----------^^^^^^^^^^^^^---------");
                                    
                                     if($summary['ActionOn']=='relatetask'){
                                         $prepare_text = Yii::t('app','related');
                                    }else{
                                         $prepare_text = Yii::t('app','unrelated');
                                    }
                                     $summary['action']='';
                                    if(!empty($changeSummary['OldValue'])){
                                       $summary['OldValue']=$changeSummary['OldValue'];
                                       $summary['OldValueText']='from';
                                   }else{
                                       $summary['OldValue'] ='';
                                       $summary['OldValueText']='';
                                   }
                                    if(!empty($changeSummary['NewValue'])){
                                        $summary['NewValue']=$setNewVal;
                                        $summary['NewValueText']='to';
                                   }else{
                                       $summary['NewValue'] ='';
                                       $summary['NewValueText']='';
                                   }
                                    $summary['prepare_text']= $prepare_text;
                                    $summary['prepostion']= 'for';
                               }else if($summary['ActionOn']=='assignedto'|| $summary['ActionOn']=='stakeholder'){
                                   error_log("jhhhk----------^^^^^^^^^^^^^---------");
                                    $prepare_text = Yii::t('app','assigned');
                                     if($summary['ActionOn']=='stakeholder'){
                                         $prepare_text = Yii::t('app','stakeholder');
                                    }
                                    $summary['action']='';
                                    if(!empty($changeSummary['OldValue'])){
                                       $summary['OldValue']='';
                                       $summary['OldValueText']='';
                                   }else{
                                       $summary['OldValue'] ='';
                                       $summary['OldValueText']='';
                                   }
                                    if(!empty($changeSummary['NewValue'])){
                                        $tinyUserDetails=TinyUserCollection::getMiniUserDetails($changeSummary['NewValue']);
                                        $setNewVal=$tinyUserDetails['UserName'];
                                        error_log("setNew--@@@@@@@@@@@@@@@@@@@@@@@@--".$setNewVal);
                                        $summary['NewValue']=$setNewVal;
                                        $summary['NewValueText']='';
                                   }else{
                                       $summary['NewValue'] ='';
                                       $summary['NewValueText']='';
                                   }
                                    $summary['prepare_text']= $prepare_text;
                                    $summary['prepostion']= 'to';
                               }elseif($summary['ActionOn']=='follow' || $summary['ActionOn']=='unfollow' ){
                                    $prepare_text = 'has';
                                    $summary['action']=Yii::t('app',$summary['ActionOn']);
                                   if(!empty($changeSummary['OldValue'])){
                                        $tinyUserDetails=TinyUserCollection::getMiniUserDetails($changeSummary['OldValue']);
                                        $setNewVal=$tinyUserDetails['UserName'];
                                        error_log("setNew--@@@@@@@@@@@@@@@@@@@@@@@@--".$setNewVal);
                                       $summary['OldValue']=$setNewVal;
                                       $summary['OldValueText']='';
                                   }else{
                                       $summary['OldValue'] ='';
                                       $summary['OldValueText']='';
                                   }
                                    if(!empty($changeSummary['NewValue'])){
                                       $tinyUserDetails=TinyUserCollection::getMiniUserDetails($changeSummary['NewValue']);
                                       $setNewVal=$tinyUserDetails['UserName'];
                                       $summary['NewValue']=$setNewVal;
                                       $summary['NewValueText']='  as a follwer';
                                   }else{
                                       $summary['NewValue'] ='';
                                       $summary['NewValueText']='';
                                   }
                                    $summary['prepare_text']= $prepare_text;
                                     $summary['prepostion']= $getActivities['DisplayAction'];
                               }elseif($summary['ActionOn']=='childtask' ){
                                     $prepare_text = Yii::t('app','created');
                                  $summary['action']='';
                                    if(!empty($changeSummary['OldValue'])){
                                        
                                       $summary['OldValue']=$setNewVal;
                                       $summary['OldValueText']='from';
                                   }else{
                                       $summary['OldValue'] ='';
                                       $summary['OldValueText']='';
                                   }
                                    if(!empty($changeSummary['NewValue'])){
                                       $selectFields = ['Title','Fields.planlevel.value_name'];
                                     $getTicketDetails = TicketCollection::getTicketDetails($changeSummary['NewValue'],$activitiesArray['ProjectId'],$selectFields);
                                        $setNewVal=$getTicketDetails['Title'];
                                        $summary['NewValue']=$setNewVal;
                                       $summary['NewValueText']='';
                                    }else{
                                       $summary['NewValue'] ='';
                                       $summary['NewValueText']=''; 
                                   }
                                    $summary['prepare_text']= $prepare_text;
                                    $summary['prepostion']= 'for';
                 
                               }else{
                                    error_log("jhhhk------34444444444-------------");
                                    $prepare_text='';
                                    $summary['action']=trim(strtolower(preg_replace('/(?<!\ )[A-Z]/', ' $0', $actionOn)));
                                    if(!empty($changeSummary['OldValue'])){
                                        if($actionOn=='bucketOwner'){
                                         $userDetails = TinyUserCollection::getMiniUserDetails($changeSummary['OldValue']);
                                         $changeSummary['OldValue']=$userDetails['UserName'];
                                         }
                                       $summary['OldValue']=CommonUtility::refineActivityData($changeSummary['OldValue'], 80);
                                       $summary['OldValueText']='from';
                                       $prepare_text = Yii::t('app','TotalTimeLog');// for the text has changed
                                 
                                   }else{
                                        $prepare_text = Yii::t('app','set');
                                        $summary['OldValue'] ='';
                                       $summary['OldValueText']='';
                                   }
                                    if(!empty($changeSummary['NewValue'])){
                                        if($actionOn=='bucketOwner'){
                                         $userDetails = TinyUserCollection::getMiniUserDetails($changeSummary['NewValue']);
                                         $changeSummary['NewValue']=$userDetails['UserName'];
                                         }
                                       $summary['NewValue']=CommonUtility::refineActivityData($changeSummary['NewValue'], 80);
                                        $summary['NewValueText']='to';
                                   }else{
                                       $summary['NewValue'] ='';
                                       $summary['NewValueText']='to';
                                   }
                                    $summary['prepare_text']= $prepare_text;
                                     $summary['prepostion']= 'for';
                               }
                            array_push($getActivities['ChangeSummary'], $summary);  
                           }
                           array_push($activityDetails, $getActivities);
                  }

                    $preparedActivities = array();
                    $finalActivity = array('activityDate' => '', 'activityData' => array());
                    $tempActivity = array();
                        foreach($activityDetails as $item){
                                $tempActivity[$item['dateOnly']][] = $item;
                        }
                    foreach ($tempActivity as $key => $value) {
                        $finalActivity = array('activityDate' => '', 'activityData' => array());
                        $finalActivity = array('activityDate' => $key, 'activityData' => $value);
                        array_push($preparedActivities, $finalActivity);
                    }
                  return array('activities'=>$preparedActivities);  
     } catch (\Throwable $ex) {
            Yii::error("CommonUtilityTwo:getAllProjectActivities::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
   
  }

  /**
   * @author Anand Singh
   * @Description  Gets top tickets statics based on project id,userid or bucket id,
   * @param type $userId
   * @param type $projectId
   * @param type $bucketId
   * @throws ErrorException
   */
  public static function getTopTicketsStats($projectId, $userId = '', $bucketId = '') {

        try{
            $topTicketsArray = array();
            $filters = Filters::getAllActiveFilters();
            $conditions = array('ProjectId' => (int) $projectId);
            $total = $assigned = $followed = '';
            $collection = Yii::$app->mongodb->getCollection('TicketCollection');
            if ($bucketId != ''){ //this is specifically for buckets by Ryan......
                $conditions['Fields.bucket.value'] = (int) $bucketId;
                $tickets_count=$collection->count($conditions);
                $totalStoryPoints=  CommonUtilityTwo::getTotalStoryPoints($projectId,'' , $bucketId); //anand's method call
                error_log("==Tickets Count==".$tickets_count);
                $totalWorkedHours=CommonUtilityTwo::getTotalWorkedHoursByBucket($projectId,$bucketId);
                error_log("==Worked Hours==".$totalWorkedHours);
                $stateCount=CommonUtilityTwo::getBucketStatesCount($projectId,$bucketId);
                
                $conditions['Fields.state.value'] = (int) 3;
                $totalActive = $collection->count($conditions);
                
                $total = $assigned = $followed = '';
                $yesterday = date("Y-m-d H:i:s", strtotime('yesterday'));
                unset($conditions['Fields.state.value']);
                $conditions['Fields.duedate.value'] = array('$lte' => new \MongoDB\BSON\UTCDateTime(strtotime($yesterday) * 1000));
                $totalOverDue = $collection->count($conditions);
                
                 $lastDayOfweek = date("Y-m-d H:i:s", strtotime('next sunday', strtotime('tomorrow')));
                 $conditions['Fields.duedate.value'] = array('$gt' => new \MongoDB\BSON\UTCDateTime(strtotime($yesterday) * 1000), '$lte' => new \MongoDB\BSON\UTCDateTime(strtotime($lastDayOfweek) * 1000));
                 $totalCurrentWeek = $collection->count($conditions);
                 
                $topTickets=array('TicketsCount'=>$tickets_count,'StoryPoints'=>$totalStoryPoints,"WorkedHours"=>$totalWorkedHours,'ActiveTickets'=>$totalActive,'OverDue'=>$totalOverDue,'CurrentOverDue'=>$totalCurrentWeek,'States'=>$stateCount); //padmaja's method call
                array_push($topTicketsArray,$topTickets);
            }else{
            $conditions['$or']=[['Fields.assignedto.value'=>(int)$userId],['Followers.FollowerId'=>(int)$userId]];
            $conditions['Fields.state.value'] = (int) 3;
            $total = $collection->count($conditions);
            unset($conditions['$or']);
            $conditions['Followers.FollowerId'] = (int) $userId;
            $followed = $collection->count($conditions);
            unset($conditions['Followers.FollowerId']);
            $conditions['Fields.assignedto.value'] = (int) $userId;
            $assigned = $collection->count($conditions);
            $topTickets = array("id" => 4,'type'=>'individual', "name" => 'My active stories/task', 'total' => $total, 'assigned' => $assigned, 'followed' => $followed);
            array_push($topTicketsArray, $topTickets);
            $topTickets = '';

            // Over due stories/tasks
            $total = $assigned = $followed = '';
            $yesterday = date("Y-m-d H:i:s", strtotime('yesterday'));
            unset($conditions['Fields.state.value']);
            unset($conditions['Fields.assignedto.value']);
            $conditions['Fields.duedate.value'] = array('$lte' => new \MongoDB\BSON\UTCDateTime(strtotime($yesterday) * 1000));
            $conditions['$or']=[['Fields.assignedto.value'=>(int)$userId],['Followers.FollowerId'=>(int)$userId]];
            $total = $collection->count($conditions);
            unset($conditions['$or']);
            $conditions['Followers.FollowerId'] = (int) $userId;
            $followed = $collection->count($conditions);
            unset($conditions['Followers.FollowerId']);
            $conditions['Fields.assignedto.value'] = (int) $userId;
            $assigned = $collection->count($conditions);
            
            $topTickets = array("id" => 11,'type'=>'individual', "name" => 'My over due stories/tasks', 'total' => $total, 'assigned' => $assigned, 'followed' => $followed);
            array_push($topTicketsArray, $topTickets);
            $topTickets = '';

            //  Due stories/tasks for current week
            $total = $assigned = $followed = '';
            $lastDayOfweek = date("Y-m-d H:i:s", strtotime('next sunday', strtotime('tomorrow')));
             $yesterday = date("Y-m-d H:i:s", strtotime('yesterday'));
            $conditions['Fields.duedate.value'] = array('$gt' => new \MongoDB\BSON\UTCDateTime(strtotime($yesterday) * 1000), '$lte' => new \MongoDB\BSON\UTCDateTime(strtotime($lastDayOfweek) * 1000));
            unset($conditions['Fields.assignedto.value']);
            $conditions['$or']=[['Fields.assignedto.value'=>(int)$userId],['Followers.FollowerId'=>(int)$userId]];
            $total = $collection->count($conditions);
            unset($conditions['$or']);
            $conditions['Followers.FollowerId'] = (int) $userId;
            $followed = $collection->count($conditions);
            unset($conditions['Followers.FollowerId']);
            $conditions['Fields.assignedto.value'] = (int) $userId;
            $assigned = $collection->count($conditions);
            $topTickets = array("id" => 12,'type'=>'individual', "name" => 'My current week due stories/tasks', 'total' => $total, 'assigned' => $assigned, 'followed' => $followed);
            array_push($topTicketsArray, $topTickets);
            $topTickets = '';
            }
            return $topTicketsArray;
        } catch (\Throwable $ex) {
            Yii::error("CommonUtilityTwo:getTopTicketsStats::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    
  
}

/**
 * @author  Anand Singh
 * @param type $activities
 * @return array
 * @throws ErrorException
 * @Description Prepares the data for displaying on User Dashboard.
 */

public static function prepareUserDashboardActivities($activities) {

        try {
            $preparedActivities = array();
            $finalActivity = array('activityDate' => '', 'activityData' => array());
            $tempActivity = array();
            foreach ($activities as $item) {

                $tempActivity[$item['onlyDate']][] = $item;
            }
            foreach ($tempActivity as $key => $value) {
                $finalActivity = array('activityDate' => '', 'activityData' => array());
                $finalActivity = array('activityDate' => $key, 'activityData' => $value);
                array_push($preparedActivities, $finalActivity);
            }

            return $preparedActivities;
        } catch (\Throwable $ex) {
            Yii::error("CommonUtilityTwo:getTopTicketsStats::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
    /**
     * @author Anand Singh
     * @param type $projectId
     * @param type $userId
     * @return type
     * @throws ErrorException
     * @Description Returns the calculated story points for User Dashboard for give project
     */
    public static function getTotalStoryPoints($projectId,$userId='',$bucketId=''){ //modified by Ryan , added 
        try {
           $totalStoryPoints=0;

           if($bucketId!=''){ //by Ryan
               $matchArray = array("ProjectId" => (int) $projectId,
                                   "Fields.bucket.value"=>(int)$bucketId,
                                   '$or'=>array(array("Fields.planlevel.value"=>1),array("Fields.planlevel.value"=>2)),
                                   "IsChild"=>0);
               $_id='$Fields.bucket.value';
           }else{
           $_id='$Fields.assignedto.value';

           $yesterday = date("Y-m-d H:i:s", strtotime('yesterday'));

           $matchArray = array("ProjectId" => (int) $projectId,'$or'=>array(array('Fields.assignedto.value'=>(int)$userId)));
           $matchArray['$and']=[['$or'=>[['Fields.duedate.value'=>array('$gt'=>new \MongoDB\BSON\UTCDateTime(strtotime($yesterday) * 1000))],['Fields.duedate.value'=>'']]]];

           }

             

            $query = Yii::$app->mongodb->getCollection('TicketCollection');
            $pipeline = array(
               
                array('$unwind'=> '$Fields'),
                array('$match' => $matchArray),
                array(
                    '$group' => array(
                         '_id' => $_id,//modified by Ryan
                        "count" => array('$sum' => 1),
                        "totalPoints" => array('$sum' => '$Fields.totalestimatepoints.value'),
                    ),
                ),
            );
            $ArrayStoryPoints = $query->aggregate($pipeline);
            if(count($ArrayStoryPoints)>0){
             $totalCount =  $ArrayStoryPoints[0]["count"];
             $totalStoryPoints =  number_format(round($ArrayStoryPoints[0]["totalPoints"]));
          }
          error_log("==Total Story Points==".$totalStoryPoints);
            return $totalStoryPoints;
        } catch (\Throwable $ex) {
            Yii::error("CommonUtilityTwo:getTotalStoryPoints::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
    /**
     * @author Ryan Marshal
     * @param type $projectId
     * @param type $bucketId
     * @return type
     * @throws ErrorException
     * @Description Gets the totdal worked hours of all the tickets in a Bucket and given project.
     */
    public static function getTotalWorkedHoursByBucket($projectId,$bucketId){
        try{
            $totalWorkedHours=0;
            $matchArray = array("ProjectId" => (int) $projectId,"Fields.bucket.value"=>(int) $bucketId);
            $query = Yii::$app->mongodb->getCollection('TicketCollection');
            $pipeline = array(
               
                array('$unwind'=> '$Fields'),
                array('$match' => $matchArray),
                array(
                    '$group' => array(
                         '_id' => '$Fields.bucket.value',
                        "count" => array('$sum' => 1),
                        "totalHours" => array('$sum' => '$TotalTimeLog'),
                    ),
                ),
            );
             $workedHours = $query->aggregate($pipeline);
            if(count($workedHours)>0){
             $totalCount =  $workedHours[0]["count"];
             error_log("===Total Hours==".$workedHours[0]["totalHours"]);
             $totalWorkedHours =  number_format(round($workedHours[0]["totalHours"]));
          }
          error_log("==Worked Hourssssss==".$totalWorkedHours);
                return $totalWorkedHours;
        } catch (\Throwable $ex) {
            Yii::error("CommonUtilityTwo:getTotalWorkedHoursByBucket::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    /**
     * 
     * @param type $projectId
     * @param type $bucketId
     * @return type
     * @throws ErrorException
     * @Description Returns the Counts of Tickets in each <b>State</b> for displaying in Bucket Dashboard
     */
    public static function getBucketStatesCount($projectId,$bucketId){
        try{
            $states=array();
            $states['New']=0;
            $states['Paused']=0;
            $states['InProgress']=0;
            $states['Waiting']=0;
            $states['Reopened']=0;
            $states['Closed']=0;
            $matchArray = array("ProjectId" => (int) $projectId,"Fields.bucket.value"=>(int) $bucketId,);
            $query = Yii::$app->mongodb->getCollection('TicketCollection');
            $states_query="select * from WorkFlowState";
            $ticketStates=Yii::$app->db->createCommand($states_query)->queryAll();
            foreach($ticketStates as $ticketState){ error_log("Ticket ID==".$ticketState['Id']);
                $matchArray["Fields.state.value"]=(int)$ticketState['Id'];
                $pipeline = array(
               
                array('$unwind'=> '$Fields'),
                array('$match' => $matchArray),
                array(
                    '$group' => array(
                         '_id' => '$Fields.bucket.value',
                        'count'=>array('$sum' => 1), 
                        'data'=>array('$push'=>'$Fields.state.value')
                    ),
                ),
            ); 
                $result=$query->aggregate($pipeline);
                switch($ticketState['Id']){
                    case 1: if(count($result)>0){$states['New']=$result[0]['count'];}break;
                    case 2: if(count($result)>0){$states['Waiting']=$result[0]['count'];}break;
                    case 3: if(count($result)>0){$states['InProgress']=$result[0]['count'];}break;
                    case 4: if(count($result)>0){$states['Paused']=$result[0]['count'];}break;
                    case 6: if(count($result)>0){$states['Closed']=$result[0]['count'];}break;
                    case 7: if(count($result)>0){$states['Reopened']=$result[0]['count'];}break;
                    default:break;
                }
           }
            return $states;
            
        } catch (\Throwable $ex) {
            Yii::error("CommonUtilityTwo:getBucketStatesCount::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    /**
     * 
     * @param type $projectId
     * @param type $bucketId
     * @return int
     * @Description Returns the Counts of Tickets in each <b>Status</b> for displaying in Bucket Dashboard
     */
    public static function getStatusCount($projectId,$bucketId){
        $StatusList = \common\models\mysql\WorkFlowFields::getWorkflowStatusList();
        $matchArray = array("ProjectId" => (int) $projectId,"Fields.bucket.value"=>(int) $bucketId,);
        $query = Yii::$app->mongodb->getCollection('TicketCollection');
        $statusCounts=array();
        foreach ($StatusList as  $value) {
            error_log("------>>>>>>".$value["Id"]);
            $matchArray["Fields.workflow.value"]=(int)$value["Id"];
            $pipeline5 = array(
               
                array('$unwind'=> '$Fields'),
                array('$match' => $matchArray),
                array(
                    '$group' => array(
                         '_id' => '$Fields.workflow.value_name',
                        'count'=>array('$sum' => 1),
                    ),
                ),
            );
            $data = $query->aggregate($pipeline5);
            $key= preg_replace('/\s/','',ucwords(strtolower($value["Name"])));
            if(count($data) > 0){
                $statusCounts[$key] = $data[0]["count"];
            }else{
                $statusCounts[$key] = 0;
            }
            
        }
        return $statusCounts;
        
        
    }
    
}
