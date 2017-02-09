import { Injectable } from '@angular/core';
import { Headers, Http } from '@angular/http';
//import { Headers,RequestOptions, Http } from '@angular/http';
import { GlobalVariable } from '../../app/config';

var headers = new Headers({ 'Content-Type': 'application/x-www-form-urlencoded' });
//headers.append('Authorization',"sdssqweqw2111");
 var  Url = GlobalVariable.BASE_API_URL;  // URL to web api
@Injectable()
export class AjaxService {
  constructor(
    private http: Http) { }

AjaxSubscribe(url:string,params:Object,callback)
{   
  
   var getAllData=  JSON.parse(localStorage.getItem('user'));
   params["userInfo"] = getAllData;
   params["projectId"] = 1;
      //var  options = new RequestOptions({headers: headers});
      this.http.post(GlobalVariable.BASE_API_URL+url, JSON.stringify(params), headers)
      .subscribe(
      (data) => {
        var res = data.json();//For Success Response
          callback(res);
      },
      err => { console.error("ERRR_____________________" + err) } //For Error Response
      );
}
}