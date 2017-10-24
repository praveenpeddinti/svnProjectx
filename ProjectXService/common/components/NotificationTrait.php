<?php

namespace common\components;

use common\models\mongo\{
    TicketCollection,
    TinyUserCollection,
    NotificationCollection,
    TicketComments
};
use common\components\{
    CommonUtility,
    EventTrait
};
use common\models\mysql\{
    WorkFlowFields,
    StoryFields,
    Priority,
    PlanLevel,
    TicketType,
    Bucket,
    Collaborators,
    TaskTypes,
    Filters,
    Projects,
    Settings
};
use yii;
use yii\base\ErrorException;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

trait NotificationTrait {

    public function sampleMethod() {
        echo "sampel emthod";
    }

    /**
     * @author Ryan
     * @Description Gets the field change value name for notification purpose
     * @params  $notifyType,$Value
     * @return type string
     */
    public static function getFieldChangeValue($notifyType, $Value,$projectId='') {
        try {
            if ($notifyType == 'priority') {
                $priorityObj = new Priority();
                $priorityDetails = $priorityObj->getPriorityDetails($Value);
                $priorityValue = $priorityDetails['Name'];
                return $priorityValue;
            }
            if ($notifyType == 'bucket') {
                error_log("TYpe___".$notifyType."Value___".$Value);
                $bucketDetails = Bucket::getBucketName($Value, $projectId);
                $bucketValue = $bucketDetails['Name'];
                return $bucketValue;
            }
            if ($notifyType == 'workflow') {
                $workflowDetails = WorkFlowFields::getWorkFlowDetails($Value);
                $workflowValue = $workflowDetails['Name'];
                return $workflowValue;
            }
            if ($notifyType == 'tickettype') {
                $tickettypeDetails = TicketType::getTicketType($Value);
                $tickettypeValue = $tickettypeDetails['Name'];
                return $tickettypeValue;
            }
        } catch (\Throwable $ex) {
            Yii::error("NotificationTrait:getFieldChangeValue::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

    /**
     * @author Moin Hussain
     * @param type $ticketId
     * @param type $projectId
     * @param type $actionfieldName
     * @param type $newValue
     * @param type $activityUserId
     * @Description Saves the activity happend in a Ticket under a Project.
     */
    public function saveActivity($ticketId, $projectId, $actionfieldName, $newValue, $activityUserId, $slug = "", $timezone="Asia/Kolkata",$reportData=array()) {
        try {
            $oldValue = "";
            $action = "";
            $returnValue = "noupdate";
            if (empty($slug))
                $slug = new \MongoDB\BSON\ObjectID();
            $ticketDetails = TicketCollection::getTicketDetails($ticketId, $projectId);
            if ($actionfieldName == "Title" || $actionfieldName == "Description" || $actionfieldName == "TotalTimeLog") { //added actionFieldName for TotalTimeLog By Ryan
                $oldValue = $ticketDetails[$actionfieldName];
            } else if ($actionfieldName == 'Followed' || $actionfieldName == 'Unfollowed' || $actionfieldName == 'Related' || $actionfieldName == 'ChildTask' || $actionfieldName == 'Unrelated') {
                $oldValue = "";
                switch ($actionfieldName) {
                    case 'Followed':$action = "added to";
                        break;
                    case 'Unfollowed':$action = "removed from";
                        break;
                    case 'Related':$action = "related";
                        break;
                    case 'ChildTask':$action = "created";
                        break;
                    case 'Unrelated':$action = "unrelated";
                        break;
                }
            } else {
                $oldValue = $ticketDetails["Fields"][$actionfieldName]["value"];
            }
            if ($action != "" || trim($oldValue) != trim($newValue)) {
                if ($action == "") {
                    if ($oldValue == "") {
                        $action = "set to";
                    } else {
                        $action = "changed from";
                    }
                }

            $db = TicketComments::getCollection();
            $currentDate = new \MongoDB\BSON\UTCDateTime(time() * 1000);
            $record = $db->findOne(array("ProjectId" => (int) $projectId, "TicketId" => (int) $ticketId));
            if(empty($reportData) && ($record["RecentActivityUser"] != $activityUserId || $record["Activity"] == "Comment" || $record["Activity"] == "Report" ||  $record["Activity"] == "PoppedFromChild")) {
                $commentDataArray = array(
                    "Slug" => $slug,
                    "CDescription" => "",
                    "CrudeCDescription" => "",
                    "ActivityOn" => $currentDate,
                    "ActivityBy" => (int) $activityUserId,
                    "Status" => (int) 1,
                    "PropertyChanges" => array(array("Slug" => $slug, "ActionFieldName" => $actionfieldName, "Action" => $action, "PreviousValue" => $oldValue, "NewValue" => $newValue, "CreatedOn" => $currentDate)),
                    "ParentIndex" => "",
                    "PoppedFromChild" => ""
                );
                $v = $db->findAndModify(array("ProjectId" => (int) $projectId, "TicketId" => (int) $ticketId), array('$addToSet' => array('Activities' => $commentDataArray)), array('new' => 1, "upsert" => 1));
                $v = $db->update(array("ProjectId" => (int) $projectId, "TicketId" => (int) $ticketId), array("RecentActivitySlug" => $slug, "RecentActivityUser" => (int) $activityUserId, "Activity" => "PropertyChange"));
                CommonUtility::prepareActivity($commentDataArray, $projectId, $timezone);
                $returnValue = array("referenceKey" => -1, "data" => $commentDataArray);
            } else if(empty($reportData) && $record["Activity"] != "Report"){ 
                $recentSlug = $record["RecentActivitySlug"];
                $property = array("Slug" => $slug, "ActionFieldName" => $actionfieldName, "Action" => $action, "PreviousValue" => $oldValue, "NewValue" => $newValue, "CreatedOn" => $currentDate);

                $db = TicketComments::getCollection();
                $currentDate = new \MongoDB\BSON\UTCDateTime(time() * 1000);
                $record = $db->findOne(array("ProjectId" => (int) $projectId, "TicketId" => (int) $ticketId));
               
                    $v = $db->findAndModify(array("ProjectId" => (int) $projectId, "TicketId" => (int) $ticketId, "Activities.Slug" => $recentSlug), array('$addToSet' => array('Activities.$.PropertyChanges' => $property)), array('new' => 1, "upsert" => 1));

                    $activitiesCount = count($v["Activities"]);
                    if ($activitiesCount > 0) {
                        $activitiesCount = $activitiesCount - 1;
                    }
                CommonUtility::prepareActivityProperty($property, $projectId, $timezone);
                $returnValue = array("referenceKey" => $activitiesCount, "data" => $property);
            }else if(!empty($reportData)){
               $commentDataArray = array(
                    "Slug" => $slug,
                    "CDescription" => $reportData['CDescription'],
                    "CrudeCDescription" => $reportData['CrudeDescription'],
                    "ActivityOn" => $currentDate,
                    "ActivityBy" => (int) $activityUserId,
                    "Status" => (int) 1,
                    "PropertyChanges" => array(array("Slug" => $slug, "ActionFieldName" => $actionfieldName, "Action" => $action, "PreviousValue" => $oldValue, "NewValue" => $newValue, "CreatedOn" => $currentDate)),
                    "ParentIndex" => "",
                    "PoppedFromChild" => ""
                );
                $v = $db->findAndModify(array("ProjectId" => (int) $projectId, "TicketId" => (int) $ticketId), array('$addToSet' => array('Activities' => $commentDataArray)), array('new' => 1, "upsert" => 1));
                $v = $db->update(array("ProjectId" => (int) $projectId, "TicketId" => (int) $ticketId), array("RecentActivitySlug" => $slug, "RecentActivityUser" => (int) $activityUserId, "Activity" => "Report"));
                CommonUtility::prepareActivity($commentDataArray, $projectId, $timezone); 
                $returnValue = array("referenceKey" => -1, "data" => $commentDataArray);
            }
            if ($ticketDetails["IsChild"] == 1 && $actionfieldName == "workflow") {
                $description="";
                $cdescription="";
                if(!empty($reportData)){
                    $description =$reportData['CrudeDescription'];
                    $cdescription =$reportData['CDescription'];
                }
                $commentDataArray = array(
                    "Slug" => $slug,
                    "CDescription" => $cdescription,
                    "CrudeCDescription" => $description,
                    "ActivityOn" => $currentDate,
                    "ActivityBy" => (int) $activityUserId,
                    "Status" => (int) 1,
                    "PropertyChanges" => array(array("Slug" => $slug, "ActionFieldName" => $actionfieldName, "Action" => $action, "PreviousValue" => $oldValue, "NewValue" => $newValue, "CreatedOn" => $currentDate)),
                    "ParentIndex" => "",
                    "PoppedFromChild" => (int) $ticketId
                );
                $parentStoryId = $ticketDetails["ParentStoryId"];

                    $v = $db->findAndModify(array("ProjectId" => (int) $projectId, "TicketId" => (int) $parentStoryId), array('$addToSet' => array('Activities' => $commentDataArray)), array('new' => 1, "upsert" => 1));
                    $v = $db->update(array("ProjectId" => (int) $projectId, "TicketId" => (int) $parentStoryId), array("RecentActivitySlug" => $slug, "RecentActivityUser" => (int) $activityUserId, "Activity" => "PoppedFromChild"));
                }
            }
            return $returnValue;
        } catch (\Throwable $ex) {
            Yii::error("NotificationTrait:saveActivity::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }

    }

    /**
     * @author Ryan Marshal
     * @param type $property
     * @return type 
     * @Description  Used for saving the notifications in submit Description and comments  
     */
    public static function saveNotificationsToMentionOnly($ticket_data, $userslist, $notifyType = null, $slug = null) {
        try {
            if ($slug == null) {
                $slug = new \MongoDB\BSON\ObjectID();
            }
            $from = $ticket_data->userInfo->username;
            $loggedinUser = $ticket_data->userInfo->Id;
            $ticketId = (int) $ticket_data->ticketId;
            $projectId = (int) $ticket_data->projectId;
            $collaboratorIds = array();
            $currentDate = new \MongoDB\BSON\UTCDateTime(time() * 1000);
            $ticket_data = TicketCollection::getTicketDetails($ticketId, $projectId);
            if (!empty($userslist)) {
                foreach ($userslist as $user) {
                    $user = Collaborators::getCollaboratorId($user);
                    array_push($collaboratorIds, array("CollaboratorId" => (int) $user['Id'], "IsRead" => 0, 'SystemNotification' => 0, 'EmailNotification' => 0, 'PushNotification' => 0));
                }
                $tic = new NotificationCollection();
                $tic->TicketId = $ticketId;
                $tic->ProjectId = $projectId;
                $tic->NotifiedCollaborators = $collaboratorIds;
                $tic->ActivityFrom = (int) $loggedinUser;
                $tic->NotificationDate = $currentDate;
                $tic->Notification_Type = 'mention';
                $tic->ActivityOn = 'description'; //$user['Id'];
                $tic->CommentSlug = $slug;
                $result = $tic->save();
                if ($result) {
                    $notificationId = $tic->_id;
                    self::sendEmailNotification(array($notificationId), $projectId);
                }
            }
        } catch (\Throwable $ex) {
            Yii::error("NotificationTrait:saveNotificationsToMentionOnly::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

    /**
     * @author Ryan
     * @param type $commentData
     * @param type $notify_type //comment,reply
     * @param type $slug
     * @Description Used to Save Notification for Comments
     */
    public static function saveNotificationsForComment($commentData, $userslist, $notify_type, $slug) {
        try {


            $commentOwner = $commentData->Comment->OriginalCommentorId;
            $loggedinUser = $commentData->userInfo->Id;
            $ticketId = (int) $commentData->ticketId;
            $projectId = (int) $commentData->projectId;
            $description = CommonUtility::refineDescriptionForEmail($commentData->Comment->CrudeCDescription);
            $mentionUserIdlist = array();
            $collaboratorIds = array();
            $commentDescripition = $description;
            $comment_desc = str_replace("&nbsp;", ' ', $commentDescripition);
            $data = TicketCollection::getTicketDetails($ticketId, $projectId);
            $followers = $data['Followers'];
            $followers = CommonUtility::filterFollowers($followers);
            $currentDate = new \MongoDB\BSON\UTCDateTime(time() * 1000);
            $oldValue = "";
            $commentOwnerName = Collaborators::getCollaboratorById($commentOwner);

            if ($notify_type == "edit") {
                $OldNotfication = NotificationCollection::getNotificationDetail($slug, $ticketId, $projectId);
                $oldValue = $OldNotfication["NewValue"];
                $displayAction = "edited";
                $actionType = "edit";
            } else if ($notify_type == "comment") {
                $displayAction = "commented";
                $actionType = "comment";
            } else if ($notify_type == "delete") {
                $displayAction = "deleted";
                $actionType = "delete";
            } else if($notify_type == "repliedOn")
                {
                 $displayAction = "replied";
                 $actionType = "repliedOn";
                }else{
                $displayAction = "replied";
                $actionType = "reply";
            }

            //For Reply....added by Ryan
            if (!empty($userslist)) {
                foreach ($userslist as $user) {
                    $user = Collaborators::getCollaboratorId($user);
                    $CollaboratorNotifications = Settings::getNotificationSettingsStatus("mention", $user['Id']);
                    if (count($CollaboratorNotifications) > 0) {
                        $system_notification = $CollaboratorNotifications[0]['SystemNotification'];
                        $email_notification = $CollaboratorNotifications[0]['EmailNotification'];
                        $push_notification = $CollaboratorNotifications[0]['PushNotification'];
                        array_push($collaboratorIds, array('CollaboratorId' => (int) $user['Id'], 'IsRead' => 0, 'SystemNotification' => (int) $system_notification, 'EmailNotification' => (int) $email_notification, 'PushNotification' => (int) $push_notification));
                    } else {
                        array_push($collaboratorIds, array('CollaboratorId' => (int) $user['Id'], 'IsRead' => 0, 'SystemNotification' => 0, 'EmailNotification' => 0, 'PushNotification' => 0));
                    }
                    array_push($mentionUserIdlist, $user['Id']);
                }
                $tic = new NotificationCollection();
                $tic->TicketId = $ticketId;
                $tic->ProjectId = $projectId;
                $tic->NotifiedCollaborators = $collaboratorIds;
                $tic->ActivityFrom = (int) $loggedinUser;
                $tic->NotificationDate = $currentDate;
                $tic->OldValue = $oldValue; //added for consistency
                $tic->NewValue = $comment_desc; //added for consistency
                $tic->Notification_Type = 'mention';
                $tic->CommentOwner=$commentOwnerName['UserName'];
                //eg: moin.hussain mentioned you
                $tic->ActivityOn = "comment";
                $tic->CommentSlug = $slug;
                $result = $tic->save();
                if ($result) {
                    $notificationId = $tic->_id;
                    self::sendEmailNotification(array($notificationId), $projectId);
                }
            }
            $collaboratorIds = array();
            $notificationIdsArray = array();
            $CollaboratorNotifications = array();
            if (!empty($followers)) {
                foreach ($followers as $follower) {
                    if ($follower['FollowerId'] != $loggedinUser && $follower['FollowerId'] != $commentOwner && !in_array($follower['FollowerId'], $mentionUserIdlist)) {
                        $CollaboratorNotifications = Settings::getNotificationSettingsStatus("comment", $follower['FollowerId']);
                        if (count($CollaboratorNotifications) > 0) {
                            $system_notification = $CollaboratorNotifications[0]['SystemNotification'];
                            $email_notification = $CollaboratorNotifications[0]['EmailNotification'];
                            $push_notification = $CollaboratorNotifications[0]['PushNotification'];
                            array_push($collaboratorIds, array('CollaboratorId' => (int) $follower['FollowerId'], 'IsRead' => 0, 'SystemNotification' => (int) $system_notification, 'EmailNotification' => (int) $email_notification, 'PushNotification' => (int) $push_notification));
                        } else {
                            array_push($collaboratorIds, array('CollaboratorId' => (int) $follower['FollowerId'], 'IsRead' => 0, 'SystemNotification' => 0, 'EmailNotification' => 0, 'PushNotification' => 0));
                        }
                    }
                }
                $tic = new NotificationCollection();
                $tic->NotifiedCollaborators = $collaboratorIds;
                $tic->TicketId = $ticketId;
                $tic->ProjectId = $projectId;
                $tic->ActivityFrom = (int) $loggedinUser;
                $tic->NotificationDate = $currentDate;
                $tic->OldValue = $oldValue; //added for consistency
                $tic->NewValue = strip_tags($comment_desc); //added for consistency
                $tic->CommentSlug = $slug;
                $tic->Notification_Type = $notify_type;
                $tic->CommentOwner=$commentOwnerName['UserName'];
                $tic->ActivityOn = "comment"; //added for consistency
                $result = $tic->save();
                if ($result) {
                    $notificationId = $tic->_id;
                    array_push($notificationIdsArray, $notificationId);
                }
                // seprate record for comment owner
                $collaboratorIds = array();
                if ($commentOwner != '' && $commentOwner != $loggedinUser) {
                    $CollaboratorNotifications = Settings::getNotificationSettingsStatus("comment", $commentOwner);
                    if (count($CollaboratorNotifications) > 0) {
                        $system_notification = $CollaboratorNotifications[0]['SystemNotification'];
                        $email_notification = $CollaboratorNotifications[0]['EmailNotification'];
                        $push_notification = $CollaboratorNotifications[0]['PushNotification'];
                        array_push($collaboratorIds, array('CollaboratorId' => (int) $commentOwner, 'IsRead' => 0, 'SystemNotification' => (int) $system_notification, 'EmailNotification' => (int) $email_notification, 'PushNotification' => (int) $push_notification));
                    } else {
                        array_push($collaboratorIds, array('CollaboratorId' => (int) $commentOwner, 'IsRead' => 0, 'SystemNotification' => 0, 'EmailNotification' => 0, 'PushNotification' => 0));
                    }
                    $tic = new NotificationCollection();
                    $tic->NotifiedCollaborators = $collaboratorIds;
                    $tic->TicketId = $ticketId;
                    $tic->ProjectId = $projectId;
                    $tic->ActivityFrom = (int) $loggedinUser;
                    $tic->NotificationDate = $currentDate;
                    $tic->OldValue = $oldValue; //added for consistency
                    $tic->NewValue = $comment_desc; //added for consistency
                    $tic->CommentSlug = $slug;
                    $tic->CommentOwner=$commentOwnerName['UserName'];
                    //eg : moin.hussain commented on or replied on the ticket.
                    $tic->Notification_Type = "reply";
                    $tic->ActivityOn = "comment"; //added for consistency

                    $result = $tic->save();
                    if ($result) {
                        $notificationId = $tic->_id;
                        array_push($notificationIdsArray, $notificationId);
                    }
                }
            }
            EventTrait::saveEvent($projectId, "Ticket", $ticketId, $displayAction, $actionType, $loggedinUser, [array("ActionOn" => "comment", "OldValue" => $tic->OldValue, "NewValue" => $tic->NewValue)], array("Slug" => $slug, "CommentId" => $tic->_id));

            self::sendEmailNotification($notificationIdsArray, $projectId);
        } catch (\Throwable $ex) {
            Yii::error("NotificationTrait:saveNotificationsForComment::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

    /**
     * @author Ryan Marshal
     * @param type $notification_data
     * @return type
     * @Description  Used for saving the notifications for assignedTo,Add/Remove Followers,stakeholder and left panel property changes  
     */
    public static function saveNotifications($notification_data, $notifyType, $activityOn, $fieldType = "", $slug = '', $bulkUpdate = '', $taskId = 0) {
        try {
            error_log("saveNotifications---" . $notifyType . "---" . $activityOn . "---" . $fieldType);
            $oldValue = '';
            $newValue = '';
            $oldCollaborator = '';
            $newCollaborator = '';
            $displayAction = "";
            $actionType = "";

            //For Story Detail Page Use Case.....
            $ticketId = isset($notification_data->ticketId) ? (int) $notification_data->ticketId : (int) $notification_data->data->ticketId;
            $projectId = (int) $notification_data->projectId;
            $loggedInUser = $notification_data->userInfo->Id;
            $notify_type = $notifyType; //this will be changed to ActivityOn in the below code....
            $currentDate = new \MongoDB\BSON\UTCDateTime(time() * 1000);
            $ticketDetails = TicketCollection::getTicketDetails($ticketId, $projectId);

            $followers = $ticketDetails['Followers'];
            $bucket = $ticketDetails["Fields"]["bucket"]["value"];
            $followers = CommonUtility::filterFollowers($followers);

            if ($notifyType == "Title" || $notifyType == "Description" || $notifyType == "TotalTimeLog") {
                if ($notifyType == "Description") {
                    $oldValue = $ticketDetails["CrudeDescription"];
                } else {
                    $oldValue = $ticketDetails[$notifyType];
                }


                if ($oldValue == $activityOn) {
                    return;
                }
                if ($oldValue != '') {
                    $notification_Type = 'changed';
                    $newValue = $activityOn;
                    $activityOn = 'changed';
                    $displayAction = "changed";
                    $actionType = "change";
                } else {
                    $notification_Type = 'set';
                    $newValue = $activityOn; //added for fixing time log issue
                    $activityOn = 'set';
                    $displayAction = "changed";
                    $actionType = "change";
                }
            } else if (isset($ticketDetails["Fields"][$notifyType])) {
                $oldValue = $ticketDetails["Fields"][$notifyType]["value"];

            }

            if ($fieldType == 1 || $fieldType == 4 || $fieldType == 5 || $fieldType == 8) {
                //for due date,dod and estimated points
                $oldValue = $oldValue;
                $newValue = $activityOn;
                if ($oldValue == $newValue) {
                    return;
                }
                if ($oldValue != '') { //if changed
                    $activityOn = 'changed';
                    $displayAction = "changed";
                    $actionType = "change";
                } else { //if set new value
                    $activityOn = 'set';
                    $oldValue = '';
                    $displayAction = "set";
                    $actionType = "set";
                }
                if ($fieldType == 4) {
                }
            } else if ($fieldType == 6) {
                                 

                $oldCollaborator = $oldValue;
                $newCollaborator = $activityOn; //this is a field value....

                if ($oldCollaborator != '') { //if changed
                    $notification_Type = 'changed';
                    $displayAction = "changed";
                    $actionType = "change";
                } else { //if set new value
                    $notification_Type = 'set';
                    $oldValue = '';
                    $displayAction = "set";
                    $actionType = "set";
                }
            } else if ($fieldType == "FollowObj") {
                if ($loggedInUser != $activityOn) {
                    error_log("follow object firsrt case((((((((((((***");
                    $notification_Type = ($notifyType == 'add') ? 'added' : 'removed';
                    $actionType = $notifyType;
                    $displayAction = $notification_Type;
                }
            } else if (($notifyType != "Description" && $notifyType != "Title" && $notifyType != "TotalTimeLog") && ($fieldType != "Description" && $fieldType != "Title" && $fieldType != "TotalTimeLog") && ($activityOn !== "ChildTask" && $activityOn != "TicketRelation")) { //This is for left hand property changes
                error_log("@@@@@@@----------------+++++++++++");
                $oldFieldId = $oldValue;
                $newFieldId = $activityOn;
                $newValue = self::getFieldChangeValue($notifyType, $newFieldId,$projectId);

                if ($oldValue == $activityOn) {
                    return;
                }
                if ($oldValue != '') { //if changed
                    $activityOn = 'changed';
                    $oldValue = self::getFieldChangeValue($notifyType, $oldFieldId,$projectId);
                    $displayAction = "changed";
                    $actionType = "change";
                } else { //if set new value
                    $activityOn = 'set';
                    $oldValue = '';
                    $displayAction = "set";
                    $actionType = "set";
                }
            }
            /* notification for all followers and the stakeholders */
            $notificationIds = array();
            $collaboratorUser = array();
            $CollaboratorNotifications = array();
            $system = '';
            //@Lakshmi
            //making collaboratorsUser array to save the followers ids in array
            foreach ($followers as $follower) {
                if ($follower['FollowerId'] == $loggedInUser) {
                    continue;
                }
                if ($activityOn !== "ChildTask" && $activityOn !== "TicketRelation" && $fieldType !== "FollowObj") {
                    $CollaboratorNotifications = Settings::getNotificationSettingsStatus($notifyType, $follower['FollowerId']);
                }
                else if ($activityOn == "ChildTask" || $activityOn == "TicketRelation") {
                    $CollaboratorNotifications = Settings::getNotificationSettingsStatus($activityOn, $follower['FollowerId']);
                } else if ($fieldType == "FollowObj") {
                    $CollaboratorNotifications = Settings::getNotificationSettingsStatus($fieldType, $follower['FollowerId']);
                }
                if (count($CollaboratorNotifications) > 0) {
                    $system_notification = $CollaboratorNotifications[0]['SystemNotification'];
                    $email_notification = $CollaboratorNotifications[0]['EmailNotification'];
                    $push_notification = $CollaboratorNotifications[0]['PushNotification'];
                    array_push($collaboratorUser, array('CollaboratorId' => $follower['FollowerId'], 'IsRead' => 0, 'SystemNotification' => (int) $system_notification, 'EmailNotification' => (int) $email_notification, 'PushNotification' => (int) $push_notification));
                } else {
                    array_push($collaboratorUser, array('CollaboratorId' => $follower['FollowerId'], 'IsRead' => 0, 'SystemNotification' => 0, 'EmailNotification' => 0, 'PushNotification' => 0));
                }
            }
            $tic = new NotificationCollection();
            $tic->CommentSlug = $slug;
            $tic->NotifiedCollaborators = $collaboratorUser;
            $tic->TargetTicketId = $taskId; //added for child task and relate task
            $tic->TicketId = $ticketId;
            $tic->ProjectId = $projectId;
            $tic->ActivityOn = $notify_type; // new use case "ActivityOn" will be Field Name
            $tic->ActivityFrom = (int) $loggedInUser;
            $tic->NotificationDate = $currentDate;
            if ($notifyType == "Description" || $notifyType == "Title") {
                $displayAction = "changed";
                $actionType = "change";
                $tic->Notification_Type = $notification_Type;
                $tic->ActivityOn = $notifyType;
                $oldValue = CommonUtility::refineDescriptionForEmail($oldValue);
                $newValue = CommonUtility::refineDescriptionForEmail($newValue);
                $tic->OldValue = $oldValue;
                $tic->NewValue = $newValue;
                $result = $tic->save();
                if ($result) {
                    $notificationId = $tic->_id;
                    array_push($notificationIds, $notificationId);
                }
            } else if ($fieldType == "FollowObj") {
                error_log("folloererer----------------------------");
                $tic->ActivityOn = $fieldType;
                if ($loggedInUser == $activityOn) {
                    $notification_Type = ($notifyType == 'add') ? 'followed' : 'unfollowed';
                } else {
                    $notification_Type = ($notifyType == 'add') ? 'added' : 'removed';
                }
                $displayAction = $notification_Type;
                $actionType = $notifyType;
                $tic->Notification_Type = $notification_Type;
                $tic->OldValue = "";
                $tic->NewValue = $activityOn;
                $result = $tic->save();
                if ($result) {
                    $notificationId = $tic->_id;
                    array_push($notificationIds, $notificationId);
                }
                  EventTrait::saveEvent($projectId, "Ticket", $ticketId, $displayAction, $actionType, $loggedInUser, [array("ActionOn" =>($displayAction=='added')?'follow':'unfollow', "OldValue" => (int)$oldValue, "NewValue" => (int)$activityOn)], array("BucketId" => (int) $bucket));
            } else if ($fieldType == 6) {

                $tic->Notification_Type = $notification_Type;
                $tic->OldValue = $oldCollaborator;
                $tic->NewValue = $newCollaborator;
                $result = $tic->save();
                if ($result) {
                    $notificationId = $tic->_id;
                    array_push($notificationIds, $notificationId);
                }
            } else if ($activityOn == "ChildTask" || $activityOn == "TicketRelation") {
                $tic->ActivityOn = $activityOn;
                $tic->ActivityFrom = (int) $loggedInUser;
                $tic->NotificationDate = $currentDate;
                $tic->Notification_Type = $notifyType;
                $tic->OldValue = '';
                $tic->NewValue = (int) $taskId;
                $result = $tic->save();
                if ($result) {
                    $notificationId = $tic->_id;
                    array_push($notificationIds, $notificationId);
                }
            } else {
                $tic->Notification_Type = $activityOn;
                $oldValue = ($fieldType == 4) ? $oldValue : (string) $oldValue;
                $tic->OldValue = $oldValue;
                $tic->NewValue = $newValue;
                error_log($oldValue."____________IIIIIIIIIIIIIIII++6+++++++++++++++++++++++".$newValue);
                $result = $tic->save(); //here not sending emails for left hand side propert change excpet Assinged to , stake holder
                if ($result) {
                    $notificationId = $tic->_id;
                    array_push($notificationIds, $notificationId);
                }
            }
            if ($bulkUpdate == 1) {
                return $notificationIds;
            } else {
                self::sendEmailNotification($notificationIds, $projectId);
            }
        } catch (\Throwable $ex) {
            Yii::error("NotificationTrait:saveNotifications::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    /**
     * 
     * @param type $user
     * @param type $projectId
     * @param type $offset
     * @param type $limit
     * @param type $viewAll
     * @param type $asActivity
     * @param type $timeZone
     * @return array
     * @throws ErrorException
     * @Description Returns the notifications data for displaying with Page nation (infinet scroll)
     */
    public static function getNotifications($user, $projectId = 0, $offset = 0, $limit = 5, $viewAll = 0, $asActivity = false, $timeZone = "Asia/Kolkata") {
        error_log("==in get notifications---" . $user . "---" . $projectId . "---" . $offset . "---" . $limit);
        $msg = '';
        $message = array();
        $result_msg = array();
        $status = '';
        try {
            $projectObj = new Projects();
            $notifications = NotificationCollection::getNotifications($user, $offset, $limit, $viewAll, $asActivity);
            //constucting the notifications for the user
            foreach ($notifications as $notification) {
                $projectId = $notification['ProjectId'];
                if ($viewAll == 0) {
                    $notification['Status'] = 0;
                    $notification['NotifiedUser'] = $user;
                } else {
                    foreach ($notification['NotifiedCollaborators'] as $key => &$value) {
                        if ($value['CollaboratorId'] == (int) $user) {
                            $notification['Status'] = $value['IsRead'];
                            $notification['NotifiedUser'] = $value['CollaboratorId'];
                            break;
                        }
                    }
                }
                $datetime = $notification['NotificationDate']->toDateTime();
                $datetime->setTimezone(new \DateTimeZone($timeZone));
                $Date = $datetime->format('Y-m-d H:i:s');
                $dateOnly = $datetime->format('Y-m-d');
                $selectfields = ['Title', 'TicketId', 'Fields.planlevel'];
                $ticket_data = TicketCollection::getTicketDetails($notification['TicketId'], $projectId, $selectfields);
                $ticket_msg = 'to' . ' ' . '#' . $notification['TicketId'] . ' ' . $ticket_data['Title'];
                $planLevel = $ticket_data["Fields"]["planlevel"]["value"];
                $planType = strtolower($ticket_data["Fields"]["planlevel"]["value_name"]);
                $from_user = TinyUserCollection::getMiniUserDetails($notification['ActivityFrom']);

                $projectDetails = $projectObj->getProjectMiniDetails($notification["ProjectId"]);

                /*                 * ************* Left Panel Field Values newly assigned ******************** */
                $activityOn = $notification['ActivityOn'];
                $activityOnFieldType = "";
                $storyField = "";
                if ($activityOn != "Title" && $activityOn != "Description" && $activityOn != "TotalTimeLog") {
                    $storyField = StoryFields::getFieldDetails($activityOn, "Field_Name");
                    ;
                    $activityOnFieldType = $storyField["Type"];
                }

                if ($activityOnFieldType == 6) { //newly assigned 
                    $preposition='to';
                     $to='';
                    if ($notification['NotifiedUser'] == $notification['NewValue']) {
                         //for logged in user
                        //Eg : moin.hussain assigned you to ticket #33 
                        $to = "you";
                    } else {
                        $action_user = Collaborators::getCollaboratorById($notification['NewValue']);
                        //Eg : moin.hussain assigned sateesh.mandru to Ticket #33
                        $to = $action_user['UserName'];
                   }
                   if($activityOn=="assignedto") 
                    $message = array('onlyDate' => $dateOnly, 'Slug' => $notification['CommentSlug'], 'Project' => $projectDetails, 'IsSeen' => $notification['Status'], 'from' => $from_user['UserName'], 'object' => "user", 'type' => Yii::t('app','assigned'), 'to' => $to, 'Title' => $ticket_data['Title'], 'TicketId' => $notification['TicketId'], 'date' => $Date, 'id' => $notification['_id'], 'PlanLevel' => $planLevel, 'Profile' => $from_user['ProfilePicture'], "Preposition" => $preposition);
                   else
                    $message = array('onlyDate' => $dateOnly, 'Slug' => $notification['CommentSlug'], 'Project' => $projectDetails, 'IsSeen' => $notification['Status'], 'from' => $from_user['UserName'], 'object' => "user", 'type' => Yii::t('app','assigned'), 'to' => $to, 'Title' => $ticket_data['Title'], 'TicketId' => $notification['TicketId'], 'date' => $Date, 'id' => $notification['_id'], 'PlanLevel' => $planLevel, 'Profile' => $from_user['ProfilePicture'], "OtherMessage" => Yii::t('app',$activityOn), "Preposition" => $preposition);
                   array_push($result_msg, $message);
                } else if ($notification['ActivityOn'] == 'Description' || $notification['ActivityOn'] == 'Title') {
                    $notification['OldValue'] = CommonUtility::refineActivityData($notification['OldValue'], 50);
                    $notification['NewValue'] = CommonUtility::refineActivityData($notification['NewValue'], 50);
                    $notification_type=$notification['Notification_Type'];
                     $notification['OldValue']= '"'.$notification['OldValue'].'"';
                         $notification['NewValue']='"'.$notification['NewValue'].'"';
                $message = array('onlyDate' => $dateOnly, 'Slug' => $notification['CommentSlug'], 'Project' => $projectDetails, 'IsSeen' => $notification['Status'], 'from' => $from_user['UserName'], 'object' => "description", 'type' =>Yii::t('app', $notification_type), 'id' => $notification['_id'], 'ActivityOn' => $notification['ActivityOn'], 'Title' => $ticket_data['Title'], 'TicketId' => $notification['TicketId'], 'date' => $Date, 'PlanLevel' => $planLevel, 'Profile' => $from_user['ProfilePicture'], 'status' => $notification['Notification_Type'], 'OldValue' => $notification['OldValue'], "NewValue" => $notification['NewValue']);
                    array_push($result_msg, $message);
                } else if ($notification['ActivityOn'] == 'ChildTask' || $notification['ActivityOn'] == 'TicketRelation') {
                    $notification_type=$notification['Notification_Type'];
                    $targetTicketData = TicketCollection::getTicketDetails($notification['TargetTicketId'], $projectId);
                    $targetPlanLevel = $targetTicketData["Fields"]["planlevel"]["value"];
                    $message = array('onlyDate' => $dateOnly, 'Slug' => $notification['CommentSlug'], 'Project' => $projectDetails, 'IsSeen' => $notification['Status'], 'from' => $from_user['UserName'], 'object' => "task", 'type' => Yii::t('app', $notification_type), 'id' => $notification['_id'], 'Title' => $ticket_data['Title'], 'TicketId' => $ticket_data['TicketId'], 'date' => $Date, 'PlanLevel' => $planLevel, 'TargetPlanLevel' => $targetPlanLevel, 'Profile' => $from_user['ProfilePicture'], 'status' => $notification['Notification_Type'], 'TargetTicketId' => $targetTicketData['TicketId'], 'TargetTicketTitle' => $targetTicketData['Title']);
                    array_push($result_msg, $message);
                } else if ($notification['ActivityOn'] == 'TotalTimeLog') {
                    $notification['OldValue'] = number_format((float) $notification['OldValue'], 1, '.', '');
                    $notification['NewValue'] = number_format((float) $notification['NewValue'], 1, '.', '');
                    $message = array('onlyDate' => $dateOnly, 'Slug' => $notification['CommentSlug'], 'Project' => $projectDetails, 'IsSeen' => $notification['Status'], 'from' => $from_user['UserName'], 'object' => "work log", 'type' =>Yii::t('app', $activityOn), 'id' => $notification['_id'], 'ActivityOn' => "Total Time Log", 'Title' => $ticket_data['Title'], 'TicketId' => $notification['TicketId'], 'date' => $Date, 'PlanLevel' => $planLevel, 'Profile' => $from_user['ProfilePicture'], 'status' => $notification['Notification_Type'], 'OldValue' => $notification['OldValue'], "NewValue" => $notification['NewValue'], 'Preposition' => 'to');
                    array_push($result_msg, $message);
                }

                /* Left Panel newly assigned Field Values End */



                /*                 * ******* Followers Messages **************** */ else if ($notification['ActivityOn'] == 'FollowObj') {

                    if ($notification['NotifiedUser'] == $notification['NewValue']) { //if logged in user has been added
                        //Eg : moin.hussain added you as a follower to ticket #33
                        $activityOn = 'you';
                    } else {
                        //Eg : moin.hussain added sateesh.mandru as a follower to Ticket #33
                        $action_user = Collaborators::getCollaboratorById($notification['NewValue']);
                        $activityOn = $action_user['UserName'];
                    }


                    if ($notification['Notification_Type'] == "followed" || $notification['Notification_Type'] == "unfollowed") {
                        $to = "";
                        $otherMessage = "";
                        $preposition = "";
                    } else {
                        $to = $activityOn;
                        $otherMessage =Yii::t('app','follower');
                        $preposition = $notification['Notification_Type'] == "added" ? "to" : "from";
                    }  
                    $notification_type=$notification['Notification_Type'];
                    $message = array('onlyDate' => $dateOnly, 'Slug' => $notification['CommentSlug'], 'Project' => $projectDetails, 'IsSeen' => $notification['Status'], 'from' => $from_user['UserName'], 'object' => "follower", 'type' =>Yii::t('app',$notification_type), 'to' => $to, 'Title' => $ticket_data['Title'], 'TicketId' => $notification['TicketId'], 'date' => $Date, 'id' => $notification['_id'], 'PlanLevel' => $planLevel, 'Profile' => $from_user['ProfilePicture'], "OtherMessage" => $otherMessage, "Preposition" => $preposition);
                    array_push($result_msg, $message);
                }


                /*                 * ***** Followers Message End ********* */

                /*                 * *** Any changes in Editor ********** */
                $commentAllowedArray = ["comment", "reply", "edit", "delete","repliedOn"];
                if ($notification['ActivityOn'] == "comment" && (in_array($notification['Notification_Type'], $commentAllowedArray))) {
                    $datetime = $notification['NotificationDate']->toDateTime();
                    $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                    $Date = $datetime->format('Y-m-d H:i:s');
                    $collaborator = new Collaborators();
                    $selectfields = ['Title', 'TicketId', 'Fields.planlevel'];
                    $on="";
                    if ($notification['Notification_Type'] == 'comment') {
                        //Eg : moin.hussain commented on #33 Ticket 
                        if ($notification['ActivityFrom'] != $user) {
                            $notification['NewValue'] = CommonUtility::refineActivityData($notification['NewValue'], 50);
                            $preposition = ' "'.$notification['NewValue'].'"'." on";
                            $object = "comment";
                            $type = Yii::t('app','comment');
                        }
                    } else if ($notification['Notification_Type'] == 'reply') {
                        $preposition = "";
                        $object = "reply";
                        $type = Yii::t('app','reply');
                    }
                    else if ($notification['Notification_Type'] == 'repliedOn') {
                        $preposition = "comment";
                        $on =$notification['CommentOwner'];
                        $object = "repliedOn";
                        $type = Yii::t('app','repliedOn');
                    } 
                    else if ($notification['Notification_Type'] == 'edit') {
                        $preposition = "on";
                        $object = "edit";
                        $type = Yii::t('app','edit');
                    } else if ($notification['Notification_Type'] == 'delete') {
                        $preposition = "on";
                        $object = "delete";
                        $type =Yii::t('app','delete');
                    }
                    $message = array('onlyDate' => $dateOnly, 'Project' => $projectDetails, 'IsSeen' => $notification['Status'], 'from' => $from_user['UserName'], 'object' => $object, 'type' => $type, 'Slug' => $notification['CommentSlug'], 'date' => $Date, 'id' => $notification['_id'], 'Title' => $ticket_data['Title'], 'TicketId' => $notification['TicketId'], 'PlanLevel' => $planLevel, 'Profile' => $from_user['ProfilePicture'], "Preposition" => $preposition,"to"=>$on);
                    array_push($result_msg, $message);
                } else if ($notification['Notification_Type'] == "mention") {
                    $datetime = $notification['NotificationDate']->toDateTime();
                    $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                    $Date = $datetime->format('Y-m-d H:i:s');
                    $collaborator = new Collaborators();
                    $selectfields = ['Title', 'TicketId', 'Fields.planlevel'];
                    $preposition = "on";
                    if ($notification['Notification_Type'] == 'mention') {

                        if ($notification['NotifiedUser'] == $user) {
                            //Eg : moin.hussain mentioned you in a comment or
                            //     moin.hussain mentioned you in a reply
                            //     moin.hussain mentined you on Ticket #33
                            $notification['NotifiedUser'] = 'you';
                            $message = array('onlyDate' => $dateOnly, 'Project' => $projectDetails, 'IsSeen' => $notification['Status'], 'from' => $from_user['UserName'], 'object' => "mention", 'type' => Yii::t('app','mention'), 'id' => $notification['_id'], 'Slug' => $notification['CommentSlug'], 'ActivityOn' => $notification['NotifiedUser'], 'Title' => $ticket_data['Title'], 'TicketId' => $notification['TicketId'], 'date' => $Date, 'PlanLevel' => $planLevel, 'Profile' => $from_user['ProfilePicture'],"Preposition" => $preposition);
                            array_push($result_msg, $message);
                        }
                    }
                }


                else if (isset($storyField['Title'])) {
error_log("-------------------------55555555555555----------------".$notification['ActivityOn']);
                    $storyFieldName = $storyField['Title'];
                    //Eg : moin.hussain set duedate to 'apr-14-2017'
                    if ($storyField['Type'] == 4) {
                        $newValue = "";
                        $oldValue = "";
                        $storyFieldName="dueDate";
                        if ($notification['NewValue'] != "") {
                            $datetime = $notification['NewValue']->toDateTime();
                            $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                            $newValue = $datetime->format('M-d-Y');
                        }

                        if ($notification['OldValue'] != "") {
                            $storyFieldName="dueDateChange";
                            $datetime = $notification['OldValue']->toDateTime();
                            $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                            $oldValue = $datetime->format('M-d-Y');
                        }

                        $preposition = $notification['Notification_Type'] == "set" ? "to" : "**";
                        error_log($storyFieldName."========+++++++++++++++=============".$notification['Notification_Type']);
                        $message = array('onlyDate' => $dateOnly, 'Slug' => $notification['CommentSlug'], 'Project' => $projectDetails, 'IsSeen' => $notification['Status'], 'from' => $from_user['UserName'], 'type' =>Yii::t('app',$storyFieldName), 'OldValue' => $oldValue, "NewValue" => $newValue, 'Title' => $ticket_data['Title'], 'TicketId' => $notification['TicketId'], 'date' => $Date, 'status' => $notification['Notification_Type'], 'id' => $notification['_id'], 'PlanLevel' => $planLevel, 'Profile' => $from_user['ProfilePicture'], "Preposition" => $preposition);

                        array_push($result_msg, $message);
                    } else if ($storyField['Type'] != 6) {
                        error_log($notification['Notification_Type']."================@@@@@@@@@@@@@@@=================".$notification['ActivityOn']);
                        if ($notification['ActivityOn'] != "workflow" && $notification['ActivityOn'] != "tickettype") {
                           $notification['OldValue'] = CommonUtility::refineActivityData($notification['OldValue'], 50);
                            $notification['NewValue'] = CommonUtility::refineActivityData($notification['NewValue'], 50);
                        }
                         $notification['OldValue']= '"'.$notification['OldValue'].'"';
                         $notification['NewValue']='"'.$notification['NewValue'].'"';
                        $preposition = $notification['Notification_Type'] == "set" ? "to" : "**";
                        $message = array('onlyDate' => $dateOnly, 'Slug' => $notification['CommentSlug'], 'Project' => $projectDetails, 'IsSeen' => $notification['Status'], 'from' => $from_user['UserName'], 'type' => Yii::t('app',$notification['Notification_Type']), 'ActivityOn' => $storyFieldName, 'OldValue' => $notification['OldValue'], "NewValue" => $notification['NewValue'], 'Title' => $ticket_data['Title'], 'TicketId' => $notification['TicketId'], 'date' => $Date, 'status' => $notification['Notification_Type'], 'id' => $notification['_id'], 'PlanLevel' => $planLevel, 'Profile' => $from_user['ProfilePicture'], "Preposition" => $preposition);
                        array_push($result_msg, $message);
                    }
                }

                /*                 * ** Changes in Editor End ************ */
            }
            return $result_msg;
        } catch (\Throwable $ex) {
            Yii::error("NotificationTrait:getNotifications::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

    /**
     * @author Moin Hussain
     * @param type $notificationIds
     * @param type $projectId
     * @Description Sends an Email Notfication for the users on an activity.
     */
    public static function sendEmailNotification($notificationIds, $projectId, $bulkUpdate = 0) {
        try {
            $notificationIds = json_encode($notificationIds);
            $path = "/data/logs/ProjectX";
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }

            shell_exec("touch $path/email_notifications.log");
            echo shell_exec("php /usr/share/nginx/www/ProjectXService/yii notifications/fork-email-notification-process '$notificationIds' '$projectId' '$bulkUpdate' >> $path/email_notifications.log &");
        } catch (\Throwable $ex) {
            Yii::error("NotificationTrait:sendEmailNotification::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

    /**
     * @author Moin Hussain
     * @param type $notificationIds
     * @param type $projectId
     * @Description Sends Emails from background on multiple updates.
     */
    public function sendEmailNotificationFromBackground($notificationIds, $projectId) {
        try {
            echo "1. SendEmailNotificationFromBackground----started--------\n";
            $msg = '';
            $message = array();
            $result_msg = array();
            $user_message = "";
            $notifications = NotificationCollection::getNotificationDetails($notificationIds);
            echo("2. Notifications Count-------------" . count($notifications) . "\n");
            foreach ($notifications as $notification) {
                $recipient_list = array();
                $datetime = $notification['NotificationDate']->toDateTime();
                $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                $Date = $datetime->format('Y-m-d H:i:s');
                $selectfields = ['Title', 'TicketId', 'Fields.planlevel'];
                $ticket_data = TicketCollection::getTicketDetails($notification['TicketId'], $projectId, $selectfields);
                $ticket_msg = 'to' . ' ' . '#' . $notification['TicketId'] . ' ' . $ticket_data['Title'];
                $planLevel = $ticket_data["Fields"]["planlevel"]["value"];
                $planType = strtolower($ticket_data["Fields"]["planlevel"]["value_name"]);
                $from_user = TinyUserCollection::getMiniUserDetails($notification['ActivityFrom']);

                $activityOn = $notification['ActivityOn'];
                $storyField = StoryFields::getFieldDetails($activityOn, "Field_Name");
                $activityOnFieldType = $storyField["Type"];
                $ticketId = $notification['TicketId'];
                $title = $ticket_data['Title'];
                $fromUser = $from_user['UserName'];
                $mailingName = $from_user['FirstName'] . " " . $from_user['LastName'];
                $projectDetails = Projects::getProjectMiniDetails($projectId);
                $projectName = $projectDetails["ProjectName"];
                $link = Yii::$app->params['AppURL'] . "/project/$projectName/" . $ticketId . "/details";
                $redirectToHome = Yii::$app->params['AppURL'] . "/home";
                $newValue = "";
                $oldValue = "";
                $user_activity = '';
                $text_message='';
                $email_text['message']='';
                echo ("3. sending email ...");
                /* Left Panel Changed Field Values Start */
                $ticket_title = " | #" . $ticketId . ": " . $title;
                $subject = "ProjectX | " . $projectName . $ticket_title;
                $project_logo = "<tr><td align='left' valign='top' style='border:solid 1px #f0f0f0;'>

                                   <table width='598' border='0' cellspacing='0' cellpadding='0' align='left'>
                                 <tr>
                                  <td colspan='4' height='7'></td>
                                 </tr>
                                 <tr>
                                 <td width='10'>&nbsp;</td>
                                 <td width='186' height='55' align='left' valign='top'><a href={$redirectToHome}><img  src=" . '' . Yii::$app->params['EmailServerURL'] . '/files/tool/logo-emailer.png' . " style='border:0px; outline:0px; width:100%;'/></a></td>
                                  <td width='392' align='left' valign='top'>
                                  <table width='392' border='0' cellspacing='0' cellpadding='0' align='left'>
                                   <tr>
                                   <td style='font-family:Arial; font-size:12px;line-height:24px;color:#333333;' align='left' width='392'>
                                   <p style='text-align:right; margin:0px; padding:0px;'>Activity alert by <a href={$redirectToHome} style='color:#0199e0'>{$fromUser}</a></p>
                                       </td>
                                   </tr>
                                   <tr>
                                   <td style='font-family:Arial; font-size:12px;line-height:24px;color:#333333;' align='left' width='392'>
                                   <p style='text-align:right; margin:0px; padding:0px;'>Project: <a href={$redirectToHome} style='color:#0199e0'>{$projectName}<a href=''></a></p>
                                       </td>
                                   </tr>
                                  </table>
                                     </td>
                                   <td width='10'>&nbsp;</td> 
                                  </tr>
                                  </table>
                                     </td></tr>
                             <tr><td bgcolor='#f8f8f8' style='border-bottom:solid 1px #fff;'>
                             <table width='100%' border='0' cellspacing='0' cellpadding='0'>
                             <tr><td width='15'>&nbsp;</td><td width='570' align='left' valign:'top'>
                              <table width='570' border='0' align='left' cellpadding='0' cellspacing='0'>
                                <tr><td height='15'>&nbsp;</td></tr>";

                $link_message = "<a  style='font-family:Arial;font-size:16px;line-height:40px;color:#0199e0;' href={$link}>#{$ticketId}: {$title} </a>";
                $ticket_message = "<tr><td style='font-family:'Arial;font-size:18px;color:#0199e0; line-height:30px; font-weight:bold; padding-top:10px; padding-bottom:10px;'>" . $link_message . "</td></tr>";
                $view_ticket_message = "<tr><td><a style='font-family:Arial;font-size:12px;line-height:40px;color:#0199e0; text-decoration:none;' href={$link}>View Activity</a></td></tr>
                                    </table></td><td width='15'>&nbsp;</td></tr></table> </td></tr>";

                if ($activityOnFieldType == 6) {//newly assigned  
                    $action_user = Collaborators::getCollaboratorById($notification['NewValue']);
                    $old_user = Collaborators::getCollaboratorById($notification['OldValue']);
                    $assigned_message = $action_user['UserName'];

                    if ($old_user != '') {
                        $assigned_message = $old_user['UserName'] . ' to ' . $action_user['UserName'];
                    }
                    $preposition = "to";
                    $fieldName = "";
                    echo "==---------$$$$$$$$$$$$------------------".$planType;
                    if ($activityOn != "assignedto") {
                        $fieldName = $storyField["Title"];
                          $user_activity=$activityOn;
                    }else{
                        $user_activity="Assigned to";
                    }
                     $fieldName = $fieldName == "" ? "" : "as a " . $fieldName;
                    $text_message = <<<EOD
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">by {$fromUser} changed the {$storyField['Title']} from {$assigned_message}.</td></tr>
EOD;
                }


                /* ------To display the total worked hours in mail-----start---------- */ else if ($notification['ActivityOn'] == 'TotalTimeLog') {
                    $notification['OldValue'] = number_format((float) $notification['OldValue'], 1, '.', '');
                    $notification['NewValue'] = number_format((float) $notification['NewValue'], 1, '.', '');
                    $workedHours = $notification['OldValue'] . " => " . $notification['NewValue'];
                    $text_message = <<<EOD
             <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">{$fromUser}:</td></tr>
             <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">Total invested hours: {$workedHours}</td></tr>
EOD;
               $user_activity = $notification['ActivityOn'];
             } else if ($activityOn == "Title") {
                    $description_message = '"'.$notification['OldValue'] .'"'. " to " .'"'. $notification['NewValue'].'"';
                    $text_message = <<<EOD
             <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">{$fromUser} edited the title from {$description_message} </td></tr>
EOD;
              $user_activity = $activityOn;
             } else if ($activityOn == "Description") {
                    $text_message = <<<EOD
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">A story description has been edited by {$fromUser}:</td></tr>
        <tr><td height="10">&nbsp;</td></tr>
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;"><b>Description before editing was:</b></td></tr>
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">{$notification['OldValue']}</td></tr>
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;"><b>Description after editing was:</b></td></tr>
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">{$notification['NewValue']}</td></tr>
EOD;
                 $user_activity = $activityOn;
        }echo "-==================".$notification['Notification_Type'] ;
                $commentAllowedArray = ["comment", "reply", "edit", "delete","repliedOn"];
                if ($notification['ActivityOn'] == "comment" && (in_array($notification['Notification_Type'], $commentAllowedArray))) {
                    $datetime = $notification['NotificationDate']->toDateTime();
                    $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                    $Date = $datetime->format('Y-m-d H:i:s');
                    $collaborator = new Collaborators();
                    $selectfields = ['Title', 'TicketId', 'Fields.planlevel'];
                    $link .= "?Slug=" . $notification['CommentSlug'];
                    if ($notification['Notification_Type'] == "comment") {
                        $preposition = "on";
                        $object = "comment";
                        $type = Yii::t('app','comment');
                        $text_message = <<<EOD
                <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">{$fromUser} added a new comment</td></tr>
   <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">{$notification['NewValue']}</td></tr>
EOD;
                        $user_activity = "Comment";
                    } else if ($notification['Notification_Type'] == "reply") {
                        $text_message = <<<EOD
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">{$fromUser} replied:</td></tr>
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">{$notification['NewValue']}</td></tr>
EOD;
                        $email_text['message'] = $fromUser . " replied to your comment in a {$planType}.";
                        
                    } 
                    else if ($notification['Notification_Type'] == "repliedOn") {
                        echo "===================repliedOn========";
                        $text_message = <<<EOD
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">Activity by {$fromUser}:</td></tr>
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">{$notification['NewValue']}</td></tr>
EOD;
                        $email_text['message'] = "You have new activity alert";
                    } 
                    else if ($notification['Notification_Type'] == "edit") {
                        $preposition = "";
                        $object = "edit";
                        $type = Yii::t('app','reply');

                        $text_message = <<<EOD
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">A {$planType} comment has been edited by {$fromUser}:</td></tr>
        <tr><td height="10">&nbsp;</td></tr>
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;"><b>Comment before editing was:</b></td></tr>
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">{$notification['OldValue']}</td></tr>
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;"><b>Comment after editing was:</b></td></tr>
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">{$notification['NewValue']}</td></tr>
EOD;
           $user_activity = "Comment edit";         
        
        } else if ($notification['Notification_Type'] == "delete") {
                        $preposition = "";
                        $object = "delete";
                        $type = Yii::t('app','delete');

                        $text_message = <<<EOD
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">{$fromUser} deleted comment:</td></tr>
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">{$notification['NewValue']}</td></tr>
EOD;
          $user_activity ="Comment delete";              
        }
                } else if ($notification['Notification_Type'] == "mention") {
                    $datetime = $notification['NotificationDate']->toDateTime();
                    $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                    $Date = $datetime->format('Y-m-d H:i:s');
                    $collaborator = new Collaborators();
                    $selectfields = ['Title', 'TicketId', 'Fields.planlevel'];

                    $text_message = <<<EOD
                <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">{$fromUser} mentioned you:</td></tr>
   <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">{$notification['NewValue']}</td></tr>
EOD;
                 $user_activity = "Mention";   
   }


                if (isset($storyField['Title'])) {

                    $storyFieldName = $storyField['Title'];
                    //Eg : moin.hussain set duedate to 'apr-14-2017'
                    if ($storyField['Type'] == 4) {
                        if ($notification['NewValue'] != "") {
                            $datetime = $notification['NewValue']->toDateTime();
                            $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                            $newValue = $datetime->format('M-d-Y');
                        }

                        if ($notification['OldValue'] != "") {
                            $datetime = $notification['OldValue']->toDateTime();
                            $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                            $oldValue = $datetime->format('M-d-Y');
                        }
                        $dueDate = $newValue;
                        if ($oldValue != '') {
                            $dueDate = $oldValue . " to " . $newValue;
                            $text_message = <<<EOD
             <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">by {$fromUser} changed the {$planType} due date from {$dueDate}</td></tr>
EOD;
                            $user_activity ="duedate change";
                        } else {
                            $text_message = <<<EOD
             <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">by {$fromUser}  set the due date to {$newValue}</td></tr>
EOD;
                            $user_activity = "duedate";
                        }
                    } else if ($storyField['Type'] != 6) {
                        if ($notification['ActivityOn'] != "workflow" && $notification['ActivityOn'] != "tickettype" && $notification['ActivityOn'] != "dod") {
                            $notification['OldValue'] = CommonUtility::refineActivityData($notification['OldValue'], 10);
                            $notification['NewValue'] = CommonUtility::refineActivityData($notification['NewValue'], 10);
                        }
                        $ticketState = $notification['NewValue'];
                        if ($notification['OldValue'] != '' ) {
                            $ticketState = $notification['OldValue'] . " => " . $notification['NewValue'];
                        }
                        if($notification['ActivityOn']!="dod"){
                         $user_activity = $notification['ActivityOn'];
                        $text_message = <<<EOD
                        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">{$fromUser} changed the {$planType} {$storyField['Title']} from {$ticketState}</td></tr>
EOD;
                        }
                        if($notification['ActivityOn']=="dod"){
                            if ($notification['OldValue'] != '' )
                                      $user_activity ="dod edit";
                            else
                                $user_activity ="dod";
                             $text_message = <<<EOD
                        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">DOD:{$ticketState}</td></tr>
EOD;
                        }
                        
                    }
                }
                if ($notification['ActivityOn'] == 'FollowObj') {//newly assigned  
                    $action_user = Collaborators::getCollaboratorById($notification['NewValue']);
                }
                foreach ($notification['NotifiedCollaborators'] as $notifyuser) {
                    $notification['NotifiedUser'] = $notifyuser['CollaboratorId'];
                    if ($notifyuser['EmailNotification'] == 1) {
                        $collaboratorData = TinyUserCollection::getMiniUserDetails($notification['NotifiedUser']);

                        $recipient_list = array($collaboratorData['Email']);
                        $notified_user = $collaboratorData['UserName'];
                        $display_name = $collaboratorData['FirstName'] . " " . $collaboratorData['LastName'];

                        /* ----------------Title and Description changes----end------ */

                        /*                         * ******* Followers Messages **************** */
                        if ($notification['ActivityOn'] == 'FollowObj') {
                            if ($notification['NotifiedUser'] == $notification['NewValue']) { //if logged in user has been added
                                //Eg : moin.hussain added you as a follower to ticket #33
                                $activityOn = 'you';
                                $activityOn = $action_user['UserName'];
                            } else {
                                //Eg : moin.hussain added sateesh.mandru as a follower to Ticket #33
                                $activityOn = $action_user['UserName'];
                            }
                            $preposition = $notification['Notification_Type'] == "added" ? "to" : "from";
                            $follower_message = $fromUser . " " . $notification['Notification_Type'] . " " . $activityOn . " as a follower.";

                            $text_message = <<<EOD
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;"> {$follower_message} </td></tr>
EOD;
                            $user_activity = $notification['Notification_Type'] . " follower";
                        }
                        if ($notification['Notification_Type'] != "reply" && $notification['Notification_Type'] != "repliedOn") {
                            $email_text = array('message' => Yii::t('app',$user_activity.', {planType}',["planType"=>$planType]));
                        }
                        $user_message = "<tr><td style='border-bottom:1px solid #f0f0f0; font-family:Arial; font-size:14px;line-height:24px;color:#333333;  padding-bottom:10px;' width='570'>Dear " . $display_name . ",<br/><span style='font-family:Arial; font-size:14px;line-height:24px;color:#333333;'>{$email_text['message']}</span></td></tr>";

                        $text_newmessage = $project_logo . $user_message . $ticket_message . $text_message . $view_ticket_message;
                        CommonUtility::sendEmail($mailingName, $recipient_list, $text_newmessage, $subject);
                        echo ("4. sending email comppleted ...");
                    }
                }
            }
        } catch (\Throwable $ex) {
            Yii::error("NotificationTrait:sendEmailNotificationFromBackground::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

    /**
     * @author Lakshmi
     * @param type $notificationIds
     * @param type $projectId
     * @param type $bulkUpdate=''
     * @Description Sends Bulk changes, that is, Full ticket edit changes to users as email.
     */
    public function sendBulkEmailNotification($notificationIds, $projectId, $bulkUpdate = '') {
        try {
            $message = array();
            $result_msg = array();
            $project_logo = '';
            $user_message = '';
            $ticket_message = '';
            $view_ticket_message = '';
            $recipient_list = array();
            $notifications = NotificationCollection::getNotificationDetails($notificationIds);
            $uniqueNotifications = CommonUtility::getUniqueArrayObjects($notifications);
            $finalUserMessageArray = array();
            $oldValue = '';
            $newValue = '';
            $title = '';
            foreach ($uniqueNotifications as $notification) {
                $msg = <<<EOD
EOD;
                $datetime = $notification['NotificationDate']->toDateTime();
                $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                $Date = $datetime->format('Y-m-d H:i:s');
                $selectfields = ['Title', 'TicketId', 'Fields.planlevel'];
                $activityOn = $notification['ActivityOn'];
                $ticket_data = TicketCollection::getTicketDetails($notification['TicketId'], $projectId, $selectfields);
                $ticket_msg = 'to' . ' ' . '#' . $notification['TicketId'] . ' ' . $ticket_data['Title'];
                $planLevel = $ticket_data["Fields"]["planlevel"]["value"];
                $planType = strtolower($ticket_data["Fields"]["planlevel"]["value_name"]);
                $from_user = TinyUserCollection::getMiniUserDetails($notification['ActivityFrom']);
                $storyField = StoryFields::getFieldDetails($activityOn, "Field_Name");
                $activityOnFieldType = $storyField["Type"];
                $ticketId = $notification['TicketId'];
                $title = $ticket_data['Title'];
                $fromUser = $from_user['UserName'];
                $mailingName = $from_user['FirstName'] . " " . $from_user['LastName'];
                $projectDetails = Projects::getProjectMiniDetails($projectId);
                $projectName = $projectDetails["ProjectName"];
                $link = Yii::$app->params['AppURL'] . "/project/$projectName/" . $ticketId . "/details";
                $redirectToHome = Yii::$app->params['AppURL'] . "/home";

                if ($activityOn == "Title") {
                    $description_message = '"'.$notification['OldValue'] .'"'. " to " . '"'.$notification['NewValue'].'"';
                    $text_message = <<<EOD
             <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;padding-top:10px;">Title: {$description_message}</td></tr>
EOD;
                    $msg = $msg . $text_message;
                }
                if ($activityOn == "Description") {
                    $text_message = <<<EOD
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;padding-top:10px;">A story description has been edited</td></tr>
        <tr><td height="10">&nbsp;</td></tr>
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;"><b>Description before editing was:</b></td></tr>
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">{$notification['OldValue']}</td></tr>
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;"><b>Description after editing was:</b></td></tr>
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">{$notification['NewValue']}</td></tr>
EOD;
                    $msg = $msg . $text_message;
                }
                if ($activityOnFieldType == 6) {
                    $action_user = Collaborators::getCollaboratorById($notification['NewValue']);
                    $assigned_message = $action_user['UserName'];
                    $old_user = Collaborators::getCollaboratorById($notification['OldValue']);
                    if ($old_user != '') {
                        $assigned_message = $old_user['UserName'] . ' to ' . $action_user['UserName'];
                    }
                    $text_message = <<<EOD
             <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;padding-top:10px;">{$storyField['Title']}: {$assigned_message}</td></tr>
EOD;
                    $msg = $msg . $text_message;
                }
                if (isset($storyField['Title'])) {

                    $storyFieldName = $storyField['Title'];
                    //Eg : moin.hussain set duedate to 'apr-14-2017'
                    if ($storyField['Type'] == 4) {
                        $newValue = "";
                        $oldValue = "";
                        if ($notification['NewValue'] != "") {
                            $datetime = $notification['NewValue']->toDateTime();
                            $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                            $newValue = $datetime->format('M-d-Y');
                        }
                        if ($notification['OldValue'] != "") {
                            $datetime = $notification['OldValue']->toDateTime();
                            $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                            $oldValue = $datetime->format('M-d-Y');
                        }
                        $dueDate = $newValue;
                        if ($oldValue != '') {
                            $dueDate = $oldValue . " to " . $newValue;
                        }
                        $title = $storyField['Title'];
                        $text_message = <<<EOD
                               
             <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;padding-top:10px;">{$storyField['Title']}: {$dueDate} </td></tr>
EOD;
                        $msg = $msg . $text_message;
                    } else if ($storyField['Type'] != 6) {
                        if ($notification['ActivityOn'] != "workflow" && $notification['ActivityOn'] != "tickettype" && $notification['ActivityOn'] != "dod") {
                            $notification['OldValue'] = CommonUtility::refineActivityData($notification['OldValue'], 10);
                            $notification['NewValue'] = CommonUtility::refineActivityData($notification['NewValue'], 10);
                        }
                        $ticketState = $notification['NewValue'];
                        if ($notification['OldValue'] != '') {
                            $ticketState = $notification['OldValue'] . " to " . $notification['NewValue'];
                        }
                        $text_message = <<<EOD
            <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;padding-top:10px;">{$storyField['Title']}: {$ticketState} </td></tr>
EOD;
                        $msg = $msg . $text_message;
                    }
                }

                $ticket_title = " | #" . $ticketId . ": " . $title;
                $subject = "ProjectX | " . $projectName . $ticket_title;
                $project_logo = "<tr><td align='left' valign='top' style='border:solid 1px #f0f0f0;'>
                                   <table width='598' border='0' cellspacing='0' cellpadding='0' align='left'>
                                 <tr>
                                  <td colspan='4' height='7'></td>
                                 </tr>
                                 <tr>
                                 <td width='10'>&nbsp;</td>
                                 <td width='186' height='55' align='left' valign='top'><a href={$redirectToHome}><img  src=" . '' . Yii::$app->params['EmailServerURL'] . '/files/tool/logo-emailer.png' . " style='border:0px; outline:0px; width:100%;'/></a></td>
                                  <td width='392' align='left' valign='top'>
                                  <table width='392' border='0' cellspacing='0' cellpadding='0' align='left'>
                                   <tr>
                                   <td style='font-family:Arial; font-size:12px;line-height:24px;color:#333333;' align='left' width='392'>
                                   <p style='text-align:right; margin:0px; padding:0px;'>Activity alert by <a href={$redirectToHome} style='color:#0199e0'>{$fromUser}</a></p>
                                       </td>
                                   </tr>
                                   <tr>
                                   <td style='font-family:Arial; font-size:12px;line-height:24px;color:#333333;' align='left' width='392'>
                                   <p style='text-align:right; margin:0px; padding:0px;'>Project: <a href={$redirectToHome} style='color:#0199e0'>{$projectName}<a href=''></a></p>
                                       </td>
                                   </tr>
                                  </table>
                                     </td>
                                   <td width='10'>&nbsp;</td> 
                                  </tr>
                                  </table>
                                     </td></tr>
                             <tr><td bgcolor='#f8f8f8' style='border-bottom:solid 1px #fff;'>
                             <table width='100%' border='0' cellspacing='0' cellpadding='0'>
                             <tr><td width='15'>&nbsp;</td><td width='570' align='left' valign:'top'>
                              <table width='570' border='0' align='left' cellpadding='0' cellspacing='0'>
                                <tr><td height='15'>&nbsp;</td></tr>";
                $link_message = "<a  style='font-family:Arial;font-size:16px;line-height:30px;color:#0199e0;' href={$link}>#{$ticketId}: {$title} </a>";
                $ticket_message = "<tr><td style='font-family:'Arial;font-size:18px;color:#0199e0; line-height:30px; font-weight:bold; padding-top:10px; padding-bottom:10px;'>" . $link_message . "</td></tr>";
                $view_ticket_message = "<tr><td><a style='font-family:Arial;font-size:12px;line-height:40px;color:#0199e0; text-decoration:none;' href={$link}>View Activity</a></td></tr>
                                    </table></td><td width='15'>&nbsp;</td></tr></table> </td></tr>";
                $activity = "<tr><td style='border-bottom:1px solid #f0f0f0; font-family:Arial; font-size:14px;line-height:24px;color:#333333; padding-bottom:10px;padding-top:10px;' width='570'>by {$fromUser} changed the {$planType} :";


                $notifiedCollaborators = $notification['NotifiedCollaborators'];
                foreach ($notifiedCollaborators as $collaborator) {
                    $collaboratorId = $collaborator["CollaboratorId"];
                    $emailNotification = $collaborator["EmailNotification"];
                    if ($emailNotification == 1) {
                        if (isset($finalUserMessageArray[$collaboratorId])) {
                            $finalUserMessageArray[$collaboratorId] = $finalUserMessageArray[$collaboratorId] . "<br/>" . $msg;
                        } else {
                            $finalUserMessageArray[$collaboratorId] = $msg;
                        }
                    }
                }
            }
            foreach ($finalUserMessageArray as $key => $message) {
                $resUser = array();
                $collaborator = TinyUserCollection::getMiniUserDetails($key);
                array_push($resUser, $collaborator['Email']);
                $value = $collaborator['Email'];
                $notified_user = $collaborator['UserName'];
                $display_name = $collaborator['FirstName'] . " " . $collaborator['LastName'];
                $user_message = "<tr><td style='border-bottom:1px solid #f0f0f0; font-family:Arial; font-size:14px;line-height:24px;color:#333333;  padding-bottom:10px;' width='570'>Dear " . $display_name . ",<br/><span style='font-family:Arial; font-size:14px;line-height:24px;color:#333333;'>you have {$title} new activity alert.</span></td></tr>";
                $text_message = $project_logo . $user_message . $ticket_message . $activity . $message . $view_ticket_message;
                CommonUtility::sendEmail($mailingName, $resUser, $text_message, $subject);
            }
        } catch (\Throwable $ex) {
            Yii::error("NotificationTrait:sendBulkEmailNotification::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

    /**
     * @author Ryan
     * @param type $mailingName
     * @param type $invite_list
     * @param type $text_message
     * @param type $subject
     * @Description Sends an Invitation Email for the user with Invitation code.
     */
    public static function processSingleEmail($mailingName, $invite_list, $text_message, $subject) {
        try {
            error_log("singleEmailprocess____==============");
            $path = "/data/logs/ProjectX";
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }

            shell_exec("touch $path/single_email_notifications.log");
            echo shell_exec("php /usr/share/nginx/www/ProjectXService/yii notifications/fork-single-email-process '$mailingName' '$invite_list' '$text_message' '$subject' >> $path/email_notifications.log &");
        } catch (\Throwable $ex) {
            Yii::error("NotificationTrait:sendEmailNotification::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

}
