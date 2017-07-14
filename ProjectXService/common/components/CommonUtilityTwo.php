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
            $shortBucketDesc= CommonUtility::refineActivityDataTimeDesc($bucketDetails["Description"],50);
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
                
            }else{
                $prepareBucketArray['AllTasks'] =TicketCollection::getAllTicketsCount($projectId,$bucketDetails['Id'],'Fields.bucket.value','Fields.state.value',$taskFlag='All');
                $prepareBucketArray['ClosedTasks'] =TicketCollection::getAllTicketsCount($projectId,$bucketDetails['Id'],'Fields.bucket.value','Fields.state.value',$taskFlag='Closed');
                $prepareBucketArray['OpenTasks'] =TicketCollection::getAllTicketsCount($projectId,$bucketDetails['Id'],'Fields.bucket.value','Fields.state.value',$taskFlag='Open');
                $prepareBucketArray['TotalHours'] =TicketCollection::getTotalWorkHoursForBucket($projectId,$bucketDetails['Id'],'Fields.bucket.value');
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
}
