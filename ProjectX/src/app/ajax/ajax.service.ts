import { Injectable } from '@angular/core';
import { Headers, Http } from '@angular/http';
//import { Headers,RequestOptions, Http } from '@angular/http';
import { GlobalVariable } from '../../app/config';
declare var jstz:any;
declare var io:any;
declare var socket:any;
var headers = new Headers({ 'Content-Type': 'application/json; charset=utf-8' });

declare var jQuery:any;
//headers.append('Authorization',"sdssqweqw2111");
 var  Url = GlobalVariable.BASE_API_URL;  // URL to web api
@Injectable()
export class AjaxService {
  constructor(
    private http: Http) { }

AjaxSubscribe(url:string,params:Object,callback)
{   console.log("params____"+JSON.stringify(params));
   jQuery("#commonSpinner").removeClass("unloading"); 
   jQuery("#commonSpinner").addClass("loading"); 
   var getAllData=  JSON.parse(localStorage.getItem('user'));
   if(getAllData != null){
      params["userInfo"] = getAllData;
     // params["projectId"] = 1;
      params["timeZone"] = jstz.determine_timezone().name();
    }
      //var  options = new RequestOptions({headers: headers});
      this.http.post(GlobalVariable.BASE_API_URL+url, JSON.stringify(params), headers)
      .subscribe(
      (data) => {
        jQuery("#commonSpinner").removeClass("loading"); 
        jQuery("#commonSpinner").addClass("unloading"); 
        var res = data.json();//For Success Response
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
 */
NodeSubscribe(url:string,params:Object,callback)
{
  var getAllData=  JSON.parse(localStorage.getItem('user'));
   if(getAllData != null){
      params["userInfo"] = getAllData;
      params["projectId"] = 1;
      params["timeZone"] = jstz.determine_timezone().name();
    }
      //var  options = new RequestOptions({headers: headers});
      this.http.post(GlobalVariable.NOTIFICATION_URL+url, params, headers)
      .subscribe(
      (data) => {
        // jQuery("#commonSpinner").removeClass("loading"); 
        // jQuery("#commonSpinner").addClass("unloading"); 
        var res = data.json();//For Success Response
          callback(res);
      },
      err => { console.error("ERRR_____________________" + err) } //For Error Response
      );
}


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