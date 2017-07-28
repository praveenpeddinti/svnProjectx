import { Component, OnInit,Input} from '@angular/core';
import { Router} from '@angular/router';
import { Headers, Http } from '@angular/http';
import { LoginService, Collaborator } from '../services/login.service';
import { AjaxService } from '../ajax/ajax.service';
import {SharedService} from '../services/shared.service';
import {BrowserAnimationsModule} from '@angular/platform-browser/animations';
import {ToasterContainerComponent, ToasterService, ToasterConfig} from 'angular2-toaster';
declare var io:any;
declare var socket:any;
declare var jQuery:any;
@Component({
  selector: 'app-header',
  templateUrl: './header.component.html',
  styleUrls: ['./header.component.css'],
  providers: [LoginService,ToasterService]
})

export class HeaderComponent implements OnInit {

  public toasterconfig : ToasterConfig = 
        new ToasterConfig({
            showCloseButton: false, 
            tapToDismiss: false, 
            timeout: 5000,
            positionClass: "toast-bottom-right",
        }); 
  public users=JSON.parse(localStorage.getItem('user'));
  public profilePicture=localStorage.getItem('profilePicture');
  public ProjectName=localStorage.getItem('ProjectName');
  public notification_msg=[];
  public notify_count:any=0;
  public pageNo=1;
  public searchresults;
  public getnotificationTimeout:any=0;
  public selectedProject:any;
  private projects=[];
  private optionTodisplay=[];
  public homeFlag=false;
  public showLoader:Boolean=true;
  public toast:any;
  //private ProjectName='';
  private PName='';
 
   constructor(
    private _ajaxService: AjaxService,
    public _router: Router,
    private http: Http,
    private _service: LoginService,
    private shared:SharedService,
    private toasterService: ToasterService,
       ) { }

  ngOnInit() {
     if(this._router.url=='/home'){
      this.ProjectName='';// to remove right side sticky menu.
    }
 var thisObj = this;
 this.shared.getNotificationCount().subscribe(value=>
      { 
        thisObj.notify_count = value;
        thisObj.notification_msg = [];
      });

 this.shared.getToasterValue().subscribe(value=>
      {
        if(thisObj.toast)
      thisObj.toasterService.clear(thisObj.toast.toastId, thisObj.toast.toastContainerId); 
      thisObj.toast = thisObj.toasterService.pop('error','Server Error!!!',value);
      
      });
       this.shared.getLoader().subscribe(value=>
      { 
       thisObj.showLoader=value;
      });
  /* For Notifications */
  this.PName=localStorage.getItem('ProjectName');
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
                 thisObj.notification_msg = [];
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

    // Following Code is the Fix for Editor Mention Hint Popover that was not disappearing before
    //@author : Ryan
    if(jQuery("body").find("div").hasClass("note-popover popover in note-hint-popover"))
    {
      jQuery("div.note-popover.popover.in.note-hint-popover").css("display","none");
    }
    
    this.getAllProjectNames();
      }
      getAllProjectNames(){
        var sendData={
           userId:JSON.parse(this.users.Id)
           }
         this._ajaxService.AjaxSubscribe('site/get-project-name-by-userid',sendData,(result)=>
        {
           this.optionTodisplay=this.projectsArray(result.data);
           this.projects=this.optionTodisplay[0].filterValue;
             })

      }
  logout() {
         this._service.logout((data)=>{ 
              localStorage.setItem('ProjectName','');
              localStorage.setItem('ProjectId',''); 
              localStorage.setItem('user','');
              this._router.navigate(['login']);
              this.shared.change(null,null,'Logout',null,''); 
              
        });

    }
projectsArray(list){
  var listItem=[];
 listItem.push({label:"Projects", value:''});
    var listMainArray=[];
     if(list.length>0){
         for(var i=0;list.length>i;i++){
           listItem.push({label:list[i].ProjectName, value:{'id':list[i].PId,'name':list[i].ProjectName}});
         } 
    }     
      listMainArray.push({type:"",filterValue:listItem});
      return listMainArray;
}
  globalSearchNavigate(){
    var searchString=jQuery("#globalsearch").val().trim();
     if(this._router.url=='/home'){
  // localStorage.setItem('PName',null);
      delete this.PName;
    
    }
          if(this.PName=='' || this.PName==undefined){
              this._router.navigate(['search',],{queryParams: {q:searchString}});
          }else{
             this._router.navigate(['project',this.PName,'search'],{queryParams: {q:searchString}});
       }
      
    }
  showErrorFunction(id,message){
          jQuery("#"+id).html(message);
          jQuery("#"+id).show();
          jQuery("#"+id).fadeOut(4000);
  }

