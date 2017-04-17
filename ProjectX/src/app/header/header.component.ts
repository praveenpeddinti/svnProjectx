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
      var post_data={};
      this._ajaxService.NodeSubscribe('/getAllNotifications',post_data,(data)=>
      {
        console.log("==Notify length=="+data.notify_result.length);
        this.notify_count=data.notify_result.length;
        console.log("==Data=="+JSON.stringify(data.notify_result));
        for(var i=0;i<data.notify_result.length;i++)
        {
         
            this.notification_msg.push(data.notify_result[i]);
          
        }
        this.notification_msg.filter(((item, index) => index <5 ))
        //  if(data.notify_result.length==0)
        //  {
        //    console.log("empty");
        //    //this.notify_count=0;
        //   jQuery(".readAll").show();
        //  }
      });
     
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
         if(searchString=='' || searchString=='undefined'){ 
           this.showErrorFunction("searchError","Please Search.")
         }else{
           this._router.navigate(['search'],{queryParams: {v:searchString}});
         }
      
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
      if(data)
      {
        this.notify_count--;
        jQuery('#'+notify_id).remove();
        if(this.notify_count==0)
        {
          jQuery(".notificationdiv").hide();
          jQuery(".readAll").show();
        }
        
      }
    })


  }

  goToTicket(ticketid)
  {
    this._router.navigate(['story-detail',ticketid]);
  }
  goToComment(ticketid,comment)
  {
    this._router.navigate(['story-detail',ticketid],{queryParams: {Slug:comment}});
  }
  allRead()
  {
    var post_data={};
    this._ajaxService.AjaxSubscribe('story/delete-all-notification',post_data,(data)=>
    {
      if(data)
      {
        this.notify_count=0;
        jQuery('.notificationdiv').hide();
      }
    })
  }
}
