import {Component, OnInit } from '@angular/core';
import { AjaxService } from '../../ajax/ajax.service';
import { Directive,NgZone } from '@angular/core';
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

  constructor(private _ajaxService: AjaxService,private zone: NgZone) { }

  ngOnInit() {
this.notificationPrefernces();
}

 /*
  *@Lakshmi
  * Preparing Notification on user preferences
  */
public notificationPrefernces(){
var data={};
this._ajaxService.AjaxSubscribe("settings/notification-preferences",data,(response) => {
    if (response.statusCode == 200) {
    this.zone.run(() =>{ 
    this.NotificationTypesToDisplay= response.data;
    var totalNotificationsCount = (response.data).length;
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

        if(totalNotificationsCount==this.SN_count){
             this.isSelectedAll_SN=true;
            }
        if(totalNotificationsCount==this.EN_count){
             this.isSelectedAll_EN=true;
            }
        if(totalNotificationsCount==this.PN_count){
             this.isSelectedAll_PN=true;
            }
         })
     }
    });
}
 
isChecked(Id,type,e){
  
        var isChecked=e.target.checked;
       if(isChecked==false){
        if (type=="SystemNotification")
          this.isSelectedAll_SN=false;
         else if(type=="EmailNotification")
          this.isSelectedAll_EN=false;
         else
          this.isSelectedAll_PN=false;
        }
        var post_data={id:Id,type:type,isChecked:isChecked};
        this._ajaxService.AjaxSubscribe("settings/notifications-settings-status-update",post_data,(response) => {
     });
    }
   
isCheckedAll(type,e){
     var isChecked=e.target.checked; 
    var post_data={isChecked:isChecked,NotificationType:type};
    this._ajaxService.AjaxSubscribe("settings/notifications-settings-status-update-all",post_data,(response) => {
    if (response.statusCode == 200) {
            this.notificationPrefernces();
   }
 });
}
}
