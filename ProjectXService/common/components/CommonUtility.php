<?php

namespace common\components;
use common\models\mongo\{TicketCollection,TinyUserCollection,TicketArtifacts,EventCollection};
use common\models\mysql\{Priority,Projects,WorkFlowFields,Bucket,TicketType,StoryFields,StoryCustomFields,PlanLevel,MapListCustomStoryFields,ProjectTeam,Collaborators,Settings};
use Yii;
use yii\base\ErrorException;
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
     * @Description Sets the format of the passed object to JSON or XML and returns the result.
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
     * @Description Returns the extension of given file name. If there is no extension it returns <b>Empty String</b>
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
            Yii::error("CommonUtility:getExtension::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
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
     * @Description Converts the given timestamp in seconds to given time zone, if no time zone is given, it converts to Default Time Zone returned by <i>date_default_timezone_get()</i><br><b>Returned Formats: </b>'m-d-Y h:i:s A','d-m-Y h:i:s A'
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
                return strtotime($time_object->format('m-d-Y h:i:s A'));
            } else {
                return $time_object->format('d-m-Y h:i:s A');
            }
        } catch (\Throwable $ex) {
            Yii::error("CommonUtility:convert_time_zone::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
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
     * @Description Validates the Given date is in right format or not <br><b>Format:</b> 'M-d-Y'
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
     * @Description Converts the given timestamp in seconds to given time zone, if no time zone is given, it converts to Default Time Zone returned by <i>date_default_timezone_get()</i><br><b>Returned Formats: </b>'M-d-Y H:i:s','M-d-Y'
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
            Yii::error("CommonUtility:convert_date_zone::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
/**
 * 
 * @param type $html
 * @param type $length
 * @return type
 * @Description Truncates the Text for Displaying limited content in Activities
 */
    static function refineActivityData($html,$length="35") {
         $html = strip_tags($html);
        if (strlen($html) > $length) {
            $html = substr($html, 0, $length) . "...";
        }
       $html = htmlspecialchars_decode($html);
        return $html;
    }
    /**
     * 
     * @param type $html
     * @param type $length
     * @return type
     * @Description Truncates the Text for Displaying limited content in Activities
     */
    static function refineActivityDataTimeDesc($html,$length="35") {
         $html = strip_tags($html);
        if (strlen($html) > $length) {
            $html = substr($html, 0, $length);
        }
       $html = htmlspecialchars_decode($html);
        return $html;
    }
/**
 * 
 * @param string $html
 * @return string
 * @Description Closes the unclosed HTML tags.
 */
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
     * @Description Truncates the given HTML for given length of String to be displayed. Optionally adds <b>Read More</b> button.
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
            Yii::error("CommonUtility:truncateHtml::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

    /**
     * @Description This method is to prepare data for ticket details page
     * @author Moin Hussain
     * @param type $ticketId
     * @param type $projectId
     * @return type
     * @modification by Anand = Modified TaskId since we are getting Object of subtask insted of just Ids from ticket collection.
     */
    public static function prepareTicketDetails($ticketDetails, $projectId,$timezone, $flag = "part") {
        try {

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
                            $readableDate = $datetime->format('M-d-Y h:i:s A');
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
                return $a["position"] >= $b["position"];
            });
            $projectDetails = Projects::getProjectMiniDetails($ticketDetails["ProjectId"]);
            $ticketDetails["Project"] = $projectDetails;

            $selectFields = [];
            if ($flag == "part") {
                $selectFields = ['Title', 'TicketId'];
            }
            $selectFields = ['Title', 'TicketId', 'Fields.priority', 'Fields.assignedto', 'Fields.workflow','Fields.estimatedpoints'];
            foreach ($ticketDetails["Tasks"] as &$task) {
                $taskDetails = TicketCollection::getTicketDetails($task['TaskId'], $projectId, $selectFields);
                $taskDetails["Title"] = htmlspecialchars_decode($taskDetails["Title"]);
                $task = (array) $taskDetails;
            }
            foreach ($ticketDetails["RelatedStories"] as &$relatedStory) {
                $relatedStoryDetails = TicketCollection::getTicketDetails($relatedStory, $projectId, $selectFields);
                $relatedStory = $relatedStoryDetails;
            }
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
            $ticketDetails["Title"] = htmlspecialchars_decode($ticketDetails["Title"]); 
            
            return $ticketDetails;
        } catch (\Throwable $ex) {
            Yii::error("CommonUtility:prepareTicketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

    /**
     * @Description This method is to prepare data for editing ticket details
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
                            $readableDate = $datetime->format('M-d-Y h:i:s A');
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
                    $value["readable_value"] = "";
                    if($value["value"] != ""){
                         $bucketName = Bucket::getBucketName($value["value"], $ticketDetails["ProjectId"]);
                         $value["readable_value"] = $bucketName;
                    }
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
                }
                if ($storyFieldDetails["Field_Name"] == "tickettype") {

                    $ticketTypeDetails = TicketType::getTicketType($value["value"]);
                    $value["readable_value"] = $ticketTypeDetails;
                    $value["meta_data"] = TicketType::getTicketTypeList();
                }
            }
            $ticketDetails['collaborators'] = ServiceFactory::getCollaboratorServiceInstance()->getProjectTeam($projectId);
            usort($ticketDetails["Fields"], function($a, $b) {
                return $a["position"] >= $b["position"];
            });
            $selectFields = ['Title', 'TicketId','Fields.estimatedpoints'];
            $getchiledTasks=array();
               foreach ($ticketDetails["Tasks"] as &$task) {
                $taskDetails = TicketCollection::getTicketDetails($task['TaskId'], $projectId, $selectFields);
                 array_push($getchiledTasks,$taskDetails);
            }
            $ticketDetails["childTasks"] =$getchiledTasks;
            $projectDetails = Projects::getProjectMiniDetails($ticketDetails["ProjectId"]);
            $ticketDetails["Project"] = $projectDetails;
            $ticketDetails["Title"] = htmlspecialchars_decode($ticketDetails["Title"]); 
            unset($ticketDetails["CreatedOn"]);
            unset($ticketDetails["UpdatedOn"]);
            unset($ticketDetails["ArtifactsRef"]);
            unset($ticketDetails["CommentsRef"]);  
            }else{
              $ticketDetails='';  
            }
           

            return $ticketDetails;
        } catch (\Throwable $ex) {
            Yii::error("CommonUtility:prepareTicketEditDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

    /**
     * @author Moin Hussain
     * @param type $description
     * @return type
     * @Description Processes the ticket desctiption, and replaces the artifacts string with respective HTML tags<br><b>Example:</b><br> <ul><li>For Images - img tag</li><li>Other Documents - Anchor tag</li></ul>
     */

  public static function refineDescription($description){
      try{
           $description = preg_replace("/<a(.*?)>/", "<a$1 target=\"_blank\">", $description);
           
           $uploadedOn = new \MongoDB\BSON\UTCDateTime(time() * 1000);
              $matches=[];
              $userlist=[];//for email purpose added by ryan
              $mention_matches=[];//added by Ryan
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
              } 
              $returnData = array("description"=>$description,"ArtifactsList"=>$artifactsList,"UsersList"=>$userlist);//modified by Ryan,added UsersList as key
              return $returnData;
      } catch (\Throwable $ex) {
            Yii::error("CommonUtility:refineDescription::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
  }
  
  /**
   * @author Moin Hussain
   * @param type $description
   * @return type
   * @Description Processes the ticket desctiption, and replaces the artifacts string with respective HTML tags for sending emails
   */
   public static function refineDescriptionForEmail($description){
      try{
           $description = preg_replace("/<a(.*?)>/", "<a$1 target=\"_blank\">", $description);
           
           $uploadedOn = new \MongoDB\BSON\UTCDateTime(time() * 1000);
              $matches=[];
              $userlist=[];//for email purpose added by ryan
          
              
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
      } catch (\Throwable $ex) {
            Yii::error("CommonUtility:refineDescriptionForEmail::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
  }
  
  
    /**
     * @author Jagadish
     * @return array
     * @Description Returns an Artifact Object
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
        } catch (\Throwable $ex) {
            Yii::error("CommonUtility:getArtifact::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

    /**
     * @author Moin Hussain
     * @param type $ticketDetails
     * @param type $projectId
     * @param type $fieldsOrderArray
     * @param type $flag
     * @return array
     * @Description Prepares an an Array with Ticket Details of a Particular Project for displaying in Ticket Listing page
     */
    public static function prepareDashboardDetails($ticketDetails, $projectId,$timezone, $fieldsOrderArray, $flag = "part",$filter=null) {
        try {
            $newArray = array();
            $arr2ordered = array();
            $ticketId = array("field_name" => "Id", "value_id" => "", "field_value" => $ticketDetails["TicketId"], "other_data" => "","field_type" => "","render_type" => "","field_id" =>"");
            $ticketDetails["Title"]= self::refineActivityData($ticketDetails["Title"],200);
            $ticketTitle = array("field_name" => "Title", "value_id" => "", "field_value" => $ticketDetails["Title"], "other_data" => "","field_type" => "text","render_type" => "text","field_id" =>"");
            array_push($arr2ordered, $ticketId);
            array_push($arr2ordered, $ticketTitle);
            $arr2ordered[1]["other_data"] = sizeof($ticketDetails["Tasks"]); 
             
            $Othervalue = array();
            foreach ($ticketDetails["Fields"] as $key => $value) {

                if ($key == "planlevel") {
                    $Othervalue["planlevel"] = $value["value"];
                    $Othervalue["parentStoryId"] = $ticketDetails['ParentStoryId'];
                    $projectDetails = Projects::getProjectMiniDetails($projectId);
                    $Othervalue["project_name"] =  $projectDetails['ProjectName'];
                    $arr2ordered[0]["other_data"] = $Othervalue;
                }
                
                if (in_array($value["Id"], $fieldsOrderArray)) {

                    if (isset($value["custom_field_id"])) {
                        $storyFieldDetails = StoryCustomFields::getFieldDetails($value["Id"]);
                        if ($storyFieldDetails["Name"] == "List") {
                            $listDetails = MapListCustomStoryFields::getListValue($value["Id"], $value["value"]);
                            $value["readable_value"] = $listDetails;
                            $value["render_type"] = "select";
                            $value["field_id"] = $storyFieldDetails["Id"];
                        }
                    } else {
                        $storyFieldDetails = StoryFields::getFieldDetails($value["Id"]);
                    }
                    $value["title"] = $storyFieldDetails["Title"];                  
                    $value["field_name"] = $storyFieldDetails["Field_Name"];
                    $value["field_type"] = $storyFieldDetails["Name"];
                    $value["field_id"] = $storyFieldDetails["Id"];
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
                            $value["render_type"] = "date";
                            $value["field_id"] = $storyFieldDetails["Id"];
                        } else {
                            $value["readable_value"] = "";
                            $value["render_type"] = "date";
                            $value["field_id"] = $storyFieldDetails["Id"];
                        }
                    }
                    if ($storyFieldDetails["Type"] == 6) {
                        $value["readable_value"] = "";
                        $value["render_type"] = "select";
                        $value["field_id"] = $storyFieldDetails["Id"];
                        if ($value["value"] != "") {
                            $assignedToDetails = TinyUserCollection::getMiniUserDetails($value["value"]);
                            $assignedToDetails["ProfilePicture"] = $assignedToDetails["ProfilePicture"];
                            $value["readable_value"] = $assignedToDetails;
                            $value["render_type"] = "select";  
                            $value["field_id"] = $storyFieldDetails["Id"];
                        }
                    }
                    if ($storyFieldDetails["Type"] == 10) {
                        $value["readable_value"] = "";
                        $value["render_type"] = "select";
                        $value["field_id"] = $storyFieldDetails["Id"];
                        if ($value["value"] != "") {
                            $bucketName = Bucket::getBucketName($value["value"], $ticketDetails["ProjectId"]);
                            $value["readable_value"] = $bucketName;
                            $value["render_type"] = "select";
                            $value["field_id"] = $storyFieldDetails["Id"];
                        }
                    }
                    if ($storyFieldDetails["Field_Name"] == "priority") {
                        $value["readable_value"] = "";
                        $value["render_type"] = "select";
                        $value["field_id"] = $storyFieldDetails["Id"];
                        if ($value["value"] != "") {
                            $priorityDetails = Priority::getPriorityDetails($value["value"]);
                            $value["readable_value"] = $priorityDetails;
                            $value["render_type"] = "select";
                            $value["field_id"] = $storyFieldDetails["Id"];
                        }
                    }
                    if ($storyFieldDetails["Field_Name"] == "planlevel") {
                        $value["readable_value"] = "";
                        $value["render_type"] = "select";
                        $value["field_id"] = $storyFieldDetails["Id"];
                        if ($value["value"] != "") {
                            $planlevelDetails = PlanLevel::getPlanLevelDetails($value["value"]);
                            $value["readable_value"] = $planlevelDetails;
                            $ticketDetails["StoryType"] = $planlevelDetails;
                            $value["render_type"] = "select";
                            $value["field_id"] = $storyFieldDetails["Id"];
                        }
                    }
                    if ($storyFieldDetails["Field_Name"] == "workflow") {
                        $value["readable_value"] = "";
                        $value["render_type"] = "select";
                        $value["field_id"] = $storyFieldDetails["Id"];
                        if ($value["value"] != "") {
                            $workFlowDetails = WorkFlowFields::getWorkFlowDetails($value["value"]);
                            $value["readable_value"] = $workFlowDetails;
                            $value["render_type"] = "select";
                            $value["field_id"] = $storyFieldDetails["Id"];
                        }
                    }
                    if ($storyFieldDetails["Field_Name"] == "tickettype") {
                        $value["readable_value"] = "";
                        $value["render_type"] = "select";
                        $value["field_id"] = $storyFieldDetails["Id"];
                        if ($value["value"] != "") {
                            $ticketTypeDetails = TicketType::getTicketType($value["value"]);
                            $value["readable_value"] = $ticketTypeDetails;
                            $value["render_type"] = "select";
                            $value["field_id"] = $storyFieldDetails["Id"];
                        }
                    }
                    if ($storyFieldDetails["Field_Name"] == "dod") {
                        $value["readable_value"] = "";
                        $value["render_type"] = "text";
                        $value["field_id"] = $storyFieldDetails["Id"];
                        if ($value["value"] != "") {
                            $value["readable_value"] = $value["value"];
                            $value["render_type"] = "text";
                            $value["field_id"] = $storyFieldDetails["Id"];
                        }
                    }
                    if ($storyFieldDetails["Field_Name"] == "estimatedpoints") {
                        $value["readable_value"] = "";
                        $value["render_type"] = "text";
                        $value["field_id"] = $storyFieldDetails["Id"];
                        if ($value["value"] != "") {
                            $value["readable_value"] = $value["value"];
                            $value["render_type"] = "text";
                            $value["field_id"] = $storyFieldDetails["Id"];
                        }
                    }

                    $tempArray = array("field_name" => "", "value_id" => "", "field_value" => "", "other_data" => "","field_type" => "","render_type" => "","field_id" =>"");
                    $tempArray["field_name"] = $value["field_name"];
                    $tempArray["field_type"] = $value["field_type"];
                    $tempArray["render_type"] = $value["render_type"];
                    $tempArray["value_id"] = $value["value"];
                    $tempArray["field_id"] = $value["field_id"];
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
              $arrow = array("field_name" => "arrow", "value_id" => "", "field_value" => "", "other_data" => "","field_type" => "","render_type" => "","field_id" =>"");
             if($filter != null){
                $showChild = $filter->showChild ;
               $filterType = $filter->type ;
                if($showChild==0){
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
           
            return $ticketDetails;
        } catch (\Throwable $ex) {
            Yii::error("CommonUtility:prepareDashboardDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

    /**
     * @author Moin Hussain
     * @param type $value
     * @param type $projectId
     * @Description Prepares an Activity Object.
     */
    public static function prepareActivity(&$value, $projectId,$timezone) {
        try {
            $userProfile = TinyUserCollection::getMiniUserDetails($value["ActivityBy"]);
            $value["ActivityBy"] = $userProfile;
            $datetime = $value["ActivityOn"]->toDateTime();
            $datetime->setTimezone(new \DateTimeZone($timezone));
            $readableDate = $datetime->format('M-d-Y h:i:s A');
            $value["ActivityOn"] = $readableDate;
            $propertyChanges = $value["PropertyChanges"];
            $poppedFromChild = $value["PoppedFromChild"];
            if (count($propertyChanges) > 0) {
                foreach ($value["PropertyChanges"] as &$property) {
                    CommonUtility::prepareActivityProperty($property,$projectId,$timezone,$poppedFromChild);
                }
            }
        } catch (\Throwable $ex) {
            Yii::error("CommonUtility:prepareActivity::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

    /**
     * @author Moin Hussain
     * @param type $property
     * @param type $projectId
     * @return type
     * @Description Prepares a Property of the given Activity Object.
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
                $property["PreviousValue"] = self::refineActivityData($property["PreviousValue"]);
                $property["NewValue"] = self::refineActivityData($property["NewValue"]);
            }
           else if($actionFieldName=='Followed' || $actionFieldName=='Unfollowed' || $actionFieldName=='Related' || $actionFieldName=='ChildTask' || $actionFieldName=='Unrelated') {
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
                             $ticketDetails = TicketCollection::getTicketDetails((int)$property["NewValue"], $projectId,["TicketId","Title","Fields"]);
                             $action=array("Id"=>$ticketDetails['TicketId'],"Name"=>$ticketDetails['Title'],'PlanLevel'=>$ticketDetails['Fields']['planlevel']['value']);
                             $newVal="Story/Task";
                             $property["type"]='related';
                             $property["ActionFieldTitle"]=$newVal;
                             $property["NewValue"] = $action;break; 
               case 'Unrelated':$newVal="unrealted";
                             $ticketDetails = TicketCollection::getTicketDetails((int)$property["NewValue"], $projectId,["TicketId","Title","Fields"]);
                             $action=array("Id"=>$ticketDetails['TicketId'],"Name"=>$ticketDetails['Title'],'PlanLevel'=>$ticketDetails['Fields']['planlevel']['value']);
                             $newVal="Story/Task";
                             $property["type"]='unrelated';
                             $property["ActionFieldTitle"]=$newVal;
                             $property["NewValue"] = $action;break;            
              case 'ChildTask':
                             $ticketDetails = TicketCollection::getTicketDetails((int)$property["NewValue"], $projectId,["TicketId","Title","Fields"]);
                             $action=array("Id"=>$ticketDetails['TicketId'],"Name"=>$ticketDetails['Title'],'PlanLevel'=>$ticketDetails['Fields']['planlevel']['value']);
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
                    $property["NewValue"] = "-none-";
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
                if($property["PreviousValue"] != ""){
                    $bucketDetails = Bucket::getBucketName($property["PreviousValue"], $projectId); 
                    $property["PreviousValue"] = $bucketDetails["Name"];
                }
               
               
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
            $readableDate = $datetime->format('M-d-Y h:i:s A');
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
            if ($property["NewValue"] === "") {
                $property["NewValue"] = "-none-";
            }
            return $property;
        } catch (\Throwable $ex) {
            Yii::error("CommonUtility:prepareActivityProperty::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

  
    /**
     * @author Moin Hussain
     * @param type $recipient_list
     * @param type $text_message
     * @param type $subject
     * @param type $attachment_list
     * @return type
     * @Description Sends Email to given set of recipient_list, with given text_message and attachments
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
        "<tr><td align='center' style='border:1px solid #f0f0f0; padding:5px;font-family:Arial; font-size:12px;line-height:40px;color:#333333; text-align:center;' >Update Your <a href=".''.Yii::$app->params['AppURL'].'/NotificationSettings'." style='color:#0199e0; text-decoration:none;'>Email Alert Preferences</a></td></tr>
        <tr><td bgcolor='#787878' align='left' valign='top' height='35'>
        <table width='600'border='0' align='left' cellpadding='0' cellspacing='0' height:'35'>
  <tr>
       <td width='10' height:'35'>&nbsp;</td>
       <td width='500' align='left' height:'35' style='font-family:Arial; font-size:12px;line-height:35px;text-align:left; color:#fff;'>This message was sent by <a href='#' style='color:#d0ebff;font-size:13dpx;text-decoration:none;'>ProjectX</a></td>
        <td width='80' align='right'height:'35' >
        <a href='#'><img src=".''.Yii::$app->params['EmailServerURL'].'/files/tool/facebook.png'." style=' border:none; outline:none;'/></a><a href='#' style='margin-left:6px; margin-right:6px;'><img src=".''.Yii::$app->params['EmailServerURL'].'/files/tool/twit.png'." style=' border:none; outline:none;'/></a><a href='#'><img src=".''.Yii::$app->params['EmailServerURL'].'/files/tool/linkedin.png'." style=' border:none; outline:none;'/></a></td>
        <td width='10'>&nbsp;</td>
        </tr>
       </table>
       </td>
       </tr>
</table>
</body>
</html>";
         ApiClient::SetApiKey(Yii::$app->params['ElasticEmailApiKey']);
        $attachments=array();//list of artifacts
        $EEemail = new Email();
        $from=Yii::$app->params['ProjectEmail'];
        $fromName= $mailingName;
        $html=$text_message;
        $text=$text_message;
        $response = $EEemail->Send($subject, $from, $fromName, null, null, null, null, null, null, $recipient_list, array(), array(), array(), array(), array(), null, null, $html, $text,null,null,null,null,null,$attachments);		
        }catch (\Throwable $ex) {
            Yii::error("CommonUtility:sendEmail::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
     /**
    * @author Padmaja
    * @param Param $ticketCollectionCount
    * @return array
    * @Description Returns the Count of occurrences of the given search string in each project.
    */
    public static function searchStringCountForProjectwise($ticketCommentsCount){
        try{
            $individualCount=array();
            if(!empty($ticketCommentsCount)){
                foreach($ticketCommentsCount as $extractProjectCount){
                    error_log("########--------".$extractProjectCount['_id']['ProjectId']);
                }
                return $projectwiseCount = array_count_values($individualCount);
            }else{
              return $projectwiseCount =0;  
            }
        }catch (\Throwable $ex) {
            Yii::error("CommonUtility:searchStringCountForProjectwise::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
       
    }
   /**
    * @author Padmaja
    * @param type $searchString
    * @param type $page
    * @param type $searchFlag
    * @param type $projectId
    * @return array
    * @Description Retruns the Data to be displayed based on the occurence of the search string.
    */
        public static function getAllDetailsForSearch($searchString,$page,$searchFlag="",$projectId="",$pageLength,$userId,$pName=""){
        try{
                $page = $page ;
                if ($page == 1) {
                    $offset = $page - 1;
                    $limit = $pageLength;   
                } else {
                    $offset = ($page - 1) * $pageLength;
                    $limit = $pageLength;
                }
                error_log("projectId-----".$searchString);
            if ( !empty($searchString)) {
                $collectAllCount=array();
                $TicketCollFinalArray = array();
                $TicketArtifactsFinalArray = array();
                $TicketCommentsFinalArray = array();
                $TinyUserFinalArray = array();
                /** Array for count **/
                $prepareArrayForAllCount=array();
                $ticketCommentsCount=array();
                $ticketCollectionCount=array();
                $tinyUserDataCount=array();
                $ticketArtifactsCount=array();
                $individualCount=array();
                $individualCountForTask=array();
                $individualCountForComment=array();
                $individualCountForArtifacts=array();
                $individualCountForUser=array();
                $collectionCount=array();
                $collectAllCountforComments =array(); 
                $collectAllCountforArtifacts =array(); 
                $collectAllCountforTasks =array();  
                 /** Ended Array for count **/
                $options = array(
                    "limit" =>$limit,
                    "skip" => $offset
                );
                if(!empty($pName)){
                   $getProjectInfo= Projects::getProjectDetails($pName);
                   $projectId=$getProjectInfo['PId'];
                }
                $totalProjects=ProjectTeam::getProjectsCountByUserId($userId);
                $projectIdArray=array();
                $getProjectIdArray=array();
                 foreach($totalProjects as $getProjectId){
                   $getProjectIdArray['ProjectId'] =$getProjectId['ProjectId'];
                   array_push($projectIdArray,(int)$getProjectId['ProjectId']);
                }
                $totalCountForAll=array();
                $countForEchProject=array();
                 error_log("############^^^^^".$projectId);
                if($searchFlag==1){
                    $collection = Yii::$app->mongodb->getCollection('TicketCollection');
                 if (strpos($searchString, '#') !== false || is_numeric($searchString)!= false) {
                         error_log("asssssss-----------------------");
                           if(strpos($searchString, '#') !== false){
                           $getTicketIdNumber = explode('#', $searchString);
                           error_log("numerrrrrrrrrrrrr".$getTicketIdNumber[1]);
                            if(is_numeric($getTicketIdNumber[1])){
                               $searchString=str_replace("#","",$searchString);
                           }
                           if(!empty($projectId)){
                              $cursor =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("PlainDescription"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId))),array(),$options);
                             $cursorForCount =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("PlainDescription"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId))),array());
                            }else{
                                 $cursor =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("PlainDescription"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)))),array(),$options); 
                                $cursorForCount =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("PlainDescription"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)))),array()); 
                           }

                        }else{
                            if(!empty($projectId)){  
                                $cursor =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("PlainDescription"=>array('$regex'=>'^'.$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId))),array(),$options);
                                $cursorForCount =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("PlainDescription"=>array('$regex'=>'^'.$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId))),array());
                                
                            }else{
                                $cursor =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("PlainDescription"=>array('$regex'=>'^'.$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)))),array(),$options);
                                $cursorForCount =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("PlainDescription"=>array('$regex'=>'^'.$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)))),array());
                                
                            }
                        }
                    }else{
                        $searchString = \quotemeta($searchString);
                        $searchString=htmlspecialchars($searchString);
                        if(!empty($projectId)){ 
                           $cursor =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("PlainDescription"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId))),array(),$options);
                           $cursorForCount =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("PlainDescription"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId))),array());
                         }else{
                            $cursor =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("PlainDescription"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)))),array(),$options);
                            $cursorForCount =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("PlainDescription"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)))),array());
                            
                         }
                    }
                    $ticketCollectionData = iterator_to_array($cursor);
                    $ticketCollectionCount= iterator_to_array($cursorForCount);
                   error_log("ticketCOl@@@@@@@@@@lection count--------".count($ticketCollectionCount));
                 
                    $TicketCollFinalArray = array();
                     foreach($ticketCollectionData as $extractCollection){
                        $forTicketCollection['TicketId'] = $extractCollection['TicketId'];
                        $forTicketCollection['Title'] = $extractCollection['Title'];
                        $forTicketCollection['description'] = !empty($extractCollection['PlainDescription'])?$extractCollection['PlainDescription']:'';
                        if(strpos($forTicketCollection['description'],$searchString) !=false){
                           $forTicketCollection['description']= $forTicketCollection['description'];
                        }
                        $forTicketCollection['planlevel'] = $extractCollection['Fields']['planlevel']['value_name'];
                        $forTicketCollection['reportedby'] = $extractCollection['Fields']['reportedby']['value_name'];
                        $UpdatedOn = $extractCollection['UpdatedOn'];
                        if(isset($UpdatedOn)){
                            $datetime = $UpdatedOn->toDateTime();
                            $readableDate =$datetime->format('M-d-Y h:i:s A');
                            $forTicketCollection['UpdatedOn'] = $readableDate;
                        }
                        $projectDetails = Projects::getProjectMiniDetails($extractCollection["ProjectId"]);
                        $forTicketCollection['Project'] = $projectDetails; 
                        array_push($TicketCollFinalArray, $forTicketCollection);
                    }
                    $matchArray = array('Activities.PlainDescription'=>array('$regex'=>$searchString,'$options' => 'i'),'Activities.Status'=>1,'ProjectId'=>array('$in'=>$projectIdArray));
                    if(!empty($projectId)){
                        $matchArray = array('Activities.PlainDescription'=>array('$regex'=>$searchString,'$options' => 'i'),'Activities.Status'=>1,'ProjectId'=>(int)$projectId);
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
                    $countPipeline = array(
                        array('$unwind' => '$Activities'),
                        array('$match' => $matchArray),
                         array(
                            '$group' => array(
                                '_id' =>  array('TicketId'=> '$TicketId', 'ProjectId'=> '$ProjectId'),
                                "commentData" => array('$push' => '$Activities'),

                             ),
                        )
                        );
                    $ticketCommentsData = $query->aggregate($pipeline);
                    $ticketCommentsCount = $query->aggregate($countPipeline);
                    error_log("comment-343---".count($ticketCommentsCount));
                    $TicketCommentsFinalArray = array();
                    $commentsArray= array();
                    $totalTicketComments=count($ticketCommentsData);
                        foreach($ticketCommentsData as $extractComments){
                           $selectFields = ['Title','ProjectId', 'TicketId','Description','Fields.planlevel.value_name','Fields.reportedby.value_name','UpdatedOn'];
                           $getTicketDetails = TicketCollection::getTicketDetails($extractComments['_id']['TicketId'],$extractComments['_id']['ProjectId'],$selectFields);
                           $forTicketComments['TicketId'] =  $extractComments['_id'];
                           $forTicketComments['Title'] =$getTicketDetails['Title'];
                           $commentsfinalArray =array();
                          foreach($extractComments['commentData'] as $eachOne){
                              $searchString= stripslashes($searchString);
                              $commentsArray['CrudeCDescription']=$eachOne['PlainDescription'];
                              $commentsArray['Slug']=$eachOne['Slug'];
                              $commentsArray['ActivityOn']=$eachOne['ActivityOn'];
                               if(stripos($commentsArray['CrudeCDescription'],$searchString)!==false){
                                array_push($commentsfinalArray,$commentsArray);
                              }
                           }
                            $forTicketComments['comments']=$commentsfinalArray;
                           $forTicketComments['planlevel'] = $getTicketDetails['Fields']['planlevel']['value_name'];
                           $forTicketComments['reportedby'] = $getTicketDetails['Fields']['reportedby']['value_name'];
                            $UpdatedOn = $getTicketDetails['UpdatedOn'];
                            if(isset($UpdatedOn)){
                                $datetime = $UpdatedOn->toDateTime();
                                $readableDate = $datetime->format('M-d-Y h:i:s A');
                                $forTicketComments['UpdatedOn'] = $readableDate;
                           }
                            $projectDetails = Projects::getProjectMiniDetails($getTicketDetails["ProjectId"]);
                            $forTicketComments['Project'] = $projectDetails; 
                            array_push($TicketCommentsFinalArray, $forTicketComments);
                       }
                    $collection = Yii::$app->mongodb->getCollection('TicketArtifacts');
                     $cursor =  $collection->find(array('$or'=>array(array("Artifacts.OriginalFileName"=>array('$regex'=>$searchString,'$options' => 'i'))),'ProjectId'=>array('$in'=>$projectIdArray)),array(),$options);
                    $cursorCountArtifacts =  $collection->find(array('$or'=>array(array("Artifacts.OriginalFileName"=>array('$regex'=>$searchString,'$options' => 'i'))),'ProjectId'=>array('$in'=>$projectIdArray)),array());
                    if(!empty($projectId)){
                       $cursor =  $collection->find(array('$or'=>array(array("Artifacts.OriginalFileName"=>array('$regex'=>$searchString,'$options' => 'i'))),'ProjectId'=>(int)$projectId),array(),$options); 
                       $cursorCountArtifacts =  $collection->find(array('$or'=>array(array("Artifacts.OriginalFileName"=>array('$regex'=>$searchString,'$options' => 'i'))),'ProjectId'=>(int)$projectId),array()); 
                       
                    }
                    $ticketArtifactsData = iterator_to_array($cursor);
                    $ticketArtifactsCount=iterator_to_array($cursorCountArtifacts);
                    error_log("ticketArtifactsCount---333--------".count($ticketArtifactsCount)); 
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
                            $readableDate =$datetime->format('M-d-Y h:i:s A');
                            $forTicketArtifacts['UpdatedOn'] = $readableDate;
                         }
                        $projectDetails = Projects::getProjectMiniDetails($getTicketDetails["ProjectId"]);
                        $forTicketArtifacts['Project'] = $projectDetails; 
                        array_push($TicketArtifactsFinalArray, $forTicketArtifacts);

                    }

                    $collection = Yii::$app->mongodb->getCollection('TinyUserCollection');
                    $cursor=$collection->find(array('$or'=>array(array("Email"=>array('$regex'=>$searchString,'$options' => 'i')),array("UserName"=>array('$regex'=>$searchString,'$options' => 'i')))),array(),$options);
                    $cursorCountTinyUser=$collection->find(array('$or'=>array(array("Email"=>array('$regex'=>$searchString,'$options' => 'i')),array("UserName"=>array('$regex'=>$searchString,'$options' => 'i')))),array());
                    $tinyUserData = iterator_to_array($cursor);
                    $tinyUserDataCount = iterator_to_array($cursorCountTinyUser);
                     error_log("ticketuserCount-----------".count($tinyUserDataCount));
                    $TinyUserFinalArray = array();
                    foreach($tinyUserData as $extractUserData){
                        $forUsercollection['Title']=  $extractUserData['UserName'];
                        $forUsercollection['ProfilePicture']=  Yii::$app->params['ServerURL'].$extractUserData['ProfilePicture'];
                        $forUsercollection['description']=  $extractUserData['Email'];
                        $UpdatedOn=  $extractUserData['UpdatedOn'];
                        if(isset($UpdatedOn)){
                            $datetime = $UpdatedOn->toDateTime();
                            $readableDate =$datetime->format('M-d-Y h:i:s A');
                            $forUsercollection['UpdatedOn'] = $readableDate; 
                          }
                        array_push($TinyUserFinalArray, $forUsercollection);
                    }

             
                }else if($searchFlag==2){
                    $searchString = \quotemeta($searchString);
                    $searchString=htmlspecialchars($searchString);
                    $matchArray = array('Activities.PlainDescription'=>array('$regex'=>$searchString,'$options' => 'i'),'Activities.Status'=>1,'ProjectId'=>array('$in'=>$projectIdArray));
                    if(!empty($projectId)){
                        $matchArray = array('Activities.PlainDescription'=>array('$regex'=>$searchString,'$options' => 'i'),'Activities.Status'=>1,'ProjectId'=>(int)$projectId);
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
                       $countPipeline = array(
                        array('$unwind' => '$Activities'),
                        array('$match' => $matchArray),
                         array(
                            '$group' => array(
                                '_id' =>  array('TicketId'=> '$TicketId', 'ProjectId'=> '$ProjectId'),
                                "commentData" => array('$push' => '$Activities'),

                             ),
                        )
                        );
                    $ticketCommentsData = $query->aggregate($pipeline);
                    $ticketCommentsCount = $query->aggregate($countPipeline);
                     error_log("comment-343---".count($ticketCommentsCount));
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
                              $commentsArray['CrudeCDescription']=$eachOne['PlainDescription'];
                              $commentsArray['Slug']=$eachOne['Slug'];
                              $commentsArray['ActivityOn']=$eachOne['ActivityOn'];
                              error_log("comment test -------".$searchString);
                               if(stripos($commentsArray['CrudeCDescription'],$searchString)!==false){
                                array_push($commentsfinalArray,$commentsArray);
                              }
                           }
                            $forTicketComments['comments']=$commentsfinalArray;
                           $forTicketComments['planlevel'] = $getTicketDetails['Fields']['planlevel']['value_name'];
                           $forTicketComments['reportedby'] = $getTicketDetails['Fields']['reportedby']['value_name'];
                            $UpdatedOn = $getTicketDetails['UpdatedOn'];
                            if(isset($UpdatedOn)){
                                $datetime = $UpdatedOn->toDateTime();
                                $readableDate = $datetime->format('M-d-Y h:i:s A');
                                $forTicketComments['UpdatedOn'] = $readableDate;
                           }
                            $projectDetails = Projects::getProjectMiniDetails($getTicketDetails["ProjectId"]);
                            $forTicketComments['Project'] = $projectDetails;
                            $forTicketComments['ticketCommentsCount'] = count($ticketCommentsCount); 
                            array_push($TicketCommentsFinalArray, $forTicketComments);
                       }
                }else if($searchFlag==3){
                    $collection = Yii::$app->mongodb->getCollection('TinyUserCollection');
                    $cursor=$collection->find(array('$or'=>array(array("Email"=>array('$regex'=>$searchString,'$options' => 'i')),array("UserName"=>array('$regex'=>$searchString,'$options' => 'i')))),array(),$options);
                    $cursorCountTinyUser=$collection->find(array('$or'=>array(array("Email"=>array('$regex'=>$searchString,'$options' => 'i')),array("UserName"=>array('$regex'=>$searchString,'$options' => 'i')))),array());
                    $tinyUserData = iterator_to_array($cursor);
                    $tinyUserDataCount = iterator_to_array($cursorCountTinyUser);
                    $TinyUserFinalArray = array();
                     foreach($tinyUserData as $extractUserData){
                        $forUsercollection['Title']=  $extractUserData['UserName'];
                        $forUsercollection['ProfilePicture']=  Yii::$app->params['ServerURL'].$extractUserData['ProfilePicture'];
                        $forUsercollection['description']=  $extractUserData['Email'];
                        $UpdatedOn=  $extractUserData['UpdatedOn'];
                        if(isset($UpdatedOn)){
                            $datetime = $UpdatedOn->toDateTime();
                            $readableDate =$datetime->format('M-d-Y h:i:s A');
                            $forUsercollection['UpdatedOn'] = $readableDate; 
                          }
                        $forUsercollection['tinyUserDataCount']= count($tinyUserDataCount);
                        array_push($TinyUserFinalArray, $forUsercollection);
                    }
                    
                }else if($searchFlag==4){
                    $collection = Yii::$app->mongodb->getCollection('TicketArtifacts');
                    $cursor =  $collection->find(array('$or'=>array(array("Artifacts.OriginalFileName"=>array('$regex'=>$searchString,'$options' => 'i'))),'ProjectId'=>array('$in'=>$projectIdArray)),array(),$options);
                    $cursorCountArtifacts =  $collection->find(array('$or'=>array(array("Artifacts.OriginalFileName"=>array('$regex'=>$searchString,'$options' => 'i'))),'ProjectId'=>array('$in'=>$projectIdArray)),array());
                    if(!empty($projectId)){
                       $cursor =  $collection->find(array('$or'=>array(array("Artifacts.OriginalFileName"=>array('$regex'=>$searchString,'$options' => 'i'))),'ProjectId'=>(int)$projectId),array(),$options); 
                       $cursorCountArtifacts =  $collection->find(array('$or'=>array(array("Artifacts.OriginalFileName"=>array('$regex'=>$searchString,'$options' => 'i'))),'ProjectId'=>(int)$projectId),array()); 
                       
                    }
                    $ticketArtifactsData = iterator_to_array($cursor);
                    $ticketArtifactsCount=iterator_to_array($cursorCountArtifacts);
                    error_log("ticketArtifactsCount---333--------".count($ticketArtifactsCount)); 
                                    
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
                            $readableDate =$datetime->format('M-d-Y h:i:s A');
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
                             $cursor =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("PlainDescription"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId))),array(),$options);
                             $cursorForCount =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("PlainDescription"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId))),array());
                             
                           }else{
                                 error_log("www--".$getTicketIdNumber[1]);
                                $cursor =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("PlainDescription"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)))),array(),$options); 
                                $cursorForCount =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("PlainDescription"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)))),array()); 
                           }

                        }else{
                            if(!empty($projectId)){  
                                $cursor =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("PlainDescription"=>array('$regex'=>'^'.$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId))),array(),$options);
                                $cursorForCount =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("PlainDescription"=>array('$regex'=>'^'.$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId))),array());
                                
                            }else{
                                $cursor =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("PlainDescription"=>array('$regex'=>'^'.$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)))),array(),$options);
                                $cursorForCount =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("PlainDescription"=>array('$regex'=>'^'.$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)))),array());
                                
                            }
                        }
                    }else{
                        $searchString = \quotemeta($searchString);
                        $searchString=htmlspecialchars($searchString);
                     
                         if(!empty($projectId)){ 
                           $cursor =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("PlainDescription"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId))),array(),$options);
                           $cursorForCount =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("PlainDescription"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId))),array());
                            error_log("333--------------tttt---------");
                         }else{
                            $cursor =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("PlainDescription"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)))),array(),$options);
                            $cursorForCount =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("PlainDescription"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)))),array());
                            
                         }
                    }
                    $ticketCollectionData = iterator_to_array($cursor);
                    $ticketCollectionCount= iterator_to_array($cursorForCount);
                    $TicketCollFinalArray = array();
                    $individualCount=array();
                     foreach($ticketCollectionData as $extractCollection){
                        $forTicketCollection['TicketId'] = $extractCollection['TicketId'];
                        $forTicketCollection['Title'] = $extractCollection['Title'];
                        $forTicketCollection['description'] = !empty($extractCollection['PlainDescription'])?$extractCollection['PlainDescription']:'';
                        if(strpos($forTicketCollection['description'],$searchString) !=false){
                           $forTicketCollection['description']= $forTicketCollection['description'];
                        }
                        $forTicketCollection['planlevel'] = $extractCollection['Fields']['planlevel']['value_name'];
                        $forTicketCollection['reportedby'] = $extractCollection['Fields']['reportedby']['value_name'];
                        $UpdatedOn = $extractCollection['UpdatedOn'];
                        if(isset($UpdatedOn)){
                            $datetime = $UpdatedOn->toDateTime();
                            $readableDate =$datetime->format('M-d-Y h:i:s A');
                            $forTicketCollection['UpdatedOn'] = $readableDate;
                        }
                        $projectDetails = Projects::getProjectMiniDetails($extractCollection["ProjectId"]);
                        $forTicketCollection['Project'] = $projectDetails;
                        $forTicketCollection['ticketCollectionCount'] = count($ticketCollectionCount); 
                        array_push($TicketCollFinalArray, $forTicketCollection);
                    }
                      if(!empty($ticketCollectionCount)){
                        foreach($ticketCollectionCount as $extractProjectCount){
                            array_push($individualCountForTask, $extractProjectCount['ProjectId']);
                             
                        }
                         $collectAllCountforTasks = array_count_values($individualCountForTask);
                        }else{
                         $collectAllCountforTasks =array();  
                        }
                 }else{
                   
                                        /* onload */
                    $collection = Yii::$app->mongodb->getCollection('TicketCollection');
                    $options = array(
                        "limit" =>$limit,
                        "skip" => $offset
                    );
                 if (strpos($searchString, '#') !== false || is_numeric($searchString)!= false) {
                         error_log("2222-----------------------");
                        if(strpos($searchString, '#') !== false){
                           $getTicketIdNumber = explode('#', $searchString);
                           error_log("numerrrrrrrrrrrrr".$getTicketIdNumber[1]);
                            if(is_numeric($getTicketIdNumber[1])){
                               $searchString=str_replace("#","",$searchString);
                           }
                           if(!empty($projectId)){
                                error_log("12122---".$getTicketIdNumber[1]);
                             $cursor =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("PlainDescription"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId))),array(),$options);
                             $cursorForCount =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("PlainDescription"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId))),array());
                             
                           }else{
                                 error_log("www--".$getTicketIdNumber[1]);
                                $cursor =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("PlainDescription"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)))),array(),$options); 
                                $cursorForCount =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("PlainDescription"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)))),array()); 
                           }

                        }else{
                            if(!empty($projectId)){  
                                $cursor =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("PlainDescription"=>array('$regex'=>'^'.$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId))),array(),$options);
                                $cursorForCount =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("PlainDescription"=>array('$regex'=>'^'.$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId))),array());
                                
                            }else{
                                $cursor =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("PlainDescription"=>array('$regex'=>'^'.$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)))),array(),$options);
                                $cursorForCount =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("PlainDescription"=>array('$regex'=>'^'.$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)))),array());
                                
                            }
                        }
                    }else{
                         error_log("333-----------------------");
                        $searchString = \quotemeta($searchString);
                        $searchString=htmlspecialchars($searchString);
                     
                         if(!empty($projectId)){ 
                           $cursor =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("PlainDescription"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId))),array(),$options);
                           $cursorForCount =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("PlainDescription"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId))),array());
                            error_log("333--------------tttt---------");
                         }else{
                            $cursor =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("PlainDescription"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)))),array(),$options);
                            $cursorForCount =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("PlainDescription"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)))),array());
                            
                         }
                    }
                    $ticketCollectionData = iterator_to_array($cursor);
                    $ticketCollectionCount= iterator_to_array($cursorForCount);
                    $TicketCollFinalArray = array();
                      foreach($ticketCollectionData as $extractCollection){
                        $forTicketCollection['TicketId'] = $extractCollection['TicketId'];
                        $forTicketCollection['Title'] = $extractCollection['Title'];
                        $forTicketCollection['description'] = !empty($extractCollection['PlainDescription'])?$extractCollection['PlainDescription']:'';
                        if(strpos($forTicketCollection['description'],$searchString) !=false){
                           $forTicketCollection['description']= $forTicketCollection['description'];
                        }
                        $forTicketCollection['planlevel'] = $extractCollection['Fields']['planlevel']['value_name'];
                        $forTicketCollection['reportedby'] = $extractCollection['Fields']['reportedby']['value_name'];
                        $UpdatedOn = $extractCollection['UpdatedOn'];
                        if(isset($UpdatedOn)){
                            $datetime = $UpdatedOn->toDateTime();
                            $readableDate =$datetime->format('M-d-Y h:i:s A');
                            $forTicketCollection['UpdatedOn'] = $readableDate;
                        }
                        $projectDetails = Projects::getProjectMiniDetails($extractCollection["ProjectId"]);
                        $forTicketCollection['Project'] = $projectDetails; 
                        array_push($TicketCollFinalArray, $forTicketCollection);
                    }

                     $matchArray = array('Activities.PlainDescription'=>array('$regex'=>$searchString,'$options' => 'i'),'Activities.Status'=>1,'ProjectId'=>array('$in'=>$projectIdArray));
                    if(!empty($projectId)){
                        $matchArray = array('Activities.PlainDescription'=>array('$regex'=>$searchString,'$options' => 'i'),'Activities.Status'=>1,'ProjectId'=>(int)$projectId);
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
                        $countPipeline = array(
                        array('$unwind' => '$Activities'),
                        array('$match' => $matchArray),
                         array(
                            '$group' => array(
                                '_id' =>  array('TicketId'=> '$TicketId', 'ProjectId'=> '$ProjectId'),
                                "commentData" => array('$push' => '$Activities'),

                             ),
                        )
                        );
                    $ticketCommentsData = $query->aggregate($pipeline);
                    $ticketCommentsCount = $query->aggregate($countPipeline);
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
                              $commentsArray['CrudeCDescription']=$eachOne['PlainDescription'];
                              $commentsArray['Slug']=$eachOne['Slug'];
                              $commentsArray['ActivityOn']=$eachOne['ActivityOn'];
                               if(stripos($commentsArray['CrudeCDescription'],$searchString)!==false){
                                array_push($commentsfinalArray,$commentsArray);
                              }
                           }
                            $forTicketComments['comments']=$commentsfinalArray;
                            $forTicketComments['planlevel'] = $getTicketDetails['Fields']['planlevel']['value_name'];
                            $forTicketComments['reportedby'] = $getTicketDetails['Fields']['reportedby']['value_name'];
                            $UpdatedOn = $getTicketDetails['UpdatedOn'];
                            if(isset($UpdatedOn)){
                                $datetime = $UpdatedOn->toDateTime();
                                $readableDate = $datetime->format('M-d-Y h:i:s A');
                                $forTicketComments['UpdatedOn'] = $readableDate;
                           }
                            $projectDetails = Projects::getProjectMiniDetails($getTicketDetails["ProjectId"]);
                             $forTicketComments['Project'] = $projectDetails; 
                            array_push($TicketCommentsFinalArray, $forTicketComments);
                       }
             
                    $collection = Yii::$app->mongodb->getCollection('TicketArtifacts');
                    $cursor =  $collection->find(array('$or'=>array(array("Artifacts.OriginalFileName"=>array('$regex'=>$searchString,'$options' => 'i'))),'ProjectId'=>array('$in'=>$projectIdArray)),array(),$options);
                    $cursorCountArtifacts =  $collection->find(array('$or'=>array(array("Artifacts.OriginalFileName"=>array('$regex'=>$searchString,'$options' => 'i'))),'ProjectId'=>array('$in'=>$projectIdArray)),array());
                    if(!empty($projectId)){
                       $cursor =  $collection->find(array('$or'=>array(array("Artifacts.OriginalFileName"=>array('$regex'=>$searchString,'$options' => 'i'))),'ProjectId'=>(int)$projectId),array(),$options); 
                       $cursorCountArtifacts =  $collection->find(array('$or'=>array(array("Artifacts.OriginalFileName"=>array('$regex'=>$searchString,'$options' => 'i'))),'ProjectId'=>(int)$projectId),array()); 
                    }
                    $ticketArtifactsData = iterator_to_array($cursor);
                    $ticketArtifactsCount=iterator_to_array($cursorCountArtifacts);
                   
                    error_log("ticketArtifactsCount-555--333--------".count($ticketArtifactsCount)); 
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
                             if($getArtifact['ArtifactType']=="image"){
                             $path=Yii::$app->params['ServerURL'].$getArtifact['ThumbnailPath']."/".$getArtifact['OriginalFileName'];
                             $Showimage = "<a href='" . $path . "' target='_blank'/>" . $getArtifact['OriginalFileName'] . "</a>";
                             array_push($getArtifactsEach,$Showimage);}
                            else{array_push($getArtifactsEach,$getArtifact['OriginalFileName']);}
                             }
                        $forTicketArtifacts['description'] =$getArtifactsEach;
                        $forTicketArtifacts['planlevel'] = $getTicketDetails['Fields']['planlevel']['value_name'];
                        $forTicketArtifacts['reportedby'] = $getTicketDetails['Fields']['reportedby']['value_name'];
                        $UpdatedOn = $getTicketDetails['UpdatedOn'];
                        if(isset($UpdatedOn)){
                            $datetime = $UpdatedOn->toDateTime();
                            $readableDate =$datetime->format('M-d-Y h:i:s A');
                            $forTicketArtifacts['UpdatedOn'] = $readableDate;
                         }
                        $projectDetails = Projects::getProjectMiniDetails($getTicketDetails["ProjectId"]);
                        $forTicketArtifacts['Project'] = $projectDetails; 
                        array_push($TicketArtifactsFinalArray, $forTicketArtifacts);

                    }
                    $collection = Yii::$app->mongodb->getCollection('TinyUserCollection');
                    $cursor=$collection->find(array('$or'=>array(array("Email"=>array('$regex'=>$searchString,'$options' => 'i')),array("UserName"=>array('$regex'=>$searchString,'$options' => 'i')))),array(),$options);
                    $cursorCountTinyUser=$collection->find(array('$or'=>array(array("Email"=>array('$regex'=>$searchString,'$options' => 'i')),array("UserName"=>array('$regex'=>$searchString,'$options' => 'i')))),array());
                    $tinyUserData = iterator_to_array($cursor);
                    $tinyUserDataCount = iterator_to_array($cursorCountTinyUser);
                    error_log("ticketuserCount-----sdfdsf------".count($tinyUserDataCount));
                    $TinyUserFinalArray = array();
                 
                    foreach($tinyUserData as $extractUserData){
                        $forUsercollection['Title']=  $extractUserData['UserName'];
                        $forUsercollection['ProfilePicture']=  Yii::$app->params['ServerURL'].$extractUserData['ProfilePicture'];
                        $forUsercollection['description']=  $extractUserData['Email'];
                        $UpdatedOn=  $extractUserData['UpdatedOn'];
                        if(isset($UpdatedOn)){
                            $datetime = $UpdatedOn->toDateTime();
                            $readableDate =$datetime->format('M-d-Y h:i:s A');
                            $forUsercollection['UpdatedOn'] = $readableDate; 
                          }
                         array_push($TinyUserFinalArray, $forUsercollection);
                    }
                }
                // Ended ----------
            
                $renderCount=self::getStringCountForGLobalsearch($searchString,$projectId,$userId,$projectIdArray,$limit,$offset);
                    $mainData=array('ticketCollection'=>$TicketCollFinalArray,'ticketComments'=>$TicketCommentsFinalArray,'ticketArtifacts'=>$TicketArtifactsFinalArray,'tinyUserData'=>$TinyUserFinalArray);
                 
                    $getCollectionData=array('mainData'=>$mainData,'dataCount'=>$renderCount['dataCount'],'projectCountForAll'=>$renderCount['getProjectCountForAll']);
                    }else{
                     $getCollectionData=array();
                    }
         
                return $getCollectionData;
 
        } catch (\Throwable $ex) {
            Yii::error("CommonUtility:getAllDetailsForSearch::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    

    /**
     * @author Anand
     * @Description Prepares the filter options
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
                   $type= (array_key_exists("Type",$val))?$val['Type']:strtolower(preg_replace('/\s+/', '', $temp['type']));
                   $showchild=(array_key_exists("ShowChild",$val))?$val['ShowChild']:1;
                   if($key == "State"){
                       $showchild =0;
                   }
                   $valueData=array("label"=>$val['Name'],"id"=>$val['Id'],"type"=>$type,"showChild"=>$showchild,'isChecked'=>false,'canDelete'=>false);
                   if($key == "Status"){
                      $valueData['stateId']=$val['State'];
                   }
                   if($key == "Personal Filters"){
                      $valueData['canDelete']=true;
                      $valueData['showChild']=0;
                   }
                   array_push($temp['filterValue'],array("label"=>$val['Name'],"value"=>$valueData));
               }
             array_push($refinedFilter,$temp); 
             $temp=array('type'=>'','filterValue'=>array());
           }
           return $refinedFilter;
        }catch (\Throwable $ex) {
            Yii::error("CommonUtility:prepareFilterOption::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
 
}
/**
 * 
 * @param type $followers
 * @return type
 * @throws ErrorException
 * @Description Returns the list of Followers for a filter.
 */
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
    } catch (\Throwable $ex) {
            Yii::error("CommonUtility:filterFollowers::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
}
public static function getUniqueArrayObjects($arrayOfObjects){
    try{
          $uniqueArrayObjects = array_filter($arrayOfObjects, function($obj) {
                    static $idList = array();
                    if (in_array($obj["ActivityOn"], $idList)) {
                        return false;
                    }
                    $idList [] = $obj["ActivityOn"];
                    return true;
                }
             );
             return $uniqueArrayObjects;
    } catch (\Throwable $ex) {
            Yii::error("CommonUtility:getUniqueArrayObjects::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
}

    /**
     * @Description This method is to prepare follower list when edit the inline for Stack Holder, Assigned to and Reported by
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
        } catch (\Throwable $ex) {
            Yii::error("CommonUtility:prepareFollowerDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
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
    public static function prepareDashboardDetailsTemp($ticketDetails, $projectId, $fieldsOrderArray, $flag = "part",$filter=null) {
        try {
            $ticketCollectionModel = new TicketCollection();
            $storyFieldsModel = new StoryFields();
            $storyCustomFieldsModel = new StoryCustomFields();
            $tinyUserModel = new TinyUserCollection();
            $bucketModel = new Bucket();
            $priorityModel = new Priority();
            $mapListModel = new MapListCustomStoryFields();
            $planlevelModel = new PlanLevel();
            $workFlowModel = new WorkFlowFields();
            $ticketTypeModel = new TicketType();
            $newArray = array();
            $finalData=array();
            $arr2ordered = array();
            error_log("-------------".$ticketDetails["TicketId"]);
            //get the sub task count
            if(empty($ticketDetails['ParentStoryId']) )  
                $totalSubtasks = sizeof($ticketDetails["Tasks"]);
            else
                $totalSubtasks = '0'; 
            
            $finalData = array("Id" => $ticketDetails["TicketId"], "title" => $ticketDetails["Title"],'totalSubtasks'=>$totalSubtasks);
                $finalData['ProfilePicture']='';
        foreach ($fieldsOrderArray as $value) {
            
            error_log($value."-----1--ass--".$ticketDetails["Fields"][$value]["value"]);

            if($value=='assignedto'){
                if ($ticketDetails["Fields"][$value]["value"] != "") {
                    $assignedToDetails = TinyUserCollection::getMiniUserDetails($ticketDetails["Fields"][$value]["value"]);
                    $finalData['ProfilePicture'] =$assignedToDetails["ProfilePicture"];
                }
            $finalData[$value] = $ticketDetails["Fields"][$value]["value_name"];   
            }
            if($value=='priority'){
                $finalData[$value] = $ticketDetails["Fields"][$value]["value_name"];   
            }
            if($value=='workflow'){
                if ($ticketDetails["Fields"][$value]["value"] != "") {
                    $workFlowDetails = WorkFlowFields::getWorkFlowDetails($ticketDetails["Fields"][$value]["value"]);
                    $workFlowStatus = $workFlowDetails['State'];
                    $finalData['workFlowStatus'] = $workFlowStatus;
                }
                $finalData[$value] = $ticketDetails["Fields"][$value]["value_name"];   
            }
            if($value=='bucket'){
                $finalData[$value] = $ticketDetails["Fields"][$value]["value_name"];   
            }
            if($value=='duedate'){
                 if ($ticketDetails["Fields"][$value]["value"] != "") {error_log($ticketDetails["Fields"][$value]["value"]."---if--".$value);
                            $datetime1 = $ticketDetails["Fields"][$value]["value"]->toDateTime();
                            $timezone="Asia/Kolkata";
                            $datetime1->setTimezone(new \DateTimeZone($timezone));
                            $duedate = $datetime1->format('M-d-Y');
                        } else {error_log("---else--".$value);
                            $duedate = "";
                        }
                        error_log($duedate."-----".$value);
            $finalData[$value] = $duedate;   
            }
            if($value=='planlevel'){
            $finalData[$value] = $ticketDetails["Fields"][$value]["value"];   
            }
              
            
        }
        if($ticketDetails["ParentStoryId"]!=""){
             $finalData["hide"] = true;
                $finalData["childClass"] = "child_".$ticketDetails["ParentStoryId"];
        }else{
            $finalData["hide"] = false;
             $finalData["childClass"] = "";
        }
            return $finalData;
        } catch (\Throwable $ex) {
            Yii::error("CommonUtility:prepareDashboardDetailsTemp::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    /**
     * @author Padmaja
     * @Description This method is used to get Ticket details for dashboard
     * @return type array
     */
    public static function getTicketDetailsForDashboard($userId,$page,$pageLength,$projectFlag=""){
        try{
                $collection = Yii::$app->mongodb->getCollection('TicketCollection');
                $assignedtoDetails =  $collection->count(array('$or'=>array( array( "Fields.assignedto.value"=>(int)$userId))));
                $followersDetails =  $collection->count(array('$or'=>array(array("Followers.FollowerId"=>(int)$userId))));
             if($assignedtoDetails !=0 || $followersDetails != 0){
                 error_log("=============ascrollllllllll-------");
               $projectDetails = ProjectTeam::getAllProjects($userId,$pageLength,$page);
                if($projectFlag==1){   
                   $prepareDetails= self::getAllProjectDetailsByUser($collection,$projectDetails,$assignedtoDetails,$followersDetails,$userId);
                }elseif($projectFlag==2){
                    $activitiesArray=array();
                    $getActivities=array();
                    $prepareDetails=array();
                    $getEventDetails= EventCollection::getAllActivities();
                    foreach($getEventDetails as $extractedEventDetails){
                       foreach($extractedEventDetails['Data'] as $getId){
                           $activitiesArray= EventCollection::getActivitiesById($getId);
                           $getActivities['ProjectId']=$activitiesArray['ProjectId'];
                           $getActivities['OccuredIn']=$activitiesArray['OccuredIn'];
                           $getActivities['ReferringId']=$activitiesArray['ReferringId'];
                           $getActivities['DisplayAction']=$activitiesArray['DisplayAction'];
                           $getActivities['ActionType']=$activitiesArray['ActionType'];
                           $getActivities['ActionBy']=$activitiesArray['ActionBy'];
                           $tinyUserDetails=TinyUserCollection::getMiniUserDetails($activitiesArray['ActionBy']);
                           $getActivities['userName']=$tinyUserDetails['UserName'];
                           $getActivities['Miscellaneous']=$activitiesArray['Miscellaneous'];
                           $datetime1=$activitiesArray['CreatedOn']->toDateTime();
                           $timezone="Asia/Kolkata";
                           $datetime1->setTimezone(new \DateTimeZone($timezone));
                           $getActivities['createdDate']= $datetime1->format('M-d-Y');
                           foreach($activitiesArray['ChangeSummary'] as $changeSummary){
                               error_log("-----s--".$changeSummary['ActionOn']);
                                $getActivities['ChangeSummary']['ActionOn']=$changeSummary['ActionOn'];
                                $getActivities['ChangeSummary']['OldValue']=$changeSummary['OldValue'];
                                $getActivities['ChangeSummary']['NewValue']=$changeSummary['NewValue'];
                               
                           }
                           array_push($prepareDetails, $getActivities);
                       }
                  }
                    $checkDates=array();
                    $preparedDates['createdDate']= array_values(array_unique(array_column($prepareDetails,'createdDate'))) ;
                    $checkDates = array_fill(0,count($preparedDates['createdDate']), []);
                    foreach($prepareDetails as $extracteData){
                       $idx = array_search($extracteData['createdDate'], $preparedDates['createdDate']);
                       if(!is_array($checkDates[$idx])){
                           $checkDates[$idx]=array();
                       }
                       array_push($checkDates[$idx], $extracteData);
                        
                    }
                    $prepareDetails=$checkDates;
                 }else{
                  $prepareDetails= self::getAllProjectDetailsByUser($collection,$projectDetails,$assignedtoDetails,$followersDetails,$userId);
                }
             }
             return array('AssignedToData'=>$assignedtoDetails,'FollowersDetails'=>$followersDetails,'ProjectwiseInfo'=>$prepareDetails);  
        }catch (\Throwable $ex) {
            Yii::error("CommonUtility:getTicketDetailsForDashboard::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
         
    }
    /**
     * @author Padmaja
     * @Description This method is used to get Project details for dashboard
     * @return type array
     */
    public static function getAllProjectDetailsByUser($collection,$projectDetails,$assignedtoDetails,$followersDetails,$userId){        
        try{   
                $prepareDetails=array();
                  foreach($projectDetails as $extractDetails){
                     $projects=Projects::getProjectMiniDetails($extractDetails['ProjectId']);
                     $userDetails=Collaborators::getCollaboratorById($projects['CreatedBy']);
                     $projectTeamDetails=Collaborators::getFilteredProjectTeam($extractDetails['ProjectId'],$userDetails['UserName']);
                     $projectInfo['ProjectId']=$extractDetails['ProjectId'];
                     $projectInfo['createdBy']=$userDetails['UserName'];
                      $projectInfo['ProfilePic']=$projectTeamDetails[0]['ProfilePic'];
                      $projectInfo['projectName']=$projects['ProjectName'];
                      $projectInfo['CreatedOn'] =$extractDetails['CreatedOn'];
                      $projecTeam=ProjectTeam::getProjectTeamCount($extractDetails['ProjectId']);
                      $projectInfo['Team']=$projecTeam['TeamCount'];
                      $projectInfo['assignedtoDetails'] =  $collection->count(array('$or'=>array( array( "Fields.assignedto.value"=>(int)$userId,"ProjectId"=>(int)$extractDetails['ProjectId']))));
                      $projectInfo['followersDetails'] =  $collection->count(array('$or'=>array(array("Followers.FollowerId"=>(int)$userId,"ProjectId"=>(int)$extractDetails['ProjectId']))));
                      $projectInfo['closedTickets'] =TicketCollection::getActiveOrClosedTicketsCount($extractDetails['ProjectId'],$userId,'Fields.state.value',6,array());
                      $projectInfo['activeTickets'] =TicketCollection::getActiveOrClosedTicketsCount($extractDetails['ProjectId'],$userId,'Fields.state.value',3,array());
                      $bucketDetails=Bucket::getActiveBucketId($extractDetails['ProjectId']);
                      if($bucketDetails=='failure'){
                          $projectInfo['currentBucket'] ='';
                     }else{
                      $projectInfo['currentBucket'] =$bucketDetails['Name'];
                     }
                      array_push($prepareDetails,$projectInfo);
                    }
                    return $prepareDetails;
             } catch (\Throwable $ex) {
            Yii::error("CommonUtility:getAllProjectDetailsByUser::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
      /**
     * @author Padmaja
     * @Description This method is used to get Last Id Project details for dashboard
     * @return type array
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
            Yii::error("CommonUtility:getLastProjectDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
        
    }
       /**
     * @author Padmaja
     * @Description This method is used for showing counts for global search
     * @return type array
     */
    public static function getStringCountForGLobalsearch($searchString,$projectId,$userId,$projectIdArray,$limit,$offset){
        error_log("enters here");       
        $options = array(
                    "limit" =>$limit,
                    "skip" => $offset
                );
                   /** Array for count **/
                $prepareArrayForAllCount=array();
                $ticketCommentsCount=array();
                $ticketCollectionCount=array();
                $tinyUserDataCount=array();
                $ticketArtifactsCount=array();
                $individualCount=array();
                $individualCountForTask=array();
                $individualCountForComment=array();
                $individualCountForArtifacts=array();
                $individualCountForUser=array();
                $collectionCount=array();
                $collectAllCountforComments =array(); 
                $collectAllCountforArtifacts =array(); 
                $collectAllCountforTasks =array();  
                 /** Ended Array for count **/
                $collection = Yii::$app->mongodb->getCollection('TicketCollection');
                 if (strpos($searchString, '#') !== false || is_numeric($searchString)!= false) {
                         if(strpos($searchString, '#') !== false){
                           $getTicketIdNumber = explode('#', $searchString);
                            if(is_numeric($getTicketIdNumber[1])){
                               $searchString=str_replace("#","",$searchString);
                           }
                           if(!empty($projectId)){
                              $cursorForCount =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("PlainDescription"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId))),array());
                            }else{
                                 $cursorForCount =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("PlainDescription"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)))),array()); 
                           }

                        }else{
                            if(!empty($projectId)){  
                                 $cursorForCount =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("PlainDescription"=>array('$regex'=>'^'.$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId))),array());
                            }else{
                                $cursorForCount =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("PlainDescription"=>array('$regex'=>'^'.$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)))),array());
                             }
                        }
                    }else{
                        $searchString = \quotemeta($searchString);
                        $searchString=htmlspecialchars($searchString);
                     
                         if(!empty($projectId)){ 
                           $cursorForCount =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("PlainDescription"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)$projectId))),array());
                         }else{
                             $cursorForCount =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("PlainDescription"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),'ProjectId'=>array('$in'=>$projectIdArray)))),array());
                            
                         }
                    }
                    $ticketCollectionCount= iterator_to_array($cursorForCount);
                    $TicketCollFinalArray = array();
                   /*count logic starts*/
                         if(!empty($ticketCollectionCount)){
                        foreach($ticketCollectionCount as $extractProjectCount){
                            array_push($individualCountForTask, $extractProjectCount['ProjectId']);
                             
                        }
                        $collectAllCountforTasks = array_count_values($individualCountForTask);
                        }else{
                         $collectAllCountforTasks =array();  
                        }
                        $matchArray = array('Activities.PlainDescription'=>array('$regex'=>$searchString,'$options' => 'i'),'Activities.Status'=>1,'ProjectId'=>array('$in'=>$projectIdArray));
                        if(!empty($projectId)){
                            $matchArray = array('Activities.PlainDescription'=>array('$regex'=>$searchString,'$options' => 'i'),'Activities.Status'=>1,'ProjectId'=>(int)$projectId);
                        }
                        $query = Yii::$app->mongodb->getCollection('TicketComments');
               
                        $countPipeline = array(
                        array('$unwind' => '$Activities'),
                        array('$match' => $matchArray),
                         array(
                            '$group' => array(
                                '_id' =>  array('TicketId'=> '$TicketId', 'ProjectId'=> '$ProjectId'),
                                "commentData" => array('$push' => '$Activities'),

                             ),
                        )
                        );
                     $ticketCommentsCount = $query->aggregate($countPipeline);
                    $TicketCommentsFinalArray = array();
                       if(!empty($ticketCommentsCount)){
                        foreach($ticketCommentsCount as $extractcommentsCount){
                            array_push($individualCountForComment,$extractcommentsCount['_id']['ProjectId']);
                        }
                        $collectAllCountforComments = array_count_values($individualCountForComment);
                        }else{
                         $collectAllCountforComments =array();  
                        }
                     $collection = Yii::$app->mongodb->getCollection('TicketArtifacts');
                    $cursorCountArtifacts =  $collection->find(array('$or'=>array(array("Artifacts.OriginalFileName"=>array('$regex'=>$searchString,'$options' => 'i'))),'ProjectId'=>array('$in'=>$projectIdArray)),array());
                    if(!empty($projectId)){
                       $cursorCountArtifacts =  $collection->find(array('$or'=>array(array("Artifacts.OriginalFileName"=>array('$regex'=>$searchString,'$options' => 'i'))),'ProjectId'=>(int)$projectId),array()); 
                    }
                    $ticketArtifactsCount=iterator_to_array($cursorCountArtifacts);
                      if(!empty($ticketArtifactsCount)){
                        foreach($ticketArtifactsCount as $extractArtifactCount){
                             array_push($individualCountForArtifacts,$extractArtifactCount['ProjectId']);
                        }
                        $collectAllCountforArtifacts = array_count_values($individualCountForArtifacts);
                        }else{
                         $collectAllCountforArtifacts =array();  
                        }
                    $collection = Yii::$app->mongodb->getCollection('TinyUserCollection');
                    $cursorCountTinyUser=$collection->find(array('$or'=>array(array("Email"=>array('$regex'=>$searchString,'$options' => 'i')),array("UserName"=>array('$regex'=>$searchString,'$options' => 'i')))),array());
                    $tinyUserDataCount = iterator_to_array($cursorCountTinyUser);
                    $prepareArrayForAllCount=array(count($ticketCollectionCount),count($ticketCommentsCount),count($ticketArtifactsCount),count($tinyUserDataCount));
                    $getAllCount=array_sum($prepareArrayForAllCount);  
                    $dataCount=array();
                    $dataCount['TaskCount']=count($ticketCollectionCount);
                    $dataCount['commentsCount']=count($ticketCommentsCount);
                    $dataCount['artifactsCount']=count($ticketArtifactsCount);
                    $dataCount['userDataCount']=count($tinyUserDataCount);
                    $dataCount['allCount']=$getAllCount;
                    if(!empty($dataCount)){
                       $dataCount=$dataCount;
                   }else{
                      $dataCount=array();
                   }
                $AllArray=array('All'=>$getAllCount);
                $getProjectCountForAll=self::array_sum_combine($AllArray,$collectAllCountforTasks,$collectAllCountforComments,$collectAllCountforArtifacts);
                if(!empty($getProjectCountForAll)){
                    $getProjectCountForAll=$getProjectCountForAll;
                }else{
                    $getProjectCountForAll=0;
                }
                  return array('dataCount'=>$dataCount,'getProjectCountForAll'=>$getProjectCountForAll);
                     
             
    }
     /**
     * @author Padmaja
     * @Description This method is used for adding all array
     * @return type array
     */
   public static function array_sum_combine($arr0,$arr1,$arr2,$arr3)
    {
      $return = array();
      $args = func_get_args();
        foreach ($args as $arr)
        {
          foreach ($arr as $k => $v)
          {
            if(is_int($k)){
              error_log("######----------".$k);
              $projectDetails=Projects::getProjectMiniDetails($k);
              $k= $projectDetails['ProjectName'];
            }
            if (!array_key_exists($k, $return))
            {
                $return[$k] = 0;
            }
               $return[$k] += $v;

          }
        }
      return $return;
    }
 
 

}

?>
