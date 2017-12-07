import { Component,OnInit,HostListener,ViewChildren,QueryList } from '@angular/core';
import { Router,ActivatedRoute } from '@angular/router';
import {AuthGuard} from '../../services/auth-guard.service';
import { AjaxService } from '../../ajax/ajax.service';
import {SharedService} from '../../services/shared.service';
import { ConfirmationBoxComponent } from '../../components/utility/confirmation-box/confirmation-box.component';
declare var jQuery:any;

@Component({
   selector: 'search-view',
    templateUrl: 'notification-component.html',
    styleUrls: ['./notification-component.css'],
    providers: [AuthGuard]
})

export class NotificationComponent implements OnInit{
    public searchString="";
    public allNotification=[];
    public notify_count:any=0;
    public stringPosition;
    public pageNo=1;
    private page=1;
    private promptedBoxId;
     @ViewChildren(ConfirmationBoxComponent) confirmationBox: QueryList<ConfirmationBoxComponent>;
    public nomorenotifications:boolean= false;
    public ready=true;
     constructor(
        private _router: Router,
         private _authGuard:AuthGuard,
        private route: ActivatedRoute,
        private _ajaxService: AjaxService,
        private shared:SharedService
        ) {
          

         }

    ngOnInit(){
      this.getAllNotification(this.pageNo);
    }
/**
 * @description To display all notifications count and list of all notifications
 */
    getAllNotification(page){
    this.shared.change(this._router.url,null,'Notifications','Other',''); //added for breadcrumb purpose
    var post_data={viewAll:1,page:page};
    this._ajaxService.NodeSubscribe('/getAllNotifications',post_data,(data)=>
      {
      if(data.notify_result.length >0){
          this.nomorenotifications = false;
          this.notify_count=data.notify_result.length;
           if(this.pageNo==1){
              this.allNotification=[];
       } 
       for(var i=0;i<data.notify_result.length;i++)
        {
         
            this.allNotification.push(data.notify_result[i]);
          
        }
        }else if(this.pageNo >1){
          this.nomorenotifications = true;
        }
     
      });
    }
  /* mark as read function*/
  
   readNotification(project,notify_id,event,domPosition) 
  {
    var post_data={'projectId':project.PId,'notifyid':notify_id,viewAll:1,page:this.pageNo};
    this._ajaxService.AjaxSubscribe('story/read-notification',post_data,(data)=>
    {
      if(data)
      {
       this._ajaxService.SocketSubscribe('getAllNotificationsCount',{});
       if(data.data.notify_result != "nodata"){ 
         this.allNotification[domPosition].IsSeen = 1;
       }
       
      }
    })
  }
   
 
  /*
  confirmation box
  */  
  promptConfirmationBox(domPosition) 
  { 
   var array = this.confirmationBox.toArray();
  
   array.forEach(function (value) {
      value.getDataFromParent(-1);
    });
  
    if(this.promptedBoxId !== domPosition){
         array[domPosition].getDataFromParent(domPosition);
    this.promptedBoxId = domPosition;
    }else{
     this.promptedBoxId = "";
    }
  }
/**
 * @description To delete notification when user delete.
 */
    deleteNotification(project,notify_id,event,domPosition) 
  {
    //ajax call for delete notificatin
    var post_data={'projectId':project.PId,'notifyid':notify_id,viewAll:1,page:this.pageNo};
    this._ajaxService.AjaxSubscribe('story/delete-notification',post_data,(data)=>
    {
      if(data)
      {
          this.notify_count=data.totalCount;
          this.shared.changeNotificationCount(data.totalCount);
         if(data.data.notify_result != "nodata"){
          this.allNotification.splice(domPosition,1); 
         this.allNotification[domPosition].IsSeen = 1;
         if(this.allNotification.length==14){
              this.pageNo =1; 
              this.getAllNotification(this.pageNo); 
          }
      }
       
      }
    })


  }
/**
 * @description Opening the ticket when user click on the notification.
 */
  goToTicket(project,ticketid,notify_id,comment)
  {
    var post_data={'projectId':project.PId,'notifyid':notify_id,viewAll:1,page:this.pageNo};
    this._ajaxService.AjaxSubscribe('story/delete-notification',post_data,(data)=>
    {
      if(data)
      {
       this._router.navigate(['project',project.ProjectName,ticketid,'details'],{queryParams: {Slug:comment}});

      }
    })
    
  }
  /**
  * @description Opening the ticket when user click on the comment notification.
   */
  goToComment(project,ticketid,comment,notify_id)
  {
    var post_data={'projectId':project.PId,'notifyid':notify_id,viewAll:1,page:this.pageNo};
    this._ajaxService.AjaxSubscribe('story/delete-notification',post_data,(data)=>
    {
      if(data)
      {
      this._router.navigate(['project',project.ProjectName,ticketid,'details',{queryParams: {Slug:comment}}]);
 
      }
    })
     
  }
  /**
 * @description Showing all the ticket notifications as read when click on mark all read.
 */
  markAllRead()
  {
    var post_data={};
    this._ajaxService.AjaxSubscribe('story/delete-notifications',post_data,(data)=>
    {
      if(data)
      {
        this.shared.changeNotificationCount(0);
       this.allNotification.forEach(function (value) {
        value.IsSeen = 1;
      });

      }
    })
  }
/**
 * @description Providing scroll to the notifications page.
 */
 @HostListener('window:scroll', ['$event']) 
    loadNotificationsOnScroll(event) {
      if (this.allNotification.length > 0 && jQuery(window).scrollTop() == jQuery(document).height() - jQuery(window).height()) {

          this.pageNo +=1; 
          this.getAllNotification(this.pageNo);     
      }
    }
}