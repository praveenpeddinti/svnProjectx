import { Component, OnInit,Input} from '@angular/core';
import { Router} from '@angular/router';
import { Headers, Http } from '@angular/http';
import { LoginService, Collaborator } from '../services/login.service';
import { AjaxService } from '../ajax/ajax.service';
import { GlobalVariable } from '../config';
declare var io:any;
declare var socket:any;
declare var jQuery:any;
@Component({
  selector: 'app-header',
  templateUrl: './header.component.html',
  styleUrls: ['./header.component.css'],
  providers: [LoginService]
})

export class HeaderComponent implements OnInit {
  public users=JSON.parse(localStorage.getItem('user'));
  public profilePicture=localStorage.getItem('profilePicture');
  public notification_msg=[];
  public notify_count:any=0;
  public pageNo=1;
  public searchresults;
   constructor(
    private _ajaxService: AjaxService,
    public _router: Router,
    private http: Http,
    private _service: LoginService
       ) { }

  ngOnInit() {
    /* For Notifications */
    if(this.users)
    {
     var thisObj = this;
    var post_data={}; 
      thisObj._ajaxService.SocketSubscribe('getAllNotificationsCount',post_data);
    
      
        socket.on('getAllNotificationsCountResponse', function(data) {
       
            data = JSON.parse(data); 
             console.log("getAllNotificationsCountResponse-----------"+data.count);
            thisObj.notify_count=data.count;
            if(data.count == 0){
            jQuery("#notificationCount").hide();
            }else{
            jQuery("#notificationCount").show();
            jQuery("#notificationCount").html(data.count);
            }

        });


      
     
    }
    
     jQuery(document).ready(function(){
      jQuery(document).bind("click",function(event){   
//alert(jQuery(event.target).closest('ul#notifications_list').length); 
          if(jQuery(event.target).closest('ul#notifications_list').length == 0 && jQuery(event.target).closest('li#notificationIcon').length == 0){
           jQuery("#notifications_list").hide();
          }
      });
     
    });
    
  }
  logout() { 
        this._service.logout((data)=>{ 
              this._router.navigate(['login']);  
        });

    }

  globalSearchNavigate(){
    var searchString=jQuery("#globalsearch").val().trim();
     //this.searchresults=searchString;
    //  var searchString=searchString.replace("#","");
        // if(searchString=='' || searchString=='undefined'){ 
        //   this.showErrorFunction("searchError","Please Search.")
        // }else{
           this._router.navigate(['search'],{queryParams: {v:searchString}});
        // }
      
    }
  showErrorFunction(id,message){
          jQuery("#"+id).html(message);
          jQuery("#"+id).show();
          jQuery("#"+id).fadeOut(4000);
  }

  deleteNotification(notify_id,event)
  {
    event.stopPropagation();
    //ajax call for delete notificatin
    var post_data={'notifyid':notify_id,viewAll:0,page:this.pageNo};
    this._ajaxService.AjaxSubscribe('story/delete-notification',post_data,(data)=>
    {
    this.notification_msg=[];
      if(data)
      {
        this.notify_count = data.totalCount;
        jQuery('#'+notify_id).remove();
        // for view all notification page
        jQuery('#mark_'+notify_id).remove(); 
        jQuery('#notify_no_'+notify_id).removeClass('unreadnotification'); 
      if(data.data.notify_result != "nodata"){
    
       for(var i=0;i<data.data.notify_result.length;i++)
        {
         
            this.notification_msg.push(data.data.notify_result[i]);
          
        }
}
       
      }
    })


  }

  goToTicket(ticketid,notify_id)
  {
    var post_data={'notifyid':notify_id,viewAll:0,page:this.pageNo};
    this._ajaxService.AjaxSubscribe('story/delete-notification',post_data,(data)=>
    {
      if(data)
      {
       if(this.notify_count >0){
       this.notify_count--;
      }
        jQuery('#'+notify_id).remove();
         // for view all notification page
        jQuery('#mark_'+notify_id).remove(); 
        jQuery('#notify_no_'+notify_id).removeClass('unreadnotification'); 
        if(this.notify_count==0)
        {
        jQuery("#notificationMessage").hide();
        
          jQuery(".readAll").show();
        }
        
      }
      this._router.navigate(['story-detail',ticketid]);
    })
    
  }
  goToComment(ticketid,comment,notify_id)
  {
    var post_data={'notifyid':notify_id,viewAll:0,page:this.pageNo};
    this._ajaxService.AjaxSubscribe('story/delete-notification',post_data,(data)=>
    {
      if(data)
      {
        if(this.notify_count >0){
       this.notify_count--;
      }
        jQuery('#'+notify_id).remove();
         // for view all notification page
        jQuery('#mark_'+notify_id).remove(); 
        jQuery('#notify_no_'+notify_id).removeClass('unreadnotification'); 
        if(this.notify_count==0)
        {
         jQuery("#notificationMessage").hide();
        
          jQuery(".readAll").show();
        }
        
      }
    })
    this._router.navigate(['story-detail',ticketid],{queryParams: {Slug:comment}});
  }
  allRead()
  {
    var post_data={};
    this._ajaxService.AjaxSubscribe('story/delete-notifications',post_data,(data)=>
    {
      if(data)
      {
        this.notify_count=0;
        
         jQuery(".notificationlist").remove();
         jQuery(".notificationdelete").remove();
         jQuery('.notificationdiv').removeClass('unreadnotification'); 
        // jQuery("#notificationMessage").show();
       
       
      }
    })
  }

  showNotifications()
  {
  if(jQuery("#notifications_list").is(":visible")){
    jQuery("#notifications_list").hide();
   }else{
     
   if(this.notify_count>0){
    console.log("show notify");
 var post_data={viewAll:0,page:1};
 this.notification_msg=[];
      this._ajaxService.NodeSubscribe('/getAllNotifications',post_data,(data)=>
      {
      console.log("--leing-------"+data.notify_result.length);
      if(data.notify_result.length >0){
      
  jQuery("#notifications_list").show();
  jQuery("#notificationMessage").hide();
    
        for(var i=0;i<data.notify_result.length;i++)
        {
         
            this.notification_msg.push(data.notify_result[i]);
          
        }
        }else{
          jQuery("#notificationMessage").show();
        }
     
      });

  
    }else{
      jQuery("#notifications_list").show();
  jQuery("#notificationMessage").show();
    }

    }
  }


  viewAllNotifications(){
    this._router.navigate(['notification']);
  }
  
}
