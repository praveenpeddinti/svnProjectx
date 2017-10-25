import { Injectable } from '@angular/core';
import { Headers, Http } from '@angular/http';
import { GlobalVariable } from '../../app/config';
import {SharedService} from '../services/shared.service';

declare var jstz:any;
declare var io:any;
declare var socket:any;
var headers = new Headers({ 'Content-Type': 'application/json; charset=utf-8' });

declare var jQuery:any;
 var  Url = GlobalVariable.BASE_API_URL;  // URL to web api
@Injectable()
export class AjaxService {
  constructor(
   private sharedServece:SharedService,
   private http: Http) { }

/**
 * @description AjaxSubscribe function using as common to get data from services.
*/
AjaxSubscribe(url:string,params:Object,callback)
{   console.log("params____"+JSON.stringify(params));
   this.sharedServece.setLoader(true);
   jQuery("#commonSpinner").removeClass("unloading"); 
   jQuery("#commonSpinner").addClass("loading"); 
   var getAllData=  JSON.parse(localStorage.getItem('user'));
   if(getAllData != null){
      params["userInfo"] = getAllData;
      params["timeZone"] = jstz.determine_timezone().name();
    }
      this.http.post(GlobalVariable.BASE_API_URL+url, JSON.stringify(params))
      .subscribe(
      (data) => {
        jQuery("#commonSpinner").removeClass("loading"); 
        jQuery("#commonSpinner").addClass("unloading"); 
        var res = data.json();//For Success Response
        this.sharedServece.setLoader(false);
        if(res.statusCode!=200){
           this.sharedServece.setToasterValue(res.message);
        }
          callback(res);
      },
      err => { console.error("ERRR_____________________" + err) } //For Error Response
      );
}

/**
 * @author : Ryan 
 * @param url 
 * @param params 
 * @param callback 
 * @description Commonly using for notificatioins
 */
NodeSubscribe(url:string,params:Object,callback)
{
  var getAllData=  JSON.parse(localStorage.getItem('user'));
   if(getAllData != null){
      params["userInfo"] = getAllData;
      params["projectId"] = 1;
      params["timeZone"] = jstz.determine_timezone().name();
    }
      this.http.post(GlobalVariable.NOTIFICATION_URL+url, params) 
      .subscribe(
      (data) => {
          this.sharedServece.setLoader(false);
        var res = data.json();//For Success Response
          callback(res);
      },
      err => { console.error("ERRR_____________________" + err) } //For Error Response
      );
}

/**
 * @description To make node calls
 */
SocketSubscribe(url:string,params:Object)
{ 
  var getAllData=  JSON.parse(localStorage.getItem('user'));
   if(getAllData != null){
      params["userInfo"] = getAllData;
      params["projectId"] = 1;
      params["timeZone"] = jstz.determine_timezone().name();
    }
  
      socket.emit(url,params);
     
}
       

}