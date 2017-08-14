import { Component, OnInit } from '@angular/core';
import { AjaxService } from '../../ajax/ajax.service';
import { Directive,NgZone } from '@angular/core';
import { Http} from '@angular/http';
import { Router, ActivatedRoute } from '@angular/router';
import {SharedService} from '../../services/shared.service';
declare var jQuery:any;

@Component({
  selector: 'app-notification-settings',
  templateUrl: './notification-settings.component.html',
  styleUrls: ['./notification-settings.component.css']
})
export class NotificationSettingsComponent implements OnInit {
checkBox:boolean=false;
public isSelectedAll=false;
public isCheckAll=false;
public NotificationTypes=[];
public NotificationTypesToDisplay=[];
public NotificationStatusToDisplay=[];
  constructor(private _ajaxService: AjaxService,private zone: NgZone,private http: Http, private route: ActivatedRoute) { }

  ngOnInit() {
this.emailPrefernces();
// var request={};
// this._ajaxService.AjaxSubscribe("settings/notifications-status",request,(response) => {
//     if (response.statusCode == 200) {
//             this.zone.run(() =>{ 
//    //  this.NotificationStatusToDisplay= this.prepareNotificationData(response.data,this.NotificationTypes);
//      })
//    }
//  });
}
public emailPrefernces(){
var data={};
this._ajaxService.AjaxSubscribe("settings/email-preferences",data,(response) => {
    if (response.statusCode == 200) {
            this.zone.run(() =>{ 
     this.NotificationTypesToDisplay= this.prepareNotificationData(response.data,this.NotificationTypes);
    // this.ready=true;
    this.NotificationTypes=this.prepareItemArray(response.data,false,'notificationSettings');
//alert((response.data).length);
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
           }
        }
        listMainArray.push({type:"",filterValue:listItem});
        return listMainArray;
    }

     prepareNotificationData(bucketData,prepareData){
         for(let bucketArray in bucketData){
        prepareData.push(bucketData[bucketArray]);
       }
       return prepareData;
    }
    isChecked(Id,type,status,e){
        var isChecked=e.target.checked;
        var post_data={id:Id,type:type,status:status,isChecked:isChecked};
        this._ajaxService.AjaxSubscribe("settings/notifications-settings-status-update",post_data,(response) => {
    if (response.statusCode == 200) {
            this.zone.run(() =>{ 
  //   this.NotificationStatusToDisplay= this.prepareNotificationData(response.data,this.NotificationTypes);
    })
   }
 });
}
    isCheckedAll(type,e){
     var isChecked=e.target.checked; 
    //  if(isChecked=="true")
    //         this.isCheckAll=true;
    //  else   
    //         this.isCheckAll=false;
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
