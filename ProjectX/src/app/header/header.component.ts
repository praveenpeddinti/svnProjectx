import { Component, OnInit,Input} from '@angular/core';
import { Router} from '@angular/router';
import { Headers, Http } from '@angular/http';
import { LoginService, Collaborator } from '../services/login.service';
import { AjaxService } from '../ajax/ajax.service';
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
  public notify_count=0;
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
      thisObj._ajaxService.NodeSubscribe('/getAllNotificationsCount',post_data,(data)=>
      {
      
        thisObj.notify_count=data.count;
       
      });
    
   
    setInterval(function(){
var post_data={}; 
      thisObj._ajaxService.NodeSubscribe('/getAllNotificationsCount',post_data,(data)=>
      {
      
        thisObj.notify_count=data.count;
       
      });
},15000)
      
     
    }
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
    var post_data={'notifyid':notify_id};
    this._ajaxService.AjaxSubscribe('story/delete-notification',post_data,(data)=>
    {
    this.notification_msg=[];
      if(data)
      {
        this.notify_count = data.totalCount;
        jQuery('#'+notify_id).remove();
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
    var post_data={'notifyid':notify_id};
    this._ajaxService.AjaxSubscribe('story/delete-notification',post_data,(data)=>
    {
      if(data)
      {
       if(this.notify_count >0){
       this.notify_count--;
      }
        jQuery('#'+notify_id).remove();
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
    var post_data={'notifyid':notify_id};
    this._ajaxService.AjaxSubscribe('story/delete-notification',post_data,(data)=>
    {
      if(data)
      {
        if(this.notify_count >0){
       this.notify_count--;
      }
        jQuery('#'+notify_id).remove();
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
        
         jQuery(".notificationlist").empty();
        // jQuery("#notificationMessage").show();
       
       
      }
    })
  }

  showNotifications()
  {
  
  if(jQuery("#notifications_list").is(":visible")){
    jQuery("#notifications_list").hide();
   }else{
     jQuery("#notifications_list").show();
   if(this.notify_count>0){
    console.log("show notify");
 var post_data={};
 this.notification_msg=[];
      this._ajaxService.NodeSubscribe('/getAllNotifications',post_data,(data)=>
      {
      console.log("--leing-------"+data.notify_result.length);
      if(data.notify_result.length >0){
      
     
  jQuery("#notificationMessage").hide();
    
        for(var i=0;i<data.notify_result.length;i++)
        {
         
            this.notification_msg.push(data.notify_result[i]);
          
        }
        }else{
          jQuery("#notificationMessage").show();
        }
     
      });

  
    }
    }
  }
  
}
