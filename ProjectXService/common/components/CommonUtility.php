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

    /**
     * @author Moin Hussain
     * @param type $date
     * @return type
     */
    public static function validateDate($date) {
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
                return strtotime($time_object->format('Y-m-d H:i:s'));
            } else {
                return $time_object->format('d-m-Y');
            }
        } catch (Exception $ex) {
            Yii::log("CommonUtility:convert_date_zone::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

    static function refineActivityData($html,$length="35") {
        // $html = CommonUtility::closetags($html);
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
    public static function prepareTicketDetails($ticketDetails, $projectId, $flag = "part") {
        try {
            $ticketCollectionModel = new TicketCollection();
            // $ticketDetails = $ticketCollectionModel->getTicketDetails($ticketId,$projectId);
            $storyFieldsModel = new StoryFields();
            $storyCustomFieldsModel = new StoryCustomFields();
            $tinyUserModel = new TinyUserCollection();
            $bucketModel = new Bucket();
            $priorityModel = new Priority();
            $mapListModel = new MapListCustomStoryFields();
            $planlevelModel = new PlanLevel();
            $workFlowModel = new WorkFlowFields();
            $ticketTypeModel = new TicketType();


            foreach ($ticketDetails["Fields"] as &$value) {
                if (isset($value["custom_field_id"])) {
                    $storyFieldDetails = $storyCustomFieldsModel->getFieldDetails($value["Id"]);
                    if ($storyFieldDetails["Name"] == "List") {

                        $listDetails = $mapListModel->getListValue($value["Id"], $value["value"]);
                        $value["readable_value"] = $listDetails;
                    }
                } else {
                    $storyFieldDetails = $storyFieldsModel->getFieldDetails($value["Id"]);
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
                            $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                            $readableDate = $datetime->format('M-d-Y');
                        } else {
                            $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
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
                        $assignedToDetails = $tinyUserModel->getMiniUserDetails($value["value"]);
                        $assignedToDetails["ProfilePicture"] = Yii::$app->params['ServerURL'] . $assignedToDetails["ProfilePicture"];
                        $value["readable_value"] = $assignedToDetails;
                    }
                }
                if ($storyFieldDetails["Type"] == 10) {
                    $value["readable_value"] = "";
                    if ($value["value"] != "") {
                        $bucketName = $bucketModel->getBucketName($value["value"], $ticketDetails["ProjectId"]);
                        $value["readable_value"] = $bucketName;
                    }
                }
                if ($storyFieldDetails["Field_Name"] == "priority") {
                    $value["readable_value"] = "";
                    if ($value["value"] != "") {
                        $priorityDetails = $priorityModel->getPriorityDetails($value["value"]);
                        $value["readable_value"] = $priorityDetails;
                        $ticketDetails["StoryPriority"] = $priorityDetails;
                    }
                }
                if ($storyFieldDetails["Field_Name"] == "planlevel") {
                    $value["readable_value"] = "";
                    if ($value["value"] != "") {
                        $planlevelDetails = $planlevelModel->getPlanLevelDetails($value["value"]);
                        $value["readable_value"] = $planlevelDetails;
                        $ticketDetails["StoryType"] = $planlevelDetails;
                    }
                }
                if ($storyFieldDetails["Field_Name"] == "workflow") {

                    $value["readable_value"] = "";
                    if ($value["value"] != "") {
                        $workFlowDetails = $workFlowModel->getWorkFlowDetails($value["value"]);
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
                        $ticketTypeDetails = $ticketTypeModel->getTicketType($value["value"]);
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
            $projectObj = new Projects();
            $projectDetails = $projectObj->getProjectMiniDetails($ticketDetails["ProjectId"]);
            $ticketDetails["Project"] = $projectDetails;

            $selectFields = [];
            if ($flag == "part") {
                $selectFields = ['Title', 'TicketId'];
            }
            $selectFields = ['Title', 'TicketId', 'Fields.priority', 'Fields.assignedto', 'Fields.workflow'];
            foreach ($ticketDetails["Tasks"] as &$task) {
                $taskDetails = $ticketCollectionModel->getTicketDetails($task['TaskId'], $projectId, $selectFields);
                $task = (array) $taskDetails;
            }
            foreach ($ticketDetails["RelatedStories"] as &$relatedStory) {
                $relatedStoryDetails = $ticketCollectionModel->getTicketDetails($relatedStory, $projectId, $selectFields);
                $relatedStory = $relatedStoryDetails;
            }
            if (!empty($ticketDetails["Followers"])) {

                $ticketDetails["Followers"] = array_filter($ticketDetails["Followers"], function($obj) {
                    static $idList = array();
                    if (in_array($obj["FollowerId"], $idList)) {
                        return false;
                    }
                    $idList [] = $obj["FollowerId"];
                    return true;
                }
                );
                foreach ($ticketDetails["Followers"] as &$followersList) {
                    //error_log($followersList['FollowerId']."----Follower--1--".print_r($followersList,1));

                    $projectFDetails = $tinyUserModel->getMiniUserDetails($followersList['FollowerId']);
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
    public static function prepareTicketEditDetails($ticketId, $projectId) {
        try {
            $ticketCollectionModel = new TicketCollection();
            $ticketDetails = $ticketCollectionModel->getTicketDetails($ticketId, $projectId);
            $storyFieldsModel = new StoryFields();
            $storyCustomFieldsModel = new StoryCustomFields();
            $tinyUserModel = new TinyUserCollection();
            $bucketModel = new Bucket();
            $priorityModel = new Priority();
            $mapListModel = new MapListCustomStoryFields();
            $planlevelModel = new PlanLevel();
            $workFlowModel = new WorkFlowFields();
            $ticketTypeModel = new TicketType();
            $workFlowDetails = array();

            foreach ($ticketDetails["Fields"] as &$value) {
                if (isset($value["custom_field_id"])) {
                    $storyFieldDetails = $storyCustomFieldsModel->getFieldDetails($value["Id"]);
                    if ($storyFieldDetails["Name"] == "List") {

                        $listDetails = $mapListModel->getListValue($value["Id"], $value["value"]);
                        $value["readable_value"] = $listDetails;
                    }
                } else {
                    $storyFieldDetails = $storyFieldsModel->getFieldDetails($value["Id"]);
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
                            $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                            $readableDate = $datetime->format('M-d-Y');
                        } else {
                            $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                            $readableDate = $datetime->format('M-d-Y H:i:s');
                        }
                        $value["readable_value"] = $readableDate;
                    } else {
                        $value["readable_value"] = "";
                    }
                }





                if ($storyFieldDetails["Type"] == 6) {
                    $assignedToDetails = $tinyUserModel->getMiniUserDetails($value["value"]);
                    $value["readable_value"] = $assignedToDetails;
                }
                if ($storyFieldDetails["Type"] == 10) {

                    $bucketName = $bucketModel->getBucketName($value["value"], $ticketDetails["ProjectId"]);
                    $value["readable_value"] = $bucketName;
                    $value["meta_data"] = $bucketModel->getBucketsList($projectId);
                }
                if ($storyFieldDetails["Field_Name"] == "priority") {

                    $priorityDetails = $priorityModel->getPriorityDetails($value["value"]);
                    $value["readable_value"] = $priorityDetails;
                    $value["meta_data"] = $priorityModel->getPriorityList();
                }
                if ($storyFieldDetails["Field_Name"] == "planlevel") {

                    $planlevelDetails = $planlevelModel->getPlanLevelDetails($value["value"]);
                    $value["readable_value"] = $planlevelDetails;
                    $ticketDetails["StoryType"] = $planlevelDetails;
                    $value["meta_data"] = $planlevelModel->getPlanLevelList();
                }
                if ($storyFieldDetails["Field_Name"] == "workflow") {


                    $workFlowDetails = $workFlowModel->getWorkFlowDetails($value["value"]);
                    $value["readable_value"] = $workFlowDetails;
                    $value["meta_data"] = $workFlowModel->getStoryWorkFlowList($ticketDetails['WorkflowType'],$value["value"]);
                }
                if ($storyFieldDetails["Field_Name"] == "state") {
                   $value["value"] =$workFlowDetails['State'];
                   $value["readable_value"] = $workFlowDetails['State'];
//                    $value["meta_data"] = $workFlowModel->getStoryWorkFlowList($ticketDetails['WorkflowType'],$value["value"]);
                }
                if ($storyFieldDetails["Field_Name"] == "tickettype") {

                    $ticketTypeDetails = $ticketTypeModel->getTicketType($value["value"]);
                    $value["readable_value"] = $ticketTypeDetails;
                    $value["meta_data"] = $ticketTypeModel->getTicketTypeList();
                }
            }
            $ticketDetails['collaborators'] = ServiceFactory::getCollaboratorServiceInstance()->getProjectTeam($projectId);
            usort($ticketDetails["Fields"], function($a, $b) {
                // echo $a["position"]."\n";
                return $a["position"] >= $b["position"];
            });
            //  return $ticketDetails["Fields"];
            // $ticketDetails["Fields"]="";
            $projectObj = new Projects();
            $projectDetails = $projectObj->getProjectMiniDetails($ticketDetails["ProjectId"]);
            $ticketDetails["Project"] = $projectDetails;



            unset($ticketDetails["CreatedOn"]);
            unset($ticketDetails["UpdatedOn"]);
            unset($ticketDetails["ArtifactsRef"]);
            unset($ticketDetails["CommentsRef"]);

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
    public static function prepareDashboardDetails($ticketDetails, $projectId, $fieldsOrderArray, $flag = "part",$filter=null) {
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

            $arr2ordered = array();

            $ticketId = array("field_name" => "Id", "value_id" => "", "field_value" => $ticketDetails["TicketId"], "other_data" => "");
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
                        $storyFieldDetails = $storyCustomFieldsModel->getFieldDetails($value["Id"]);
                        if ($storyFieldDetails["Name"] == "List") {

                            $listDetails = $mapListModel->getListValue($value["Id"], $value["value"]);
                            $value["readable_value"] = $listDetails;
                        }
                    } else {
                        $storyFieldDetails = $storyFieldsModel->getFieldDetails($value["Id"]);
                    }
                    $value["title"] = $storyFieldDetails["Title"];

                    $value["field_name"] = $storyFieldDetails["Field_Name"];
                    if ($storyFieldDetails["Type"] == 4 || $storyFieldDetails["Type"] == 5) {
                        if ($value["value"] != "") {
                            $datetime = $value["value"]->toDateTime();
                            if ($storyFieldDetails["Type"] == 4) {
                                $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                                $readableDate = $datetime->format('M-d-Y');
                            } else {
                                $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
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
                            $assignedToDetails = $tinyUserModel->getMiniUserDetails($value["value"]);
                            $assignedToDetails["ProfilePicture"] = $assignedToDetails["ProfilePicture"];
                            $value["readable_value"] = $assignedToDetails;
                        }
                    }
                    if ($storyFieldDetails["Type"] == 10) {
                        $value["readable_value"] = "";
                        if ($value["value"] != "") {
                            $bucketName = $bucketModel->getBucketName($value["value"], $ticketDetails["ProjectId"]);
                            $value["readable_value"] = $bucketName;
                        }
                    }
                    if ($storyFieldDetails["Field_Name"] == "priority") {
                        $value["readable_value"] = "";
                        if ($value["value"] != "") {
                            $priorityDetails = $priorityModel->getPriorityDetails($value["value"]);
                            $value["readable_value"] = $priorityDetails;
                        }
                    }
                    if ($storyFieldDetails["Field_Name"] == "planlevel") {
                        $value["readable_value"] = "";
                        if ($value["value"] != "") {
                            $planlevelDetails = $planlevelModel->getPlanLevelDetails($value["value"]);
                            $value["readable_value"] = $planlevelDetails;
                            $ticketDetails["StoryType"] = $planlevelDetails;
                        }
                    }
                    if ($storyFieldDetails["Field_Name"] == "workflow") {

                        $value["readable_value"] = "";
                        if ($value["value"] != "") {
                            $workFlowDetails = $workFlowModel->getWorkFlowDetails($value["value"]);
                            $value["readable_value"] = $workFlowDetails;
                        }
                    }
                    if ($storyFieldDetails["Field_Name"] == "tickettype") {
                        $value["readable_value"] = "";
                        if ($value["value"] != "") {
                            $ticketTypeDetails = $ticketTypeModel->getTicketType($value["value"]);
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
    public static function prepareActivity(&$value, $projectId) {
        try {
            $tinyUserModel = new TinyUserCollection();
            $userProfile = $tinyUserModel->getMiniUserDetails($value["ActivityBy"]);
            $value["ActivityBy"] = $userProfile;
            $datetime = $value["ActivityOn"]->toDateTime();
            $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
            $readableDate = $datetime->format('M-d-Y H:i:s');
            $value["ActivityOn"] = $readableDate;
            $propertyChanges = $value["PropertyChanges"];
            $poppedFromChild = $value["PoppedFromChild"];
            if (count($propertyChanges) > 0) {
                foreach ($value["PropertyChanges"] as &$property) {
                    CommonUtility::prepareActivityProperty($property,$projectId,$poppedFromChild);
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
    public static function prepareActivityProperty(&$property,$projectId,$poppedFromChild="") {
        try {
            $tinyUserModel = new TinyUserCollection();
            $fieldName = $property["ActionFieldName"];

            $storyFieldDetails = StoryFields::getFieldDetails($fieldName, "Field_Name");
            $type = $storyFieldDetails["Type"];
            $actionFieldName = $property["ActionFieldName"];
            $property["ActionFieldTitle"] = $fieldName;
            if ($storyFieldDetails["Title"] != "" && $storyFieldDetails["Title"] != null) {
                $property["ActionFieldTitle"] = $storyFieldDetails["Title"];
            }
           if($poppedFromChild !=""){
                $ticketDetails = TicketCollection::getTicketDetails($poppedFromChild, $projectId,["TicketId","Title"]);
                error_log(print_r($ticketDetails,1));
                $ticketInfo = $ticketDetails["TicketId"]." ".$ticketDetails["Title"];
                $property["ActionFieldTitle"] = $property["ActionFieldTitle"];
              
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


            if ($type == 6) {
                if ($property["PreviousValue"] != "") {
                    $property["PreviousValue"] = $tinyUserModel->getMiniUserDetails($property["PreviousValue"]);
                }
                if ($property["NewValue"] != "") {
                    $property["NewValue"] = $tinyUserModel->getMiniUserDetails($property["NewValue"]);
                } else {
                    $property["NewValue"] = "-none-";
                }
                $property["type"] = "user";
            }
            if ($fieldName == "workflow") {
                $workflowDetails = WorkFlowFields::getWorkFlowDetails($property["PreviousValue"]);
                $property["PreviousValue"] = $workflowDetails["Name"];
                $workflowDetails = WorkFlowFields::getWorkFlowDetails($property["NewValue"]);
                $property["NewValue"] = $workflowDetails["Name"];
            }
            if ($fieldName == "priority") {
                $priorityDetails = Priority::getPriorityDetails($property["PreviousValue"]);
                $property["PreviousValue"] = $priorityDetails["Name"];
                $priorityDetails = Priority::getPriorityDetails($property["NewValue"]);
                $property["NewValue"] = $priorityDetails["Name"];
            }
            if ($type == 4) {
                if ($property["PreviousValue"] != "") {
                    $datetime = $property["PreviousValue"]->toDateTime();
                    $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                    $property["PreviousValue"] = $datetime->format('M-d-Y');
                }


                $datetime = $property["NewValue"]->toDateTime();
                $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                $property["NewValue"] = $datetime->format('M-d-Y');
            }
            if ($type == 8) {
                //due date
            }
            if ($type == 10) {
                //bucket
                $bucketDetails = Bucket::getBucketName($property["PreviousValue"], $projectId);
                $property["PreviousValue"] = $bucketDetails["Name"];
                $bucketDetails = Bucket::getBucketName($property["NewValue"], $projectId);
                $property["NewValue"] = $bucketDetails["Name"];
            }
            if ($fieldName == "planlevel") {
                //Plan Level
                $planlevelDetails = PlanLevel::getPlanLevelDetails($property["PreviousValue"]);
                $property["PreviousValue"] = $planlevelDetails["Name"];
                $planlevelDetails = PlanLevel::getPlanLevelDetails($property["NewValue"]);
                $property["NewValue"] = $planlevelDetails["Name"];
            }
            if ($fieldName == "tickettype") {
                //Ticket Type
                $ticketTypeDetails = TicketType::getTicketType($property["PreviousValue"]);
                $property["PreviousValue"] = $ticketTypeDetails["Name"];
                $ticketTypeDetails = TicketType::getTicketType($property["NewValue"]);
                $property["NewValue"] = $ticketTypeDetails["Name"];
            }
            $datetime = $property["CreatedOn"]->toDateTime();
            $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
            $readableDate = $datetime->format('M-d-Y H:i:s');
            $property["ActivityOn"] = $readableDate;
            if ($property["NewValue"] == "") {
                $property["NewValue"] = "-none-";
            }
            return $property;
        } catch (Exception $ex) {
            Yii::log("CommonUtility:prepareActivityProperty::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

  
    /**
      * @author Ryan Marshal
      * @param type $loggedInUser
      * @param type $recipients
      * @param type $ticketdetails
      * @param type $artifacts
      * @return type
      */
    public static function sendMail($loggedInUser,$recipients,$ticketdetails,$text_message=null)
    {
        error_log("==Followers==".print_r($ticketdetails['Followers'],1));
        $followers=$ticketdetails['Followers'];
        $recipient_list=array();
        $attachment_list=array();
        try
        {
          
            ApiClient::SetApiKey("9d55f483-0501-4005-8ada-3335f666e731");
            $qry="select UserName,Email from Collaborators where UserName = '$loggedInUser'";
            $from = Yii::$app->db->createCommand($qry)->queryOne();
            if(is_array($recipients)) // for @mentioned users
            {
                if(!empty($recipients))
                {
                    for($i=0;$i<count($recipients);$i++)
                    {
                        $qry="select UserName,Email from Collaborators where UserName = '$recipients[$i]'";
                        $data = Yii::$app->db->createCommand($qry)->queryOne();
                        array_push($recipient_list,$data['Email']);
                    }
                }
                
                // for followers
                foreach($followers as $follower)
                {
                    $followerid=$follower['FollowerId'];
                    error_log("==Follower Id==".$followerid);
                    $qry="select UserName,Email from Collaborators where Id = '$followerid'";
                    $data = Yii::$app->db->createCommand($qry)->queryOne();
                    if($data['UserName']!=$loggedInUser) //To avoidloggedin user @mentioning himself
                    {
                        array_push($recipient_list,$data['Email']);
                    }
                }
            }
            else // for assigned to and stakeholder
            {
                $qry="select UserName,Email from Collaborators where UserName = '$recipients'";
                $data = Yii::$app->db->createCommand($qry)->queryOne();
                array_push($recipient_list,$data['Email']);
            }
            //error_log("===Recipient List==".print_r($recipient_list,1));
            
//            foreach($artifacts as $artifact)
//            {
//                $filename=$artifact['FileName'].'.'.$artifact['Extension'];
//                error_log("==File Name==".$filename);
//                $filename='/usr/share/nginx/www/ProjectXService/frontend/web/files/story/'.$filename;
//                array_push($attachment_list,$filename);
//            }
            
            //$email_recepients=[$recipient_list]; //list of recepient email address
            //$recipient_list=['marshal.ryan@techo2.com'];
            $attachments=$attachment_list;//list of artifacts
            $EEemail = new Email();
             try
                {
                    $subject="Ticket Status";
                    $from=Yii::$app->params['ProjectEmail'];
                    $fromName="ProjectX";
                    $html="<h1> $text_message </h1>";
                    $text=$text_message;
                    $response = $EEemail->Send($subject, $from, $fromName, null, null, null, null, null, null, $recipient_list, array(), array(), array(), array(), array(), null, null, $html, $text,null,null,null,null,null,$attachments);		
                }
                catch (Exception $e)
                {
                    echo 'Something went wrong: ', $e->getMessage(), '\n';
                    return;
                }		
            
        } catch (Exception $ex) {
            Yii::log("CommonUtility:sendMail::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
     /**
     * @author Padmaja
     * @param type $searchString
     * @return type
     */
    public static function getAllDetailsForSearch($searchString,$page){
        try{
                $page = $page ;
                $pageLength = 10;
                if ($page == 1) {
                    $offset = $page - 1;
                    $limit = $pageLength;
                } else {
                    $offset = ($page - 1) * $pageLength;
                    $limit = $pageLength;
                }
            $collection = Yii::$app->mongodb->getCollection('TicketCollection');
            $options = array(
                "limit" =>$limit,
                "skip" => $offset
            );
            if (strpos($searchString, '#') !== false || is_numeric($searchString)!= false) {
                if(strpos($searchString, '#') !== false){
                    $searchString=str_replace("#","",$searchString); 
                    $cursor =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)1),array("CrudeDescription"=>array('$regex'=>'#'.$searchString,'$options' => 'i'),"ProjectId" => (int)1),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)1),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)1))),array(),$options);
                }else{
                    $cursor =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)1),array("CrudeDescription"=>array('$regex'=>'^'.$searchString,'$options' => 'i'),"ProjectId" => (int)1),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)1),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)1))),array(),$options);
                }
               
            }else{
               $cursor =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)1),array("CrudeDescription"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)1),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)1),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)1))),array(),$options);
            }
           // $cursor =  $collection->find(array('$or'=>array(array("Title"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)1),array("CrudeDescription"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)1),array("TicketId"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)1),array("TicketIdString"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)1))),array(),$options);
            $ticketCollectionData = iterator_to_array($cursor);
            $TicketCollFinalArray = array();
            foreach($ticketCollectionData as $extractCollection){
                $forTicketCollection['TicketId'] = $extractCollection['TicketId'];
                $forTicketCollection['Title'] = $extractCollection['Title'];
                $forTicketCollection['description'] = strip_tags($extractCollection['CrudeDescription']);
                $forTicketCollection['planlevel'] = $extractCollection['Fields']['planlevel']['value_name'];
                $forTicketCollection['reportedby'] = $extractCollection['Fields']['reportedby']['value_name'];
                $UpdatedOn = $extractCollection['UpdatedOn'];
                if(isset($UpdatedOn)){
                    $datetime = $UpdatedOn->toDateTime();
                    $readableDate =$datetime->format('Y-m-d H:i');
                    $forTicketCollection['UpdatedOn'] = $readableDate;
                }
                array_push($TicketCollFinalArray, $forTicketCollection);
            }
            $matchArray = array('Activities.CrudeCDescription'=>array('$regex'=>$searchString,'$options' => 'i'));
            $query = Yii::$app->mongodb->getCollection('TicketComments');
            $pipeline = array(
                array('$unwind' => '$Activities'),
                array('$match' => $matchArray),
                 array(
                    '$group' => array(
                        '_id' => '$TicketId',
                        "commentData" => array('$push' => '$Activities'),
                        
                     ),
                ),array('$limit' => $limit),array('$skip' => $offset)
                );
            $ticketCommentsData = $query->aggregate($pipeline);
            $TicketCommentsFinalArray = array();
            $commentsArray= array();
           
                foreach($ticketCommentsData as $extractComments){
                   $ticketCollectionModel = new TicketCollection();
                   $selectFields = ['Title', 'TicketId','Description','Fields.planlevel.value_name','Fields.reportedby.value_name','UpdatedOn'];
                   $getTicketDetails = $ticketCollectionModel->getTicketDetails($extractComments['_id'],1,$selectFields);
                   $forTicketComments['TicketId'] =  $extractComments['_id'];
                   $forTicketComments['Title'] =$getTicketDetails['Title'];
                 //  $forTicketComments['comments'] =  $extractComments['commentData'];
                    $commentsfinalArray =array();
                   foreach($extractComments['commentData'] as $eachOne){
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
                        $readableDate = $datetime->format('Y-m-d H:i');
                        $forTicketComments['UpdatedOn'] = $readableDate;
                   }
                    array_push($TicketCommentsFinalArray, $forTicketComments);
               }
            $collection = Yii::$app->mongodb->getCollection('TicketArtifacts');
            $cursor =  $collection->find(array('$or'=>array(array("Artifacts.OriginalFileName"=>array('$regex'=>$searchString,'$options' => 'i'),"ProjectId" => (int)1))),array(),$options);
            $ticketArtifactsData = iterator_to_array($cursor);
            $TicketArtifactsFinalArray = array();
            foreach($ticketArtifactsData as $extractArtifacts){
                $ticketCollectionModel = new TicketCollection();
                $selectFields = ['Title', 'TicketId','Fields.planlevel.value_name','Fields.reportedby.value_name','UpdatedOn','CrudeDescription'];
                $getTicketDetails = $ticketCollectionModel->getTicketDetails($extractArtifacts['TicketId'],1,$selectFields);
                $forTicketArtifacts['TicketId'] =$extractArtifacts['TicketId'];
                $forTicketArtifacts['Title'] =$getTicketDetails['Title'];
                $ticketArtifactsModel = new TicketArtifacts();
                $artifacts = $ticketArtifactsModel->getTicketArtifacts($extractArtifacts['TicketId'],1);
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
                    $readableDate =$datetime->format('Y-m-d H:i');
                    $forTicketArtifacts['UpdatedOn'] = $readableDate;
                 }
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
                    $readableDate =$datetime->format('Y-m-d H:i');
                    $forUsercollection['UpdatedOn'] = $readableDate; 
                  }
                 array_push($TinyUserFinalArray, $forUsercollection);
            }
            $getCollectionData=array('ticketCollection'=>$TicketCollFinalArray,'ticketComments'=>$TicketCommentsFinalArray,'ticketArtifacts'=>$TicketArtifactsFinalArray,'tinyUserData'=>$TinyUserFinalArray);
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
           $type='';
           foreach($options as $key=>$value){
             
                 foreach($value as $val){
                      if($key=='bucket'){
                         $type= 'bucket'; 
                         $showchild=1;
                      }else{
                         $type= $val['Type'];
                         $showchild=$val['ShowChild'];
                      }
             array_push($refinedFilter,array("Label"=>$val['Name'],"Id"=>$val['Id'],"Type"=>$type,"ShowChild"=>$showchild));
               }
                
           }
           return $refinedFilter;
        }catch (Exception $ex) {
            Yii::log("CommonUtility:prepareFilterOption::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
 
}

}

?>