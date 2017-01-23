import { Injectable } from '@angular/core';
import { Headers, Http } from '@angular/http';
import { GlobalVariable } from '../../app/config';

var headers = new Headers({ 'Content-Type': 'application/x-www-form-urlencoded' });
 var  Url = GlobalVariable.BASE_API_URL;  // URL to web api
@Injectable()
export class AjaxService {
  constructor(
    private http: Http) { }

AjaxSubscribe(url:string,params:Object,callback)
{     
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