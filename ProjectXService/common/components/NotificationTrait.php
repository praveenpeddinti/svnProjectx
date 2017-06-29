<?php

namespace common\components;

use common\models\mongo\{
    TicketCollection,
    TinyUserCollection,
    NotificationCollection,
    TicketComments
};
use common\components\{
    CommonUtility
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
    Projects
};
use yii;

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
     * @uses Gets the field change value name for notification purpose
     * @params  $notifyType,$Value
     * @return type string
     */
    public static function getFieldChangeValue($notifyType, $Value) {
        try {
            if ($notifyType == 'priority') {
                $priorityObj = new Priority();
                $priorityDetails = $priorityObj->getPriorityDetails($Value);
                $priorityValue = $priorityDetails['Name'];
                return $priorityValue;
            }
            if ($notifyType == 'bucket') {
                $bucketDetails = Bucket::getBucketName($Value, 1);
                $bucketValue = $bucketDetails['Name'];
                return $bucketValue;
            }
            if ($notifyType == 'workflow') {
                $workflowDetails = WorkFlowFields::getWorkFlowDetails($Value);
                $workflowValue = $workflowDetails['Name'];
                return $workflowValue;
            }
            if ($notifyType == 'tickettype') {
                // error_log("------------------_SSSSSSSSSSSSSSSSS-----------tickettype-");
                $tickettypeDetails = TicketType::getTicketType($Value);
                $tickettypeValue = $tickettypeDetails['Name'];
                return $tickettypeValue;
            }
        } catch (Exception $ex) {
            Yii::log("NotificationTrait:getFieldChangeValue::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

    /**
     * @author Moin Hussain
     * @param type $ticketId
     * @param type $projectId
     * @param type $actionfieldName
     * @param type $newValue
     * @param type $activityUserId
     */
    public function saveActivity($ticketId, $projectId, $actionfieldName, $newValue, $activityUserId, $slug = "", $timezone) {
        $oldValue = "";
        $action = "";
        $returnValue = "noupdate";
        if (empty($slug))
            $slug = new \MongoDB\BSON\ObjectID();
        $ticketDetails = TicketCollection::getTicketDetails($ticketId, $projectId);
        // error_log("------------getTicket Details=======TTTTTTTTTTTT=====" . print_r($ticketDetails, 1));
        if ($actionfieldName == "Title" || $actionfieldName == "Description" || $actionfieldName == "TotalTimeLog") { //added actionFieldName for TotalTimeLog By Ryan
            //error_log("---------title-------" . $ticketDetails[$actionfieldName]);
            $oldValue = $ticketDetails[$actionfieldName];
        } else if ($actionfieldName == 'Followed' || $actionfieldName == 'Unfollowed' || $actionfieldName == 'Related' || $actionfieldName == 'ChildTask' || $actionfieldName == 'Unrelated') {
            $oldValue = "";
            switch ($actionfieldName) {
                case 'Followed':$action = "added to";
                    break;
                case 'Unfollowed':$action = "removed from";
                    break;
                case 'Related':$action = "has related";
                    break;
                case 'ChildTask':$action = "created";
                    break;
                case 'Unrelated':$action = "has unrelated";
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
            //  $record = iterator_to_array($record);
            //  error_log(print_r($record,1));
            //$slug =  new \MongoDB\BSON\ObjectID();
            if ($record["RecentActivityUser"] != $activityUserId || $record["Activity"] == "Comment" || $record["Activity"] == "PoppedFromChild") {
                // $dataArray = array();
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
            } else {
                $recentSlug = $record["RecentActivitySlug"];
                $property = array("Slug" => $slug, "ActionFieldName" => $actionfieldName, "Action" => $action, "PreviousValue" => $oldValue, "NewValue" => $newValue, "CreatedOn" => $currentDate);

                $v = $db->findAndModify(array("ProjectId" => (int) $projectId, "TicketId" => (int) $ticketId, "Activities.Slug" => $recentSlug), array('$addToSet' => array('Activities.$.PropertyChanges' => $property)), array('new' => 1, "upsert" => 1));

                $activitiesCount = count($v["Activities"]);
                if ($activitiesCount > 0) {
                    $activitiesCount = $activitiesCount - 1;
                }
                CommonUtility::prepareActivityProperty($property, $projectId, $timezone);
                $returnValue = array("referenceKey" => $activitiesCount, "data" => $property);
            }
            if ($ticketDetails["IsChild"] == 1 && $actionfieldName == "workflow") {
                //    $slug =  new \MongoDB\BSON\ObjectID();
                $commentDataArray = array(
                    "Slug" => $slug,
                    "CDescription" => "",
                    "CrudeCDescription" => "",
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
        //error_log("response-------".$v);
    }

    /**
     * @author Ryan Marshal
     * @param type $property
     * @return type 
     * @description  Used for saving the notifications in submit Description and comments  
     */
    public static function saveNotificationsToMentionOnly($ticket_data, $userslist, $notifyType = null, $slug = null) {
        try {
            // error_log("----saveNotificationsToMentionOnly---");
            if($slug== null){
                $slug =  new \MongoDB\BSON\ObjectID();
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
                    //error_log("====Notification with mention==" . $user);
                    $user = Collaborators::getCollaboratorId($user);
                    array_push($collaboratorIds, array("CollaboratorId"=>(int)$user['Id'] ,"IsRead"=>0));
                }
                    $tic = new NotificationCollection();
                    $tic->TicketId = $ticketId;
                    $tic->ProjectId = $projectId;
                    $tic->NotifiedCollaborators = $collaboratorIds;
                    $tic->ActivityFrom = (int) $loggedinUser;
                    $tic->NotificationDate = $currentDate;
                    $tic->Notification_Type = 'mention';
                    $tic->ActivityOn = 'description';//$user['Id'];
                    $tic->CommentSlug = $slug;
                    $result = $tic->save();
                    if ($result) {
                        $notificationId = $tic->_id;
                        self::sendEmailNotification(array($notificationId), $projectId);
                    }
            }
        } catch (Exception $ex) {
            Yii::log("NotificationTrait:saveNotificationsWithMention::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

    /**
     * @author Ryan
     * @param type $commentData
     * @param type $notify_type //comment,reply
     * @param type $slug
     */
    public static function saveNotificationsForComment($commentData, $userslist, $notify_type, $slug) {
        try {
            // error_log("saveNotificationsForComment---".$notify_type."---".$slug);
            $commentOwner = $commentData->Comment->OriginalCommentorId;
            $loggedinUser = $commentData->userInfo->Id;
            $ticketId = (int) $commentData->ticketId;
            $projectId = (int) $commentData->projectId;
            $description = CommonUtility::refineDescriptionForEmail($commentData->Comment->CrudeCDescription);
            $mentionUserIdlist = array();
            $collaboratorIds = array();
            //  error_log("description------------".$description);
            $commentDescripition = $description;
            $data = TicketCollection::getTicketDetails($ticketId, $projectId);
            $followers = $data['Followers'];
            $followers = CommonUtility::filterFollowers($followers);
            $currentDate = new \MongoDB\BSON\UTCDateTime(time() * 1000);
            $oldValue = "";
            if ($notify_type == "edit") {
                $OldNotfication = NotificationCollection::getNotificationDetail($slug, $ticketId, $projectId);
                $oldValue = $OldNotfication["NewValue"];
                // error_log("oldv alue-----------".$oldValue);
            }

            //For Reply....added by Ryan
            if (!empty($userslist)) {
             foreach ($userslist as $user) {
              $user = Collaborators::getCollaboratorId($user);
              array_push($collaboratorIds, array("CollaboratorId"=>(int)$user['Id'] ,"IsRead"=>0));
              array_push($mentionUserIdlist, $user['Id']);
             }
                    $tic = new NotificationCollection();
                    $tic->TicketId = $ticketId;
                    $tic->ProjectId = $projectId;
                    $tic->NotifiedCollaborators = $collaboratorIds;
                    $tic->ActivityFrom = (int) $loggedinUser;
                    $tic->NotificationDate = $currentDate;
                    $tic->OldValue = $oldValue; //added for consistency
                    $tic->NewValue = $commentDescripition; //added for consistency
                    $tic->Notification_Type = 'mention';
                    //$tic->ActivityOn=$user['Id']; //previous use case
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
            if (!empty($followers)) {
            foreach ($followers as $follower) {
                if ($follower['FollowerId'] != $loggedinUser && $follower['FollowerId'] != $commentOwner && !in_array($follower['FollowerId'], $mentionUserIdlist)) {
                     array_push($collaboratorIds, array("CollaboratorId"=>(int)$follower['FollowerId'] ,"IsRead"=>0));
                    }
            }
                    $tic = new NotificationCollection();
                    $tic->NotifiedCollaborators = $collaboratorIds;
                    $tic->TicketId = $ticketId;
                    $tic->ProjectId = $projectId;
                    $tic->ActivityFrom = (int) $loggedinUser;
                    $tic->NotificationDate = $currentDate;
                    $tic->OldValue = $oldValue; //added for consistency
                    $tic->NewValue = $commentDescripition; //added for consistency
                    $tic->CommentSlug = $slug;
                    $tic->Notification_Type = $notify_type;
                    $tic->ActivityOn = "comment"; //added for consistency
                    //}
                    $result = $tic->save();
                    if ($result) {
                        $notificationId = $tic->_id;
                        array_push($notificationIdsArray, $notificationId);
                    }
                    
                   // seprate record for comment owner
                    $collaboratorIds = array();
                    if($commentOwner !=''){
                    array_push($collaboratorIds, array("CollaboratorId"=>(int)$commentOwner ,"IsRead"=>0));
                    $tic = new NotificationCollection();
                    $tic->NotifiedCollaborators = $collaboratorIds;
                    $tic->TicketId = $ticketId;
                    $tic->ProjectId = $projectId;
                    $tic->ActivityFrom = (int) $loggedinUser;
                    $tic->NotificationDate = $currentDate;
                    $tic->OldValue = $oldValue; //added for consistency
                    $tic->NewValue = $commentDescripition; //added for consistency
                    $tic->CommentSlug = $slug;
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
            self::sendEmailNotification($notificationIdsArray, $projectId);
        } catch (Exception $ex) {
            Yii::log("NotificationTrait:saveNotificationsForComment::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

    /**
     * @author Ryan Marshal
     * @param type $notification_data
     * @return type
     * @description  Used for saving the notifications for assignedTo,Add/Remove Followers,stakeholder and left panel property changes  
     */
    public static function saveNotifications($notification_data, $notifyType, $activityOn, $fieldType = "", $slug = '', $bulkUpdate = '', $taskId = 0) {
        try {
            $oldValue = '';
            $newValue = '';
            $oldCollaborator = '';
            $newCollaborator = '';
            //For Story Detail Page Use Case.....
            $ticketId = isset($notification_data->ticketId) ? (int) $notification_data->ticketId : (int) $notification_data->data->ticketId;
            // $ticketId=$notification_data->TicketId;
            $projectId = (int) $notification_data->projectId;
            // $from=$notification_data->userInfo->username;
            $loggedInUser = $notification_data->userInfo->Id;
            $notify_type = $notifyType; //this will be changed to ActivityOn in the below code....
            $currentDate = new \MongoDB\BSON\UTCDateTime(time() * 1000);
           // error_log("=+++++++++=save notifications=+++++++");
            $ticketDetails = TicketCollection::getTicketDetails($ticketId, $projectId);
            $followers = $ticketDetails['Followers'];
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
                } else {
                    $notification_Type = 'set';
                    $newValue = $activityOn; //added for fixing time log issue
                    $activityOn = 'set';
                }
            } else if (isset($ticketDetails["Fields"][$notifyType])) {
                $oldValue = $ticketDetails["Fields"][$notifyType]["value"];
            }

//                    if(!($notify_type=='added' || $notify_type=='removed'))
//                    {
            if ($fieldType == 1 || $fieldType == 4 || $fieldType == 5 || $fieldType == 8) {
                //for due date,dod and estimated points
                $oldValue = $oldValue;
                $newValue = $activityOn;
                if ($oldValue == $newValue) {
                    return;
                }
                if ($oldValue != '') { //if changed
                    $activityOn = 'changed';
                } else { //if set new value
                    $activityOn = 'set';
                    $oldValue = '';
                }
                if ($fieldType == 4) {
                    $validDate = CommonUtility::validateDate($newValue);
                    if ($validDate) {
                        $newValue = new \MongoDB\BSON\UTCDateTime(strtotime($validDate) * 1000);
                    }
                    $validDate = CommonUtility::validateDate($oldValue);
                    if ($validDate) {
                        $oldValue = new \MongoDB\BSON\UTCDateTime(strtotime($validDate) * 1000);
                    }
                }
            } else if ($fieldType == 6) {
                $oldCollaborator = $oldValue;
                $newCollaborator = $activityOn; //this is a field value....
                if ($oldCollaborator != '') { //if changed
                    $activityOn = 'changed';
                } else { //if set new value
                    $activityOn = 'set';
                    $oldValue = '';
                }
                error_log("==activity on== newCollaborator==========" . $newCollaborator);
                $tic = new NotificationCollection();
                $tic->CommentSlug = $slug;
                $tic->TicketId = $ticketId;
                $tic->ProjectId = $projectId;
                $tic->NotifiedCollaborators =array(array('CollaboratorId'=>(int) $newCollaborator,'IsRead'=>0)) ; //new use case "setting newly assigned mem
                $tic->ActivityFrom = (int) $loggedInUser;
                $tic->Notification_Type = $activityOn;
                $tic->ActivityOn = $notify_type;
                $tic->NotificationDate = $currentDate;
                $tic->OldValue = $oldCollaborator;
                $tic->NewValue = $newCollaborator;
               // $tic->Status = 0;

                //$tic->Notification_Type=$notify_type; //previous use case
                //new use case 'set' or 'changed'
                //$tic->ActivityOn= $activityOn; //previous use case
                //new use case eg: assignedTo,stakeholder,priority,etc...
                $result = $tic->save();
                if ($result) {
                    $notificationId = $tic->_id;
                    //  error_log("before sendign assing to notircioant--");
                    self::sendEmailNotification(array($notificationId), $projectId);
                }

                //self::sendMail($ticketDetails,$loggedInUser, $newCollaborator,$notify_type);   
                //}
            } else if ($fieldType == "FollowObj") {
                if ($loggedInUser != $activityOn) {

                    $notification_Type = ($notifyType == 'add') ? 'added' : 'removed';

                    $tic = new NotificationCollection();
                    $tic->CommentSlug = $slug;
                    $tic->TicketId = $ticketId;
                    $tic->ProjectId = $projectId;
                    $tic->NotifiedCollaborators = array(array('CollaboratorId'=>(int) $activityOn,'IsRead'=>0) ); //new use case "setting newly assigned mem
                    $tic->ActivityFrom = (int) $loggedInUser;
                    $tic->Notification_Type = $notification_Type;
                    $tic->ActivityOn = $fieldType;
                    $tic->NotificationDate = $currentDate;
                    $tic->OldValue = "";
                    $tic->NewValue = $activityOn;
                    //$tic->Status = 0;

                    //$tic->Notification_Type=$notify_type; //previous use case
                    //new use case 'set' or 'changed'
                    //$tic->ActivityOn= $activityOn; //previous use case
                    //new use case eg: assignedTo,stakeholder,priority,etc...
                    $result = $tic->save();
                    if ($result) {
                        $notificationId = $tic->_id;
                        self::sendEmailNotification(array($notificationId), $projectId);
                    }
                }
            } else if (($notifyType != "Description" && $notifyType != "Title" && $notifyType != "TotalTimeLog") && ($fieldType != "Description" && $fieldType != "Title" && $fieldType != "TotalTimeLog")) { //This is for left hand property changes
                $oldFieldId = $oldValue;
                $newFieldId = $activityOn;
                $newValue = self::getFieldChangeValue($notifyType, $newFieldId);
                if ($oldValue != '') { //if changed
                    $activityOn = 'changed';
                    $oldValue = self::getFieldChangeValue($notifyType, $oldFieldId);
                } else { //if set new value
                    $activityOn = 'set';
                    $oldValue = '';
                }
            }
           
            /* notification for all followers and the stakeholders */
            $notificationIds = array();
            $collaboratorUser=array();
           
            //@Lakshmi
            //making collaboratorsUser array to save the followers ids in array
            foreach ($followers as $follower) {
                if ($follower['FollowerId'] == $loggedInUser) {
                    continue;
                }
            array_push($collaboratorUser,array('CollaboratorId'=>$follower['FollowerId'],'IsRead'=>0));
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
                    //  error_log("in descrioton--------#$#$");
                    $tic->Notification_Type = $notification_Type;
                    $tic->ActivityOn = $notifyType;
                    $oldValue = CommonUtility::refineDescriptionForEmail($oldValue);
                    $newValue = CommonUtility::refineDescriptionForEmail($newValue);
                    $tic->OldValue = $oldValue;
                    $tic->NewValue = $newValue;
                    //$tic->Status = 0;
                    $result = $tic->save();
                    if ($result) {
                        $notificationId = $tic->_id;
                        array_push($notificationIds, $notificationId);
                    }
                } else if ($fieldType == "FollowObj") {

                    //error_log("===============Folloewrr+===111111111111==========" . $fieldType);

                    if ($follower['FollowerId'] != $activityOn) {
                        $tic->ActivityOn = $fieldType;
                        if ($loggedInUser == $activityOn) {
                            $notification_Type = ($notifyType == 'add') ? 'followed' : 'unfollowed';
                        } else {
                            $notification_Type = ($notifyType == 'add') ? 'added' : 'removed';
                        }
                        $tic->Notification_Type = $notification_Type;
                        $tic->OldValue = "";
                        $tic->NewValue = $activityOn;
                       // $tic->Status = 0;
                        $result = $tic->save();
                        //   error_log("===============Folloewrr+=============");
                        if ($result) {
                            $notificationId = $tic->_id;
                            array_push($notificationIds, $notificationId);
                        }
                    }
                } else if ($fieldType == 6) {

                    if ($follower['FollowerId'] != $newCollaborator) { //previous case it was $activityOn which was collaborator_name
                        $tic->Notification_Type = $activityOn;
                       // $tic->Status = 0;
                        $tic->OldValue = $oldCollaborator;
                        $tic->NewValue = $newCollaborator;
                        $result = $tic->save();
                        if ($result) {
                            $notificationId = $tic->_id;
                            array_push($notificationIds, $notificationId);
                        }
                    }
                } else {
                    $tic->Notification_Type = $activityOn;
                   // $tic->Status = 0;
                    $oldValue = ($fieldType == 4) ? $oldValue : (string) $oldValue;
                    $tic->OldValue = $oldValue;
                    $tic->NewValue = $newValue;
                    $result = $tic->save(); //here not sending emails for left hand side propert change excpet Assinged to , stake holder
                    if ($result) {
                        //  error_log("-----------_SSSSSSSSS------saving type----------");
                        $notificationId = $tic->_id;
                        array_push($notificationIds, $notificationId);
                    }
                }
          //  }
            //error_log("before sendign assing to notircioant-****-" . print_r($notificationIds, 1));
            if ($bulkUpdate == 1) {
                return $notificationIds;
            } else {
                self::sendEmailNotification($notificationIds, $projectId);
            }
        } catch (Exception $ex) {
            Yii::log("NotificationTrait:saveNotifications::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

    public static function getNotifications($user, $projectId, $offset = 0, $limit = 5, $viewAll = 0) {
        error_log("==in get notifications---" . $user . "---" . $projectId . "---" . $offset . "---" . $limit);
        $msg = '';
        $message = array();
        $result_msg = array();
        // $action_user=Collaborators::getCollaboratorById($user);
        $status='';
        try {
            $projectObj = new Projects();
            $notifications = NotificationCollection::getNotifications($user, $offset, $limit, $viewAll);
            //constucting the notifications for the user
            foreach ($notifications as $notification) {

                if($viewAll == 0){
                   $notification['Status'] = 0;
                   $notification['NotifiedUser']= $user;  
                }else{
                 foreach($notification['NotifiedCollaborators']  as $key => &$value){
                    if($value['CollaboratorId']==(int)$user){
                       $notification['Status']=$value['IsRead'];
                       $notification['NotifiedUser']= $value['CollaboratorId'];
                       break;
                    }
                 }  
                }
                $datetime = $notification['NotificationDate']->toDateTime();
                $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                $Date = $datetime->format('M-d-Y H:i:s');
                $selectfields = ['Title', 'TicketId', 'Fields.planlevel'];
                $ticket_data = TicketCollection::getTicketDetails($notification['TicketId'], $projectId, $selectfields);
                $ticket_msg = 'to' . ' ' . '#' . $notification['TicketId'] . ' ' . $ticket_data['Title'];
                $planLevel = $ticket_data["Fields"]["planlevel"]["value"];
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
                    //$action_user=Collaborators::getCollaboratorById($notification['ActivityOn']);
                    if ($notification['NotifiedUser'] == $notification['NewValue']) {

                        //for logged in user
                        //Eg : moin.hussain assigned you to ticket #33 
                        $to = "you";
                    }
                     else {
                        $action_user = Collaborators::getCollaboratorById($notification['NewValue']);
                        //Eg : moin.hussain assigned sateesh.mandru to Ticket #33
                        //$msg=$from_user['UserName'] .' '. Yii::$app->params['assignedTo'] .' '.$action_user['UserName'].' '.$ticket_msg;
                        $to = $action_user['UserName'];
                    }
                    $preposition = "to";
                    $message = array('Slug' => $notification['CommentSlug'], 'Project' => $projectDetails, 'IsSeen' => $notification['Status'], 'from' => $from_user['UserName'], 'object' => "user", 'type' => Yii::$app->params['assigned'], 'to' => $to, 'Title' => $ticket_data['Title'], 'TicketId' => $notification['TicketId'], 'date' => $Date, 'id' => $notification['_id'], 'PlanLevel' => $planLevel, 'Profile' => $from_user['ProfilePicture'], "OtherMessage" => Yii::$app->params[$activityOn], "Preposition" => $preposition);
                    array_push($result_msg, $message);
                } else if ($notification['ActivityOn'] == 'Description' || $notification['ActivityOn'] == 'Title') {
                    $notification['OldValue'] = CommonUtility::refineActivityData($notification['OldValue'], 10);
                    $notification['NewValue'] = CommonUtility::refineActivityData($notification['NewValue'], 10);
                    $message = array('Slug' => $notification['CommentSlug'], 'Project' => $projectDetails, 'IsSeen' => $notification['Status'], 'from' => $from_user['UserName'], 'object' => "description", 'type' => Yii::$app->params[$notification['Notification_Type']], 'id' => $notification['_id'], 'ActivityOn' => $notification['ActivityOn'], 'Title' => $ticket_data['Title'], 'TicketId' => $notification['TicketId'], 'date' => $Date, 'PlanLevel' => $planLevel, 'Profile' => $from_user['ProfilePicture'], 'status' => $notification['Notification_Type'], 'OldValue' => $notification['OldValue'], "NewValue" => $notification['NewValue']);
                    array_push($result_msg, $message);
                }
                //for child task create...by Ryan
                else if ($notification['ActivityOn'] == 'Create Task' || $notification['ActivityOn'] == 'Relate' || $notification['ActivityOn'] == 'UnRelate') {
                    $targetTicketData = TicketCollection::getTicketDetails($notification['TargetTicketId'], $projectId);
                    $targetPlanLevel = $targetTicketData["Fields"]["planlevel"]["value"];
                    if ($notification['ActivityOn'] == 'Create Task') {
                        $message = array('Slug' => $notification['CommentSlug'], 'Project' => $projectDetails, 'IsSeen' => $notification['Status'], 'from' => $from_user['UserName'], 'object' => "task", 'type' => 'created', 'id' => $notification['_id'], 'Title' => $ticket_data['Title'], 'TicketId' => $ticket_data['TicketId'], 'date' => $Date, 'PlanLevel' => $planLevel, 'TargetPlanLevel' => $targetPlanLevel, 'Profile' => $from_user['ProfilePicture'], 'status' => $notification['Notification_Type'], 'TargetTicketId' => $targetTicketData['TicketId'], 'TargetTicketTitle' => $targetTicketData['Title']);
                    } else {
                        if ($notification['ActivityOn'] == 'Relate') {
                            $message = array('Slug' => $notification['CommentSlug'], 'Project' => $projectDetails, 'IsSeen' => $notification['Status'], 'from' => $from_user['UserName'], 'object' => "task", 'type' => 'related', 'id' => $notification['_id'], 'Title' => $ticket_data['Title'], 'TicketId' => $ticket_data['TicketId'], 'date' => $Date, 'PlanLevel' => $planLevel, 'TargetPlanLevel' => $targetPlanLevel, 'Profile' => $from_user['ProfilePicture'], 'status' => $notification['Notification_Type'], 'TargetTicketId' => $targetTicketData['TicketId'], 'TargetTicketTitle' => $targetTicketData['Title']);
                        } else {
                            $message = array('Slug' => $notification['CommentSlug'], 'Project' => $projectDetails, 'IsSeen' => $notification['Status'], 'from' => $from_user['UserName'], 'object' => "task", 'type' => 'unrelated', 'id' => $notification['_id'], 'Title' => $ticket_data['Title'], 'TicketId' => $ticket_data['TicketId'], 'date' => $Date, 'PlanLevel' => $planLevel, 'TargetPlanLevel' => $targetPlanLevel, 'Profile' => $from_user['ProfilePicture'], 'status' => $notification['Notification_Type'], 'TargetTicketId' => $targetTicketData['TicketId'], 'TargetTicketTitle' => $targetTicketData['Title']);
                        }
                    }
                    array_push($result_msg, $message);
                } else if ($notification['ActivityOn'] == 'TotalTimeLog') {
                    $notification['OldValue'] = number_format((float) $notification['OldValue'], 1, '.', '');
                    $notification['NewValue'] = number_format((float) $notification['NewValue'], 1, '.', '');
                    $message = array('Slug' => $notification['CommentSlug'], 'Project' => $projectDetails, 'IsSeen' => $notification['Status'], 'from' => $from_user['UserName'], 'object' => "work log", 'type' => Yii::$app->params[$notification['Notification_Type']], 'id' => $notification['_id'], 'ActivityOn' => "Total Time Log", 'Title' => $ticket_data['Title'], 'TicketId' => $notification['TicketId'], 'date' => $Date, 'PlanLevel' => $planLevel, 'Profile' => $from_user['ProfilePicture'], 'status' => $notification['Notification_Type'], 'OldValue' => $notification['OldValue'], "NewValue" => $notification['NewValue'], 'Preposition' => 'to');
                    array_push($result_msg, $message);
                }

                /* Left Panel newly assigned Field Values End */



                /*                 * ******* Followers Messages **************** */ else if ($notification['ActivityOn'] == 'FollowObj') {

                    if ($notification['NotifiedUser'] == $notification['NewValue']) { //if logged in user has been added
                        //Eg : moin.hussain added you as a follower to ticket #33
                        $activityOn = 'you';
                    }
   
                    else {
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
                        $otherMessage = Yii::$app->params['follower'];
                        $preposition = $notification['Notification_Type'] == "added" ? "to" : "from";
                    }
                    $message = array('Slug' => $notification['CommentSlug'], 'Project' => $projectDetails, 'IsSeen' => $notification['Status'], 'from' => $from_user['UserName'], 'object' => "follower", 'type' => Yii::$app->params[$notification['Notification_Type']], 'to' => $to, 'Title' => $ticket_data['Title'], 'TicketId' => $notification['TicketId'], 'date' => $Date, 'id' => $notification['_id'], 'PlanLevel' => $planLevel, 'Profile' => $from_user['ProfilePicture'], "OtherMessage" => $otherMessage, "Preposition" => $preposition);
                    array_push($result_msg, $message);
                }


                /*                 * ***** Followers Message End ********* */


                /*                 * *** Any changes in Editor ********** */
                $commentAllowedArray = ["comment", "reply", "edit", "delete"];
                if ($notification['ActivityOn'] == "comment" && (in_array($notification['Notification_Type'], $commentAllowedArray))) {
                    $datetime = $notification['NotificationDate']->toDateTime();
                    $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                    $Date = $datetime->format('M-d-Y H:i:s');
                    $collaborator = new Collaborators();
                    $selectfields = ['Title', 'TicketId', 'Fields.planlevel'];
                    if ($notification['Notification_Type'] == 'comment') {
                        //Eg : moin.hussain commented on #33 Ticket 
                        if ($notification['ActivityFrom'] != $user) {
                            $preposition = "on";
                            $object = "comment";
                            $type = Yii::$app->params['comment'];
                        }
                    } else if ($notification['Notification_Type'] == 'reply') {
                        $preposition = "";
                        $object = "reply";
                        $type = Yii::$app->params['reply'];
                    } else if ($notification['Notification_Type'] == 'edit') {
                        $preposition = "on";
                        $object = "edit";
                        $type = Yii::$app->params['edit'];
                    } else if ($notification['Notification_Type'] == 'delete') {
                        $preposition = "on";
                        $object = "delete";
                        $type = Yii::$app->params['delete'];
                    }
                    $message = array('Project' => $projectDetails, 'IsSeen' => $notification['Status'], 'from' => $from_user['UserName'], 'object' => $object, 'type' => $type, 'Slug' => $notification['CommentSlug'], 'date' => $Date, 'id' => $notification['_id'], 'Title' => $ticket_data['Title'], 'TicketId' => $notification['TicketId'], 'PlanLevel' => $planLevel, 'Profile' => $from_user['ProfilePicture'], "Preposition" => $preposition);
                    array_push($result_msg, $message);
                } else if ($notification['Notification_Type'] == "mention") {
                    $datetime = $notification['NotificationDate']->toDateTime();
                    $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                    $Date = $datetime->format('M-d-Y H:i:s');
                    $collaborator = new Collaborators();
                    $selectfields = ['Title', 'TicketId', 'Fields.planlevel'];
                    if ($notification['Notification_Type'] == 'mention') {

                        if ($notification['NotifiedUser'] == $user) {
                            //Eg : moin.hussain mentioned you in a comment or
                            //     moin.hussain mentioned you in a reply
                            //     moin.hussain mentined you on Ticket #33
                            $notification['NotifiedUser'] = 'you';
                            $message = array('Project' => $projectDetails, 'IsSeen' => $notification['Status'], 'from' => $from_user['UserName'], 'object' => "mention", 'type' => Yii::$app->params['mention'], 'id' => $notification['_id'], 'Slug' => $notification['CommentSlug'], 'ActivityOn' => $notification['NotifiedUser'], 'Title' => $ticket_data['Title'], 'TicketId' => $notification['TicketId'], 'date' => $Date, 'PlanLevel' => $planLevel, 'Profile' => $from_user['ProfilePicture']);
                            array_push($result_msg, $message);
                        }
                    }
                }


                //if($notification['Notification_Type']=='duedate' || $notification['Notification_Type']=='dod' || $notification['Notification_Type']=='estimatedpoints')
                // {
                // $storyField = StoryFields::getFieldDetails($notification['ActivityOn'],"Field_Name");
                else if (isset($storyField['Title'])) {

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

                        $preposition = $notification['Notification_Type'] == "set" ? "to" : "**";
                        $message = array('Slug' => $notification['CommentSlug'], 'Project' => $projectDetails, 'IsSeen' => $notification['Status'], 'from' => $from_user['UserName'], 'type' => Yii::$app->params["{$notification['Notification_Type']}"], 'ActivityOn' => $storyFieldName, 'OldValue' => $oldValue, "NewValue" => $newValue, 'Title' => $ticket_data['Title'], 'TicketId' => $notification['TicketId'], 'date' => $Date, 'status' => $notification['Notification_Type'], 'id' => $notification['_id'], 'PlanLevel' => $planLevel, 'Profile' => $from_user['ProfilePicture'], "Preposition" => $preposition);
                        array_push($result_msg, $message);
                    } else if ($storyField['Type'] != 6) {
                        if ($notification['ActivityOn'] != "workflow" && $notification['ActivityOn'] != "tickettype") {
                            $notification['OldValue'] = CommonUtility::refineActivityData($notification['OldValue'], 10);
                            $notification['NewValue'] = CommonUtility::refineActivityData($notification['NewValue'], 10);
                        }

                        $preposition = $notification['Notification_Type'] == "set" ? "to" : "**";
                        $message = array('Slug' => $notification['CommentSlug'], 'Project' => $projectDetails, 'IsSeen' => $notification['Status'], 'from' => $from_user['UserName'], 'type' => Yii::$app->params["{$notification['Notification_Type']}"], 'ActivityOn' => $storyFieldName, 'OldValue' => $notification['OldValue'], "NewValue" => $notification['NewValue'], 'Title' => $ticket_data['Title'], 'TicketId' => $notification['TicketId'], 'date' => $Date, 'status' => $notification['Notification_Type'], 'id' => $notification['_id'], 'PlanLevel' => $planLevel, 'Profile' => $from_user['ProfilePicture'], "Preposition" => $preposition);
                        array_push($result_msg, $message);
                    }
                }

                /* Left Panel Changed Field Values Start */



                /*                 * *******Left Panel  Changed Field Values End ****************** */



                /*                 * ** Changes in Editor End ************ */
            }


            // error_log("==Result Msg==".print_r($result_msg,1));
            // return $notifications;
            return $result_msg;
        } catch (Exception $ex) {
            Yii::log("NotificationTrait:getNotifications::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

    /**
     * @author Moin Hussain
     * @param type $notificationIds
     * @param type $projectId
     */
    public static function sendEmailNotification($notificationIds, $projectId, $bulkUpdate = 0) {
        try {
              error_log("sendEmailNotification____==============" .print_r($notificationIds,1));
            $notificationIds = json_encode($notificationIds);
            // error_log("send e,ao;=======222222222===============");
            $path = "/data/logs/ProjectX";
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }

            shell_exec("touch $path/email_notifications.log");
            echo shell_exec("php /usr/share/nginx/www/ProjectXService/yii notifications/fork-email-notification-process '$notificationIds' '$projectId' '$bulkUpdate' >> $path/email_notifications.log &");
        } catch (Exception $ex) {
            Yii::log("NotificationTrait:sendEmailNotification::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

    /**
     * @author Moin Hussain
     * @param type $notificationIds
     * @param type $projectId
     */
    public function sendEmailNotificationFromBackground($notificationIds, $projectId) {
        try {
            // echo "1. SendEmailNotificationFromBackground----started--------\n";
            $msg = '';
            $message = array();
            $result_msg = array();

             echo("sendEmailNotification--New==".print_r($notificationIds,1));
            $notifications = NotificationCollection::getNotificationDetails($notificationIds);
             echo("2. Notifications Count-------------New" . print_r($notifications,1) . "\n");
            foreach ($notifications as $notification) {
                //echo $notification['_id'];
                 echo("3. Processing Notification-------------New" . $notification['_id'] . "---" . $notification['ActivityOn'] . "\n");
                $recipient_list = array();
                $datetime = $notification['NotificationDate']->toDateTime();
                $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                $Date = $datetime->format('M-d-Y H:i:s');
                $selectfields = ['Title', 'TicketId', 'Fields.planlevel'];
                $ticket_data = TicketCollection::getTicketDetails($notification['TicketId'], $projectId, $selectfields);
                $ticket_msg = 'to' . ' ' . '#' . $notification['TicketId'] . ' ' . $ticket_data['Title'];
                $planLevel = $ticket_data["Fields"]["planlevel"]["value"];
                $from_user = TinyUserCollection::getMiniUserDetails($notification['ActivityFrom']);


                /*                 * ************* Left Panel Field Values newly assigned ******************** */
                $activityOn = $notification['ActivityOn'];
                $storyField = StoryFields::getFieldDetails($activityOn, "Field_Name");
//                                  error_log("activtiy form---------------".$activityOn);
                $activityOnFieldType = $storyField["Type"];
                $ticketId = $notification['TicketId'];
                $title = $ticket_data['Title'];
                $fromUser = $from_user['UserName'];
                $mailingName = $from_user['FirstName'] . " " . $from_user['LastName'];
                $projectDetails = Projects::getProjectMiniDetails($projectId);
                $projectName = $projectDetails["ProjectName"];
                $link = Yii::$app->params['AppURL'] . "/project/$projectName/" . $ticketId . "/details";
                $redirectToHome = Yii::$app->params['AppURL'] . "/home";
                foreach($notification['NotifiedCollaborators'] as $notifyuser){
                   $notification['NotifiedUser'] = $notifyuser['CollaboratorId'];
                //  echo ("3. sending email .11111.." . $notification['ActivityOn'] . "SSSSSSS");
                if ($activityOnFieldType == 6) {//newly assigned  
                    //$action_user=Collaborators::getCollaboratorById($notification['ActivityOn']);
                    if ($notification['NotifiedUser'] == $notification['NewValue']) {

                        //for logged in user
                        //Eg : moin.hussain assigned you to ticket #33
                        $to = "you";
                        $action_user = Collaborators::getCollaboratorById($notification['NewValue']);
                    } else {
                        $action_user = Collaborators::getCollaboratorById($notification['NewValue']);
                        //Eg : moin.hussain assigned sateesh.mandru to Ticket #33
                        //$msg=$from_user['UserName'] .' '. Yii::$app->params['assignedTo'] .' '.$action_user['UserName'].' '.$ticket_msg;
                        $to = $action_user['UserName'];
                    }
                    $assigned_message = $action_user['UserName'];
                    $old_user = Collaborators::getCollaboratorById($notification['OldValue']);
                    if ($old_user != '') {
                        $assigned_message = $old_user['UserName'] . ' => ' . $action_user['UserName'];
                    }
                    $preposition = "to";
                    //  $message=array('from'=>$from_user['UserName'],'object'=>"user",'type'=> Yii::$app->params['assignedTo'],'to'=>$to,'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'id'=>$notification['_id'],'PlanLevel'=>$planLevel,'Profile'=>$from_user['ProfilePicture'],"OtherMessage"=>Yii::$app->params['stakeholder'],"Preposition"=>$preposition);
                    $fieldName = "";
                    if ($activityOn != "assignedto") {
                        $fieldName = $storyField["Title"];
                    }
                    $fieldName = $fieldName == "" ? "" : "as a " . $fieldName;

//                                $text_message = <<<EOD
//{$fromUser} has assigned {$to} {$fieldName} to <a href={$link}>#{$ticketId} {$title} </a>
//EOD;

                    $text_message = <<<EOD
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">Activity by {$fromUser}:</td></tr>
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">{$storyField['Title']}: {$assigned_message} </td></tr>
EOD;

                    array_push($recipient_list, $notification['NotifiedUser']);
                }


                /* ------To display the total worked hours in mail-----start---------- */ else if ($notification['ActivityOn'] == 'TotalTimeLog') {
                    $notification['OldValue'] = number_format((float) $notification['OldValue'], 1, '.', '');
                    $notification['NewValue'] = number_format((float) $notification['NewValue'], 1, '.', '');
                    $workedHours = $notification['OldValue'] . " => " . $notification['NewValue'];
                    $text_message = <<<EOD
             <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">Activity by {$fromUser}:</td></tr>
             <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">Total invested hours: {$workedHours}</td></tr>
EOD;
                    array_push($recipient_list, $notification['NotifiedUser']);
                }
                /* ------To display the total worked hours in mail-----end---------- */


                /* Left Panel newly assigned Field Values End */

                /* ----------------Title and Description changes----start------ */
//                else if ($activityOn == "Description" || $activityOn == "Title") {
//                    $message = array('IsSeen' => $notification['Status'], 'from' => $from_user['UserName'], 'object' => "description", 'type' => Yii::$app->params[$notification['Notification_Type']], 'id' => $notification['_id'], 'ActivityOn' => $notification['ActivityOn'], 'Title' => $ticket_data['Title'], 'TicketId' => $notification['TicketId'], 'date' => $Date, 'PlanLevel' => $planLevel, 'Profile' => $from_user['ProfilePicture'], 'status' => $notification['Notification_Type'], 'OldValue' => $notification['OldValue'], "NewValue" => $notification['NewValue']);
////                       
                else if ($activityOn == "Title") {
                    $description_message = $notification['OldValue'] . " => " . $notification['NewValue'];
                    $text_message = <<<EOD
             <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">Activity by {$fromUser}:</td></tr>
             <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">Title: {$description_message} </td></tr>
EOD;
                    array_push($recipient_list, $notification['NotifiedUser']);
                } else if ($activityOn == "Description") {
                    $text_message = <<<EOD
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">A story description has been edited by {$fromUser}:</td></tr>
        <tr><td height="10">&nbsp;</td></tr>
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;"><b>Description before editing was:</b></td></tr>
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">{$notification['OldValue']}</td></tr>
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;"><b>Description after editing was:</b></td></tr>
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">{$notification['NewValue']}</td></tr>
EOD;
                    array_push($recipient_list, $notification['NotifiedUser']);
                }
                /* ----------------Title and Description changes----end------ */

                /*                 * ******* Followers Messages **************** */ else if ($notification['ActivityOn'] == 'FollowObj') {
                    //  error_log("added");
                    if ($notification['NotifiedUser'] == $notification['NewValue']) { //if logged in user has been added
                        //Eg : moin.hussain added you as a follower to ticket #33
                        $activityOn = 'you';
                        $action_user = Collaborators::getCollaboratorById($notification['NewValue']);
                        $activityOn = $action_user['UserName'];
                    } else {
                        //Eg : moin.hussain added sateesh.mandru as a follower to Ticket #33
                        $action_user = Collaborators::getCollaboratorById($notification['NewValue']);
                        $activityOn = $action_user['UserName'];
                    }
                    $preposition = $notification['Notification_Type'] == "added" ? "to" : "from";
                    // $message=array('from'=>$from_user['UserName'],'object'=>"follower",'type'=> Yii::$app->params[$notification['Notification_Type']],'to'=>$activityOn,'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'id'=>$notification['_id'],'PlanLevel'=>$planLevel,'Profile'=>$from_user['ProfilePicture'],"OtherMessage"=>Yii::$app->params['follower'],"Preposition"=>$preposition);
                    $follower_message = "Follower:" . " " . $notification['Notification_Type'] . " " . $activityOn;

//                                 $text_message = <<<EOD
//{$fromUser}  {$message} {$activityOn} as follower {$preposition} <a href={$link}>#{$ticketId} {$title} </a>
//EOD;

                    $text_message = <<<EOD
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">Activity by {$fromUser}:</td></tr>
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;"> {$follower_message} </td></tr>
EOD;

                    array_push($recipient_list, $notification['NotifiedUser']);
                }


                /*                 * ***** Followers Message End ********* */


                /*                 * *** Any changes in Editor ********** */
                $commentAllowedArray = ["comment", "reply", "edit", "delete"];
                if ($notification['ActivityOn'] == "comment" && (in_array($notification['Notification_Type'], $commentAllowedArray))) {
                    $datetime = $notification['NotificationDate']->toDateTime();
                    $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                    $Date = $datetime->format('M-d-Y H:i:s');
                    $collaborator = new Collaborators();
                    $selectfields = ['Title', 'TicketId', 'Fields.planlevel'];
                    $link .= "?Slug=" . $notification['CommentSlug'];
                    if ($notification['Notification_Type'] == "comment") {
                        //  error_log("comment-----------------------22222");
                        $preposition = "on";
                        $object = "comment";
                        $type = Yii::$app->params['comment'];
                        $text_message = <<<EOD
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">Activity by {$fromUser}:</td></tr>
                <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">{$fromUser} added new comment</td></tr>
   <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">{$notification['NewValue']}</td></tr>
EOD;
                    } else if ($notification['Notification_Type'] == "reply") {
                        //  error_log("replyyyyyyyyyyyyy-----------------------111111111111s");
                        $preposition = "";
                        $object = "reply";
                        $type = Yii::$app->params['reply'];
//            $text_message = <<<EOD
//<a href={$link}>#{$ticketId} {$title} </a> <br/> replied by {$fromUser}
//EOD;
                        $text_message = <<<EOD
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">Activity by {$fromUser}:</td></tr>
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">{$fromUser} replied to your comment
        {$notification['NewValue']}</td></tr>
EOD;
                    } else if ($notification['Notification_Type'] == "edit") {
                        //  error_log("replyyyyyyyyyyyyy-----------------------111111111111s");
                        $preposition = "";
                        $object = "edit";
                        $type = Yii::$app->params['reply'];
//            $text_message = <<<EOD
//<a href={$link}>#{$ticketId} {$title} </a> <br/> comment edited by {$fromUser}
//EOD;
                        $text_message = <<<EOD
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">A story comment has been edited by {$fromUser}:</td></tr>
        <tr><td height="10">&nbsp;</td></tr>
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;"><b>Comment before editing was:</b></td></tr>
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">{$notification['OldValue']}</td></tr>
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;"><b>Comment after editing was:</b></td></tr>
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">{$notification['NewValue']}</td></tr>
EOD;
                    } else if ($notification['Notification_Type'] == "delete") {
                        //  error_log("replyyyyyyyyyyyyy-----------------------111111111111s");
                        $preposition = "";
                        $object = "delete";
                        $type = Yii::$app->params['delete'];
//            $text_message = <<<EOD
//<a href={$link}>#{$ticketId} {$title} </a> <br/> comment deleted by {$fromUser}
//EOD;
                        $text_message = <<<EOD
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">Activity by {$fromUser}:</td></tr>
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">{$fromUser} deleted comment</td></tr>
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">{$notification['NewValue']}</td></tr>
EOD;
                    }

                    array_push($recipient_list, $notification['NotifiedUser']);
                } else if ($notification['Notification_Type'] == "mention") {
                    $datetime = $notification['NotificationDate']->toDateTime();
                    $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                    $Date = $datetime->format('M-d-Y H:i:s');
                    $collaborator = new Collaborators();
                    $selectfields = ['Title', 'TicketId', 'Fields.planlevel'];
//            $text_message = <<<EOD
//<a href={$link}>#{$ticketId} {$title} </a> <br/> mentiond you by {$fromUser}
//EOD;
                    $text_message = <<<EOD
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">Activity by {$fromUser}:</td></tr>
                <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">{$fromUser} mentioned you</td></tr>
   <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">{$notification['NewValue']}</td></tr>
EOD;

                    array_push($recipient_list, $notification['NotifiedUser']);
                }


                //if($notification['Notification_Type']=='duedate' || $notification['Notification_Type']=='dod' || $notification['Notification_Type']=='estimatedpoints')
                // {
                // $storyField = StoryFields::getFieldDetails($notification['ActivityOn'],"Field_Name");
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
                            $dueDate = $oldValue . " => " . $newValue;
                        }
                        $text_message = <<<EOD
             <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">Activity by {$fromUser}:</td></tr>
             <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">{$storyField['Title']}: {$dueDate} </td></tr>
EOD;
                        array_push($recipient_list, $notification['NotifiedUser']);
                    } else if ($storyField['Type'] != 6) {
                        if ($notification['ActivityOn'] != "workflow" && $notification['ActivityOn'] != "tickettype" && $notification['ActivityOn'] != "dod") {
                            $notification['OldValue'] = CommonUtility::refineActivityData($notification['OldValue'], 10);
                            $notification['NewValue'] = CommonUtility::refineActivityData($notification['NewValue'], 10);
                        }
                        $ticketState = $notification['NewValue'];
                        if ($notification['OldValue'] != '') {
                            $ticketState = $notification['OldValue'] . " => " . $notification['NewValue'];
                        }
                        $text_message = <<<EOD
            <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">Activity by {$fromUser}:</td></tr>
            <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">{$storyField['Title']}: {$ticketState} </td></tr>
EOD;
                        array_push($recipient_list, $notification['NotifiedUser']);
                    }
                }
            }
                echo ("3. sending email ...");
                /* Left Panel Changed Field Values Start */
                foreach ($recipient_list as &$value) {
                    $collaboratorData = TinyUserCollection::getMiniUserDetails($value);
                    $value = $collaboratorData['Email'];
                    $notified_user = $collaboratorData['UserName'];
                    $display_name = $collaboratorData['FirstName'] . " " . $collaboratorData['LastName'];
                      echo("EMAIL________________+++++++++++_____________".print_r($display_name,1));
                }
                echo "---dsdsadasda-s-sdasd-as-dd--" . print_r($recipient_list, 1);
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
                $user_message = "<tr><td style='border-bottom:1px solid #f0f0f0; font-family:Arial; font-size:14px;line-height:24px;color:#333333;  padding-bottom:10px;' width='570'>Dear " . $display_name . ",<br/><span style='font-family:Arial; font-size:14px;line-height:24px;color:#333333;'>you have a new activity alert.</span></td></tr>";
                $link_message = "<a  style='font-family:Arial;font-size:16px;line-height:40px;color:#0199e0;' href={$link}>#{$ticketId}: {$title} </a>";
                $ticket_message = "<tr><td style='font-family:'Arial;font-size:18px;color:#0199e0; line-height:30px; font-weight:bold; padding-top:10px; padding-bottom:10px;'>" . $link_message . "</td></tr>";
                $view_ticket_message = "<tr><td><a style='font-family:Arial;font-size:12px;line-height:40px;color:#0199e0; text-decoration:none;' href={$link}>View Activity</a></td></tr>
                                    </table></td><td width='15'>&nbsp;</td></tr></table> </td></tr>";
                $text_message = $project_logo . $user_message . $ticket_message . $text_message . $view_ticket_message;
                echo ("4. 11111111111 sending email comppleted ..hhhhhhhhh." . print_r($recipient_list, 1));
                CommonUtility::sendEmail($mailingName, $recipient_list, $text_message, $subject);
                echo ("4. sending email comppleted ...");
            }
        } catch (Exception $ex) {
            Yii::log("NotificationTrait:sendEmailNotificationFromBackground::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

    /**
     * @author Lakshmi
     * @param type $notificationIds
     * @param type $projectId
     * @param type $bulkUpdate=''
     */
    public function sendBulkEmailNotification($notificationIds, $projectId, $bulkUpdate = '') {
        try {
            //echo "sendBulkEmailNotification---------------".count($notificationIds);
            $message = array();
            $result_msg = array();
            $project_logo = '';
            $user_message = '';
            $ticket_message = '';
            $view_ticket_message = '';
            $msg = <<<EOD
EOD;
            $recipient_list = array();
           // echo ("============notifications ids--------11111111------".print_r($notificationIds,1));
            $notifications = NotificationCollection::getNotificationDetails($notificationIds);
            echo ("============notifications ids--------22222222------".print_r($notifications,1));
            $uniqueNotifications = CommonUtility::getUniqueArrayObjects($notifications);
            foreach ($uniqueNotifications as $notification) {
                // echo(count($notificationIds)."-------uniaqu----------".count($uniqueNotifications));
                $datetime = $notification['NotificationDate']->toDateTime();
                $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                $Date = $datetime->format('M-d-Y H:i:s');
                $selectfields = ['Title', 'TicketId', 'Fields.planlevel'];
                $activityOn = $notification['ActivityOn'];
                $ticket_data = TicketCollection::getTicketDetails($notification['TicketId'], $projectId, $selectfields);
                $ticket_msg = 'to' . ' ' . '#' . $notification['TicketId'] . ' ' . $ticket_data['Title'];
                $planLevel = $ticket_data["Fields"]["planlevel"]["value"];
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
                    $description_message = $notification['OldValue'] . " => " . $notification['NewValue'];
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
                if ($activityOn == "assignedto" || $activityOn == "stakeholder") {
                    $action_user = Collaborators::getCollaboratorById($notification['NewValue']);
                    $assigned_message = $action_user['UserName'];
                    $old_user = Collaborators::getCollaboratorById($notification['OldValue']);
                    if ($old_user != '') {
                        $assigned_message = $old_user['UserName'] . ' => ' . $action_user['UserName'];
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
                            $dueDate = $oldValue . " => " . $newValue;
                        }
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
                            $ticketState = $notification['OldValue'] . " => " . $notification['NewValue'];
                        }
                        $text_message = <<<EOD
            <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;padding-top:10px;">{$storyField['Title']}: {$ticketState} </td></tr>
EOD;
                        $msg = $msg . $text_message;
                    }
                }
            }
            $notificationUsers = array();
           // $notificationUsers = array_column($notifications, 'NotifiedCollaborators');
            foreach ($notifications[0]['NotifiedCollaborators'] as &$value) {
                $resUser=array();
                $collaborator = TinyUserCollection::getMiniUserDetails($value['CollaboratorId']);
                array_push($resUser,$collaborator['Email']);
                $value = $collaborator['Email'];
                $notified_user = $collaborator['UserName'];
                $display_name = $collaborator['FirstName'] . " " . $collaborator['LastName'];
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
            $user_message = "<tr><td style='border-bottom:1px solid #f0f0f0; font-family:Arial; font-size:14px;line-height:24px;color:#333333;  padding-bottom:10px;' width='570'>Dear " . $display_name . ",<br/><span style='font-family:Arial; font-size:14px;line-height:24px;color:#333333;'>you have a new activity alert.</span></td></tr>";
            $link_message = "<a  style='font-family:Arial;font-size:16px;line-height:30px;color:#0199e0;' href={$link}>#{$ticketId}: {$title} </a>";
            $ticket_message = "<tr><td style='font-family:'Arial;font-size:18px;color:#0199e0; line-height:30px; font-weight:bold; padding-top:10px; padding-bottom:10px;'>" . $link_message . "</td></tr>";
            $view_ticket_message = "<tr><td><a style='font-family:Arial;font-size:12px;line-height:40px;color:#0199e0; text-decoration:none;' href={$link}>View Activity</a></td></tr>
                                    </table></td><td width='15'>&nbsp;</td></tr></table> </td></tr>";
            $activity = "<tr><td style='border-bottom:1px solid #f0f0f0; font-family:Arial; font-size:14px;line-height:24px;color:#333333; padding-bottom:10px;padding-top:10px;' width='570'>Activity by {$fromUser}:";
            $text_message = $project_logo . $user_message . $ticket_message . $activity . $msg . $view_ticket_message;
            CommonUtility::sendEmail($mailingName, $resUser, $text_message, $subject);
        }
            } catch (Exception $ex) {
            Yii::log("NotificationTrait:sendEmailNotificationFromBackground::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

}
