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
     "NewValue"
    
   
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
            ->where(["NotifiedUser" => $user,'ProjectId'=>$projectId,'Status'=>0]);
            $mentioned_notifications=$query->all();
            error_log("==mentioned Notifications==".print_r($mentioned_notifications,1));
            
            //constucting the notifications for the user
            foreach($notifications as $notification)
            {
                $datetime = $notification['NotificationDate']->toDateTime();
                $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                $Date = $datetime->format('M-d-Y');
                $ticket_data=ServiceFactory::getStoryServiceInstance()->getTicketDetails($notification['TicketId'],$projectId);
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
                            $message=array('from'=>$notification['ActivityFrom'],'type'=> Yii::$app->params['assignedTo'],'ActivityOn'=>$notification['ActivityOn'],'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'id'=>$notification['_id']);
                        }
                        else
                        {
                            //for other followers
                            if($notification['ActivityFrom']!=$user)
                            {
                                //Eg : moin.hussain assigned sateesh.mandru to Ticket #33
                                //$msg=$notification['ActivityFrom'] .' '. Yii::$app->params['assignedTo'] .' '.$action_user['UserName'].' '.$ticket_msg;
                                $message=array('from'=>$notification['ActivityFrom'],'type'=> Yii::$app->params['assignedTo'],'ActivityOn'=>$action_user['UserName'],'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'id'=>$notification['_id']);
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
                            $message=array('from'=>$notification['ActivityFrom'],'type'=> Yii::$app->params['assignedTo'],'ActivityOn'=>$notification['ActivityOn'],'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'stakeholder'=>Yii::$app->params['stakeholder'],'id'=>$notification['_id']);
                        }
                        else
                        {
                            //for other followers
                            //Eg : moin.hussain assigned sateesh.mandru to ticket #33 as a stakeholder
                            if($notification['ActivityFrom']!=$user)
                            {
                                $message=array('from'=>$notification['ActivityFrom'],'type'=> Yii::$app->params['assignedTo'],'ActivityOn'=>$action_user['UserName'],'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'stakeholder'=>Yii::$app->params['stakeholder'],'id'=>$notification['_id']);
                        
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
                                 
                                 $message=array('from'=>$notification['ActivityFrom'],'type'=> Yii::$app->params['setduedate'],'ActivityOn'=>$duedate,'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'status'=>'set','id'=>$notification['_id']);
                             }
                             else
                             {
                                 $notification['Notification_Type']=Yii::$app->params['set'].' '.$notification['Notification_Type'].' '.'to';
                                 $message=array('from'=>$notification['ActivityFrom'],'type'=> $notification['Notification_Type'],'ActivityOn'=>$notification['ActivityOn'],'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'status'=>'set','id'=>$notification['_id']);
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
                                        $message=array('from'=>$notification['ActivityFrom'],'type'=> Yii::$app->params['assignedTo'],'ActivityOn'=>$notification['ActivityOn'],'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'id'=>$notification['_id']);
                                    }
                                    else
                                    {
                                         //Eg: moin.hussain changed assignedTo from madan.ongole to praveen.peddinti
                                        $message=array('from'=>$notification['ActivityFrom'],'type'=> $notification['Notification_Type'],'ActivityOn'=>$notification['ActivityOn'],'Old'=>$notification['OldValue']['UserName'],'New'=>$notification['NewValue']['UserName'],'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'id'=>$notification['_id']);
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
                                         $message=array('from'=>$notification['ActivityFrom'],'type'=> Yii::$app->params['assignedTo'],'ActivityOn'=>$notification['ActivityOn'],'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'stakeholder'=>Yii::$app->params['stakeholder'],'id'=>$notification['_id']);
                                    }
                                    else
                                    {
                                         //Eg: moin.hussain changed stakeholder from madan.ongole to praveen.peddinti
                                        $message=array('from'=>$notification['ActivityFrom'],'type'=> $notification['Notification_Type'],'ActivityOn'=>$notification['ActivityOn'],'Old'=>$notification['OldValue']['UserName'],'New'=>$notification['NewValue']['UserName'],'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'id'=>$notification['_id']);
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
                                        $message=array('from'=>$notification['ActivityFrom'],'type'=> $notification['Notification_Type'],'ActivityOn'=>$notification['ActivityOn'],'Old'=>$notification['OldValue'],'New'=>$notification['NewValue'],'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date,'id'=>$notification['_id']);
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
            } 
                    
                    /******* Followers Message End **********/
                    
                    
                    /***** Any changes in Editor ***********/
                    foreach($mentioned_notifications as $notification)
                    {
                        $datetime = $notification['NotificationDate']->toDateTime();
                        $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                        $Date = $datetime->format('M-d-Y');
                        $ticket_data=ServiceFactory::getStoryServiceInstance()->getTicketDetails($notification['TicketId'],$projectId);
                        if($notification['Notification_Type']=='comment')
                        {
                         //Eg : moin.hussain commented on #33 Ticket   
                                $msg=$notification['ActivityFrom'].Yii::$app->params['comment'] .$ticket_msg;

                        }
                        if($notification['Notification_Type']=='reply')
                        {
                            //Eg: moin.hussain replied on #33 Ticket
                             $msg=$notification['ActivityFrom'].Yii::$app->params['reply'] .$ticket_msg;
                        }
                        if($notification['Notification_Type']=='mention')
                        {
                            if($notification['ActivityFrom']!=$user)
                            {
                                error_log("==Notified User==".$notification['NotifiedUser']);
                                //Eg : moin.hussain mentioned Priority from Low to High
                                if($notification['NotifiedUser']==$user)
                                {
                                    $notification['NotifiedUser']='You';
                                    $message=array('from'=>$notification['ActivityFrom'],'type'=> Yii::$app->params['mention'],'ActivityOn'=>$notification['NotifiedUser'],'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date);
                                }
                                else
                                {
                                    $message=array('from'=>$notification['ActivityFrom'],'type'=> Yii::$app->params['mention'],'ActivityOn'=>$notification['NotifiedUser'],'Title'=>$ticket_data['Title'],'TicketId'=>$notification['TicketId'],'date'=>$Date);
                                }
                            }
                        }
                   } 
                    /**** Changes in Editor End *************/
                    
                         
                
                //array_push($result_msg,$msg);
                    if(!empty($message))
                    {
                        array_push($result_msg,$message);
                    }
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
    public static function saveNotificationsWithMention($ticket_data,$userslist,$notifyType=null)
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
    public static function saveNotificationsForComment($commentData,$notify_type)
    {
        try
        {
            error_log("in comment");
            $from=$commentData->userInfo->username;
            $ticketId=$commentData->TicketId;
            $projectId=$commentData->projectId;
            $data = ServiceFactory::getStoryServiceInstance()->getTicketDetails($ticketId,$projectId);
            $followers=$data['Followers'];
            $currentDate = new \MongoDB\BSON\UTCDateTime(time() * 1000);
            foreach($followers as $follower)
                {
                error_log("==Follower==".$follower['UserName']);
                    if($follower['UserName']!=$from)
                    {
                            $tic = new NotificationCollection();
                            $tic->NotifiedUser=$follower['UserName'];
                            $tic->TicketId =$ticketId;
                            $tic->ProjectId =$projectId;
                            $tic->ActivityFrom=$from;
                            $tic->NotificationDate=$currentDate;
                            $tic->Notification_Type=$notify_type;
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
     * @description  Used for deleting the notifications 
     */
    public static function deleteNotification($notify)
    {
        error_log("==in delete notification==");
        $user=$notify->userInfo->username;
        $notifyid=$notify->notifyid;
        try
        {
            error_log("in try");
            $db=NotificationCollection::getCollection();
            $db->findAndModify(array("_id"=>$notifyid,"NotifiedUser.UserName"=>$user),array('$addToSet'=> array("Status"=>1)));
            $db->findAndModify(array("_id"=>$notifyid,"NotifiedUser"=>$user), array('$addToSet'=> array("Status"=>1)));
            error_log("==success==");
        }catch(Exception $ex)
        {
            Yii::log("NotificationCollection:deleteNotification::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
}