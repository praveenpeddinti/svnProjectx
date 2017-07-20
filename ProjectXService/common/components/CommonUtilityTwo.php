<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace common\components;
use common\models\mongo\{TicketCollection,TinyUserCollection,TicketArtifacts};
use common\models\mysql\{Priority,Projects,WorkFlowFields,Bucket,TicketType,StoryFields,StoryCustomFields,PlanLevel,MapListCustomStoryFields};
use Yii;

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
        } catch (Exception $ex) {
            Yii::log("CommonUtility:convert_date_zone::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
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
        } catch (Exception $ex) {
            Yii::log("CommonUtilityTwo:prepareBucketDashboardDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
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
        } catch (Exception $ex) {
            Yii::log("CommonUtilityTwo:truncateHtml::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
}
