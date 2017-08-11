import { Component,OnInit,AfterViewInit,HostListener ,ViewChildren,QueryList } from '@angular/core';
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
    public nomorenotifications:boolean= false;
    public ready=true;
     @ViewChildren(ConfirmationBoxComponent) confirmationBox: QueryList<ConfirmationBoxComponent>;
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
       window.scrollTo(0,0);
    }
  ngAfterViewInit(){
     console.log("afger view init-----------------",this.confirmationBox);
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

    readNotification(project,notify_id,event,domPosition) 
  {
    // jQuery("#notifications_list").hide();
    // event.stopPropagation();
    //ajax call for delete notificatin
    var post_data={'projectId':project.PId,'notifyid':notify_id,viewAll:1,page:this.pageNo};
    this._ajaxService.AjaxSubscribe('story/read-notification',post_data,(data)=>
    {
      if(data)
      {
         
          //  this.notify_count= this.notify_count - 1;
          //  this.shared.changeNotificationCount(-1);
       this._ajaxService.SocketSubscribe('getAllNotificationsCount',{});
       if(data.data.notify_result != "nodata"){ 
         this.allNotification[domPosition].IsSeen = 1;
       }
       
      }
    })


  }
    promptConfirmationBox(domPosition) 
  { //alert('delete-'+domPosition);
   var array = this.confirmationBox.toArray();
   array.forEach(function (value) {
      value.getDataFromParent(-1);
      });
     array[domPosition].getDataFromParent(domPosition);
    


  }
   deleteNotification(project,notify_id,event,domPosition) 
  { 
     var post_data={'projectId':project.PId,'notifyid':notify_id,viewAll:1,page:this.pageNo};
    this._ajaxService.AjaxSubscribe('story/delete-notification',post_data,(data)=>
    {
      if(data)
      {
         this._ajaxService.SocketSubscribe('getAllNotificationsCount',{});
       if(data.data.notify_result != "nodata"){ 
        // this.allNotification[domPosition].IsSeen = 1;
          this.allNotification.splice(domPosition,1);
          console.log(this.pageNo+'---length---'+this.allNotification.length);
          if(this.allNotification.length==14){
              this.pageNo =1; 
              this.getAllNotification(this.pageNo); 
          }
       }
       
      }
    })


  }



  goToTicket(project,ticketid,notify_id,comment)
  {
    var post_data={'projectId':project.PId,'notifyid':notify_id,viewAll:1,page:this.pageNo};
    this._ajaxService.AjaxSubscribe('story/read-notification',post_data,(data)=>
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
    this._ajaxService.AjaxSubscribe('story/read-notification',post_data,(data)=>
    {
      if(data)
      {
      this._router.navigate(['project',project.ProjectName,ticketid,'details'],{queryParams: {Slug:comment}});
 
      }
    })
     
  }
  markAllRead()
  {
    var post_data={};
    this._ajaxService.AjaxSubscribe('story/read-notifications',post_data,(data)=>
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

 @HostListener('window:scroll', ['$event']) 
    loadNotificationsOnScroll(event) {
      console.log("Scroll Event******" );
      if (this.nomorenotifications == false && jQuery(window).scrollTop() == jQuery(document).height() - jQuery(window).height()) {
  console.log("Scroll Event****** In" );
          this.pageNo +=1; 
          this.getAllNotification(this.pageNo);     
      }
    }
}