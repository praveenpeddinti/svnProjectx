<?php
namespace common\service;
use common\models\mongo\{TicketCollection,NotificationCollection};

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
    public static function saveNotificationsForComment($commentData,$notify_type,$slug)
    {
        try
        {
            error_log("in comment".$notify_type);
            $loggedinUser=$commentData->userInfo->Id;
            $ticketId=$commentData->TicketId;
            $projectId=$commentData->projectId;
            $data = TicketCollection::getTicketDetails($ticketId,$projectId);
            $followers=$data['Followers'];
            $currentDate = new \MongoDB\BSON\UTCDateTime(time() * 1000);
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
            Yii::log("NotificationTrait:saveNotificationsForComment::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
    
    
} 