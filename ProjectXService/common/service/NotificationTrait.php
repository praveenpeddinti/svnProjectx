<?php
namespace common\service;
use common\models\mongo\{TicketCollection,TinyUserCollection,NotificationCollection};
use common\components\{CommonUtility};
use common\models\mysql\{WorkFlowFields,StoryFields,Priority,PlanLevel,TicketType,Bucket,Collaborators,TaskTypes,Filters,Projects};
use yii;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

 trait NotificationTrait {
  public function sampleMethod(){
      echo "sampel emthod";
  }  
  
   /**
     * @author Ryan
     * @uses Gets the field change value name for notification purpose
     * @params  $notifyType,$Value
     * @return type string
     */
    public static function getFieldChangeValue($notifyType,$Value)
    {
        try
        {
            if($notifyType=='priority')
            {
               $priorityObj = new Priority();
               $priorityDetails = $priorityObj->getPriorityDetails($Value);
               $priorityValue=$priorityDetails['Name'];
               return $priorityValue;
            }
            if($notifyType=='bucket')
            {
                $bucketDetails=Bucket::getBucketName($Value,1);
                $bucketValue=$bucketDetails['Name'];
                return $bucketValue;
            }
            if($notifyType=='workflow')
            {
                $workflowDetails=WorkFlowFields::getWorkFlowDetails($Value);
                $workflowValue=$workflowDetails['Name'];
                return $workflowValue;
            }
            if($notifyType=='tickettype')
            {
                $tickettypeDetails=TicketType::getTicketType($Value);
                $tickettypeValue=$tickettypeDetails['Name'];
                return $tickettypeValue;
            }
            
        }catch(Exception $ex)
        {
            Yii::log("NotificationTrait:getFieldChangeValue::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
        
    }
    
    /**
     * @author Ryan Marshal
     * @param type $property
     * @return type 
     * @description  Used for saving the notifications in submit Description and comments  
     */
    public static function saveNotificationsToMentionOnly($ticket_data,$userslist,$notifyType=null,$slug=null)
    {
        try
        {
            error_log("----saveNotificationsToMentionOnly---");
            $from=$ticket_data->userInfo->username;
             $loggedinUser=$ticket_data->userInfo->Id;
            $ticketId=(int)$ticket_data->ticketId;
            $projectId=(int)$ticket_data->projectId;
            $currentDate = new \MongoDB\BSON\UTCDateTime(time() * 1000);
           $ticket_data = TicketCollection::getTicketDetails($ticketId,$projectId);
            if(!empty($userslist))
            {
                foreach($userslist as $user)
                {
                    error_log("====Notification with mention==".$user);
                    $user=Collaborators::getCollaboratorId($user);
                    error_log("==mentioned user==".$user['Id']);
                    $tic = new NotificationCollection();
                    $tic->TicketId =$ticketId;
                    $tic->ProjectId =$projectId;
                    $tic->NotifiedUser=(int)$user['Id'];
                    $tic->ActivityFrom=(int)$loggedinUser;
                    $tic->NotificationDate=$currentDate;
//                    if($notifyType=='mention')
//                    {
//                        $tic->Notification_Type='mention';
//                    }
//                    if($notifyType=='comment')
//                    {
//                        $tic->Notification_Type='comment';
//                    }
                     $tic->Notification_Type='mention';
                     $tic->ActivityOn=$user['Id'];
                     $tic->Status=0;
                     $tic->CommentSlug=$slug;
                   $result = $tic->save();
                   if($result){
                     $notificationId = $tic->_id;
                     self::sendEmailNotification(array($notificationId),$projectId);
                  }   
                }
            }
        }catch(Exception $ex)
        {
            Yii::log("NotificationTrait:saveNotificationsWithMention::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
  /**
   * @author Ryan
   * @param type $commentData
   * @param type $notify_type //comment,reply
   * @param type $slug
   */
    public static function saveNotificationsForComment($commentData,$userslist,$notify_type,$slug)
    {
        try
        {
            error_log("saveNotificationsForComment---".$notify_type);
            $commentOwner=$commentData->Comment->OriginalCommentorId;
            $loggedinUser=$commentData->userInfo->Id;
            $ticketId=(int)$commentData->ticketId;
            $projectId=(int)$commentData->projectId;
            $data = TicketCollection::getTicketDetails($ticketId,$projectId);
            $followers=$data['Followers'];
            $followers = CommonUtility::filterFollowers($followers);
            $currentDate = new \MongoDB\BSON\UTCDateTime(time() * 1000);
            //For Reply....added by Ryan
             $mentionUserIdlist= array();
                 if(!empty($userslist))
            {
                foreach($userslist as $user)
                {
                    $user=Collaborators::getCollaboratorId($user);
                    array_push($mentionUserIdlist,$user['Id']);
                    $tic = new NotificationCollection();
                    $tic->TicketId =$ticketId;
                    $tic->ProjectId =$projectId;
                    $tic->NotifiedUser=(int)$user['Id'];
                    $tic->ActivityFrom=(int)$loggedinUser;
                    $tic->NotificationDate=$currentDate;
                    $tic->OldValue="";//added for consistency
                    $tic->NewValue="";//added for consistency
                    $tic->Notification_Type='mention';
                    //$tic->ActivityOn=$user['Id']; //previous use case
                    //eg: moin.hussain mentioned you
                    $tic->ActivityOn="comment";
                    $tic->Status=0;
                    $tic->CommentSlug=$slug;
                    $result = $tic->save();
                   if($result){
                     $notificationId = $tic->_id;
                     self::sendEmailNotification(array($notificationId),$projectId);
                  }    
                                 
                    
                }
            }
            $notificationIdsArray = array();
            foreach($followers as $follower)
                {             
                    if($follower['FollowerId']!=$loggedinUser && !in_array($follower['FollowerId'],$mentionUserIdlist))
                    {
                            $tic = new NotificationCollection();
                            $tic->NotifiedUser=(int)$follower['FollowerId'];
                            $tic->TicketId =$ticketId;
                            $tic->ProjectId =$projectId;
                            $tic->ActivityFrom=(int)$loggedinUser;
                            $tic->NotificationDate=$currentDate;
                            $tic->OldValue=""; //added for consistency
                            $tic->NewValue=""; //added for consistency
                            $tic->CommentSlug=$slug;
                            //eg : moin.hussain commented on or replied on the ticket.
                            if($commentOwner == $follower['FollowerId'])  //added by Ryan for Reply
                            {
                                error_log("==reply==");
                                $tic->Notification_Type="reply";
                                $tic->ActivityOn="comment"; //added for consistency
                            }else{
                                error_log("==comment==");
                                 $tic->Notification_Type=$notify_type;
                                 $tic->ActivityOn="comment"; //added for consistency
                            }
                            $tic->Status=0;
                            $result = $tic->save();
                            if($result){
                              $notificationId = $tic->_id;
                              array_push($notificationIdsArray, $notificationId);
                           }    
                         
                    }

                }
                  self::sendEmailNotification($notificationIdsArray,$projectId);
        }catch(Exception $ex)
        {
            Yii::log("NotificationTrait:saveNotificationsForComment::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
     /**
     * @author Ryan Marshal
     * @param type $notification_data
     * @return type
     * @description  Used for saving the notifications for assignedTo,Add/Remove Followers,stakeholder and left panel property changes  
     */
    public static function saveNotifications($notification_data,$notifyType,$activityOn,$fieldType="",$slug='',$taskId=0)
    {
        error_log("in save notifications--".$notifyType."----------".$activityOn."--------".$fieldType."---");
        try{
            
            $oldValue='';
            $newValue='';
            $oldCollaborator='';
            $newCollaborator='';
                //For Story Detail Page Use Case.....
           $ticketId =  isset($notification_data->ticketId) ? (int)$notification_data->ticketId : (int)$notification_data->data->ticketId;
           // $ticketId=$notification_data->TicketId;
            $projectId=(int)$notification_data->projectId;
           // $from=$notification_data->userInfo->username;
            $loggedInUser=$notification_data->userInfo->Id;         
            $notify_type=$notifyType;//this will be changed to ActivityOn in the below code....
            $currentDate = new \MongoDB\BSON\UTCDateTime(time() * 1000);
            $ticketDetails = TicketCollection::getTicketDetails($ticketId,$projectId);
            $followers=$ticketDetails['Followers']; 
            
            $followers = CommonUtility::filterFollowers($followers);
            if($notifyType == "Title" || $notifyType == "Description" || $notifyType == "TotalTimeLog")
            {
                $oldValue = $ticketDetails[$notifyType]; 
                error_log("Old_value___".$oldValue."ACTivityON___".$activityOn);
                
                  if($oldValue == $activityOn){
                   return;
                 }
                 if($oldValue!=''){
                            $notification_Type='changed';
                            $newValue=$activityOn;
                            $activityOn='changed';
                        }
                        else{
                            $notification_Type='set';
                            $newValue=$activityOn; //added for fixing time log issue
                            $activityOn='set';
                        }
            }
            else if(isset($ticketDetails["Fields"][$notifyType]))
            {
               $oldValue = $ticketDetails["Fields"][$notifyType]["value"];
               error_log("==in else old value==".$oldValue);
            }
            
//                    if(!($notify_type=='added' || $notify_type=='removed'))
//                    { 
                     if($fieldType==1 ||$fieldType==4 || $fieldType==5 || $fieldType==8)
                    {
                        //for due date,dod and estimated points
                        $oldValue=$oldValue;
                        $newValue=$activityOn;
                        if($oldValue == $newValue){
                            return;
                        }
                        if($oldValue!='') //if changed
                            {
                                $activityOn='changed';
                            }
                            else //if set new value
                            {
                                $activityOn='set';
                                $oldValue='';
                            }
                          if($fieldType == 4){
                              error_log("in field tyupe 4444444444444");
                                       $validDate = CommonUtility::validateDate($newValue);
                                      if($validDate){
                                     $newValue = new \MongoDB\BSON\UTCDateTime(strtotime($validDate) * 1000);
                                       }
                                        $validDate = CommonUtility::validateDate($oldValue);
                                      if($validDate){
                                      $oldValue = new \MongoDB\BSON\UTCDateTime(strtotime($validDate) * 1000);
                                       }
                                         error_log($newValue."----in field tyupe 4444444444444----".$oldValue);
                             }    
                    }
                    else if($fieldType==6)
                    {
                            error_log("==in assigned to==");
                            $oldCollaborator=$oldValue;
                            $newCollaborator=$activityOn; //this is a field value....
                            if($oldCollaborator!='') //if changed
                            {
                                $activityOn='changed';
                            }
                            else //if set new value
                            {
                                $activityOn='set';
                                $oldValue='';
                            }
                            error_log("==activity on==".$activityOn);
                            $tic = new NotificationCollection();
                            $tic->CommentSlug=$slug;
                            $tic->TicketId =$ticketId;
                            $tic->ProjectId =$projectId;
                            $tic->NotifiedUser=(int)$newCollaborator; //new use case "setting newly assigned mem
                            $tic->ActivityFrom=(int)$loggedInUser;
                            $tic->Notification_Type=$activityOn;
                            $tic->ActivityOn= $notify_type;
                            $tic->NotificationDate=$currentDate;
                            $tic->OldValue=$oldCollaborator;
                            $tic->NewValue=$newCollaborator;
                            $tic->Status=0;
                            
                            //$tic->Notification_Type=$notify_type; //previous use case
                            //new use case 'set' or 'changed'
                             error_log("==In saving the assigned to==");
                            //$tic->ActivityOn= $activityOn; //previous use case
                             //new use case eg: assignedTo,stakeholder,priority,etc... 
                              $result =  $tic->save();
                            if($result){
                                $notificationId = $tic->_id;
                                error_log("before sendign assing to notircioant--");
                                 self::sendEmailNotification(array($notificationId),$projectId);
                       } 
   
                               //self::sendMail($ticketDetails,$loggedInUser, $newCollaborator,$notify_type);    
                            
                            
                            
                        //}
                    }
                     else if($fieldType== "FollowObj")
                    {
                         if($loggedInUser != $activityOn){
                        
                             $notification_Type =  ($notifyType=='add') ? 'added' : 'removed';
                        
                            $tic = new NotificationCollection();
                            $tic->CommentSlug=$slug;
                            $tic->TicketId =$ticketId;
                            $tic->ProjectId =$projectId;
                            $tic->NotifiedUser=(int)$activityOn; //new use case "setting newly assigned mem
                            $tic->ActivityFrom=(int)$loggedInUser;
                            $tic->Notification_Type=$notification_Type;
                            $tic->ActivityOn= $fieldType;
                            $tic->NotificationDate=$currentDate;
                            $tic->OldValue="";
                            $tic->NewValue=$activityOn;
                            $tic->Status=0;
                            
                            //$tic->Notification_Type=$notify_type; //previous use case
                            //new use case 'set' or 'changed'
                             error_log("==In saving the assigned to==");
                            //$tic->ActivityOn= $activityOn; //previous use case
                             //new use case eg: assignedTo,stakeholder,priority,etc... 
                           $result = $tic->save();
                             if($result){
                                $notificationId = $tic->_id;
                                error_log("before sendign assing to notircioant--");
                                 self::sendEmailNotification(array($notificationId),$projectId);
                       } 
                         }
                    } 
                    else if($fieldType!= "Description" && $fieldType!= "Title" && $fieldType!= "TotalTimeLog"){ //This is for left hand property changes
                        $oldFieldId=$oldValue;
                        $newFieldId=$activityOn;
                        error_log("==Old Value==".$oldFieldId);
                        error_log("==New Value==".$newFieldId);
                        
                        $newValue= self::getFieldChangeValue($notifyType,$newFieldId);
                        error_log("==new value name==".$newValue);
                        if($oldValue!='') //if changed
                        {
                            $activityOn='changed';
                            $oldValue=self::getFieldChangeValue($notifyType,$oldFieldId);
                           
                        }
                        else //if set new value
                        {
                            $activityOn='set';
                            $oldValue='';
                        }
                    }
                    
            

            /* notification for all followers and the stakeholders */
            $notificationIds = array();
                    
            foreach($followers as $follower)
            {
                error_log($loggedInUser."--foloowerid-----------------------".$follower['FollowerId']);
               error_log("===Notify Type".$notify_type);
              if($follower['FollowerId'] == $loggedInUser){
                  error_log("continure----------------");
                  continue;
              }
              error_log("procssin----------------");
                    $tic = new NotificationCollection();
                    $tic->CommentSlug=$slug;
                    $tic->NotifiedUser=(int)$follower['FollowerId'];
                    $tic->TargetTicketId =$taskId; //added for child task and relate task
                    $tic->TicketId =$ticketId;
                    $tic->ProjectId =$projectId;
                    $tic->ActivityOn=$notify_type; // new use case "ActivityOn" will be Field Name
                    $tic->ActivityFrom=(int)$loggedInUser;
                    $tic->NotificationDate=$currentDate;
                    
                         if($fieldType == "Description" || $fieldType == "Title"){
                             error_log("in descrioton--------");
                            $tic->Notification_Type=$notification_Type;
                            $tic->ActivityOn= $fieldType;
                            $tic->OldValue=$oldValue;
                            $tic->NewValue=$newValue;
                            $tic->Status=0;
                            $result =  $tic->save();
                            if($result){
                              $notificationId = $tic->_id;
                             array_push($notificationIds,$notificationId);
                              }
            }
                    
                    
                   else if($fieldType== "FollowObj"){
                        
                        if($follower['FollowerId'] != $activityOn){
                         $tic->ActivityOn=$fieldType;
                        if($loggedInUser == $activityOn){
                             $notification_Type = ($notifyType=='add') ? 'followed' : 'unfollowed';
                             
                         }else{
                             $notification_Type =  ($notifyType=='add') ? 'added' : 'removed';
                         }
                        $tic->Notification_Type=$notification_Type;
                        $tic->OldValue="";
                        $tic->NewValue=$activityOn;
                        $tic->Status=0;
                        $result =  $tic->save();
//                             if($result){
//                              $notificationId = $tic->_id;
//                              array_push($notificationIds,$notificationId);
//                              }
                        }
                    }
                    else if($fieldType == 6)
                    {
                        
                        if($follower['FollowerId']!=$newCollaborator) //previous case it was $activityOn which was collaborator_name
                        {
                            $tic->Notification_Type=$activityOn;
                            $tic->Status=0;
                            $tic->OldValue=$oldCollaborator;
                            $tic->NewValue=$newCollaborator;
                            $result =  $tic->save();
                             if($result){
                              $notificationId = $tic->_id;
                              array_push($notificationIds,$notificationId);
                              }
                        }
                    }
                    else
                    {                
                          
                        error_log("Activity____On--".$activityOn);
                        $tic->Notification_Type=$activityOn;
                        $tic->Status=0;
                        $oldValue = ($fieldType == 4) ? $oldValue : (string)$oldValue;
                        $tic->OldValue = $oldValue; 
                       
                      
                        $tic->NewValue=$newValue;
                        $tic->save(); //here not sending emails for left hand side propert change excpet Assinged to , stake holder
                    }
                    
                }
                     
             error_log("before sendign assing to notircioant-****-".print_r($notificationIds,1));
             self::sendEmailNotification($notificationIds,$projectId);
                     
            
        } catch (Exception $ex) {
            Yii::log("NotificationTrait:saveNotifications::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
    
    
    
     public static function getNotifications($user,$projectId,$offset=0,$limit=5,$viewAll=0)
    {
        error_log("==in get notifications---".$user."---".$projectId."---".$offset."---".$limit);
        $msg='';
        $message=array();
        $result_msg=array();
       // $action_user=Collaborators::getCollaboratorById($user);
        try{
            $projectObj = new Projects();
            $notifications = NotificationCollection::getNotifications($user, $projectId,$offset,$limit,$viewAll);
            //constucting the notifications for the user
            error_log("not cont--------------------".count($notifications));
            foreach($notifications as $notification)
            {  
                 error_log($notification['_id']."==Notification Type==".$notification['Notification_Type']);
                $datetime = $notification['NotificationDate']->toDateTime();
                $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                $Date = $datetime->format('M-d-Y H:i:s');
                $selectfields=['Title','TicketId','Fields.planlevel'];
                $ticket_data= TicketCollection::getTicketDetails($notification['TicketId'],$projectId,$selectfields);
                $ticket_msg='to'. ' '.'#'. $notification['TicketId'] .' ' .$ticket_data['Title'];
                $planLevel = $ticket_data["Fields"]["planlevel"]["value"];
                error_log("activtiy form---------------".$notification['ActivityFrom']);
                $from_user= TinyUserCollection::getMiniUserDetails($notification['ActivityFrom']);
                
               $projectDetails = $projectObj->getProjectMiniDetails($notification["ProjectId"]);
                  
                   /*************** Left Panel Field Values newly assigned *********************/
                 $activityOn = $notification['ActivityOn'];
                 $activityOnFieldType = "";
                 $storyField = "";
                 if($activityOn !="Title" && $activityOn != "Description" && $activityOn != "TotalTimeLog"){
                     $storyField = StoryFields::getFieldDetails($activityOn,"Field_Name");;
                    $activityOnFieldType = $storyField["Type"];  
                 }
                  
                 if($activityOnFieldType== 6) //newly assigned 
                    {
                        //$action_user=Collaborators::getCollaboratorById($notification['ActivityOn']);
                       
                        if($notification['NotifiedUser']==$notification['NewValue'])
                        {
                           
                            //for logged in user
                            //Eg : moin.hussain assigned you to ticket #33 
                             $to =  "you";
                        }
                        else
                        {
                           $action_user=Collaborators::getCollaboratorById($notification['NewValue']);
                                //Eg : moin.hussain assigned sateesh.mandru to Ticket #33
                                //$msg=$from_user['UserName'] .' '. Yii::$app->params['assignedTo'] .' '.$action_user['UserName'].' '.$ticket_msg;
                            $to =  $action_user['UserName']; 
                           
                        }
                          $preposition =  "to";
                          $message=array('Slug'=>$notification['CommentSlug'],'Project'=>$projectDetails,'IsSeen'=>$notification['Status'],'from'=>$from_user['UserName'],'object'=>"user",'type'=> Yii::$app->params['assigned'],'to'=>$to,'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'id'=>$notification['_id'],'PlanLevel'=>$planLevel,'Profile'=>$from_user['ProfilePicture'],"OtherMessage"=>Yii::$app->params[$activityOn],"Preposition"=>$preposition);
                                array_push($result_msg,$message); 
                    }
              else if($notification['ActivityOn']=='Description' || $notification['ActivityOn']=='Title'){
                    $notification['OldValue']  =  \common\components\CommonUtility::refineActivityData($notification['OldValue'],10);
                     $notification['NewValue']  =  \common\components\CommonUtility::refineActivityData($notification['NewValue'],10);
                   $message=array('Slug'=>$notification['CommentSlug'],'Project'=>$projectDetails,'IsSeen'=>$notification['Status'],'from'=>$from_user['UserName'],'object'=>"description",'type'=> Yii::$app->params[$notification['Notification_Type']],'id'=>$notification['_id'],'ActivityOn'=>$notification['ActivityOn'],'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'PlanLevel'=>$planLevel,'Profile'=>$from_user['ProfilePicture'],'status'=>$notification['Notification_Type'],'OldValue'=>$notification['OldValue'],"NewValue"=>$notification['NewValue']);
                     array_push($result_msg,$message);
             }
             //for child task create...by Ryan
             else if($notification['ActivityOn']=='Create Task' || $notification['ActivityOn']=='Relate' || $notification['ActivityOn']=='UnRelate')
             {
                    error_log("==In Create Task== ");
                    $targetTicketData= TicketCollection::getTicketDetails($notification['TargetTicketId'],$projectId);
                    $targetPlanLevel=$targetTicketData["Fields"]["planlevel"]["value"];
                    if($notification['ActivityOn']=='Create Task')
                    {
                     $message=array('Slug'=>$notification['CommentSlug'],'Project'=>$projectDetails,'IsSeen'=>$notification['Status'],'from'=>$from_user['UserName'],'object'=>"task",'type'=> 'created','id'=>$notification['_id'],'Title'=>$ticket_data['Title'],'TicketId'=>$ticket_data['TicketId'],'date'=>$Date,'PlanLevel'=>$planLevel,'TargetPlanLevel'=>$targetPlanLevel,'Profile'=>$from_user['ProfilePicture'],'status'=>$notification['Notification_Type'],'TargetTicketId'=>$targetTicketData['TicketId'],'TargetTicketTitle'=>$targetTicketData['Title']);   
                    }
                    else
                    {
                        if($notification['ActivityOn']=='Relate')
                        {
                            $message=array('Slug'=>$notification['CommentSlug'],'Project'=>$projectDetails,'IsSeen'=>$notification['Status'],'from'=>$from_user['UserName'],'object'=>"task",'type'=> 'related','id'=>$notification['_id'],'Title'=>$ticket_data['Title'],'TicketId'=>$ticket_data['TicketId'],'date'=>$Date,'PlanLevel'=>$planLevel,'TargetPlanLevel'=>$targetPlanLevel,'Profile'=>$from_user['ProfilePicture'],'status'=>$notification['Notification_Type'],'TargetTicketId'=>$targetTicketData['TicketId'],'TargetTicketTitle'=>$targetTicketData['Title']);
                        }
                        else
                        {
                            $message=array('Slug'=>$notification['CommentSlug'],'Project'=>$projectDetails,'IsSeen'=>$notification['Status'],'from'=>$from_user['UserName'],'object'=>"task",'type'=> 'unrelated','id'=>$notification['_id'],'Title'=>$ticket_data['Title'],'TicketId'=>$ticket_data['TicketId'],'date'=>$Date,'PlanLevel'=>$planLevel,'TargetPlanLevel'=>$targetPlanLevel,'Profile'=>$from_user['ProfilePicture'],'status'=>$notification['Notification_Type'],'TargetTicketId'=>$targetTicketData['TicketId'],'TargetTicketTitle'=>$targetTicketData['Title']);
                        }
                    }
                        array_push($result_msg,$message);
             }
             
             else if($notification['ActivityOn']=='TotalTimeLog')
             {
                $message=array('Slug'=>$notification['CommentSlug'],'Project'=>$projectDetails,'IsSeen'=>$notification['Status'],'from'=>$from_user['UserName'],'object'=>"work log",'type'=> Yii::$app->params[$notification['Notification_Type']],'id'=>$notification['_id'],'ActivityOn'=>$notification['ActivityOn'],'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'PlanLevel'=>$planLevel,'Profile'=>$from_user['ProfilePicture'],'status'=>$notification['Notification_Type'],'OldValue'=>$notification['OldValue'],"NewValue"=>$notification['NewValue'],'Preposition'=>'to');
                array_push($result_msg,$message);
             }
             
                    /* Left Panel newly assigned Field Values End */
                    
                   
                    
                    /********* Followers Messages *****************/
                    
                   else if($notification['ActivityOn']=='FollowObj')
                    {
                        error_log("added");
                        
                            if($notification['NotifiedUser']==$notification['NewValue']) //if logged in user has been added
                            {
                                //Eg : moin.hussain added you as a follower to ticket #33
                                $activityOn ='you';
                             
                            }
                            else
                            {
                                //Eg : moin.hussain added sateesh.mandru as a follower to Ticket #33
                                $action_user=Collaborators::getCollaboratorById($notification['NewValue']);
                                $activityOn =$action_user['UserName'];
                              
                            }
                         

                            if($notification['Notification_Type'] == "followed" || $notification['Notification_Type'] == "unfollowed"){
                                $to="";
                                $otherMessage = "";
                                $preposition = "";
                            }else{
                                $to= $activityOn;
                                $otherMessage = Yii::$app->params['follower'];
                                 $preposition =  $notification['Notification_Type'] == "added" ? "to" : "from";
                            }
                         $message=array('Slug'=>$notification['CommentSlug'],'Project'=>$projectDetails,'IsSeen'=>$notification['Status'],'from'=>$from_user['UserName'],'object'=>"follower",'type'=> Yii::$app->params[$notification['Notification_Type']],'to'=>$to,'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'id'=>$notification['_id'],'PlanLevel'=>$planLevel,'Profile'=>$from_user['ProfilePicture'],"OtherMessage"=>$otherMessage,"Preposition"=>$preposition);
                                array_push($result_msg,$message);
                    }
                              
                   
                    /******* Followers Message End **********/
                    
                    
                    /***** Any changes in Editor ***********/
                    error_log("notifc t-------------------".$notification['Notification_Type']);
                   $commentAllowedArray = ["comment","reply","edit","delete"];
                   if($notification['ActivityOn']=="comment" && (in_array($notification['Notification_Type'],$commentAllowedArray))){
                        $datetime = $notification['NotificationDate']->toDateTime();
                        $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                        $Date = $datetime->format('M-d-Y H:i:s');
                        $collaborator=new Collaborators();
                        //$collaborator=$collaborator->getCollaboratorByUserName($from_user['UserName']);
                        //error_log("===Collaborator==".print_r($collaborator,1));                       
                        $selectfields=['Title','TicketId','Fields.planlevel'];
                        //$ticket_data=ServiceFactory::getStoryServiceInstance()->getTicketDetails($notification['TicketId'],$projectId,$selectfields);
                      //  $collaborator=Collaborators::getCollaboratorWithProfile($from_user['UserName']);
                        if($notification['Notification_Type']=='comment')
                        {
                         //Eg : moin.hussain commented on #33 Ticket 
                             if($notification['ActivityFrom']!=$user)
                             {
                                  $preposition = "on";
                                  $object = "comment";
                           $type =  Yii::$app->params['comment'];
                             }
                        }
                        else if($notification['Notification_Type']=='reply'){
                                   $preposition = "";
                                  $object = "reply";
                             $type =  Yii::$app->params['reply'];
                           
                        }else if($notification['Notification_Type']=='edit'){
                              $preposition = "on";
                                  $object = "edit";
                             $type =  Yii::$app->params['edit'];
                        }else if($notification['Notification_Type']=='delete'){
                              $preposition = "on";
                                  $object = "delete";
                             $type =  Yii::$app->params['delete'];
                        }
                     $message=array('Project'=>$projectDetails,'IsSeen'=>$notification['Status'],'from'=>$from_user['UserName'],'object'=>$object,'type'=>$type,'Slug'=>$notification['CommentSlug'],'date'=>$Date,'id'=>$notification['_id'],'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'PlanLevel'=>$planLevel,'Profile'=>$from_user['ProfilePicture'],"Preposition"=>$preposition);
                     array_push($result_msg,$message);
                 }
                   
                 else if($notification['Notification_Type'] == "mention" )
                   {
                       $datetime = $notification['NotificationDate']->toDateTime();
                        $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                        $Date = $datetime->format('M-d-Y H:i:s');
                        $collaborator=new Collaborators();
                        //$collaborator=$collaborator->getCollaboratorByUserName($from_user['UserName']);
                        //error_log("===Collaborator==".print_r($collaborator,1));                       
                        $selectfields=['Title','TicketId','Fields.planlevel'];
                       // $ticket_data=ServiceFactory::getStoryServiceInstance()->getTicketDetails($notification['TicketId'],$projectId,$selectfields);
                       // $collaborator=Collaborators::getCollaboratorWithProfile($from_user['UserName']);
                        if($notification['Notification_Type']=='mention')
                        {
                            error_log("==in mention==");
                           
                                if($notification['NotifiedUser']==$user){
                                    //Eg : moin.hussain mentioned you in a comment or
                                    //     moin.hussain mentioned you in a reply
                                    //     moin.hussain mentined you on Ticket #33
                                    $notification['NotifiedUser']='you';
                                    $message=array('Project'=>$projectDetails,'IsSeen'=>$notification['Status'],'from'=>$from_user['UserName'],'object'=>"mention",'type'=> Yii::$app->params['mention'],'id'=>$notification['_id'],'Slug'=>$notification['CommentSlug'],'ActivityOn'=>$notification['NotifiedUser'],'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'PlanLevel'=>$planLevel,'Profile'=>$from_user['ProfilePicture']);
                                    array_push($result_msg,$message);
                                    
                                }
                             
                            
                        }
                   }
                   
                       
                    //if($notification['Notification_Type']=='duedate' || $notification['Notification_Type']=='dod' || $notification['Notification_Type']=='estimatedpoints')
                   // {
                  // $storyField = StoryFields::getFieldDetails($notification['ActivityOn'],"Field_Name");
                        else if(isset($storyField['Title'])){
                             error_log("*******************************************".$storyField['Title'])  ;
                      
                         $storyFieldName=$storyField['Title'];
                             //Eg : moin.hussain set duedate to 'apr-14-2017'
                             if($storyField['Type']==4)
                             {
                                 $newValue ="";
                                 $oldValue = "";
                                if($notification['NewValue'] != ""){
                                 $datetime = $notification['NewValue']->toDateTime();
                                 $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                                 $newValue = $datetime->format('M-d-Y');
                                   }
                                 
                                 if($notification['OldValue'] != ""){
                                $datetime = $notification['OldValue']->toDateTime();
                                 $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                                 $oldValue = $datetime->format('M-d-Y');
                                 }
                                
                                 $preposition =  $notification['Notification_Type'] == "set" ? "to" : "**";
                                 $message=array('Slug'=>$notification['CommentSlug'],'Project'=>$projectDetails,'IsSeen'=>$notification['Status'],'from'=>$from_user['UserName'],'type'=> Yii::$app->params["{$notification['Notification_Type']}"],'ActivityOn'=>$storyFieldName,'OldValue'=>$oldValue,"NewValue"=>$newValue,'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'status'=>$notification['Notification_Type'],'id'=>$notification['_id'],'PlanLevel'=>$planLevel,'Profile'=>$from_user['ProfilePicture'],"Preposition"=>$preposition);
                                 array_push($result_msg,$message);
                             }
                             else if($storyField['Type']!=6 )
                             {
                                $notification['OldValue']  =  \common\components\CommonUtility::refineActivityData($notification['OldValue'],10);
                                $notification['NewValue']  =  \common\components\CommonUtility::refineActivityData($notification['NewValue'],10);
                                $preposition =  $notification['Notification_Type'] == "set" ? "to" : "**";
                                $message=array('Slug'=>$notification['CommentSlug'],'Project'=>$projectDetails,'IsSeen'=>$notification['Status'],'from'=>$from_user['UserName'],'type'=>Yii::$app->params["{$notification['Notification_Type']}"],'ActivityOn'=>$storyFieldName,'OldValue'=>$notification['OldValue'],"NewValue"=>$notification['NewValue'],'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'status'=>$notification['Notification_Type'],'id'=>$notification['_id'],'PlanLevel'=>$planLevel,'Profile'=>$from_user['ProfilePicture'],"Preposition"=>$preposition);
                                 array_push($result_msg,$message);
                                 
                             }
                             
                              }
                              
                       /*Left Panel Changed Field Values Start*/
                    
          
                    
                    /*********Left Panel  Changed Field Values End *******************/        
                
                   
                   
                    /**** Changes in Editor End *************/
                    
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
    public static function sendEmailNotification($notificationIds,$projectId){
        try{
         // error_log("send e,ao;====nr==================".print_r($notificationIds,1));
          $notificationIds = json_encode($notificationIds);
        //  error_log("send e,ao;======================".$notificationIds);
           $path = "/data/logs/ProjectX";
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
        
         shell_exec("touch $path/email_notifications.log");
         echo shell_exec("php /usr/share/nginx/www/ProjectXService/yii notifications/fork-email-notification-process '$notificationIds' '$projectId' >> $path/email_notifications.log &");
        
          } catch (Exception $ex) {
            Yii::log("NotificationTrait:sendEmailNotification::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');

        }     
        
    }
    /**
     * @author Moin Hussain
     * @param type $notificationIds
     * @param type $projectId
     */
     public function sendEmailNotificationFromBackground($notificationIds,$projectId){
         try{
         echo "1. SendEmailNotificationFromBackground----started--------\n";
            $msg='';
        $message=array();
        $result_msg=array(); 
        
        // error_log("sendEmailNotification--".print_r($notificationIds,1));
        $notifications = NotificationCollection::getNotificationDetails($notificationIds);
       echo("2. Notifications Count-------------".count($notifications)."\n");
     foreach($notifications as $notification){
         //echo $notification['_id'];
          echo("3. Processing Notification-------------".$notification['_id']."---".$notification['ActivityOn']."\n");
          $recipient_list=array();
         
                 error_log($notification['_id']."==Notification Type==".$notification['Notification_Type']);
                $datetime = $notification['NotificationDate']->toDateTime();
                $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                $Date = $datetime->format('M-d-Y H:i:s');
                $selectfields=['Title','TicketId','Fields.planlevel'];
                $ticket_data= TicketCollection::getTicketDetails($notification['TicketId'],$projectId,$selectfields);
                $ticket_msg='to'. ' '.'#'. $notification['TicketId'] .' ' .$ticket_data['Title'];
                $planLevel = $ticket_data["Fields"]["planlevel"]["value"];
              //  error_log("activtiy form---------------".$notification['ActivityFrom']);
                $from_user= TinyUserCollection::getMiniUserDetails($notification['ActivityFrom']);
                
                  
                   /*************** Left Panel Field Values newly assigned *********************/
                 $activityOn = $notification['ActivityOn'];
                 $storyField = StoryFields::getFieldDetails($activityOn,"Field_Name");;
                 $activityOnFieldType = $storyField["Type"]; 
                 $ticketId = $notification['TicketId'];
                 $title = $ticket_data['Title'];
                 $fromUser = $from_user['UserName'];
          
                $projectDetails = Projects::getProjectMiniDetails($projectId);
                $projectName = $projectDetails["ProjectName"];
                $link = Yii::$app->params['AppURL']."/#/project/$projectName/".$ticketId."/details"; 
                 if($activityOnFieldType== 6) {//newly assigned  
                        //$action_user=Collaborators::getCollaboratorById($notification['ActivityOn']);
                       
                        if($notification['NotifiedUser']==$notification['NewValue']){
                           
                            //for logged in user
                            //Eg : moin.hussain assigned you to ticket #33
                             $to =  "you";
                             $action_user=Collaborators::getCollaboratorById($notification['NewValue']);
                             
                        }
                        else{
                           $action_user=Collaborators::getCollaboratorById($notification['NewValue']);
                                //Eg : moin.hussain assigned sateesh.mandru to Ticket #33
                                //$msg=$from_user['UserName'] .' '. Yii::$app->params['assignedTo'] .' '.$action_user['UserName'].' '.$ticket_msg;
                            $to =  $action_user['UserName'];
                           
                        }
                        $assigned_message=$action_user['UserName'];
                        $old_user=Collaborators::getCollaboratorById($notification['OldValue']);
                        if($old_user!='')
                        {
                            $assigned_message=$old_user['UserName'] . '=>' .$action_user['UserName'];
                        }
                          $preposition =  "to";
                        //  $message=array('from'=>$from_user['UserName'],'object'=>"user",'type'=> Yii::$app->params['assignedTo'],'to'=>$to,'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'id'=>$notification['_id'],'PlanLevel'=>$planLevel,'Profile'=>$from_user['ProfilePicture'],"OtherMessage"=>Yii::$app->params['stakeholder'],"Preposition"=>$preposition);
        $fieldName = "";
        if($activityOn != "assignedto" ){
                 $fieldName = $storyField["Title"];   
        }
       $fieldName =  $fieldName == "" ? "":"as a ".$fieldName;
                   
//                                $text_message = <<<EOD
//{$fromUser} has assigned {$to} {$fieldName} to <a href={$link}>#{$ticketId} {$title} </a>
//EOD;
       
$text_message = <<<EOD
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">Changes by {$fromUser} </td></tr>
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;"> {$storyField['Title']} : {$assigned_message} </td></tr>
EOD;
 
         array_push($recipient_list,$notification['NotifiedUser']);  
                    }
                else  if($activityOn == "Description" || $activityOn == "Title"){
                    $notification['OldValue']  =  CommonUtility::refineActivityData($notification['OldValue'],10);
                    $notification['NewValue']  =  CommonUtility::refineActivityData($notification['NewValue'],10);
                    $message=array('IsSeen'=>$notification['Status'],'from'=>$from_user['UserName'],'object'=>"description",'type'=> Yii::$app->params[$notification['Notification_Type']],'id'=>$notification['_id'],'ActivityOn'=>$notification['ActivityOn'],'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'PlanLevel'=>$planLevel,'Profile'=>$from_user['ProfilePicture'],'status'=>$notification['Notification_Type'],'OldValue'=>$notification['OldValue'],"NewValue"=>$notification['NewValue']);
                    $description_message=$notification['OldValue']. "=>" . $notification['NewValue'];
                             
//                                $text_message = <<<EOD
//<a href={$link}>#{$ticketId} {$title} </a> </br>
//{$activityOn} has been changed by {$fromUser}
//EOD;
                    $text_message = <<<EOD
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">Changes by {$fromUser} </td></tr>
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">{$activityOn} : {$description_message} </td></tr>
EOD;
         array_push($recipient_list,$notification['NotifiedUser']); 
             }
                    
                    /* Left Panel newly assigned Field Values End */
                    
                   
                    
                    /********* Followers Messages *****************/
                    
                    else if($notification['ActivityOn']=='FollowObj'){
                      //  error_log("added");
                            echo ("5. Add follower");
                            if($notification['NotifiedUser']==$notification['NewValue']) //if logged in user has been added
                            {
                                //Eg : moin.hussain added you as a follower to ticket #33
                                $activityOn ='you';
                                $action_user=Collaborators::getCollaboratorById($notification['NewValue']);
                                $activityOn =$action_user['UserName'];
                             
                            }
                            else
                            {
                                //Eg : moin.hussain added sateesh.mandru as a follower to Ticket #33
                                $action_user=Collaborators::getCollaboratorById($notification['NewValue']);
                                $activityOn =$action_user['UserName'];
                              
                            }
                           $preposition =  $notification['Notification_Type'] == "added" ? "to" : "from";
                        // $message=array('from'=>$from_user['UserName'],'object'=>"follower",'type'=> Yii::$app->params[$notification['Notification_Type']],'to'=>$activityOn,'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'id'=>$notification['_id'],'PlanLevel'=>$planLevel,'Profile'=>$from_user['ProfilePicture'],"OtherMessage"=>Yii::$app->params['follower'],"Preposition"=>$preposition);
                           $follower_message="Follower :". " ". $notification['Notification_Type']. " ". $activityOn;
                               
//                                 $text_message = <<<EOD
//{$fromUser}  {$message} {$activityOn} as follower {$preposition} <a href={$link}>#{$ticketId} {$title} </a>
//EOD;
                               
                                   $text_message = <<<EOD
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">(Comment by) {$fromUser} </td></tr>
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;"> {$follower_message} </td></tr>
EOD;
 
         array_push($recipient_list,$notification['NotifiedUser']);     
                    }
                              
                   
                    /******* Followers Message End **********/
                    
                    
                    /***** Any changes in Editor ***********/
                    $commentAllowedArray = ["comment","reply","edit","delete"];
                   if($notification['ActivityOn']=="comment" && (in_array($notification['Notification_Type'],$commentAllowedArray))){
                        $datetime = $notification['NotificationDate']->toDateTime();
                        $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                        $Date = $datetime->format('M-d-Y H:i:s');
                        $collaborator=new Collaborators();
                        //$collaborator=$collaborator->getCollaboratorByUserName($from_user['UserName']);
                        //error_log("===Collaborator==".print_r($collaborator,1));                       
                        $selectfields=['Title','TicketId','Fields.planlevel'];
                        //$ticket_data=ServiceFactory::getStoryServiceInstance()->getTicketDetails($notification['TicketId'],$projectId,$selectfields);
                      //  $collaborator=Collaborators::getCollaboratorWithProfile($from_user['UserName']);
                      
                    // $message=array('from'=>$from_user['UserName'],'object'=>$object,'type'=>$type,'Slug'=>$notification['CommentSlug'],'date'=>$Date,'id'=>$notification['_id'],'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'PlanLevel'=>$planLevel,'Profile'=>$from_user['ProfilePicture'],"Preposition"=>$preposition);
                    // array_push($result_msg,$message);
          error_log("Notification___Type___________########################".$notification['Notification_Type'] );       
         $link .=  "?Slug=".$notification['CommentSlug'] ;  
          if($notification['Notification_Type'] == "comment"){
            //  error_log("comment-----------------------22222");
               $preposition = "on";
                           $object = "comment";
                           $type =  Yii::$app->params['comment'];
            $text_message = <<<EOD
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">(Comment by) {$fromUser} </td></tr>
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">{$fromUser} commented on Ticket </td></tr>
EOD;
              
    } 
              else if($notification['Notification_Type'] == "reply"){
                 //  error_log("replyyyyyyyyyyyyy-----------------------111111111111s");
                      $preposition = "";
                       $object = "reply";
                             $type =  Yii::$app->params['reply']; 
//            $text_message = <<<EOD
//<a href={$link}>#{$ticketId} {$title} </a> <br/> replied by {$fromUser}
//EOD;
                             $text_message = <<<EOD
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">(Comment by) {$fromUser} </td></tr>
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">{$fromUser} replied on Ticket </td></tr>
EOD;
            
    } 
                  else if($notification['Notification_Type'] == "edit"){
                 //  error_log("replyyyyyyyyyyyyy-----------------------111111111111s");
                      $preposition = "";
                       $object = "edit";
                             $type =  Yii::$app->params['reply']; 
//            $text_message = <<<EOD
//<a href={$link}>#{$ticketId} {$title} </a> <br/> comment edited by {$fromUser}
//EOD;
                              $text_message = <<<EOD
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">(Comment by) {$fromUser} </td></tr>
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">{$fromUser} edited comment on Ticket </td></tr>
EOD;
            
    }    else if($notification['Notification_Type'] == "delete"){
                 //  error_log("replyyyyyyyyyyyyy-----------------------111111111111s");
                      $preposition = "";
                       $object = "delete";
                             $type =  Yii::$app->params['delete'];
//            $text_message = <<<EOD
//<a href={$link}>#{$ticketId} {$title} </a> <br/> comment deleted by {$fromUser}
//EOD;
                              $text_message = <<<EOD
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">(Comment by) {$fromUser} </td></tr>
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">{$fromUser} deleted comment on Ticket </td></tr>
EOD;
        
    } 
                     
                array_push($recipient_list,$notification['NotifiedUser']);     
                 }
                   
                  else if($notification['Notification_Type'] == "mention" )
                   {
                       $datetime = $notification['NotificationDate']->toDateTime();
                        $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                        $Date = $datetime->format('M-d-Y H:i:s');
                        $collaborator=new Collaborators();
                        //$collaborator=$collaborator->getCollaboratorByUserName($from_user['UserName']);
                        //error_log("===Collaborator==".print_r($collaborator,1));                       
                        $selectfields=['Title','TicketId','Fields.planlevel'];
                       // $ticket_data=ServiceFactory::getStoryServiceInstance()->getTicketDetails($notification['TicketId'],$projectId,$selectfields);
                       // $collaborator=Collaborators::getCollaboratorWithProfile($from_user['UserName']);
                        if($notification['Notification_Type']=='mention')
                        {
                        //    error_log("==in mention==");
                      
//            $text_message = <<<EOD
//<a href={$link}>#{$ticketId} {$title} </a> <br/> mentiond you by {$fromUser}
//EOD;
                             $text_message = <<<EOD
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">(Comment by) {$fromUser} </td></tr>
        <tr><td style="font-family:'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333;">{$fromUser} mentioned on Ticket </td></tr>
EOD;
           
                    array_push($recipient_list,$notification['NotifiedUser']);          
                             
                            
                        }
                   }
                   
                       
                    //if($notification['Notification_Type']=='duedate' || $notification['Notification_Type']=='dod' || $notification['Notification_Type']=='estimatedpoints')
                   // {
                  // $storyField = StoryFields::getFieldDetails($notification['ActivityOn'],"Field_Name");
                         if(isset($storyField['Title'])){
                        //     error_log("*******************************************")  ;
                      
                         $storyFieldName=$storyField['Title'];
                             //Eg : moin.hussain set duedate to 'apr-14-2017'
                             if($storyField['Type']==4)
                             {
                                 $newValue ="";
                                 $oldValue = "";
                                if($notification['NewValue'] != ""){
                                 $datetime = $notification['NewValue']->toDateTime();
                                 $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                                 $newValue = $datetime->format('M-d-Y');
                                   }
                                 
                                 if($notification['OldValue'] != ""){
                                $datetime = $notification['OldValue']->toDateTime();
                                 $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                                 $oldValue = $datetime->format('M-d-Y');
                                 }
                                
                                 $preposition =  $notification['Notification_Type'] == "set" ? "to" : "**";
                                 $message=array('from'=>$from_user['UserName'],'type'=> Yii::$app->params["{$notification['Notification_Type']}"],'ActivityOn'=>$storyFieldName,'OldValue'=>$oldValue,"NewValue"=>$newValue,'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'status'=>$notification['Notification_Type'],'id'=>$notification['_id'],'PlanLevel'=>$planLevel,'Profile'=>$from_user['ProfilePicture'],"Preposition"=>$preposition);
                                 array_push($result_msg,$message);
                             }
                             else if($storyField['Type']!=6 )
                             {
                                $notification['OldValue']  =  CommonUtility::refineActivityData($notification['OldValue'],10);
                                $notification['NewValue']  =  CommonUtility::refineActivityData($notification['NewValue'],10);
                               $preposition =  $notification['Notification_Type'] == "set" ? "to" : "**";
                                 $message=array('from'=>$from_user['UserName'],'type'=>Yii::$app->params["{$notification['Notification_Type']}"],'ActivityOn'=>$storyFieldName,'OldValue'=>$notification['OldValue'],"NewValue"=>$notification['NewValue'],'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'status'=>$notification['Notification_Type'],'id'=>$notification['_id'],'PlanLevel'=>$planLevel,'Profile'=>$from_user['ProfilePicture'],"Preposition"=>$preposition);
                                 array_push($result_msg,$message);
                                 
                             }
                             
                              }
                            echo ("3. sending email ...");  
                       /*Left Panel Changed Field Values Start*/
             foreach ($recipient_list as &$value) {
                  $collaboratorData = TinyUserCollection::getMiniUserDetails($value);
                  $value = $collaboratorData['Email'];
                  $notified_user=$collaboratorData['UserName'];
               //   error_log("EMAIL________________+++++++++++_____________".$value);
             }
              $subject=$projectName;
              $project_logo="<tr><td style='border:solid 1px #f0f0f0; padding:5px;'><a href='' ><img src=".''.Yii::$app->params['EmailServerURL'].'/files/tool/logo.png'." style='border:0px; outline:0px;'/></a></td></tr>
                             <tr><td bgcolor='#f8f8f8' style='border-bottom:solid 1px #fff;'>
                             <table width='96%' border='0' align='center' cellpadding='0' cellspacing='0'>
                              <tr>
                              <td height='15'>&nbsp;</td>
                              </tr>";
              $user_message="<tr><td style='font-family':'Arial', Helvetica, sans-serif;  font-size:14px;line-height:24px;color:#333333; border-bottom:solid 1px #f0f0f0; padding-bottom:10px;>Hi ". $notified_user."<br/>"."you have a new ticket alert.</td></tr>";
              $link_message = "<a  style='font-family:Arial, Helvetica, sans-serif;  font-size:16px;line-height:40px;color:#0199e0; text-decoration:none;' href={$link}>#{$ticketId} {$title} </a>";
              $ticket_message="<tr><td style='font-family:'Arial', Helvetica, sans-serif;  font-size:18px;color:#0199e0; line-height:30px; font-weight:bold; padding-top:10px; padding-bottom:10px;'>".$link_message."</td></tr>";
              $view_ticket_message="<tr><td><a style='font-family:Arial, Helvetica, sans-serif;  font-size:16px;line-height:40px;color:#0199e0; text-decoration:none;' href={$link}>View ticket</a></td></tr></table> </td></tr>";
              $text_message=$project_logo . $user_message . $ticket_message . $text_message.$view_ticket_message;
              echo("==going to send mail==");
             CommonUtility::sendEmail($recipient_list,$text_message,$subject);
     }
     }catch (Exception $ex) {
        Yii::log("NotificationTrait:sendEmailNotificationFromBackground::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');

        } 
    }  
} 