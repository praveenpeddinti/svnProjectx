<?php
namespace common\models\mongo;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/*
 * refer : 
 * https://github.com/yiisoft/yii2-mongodb/blob/master/docs/guide/usage-ar.md
 */
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
//use yii\db\ActiveRecord;
use yii\mongodb\ActiveRecord;
use yii\mongodb\Query;
use yii\data\ActiveDataProvider;
use yii\web\IdentityInterface;
use common\service\StoryService;
use common\models\mongo\TicketCollection;
use common\components\ServiceFactory;
use common\models\mysql\Collaborators;

class NotificationCollection extends ActiveRecord 
{
    public static function collectionName()
    {
        return 'NotificationCollection';
    }
    
    public function attributes() {
//        parent::attributes();
        return [
      "_id",      
     "TicketId",
     "ProjectId",
     "NotifiedUser",
     "Notification_Type",
     "ActivityFrom",
     "Status",
     "ActivityOn",
     "NotificationDate",
     "OldValue",
     "NewValue",
     "CommentSlug"
    
   
        ];
    }
    
//    public function behaviors()
//    {
//        return [
//            TimestampBehavior::className(),
//        ];
//    }

    /**
     * @author Ryan Marshal  
     * @description This method is used for getting all the notifications for user.
     * @param type $user
     * @param type $projectId
     * @return array
     */   
    public static function getNotifications($user,$projectId)
    {
        error_log("==in get notifications".$user);
        $msg='';
        $message=array();
        $result_msg=array();
        
        try{
            $query=new Query();
            $query->from('NotificationCollection')
            ->where(["NotifiedUser.UserName" => $user,'ProjectId'=>$projectId,'Status'=>0]);
            $notifications=$query->all();
            error_log("==Notifications==".print_r($notifications,1));
            
            $query=new Query();
            $query->from('NotificationCollection')
            ->where(["NotifiedUser" => $user,'ProjectId'=>$projectId,'Status'=>0,"Notification_Type"=>['mention','comment','reply']]);
            $mentioned_notifications=$query->all();
            
//            $query=NotificationCollection::getCollection();
//            $cursor=$query->find(array('$or'=>array(array('Notification_Type'=>array('$in'=>array('comment','mention')),"NotifiedUser" => $user,'ProjectId'=>(int)$projectId,'Status'=>0))));
//            $mentioned_notifications = iterator_to_array($cursor);
            error_log("==mentioned Notifications==".print_r($mentioned_notifications,1));
            
            //constucting the notifications for the user
            foreach($notifications as $notification)
            {
                $datetime = $notification['NotificationDate']->toDateTime();
                $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                $Date = $datetime->format('M-d-Y H:i:s');
                $selectfields=['Title','TicketId','Fields.planlevel'];
                $ticket_data=ServiceFactory::getStoryServiceInstance()->getTicketDetails($notification['TicketId'],$projectId,$selectfields);
                $ticket_msg='to'. ' '.'#'. $notification['TicketId'] .' ' .$ticket_data['Title'];
               
                error_log("==Notification Type==".$notification['Notification_Type']);
                  
                   /*************** Left Panel Field Values newly assigned *********************/
                    if($notification['Notification_Type']=='assignedto') //newly assigned 
                    {
                        $action_user=Collaborators::getCollaboratorById($notification['ActivityOn']); 
                        if($action_user['UserName']==$user)
                        {
                            //for logged in user
                            //Eg : moin.hussain assigned you to ticket #33 
                            $notification['ActivityOn']='You to';
                            $message=array('from'=>$notification['ActivityFrom'],'type'=> Yii::$app->params['assignedTo'],'ActivityOn'=>$notification['ActivityOn'],'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'id'=>$notification['_id'],'PlanLevel'=>$ticket_data['StoryType']['Id'],'Profile'=>$collaborator['ProfilePic']);
                        }
                        else
                        {
                            //for other followers
                            if($notification['ActivityFrom']!=$user)
                            {
                                //Eg : moin.hussain assigned sateesh.mandru to Ticket #33
                                //$msg=$notification['ActivityFrom'] .' '. Yii::$app->params['assignedTo'] .' '.$action_user['UserName'].' '.$ticket_msg;
                                $message=array('from'=>$notification['ActivityFrom'],'type'=> Yii::$app->params['assignedTo'],'ActivityOn'=>$action_user['UserName'],'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'id'=>$notification['_id'],'PlanLevel'=>$ticket_data['StoryType']['Id'],'Profile'=>$collaborator['ProfilePic']);
                            }
                        }
                         
                    }
                    
                    if($notification['Notification_Type']=='stakeholder') //newly assigned 
                    {
                        $action_user=Collaborators::getCollaboratorById($notification['ActivityOn']); 
                        if($action_user['UserName']==$user)
                        {
                            //for Logged In User
                            //Eg : moin.hussain assigned you to ticket #33 as a stakeholder
                            $notification['ActivityOn']='You to';
                            $message=array('from'=>$notification['ActivityFrom'],'type'=> Yii::$app->params['assignedTo'],'ActivityOn'=>$notification['ActivityOn'],'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'stakeholder'=>Yii::$app->params['stakeholder'],'id'=>$notification['_id'],'PlanLevel'=>$ticket_data['StoryType']['Id'],'Profile'=>$collaborator['ProfilePic']);
                        }
                        else
                        {
                            //for other followers
                            //Eg : moin.hussain assigned sateesh.mandru to ticket #33 as a stakeholder
                            if($notification['ActivityFrom']!=$user)
                            {
                                $message=array('from'=>$notification['ActivityFrom'],'type'=> Yii::$app->params['assignedTo'],'ActivityOn'=>$action_user['UserName'],'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'stakeholder'=>Yii::$app->params['stakeholder'],'id'=>$notification['_id'],'PlanLevel'=>$ticket_data['StoryType']['Id'],'Profile'=>$collaborator['ProfilePic']);
                        
                            }
                        }
                    }
                    if($notification['Notification_Type']=='duedate' || $notification['Notification_Type']=='dod' || $notification['Notification_Type']=='estimatedpoints')
                    {
                         if($notification['ActivityFrom']!=$user)
                            {
                             //Eg : moin.hussain set duedate to 'apr-14-2017'
                             if($notification['Notification_Type']=='duedate')
                             {
                                 $datetime = $notification['ActivityOn']->toDateTime();
                                 $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                                 $duedate = $datetime->format('M-d-Y');
                                 
                                 $message=array('from'=>$notification['ActivityFrom'],'type'=> Yii::$app->params['setduedate'],'ActivityOn'=>$duedate,'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'status'=>'set','id'=>$notification['_id'],'PlanLevel'=>$ticket_data['StoryType']['Id'],'Profile'=>$collaborator['ProfilePic']);
                             }
                             else
                             {
                                 $notification['Notification_Type']=Yii::$app->params['set'].' '.$notification['Notification_Type'].' '.'to';
                                 $message=array('from'=>$notification['ActivityFrom'],'type'=> $notification['Notification_Type'],'ActivityOn'=>$notification['ActivityOn'],'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'status'=>'set','id'=>$notification['_id'],'PlanLevel'=>$ticket_data['StoryType']['Id'],'Profile'=>$collaborator['ProfilePic']);
                             }
                             
                            }
                    }
                    
                    /* Left Panel newly assigned Field Values End */
                    
                    /*Left Panel Changed Field Values Start*/
                    
                    if($notification['Notification_Type']=='changed') //changed fields in the left panel
                    {
                        
                            //changed from in assignTo field
                            if($notification['ActivityOn']=='assignedto')
                            {
                                    
                                    //$msg=$notification['ActivityFrom'].' '. $notification['Notification_Type'].' '. $notification['ActivityOn'].' '.'from'.' '.$notification['OldValue']['UserName'].' '.'to'.' '.$notification['NewValue']['UserName'].' '.'for'.' '. $ticket_msg;
                                if($notification['ActivityFrom']!=$user && $notification['OldValue']['UserName']!='')
                                {
                                   
                                    if($notification['NewValue']['UserName']==$user)
                                    {
                                        //Eg : moin.hussain assigned you to Ticket #33
                                        $notification['ActivityOn']='You to';
                                        $message=array('from'=>$notification['ActivityFrom'],'type'=> Yii::$app->params['assignedTo'],'ActivityOn'=>$notification['ActivityOn'],'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'id'=>$notification['_id'],'PlanLevel'=>$ticket_data['StoryType']['Id'],'Profile'=>$collaborator['ProfilePic']);
                                    }
                                    else
                                    {
                                         //Eg: moin.hussain changed assignedTo from madan.ongole to praveen.peddinti
                                        $message=array('from'=>$notification['ActivityFrom'],'type'=> $notification['Notification_Type'],'ActivityOn'=>$notification['ActivityOn'],'Old'=>$notification['OldValue']['UserName'],'New'=>$notification['NewValue']['UserName'],'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'id'=>$notification['_id'],'PlanLevel'=>$ticket_data['StoryType']['Id'],'Profile'=>$collaborator['ProfilePic']);
                                    }
                                }
                            }
                            else if($notification['ActivityOn']=='stakeholder')
                            {
                                    if($notification['NewValue']['UserName']==$user && $notification['OldValue']['UserName']!='')
                                    {
                                        
                                        //for Logged In User
                                        //Eg : moin.hussain assigned you to ticket #33 as a stakeholder
                                         $notification['ActivityOn']='You to';
                                         $message=array('from'=>$notification['ActivityFrom'],'type'=> Yii::$app->params['assignedTo'],'ActivityOn'=>$notification['ActivityOn'],'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'stakeholder'=>Yii::$app->params['stakeholder'],'id'=>$notification['_id'],'PlanLevel'=>$ticket_data['StoryType']['Id'],'Profile'=>$collaborator['ProfilePic']);
                                    }
                                    else
                                    {
                                         //Eg: moin.hussain changed stakeholder from madan.ongole to praveen.peddinti
                                        $message=array('from'=>$notification['ActivityFrom'],'type'=> $notification['Notification_Type'],'ActivityOn'=>$notification['ActivityOn'],'Old'=>$notification['OldValue']['UserName'],'New'=>$notification['NewValue']['UserName'],'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'id'=>$notification['_id'],'PlanLevel'=>$ticket_data['StoryType']['Id'],'Profile'=>$collaborator['ProfilePic']);
                                    }
                            }
                            else
                            {
                                // for other left panel fields
                                if($notification['OldValue']!='')
                                {
                                    
                                    //$msg=$notification['ActivityFrom'].' '. $notification['Notification_Type'].' '. $notification['ActivityOn'].' '.'from'.' '.$notification['OldValue'].' '.'to'.' '.$notification['NewValue'].' '.'for'.' '. $ticket_msg;
                                    if($notification['ActivityFrom']!=$user)
                                    {
                                       //Eg : moin.hussain changed Priority from Low to High
                                        $message=array('from'=>$notification['ActivityFrom'],'type'=> $notification['Notification_Type'],'ActivityOn'=>$notification['ActivityOn'],'Old'=>$notification['OldValue'],'New'=>$notification['NewValue'],'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'id'=>$notification['_id'],'PlanLevel'=>$ticket_data['StoryType']['Id'],'Profile'=>$collaborator['ProfilePic']);
                                    }
                                }
//                                else //old value is null i.e The field is newly set
//                                {
//                                    if($notification['ActivityFrom']!=$user)
//                                    {
//                                       //Eg : moin.hussain set Priority to High
//                                        $notification['Notification_Type']="set".$notification['Notification_Type']."to";
//                                        $message=array('from'=>$notification['ActivityFrom'],'type'=> $notification['Notification_Type'],'ActivityOn'=>$notification['ActivityOn'],'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date);
//                                    }
//                                }
                            }  
                    }
                    
                    /*********Left Panel  Changed Field Values End *******************/
                    
                    /********* Followers Messages *****************/
                    
                    if($notification['Notification_Type']=='added')
                    {
                        
                        $action_user=Collaborators::getCollaboratorById($notification['ActivityOn']);
                        if($action_user['UserName']==$user) //if logged in user has been added
                        {
                            //Eg : moin.hussain added you as a follower to ticket #33
                            $msg=$user .  Yii::$app->params['addedyou'].Yii::$app->params['follower'] .$ticket_msg;
                        }
                        else
                        {
                            //Eg : moin.hussain added sateesh.mandru as a follower to Ticket #33
                            $msg=$user .  Yii::$app->params['added'] .$notification['NotifiedUser.UserName'] .Yii::$app->params['follower'].$ticket_msg;
                        }
                    }
                    
                    if($notification['Notification_Type']=='removed')
                    {
                        $action_user=Collaborators::getCollaboratorById($notification['ActivityOn']);
                        if($action_user['UserName']==$user) //if logged in user has been added
                        {
                            //Eg : moin.hussain removed you as a follower to ticket #33
                            $msg=$user .  Yii::$app->params['removedyou'] .Yii::$app->params['follower'] .$ticket_msg;
                        }
                        else
                        {
                            //Eg : moin.hussain removed sateesh.mandru as a follower to Ticket #33
                            $msg=$user .  Yii::$app->params['removed'] .$notification['NotifiedUser.UserName'] .Yii::$app->params['follower'].$ticket_msg;
                        }
                    }
                    if($notification['Notification_Type']=='followed' || $notification['Notification_Type']=='unfollowed')
                    {
                        $action_user=Collaborators::getCollaboratorById($notification['ActivityOn']);
                        $msg=$action_user['UserName'] .  $notification['Notification_Type'] .$ticket_msg;
                    }
                    //array_push($result_msg,$msg);
                    if(!empty($message))
                    {
                        array_push($result_msg,$message);
                    }
            } 
                    
                    /******* Followers Message End **********/
                    
                    
                    /***** Any changes in Editor ***********/
                    foreach($mentioned_notifications as $notification)
                    {
                        $datetime = $notification['NotificationDate']->toDateTime();
                        $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                        $Date = $datetime->format('M-d-Y H:i:s');
                        $collaborator=new Collaborators();
                        $collaborator=$collaborator->getCollaboratorByUserName($notification['ActivityFrom']);
                        error_log("===Collaborator==".print_r($collaborator,1));                       
                        $selectfields=['Title','TicketId','Fields.planlevel'];
                        $ticket_data=ServiceFactory::getStoryServiceInstance()->getTicketDetails($notification['TicketId'],$projectId,$selectfields);
                        
                        if($notification['Notification_Type']=='comment')
                        {
                         //Eg : moin.hussain commented on #33 Ticket 
                             if($notification['ActivityFrom']!=$user)
                             {
                              $message=array('from'=>$notification['ActivityFrom'],'type'=>Yii::$app->params['comment'],'Slug'=>$notification['CommentSlug'],'date'=>$Date,'id'=>$notification['_id'],'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'PlanLevel'=>$ticket_data['StoryType']['Id'],'Profile'=>$collaborator['ProfilePic']);
                             }
                             
                        }
                        if($notification['Notification_Type']=='reply')
                        {
                            //Eg: moin.hussain replied on #33 Ticket
                            if($notification['ActivityFrom']!=$user)
                            {
                             $message=array('from'=>$notification['ActivityFrom'],'type'=>Yii::$app->params['reply'],'Slug'=>$notification['CommentSlug'],'date'=>$Date,'id'=>$notification['_id'],'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'PlanLevel'=>$ticket_data['StoryType']['Id'],'Profile'=>$collaborator['ProfilePic']);
                        
                            }
                        }
                        if($notification['Notification_Type']=='mention')
                        {
                            error_log("==in mention==");
                            if($notification['ActivityFrom']!=$user)
                            {
                                if($notification['NotifiedUser']==$user)
                                {
                                    //Eg : moin.hussain mentioned you in a comment or
                                    //     moin.hussain mentioned you in a reply
                                    //     moin.hussain mentined you on Ticket #33
                                    $notification['NotifiedUser']='You';
                                    $message=array('from'=>$notification['ActivityFrom'],'type'=> Yii::$app->params['mention'],'id'=>$notification['_id'],'Slug'=>$notification['CommentSlug'],'ActivityOn'=>$notification['NotifiedUser'],'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'PlanLevel'=>$ticket_data['StoryType']['Id'],'Profile'=>$collaborator['ProfilePic']);
                                }
                                else
                                {
                                    //Eg : moin.hussain mentioned madan.ongole in a comment or
                                    //     moin.hussain mentioned madan.ongole in a reply
                                    //     moin.hussain mentined madan.ongole on Ticket #33
                                    $message=array('from'=>$notification['ActivityFrom'],'type'=> Yii::$app->params['mention'],'id'=>$notification['_id'],'Slug'=>$notification['CommentSlug'],'ActivityOn'=>$notification['NotifiedUser'],'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'PlanLevel'=>$ticket_data['StoryType']['Id'],'Profile'=>$collaborator['ProfilePic']);
                                }
                            }
                        }
                         if(!empty($message))
                        {
                            array_push($result_msg,$message);
                        }
                   } 
                    /**** Changes in Editor End *************/
                    
                         
                
                
                error_log("==Result Msg==".print_r($result_msg,1));
            //}
           // return $notifications;
            return $result_msg;
        } catch (Exception $ex) {

        }
    }
    
    /**
     * @author Ryan Marshal
     * @param type $notification_data
     * @return type
     * @description  Used for saving the notifications for assignedTo,Add/Remove Followers,stakeholder and left panel property changes  
     */
    public static function saveNotifications($notification_data,$notifyType,$collaborator_name,$project=null)
    {
        error_log("in save notifications");
        error_log("===status==".$notifyType);
        try{
            //$collaborator='';
            //$activity='';
            $oldvalue='';
            $newvalue='';
            if($project==null)
            {
                //For Story Detail Page Use Case.....
                $ticketId=$notification_data->TicketId;
                $projectId=$notification_data->projectId;
            }
            else
            {
                //For Story Edit Page Use Case.....since params are passed differently in story edit
                $ticketId=$notification_data->data->TicketId;
                $projectId=$project;
            }
            $from=$notification_data->userInfo->username;
            $loggedInUser=$notification_data->userInfo->Id;
            //$notify_type=$notification_data->comment_type;
            $notify_type=$notifyType;
            $currentDate = new \MongoDB\BSON\UTCDateTime(time() * 1000);
            
            $data = ServiceFactory::getStoryServiceInstance()->getTicketDetails($ticketId,$projectId);
            error_log("==Follower Data==".print_r($data,1));
            if(!($notify_type=='added' || $notify_type=='removed'))
            {
                //$activityData=ServiceFactory::getStoryServiceInstance()->saveActivity($notification_data->TicketId,$notification_data->projectId,$notifyType,$collaborator_name,$loggedInUser);
                $activityData=ServiceFactory::getStoryServiceInstance()->saveActivity($ticketId,$projectId,$notifyType,$collaborator_name,$loggedInUser);
                
                $oldvalue=$activityData['data']['PreviousValue'];
                $newvalue=$activityData['data']['NewValue'];
                
            }
            $followers=$data['Followers'];
            $tic = new NotificationCollection();
            //if($notify_type=='priority' || $notify_type=='bucket' || $notify_type=='workflow') //checking the left side 
            if($oldvalue!='')
            {
                
                $activity=$notify_type;
                $notify_type='changed';   
                
            }
            else
            {
                $collaborator=$collaborator_name;
                //$notify_type=$notification_data->comment_type;
                $tic->TicketId =$ticketId;
                $tic->ProjectId =$projectId;
                $tic->NotifiedUser=$collaborator;
                $tic->Notification_Type=$notify_type;
                $tic->ActivityFrom=$from;
                $tic->NotificationDate=$currentDate;
                $tic->Status=0;
                $result = $tic->save();
            }
            
            
            /* notification for all followers and the stakeholders */
            
            foreach($followers as $follower)
            {
//                if($follower['UserName']!=$collaborator_name)
//                {
               
                    $tic = new NotificationCollection();
                    $tic->NotifiedUser=$follower;
                    $tic->TicketId =$ticketId;
                    $tic->ProjectId =$projectId;
                    if($notify_type=='changed')
                    {
                        $tic->ActivityOn=$activity;
                        $tic->OldValue=$oldvalue; 
                        $tic->NewValue=$newvalue; 
                    }
                    else
                    {
                        $tic->ActivityOn=$collaborator_name;
                    }
                    $tic->ActivityFrom=$from;
                    $tic->NotificationDate=$currentDate;
                    
                    if($notify_type=='added')
                    {
                        $tic->Notification_Type='followed';
                    }
                    else if($notify_type=='removed')
                    {
                        $tic->Notification_Type='unfollowed';
                    }
                    else
                    {
                        $tic->Notification_Type=$notify_type;
                    }
//                    if($notify_type=='assignedto')
//                    {
//                        $tic->Notification_Type=$notify_type;
//                    }
//                    if($notify_type=='stakeholder')
//                    {
//                        $tic->Notification_Type=$notify_type;
//                    }
                    $tic->Status=0;
                    $tic->save();
                //}
            }
            
        } catch (Exception $ex) {
            Yii::log("NotificationsCollection:saveNotifications::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
    /**
     * @author Ryan Marshal
     * @param type $property
     * @return type 
     * @description  Used for saving the notifications in submit Description and comments  
     */
    public static function saveNotificationsWithMention($ticket_data,$userslist,$notifyType=null,$slug=null)
    {
        try
        {
            $from=$ticket_data->userInfo->username;
            $ticketId=$ticket_data->TicketId;
            $projectId=$ticket_data->projectId;
            $currentDate = new \MongoDB\BSON\UTCDateTime(time() * 1000);
            if(!empty($userslist))
            {
                foreach($userslist as $user)
                {
                    error_log("====Notification with mention==".$user);
                    $tic = new NotificationCollection();
                    $tic->TicketId =$ticketId;
                    $tic->ProjectId =$projectId;
                    $tic->NotifiedUser=$user;
                    $tic->ActivityFrom=$from;
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
                     $tic->ActivityOn=$notifyType;
                     $tic->Status=0;
                     $tic->CommentSlug=$slug;
                    $tic->save();
                }
            }
        }catch(Exception $ex)
        {
            Yii::log("NotificationCollection:saveNotificationsWithMention::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
    /**
     * @author Ryan Marshal
     * @param type $property
     * @return type 
     * @description  Used for saving the notifications for comments and reply 
     */
    public static function saveNotificationsForComment($commentData,$notify_type,$slug)
    {
        try
        {
            error_log("in comment".$notify_type);
            $from=$commentData->userInfo->username;
            $ticketId=$commentData->TicketId;
            $projectId=$commentData->projectId;
            $data = ServiceFactory::getStoryServiceInstance()->getTicketDetails($ticketId,$projectId);
            $followers=$data['Followers'];
            $currentDate = new \MongoDB\BSON\UTCDateTime(time() * 1000);
            foreach($followers as $follower)
                {
                    if($follower['UserName']!=$from)
                    {
                            $tic = new NotificationCollection();
                            $tic->NotifiedUser=$follower['UserName'];
                            $tic->TicketId =$ticketId;
                            $tic->ProjectId =$projectId;
                            $tic->ActivityFrom=$from;
                            $tic->NotificationDate=$currentDate;
                            $tic->Notification_Type=$notify_type;
                            $tic->CommentSlug=$slug;
                            $tic->Status=0;
                            $tic->save();
                    }

                }
        }catch(Exception $ex)
        {
            Yii::log("NotificationCollection:saveNotificationsForComment::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
    /**
     * @author Ryan Marshal
     * @param type $notifyid,$user
     * @return type 
     * @description  Used for deleting the notification 
     */
    public static function deleteNotification($notify)
    {
        error_log("==in delete notification==");
        $user=$notify->userInfo->username;
        error_log("==user==".$user);
        $notifyid=$notify->notifyid;
        try
        {
          
            $notifications=NotificationCollection::getCollection();
            $notifications->update(array('_id'=>$notifyid,'NotifiedUser'=>$user), array("Status"=>1));
            return;
        }catch(Exception $ex)
        {
            Yii::log("NotificationCollection:deleteNotification::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
    /**
     * @author Ryan Marshal
     * @param type $notifyid,$user
     * @return type 
     * @description  Used for deleting all notifications of the user 
     */
    public static function deleteAllNotifications($notify)
    {
        
        $user=$notify->userInfo->username;
        
        try
        {
            
            $notification=NotificationCollection::getCollection();
            foreach($notification as $notify)
            {
                $notifyid=$notify->notifyid;
                $db->update(array('_id'=>$notifyid,'NotifiedUser'=>$user), array("Status"=>1));
            }
            return;
        }catch(Exception $ex)
        {
            Yii::log("NotificationCollection:deleteNotification::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
}