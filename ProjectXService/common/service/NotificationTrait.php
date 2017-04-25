<?php
namespace common\service;
use common\models\mongo\{TicketCollection,NotificationCollection,TinyUserCollection};
use common\components\CommonUtility;
use common\models\mysql\Collaborators;
use yii;
use common\models\mysql\StoryFields;

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
                    $tic->save();
                }
            }
            
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
                            $tic->save();
                    }

                }
        }catch(Exception $ex)
        {
            Yii::log("NotificationTrait:saveNotificationsForComment::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
     public static function getNotifications($user,$projectId,$offset=0,$limit=5)
    {
        error_log("==in get notifications---".$user."---".$projectId."---".$offset."---".$limit);
        $msg='';
        $message=array();
        $result_msg=array();
        $action_user=Collaborators::getCollaboratorById($user);
        try{
            $notifications = NotificationCollection::getNotifications($user, $projectId,$offset,$limit);
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
                            $message=array('from'=>$from_user['UserName'],'type'=> Yii::$app->params['assignedTo'],'to'=>"you to",'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'id'=>$notification['_id'],'PlanLevel'=>$ticket_data['StoryType']['Id'],'Profile'=>$from_user['ProfilePicture'],"OtherMessage"=>Yii::$app->params['stakeholder']);
                            array_push($result_msg,$message);
                        }
                        else
                        {
                           
                                //Eg : moin.hussain assigned sateesh.mandru to Ticket #33
                                //$msg=$from_user['UserName'] .' '. Yii::$app->params['assignedTo'] .' '.$action_user['UserName'].' '.$ticket_msg;
                                $message=array('from'=>$from_user['UserName'],'type'=> Yii::$app->params['assignedTo'],'to'=>$action_user['UserName'],'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'id'=>$notification['_id'],'PlanLevel'=>$ticket_data['StoryType']['Id'],'Profile'=>$from_user['ProfilePicture'],"OtherMessage"=>Yii::$app->params['stakeholder']);
                                array_push($result_msg,$message);
                           
                        }
                         
                    }
            
                    
                    /* Left Panel newly assigned Field Values End */
                    
                   
                    
                    /********* Followers Messages *****************/
                    
                    if($notification['ActivityOn']=='FollowObj')
                    {
                        error_log("added");
                        
                            if($notification['NotifiedUser']==$notification['NewValue']) //if logged in user has been added
                            {
                                //Eg : moin.hussain added you as a follower to ticket #33
                                $activityOn ='You';
                             
                            }
                            else
                            {
                                //Eg : moin.hussain added sateesh.mandru as a follower to Ticket #33
                                $action_user=Collaborators::getCollaboratorById($notification['NewValue']);
                                $activityOn =$action_user['UserName'];
                              
                            }
                         $message=array('from'=>$from_user['UserName'],'type'=> Yii::$app->params['added'],'to'=>$activityOn,'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'id'=>$notification['_id'],'PlanLevel'=>$planLevel,'Profile'=>$from_user['ProfilePicture'],"OtherMessage"=>Yii::$app->params['stakeholder']);
                                array_push($result_msg,$message);
                    }
                    
                    if($notification['Notification_Type']=='removed')
                    {
                       
                            if($notification['NotifiedUser']==$notification['ActivityOn']) //if logged in user has been added
                            {
                                //Eg : moin.hussain added you as a follower to ticket #33
                                $notification['ActivityOn']='You from';
                                $message=array('from'=>$from_user['UserName'],'type'=> Yii::$app->params['removed'],'ActivityOn'=>$notification['ActivityOn'],'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'stakeholder'=>Yii::$app->params['follower'],'id'=>$notification['_id'],'PlanLevel'=>$planLevel,'Profile'=>$from_user['ProfilePicture']);
                            }

                       
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
                             if($from_user['UserName']!=$action_user['UserName'])
                             {
                              $message=array('from'=>$from_user['UserName'],'type'=>Yii::$app->params['comment'],'Slug'=>$notification['CommentSlug'],'date'=>$Date,'id'=>$notification['_id'],'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'PlanLevel'=>$planLevel,'Profile'=>$from_user['ProfilePicture']);
                              array_push($result_msg,$message);
                             }
                           
                             
                        }
                        if($notification['Notification_Type']=='reply')
                        {
                            //Eg: moin.hussain replied on #33 Ticket
                           // error_log(print_r($from_user,1)."---".print_r($action_user,1));
                            
                            // For replied on your comment
                           
                                    //Eg: ranjani.thakur replied on the comment
                                    $message=array('from'=>$from_user['UserName'],'type'=>Yii::$app->params['reply'],'Slug'=>$notification['CommentSlug'],'date'=>$Date,'id'=>$notification['_id'],'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'PlanLevel'=>$planLevel,'Profile'=>$from_user['ProfilePicture'],'Reply'=>1);
                                    array_push($result_msg,$message);
                               
                           
                        }

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
                                    $notification['NotifiedUser']='You';
                                    $message=array('from'=>$from_user['UserName'],'type'=> Yii::$app->params['mention'],'id'=>$notification['_id'],'Slug'=>$notification['CommentSlug'],'ActivityOn'=>$notification['NotifiedUser'],'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'PlanLevel'=>$planLevel,'Profile'=>$from_user['ProfilePicture']);
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
                                
                                 
                                 $message=array('from'=>$from_user['UserName'],'type'=> Yii::$app->params["{$notification['Notification_Type']}"],'ActivityOn'=>$storyFieldName,'OldValue'=>$oldValue,"NewValue"=>$newValue,'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'status'=>$notification['Notification_Type'],'id'=>$notification['_id'],'PlanLevel'=>$planLevel,'Profile'=>$from_user['ProfilePicture']);
                                 array_push($result_msg,$message);
                             }
                             else
                             {
                                $notification['OldValue']  =  \common\components\CommonUtility::refineActivityData($notification['OldValue'],10);
                                $notification['NewValue']  =  \common\components\CommonUtility::refineActivityData($notification['NewValue'],10);
                                 $message=array('from'=>$from_user['UserName'],'type'=>Yii::$app->params["{$notification['Notification_Type']}"],'ActivityOn'=>$storyFieldName,'OldValue'=>$notification['OldValue'],"NewValue"=>$notification['NewValue'],'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'status'=>$notification['Notification_Type'],'id'=>$notification['_id'],'PlanLevel'=>$planLevel,'Profile'=>$from_user['ProfilePicture']);
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

        }
    }
    
} 