import { Component, OnInit } from '@angular/core';
import { AjaxService } from '../../ajax/ajax.service';
import { Directive,NgZone } from '@angular/core';
import { Http} from '@angular/http';
import { Router, ActivatedRoute } from '@angular/router';


@Component({
  selector: 'app-notification-settings',
  templateUrl: './notification-settings.component.html',
  styleUrls: ['./notification-settings.component.css']
})
export class NotificationSettingsComponent implements OnInit {
public ready=true;
public NotificationTypes=[];
public NotificationTypesToDisplay=[];
public NotificationStatusToDisplay=[];
  constructor(private _ajaxService: AjaxService,private zone: NgZone,private http: Http, private route: ActivatedRoute) { }

  ngOnInit() {
var data={};
this._ajaxService.AjaxSubscribe("settings/email-preferences",data,(response) => {
    if (response.statusCode == 200) {
            this.zone.run(() =>{ 
     this.NotificationTypesToDisplay= this.prepareNotificationData(response.data,this.NotificationTypes);
     
     this.ready=true;
    this.NotificationTypes=this.prepareItemArray(response.data,false,'notificationSettings');
 })
 }
});
// this._ajaxService.AjaxSubscribe("settings/notifications-status",data,(response) => {
//     if (response.statusCode == 200) {
//             this.zone.run(() =>{ 
//      this.NotificationStatusToDisplay= this.prepareNotificationData(response.data,this.NotificationTypes);
//      })
//    }
//  });
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
              listItem.push({label:list[i].ActivityTitle, value:list[i].Id,priority:priority,type:status});
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
    checked(Id,type,status){
        var post_data={id:Id,type:type,status:status};
        this._ajaxService.AjaxSubscribe("settings/notifications-settings-status-update",post_data,(response) => {
    if (response.statusCode == 200) {
            this.zone.run(() =>{ 
     this.NotificationStatusToDisplay= this.prepareNotificationData(response.data,this.NotificationTypes);
     })
   }
 });

    }
}
