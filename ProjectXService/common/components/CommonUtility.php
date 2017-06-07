<?php

namespace common\components;
use common\models\mongo\{TicketCollection,TinyUserCollection,TicketArtifacts};
use common\models\mysql\{Priority,Projects,WorkFlowFields,Bucket,TicketType,StoryFields,StoryCustomFields,PlanLevel,MapListCustomStoryFields};
use Yii;

use common\components\ApiClient; //only for testing purpose
use common\components\Email; //only for testing purpose
include_once 'ElasticEmailClient.php';


 /*
 * @author Moin Hussain
 */

class CommonUtility {

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
        } catch (Exception $ex) {
            Yii::log("CommonUtility:getExtension::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
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
        
        error_log("-----------------" . $date);
        $date = preg_replace("/\([^)]+\)/", "", $date);
        error_log("------------afet-----" . $date);
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
        } catch (Exception $ex) {
            Yii::log("CommonUtility:convert_date_zone::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

    static function refineActivityData($html,$length="35") {
        // $html = CommonUtility::closetags($html);
        error_log("lengthtttttt".$length);
         $html = strip_tags($html);
        if (strlen($html) > $length) {
            $html = substr($html, 0, $length) . "...";
        }
        return $html;
    }

    static function closetags($html) {

        #put all opened tags into an array

        preg_match_all('#<([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);

        $openedtags = $result[1];   #put all closed tags into an array

        preg_match_all('#</([a-z]+)>#iU', $html, $result);

        $closedtags = $result[1];

        $len_opened = count($openedtags);

        # all tags are closed

        if (count($closedtags) == $len_opened) {

            return $html;
        }

        $openedtags = array_reverse($openedtags);

        # close tags

        for ($i = 0; $i < $len_opened; $i++) {

            if (!in_array($openedtags[$i], $closedtags)) {

                $html .= '</' . $openedtags[$i] . '>';
            } else {

                unset($closedtags[array_search($openedtags[$i], $closedtags)]);
            }
        } return $html;
    }

    /**
     * @author Moin Hussain
     * @param type $text
     * @param type $length
     * @param type $ending
     * @param type $exact
     * @param type $considerHtml
     * @param type $customizedHtml
     * @return string
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
        } catch (Exception $ex) {
            Yii::log("CommonUtility:truncateHtml::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

    /**
     * @description This method is to prepare ticket details
     * @author Moin Hussain
     * @param type $ticketId
     * @param type $projectId
     * @return type
     * @modification by Anand = Modified TaskId since we are getting Object of subtask insted of just Ids from ticket collection.
     */
    public static function prepareTicketDetails($ticketDetails, $projectId,$timezone, $flag = "part") {
        try {
            // $ticketDetails = TicketCollection::getTicketDetails($ticketId,$projectId);

            foreach ($ticketDetails["Fields"] as &$value) {
                if (isset($value["custom_field_id"])) {
                    $storyFieldDetails = StoryCustomFields::getFieldDetails($value["Id"]);
                    if ($storyFieldDetails["Name"] == "List") {

                        $listDetails = MapListCustomStoryFields::getListValue($value["Id"], $value["value"]);
                        $value["readable_value"] = $listDetails;
                    }
                } else {
                    $storyFieldDetails = StoryFields::getFieldDetails($value["Id"]);
                }
                $value["position"] = $storyFieldDetails["Position"];
                $value["title"] = $storyFieldDetails["Title"];
                $value["required"] = $storyFieldDetails["Required"];
                $value["readonly"] = $storyFieldDetails["ReadOnly"];
                $value["field_type"] = $storyFieldDetails["Name"];
                $value["field_name"] = $storyFieldDetails["Field_Name"];
                if ($storyFieldDetails["Type"] == 4 || $storyFieldDetails["Type"] == 5) {
                    if ($value["value"] != "") {
                        $datetime = $value["value"]->toDateTime();
                        if ($storyFieldDetails["Type"] == 4) {
                            $datetime->setTimezone(new \DateTimeZone($timezone));
                            $readableDate = $datetime->format('M-d-Y');
                        } else {
                            $datetime->setTimezone(new \DateTimeZone($timezone));
                            $readableDate = $datetime->format('M-d-Y H:i:s');
                        }
                        $value["readable_value"] = $readableDate;
                    } else {
                        $value["readable_value"] = "";
                    }
                }
                if ($storyFieldDetails["Type"] == 6) {
                    $value["readable_value"] = "";
                    if ($value["value"] != "") {
                        $assignedToDetails = TinyUserCollection::getMiniUserDetails($value["value"]);
                        $assignedToDetails["ProfilePicture"] = Yii::$app->params['ServerURL'] . $assignedToDetails["ProfilePicture"];
                        $value["readable_value"] = $assignedToDetails;
                    }
                }
                if ($storyFieldDetails["Type"] == 10) {
                    $value["readable_value"] = "";
                    if ($value["value"] != "") {
                        $bucketName = Bucket::getBucketName($value["value"], $ticketDetails["ProjectId"]);
                        if($ticketDetails["IsChild"]==1){
                          $value["readonly"] = 1;  
                        }
                        $value["readable_value"] = $bucketName;
                    }
                }
                if ($storyFieldDetails["Field_Name"] == "priority") {
                    $value["readable_value"] = "";
                    if ($value["value"] != "") {
                        $priorityDetails = Priority::getPriorityDetails($value["value"]);
                        $value["readable_value"] = $priorityDetails;
                        $ticketDetails["StoryPriority"] = $priorityDetails;
                    }
                }
                if ($storyFieldDetails["Field_Name"] == "planlevel") {
                    $value["readable_value"] = "";
                    if ($value["value"] != "") {
                        $planlevelDetails = PlanLevel::getPlanLevelDetails($value["value"]);
                        $value["readable_value"] = $planlevelDetails;
                        $ticketDetails["StoryType"] = $planlevelDetails;
                    }
                }
                if ($storyFieldDetails["Field_Name"] == "workflow") {

                    $value["readable_value"] = "";
                    if ($value["value"] != "") {
                        $workFlowDetails = WorkFlowFields::getWorkFlowDetails($value["value"]);
                        $value["readable_value"] = $workFlowDetails;
                    }
                }
                 if ($storyFieldDetails["Field_Name"] == "state") {
                    $value["value"] =$workFlowDetails['State'];
                    $value["readable_value"] = $workFlowDetails['State'];
                    
                }
                if ($storyFieldDetails["Field_Name"] == "tickettype") {
                    $value["readable_value"] = "";
                    if ($value["value"] != "") {
                        $ticketTypeDetails = TicketType::getTicketType($value["value"]);
                        $value["readable_value"] = $ticketTypeDetails;
                    }
                }
            }
            usort($ticketDetails["Fields"], function($a, $b) {
                // echo $a["position"]."\n";
                return $a["position"] >= $b["position"];
            });
            //  return $ticketDetails["Fields"];
            // $ticketDetails["Fields"]="";
            $projectDetails = Projects::getProjectMiniDetails($ticketDetails["ProjectId"]);
            $ticketDetails["Project"] = $projectDetails;

            $selectFields = [];
            if ($flag == "part") {
                $selectFields = ['Title', 'TicketId'];
            }
            $selectFields = ['Title', 'TicketId', 'Fields.priority', 'Fields.assignedto', 'Fields.workflow'];
            foreach ($ticketDetails["Tasks"] as &$task) {
                $taskDetails = TicketCollection::getTicketDetails($task['TaskId'], $projectId, $selectFields);
                $task = (array) $taskDetails;
            }
            foreach ($ticketDetails["RelatedStories"] as &$relatedStory) {
                $relatedStoryDetails = TicketCollection::getTicketDetails($relatedStory, $projectId, $selectFields);
                $relatedStory = $relatedStoryDetails;
            }
            if (!empty($ticketDetails["Followers"])) {
        $ticketDetails["Followers"] = CommonUtility::filterFollowers($ticketDetails["Followers"]);
             
                foreach ($ticketDetails["Followers"] as &$followersList) {
                    //error_log($followersList['FollowerId']."----Follower--1--".print_r($followersList,1));

                    $projectFDetails = TinyUserCollection::getMiniUserDetails($followersList['FollowerId']);
                    $followersList["ProfilePicture"] = $projectFDetails["ProfilePicture"];
                    $followersList["UserName"] = $projectFDetails["UserName"];
                    //$followersList["readable_value"] = $projectFDetails;
                    //error_log($followersList['FollowerId']."----Follower--2--".print_r($followersList,1));
                }
            }
            usort($ticketDetails["Followers"], function($a, $b) {
                   return $a["DefaultFollower"] <= $b["DefaultFollower"];
            });
            unset($ticketDetails["CreatedOn"]);
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
    public static function prepareTicketEditDetails($ticketId, $projectId,$timezone) {
        try {
            $ticketDetails = TicketCollection::getTicketDetails($ticketId, $projectId);
            $workFlowDetails = array();
            if(!empty($ticketDetails)){
               foreach ($ticketDetails["Fields"] as &$value) {
                if (isset($value["custom_field_id"])) {
                    $storyFieldDetails = StoryCustomFields::getFieldDetails($value["Id"]);
                    if ($storyFieldDetails["Name"] == "List") {

                        $listDetails = MapListCustomStoryFields::getListValue($value["Id"], $value["value"]);
                        $value["readable_value"] = $listDetails;
                    }
                } else {
                    $storyFieldDetails = StoryFields::getFieldDetails($value["Id"]);
                }
                $value["position"] = $storyFieldDetails["Position"];
                $value["title"] = $storyFieldDetails["Title"];
                $value["required"] = $storyFieldDetails["Required"];
                $value["readonly"] = $storyFieldDetails["ReadOnly"];
                $value["field_type"] = $storyFieldDetails["Name"];
                $value["field_name"] = $storyFieldDetails["Field_Name"];


                if ($storyFieldDetails["Type"] == 4 || $storyFieldDetails["Type"] == 5) {
                    if ($value["value"] != "") {
                        $datetime = $value["value"]->toDateTime();
                        if ($storyFieldDetails["Type"] == 4) {
                            $datetime->setTimezone(new \DateTimeZone($timezone));
                            $readableDate = $datetime->format('M-d-Y');
                        } else {
                            $datetime->setTimezone(new \DateTimeZone($timezone));
                            $readableDate = $datetime->format('M-d-Y H:i:s');
                        }
                        $value["readable_value"] = $readableDate;
                    } else {
                        $value["readable_value"] = "";
                    }
                }





                if ($storyFieldDetails["Type"] == 6) {
                    $assignedToDetails = TinyUserCollection::getMiniUserDetails($value["value"]);
                    $value["readable_value"] = $assignedToDetails;
                }
                if ($storyFieldDetails["Type"] == 10) {

                    $bucketName = Bucket::getBucketName($value["value"], $ticketDetails["ProjectId"]);
                    $value["readable_value"] = $bucketName;
                    $value["meta_data"] = Bucket::getBucketsList($projectId);
                    if($ticketDetails['IsChild']==1){
                        $value["readonly"] = 1; 
                    }
                }
                if ($storyFieldDetails["Field_Name"] == "priority") {

                    $priorityDetails = Priority::getPriorityDetails($value["value"]);
                    $value["readable_value"] = $priorityDetails;
                    $value["meta_data"] = Priority::getPriorityList();
                }
                if ($storyFieldDetails["Field_Name"] == "planlevel") {

                    $planlevelDetails = PlanLevel::getPlanLevelDetails($value["value"]);
                    $value["readable_value"] = $planlevelDetails;
                    $ticketDetails["StoryType"] = $planlevelDetails;
                    $value["meta_data"] = PlanLevel::getPlanLevelList();
                }
                if ($storyFieldDetails["Field_Name"] == "workflow") {


                    $workFlowDetails = WorkFlowFields::getWorkFlowDetails($value["value"]);
                    $value["readable_value"] = $workFlowDetails;
                    $value["meta_data"] = WorkFlowFields::getStoryWorkFlowList($ticketDetails['WorkflowType'],$value["value"]);
                }
                if ($storyFieldDetails["Field_Name"] == "state") {
                   $value["value"] =$workFlowDetails['State'];
                   $value["readable_value"] = $workFlowDetails['State'];
//                    $value["meta_data"] = WorkFlowFields::getStoryWorkFlowList($ticketDetails['WorkflowType'],$value["value"]);
                }
                if ($storyFieldDetails["Field_Name"] == "tickettype") {

                    $ticketTypeDetails = TicketType::getTicketType($value["value"]);
                    $value["readable_value"] = $ticketTypeDetails;
                    $value["meta_data"] = TicketType::getTicketTypeList();
                }
            }
            $ticketDetails['collaborators'] = ServiceFactory::getCollaboratorServiceInstance()->getProjectTeam($projectId);
            usort($ticketDetails["Fields"], function($a, $b) {
                // echo $a["position"]."\n";
                return $a["position"] >= $b["position"];
            });
            //  return $ticketDetails["Fields"];
            // $ticketDetails["Fields"]="";
            $projectDetails = Projects::getProjectMiniDetails($ticketDetails["ProjectId"]);
            $ticketDetails["Project"] = $projectDetails;



            unset($ticketDetails["CreatedOn"]);
            unset($ticketDetails["UpdatedOn"]);
            unset($ticketDetails["ArtifactsRef"]);
            unset($ticketDetails["CommentsRef"]);  
            }else{
              $ticketDetails='';  
            }
           

            return $ticketDetails;
        } catch (Exception $ex) {
            Yii::log("CommonUtility:prepareTicketEditDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

    /**
     * @author Moin Hussain
     * @param type $description
     * @return type
     */

  public static function refineDescription($description){
      try{
           $description = preg_replace("/<a(.*?)>/", "<a$1 target=\"_blank\">", $description);
           
           $uploadedOn = new \MongoDB\BSON\UTCDateTime(time() * 1000);
              $matches=[];
              $userlist=[];//for email purpose added by ryan
              $mention_matches=[];//added by Ryan
              //preg_match_all('/(@\w+.\w+)/', $description, $mention_matches);//added by ryan
              preg_match_all('/@([\w_\.]+)/', $description, $mention_matches);
              $mentionmatches=$mention_matches[0];//added by Ryan
              for($i=0;$i<count($mentionmatches);$i++)//added by Ryan
              {
                  $value=explode('@',$mentionmatches[$i]);
                  //query for matching users 
                  $user=ServiceFactory::getCollaboratorServiceInstance()->getMatchedCollaborator($value[1]);
                  if(!empty($user))
                  {
                      array_push($userlist,$user);//added by ryan for email purpose
                      //replace the @mention with <a> tag
                      $userMention='@'.$user;
                      $user_link="<a name=".$user." ". "href='javascript:void(0)'>".$userMention."</a>";
                      //replace the link of @mention in description
                      $description=  str_replace($userMention, $user_link, $description);
                  }
                  
              }//code end .... By Ryan
              
              preg_match_all("/\[\[\w+:\w+\/\w+(\|[A-Z0-9\s-_+#$%^&()*a-z]+\.\w+)*\]\]/", $description, $matches);
              $filematches = $matches[0];
              $artifactsList=array();
              for($i = 0; $i< count($filematches); $i++){
                   $value = $filematches[$i];
                   $firstArray =  explode("/", $value);
                   $secondArray = explode("|", $firstArray[1]);
                   $tempFileName = $secondArray[0];
                   $originalFileName = $secondArray[1];
                   $originalFileName = str_replace("]]", "", $originalFileName);
                   $storyArtifactPath = Yii::$app->params['ProjectRoot']. Yii::$app->params['StoryArtifactPath'] ;
                   if(!is_dir($storyArtifactPath)){
                       if(!mkdir($storyArtifactPath, 0775,true)){
                           Yii::log("CommonUtility:refineDescription::Unable to create folder--" . $ex->getTraceAsString(), 'error', 'application');
                       }
                   }
                $newPath = Yii::$app->params['ServerURL'].Yii::$app->params['StoryArtifactPath']."/".$tempFileName."-".$originalFileName;
                $push = true;
                if (file_exists($storyArtifactPath . "/" . $tempFileName . "-" . $originalFileName)) {
                    $push = false;
                } else {
                    $push = true;
                }
                if (file_exists("/usr/share/nginx/www/ProjectXService/node/uploads/$tempFileName")) {
                    rename("/usr/share/nginx/www/ProjectXService/node/uploads/$tempFileName", $storyArtifactPath . "/" . $tempFileName . "-" . $originalFileName);
                }
                $fileName = explode(".", $originalFileName);

                $extension = CommonUtility::getExtension($originalFileName);
                $extension = strtolower($extension);
                $imageExtensions = array("jpg", "jpeg", "gif", "png");
                $videoExtensions = array("mp4", "mov", "ogg", "avi");
                if (in_array($extension, $imageExtensions)) {
                    $replaceString = "<img src='" . $newPath . "'/>";
                    $artifactType = "image";
                } else if (in_array($extension, $videoExtensions)) {
                    $filename = $tempFileName . "-" . $originalFileName;
                    exec("ffmpeg -i $storyArtifactPath/$filename -vf scale=320:-1 $storyArtifactPath/thumb1.png");
                    $replaceString = "<video controls width='50%' height='50%'><source src='" . $newPath . "' type='video/mp4'/></video>";
                    $artifactType = "video";
                } else {
                    $replaceString = "<a href='" . $newPath . "' target='_blank'/>" . $originalFileName . "</a>";
                    $artifactType = "other";
                }            
                $description = str_replace($value, $replaceString, $description);

                if ($push) {
                    $artifactData = CommonUtility::getArtifact($tempFileName, $originalFileName, $extension, $fileName, $artifactType);
                    array_push($artifactsList, $artifactData);
                }
//               TicketArtifacts::saveArtifacts($ticketNumber, $projectId);
              } 
              $returnData = array("description"=>$description,"ArtifactsList"=>$artifactsList,"UsersList"=>$userlist);//modified by Ryan,added UsersList as key
              return $returnData;
      } catch (Exception $ex) {
Yii::log("CommonUtility:refineDescription::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
      }
  }
  
  /**
   * @author Moin Hussain
   * @param type $description
   * @return type
   */
   public static function refineDescriptionForEmail($description){
      try{
           $description = preg_replace("/<a(.*?)>/", "<a$1 target=\"_blank\">", $description);
           
           $uploadedOn = new \MongoDB\BSON\UTCDateTime(time() * 1000);
              $matches=[];
              $userlist=[];//for email purpose added by ryan
              //preg_match_all('/(@\w+.\w+)/', $description, $mention_matches);//added by ryan
          
              
              preg_match_all("/\[\[\w+:\w+\/\w+(\|[A-Z0-9\s-_+#$%^&()*a-z]+\.\w+)*\]\]/", $description, $matches);
              $filematches = $matches[0];
              $artifactsList=array();
              for($i = 0; $i< count($filematches); $i++){
                   $value = $filematches[$i];
                   $firstArray =  explode("/", $value);
                   $secondArray = explode("|", $firstArray[1]);
                   $tempFileName = $secondArray[0];
                   $originalFileName = $secondArray[1];
                   $originalFileName = str_replace("]]", "", $originalFileName);
                   
                  $newPath = Yii::$app->params['ServerURL'].Yii::$app->params['StoryArtifactPath']."/".$tempFileName."-".$originalFileName;
               
                 $replaceString = "<a href='" . $newPath . "' target='_blank'/>" . $originalFileName . "</a>";
                 $artifactType = "other";
                 $description = str_replace($value, $replaceString, $description);

              } 
              return $description;
      } catch (Exception $ex) {
Yii::log("CommonUtility:refineDescriptionForEmail::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
      }
  }
  
  
    /*
     * @author Jagadish
     * @return array
     */
    public static function getArtifact($tempFileName, $originalFileName, $extension, $fileName, $artifactType) {
        try {
            $slug = new \MongoDB\BSON\ObjectID();
            $time = new \MongoDB\BSON\UTCDateTime(time() * 1000);
            $artifactData = array(
                "Slug" => $slug,
                "UploadedOn" => $time,
                "UploadedBy" => '',
                "Status" => (int) 1,
                "ArtifactType" => "$artifactType",
                "isThumbnailExist" => ($artifactType == "video") ? (int) 1 : (int) 0,
                "ThumbnailPath" => Yii::$app->params['StoryArtifactPath'] . "/thumbnails",
                "FileName" => $tempFileName . "-" . $fileName[0],
                "OriginalFileName" => $originalFileName,
                "Extension" => $extension
            );
            return $artifactData;
        } catch (Exception $ex) {
            Yii::log("CommonUtility:getArtifact::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

    /**
     * @author Moin Hussain
     * @param type $ticketDetails
     * @param type $projectId
     * @param type $fieldsOrderArray
     * @param type $flag
     * @return array
     */
    public static function prepareDashboardDetails($ticketDetails, $projectId,$timezone, $fieldsOrderArray, $flag = "part",$filter=null) {
        try {
            $newArray = array();
            $arr2ordered = array();
            $ticketId = array("field_name" => "Id", "value_id" => "", "field_value" => $ticketDetails["TicketId"], "other_data" => "");
            $ticketDetails["Title"]= self::refineActivityData($ticketDetails["Title"],100);
            $ticketTitle = array("field_name" => "Title", "value_id" => "", "field_value" => $ticketDetails["Title"], "other_data" => "");
            array_push($arr2ordered, $ticketId);
            array_push($arr2ordered, $ticketTitle);
            $arr2ordered[1]["other_data"] = sizeof($ticketDetails["Tasks"]); 
            $Othervalue = array();
            foreach ($ticketDetails["Fields"] as $key => $value) {

                if ($key == "planlevel") {
                    //$arr2ordered[0]["other_data"] = $value["value"];
                    $Othervalue["planlevel"] = $value["value"];
                   // $Othervalue["totalSubtasks"] = sizeof($ticketDetails["Tasks"]);
                    
                      if($filter != null){
                $showChild = $filter->showChild ;
               $filterType = $filter->type ;
            if($filterType == "general" && $showChild==0){
                $Othervalue["totalSubtasks"] = "";
            }else{
              $Othervalue["totalSubtasks"] = sizeof($ticketDetails["Tasks"]); 
            }
             }else{
               $Othervalue["totalSubtasks"] = sizeof($ticketDetails["Tasks"]);   
             }
                    
                    
                    
                    $arr2ordered[0]["other_data"] = $Othervalue;
                }
                if (in_array($value["Id"], $fieldsOrderArray)) {

                    if (isset($value["custom_field_id"])) {
                        $storyFieldDetails = StoryCustomFields::getFieldDetails($value["Id"]);
                        if ($storyFieldDetails["Name"] == "List") {

                            $listDetails = MapListCustomStoryFields::getListValue($value["Id"], $value["value"]);
                            $value["readable_value"] = $listDetails;
                        }
                    } else {
                        $storyFieldDetails = StoryFields::getFieldDetails($value["Id"]);
                    }
                    $value["title"] = $storyFieldDetails["Title"];

                    $value["field_name"] = $storyFieldDetails["Field_Name"];
                    if ($storyFieldDetails["Type"] == 4 || $storyFieldDetails["Type"] == 5) {
                        if ($value["value"] != "") {
                            $datetime = $value["value"]->toDateTime();
                            if ($storyFieldDetails["Type"] == 4) {
                                $datetime->setTimezone(new \DateTimeZone($timezone));
                                $readableDate = $datetime->format('M-d-Y');
                            } else {
                                $datetime->setTimezone(new \DateTimeZone($timezone));
                                $readableDate = $datetime->format('M-d-Y H:i:s');
                            }
                            $value["readable_value"] = $readableDate;
                        } else {
                            $value["readable_value"] = "";
                        }
                    }
                    if ($storyFieldDetails["Type"] == 6) {
                        $value["readable_value"] = "";
                        if ($value["value"] != "") {
                            $assignedToDetails = TinyUserCollection::getMiniUserDetails($value["value"]);
                            $assignedToDetails["ProfilePicture"] = $assignedToDetails["ProfilePicture"];
                            $value["readable_value"] = $assignedToDetails;
                        }
                    }
                    if ($storyFieldDetails["Type"] == 10) {
                        $value["readable_value"] = "";
                        if ($value["value"] != "") {
                            $bucketName = Bucket::getBucketName($value["value"], $ticketDetails["ProjectId"]);
                            $value["readable_value"] = $bucketName;
                        }
                    }
                    if ($storyFieldDetails["Field_Name"] == "priority") {
                        $value["readable_value"] = "";
                        if ($value["value"] != "") {
                            $priorityDetails = Priority::getPriorityDetails($value["value"]);
                            $value["readable_value"] = $priorityDetails;
                        }
                    }
                    if ($storyFieldDetails["Field_Name"] == "planlevel") {
                        $value["readable_value"] = "";
                        if ($value["value"] != "") {
                            $planlevelDetails = PlanLevel::getPlanLevelDetails($value["value"]);
                            $value["readable_value"] = $planlevelDetails;
                            $ticketDetails["StoryType"] = $planlevelDetails;
                        }
                    }
                    if ($storyFieldDetails["Field_Name"] == "workflow") {

                        $value["readable_value"] = "";
                        if ($value["value"] != "") {
                            $workFlowDetails = WorkFlowFields::getWorkFlowDetails($value["value"]);
                            $value["readable_value"] = $workFlowDetails;
                        }
                    }
                    if ($storyFieldDetails["Field_Name"] == "tickettype") {
                        $value["readable_value"] = "";
                        if ($value["value"] != "") {
                            $ticketTypeDetails = TicketType::getTicketType($value["value"]);
                            $value["readable_value"] = $ticketTypeDetails;
                        }
                    }
                    if ($storyFieldDetails["Field_Name"] == "dod") {
                        $value["readable_value"] = "";
                        if ($value["value"] != "") {
                            $value["readable_value"] = $value["value"];
                        }
                    }
                    if ($storyFieldDetails["Field_Name"] == "estimatedpoints") {
                        $value["readable_value"] = "";
                        if ($value["value"] != "") {
                            $value["readable_value"] = $value["value"];
                        }
                    }

                    $tempArray = array("field_name" => "", "value_id" => "", "field_value" => "", "other_data" => "");
                    $tempArray["field_name"] = $value["field_name"];
                    $tempArray["value_id"] = $value["value"];
                    if (isset($value["readable_value"]["UserName"])) {
                        $tempArray["field_value"] = $value["readable_value"]["UserName"];
                        $tempArray["other_data"] = $value["readable_value"]["ProfilePicture"];
                    } else if (isset($value["readable_value"]["Name"])) {
                        $tempArray["field_value"] = $value["readable_value"]["Name"];
                    } else {
                        $tempArray["field_value"] = $value["readable_value"];
                    }
                    if ($key == "workflow") {
                        $tempArray["other_data"] = $ticketDetails['Fields']['state']['value_name'];
                    }
                    $newArray[$value["Id"]] = $tempArray;
                }
           }
            foreach ($fieldsOrderArray as $key) {
               array_push($arr2ordered, $newArray[$key]);
            }
              $arrow = array("field_name" => "arrow", "value_id" => "", "field_value" => "", "other_data" => "");
             if($filter != null){
                $showChild = $filter->showChild ;
               $filterType = $filter->type ;
                if($filterType == "general" && $showChild==0){
                   $arrow['other_data'] = 0; 
                }else{
                     $arrow['other_data'] = sizeof($ticketDetails["Tasks"]);  
                } 
             }else{
                 $arrow['other_data'] = sizeof($ticketDetails["Tasks"]);   
             }

              
               array_push($arr2ordered, $arrow);
            unset($ticketDetails["Fields"]);
            $ticketDetails = $arr2ordered;
            $projectDetails = Projects::getProjectMiniDetails($projectId);
            $ticketDetails['project_name']=$projectDetails['ProjectName'];
            return $ticketDetails;
        } catch (Exception $ex) {
            Yii::log("CommonUtility:prepareDashboardDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

    /**
     * @author Moin Hussain
     * @param type $value
     * @param type $projectId
     */
    public static function prepareActivity(&$value, $projectId,$timezone) {
        try {
            $userProfile = TinyUserCollection::getMiniUserDetails($value["ActivityBy"]);
            $value["ActivityBy"] = $userProfile;
            $datetime = $value["ActivityOn"]->toDateTime();
            $datetime->setTimezone(new \DateTimeZone($timezone));
            $readableDate = $datetime->format('M-d-Y H:i:s');
            $value["ActivityOn"] = $readableDate;
            $propertyChanges = $value["PropertyChanges"];
            $poppedFromChild = $value["PoppedFromChild"];
            if (count($propertyChanges) > 0) {
                foreach ($value["PropertyChanges"] as &$property) {
                    CommonUtility::prepareActivityProperty($property,$projectId,$timezone,$poppedFromChild);
                }
            }
        } catch (Exception $ex) {
            Yii::log("CommonUtility:prepareActivity::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

    /**
     * @author Moin Hussain
     * @param type $property
     * @param type $projectId
     * @return type
     */
    public static function prepareActivityProperty(&$property,$projectId,$timezone,$poppedFromChild="") {
        try {
            $fieldName = $property["ActionFieldName"];
            $property["SpecialActivity"]=0;
            $storyFieldDetails = StoryFields::getFieldDetails($fieldName, "Field_Name");
            $type = $storyFieldDetails["Type"];
            $actionFieldName = $property["ActionFieldName"];
            $fieldTitle = preg_replace('/(?<!\ )[A-Z]/', ' $0', $fieldName);
            $property["ActionFieldTitle"] = $fieldTitle;
            if ($storyFieldDetails["Title"] != "" && $storyFieldDetails["Title"] != null) {
                $property["ActionFieldTitle"] = $storyFieldDetails["Title"];
                $property["ActionFieldType"] = $type;
            }
           if($poppedFromChild !=""){
                $ticketDetails = TicketCollection::getTicketDetails($poppedFromChild, $projectId,["TicketId","Title"]);
                error_log(print_r($ticketDetails,1));
                $ticketInfo = $ticketDetails["TicketId"]." ".$ticketDetails["Title"];
                $property["ActionFieldTitle"] = $property["ActionFieldTitle"];
                $property["ActionFieldType"] = $type;
                $ticketDetails["Title"] = self::refineActivityData($ticketDetails["Title"],30);
                $property["PoppedChildTitle"] = $ticketDetails["Title"];
                $property["PoppedChildId"] = $ticketDetails["TicketId"];
           }
            $previousValue = $property["PreviousValue"];
            $property["NewValue"];
            $property["CreatedOn"];
            if ($fieldName == "Title" || $fieldName == "Description") {
                //$property["PreviousValue"]  = substr($property["PreviousValue"], 0, 25);
                // $property["NewValue"]   = substr($property["NewValue"], 0, 25);
                $property["PreviousValue"] = self::refineActivityData($property["PreviousValue"]);
                $property["NewValue"] = self::refineActivityData($property["NewValue"]);
            }
           else if($actionFieldName=='Followed' || $actionFieldName=='Unfollowed' || $actionFieldName=='Related' || $actionFieldName=='ChildTask' || $actionFieldName=='Unrelated') {
                //$property["PreviousValue"]  = substr($property["PreviousValue"], 0, 25);
                // $property["NewValue"]   = substr($property["NewValue"], 0, 25);
            $property["SpecialActivity"]=1;
            $action=array("Id"=>'',"Name"=>'');
            $property["PreviousValue"] = self::refineActivityData($property["PreviousValue"]);
               switch($actionFieldName){
              case 'Followed':
              case 'Unfollowed':
                             $user=TinyUserCollection::getMiniUserDetails($property["NewValue"]);
                             $action=array("Id"=>$user['_id'],"Name"=>$user['UserName']);
                             $newVal="followers"; 
                             $property["type"]='follower';
                             $property["ActionFieldTitle"]=$action;
                             $property["NewValue"] = self::refineActivityData($newVal);break;
              case 'Related':$newVal="realted";
                             $ticketDetails = TicketCollection::getTicketDetails((int)$property["NewValue"], $projectId,["TicketId","Title"]);
                             $action=array("Id"=>$ticketDetails['TicketId'],"Name"=>$ticketDetails['Title']);
                             $newVal="Story/Task";
                             $property["type"]='related';
                             $property["ActionFieldTitle"]=$newVal;
                             $property["NewValue"] = $action;break; 
               case 'Unrelated':$newVal="unrealted";
                             $ticketDetails = TicketCollection::getTicketDetails((int)$property["NewValue"], $projectId,["TicketId","Title"]);
                             $action=array("Id"=>$ticketDetails['TicketId'],"Name"=>$ticketDetails['Title']);
                             $newVal="Story/Task";
                             $property["type"]='unrelated';
                             $property["ActionFieldTitle"]=$newVal;
                             $property["NewValue"] = $action;break;            
              case 'ChildTask':
                             $ticketDetails = TicketCollection::getTicketDetails((int)$property["NewValue"], $projectId,["TicketId","Title"]);
                             $action=array("Id"=>$ticketDetails['TicketId'],"Name"=>$ticketDetails['Title']);
                             $newVal="Child task";
                             $property["type"]='childtask';
                             $property["ActionFieldTitle"]=$newVal;
                             $property["NewValue"] = $action;break; 
          }
             
             
             
              
            }

            else if ($type == 6) {
                if ($property["PreviousValue"] != "") {
                    $property["PreviousValue"] = TinyUserCollection::getMiniUserDetails($property["PreviousValue"]);
                }
                if ($property["NewValue"] != "") {
                    $property["NewValue"] = TinyUserCollection::getMiniUserDetails($property["NewValue"]);
                } else {
                    $property["NewValue"] = "-zero-";
                }
                $property["type"] = "user";
            }
            else if ($fieldName == "workflow") {
                $workflowDetails = WorkFlowFields::getWorkFlowDetails($property["PreviousValue"]);
                $property["PreviousValue"] = $workflowDetails["Name"];
                $workflowDetails = WorkFlowFields::getWorkFlowDetails($property["NewValue"]);
                $property["NewValue"] = $workflowDetails["Name"];
            }
            else if ($fieldName == "priority") {
                $priorityDetails = Priority::getPriorityDetails($property["PreviousValue"]);
                $property["PreviousValue"] = $priorityDetails["Name"];
                $priorityDetails = Priority::getPriorityDetails($property["NewValue"]);
                $property["NewValue"] = $priorityDetails["Name"];
            }
            else if ($type == 4) {
                if ($property["PreviousValue"] != "") {
                    $datetime = $property["PreviousValue"]->toDateTime();
                    $datetime->setTimezone(new \DateTimeZone($timezone));
                    $property["PreviousValue"] = $datetime->format('M-d-Y');
                }


                $datetime = $property["NewValue"]->toDateTime();
                $datetime->setTimezone(new \DateTimeZone($timezone));
                $property["NewValue"] = $datetime->format('M-d-Y');
            }
            else if ($type == 8) {
                //DOD
            }
            else if ($type == 10) {
                //bucket
                $bucketDetails = Bucket::getBucketName($property["PreviousValue"], $projectId);
                $property["PreviousValue"] = $bucketDetails["Name"];
                $bucketDetails = Bucket::getBucketName($property["NewValue"], $projectId);
                $property["NewValue"] = $bucketDetails["Name"];
            }
            else if ($fieldName == "planlevel") {
                //Plan Level
                $planlevelDetails = PlanLevel::getPlanLevelDetails($property["PreviousValue"]);
                $property["PreviousValue"] = $planlevelDetails["Name"];
                $planlevelDetails = PlanLevel::getPlanLevelDetails($property["NewValue"]);
                $property["NewValue"] = $planlevelDetails["Name"];
            }
            else if ($fieldName == "tickettype") {
                //Ticket Type
                $ticketTypeDetails = TicketType::getTicketType($property["PreviousValue"]);
                $property["PreviousValue"] = $ticketTypeDetails["Name"];
                $ticketTypeDetails = TicketType::getTicketType($property["NewValue"]);
                $property["NewValue"] = $ticketTypeDetails["Name"];
            }
            $datetime = $property["CreatedOn"]->toDateTime();
            $datetime->setTimezone(new \DateTimeZone($timezone));
            $readableDate = $datetime->format('M-d-Y H:i:s');
            $property["ActivityOn"] = $readableDate;
             if(gettype($property["PreviousValue"]) == 'double'){
                $property["PreviousValue"]= number_format((float)$property["PreviousValue"], 1, '.', '');
             }else{
                $property["PreviousValue"]=$property["PreviousValue"];
            }
            if(gettype($property["NewValue"]) == 'double'){
                $property["NewValue"]= number_format((float)$property["NewValue"], 1, '.', '');
           }else{
                $property["NewValue"]=$property["NewValue"];
            }
          
            if ($property["NewValue"] == "") {
                $property["NewValue"] = "-zero-";
            }
            return $property;
        } catch (Exception $ex) {
            Yii::log("CommonUtility:prepareActivityProperty::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

  
    /**
     * @author Moin Hussain
     * @param type $recipient_list
     * @param type $text_message
     * @param type $subject
     * @param type $attachment_list
     * @return type
     */
    
    public static function sendEmail($mailingName="ProjectX",$recipient_list,$text_message,$subject,$attachment_list=array()){
        try{
            
$html="<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml'>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
<title>E-mailer</title>
</head>
<body>
<table width='600' border='0' align='center' cellpadding='0' cellspacing='0'>";          
$text_message=$html . $text_message . 
        "<tr><td align='center' style='border:1px solid #f0f0f0; padding:5px;font-family:Arial; font-size:12px;line-height:40px;color:#333333; text-align:center;' >Update Your <a href=".''.Yii::$app->params['AppURL'].'/home'." style='color:#0199e0; text-decoration:none;'>Email Alert Preferences</a></td></tr>
        <tr><td bgcolor='#787878' align='left' valign='top' height='35'>
        <table width='100%'  border='0' align='center' cellpadding='0' cellspacing='0' height:'35'>
  <tr>
  <td width='15' height:'35'>&nbsp;</td>
       <td wdth='512' align='left' height:'35' style='font-family:Arial; font-size:12px;line-height:35px; color:#fff;'>This message was sent by <a href='#' style='color:#d0ebff;font-size:13dpx;text-decoration:none;'>ProjectX</a></td>
        <td width='20' align='left'height:'35' ><a href='#'><img src=".''.Yii::$app->params['EmailServerURL'].'/files/tool/facebook.png'." style=' border:none; outline:none;'/></a></td>
        <td width='20' align='left' height:'35'><a href='#'><img src=".''.Yii::$app->params['EmailServerURL'].'/files/tool/twit.png'." style=' border:none; outline:none;'/></a></td>
        <td width='20' align='center' height:'35'><a href='#'><img src=".''.Yii::$app->params['EmailServerURL'].'/files/tool/linkedin.png'." style=' border:none; outline:none;'/></a></td>
        <td width='15'>&nbsp;</td>
        </tr>
       </table>
       </td>
       </tr>
</table>
</body>
</html>";
//$subject_text="ProjectX | ".$subject;
         echo("4. In CommonUtiltiy sendEmail started\n");
         ApiClient::SetApiKey(Yii::$app->params['ElasticEmailApiKey']);
        $attachments=array();//list of artifacts
        $EEemail = new Email();

        $from=Yii::$app->params['ProjectEmail'];
        $fromName= $mailingName;
        $html=$text_message;
        $text=$text_message;
        $response = $EEemail->Send($subject, $from, $fromName, null, null, null, null, null, null, $recipient_list, array(), array(), array(), array(), array(), null, null, $html, $text,null,null,null,null,null,$attachments);		
                

//              Yii::$app->mailer->compose()
//           ->setFrom(Yii::$app->params['ProjectEmail'])
//           ->setTo($recipient_list)
//           ->setSubject("ProjectX | ".$subject)
//           ->setTextBody('This is ProjectX')
//           ->setHtmlBody($text_message)
//           ->send();
             error_log("in send mail");
//        echo("5. In CommonUtiltiy sendEmail completed..\n");
//        echo("6. Sending email background job has completed\n");
        }catch(Exception $ex){
           echo("Exception:In CommonUtiltiy sendEmail failed..".$ex->getMessage()."\n");
           Yii::log("CommonUtility:sendEmail::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');

        }
    }
   /**
    * @author Padmaja
    * @param type $searchString
    * @param type $page
    * @param type $searchFlag
    * @param type $projectId
    * @return array
    */
    public static function getAllDetailsForSearch($searchString,$page,$searchFlag="",$projectId="",$pageLength){
        try{
                $page = $page ;
              //  $pageLength = 10;
                if ($page == 1) {
                    $offset = $page - 1;
                    $limit = $pageLength;   
                } else {
                    $offset = ($page - 1) * $pageLength;
                    $limit = $pageLength;
                }
            $searchString=strtolower($searchString);
            if ( !empty($searchString)) {
                $TicketCollFinalArray = array();
                $TicketArtifactsFinalArray = array();
                $TicketCommentsFinalArray = array();
                $TinyUserFinalArray = array();
                $options = array(
                    "limit" =>$limit,
                    "skip" => $offset
                );
                if($searchFlag==1){
                    $collection = Yii::$app->mongodb->getCollection('TicketCollection');
                   if (strpos($searchString, '#') !== false || is_numeric($searchString)!= false) {
                        if(strpos($searchString, '#') !== false){
                           $getTicketIdNumber = explode('#', $searchString);
                            if(is_numeric($getTicketIdNumber[1])){
                                $searchString=str_replace("#","",$searchString);
                            }
                            if(!empty($projectId)){
                                $cursor =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("CrudeDescription"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId))),array(),$options);
                               // $cursor =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId))),array(),$options);
                            }else{
                                $cursor =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i')),array("CrudeDescription"=>array('$regex'=>$searchString,'$options' => 'i')),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i')),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i')))),array(),$options); 
                            }
                        }else{
                            if(!empty($projectId)){  
                                $cursor =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("CrudeDescription"=>array('$regex'=>'^'.$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId))),array(),$options);
                            }else{
                                $cursor =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i')),array("CrudeDescription"=>array('$regex'=>'^'.$searchString,'$options' => 'i')),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i')),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i')))),array(),$options);
                            }
                        }

                    }else{
                         if(!empty($projectId)){ 
                            $searchString = \quotemeta($searchString);
                            $cursor =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("CrudeDescription"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId))),array(),$options);
                        }else{
                            $cursor =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i')),array("CrudeDescription"=>array('$regex'=>$searchString,'$options' => 'i')),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i')),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i')))),array(),$options);
                        }
                    }
                   // $cursor =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)1),array("CrudeDescription"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)1),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)1),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)1))),array(),$options);
                    $ticketCollectionData = iterator_to_array($cursor);
                    $TicketCollFinalArray = array();
                    foreach($ticketCollectionData as $extractCollection){
                        $forTicketCollection['TicketId'] = $extractCollection['TicketId'];
                        $forTicketCollection['Title'] = $extractCollection['Title'];
                        $forTicketCollection['description'] = strip_tags($extractCollection['CrudeDescription']);
                        if(strpos(strtolower($forTicketCollection['description']),$searchString) !==false){
                           $forTicketCollection['description']= $forTicketCollection['description'];
                        }
                        $forTicketCollection['planlevel'] = $extractCollection['Fields']['planlevel']['value_name'];
                        $forTicketCollection['reportedby'] = $extractCollection['Fields']['reportedby']['value_name'];
                        $UpdatedOn = $extractCollection['UpdatedOn'];
                        if(isset($UpdatedOn)){
                            $datetime = $UpdatedOn->toDateTime();
                            $readableDate =$datetime->format('M-d-Y H:i:s');
                            $forTicketCollection['UpdatedOn'] = $readableDate;
                        }
                     
                        $projectDetails = Projects::getProjectMiniDetails($extractCollection["ProjectId"]);
                        $forTicketCollection['Project'] = $projectDetails;
                  
                        array_push($TicketCollFinalArray, $forTicketCollection);
                    }
                    $searchString=stripslashes($searchString);
                    $searchString = \quotemeta($searchString);
                    $matchArray = array('Activities.CrudeCDescription'=>array('$regex'=>$searchString,'$options' => 'i'));
                    if(!empty($projectId)){
                        $matchArray = array('Activities.CrudeCDescription'=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>(int)$projectId);
                    }
                    $query = Yii::$app->mongodb->getCollection('TicketComments');
                    $pipeline = array(
                        array('$unwind' => '$Activities'),
                        array('$match' => $matchArray),
                         array(
                            '$group' => array(
                                      '_id' =>  array('TicketId'=> '$TicketId', 'ProjectId'=> '$ProjectId'),
                                "commentData" => array('$push' => '$Activities'),

                             ),
                        ),array('$limit' => $limit),array('$skip' => $offset)
                        );
                    $ticketCommentsData = $query->aggregate($pipeline);
                    $TicketCommentsFinalArray = array();
                    $commentsArray= array();
                    
                       foreach($ticketCommentsData as $extractComments){
                           $selectFields = ['Title','ProjectId', 'TicketId','Description','Fields.planlevel.value_name','Fields.reportedby.value_name','UpdatedOn'];
                           $getTicketDetails = TicketCollection::getTicketDetails($extractComments['_id']['TicketId'],$extractComments['_id']['ProjectId'],$selectFields);
                           $forTicketComments['TicketId'] =  $extractComments['_id'];
                           $forTicketComments['Title'] =$getTicketDetails['Title'];
                         //  $forTicketComments['comments'] =  $extractComments['commentData'];
                           $commentsfinalArray =array();
                           foreach($extractComments['commentData'] as $eachOne){
                              $searchString= stripslashes($searchString);
                              $commentsArray['CrudeCDescription']=strip_tags($eachOne['CrudeCDescription']);
                              $commentsArray['Slug']=$eachOne['Slug'];
                              $commentsArray['ActivityOn']=$eachOne['ActivityOn'];
                              if(strpos($commentsArray['CrudeCDescription'],$searchString) !==false){
                                array_push($commentsfinalArray,$commentsArray);
                              }
                           }
                            $forTicketComments['comments']=$commentsfinalArray;
                           $forTicketComments['planlevel'] = $getTicketDetails['Fields']['planlevel']['value_name'];
                           $forTicketComments['reportedby'] = $getTicketDetails['Fields']['reportedby']['value_name'];
                            $UpdatedOn = $getTicketDetails['UpdatedOn'];
                            if(isset($UpdatedOn)){
                                $datetime = $UpdatedOn->toDateTime();
                                $readableDate = $datetime->format('M-d-Y H:i:s');
                                $forTicketComments['UpdatedOn'] = $readableDate;
                           }
                            $projectDetails = Projects::getProjectMiniDetails($getTicketDetails["ProjectId"]);
                             $forTicketComments['Project'] = $projectDetails; 
                            array_push($TicketCommentsFinalArray, $forTicketComments);
                       }
                          $collection = Yii::$app->mongodb->getCollection('TicketArtifacts');
                    $cursor =  $collection->find(array('$or'=>array(array("Artifacts.OriginalFileName"=>array('$regex'=>$searchString,'$options' => 'i')))),array(),$options);
                    if(!empty($projectId)){
                       $cursor =  $collection->find(array('$or'=>array(array("Artifacts.OriginalFileName"=>array('$regex'=>$searchString,'$options' => 'i'))),'ProjectId'=>(int)$projectId),array(),$options); 
                    }
                    $ticketArtifactsData = iterator_to_array($cursor);
                    $TicketArtifactsFinalArray = array();
                    foreach($ticketArtifactsData as $extractArtifacts){
                        $selectFields = ['Title','ProjectId', 'TicketId','Fields.planlevel.value_name','Fields.reportedby.value_name','UpdatedOn','CrudeDescription'];
                        $getTicketDetails = TicketCollection::getTicketDetails($extractArtifacts['TicketId'],$extractArtifacts['ProjectId'],$selectFields);
                        $forTicketArtifacts['TicketId'] =$extractArtifacts['TicketId'];
                        $forTicketArtifacts['Title'] =$getTicketDetails['Title'];
                        $ticketArtifactsModel = new TicketArtifacts();
                        $artifacts = $ticketArtifactsModel->getTicketArtifacts($extractArtifacts['TicketId'],$extractArtifacts['ProjectId']);
                        $getArtifactsEach=array();
                        foreach($artifacts['Artifacts'] as $getArtifact){
                             array_push($getArtifactsEach,$getArtifact['OriginalFileName']);
                        }
                        $forTicketArtifacts['description'] =$getArtifactsEach;
                        $forTicketArtifacts['planlevel'] = $getTicketDetails['Fields']['planlevel']['value_name'];
                        $forTicketArtifacts['reportedby'] = $getTicketDetails['Fields']['reportedby']['value_name'];
                        $UpdatedOn = $getTicketDetails['UpdatedOn'];
                        if(isset($UpdatedOn)){
                            $datetime = $UpdatedOn->toDateTime();
                            $readableDate =$datetime->format('M-d-Y H:i:s');
                            $forTicketArtifacts['UpdatedOn'] = $readableDate;
                         }
                        $projectDetails = Projects::getProjectMiniDetails($getTicketDetails["ProjectId"]);
                        $forTicketArtifacts['Project'] = $projectDetails; 
                        array_push($TicketArtifactsFinalArray, $forTicketArtifacts);

                    }

                    $collection = Yii::$app->mongodb->getCollection('TinyUserCollection');
                    $cursor=$collection->find(array('$or'=>array(array("Email"=>array('$regex'=>$searchString,'$options' => 'i')),array("UserName"=>array('$regex'=>$searchString,'$options' => 'i')))),array(),$options);
                    $tinyUserData = iterator_to_array($cursor);
                    $TinyUserFinalArray = array();
                    foreach($tinyUserData as $extractUserData){
                        $forUsercollection['Title']=  $extractUserData['UserName'];
                        $forUsercollection['ProfilePicture']=  $extractUserData['ProfilePicture'];
                        $forUsercollection['description']=  $extractUserData['Email'];
                        $UpdatedOn=  $extractUserData['UpdatedOn'];
                        if(isset($UpdatedOn)){
                            $datetime = $UpdatedOn->toDateTime();
                            $readableDate =$datetime->format('M-d-Y H:i:s');
                            $forUsercollection['UpdatedOn'] = $readableDate; 
                          }
                         array_push($TinyUserFinalArray, $forUsercollection);
                    }
                }else if($searchFlag==2){
                    $searchString=stripslashes($searchString);
                    $searchString = \quotemeta($searchString);
        
                    $matchArray = array('Activities.CrudeCDescription'=>array('$regex'=>$searchString,'$options' => 'i'));
                     if(!empty($projectId)){
                        $matchArray = array('Activities.CrudeCDescription'=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>(int)$projectId);
                    }
                    $query = Yii::$app->mongodb->getCollection('TicketComments');
                    $pipeline = array(
                        array('$unwind' => '$Activities'),
                        array('$match' => $matchArray),
                         array(
                            '$group' => array(
                                '_id' =>  array('TicketId'=> '$TicketId', 'ProjectId'=> '$ProjectId'),
                                "commentData" => array('$push' => '$Activities'),

                             ),
                        ),array('$limit' => $limit),array('$skip' => $offset)
                        );
                    $ticketCommentsData = $query->aggregate($pipeline);
                    $TicketCommentsFinalArray = array();
                    $commentsArray= array();
                        foreach($ticketCommentsData as $extractComments){
                           $selectFields = ['Title','ProjectId', 'TicketId','Description','Fields.planlevel.value_name','Fields.reportedby.value_name','UpdatedOn'];
                           $getTicketDetails = TicketCollection::getTicketDetails($extractComments['_id']['TicketId'],$extractComments['_id']['ProjectId'],$selectFields);
                           $forTicketComments['TicketId'] =  $extractComments['_id'];
                           $forTicketComments['Title'] =$getTicketDetails['Title'];
                         //  $forTicketComments['comments'] =  $extractComments['commentData'];
                           $commentsfinalArray =array();
                           foreach($extractComments['commentData'] as $eachOne){
                              $searchString= stripslashes($searchString);
                              $commentsArray['CrudeCDescription']=strip_tags($eachOne['CrudeCDescription']);
                              $commentsArray['Slug']=$eachOne['Slug'];
                              $commentsArray['ActivityOn']=$eachOne['ActivityOn'];
                              if(strpos($commentsArray['CrudeCDescription'],$searchString) !==false){
                                array_push($commentsfinalArray,$commentsArray);
                              }
                           }
                            $forTicketComments['comments']=$commentsfinalArray;
                           $forTicketComments['planlevel'] = $getTicketDetails['Fields']['planlevel']['value_name'];
                           $forTicketComments['reportedby'] = $getTicketDetails['Fields']['reportedby']['value_name'];
                          // $forTicketComments['UpdatedOn'] =$getTicketDetails['UpdatedOn'];
                            $UpdatedOn = $getTicketDetails['UpdatedOn'];
                            if(isset($UpdatedOn)){
                                $datetime = $UpdatedOn->toDateTime();
                                $readableDate = $datetime->format('M-d-Y H:i:s');
                                $forTicketComments['UpdatedOn'] = $readableDate;
                           }
                            $projectDetails = Projects::getProjectMiniDetails($getTicketDetails["ProjectId"]);
                             $forTicketComments['Project'] = $projectDetails; 
                            array_push($TicketCommentsFinalArray, $forTicketComments);
                       }
                }else if($searchFlag==3){
                    $collection = Yii::$app->mongodb->getCollection('TinyUserCollection');
                    $cursor=$collection->find(array('$or'=>array(array("Email"=>array('$regex'=>$searchString,'$options' => 'i')),array("UserName"=>array('$regex'=>$searchString,'$options' => 'i')))),array(),$options);
                    $tinyUserData = iterator_to_array($cursor);
                    $TinyUserFinalArray = array();
                    foreach($tinyUserData as $extractUserData){
                        $forUsercollection['Title']=  $extractUserData['UserName'];
                        $forUsercollection['ProfilePicture']=  $extractUserData['ProfilePicture'];
                        $forUsercollection['description']=  $extractUserData['Email'];
                        $UpdatedOn=  $extractUserData['UpdatedOn'];
                        if(isset($UpdatedOn)){
                            $datetime = $UpdatedOn->toDateTime();
                            $readableDate =$datetime->format('M-d-Y H:i:s');
                            $forUsercollection['UpdatedOn'] = $readableDate; 
                          }
                         array_push($TinyUserFinalArray, $forUsercollection);
                    }
                    
           
                    
                }else if($searchFlag==4){
                       $collection = Yii::$app->mongodb->getCollection('TicketArtifacts');
                    $cursor =  $collection->find(array('$or'=>array(array("Artifacts.OriginalFileName"=>array('$regex'=>$searchString,'$options' => 'i')))),array(),$options);
                    if(!empty($projectId)){
                       $cursor =  $collection->find(array('$or'=>array(array("Artifacts.OriginalFileName"=>array('$regex'=>$searchString,'$options' => 'i'))),'ProjectId'=>(int)$projectId),array(),$options); 
                    }
                    $ticketArtifactsData = iterator_to_array($cursor);
                    $TicketArtifactsFinalArray = array();
                    foreach($ticketArtifactsData as $extractArtifacts){
                        $selectFields = ['Title','ProjectId', 'TicketId','Fields.planlevel.value_name','Fields.reportedby.value_name','UpdatedOn','CrudeDescription'];
                        $getTicketDetails = TicketCollection::getTicketDetails($extractArtifacts['TicketId'],$extractArtifacts['ProjectId'],$selectFields);
                        $forTicketArtifacts['TicketId'] =$extractArtifacts['TicketId'];
                        $forTicketArtifacts['Title'] =$getTicketDetails['Title'];
                        $ticketArtifactsModel = new TicketArtifacts();
                        $artifacts = $ticketArtifactsModel->getTicketArtifacts($extractArtifacts['TicketId'],$extractArtifacts['ProjectId']);
                        $getArtifactsEach=array();
                        foreach($artifacts['Artifacts'] as $getArtifact){
                            array_push($getArtifactsEach,$getArtifact['OriginalFileName']);
                        }
                        $forTicketArtifacts['description'] =$getArtifactsEach;
                        $forTicketArtifacts['planlevel'] = $getTicketDetails['Fields']['planlevel']['value_name'];
                        $forTicketArtifacts['reportedby'] = $getTicketDetails['Fields']['reportedby']['value_name'];
                        $UpdatedOn = $getTicketDetails['UpdatedOn'];
                        if(isset($UpdatedOn)){
                            $datetime = $UpdatedOn->toDateTime();
                            $readableDate =$datetime->format('M-d-Y H:i:s');
                            $forTicketArtifacts['UpdatedOn'] = $readableDate;
                         }
                        $projectDetails = Projects::getProjectMiniDetails($getTicketDetails["ProjectId"]);
                        $forTicketArtifacts['Project'] = $projectDetails; 
                        array_push($TicketArtifactsFinalArray, $forTicketArtifacts);

                    }
                }else if($searchFlag==5){
                    $collection = Yii::$app->mongodb->getCollection('TicketCollection');
                    if (strpos($searchString, '#') !== false || is_numeric($searchString)!= false) {
                        if(strpos($searchString, '#') !== false){
                           $getTicketIdNumber = explode('#', $searchString);
                        if(is_numeric($getTicketIdNumber[1])){
                            $searchString=str_replace("#","",$searchString);
                        }
                            if(!empty($projectId)){
                             $cursor =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("CrudeDescription"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId))),array(),$options);
                            }else{
                               $cursor =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i')),array("CrudeDescription"=>array('$regex'=>$searchString,'$options' => 'i')),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i')),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i')))),array(),$options); 
                            }

                        }else{
                            if(!empty($projectId)){  
                                $cursor =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("CrudeDescription"=>array('$regex'=>'^'.$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId))),array(),$options);
                            }else{
                                $cursor =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i')),array("CrudeDescription"=>array('$regex'=>'^'.$searchString,'$options' => 'i')),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i')),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i')))),array(),$options);
                            }
                        }
                    }else{
                         if(!empty($projectId)){ 
                            $searchString = \quotemeta($searchString);
                            $cursor =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("CrudeDescription"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId))),array(),$options);
                        }else{
                            $searchString = \quotemeta($searchString);
                            $cursor =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i')),array("CrudeDescription"=>array('$regex'=>$searchString,'$options' => 'i')),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i')),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i')))),array(),$options);
                        }
                    }
                   // $cursor =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)1),array("CrudeDescription"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)1),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)1),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)1))),array(),$options);
                    $ticketCollectionData = iterator_to_array($cursor);
                    $TicketCollFinalArray = array();
                    foreach($ticketCollectionData as $extractCollection){
                        $forTicketCollection['TicketId'] = $extractCollection['TicketId'];
                        $forTicketCollection['Title'] = $extractCollection['Title'];
                        $forTicketCollection['description'] = strip_tags($extractCollection['CrudeDescription']);
                        if(strpos(strtolower($forTicketCollection['description']),$searchString) !==false){
                           $forTicketCollection['description']= $forTicketCollection['description'];
                        }
                        $forTicketCollection['planlevel'] = $extractCollection['Fields']['planlevel']['value_name'];
                        $forTicketCollection['reportedby'] = $extractCollection['Fields']['reportedby']['value_name'];
                        $UpdatedOn = $extractCollection['UpdatedOn'];
                        if(isset($UpdatedOn)){
                            $datetime = $UpdatedOn->toDateTime();
                            $readableDate =$datetime->format('M-d-Y H:i:s');
                            $forTicketCollection['UpdatedOn'] = $readableDate;
                        }
                     
                        $projectDetails = Projects::getProjectMiniDetails($extractCollection["ProjectId"]);
                        $forTicketCollection['Project'] = $projectDetails;
                  
                        array_push($TicketCollFinalArray, $forTicketCollection);
                    }
                }else{
                    $collection = Yii::$app->mongodb->getCollection('TicketCollection');
                    $options = array(
                        "limit" =>$limit,
                        "skip" => $offset
                    );
                    if (strpos($searchString, '#') !== false || is_numeric($searchString)!= false) {
                        if(strpos($searchString, '#') !== false){
                           $getTicketIdNumber = explode('#', $searchString);
                         if(is_numeric($getTicketIdNumber[1])){
                            $searchString=str_replace("#","",$searchString);
                        }
                            if(!empty($projectId)){
                             $cursor =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("CrudeDescription"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId))),array(),$options);
                            }else{
                               $cursor =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i')),array("CrudeDescription"=>array('$regex'=>$searchString,'$options' => 'i')),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i')),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i')))),array(),$options); 
                            }

                        }else{
                            if(!empty($projectId)){  
                                $cursor =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("CrudeDescription"=>array('$regex'=>'^'.$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId))),array(),$options);
                            }else{
                                $cursor =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i')),array("CrudeDescription"=>array('$regex'=>'^'.$searchString,'$options' => 'i')),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i')),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i')))),array(),$options);
                            }
                        }
                    }else{
                         if(!empty($projectId)){ 
                            $searchString = \quotemeta($searchString);
                           $cursor =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("CrudeDescription"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId))),array(),$options);
                        }else{
                            $cursor =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i')),array("CrudeDescription"=>array('$regex'=>$searchString,'$options' => 'i')),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i')),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i')))),array(),$options);
                        }
                    }
                    $ticketCollectionData = iterator_to_array($cursor);
                    $TicketCollFinalArray = array();
                    foreach($ticketCollectionData as $extractCollection){
                        $forTicketCollection['TicketId'] = $extractCollection['TicketId'];
                        $forTicketCollection['Title'] = $extractCollection['Title'];
                        $forTicketCollection['description'] = strip_tags($extractCollection['CrudeDescription']);
                        if(strpos($forTicketCollection['description'],$searchString) !==false){
                           $forTicketCollection['description']= $forTicketCollection['description'];
                        }
                        $forTicketCollection['planlevel'] = $extractCollection['Fields']['planlevel']['value_name'];
                        $forTicketCollection['reportedby'] = $extractCollection['Fields']['reportedby']['value_name'];
                        $UpdatedOn = $extractCollection['UpdatedOn'];
                        if(isset($UpdatedOn)){
                            $datetime = $UpdatedOn->toDateTime();
                            $readableDate =$datetime->format('M-d-Y H:i:s');
                            $forTicketCollection['UpdatedOn'] = $readableDate;
                        }
                        $projectDetails = Projects::getProjectMiniDetails($extractCollection["ProjectId"]);
                        $forTicketCollection['Project'] = $projectDetails; 
                        array_push($TicketCollFinalArray, $forTicketCollection);
                    }
                    $searchString=stripslashes($searchString);
                    $searchString = \quotemeta($searchString);
                    $matchArray = array('Activities.CrudeCDescription'=>array('$regex'=>$searchString,'$options' => 'i'));
                    if(!empty($projectId)){
                        $matchArray = array('Activities.CrudeCDescription'=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>(int)$projectId);
                    }
                    $query = Yii::$app->mongodb->getCollection('TicketComments');
                    $pipeline = array(
                        array('$unwind' => '$Activities'),
                        array('$match' => $matchArray),
                         array(
                            '$group' => array(
                                '_id' =>  array('TicketId'=> '$TicketId', 'ProjectId'=> '$ProjectId'),
                                "commentData" => array('$push' => '$Activities'),

                             ),
                        ),array('$limit' => $limit),array('$skip' => $offset)
                        );
                    $ticketCommentsData = $query->aggregate($pipeline);
                    $TicketCommentsFinalArray = array();
                    $commentsArray= array();
                        foreach($ticketCommentsData as $extractComments){
                           $selectFields = ['Title','ProjectId', 'TicketId','Description','Fields.planlevel.value_name','Fields.reportedby.value_name','UpdatedOn'];
                           $getTicketDetails = TicketCollection::getTicketDetails($extractComments['_id']['TicketId'],$extractComments['_id']['ProjectId'],$selectFields);
                           $forTicketComments['TicketId'] =  $extractComments['_id'];
                           $forTicketComments['Title'] =$getTicketDetails['Title'];
                           $commentsfinalArray =array();
                           foreach($extractComments['commentData'] as $eachOne){
                              $searchString= stripslashes($searchString);
                              $commentsArray['CrudeCDescription']=strip_tags($eachOne['CrudeCDescription']);
                              $commentsArray['Slug']=$eachOne['Slug'];
                              $commentsArray['ActivityOn']=$eachOne['ActivityOn'];
                              if(strpos($commentsArray['CrudeCDescription'],$searchString) !==false){
                                array_push($commentsfinalArray,$commentsArray);
                              }
                           }
                            $forTicketComments['comments']=$commentsfinalArray;
                           $forTicketComments['planlevel'] = $getTicketDetails['Fields']['planlevel']['value_name'];
                           $forTicketComments['reportedby'] = $getTicketDetails['Fields']['reportedby']['value_name'];
                            $UpdatedOn = $getTicketDetails['UpdatedOn'];
                            if(isset($UpdatedOn)){
                                $datetime = $UpdatedOn->toDateTime();
                                $readableDate = $datetime->format('M-d-Y H:i:s');
                                $forTicketComments['UpdatedOn'] = $readableDate;
                           }
                            $projectDetails = Projects::getProjectMiniDetails($getTicketDetails["ProjectId"]);
                             $forTicketComments['Project'] = $projectDetails; 
                            array_push($TicketCommentsFinalArray, $forTicketComments);
                       }
                    $collection = Yii::$app->mongodb->getCollection('TicketArtifacts');
                    $cursor =  $collection->find(array('$or'=>array(array("Artifacts.OriginalFileName"=>array('$regex'=>$searchString,'$options' => 'i')))),array(),$options);
                    if(!empty($projectId)){
                       $cursor =  $collection->find(array('$or'=>array(array("Artifacts.OriginalFileName"=>array('$regex'=>$searchString,'$options' => 'i'))),'ProjectId'=>(int)$projectId),array(),$options); 
                    }
                    $ticketArtifactsData = iterator_to_array($cursor);
                    $TicketArtifactsFinalArray = array();
                    foreach($ticketArtifactsData as $extractArtifacts){
                        $selectFields = ['Title','ProjectId', 'TicketId','Fields.planlevel.value_name','Fields.reportedby.value_name','UpdatedOn','CrudeDescription'];
                        $getTicketDetails = TicketCollection::getTicketDetails($extractArtifacts['TicketId'],$extractArtifacts['ProjectId'],$selectFields);
                        $forTicketArtifacts['TicketId'] =$extractArtifacts['TicketId'];
                        $forTicketArtifacts['Title'] =$getTicketDetails['Title'];
                        $ticketArtifactsModel = new TicketArtifacts();
                        $artifacts = $ticketArtifactsModel->getTicketArtifacts($extractArtifacts['TicketId'],$extractArtifacts['ProjectId']);
                        $getArtifactsEach=array();
                        foreach($artifacts['Artifacts'] as $getArtifact){
                             array_push($getArtifactsEach,$getArtifact['OriginalFileName']);
                        }
                        $forTicketArtifacts['description'] =$getArtifactsEach;
                        $forTicketArtifacts['planlevel'] = $getTicketDetails['Fields']['planlevel']['value_name'];
                        $forTicketArtifacts['reportedby'] = $getTicketDetails['Fields']['reportedby']['value_name'];
                        $UpdatedOn = $getTicketDetails['UpdatedOn'];
                        if(isset($UpdatedOn)){
                            $datetime = $UpdatedOn->toDateTime();
                            $readableDate =$datetime->format('M-d-Y H:i:s');
                            $forTicketArtifacts['UpdatedOn'] = $readableDate;
                         }
                        $projectDetails = Projects::getProjectMiniDetails($getTicketDetails["ProjectId"]);
                        $forTicketArtifacts['Project'] = $projectDetails; 
                        array_push($TicketArtifactsFinalArray, $forTicketArtifacts);

                    }
                    $collection = Yii::$app->mongodb->getCollection('TinyUserCollection');
                    $cursor=$collection->find(array('$or'=>array(array("Email"=>array('$regex'=>$searchString,'$options' => 'i')),array("UserName"=>array('$regex'=>$searchString,'$options' => 'i')))),array(),$options);
                    $tinyUserData = iterator_to_array($cursor);
                    $TinyUserFinalArray = array();
                 
                    foreach($tinyUserData as $extractUserData){
                        $forUsercollection['Title']=  $extractUserData['UserName'];
                        $forUsercollection['ProfilePicture']=  $extractUserData['ProfilePicture'];
                        $forUsercollection['description']=  $extractUserData['Email'];
                        $UpdatedOn=  $extractUserData['UpdatedOn'];
                        if(isset($UpdatedOn)){
                            $datetime = $UpdatedOn->toDateTime();
                            $readableDate =$datetime->format('M-d-Y H:i:s');
                            $forUsercollection['UpdatedOn'] = $readableDate; 
                          }
                         array_push($TinyUserFinalArray, $forUsercollection);
                    }
                }  
                $getCollectionData=array('ticketCollection'=>$TicketCollFinalArray,'ticketComments'=>$TicketCommentsFinalArray,'ticketArtifacts'=>$TicketArtifactsFinalArray,'tinyUserData'=>$TinyUserFinalArray);
                        }else{
            $getCollectionData=array();
        }
         
            return $getCollectionData;
 
        } catch (Exception $ex) {
            Yii::log("CommonUtility:getAllDetailsForSearch::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
    /**
     * @author Anand
     * @uses prepare the filter options
     * @param type $options
     * @return string
     */
    public static function prepareFilterOption($options){
        try{
            $refinedFilter=array();
            $temp=array('type'=>'','filterValue'=>array());
           $type='';
           foreach($options as $key=>$value){
             $temp['type']=$key;
                 foreach($value as $val){
                      if($key=='Buckets'){
                         $type= 'bucket'; 
                         $showchild=1;
                      }else{
                         $type= $val['Type'];
                         $showchild=$val['ShowChild'];
                      }
             array_push($temp['filterValue'],array("label"=>$val['Name'],"value"=>array("label"=>$val['Name'],"id"=>$val['Id'],"type"=>$type,"showChild"=>$showchild)));
               }
             array_push($refinedFilter,$temp); 
             $temp=array('type'=>'','filterValue'=>array());
           }
           return $refinedFilter;
        }catch (Exception $ex) {
            Yii::log("CommonUtility:prepareFilterOption::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
 
}

public static function filterFollowers($followers){
    try{
          $followers = array_filter($followers, function($obj) {
                    static $idList = array();
                    if (in_array($obj["FollowerId"], $idList)) {
                        return false;
                    }
                    $idList [] = $obj["FollowerId"];
                    return true;
                }
             );
             return $followers;
    } catch (Exception $ex) {

    }
}


    /**
     * @description This method is to prepare follower list when edit the inline for Stack Holder, Assigned to and Reported by
     * @author Praveen P
     * @param type $ticketId
     * @param type $projectId
     * @return type
     */
    public static function prepareFollowerDetails($ticketDetails) {
        try {
            if (!empty($ticketDetails["Followers"])) {
            $ticketDetails["Followers"] = CommonUtility::filterFollowers($ticketDetails["Followers"]);
                foreach ($ticketDetails["Followers"] as &$followersList) {
                    $projectFDetails = TinyUserCollection::getMiniUserDetails($followersList['FollowerId']);
                    $followersList["ProfilePicture"] = $projectFDetails["ProfilePicture"];
                    $followersList["UserName"] = $projectFDetails["UserName"];
                }
            }
            usort($ticketDetails["Followers"], function($a, $b) {
                   return $a["DefaultFollower"] <= $b["DefaultFollower"];
            });
            unset($ticketDetails["CreatedOn"]);
            unset($ticketDetails["UpdatedOn"]);
            return $ticketDetails["Followers"];
        } catch (Exception $ex) {
            Yii::log("CommonUtility:prepareFollowerDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

}

?>