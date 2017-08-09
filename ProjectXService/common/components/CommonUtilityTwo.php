<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace common\components;
use common\models\mongo\{TicketCollection,TinyUserCollection,TicketArtifacts,EventCollection};
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
            //$ext .= '_'.$_SESSION['user']->id;
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
            if($bType=='Current'){
            if(strtotime($dueDate) > strtotime($nowDate))
                $milestoneMessage = "This milestone have passed for due date.";
            }
            if($bType=='Closed'){
                $closeDateTime = strtotime($bucketDetails["CloseDate"]);
                $closeDate = date('M-d-Y',$closeDateTime);
                $datediff = (strtotime($closeDate) - strtotime($dueDate));
                $countOfdays = floor($datediff / (60 * 60 * 24));
                if($countOfdays>0)
                $milestoneMessage = "This milestone was closed <span class='title'>$countOfdays</span> days after due date.";
            }
            $prepareBucketArray = array();
            $prepareBucketArray['BucketType'] = $bucketDetails['BucketType'];
            $prepareBucketArray['BucketId'] = $bucketDetails['Id'];
            $prepareBucketArray['BucketName'] = $bucketDetails['Name'];
            $userDetails = TinyUserCollection::getMiniUserDetails($bucketDetails['Responsible']);
            $prepareBucketArray["ProfilePicture"] = $userDetails["ProfilePicture"];
            $prepareBucketArray["UserName"] = $userDetails["UserName"];
            $prepareBucketArray["StartDate"] = $startDate;
            $prepareBucketArray["DueDate"] = $dueDate;
            $prepareBucketArray["CloseDate"] = $closeDate;
            //$shortBucketDesc= CommonUtility::refineActivityDataTimeDesc($bucketDetails["Description"],50);
            $shortBucketDesc= CommonUtilityTwo::truncateHtml($bucketDetails["Description"],50);
            $prepareBucketArray["Description"] = $bucketDetails["Description"];
            $prepareBucketArray["ShortDescription"] = $shortBucketDesc;
            $prepareBucketArray['ResponsibleUser'] =$bucketDetails["Responsible"];
            $prepareBucketArray['BucketType'] =$bucketDetails["BucketType"];
            $prepareBucketArray['EmailNotify'] =$bucketDetails["EmailNotify"];
            $prepareBucketArray['EmailReminder'] =$bucketDetails["EmailReminder"];
            $prepareBucketArray['DropDownBucket'] =(int)0;
            $prepareBucketArray['BucketRole'] = $bType;
            $prepareBucketArray['milestoneMessage'] = $milestoneMessage;
            
            
            $checkTicketsinBuckets = TicketCollection::checkTicketsinBuckets($projectId,$bucketDetails['Id']);
            if(count($checkTicketsinBuckets)==0){
                $prepareBucketArray['AllTasks'] =(int)0;
                $prepareBucketArray['ClosedTasks'] =(int)0;
                $prepareBucketArray['OpenTasks'] =(int)0;
                $prepareBucketArray['TotalHours'] =(int)0;
                $prepareBucketArray['Taskspercentage'] = (int)0;
                
            }else{
                $prepareBucketArray['AllTasks'] =TicketCollection::getAllTicketsCount($projectId,$bucketDetails['Id'],'Fields.bucket.value','Fields.state.value',$taskFlag='All');
                $prepareBucketArray['ClosedTasks'] =TicketCollection::getAllTicketsCount($projectId,$bucketDetails['Id'],'Fields.bucket.value','Fields.state.value',$taskFlag='Closed');
                $prepareBucketArray['OpenTasks'] =TicketCollection::getAllTicketsCount($projectId,$bucketDetails['Id'],'Fields.bucket.value','Fields.state.value',$taskFlag='Open');
                $prepareBucketArray['TotalHours'] =TicketCollection::getTotalWorkHoursForBucket($projectId,$bucketDetails['Id'],'Fields.bucket.value');
                $prepareBucketArray['Taskspercentage'] = (int)round((($prepareBucketArray['ClosedTasks']/$prepareBucketArray['AllTasks'])*100));
                
            }
            //$startDateTime = $ticketDetails["StartDate"];
            //$startDateTime->setTimezone(new \DateTimeZone($timezone));
            //$startDate = $startDateTime->format('M-d-Y');
            //$dueDateTime = $ticketDetails["StartDate"]->toDateTime();
            //$dueDateTime->setTimezone(new \DateTimeZone($timezone));
            //$dueDate = $dueDateTime->format('M-d-Y');
            //error_log("--prapereBucketData-Two--".print_r($prepareBucketArray,1));
            return $prepareBucketArray;
        } catch (\Throwable $ex) {
            Yii::error("CommonUtilityTwo:prepareBucketDashboardDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

     /**
     * @author Padmaja
     * @description This method is used to get Ticket details for dashboard
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
                // error_log("=============ascrollllllllll-------");
                 $activitiesArray=array();
                 $getActivities=array();
                 $activityDetails=array();
                 $projectData=array();
                 //$projectDetails = ProjectTeam::getProjectTeamDetailsByRole($userId,$options['limit'],$options['skip']);
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
     * @description This method is used to prepare Project details for dashboard
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
                     $projectTeamDetails=Collaborators::getFilteredProjectTeam($extractDetails['ProjectId'],$userDetails['UserName']);
                     $projectInfo['projectId']=$extractDetails['ProjectId'];
                     $projectInfo['createdBy']=$userDetails['UserName'];
                     $projectInfo['profilePic']=$projectTeamDetails[0]['ProfilePic'];
                     $projectInfo['projectName']=$projects['ProjectName'];
                     $projectInfo['createdOn'] =$extractDetails['CreatedOn'];
                     $projecTeam=ProjectTeam::getProjectTeamCount($extractDetails['ProjectId']);
                     $projectInfo['team']=$projecTeam['TeamCount'];
                     $projectInfo['topTickets'] = self::getTopTicketsStats($extractDetails['ProjectId'],$userId);
                     $projectInfo['weeklyProjectTimeLog'] =  ServiceFactory::getTimeReportServiceInstance()->getCurrentWeekTimeLog($userId,$extractDetails['ProjectId']);
                     $bucketDetails=Bucket::getProjectBucketByAttributes($extractDetails['ProjectId'],0,2);
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
     * @description This method is used to get Last Id Project details for dashboard
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

    /*
     * @Praveen show the short desc in that html tags
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
            // if (!$exact) {
            // ...search the last occurance of a space...
            $spacepos = strrpos($truncate, ' ');
            if (isset($spacepos)) {
                // ...and cut the text in this position
                $truncate = substr($truncate, 0, $spacepos);
            }
            //}
            // add the defined ending to the text
            //$truncate .= $ending;
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
     * @description This method is used to get all project Activities
     * @return type $projectId
     *  @return type $userId
     */
  public static function getAllProjectActivities($postData){
      try{
                    $activitiesArray=array();
                    $getActivities=array();
                    $activityDetails=array();
                    $getEventDetails= EventCollection::getAllActivities($postData);
                   foreach($getEventDetails as $extractedEventDetails){
                      // error_log("eventttttt-tt------------".print_r($extractedEventDetails,1));
                       foreach($extractedEventDetails['Data'] as $getId){
                           $getActivities=array();
                           $activitiesArray= EventCollection::getActivitiesById($getId);
//                           error_log("------44444444--------".$activitiesArray['OccuredIn']);
                           $getActivities['ProjectId']=$activitiesArray['ProjectId'];
                           $projectDetails = Projects::getProjectMiniDetails($activitiesArray['ProjectId']);
                           $getActivities['ProjectName'] = $projectDetails['ProjectName']; 
                           $getActivities['OccuredIn']=$activitiesArray['OccuredIn'];
                           if($getActivities['OccuredIn']=='Ticket'){
                                $selectFields = ['Title','Fields.planlevel.value_name'];
                                $getTicketDetails = TicketCollection::getTicketDetails($activitiesArray['ReferringId'],$activitiesArray['ProjectId'],$selectFields);
                                $getActivities['Title']='#'.$activitiesArray['ReferringId'].' '.$getTicketDetails['Title'];
                                $getActivities['planlevel'] = $getTicketDetails['Fields']['planlevel']['value_name'];
                            }
                            else if($getActivities['OccuredIn']=='Bucket'){
                              //  $getTicketDetails = TicketCollection::checkTicketsinBuckets($activitiesArray['ProjectId'],$activitiesArray['ReferringId']);
                             $getBucketDetails =Bucket::getBucketName($activitiesArray['ReferringId'],$activitiesArray['ProjectId']);
                                // error_log("----------------".print_r($getTicketDetails[0]['Fields']['bucket']['value_name'],1));
                               $getActivities['Title']=!empty($getBucketDetails['Name'])?$getBucketDetails['Name']:'';
                               $getActivities['planlevel']='';
                            }
                            else if($getActivities['OccuredIn']=='Project'){
                                $getActivities['Title']=!empty($projectDetails['ProjectName'])?$projectDetails['ProjectName']:'';
                                $getActivities['planlevel']='';
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
                           $timezone="Asia/Kolkata";
                           $datetime1->setTimezone(new \DateTimeZone($timezone));
                           $getActivities['createdDate']= $datetime1->format('M-d-Y');
                           $getActivities['Month']= $datetime1->format('M');
                           $getActivities['Day']= $datetime1->format('d');
                           $getActivities['Year']= $datetime1->format('Y');
                           $getActivities['time']= $datetime1->format('H:i:s');
                           $getActivities['ChangeSummary'] = array();
//                           error_log("changee-------".print_r($activitiesArray['ChangeSummary'],1));
                           foreach($activitiesArray['ChangeSummary'] as $changeSummary){
                               $summary = array();
                              $summary['ActionOn']=!empty($changeSummary['ActionOn'])?$changeSummary['ActionOn']:'';
                              if(!empty($changeSummary['OldValue'])){
                                $validDate = CommonUtility::validateDate($changeSummary['OldValue']);
                                if($validDate){
                                    $summary['OldValue'] = new \MongoDB\BSON\UTCDateTime(strtotime($validDate) * 1000);
                                    $datetime1=$summary['OldValue']->toDateTime();
                                    $timezone="Asia/Kolkata";
                                    $datetime1->setTimezone(new \DateTimeZone($timezone));
                                    $summary['OldValue']= $datetime1->format('M-d-Y');
                                }else{
//                               error_log("===Field Name111111==");
                                   $summary['OldValue'] =trim(html_entity_decode(strip_tags($changeSummary['OldValue']))); 
                                }
                              }else{
                                  $summary['OldValue'] ='';
                              }
                              if(!empty($changeSummary['NewValue'])){
                                $validDate = CommonUtility::validateDate($changeSummary['NewValue']);
                                if($validDate){
                                    error_log("=====-============".$validDate);
                                    $summary['NewValue'] = new \MongoDB\BSON\UTCDateTime(strtotime($validDate) * 1000);
                                    $datetime1=$summary['NewValue']->toDateTime();
                                    $timezone="Asia/Kolkata";
                                    $datetime1->setTimezone(new \DateTimeZone($timezone));
                                    $summary['NewValue']= $datetime1->format('M-d-Y');
                                    
                                    error_log("summaryyyyyyy=-============".$summary['NewValue']);
                                }else{
//                                error_log("===Field Name22222222==");
                                   $summary['NewValue'] = trim(html_entity_decode(strip_tags($changeSummary['NewValue']))); 
                                }
                              }else{
                                  $summary['NewValue'] ='';
                              }
                             // $getActivities['ChangeSummary']['OldValue']=!empty($changeSummary['OldValue'])?$changeSummary['OldValue']:'';
                             // $getActivities['ChangeSummary']['NewValue']=!empty($changeSummary['NewValue'])?$changeSummary['NewValue']:'';
                             array_push($getActivities['ChangeSummary'], $summary);  
                           }
                           array_push($activityDetails, $getActivities);
                       }
                  }
                    $checkDates=array();
                    error_log("========864454=============".print_r(array_reverse(array_values(array_unique(array_column($activityDetails,'createdDate')))),1));
                    $preparedDates['createdDate']= array_reverse(array_values(array_unique(array_column($activityDetails,'createdDate'))));
                    error_log("========23243=============".print_r($preparedDates['createdDate'],1));
                    $checkDates = array_fill(0,count($preparedDates['createdDate']), []);
                    foreach($activityDetails as $extracteData){
                       $idx = array_search($extracteData['createdDate'], $preparedDates['createdDate']);
                       if(!is_array($checkDates[$idx])){
                           $checkDates[$idx]=array();
                       }
                       array_push($checkDates[$idx], $extracteData);
                        
                    }
                return  $activityDetails=$checkDates;
     } catch (\Throwable $ex) {
            Yii::error("CommonUtilityTwo:getAllProjectActivities::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
  
  }
  
  /**
   * @author Anand Singh
   * @uses  Get top tickets statics based on project id,userid or bucket id,
   * @param type $userId
   * @param type $projectId
   * @param type $bucketId
   * @throws ErrorException
   */
  public static function getTopTicketsStats($projectId,$userId='',$bucketId=''){
      
      try { 
          $topTicketsArray= array();
          $filters=Filters::getAllActiveFilters();
          $conditions=array('ProjectId'=>(int)$projectId);
          $collection = Yii::$app->mongodb->getCollection('TicketCollection');
          if($bucketId!='')$conditions['Fields.bucket.value']=(int)$bucketId;
          foreach ($filters as $filter) {
                        $topTickets = '';
                        switch((int)$filter['Id']){
                            case 4:
                                $conditions['$or']=[['Fields.assignedto.value'=>(int)$userId],['Followers.FollowerId'=>(int)$userId]];
                                $conditions['Fields.state.value']=(int)3;
                                $total = $collection->count($conditions);
                                unset($conditions['$or']);
                                $conditions['$or']=[['Fields.assignedto.value'=>(int)$userId]];
                                $assigned = $collection->count($conditions);
                                $followed = (int)$total ;
                                $topTickets =  array("id"=>$filter['Id'],"name"=>$filter['Name'],'total'=>$total,'assigned'=>$assigned,'followed'=>$followed);
                                break;
                                }
                        ($topTickets!='')?array_push($topTicketsArray,$topTickets):$topTickets='';       
                      }  
      return $topTicketsArray;
          }
    catch (\Throwable $ex) {
            Yii::error("CommonUtilityTwo:getTopTicketsStats::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    
  
}

/**
 * @author  Anand Singh
 * @param type $activities
 * @return array
 * @throws ErrorException
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
    
    
    public static function getTotalStoryPoints($projectId,$userId){
        try {
           $totalStoryPoints=0;
           $matchArray = array("ProjectId" => (int) $projectId,'$or'=>array(array('Fields.assignedto.value'=>(int)$userId)));
            $query = Yii::$app->mongodb->getCollection('TicketCollection');
            $pipeline = array(
               
                array('$unwind'=> '$Fields'),
                array('$match' => $matchArray),
                array(
                    '$group' => array(
                         '_id' => '$Fields.assignedto.value',
                        "count" => array('$sum' => 1),
                        "totalPoints" => array('$sum' => '$Fields.totalestimatepoints.value'),
                    ),
                ),
            );
            $ArrayStoryPoints = $query->aggregate($pipeline);
            if(count($ArrayStoryPoints)>0){
             $totalCount =  $ArrayStoryPoints[0]["count"];
             $totalStoryPoints =  number_format(round($ArrayStoryPoints[0]["totalPoints"],2),2);
          }
            return $totalStoryPoints;
        } catch (\Throwable $ex) {
            Yii::error("CommonUtilityTwo:getTotalStoryPoints::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

}
