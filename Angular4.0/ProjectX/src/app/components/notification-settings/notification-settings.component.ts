import {Component, OnInit } from '@angular/core';
import { AjaxService } from '../../ajax/ajax.service';
import { Directive,NgZone } from '@angular/core';
import {SharedService} from '../../services/shared.service';
import { Router,ActivatedRoute } from '@angular/router';

@Component({
  selector: 'app-notification-settings',
  templateUrl: './notification-settings.component.html',
  styleUrls: ['./notification-settings.component.css']
})
export class NotificationSettingsComponent implements OnInit {
public isSelectedAll_SN=false;
public isSelectedAll_EN=false;
public isSelectedAll_PN=false;
public SN_count=0;
public EN_count=0;
public PN_count=0;
public NotificationTypesToDisplay=[];
public totalNotificationsCount=0;

  constructor(private _ajaxService: AjaxService,private zone: NgZone, private shared:SharedService,private _router: Router) { }

  ngOnInit() {
        this.shared.change(this._router.url,'','Notification Settings','Other',''); //added By Ryan for breadcrumb purpose
this.notificationPrefernces();
}
/**
 * @description Making the select all option as checked by checking the count
 */
public checkCount(){
        if(this.totalNotificationsCount==this.SN_count){
             this.isSelectedAll_SN=true;
            }
        if(this.totalNotificationsCount==this.EN_count){
             this.isSelectedAll_EN=true;
            }
        if(this.totalNotificationsCount==this.PN_count){
             this.isSelectedAll_PN=true;
            }
        }
 
 /*
  *@Lakshmi
  * Preparing Notification on user preferences
  */
public notificationPrefernces(){
var data={};
    this.SN_count=0;
    this.EN_count=0;
    this.PN_count=0;
this._ajaxService.AjaxSubscribe("settings/notification-preferences",data,(response) => {
    if (response.statusCode == 200) {
    this.zone.run(() =>{ 
    this.NotificationTypesToDisplay= response.data;
    this.totalNotificationsCount = (response.data).length;
    var data= response.data;
   for(var i=0;data.length>i;i++){
              if(data[i].SystemNotification==1){
               this.SN_count=this.SN_count+1;
              }
              if(data[i].EmailNotification==1){
               this.EN_count=this.EN_count+1;
              }
              if(data[i].PushNotification==1){
               this.PN_count=this.PN_count+1;
              }
         }
        this.checkCount();
       })
     }
    });
}
/**
 * @description Ckhecking or unchecking the checkboxes based on user option
 */
isChecked(Id,type,e){
        var isChecked=e.target.checked;
        if(isChecked==false){
        if (isChecked==false && type=="SystemNotification"){
           this.SN_count=this.SN_count-1;
           this.isSelectedAll_SN=false;
        }else if(isChecked==false && type=="EmailNotification"){
            this.EN_count=this.EN_count-1;
            this.isSelectedAll_EN=false;
         } else if(isChecked==false && type=="PushNotification"){
            this.PN_count=this.PN_count-1;
            this.isSelectedAll_PN=false;
         }
        } else{
            if (type=="SystemNotification"){
             this.SN_count=this.SN_count+1;         
         }  else if (type=="EmailNotification"){
              this.EN_count=this.EN_count+1;               
          } else if(type=="PushNotification"){
              this.PN_count=this.PN_count+1;
         }      
         this.checkCount();
        }
        var post_data={id:Id,type:type,isChecked:isChecked};
        this._ajaxService.AjaxSubscribe("settings/notifications-settings-status-update",post_data,(response) => {
    });
    }
   /**
    * @description Providing select/deselect all option for each type of notification.
    */
isCheckedAll(type,e){
      var isChecked=e.target.checked;

       if(isChecked==true){
           if (type=="SystemNotification")
           this.SN_count=this.totalNotificationsCount;        
            else if (type=="EmailNotification")
            this.EN_count=this.totalNotificationsCount;          
              else
               this.PN_count=this.totalNotificationsCount;          
       }
       else{
           if(type=="SystemNotification")
                 this.SN_count=0;
           else if(type=="EmailNotification")
                this.EN_count=0;          
          else          
                this.PN_count=0;         
       }
     var isChecked=e.target.checked; 
    var post_data={isChecked:isChecked,NotificationType:type};
    this._ajaxService.AjaxSubscribe("settings/notifications-settings-status-update-all",post_data,(response) => {
    if (response.statusCode == 200) {
            this.notificationPrefernces();
   }
 });
}
}
