<?php
namespace common\service;
use common\models\mongo\{TicketCollection,TinyUserCollection,ProjectTicketSequence,TicketTimeLog,TicketComments,TicketArtifacts,NotificationCollection};
use common\components\{CommonUtility};
use common\models\mysql\{WorkFlowFields,StoryFields,Priority,PlanLevel,TicketType,Bucket,Collaborators,TaskTypes,Filters};
use yii;
use \ArrayObject;

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
            $ticketId=$ticket_data->TicketId;
            $projectId=$ticket_data->projectId;
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
            $commentOwner=$commentData->Comment->OrigianalCommentorId;
            $loggedinUser=$commentData->userInfo->Id;
            $ticketId=$commentData->TicketId;
            $projectId=$commentData->projectId;
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
                    $tic->ActivityOn=$notify_type;
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
                                $tic->Notification_Type=$notify_type;
                                $tic->ActivityOn=$notify_type; //added for consistency
                            }else{
                                error_log("==comment==");
                                 $tic->Notification_Type="comment";
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
    public static function saveNotifications($notification_data,$notifyType,$activityOn,$fieldType="")
    {
        error_log("in save notifications--".$notifyType."----------".$activityOn."--------".$fieldType."---");
        try{
            
            $oldValue='';
            $newValue='';
            $oldCollaborator='';
            $newCollaborator='';
                //For Story Detail Page Use Case.....
           $ticketId =  isset($notification_data->TicketId) ? $notification_data->TicketId : $notification_data->data->TicketId;
           // $ticketId=$notification_data->TicketId;
            $projectId=$notification_data->projectId;
            $from=$notification_data->userInfo->username;
            $loggedInUser=$notification_data->userInfo->Id;         
            $notify_type=$notifyType;//this will be changed to ActivityOn in the below code....
            $currentDate = new \MongoDB\BSON\UTCDateTime(time() * 1000);
            $ticketDetails = TicketCollection::getTicketDetails($ticketId,$projectId);  
            $followers=$ticketDetails['Followers'];
            if($notifyType == "Title" || $notifyType == "Description")
            {
                $oldValue = $ticketDetails[$notifyType]; 
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
                                       $validDate = CommonUtility::validateDate($newValue);
                                      if($validDate){
                                     $newValue = new \MongoDB\BSON\UTCDateTime(strtotime($validDate) * 1000);
                                       }
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
                    else if($fieldType!= "Description" && $fieldType!= "Title"){ //This is for left hand property changes
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
                error_log("foloowerid-----------------------".$follower['FollowerId']);
               error_log("===Notify Type".$notify_type);
              if($follower['FollowerId'] == $loggedInUser){
                  error_log("continure----------------");
                  continue;
              }
              error_log("procssin----------------");
                    $tic = new NotificationCollection();
                    $tic->NotifiedUser=(int)$follower['FollowerId'];
                    $tic->TicketId =$ticketId;
                    $tic->ProjectId =$projectId;
                    $tic->ActivityOn=$notify_type; // new use case "ActivityOn" will be Field Name
                    $tic->ActivityFrom=(int)$loggedInUser;
                    $tic->NotificationDate=$currentDate;
                    
                         if($fieldType == "Description" || $fieldType == "Title"){
                             error_log("in descrioton--------");
                            $tic->Notification_Type=$fieldType;
                            $tic->ActivityOn= $fieldType;
                            $tic->OldValue=$oldValue;
                            $tic->NewValue=$activityOn;
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
                          
                        
                        $tic->Notification_Type=$activityOn;
                        $tic->Status=0;
                        $tic->OldValue=$oldValue;
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
                
                  
                   /*************** Left Panel Field Values newly assigned *********************/
                 $activityOn = $notification['ActivityOn'];
                 $storyField = StoryFields::getFieldDetails($activityOn,"Field_Name");;
                 $activityOnFieldType = $storyField["Type"];   
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
                          $message=array('IsSeen'=>$notification['Status'],'from'=>$from_user['UserName'],'object'=>"user",'type'=> Yii::$app->params['assigned'],'to'=>$to,'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'id'=>$notification['_id'],'PlanLevel'=>$planLevel,'Profile'=>$from_user['ProfilePicture'],"OtherMessage"=>Yii::$app->params[$activityOn],"Preposition"=>$preposition);
                                array_push($result_msg,$message); 
                    }
            
                    
                    /* Left Panel newly assigned Field Values End */
                    
                   
                    
                    /********* Followers Messages *****************/
                    
                    if($notification['ActivityOn']=='FollowObj')
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
                         

                            if($notification['Notification_Type'] == "followed"){
                                $to="";
                                $otherMessage = "";
                                $preposition = "";
                            }else{
                                $to= $activityOn;
                                $otherMessage = Yii::$app->params['follower'];
                                 $preposition =  $notification['Notification_Type'] == "added" ? "to" : "from";
                            }
                         $message=array('IsSeen'=>$notification['Status'],'from'=>$from_user['UserName'],'object'=>"follower",'type'=> Yii::$app->params[$notification['Notification_Type']],'to'=>$to,'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'id'=>$notification['_id'],'PlanLevel'=>$planLevel,'Profile'=>$from_user['ProfilePicture'],"OtherMessage"=>$otherMessage,"Preposition"=>$preposition);
                                array_push($result_msg,$message);
                    }
                              
                   
                    /******* Followers Message End **********/
                    
                    
                    /***** Any changes in Editor ***********/
                    error_log("notifc t-------------------".$notification['Notification_Type']);
                   if($notification['Notification_Type'] == "comment" || $notification['Notification_Type'] == "reply"){
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
                        if($notification['Notification_Type']=='reply'){
                                   $preposition = "";
                                  $object = "reply";
                             $type =  Yii::$app->params['reply'];
                           
                        }
                     $message=array('IsSeen'=>$notification['Status'],'from'=>$from_user['UserName'],'object'=>$object,'type'=>$type,'Slug'=>$notification['CommentSlug'],'date'=>$Date,'id'=>$notification['_id'],'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'PlanLevel'=>$planLevel,'Profile'=>$from_user['ProfilePicture'],"Preposition"=>$preposition);
                     array_push($result_msg,$message);
                 }
                   
                  if($notification['Notification_Type'] == "mention" )
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
                                    $message=array('IsSeen'=>$notification['Status'],'from'=>$from_user['UserName'],'object'=>"mention",'type'=> Yii::$app->params['mention'],'id'=>$notification['_id'],'Slug'=>$notification['CommentSlug'],'ActivityOn'=>$notification['NotifiedUser'],'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'PlanLevel'=>$planLevel,'Profile'=>$from_user['ProfilePicture']);
                                    array_push($result_msg,$message);
                                    
                                }
                             
                            
                        }
                   }
                   
                       
                    //if($notification['Notification_Type']=='duedate' || $notification['Notification_Type']=='dod' || $notification['Notification_Type']=='estimatedpoints')
                   // {
                  // $storyField = StoryFields::getFieldDetails($notification['ActivityOn'],"Field_Name");
                         if(isset($storyField['Title'])){
                             error_log("*******************************************")  ;
                      
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
                                 $message=array('IsSeen'=>$notification['Status'],'from'=>$from_user['UserName'],'type'=> Yii::$app->params["{$notification['Notification_Type']}"],'ActivityOn'=>$storyFieldName,'OldValue'=>$oldValue,"NewValue"=>$newValue,'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'status'=>$notification['Notification_Type'],'id'=>$notification['_id'],'PlanLevel'=>$planLevel,'Profile'=>$from_user['ProfilePicture'],"Preposition"=>$preposition);
                                 array_push($result_msg,$message);
                             }
                             else if($storyField['Type']!=6 )
                             {
                                $notification['OldValue']  =  \common\components\CommonUtility::refineActivityData($notification['OldValue'],10);
                                $notification['NewValue']  =  \common\components\CommonUtility::refineActivityData($notification['NewValue'],10);
                                $preposition =  $notification['Notification_Type'] == "set" ? "to" : "**";
                                $message=array('IsSeen'=>$notification['Status'],'from'=>$from_user['UserName'],'type'=>Yii::$app->params["{$notification['Notification_Type']}"],'ActivityOn'=>$storyFieldName,'OldValue'=>$notification['OldValue'],"NewValue"=>$notification['NewValue'],'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'status'=>$notification['Notification_Type'],'id'=>$notification['_id'],'PlanLevel'=>$planLevel,'Profile'=>$from_user['ProfilePicture'],"Preposition"=>$preposition);
                                 array_push($result_msg,$message);
                                 
                             }
                             
                              }
                              
                       /*Left Panel Changed Field Values Start*/
                    
          
                    
                    /*********Left Panel  Changed Field Values End *******************/        
                
                   
                   
                    /**** Changes in Editor End *************/
                    
            }
                
                
                error_log("==Result Msg==".print_r($result_msg,1));
          
           // return $notifications;
            return $result_msg;
          
        } catch (Exception $ex) {
            Yii::log("NotificationTrait:getNotifications::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');

        }
    }
    public static function sendEmailNotification($notificationIds,$projectId){
        
         $msg='';
        $message=array();
        $result_msg=array(); 
        
        
         error_log("sendEmailNotification--".print_r($notificationIds,1));
        $notifications = NotificationCollection::getNotificationDetails($notificationIds);
        error_log("count-------------".count($notifications));
     foreach($notifications as $notification){
          $recipient_list=array();
         
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
                
                  
                   /*************** Left Panel Field Values newly assigned *********************/
                 $activityOn = $notification['ActivityOn'];
                 $storyField = StoryFields::getFieldDetails($activityOn,"Field_Name");;
                 $activityOnFieldType = $storyField["Type"]; 
                 $ticketId = $notification['TicketId'];
                 $title = $ticket_data['Title'];
                 $fromUser = $from_user['UserName'];
                 if($activityOn == "Description" || $activityOn == "Title"){
                   
                                 $link=Yii::$app->params['AppURL']."/#/story-detail/".$ticketId;
                                $text_message = <<<EOD
<a href={$link}>#{$ticketId} {$title} </a> </br>
{$activityOn} has been changed by {$fromUser}
EOD;
 
         array_push($recipient_list,$notification['NotifiedUser']);                       
                 }
                 
                else if($activityOnFieldType== 6) //newly assigned 
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
                        //  $message=array('from'=>$from_user['UserName'],'object'=>"user",'type'=> Yii::$app->params['assignedTo'],'to'=>$to,'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'id'=>$notification['_id'],'PlanLevel'=>$planLevel,'Profile'=>$from_user['ProfilePicture'],"OtherMessage"=>Yii::$app->params['stakeholder'],"Preposition"=>$preposition);
        $fieldName = "";
        if($activityOn != "assignedto" ){
                 $fieldName = $storyField["Title"];   
        }
       $fieldName =  $fieldName == "" ? "":"as a ".$fieldName;
                   
                                 $link=Yii::$app->params['AppURL']."/#/story-detail/".$ticketId;
                                $text_message = <<<EOD
{$fromUser} has assigned {$to} {$fieldName} to <a href={$link}>#{$ticketId} {$title} </a>
EOD;
 
         array_push($recipient_list,$notification['NotifiedUser']);  
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
                           $preposition =  $notification['Notification_Type'] == "added" ? "to" : "from";
                        // $message=array('from'=>$from_user['UserName'],'object'=>"follower",'type'=> Yii::$app->params[$notification['Notification_Type']],'to'=>$activityOn,'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'id'=>$notification['_id'],'PlanLevel'=>$planLevel,'Profile'=>$from_user['ProfilePicture'],"OtherMessage"=>Yii::$app->params['follower'],"Preposition"=>$preposition);
                        
                                 $link=Yii::$app->params['AppURL']."/#/story-detail/".$ticketId;
                               $message =  Yii::$app->params[$notification['Notification_Type']];
                                $text_message = <<<EOD
{$fromUser}  {$message} {$activityOn} as follower {$preposition} <a href={$link}>#{$ticketId} {$title} </a>
EOD;
 
         array_push($recipient_list,$notification['NotifiedUser']);     
                    }
                              
                   
                    /******* Followers Message End **********/
                    
                    
                    /***** Any changes in Editor ***********/
                  else if($notification['Notification_Type'] == "comment" || $notification['Notification_Type'] == "reply"){
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
                     
          if($notification['Notification_Type'] == "comment"){
              error_log("comment-----------------------");
               $preposition = "on";
                           $object = "comment";
                           $type =  Yii::$app->params['comment'];
               $link=Yii::$app->params['AppURL']."/#/story-detail/".$ticketId;
            $text_message = <<<EOD
<a href={$link}>#{$ticketId} {$title} </a> <br/> commented by {$fromUser}
EOD;
            
    } 
              if($notification['Notification_Type'] == "reply"){
                   error_log("replyyyyyyyyyyyyy-----------------------");
                      $preposition = "";
                       $object = "reply";
                             $type =  Yii::$app->params['reply'];
               $link=Yii::$app->params['AppURL']."/#/story-detail/".$ticketId;
            $text_message = <<<EOD
<a href={$link}>#{$ticketId} {$title} </a> <br/> replied by {$fromUser}
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
                            error_log("==in mention==");
                      
 $link=Yii::$app->params['AppURL']."/#/story-detail/".$ticketId;
            $text_message = <<<EOD
<a href={$link}>#{$ticketId} {$title} </a> <br/> mentiond you by {$fromUser}
EOD;
           
                    array_push($recipient_list,$notification['NotifiedUser']);          
                             
                            
                        }
                   }
                   
                       
                    //if($notification['Notification_Type']=='duedate' || $notification['Notification_Type']=='dod' || $notification['Notification_Type']=='estimatedpoints')
                   // {
                  // $storyField = StoryFields::getFieldDetails($notification['ActivityOn'],"Field_Name");
                         if(isset($storyField['Title'])){
                             error_log("*******************************************")  ;
                      
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
                                $notification['OldValue']  =  \common\components\CommonUtility::refineActivityData($notification['OldValue'],10);
                                $notification['NewValue']  =  \common\components\CommonUtility::refineActivityData($notification['NewValue'],10);
                               $preposition =  $notification['Notification_Type'] == "set" ? "to" : "**";
                                 $message=array('from'=>$from_user['UserName'],'type'=>Yii::$app->params["{$notification['Notification_Type']}"],'ActivityOn'=>$storyFieldName,'OldValue'=>$notification['OldValue'],"NewValue"=>$notification['NewValue'],'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'status'=>$notification['Notification_Type'],'id'=>$notification['_id'],'PlanLevel'=>$planLevel,'Profile'=>$from_user['ProfilePicture'],"Preposition"=>$preposition);
                                 array_push($result_msg,$message);
                                 
                             }
                             
                              }
                              
                       /*Left Panel Changed Field Values Start*/
                    
          
                    
                    /*********Left Panel  Changed Field Values End *******************/        
              
                   
                    /**** Changes in Editor End *************/
                     error_log("sendEmailNotification--snedin eaml--");
                  
           
             error_log("send eamil--------------------".print_r($recipient_list,1));
             foreach ($recipient_list as &$value) {
                  $collaboratorData = TinyUserCollection::getMiniUserDetails($value);
                  $value = $collaboratorData['Email'];
             }
                  
                  
                      CommonUtility::sendEmail($recipient_list,$text_message);
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
    public static function sendMail($ticketData,$loggedInUser,$recipients,$notificationType,$toFollower = false)
    {
        error_log("sendMail--".$loggedInUser."---".$recipients."--".$notificationType);
      //  error_log("==Followers==".print_r($ticketdetails['Followers'],1));
       // $followers=$ticketdetails['Followers'];
       // $recipient_list=array();
        $attachment_list=array();
        try
        {
            $collaboratorData = TinyUserCollection::getMiniUserDetails($loggedInUser);
            $actionByUser = $collaboratorData["UserName"];
            $ticketId = $ticketData["TicketId"];
            $title = $ticketData["Title"];
            error_log("sending email-------");
            if($notificationType == "mention"){
               $link=Yii::$app->params['AppURL']."/#/story-detail/".$ticketId;
            $text_message = <<<EOD
<a href={$link}>#{$ticketId} {$title} </a> <br/> mentiond you by {$actionByUser}
EOD;
            
    } 
          if($notificationType == "comment"){
               $link=Yii::$app->params['AppURL']."/#/story-detail/".$ticketId;
            $text_message = <<<EOD
<a href={$link}>#{$ticketId} {$title} </a> <br/> commented by {$actionByUser}
EOD;
            
    } 
              if($notificationType == "reply"){
               $link=Yii::$app->params['AppURL']."/#/story-detail/".$ticketId;
            $text_message = <<<EOD
<a href={$link}>#{$ticketId} {$title} </a> <br/> replied by {$actionByUser}
EOD;
            
    } 
    if($notificationType == "assignedto" || $notificationType == "stakeholder"){
        $fieldName = "";
        if($notificationType != "assignedto" ){
           $storyField = StoryFields::getFieldDetails($notificationType,"Field_Name");
                 $fieldName = $storyField["Title"];   
        }
       $fieldName =  $fieldName == "" ? "":"as a ".$fieldName;
                    
                                 $link=Yii::$app->params['AppURL']."/#/story-detail/".$ticketId;
                                $text_message = <<<EOD
{$actionByUser} has assigned you {$fieldName} to <a href={$link}>#{$ticketId} {$title} </a>
EOD;
 
   } 
  
    
    
         //   $qry="select UserName,Email from Collaborators where UserName = '$loggedInUser'";
           // $from = Yii::$app->db->createCommand($qry)->queryOne();
//            if(is_array($recipients)) // for @mentioned users
//            {
//                if(!empty($recipients))
//                {
//                    for($i=0;$i<count($recipients);$i++)
//                    {
//                        $qry="select UserName,Email from Collaborators where UserName = '$recipients[$i]'";
//                        $data = Yii::$app->db->createCommand($qry)->queryOne();
//                        array_push($recipient_list,$data['Email']);
//                    }
//                }
//                
//                // for followers
//                foreach($followers as $follower)
//                {
//                    $followerid=$follower['FollowerId'];
//                    error_log("==Follower Id==".$followerid);
//                    $qry="select UserName,Email from Collaborators where Id = '$followerid'";
//                    $data = Yii::$app->db->createCommand($qry)->queryOne();
//                    if($data['UserName']!=$loggedInUser) //To avoidloggedin user @mentioning himself
//                    {
//                        array_push($recipient_list,$data['Email']);
//                    }
//                }
//            }
//            else // for assigned to and stakeholder
//            {
//                $qry="select UserName,Email from Collaborators where UserName = '$recipients'";
//                $data = Yii::$app->db->createCommand($qry)->queryOne();
//                array_push($recipient_list,$data['Email']);
//            }
    
         $recipient_list=array();
             if(!is_array($recipients)){
                 array_push($recipient_list,$recipients);
             }else{
                 $recipient_list = $recipients;
             }
             error_log("send eamil--------------------".print_r($recipient_list,1));
             foreach ($recipient_list as &$value) {
                  $collaboratorData = TinyUserCollection::getMiniUserDetails($value);
                  $value = $collaboratorData['Email'];
             }
            CommonUtility::sendEmail($recipient_list,$text_message);
 
            
        } catch (Exception $ex) {
            Yii::log("NotificationTrait:sendMail::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
} 