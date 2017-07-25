import { Component,OnInit,HostListener } from '@angular/core';
import { Router,ActivatedRoute } from '@angular/router';
import {AuthGuard} from '../../services/auth-guard.service';
import { AjaxService } from '../../ajax/ajax.service';
import {SharedService} from '../../services/shared.service';
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

    getAllNotification(page){
    this.shared.change(this._router.url,null,'Notifications','Other',''); //added for breadcrumb purpose
    var post_data={viewAll:1,page:page};
    this._ajaxService.NodeSubscribe('/getAllNotifications',post_data,(data)=>
      {
      console.log("--leing-----viewAllNotifications--"+data.notify_result.length);
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

    deleteNotification(project,notify_id,event,domPosition) 
  {
    // jQuery("#notifications_list").hide();
    // event.stopPropagation();
    //ajax call for delete notificatin
    var post_data={'projectId':project.PId,'notifyid':notify_id,viewAll:1,page:this.pageNo};
    this._ajaxService.AjaxSubscribe('story/delete-notification',post_data,(data)=>
    {
      if(data)
      {
          this.notify_count=data.totalCount;
          this.shared.changeNotificationCount(data.totalCount);
       if(data.data.notify_result != "nodata"){ 
         this.allNotification[domPosition].IsSeen = 1;
       }
       
      }
    })


  }

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
  markAllRead()
  {
    var post_data={};
    this._ajaxService.AjaxSubscribe('story/delete-notifications',post_data,(data)=>
    {
      if(data)
      {
        this.notify_count=0;
       this.allNotification.forEach(function (value) {
        value.IsSeen = 1;
      });

      }
    })
  }

 @HostListener('window:scroll', ['$event']) 
    loadNotificationsOnScroll(event) {
     // console.debug("Scroll Event", window.pageYOffset );
      if (this.allNotification.length > 0 && jQuery(window).scrollTop() == jQuery(document).height() - jQuery(window).height()) {

          this.pageNo +=1; 
          this.getAllNotification(this.pageNo);     
      }
    }
}