  deleteNotification(project,notify_id,event,domIndex)
  {
    event.stopPropagation();
    //ajax call for delete notificatin
    var post_data={'projectId':project.PId,'notifyid':notify_id,viewAll:0,page:this.pageNo};
    this._ajaxService.AjaxSubscribe('story/delete-notification',post_data,(data)=>
    {
    this.notification_msg=[];
      if(data)
      {
        this.notify_count = data.totalCount;
      if(data.data.notify_result != "nodata"){
        this.notification_msg = data.data.notify_result;
      
      }
       
      }
    })


  }

  goToTicket(project,ticketid,notify_id,slug)
  {
    var post_data={'projectId':project.PId,'notifyid':notify_id,viewAll:0,page:this.pageNo};
    this._ajaxService.AjaxSubscribe('story/delete-notification',post_data,(data)=>
    {
      if(data)
      {
         this.notify_count = data.totalCount;
        
      }
      this._router.navigate(['project',project.ProjectName,ticketid,'details'],{queryParams: {Slug:slug,From:"Notification"}});
    })
    
  }
  goToComment(project,ticketid,comment,notify_id)
  {
    var post_data={'projectId':project.PId,'notifyid':notify_id,viewAll:0,page:this.pageNo};
    this._ajaxService.AjaxSubscribe('story/delete-notification',post_data,(data)=>
    {
      if(data)
      {
          this.notify_count = data.totalCount;
        
      }
    })
    this._router.navigate(['project',project.ProjectName,ticketid,'details'],{queryParams: {Slug:comment,From:"Notification"}});
  }
  allRead()
  {
    var post_data={};
    this._ajaxService.AjaxSubscribe('story/delete-notifications',post_data,(data)=>
    {
      if(data)
      {
        this.notify_count=0;
        this.notification_msg=[];
       
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
  var post_data={viewAll:0,page:1};
  if(this.getnotificationTimeout != 0){
   clearTimeout(this.getnotificationTimeout);
 }
 
 this.getnotificationTimeout = setTimeout(()=>{
      this._ajaxService.NodeSubscribe('/getAllNotifications',post_data,(data)=>
      {
       this.notification_msg=[];
      if(data.notify_result.length >0){
      this.notify_count = data.notify_result.length;
      this.notification_msg = data.notify_result;
     
        }
     
      });
},100);
  
    }

    }
  }


  viewAllNotifications(){
    this._router.navigate(['collaborator','notifications']);
  }

  modifyRoute()
  {
    this.shared.change(null,null,'Home',null,'');
  }
  
  TimeReport(){
  this._router.navigate(['project',localStorage.getItem('ProjectName'),'time-report']);
}

 changeProject(){
       // alert("s__P@@@@@@@@@---"+JSON.stringify(this.selectedProject));
        localStorage.setItem('ProjectName',this.selectedProject.name);
        localStorage.setItem('ProjectId',this.selectedProject.id);
        this._router.navigate(['project',this.selectedProject.name,'list']);
    }

Bucket(){
  this._router.navigate(['project',localStorage.getItem('ProjectName'),'bucket']);
}

}
