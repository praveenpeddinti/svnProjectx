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
//use yii\db\ActiveRecord;
use yii\mongodb\ActiveRecord;
use yii\mongodb\Query;
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
     "CommentSlug",
    
   
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
    
    
     public static function getNotificationsCount($user,$projectId)
    {
          $query=new Query();
            $query->from('NotificationCollection')
            ->where(["NotifiedUser" =>(int) $user,'ProjectId'=>(int)$projectId,'Status'=>(int) 0])
            ->andWhere(['!=','ActivityFrom', (int)$user]);
            $notificationsCount=$query->count();
            error_log("getNotificationsCount----".$notificationsCount);
            return $notificationsCount;
     }
    
      public static function getNotifications($user,$projectId,$offset=0,$limit=5)
    {
         $query=new Query();
            $query->from('NotificationCollection')
            ->where(["NotifiedUser" =>(int) $user,'ProjectId'=>(int)$projectId,'Status'=>(int) 0])
            ->andWhere(['!=','ActivityFrom', (int)$user])
            ->orderBy(["_id"=>SORT_DESC])
             ->offset($offset)      
            ->limit($limit);
            $notifications=$query->all();
            return $notifications;
     }
    
   
    
    /**
     * @author Ryan Marshal
     * @param type $notification_data
     * @return type
     * @description  Used for saving the notifications for assignedTo,Add/Remove Followers,stakeholder and left panel property changes  
     */
    public static function saveNotifications($notification_data,$notifyType,$activityOn,$fieldType="")
    {
        error_log("in save notifications--".$notifyType."----------".$activityOn."--------".$fieldType);
        try{
            
            $oldValue='';
            $newValue='';
            $oldCollaborator='';
            $newCollaborator='';
                //For Story Detail Page Use Case.....
            $ticketId=$notification_data->TicketId;
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
                            $tic->save();
                        //}
                    }
                     else if($fieldType== "FollowObj")
                    {
                         if($loggedInUser == $activityOn){
                             $notification_Type = ($notifyType=='add') ? 'followed' : 'unfollowed';
                             
                         }else{
                             $notification_Type =  ($notifyType=='add') ? 'added' : 'removed';
                         }
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
                            $tic->save();
                        //}
                    } 
                    else
                    {
                        $oldFieldId=$oldValue;
                        $newFieldId=$activityOn;
                        error_log("==Old Value==".$oldFieldId);
                        error_log("==New Value==".$newFieldId);
                        
                        $newValue=ServiceFactory::getStoryServiceInstance()->getFieldChangeValue($notifyType,$newFieldId);
                        error_log("==new value name==".$newValue);
                        if($oldValue!='') //if changed
                        {
                            $activityOn='changed';
                            $oldValue=ServiceFactory::getStoryServiceInstance()->getFieldChangeValue($notifyType,$oldFieldId);
                           
                        }
                        else //if set new value
                        {
                            $activityOn='set';
                            $oldValue='';
                        }
                    }
                    
            

            /* notification for all followers and the stakeholders */
            
            foreach($followers as $follower)
            {
               error_log("===Notify Type".$notify_type);
              if($follower['FollowerId'] == $loggedInUser){
                
                  continue;
              }
                    $tic = new NotificationCollection();
                    $tic->NotifiedUser=(int)$follower['FollowerId'];
                    $tic->TicketId =$ticketId;
                    $tic->ProjectId =$projectId;
                    $tic->ActivityOn=$notify_type; // new use case "ActivityOn" will be Field Name
                    $tic->ActivityFrom=(int)$loggedInUser;
                    $tic->NotificationDate=$currentDate;
                    
                    if($fieldType== "FollowObj"){
                        
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
                         $tic->save();
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
                            $tic->save();
                        }
                    }
                    else
                    {
                        $tic->Notification_Type=$activityOn;
                        $tic->Status=0;
                        $tic->OldValue=$oldValue;
                        $tic->NewValue=$newValue;
                        $tic->save();
                    }
                    
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
             $loggedinUser=$ticket_data->userInfo->Id;
            $ticketId=$ticket_data->TicketId;
            $projectId=$ticket_data->projectId;
            $currentDate = new \MongoDB\BSON\UTCDateTime(time() * 1000);
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
                    $tic->save();
                }
            }
        }catch(Exception $ex)
        {
            Yii::log("NotificationCollection:saveNotificationsWithMention::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
  /**
   * 
   * @param type $commentData
   * @param type $notify_type //comment,reply
   * @param type $slug
   */
    public static function saveNotificationsForComment($commentData,$notify_type,$slug)
    {
        try
        {
            error_log("in comment".$notify_type);
            error_log("==comment data==");
            $commentOwner=$commentData->Comment->OrigianalCommentorId; //added by Ryan for reply
            error_log("==comment owner==".$commentOwner);
            $loggedinUser=$commentData->userInfo->Id;
            $ticketId=$commentData->TicketId;
            $projectId=$commentData->projectId;
            $followers= TicketCollection::getTicketDetails($ticketId,$projectId,['Followers']);
            $currentDate = new \MongoDB\BSON\UTCDateTime(time() * 1000);
            
            //For Reply....
            if($commentOwner!='')
            {
                $tic = new NotificationCollection();
                $tic->NotifiedUser=(int)$commentOwner;
                $tic->TicketId =$ticketId;
                $tic->ProjectId =$projectId;
                $tic->ActivityFrom=(int)$loggedinUser;
                $tic->NotificationDate=$currentDate;
                $tic->Notification_Type=$notify_type;
                $tic->CommentSlug=$slug;
                $tic->Status=0;
                $tic->CommentOwner=(int)$commentOwner;
                $tic->save();
            }
            
            foreach($followers as $follower)
                {
                    if($follower['FollowerId']!=$loggedinUser)
                    {
                            $tic = new NotificationCollection();
                            $tic->NotifiedUser=(int)$follower['FollowerId'];
                            $tic->TicketId =$ticketId;
                            $tic->ProjectId =$projectId;
                            $tic->ActivityFrom=(int)$loggedinUser;
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
        $user=$notify->userInfo->Id;
        error_log("==user==".$user);
        $notifyid=$notify->notifyid;
        try
        {
          
            $notifications=NotificationCollection::getCollection();
            $notifications->update(array('_id'=>$notifyid,'NotifiedUser'=>(int)$user), array("Status"=>1));
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
        
        $user=$notify->userInfo->Id;
        
        try
        {
            
            $notification=NotificationCollection::getCollection();
            foreach($notification as $notify)
            {
                
                $notification->update(array('NotifiedUser'=>(int)$user), array("Status"=>1));
            }
            return;
        }catch(Exception $ex)
        {
            Yii::log("NotificationCollection:deleteNotification::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
}