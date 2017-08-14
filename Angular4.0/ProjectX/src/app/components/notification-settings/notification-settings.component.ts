import { Pipe,Component, OnInit } from '@angular/core';
import { AjaxService } from '../../ajax/ajax.service';
import { Directive,NgZone } from '@angular/core';
import { Http} from '@angular/http';
import { Router, ActivatedRoute } from '@angular/router';
import {SharedService} from '../../services/shared.service';
import {AccordionModule,DropdownModule,SelectItem,CalendarModule,CheckboxModule} from 'primeng/primeng';

declare var jQuery:any;

@Component({
  selector: 'app-notification-settings',
  templateUrl: './notification-settings.component.html',
  styleUrls: ['./notification-settings.component.css']
})
export class NotificationSettingsComponent implements OnInit {
checkBox:boolean=false;
public isSelectedAll_SN=false;
public isSelectedAll_EN=false;
public isSelectedAll_PN=false;
public SN_count=0;
public EN_count=0;
public PN_count=0;
public NotificationTypes=[];
public NotificationTypesToDisplay=[];
public NotificationStatusToDisplay=[];
  constructor(private _ajaxService: AjaxService,private zone: NgZone,private http: Http, private route: ActivatedRoute) { }

  ngOnInit() {
this.emailPrefernces();
}
public emailPrefernces(){
var data={};
this._ajaxService.AjaxSubscribe("settings/notification-preferences",data,(response) => {
    if (response.statusCode == 200) {
            this.zone.run(() =>{ 
     this.NotificationTypesToDisplay= this.prepareNotificationData(response.data,this.NotificationTypes);
    // this.ready=true;
    this.NotificationTypes=this.prepareItemArray(response.data,false,'notificationSettings');
if((response.data).length==this.SN_count){
 this.isSelectedAll_SN=true;
}
if((response.data).length==this.EN_count){
 this.isSelectedAll_EN=true;
}
if((response.data).length==this.PN_count){
 this.isSelectedAll_PN=true;
}
 })
 }
});
    }
 /*
    @params    :  list,priority,status
    @ParamType :  any,boolean,string
    @Description: Building Dynamic Dropdown List Values
    */
    public prepareItemArray(list:any,priority:boolean,status){
        var listItem=[];
        var listMainArray=[];
        if(list.length>0) { 
            // if(status == "notificationSettings") {
            //     listItem.push({label:"Select Responsible ", value:"",priority:priority,type:status});
            // }
           for(var i=0;list.length>i;i++){
              listItem.push({title:list[i].ActivityTitle,desc:list[i].ActivityDescription, value:list[i].Id,priority:priority,type:status});
              if(list[i].SystemNotification==1){
               this.SN_count=this.SN_count+1;
              }
              if(list[i].EmailNotification==1){
               this.EN_count=this.EN_count+1;
              }
              if(list[i].PushNotification==1){
               this.PN_count=this.PN_count+1;
              }
         }
        }
        listMainArray.push({type:"",filterValue:listItem});
        return listMainArray;
    }

     prepareNotificationData(notificationsData,prepareData){
         for(let notificationArray in notificationsData){
        prepareData.push(notificationsData[notificationArray]);
       }
       return prepareData;
    }
    isChecked(Id,type,status,e){
        var isChecked=e.target.checked;
        var post_data={id:Id,type:type,status:status,isChecked:isChecked};
        this._ajaxService.AjaxSubscribe("settings/notifications-settings-status-update",post_data,(response) => {
 });
}
    isCheckedAll(type,e){
     var isChecked=e.target.checked; 
    var post_data={isChecked:isChecked,NotificationType:type};
    this._ajaxService.AjaxSubscribe("settings/notifications-settings-status-update-all",post_data,(response) => {
    if (response.statusCode == 200) {
            this.zone.run(() =>{  
            this.emailPrefernces();
    })
   }
 });
    }
}
