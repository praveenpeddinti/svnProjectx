import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { Headers, Http } from '@angular/http';
import { GlobalVariable } from '../../app/config';

import 'rxjs/add/operator/toPromise';
var headers = new Headers({ 'Content-Type': 'application/json; charset=utf-8' });

@Injectable()
export class ProjectService {
  constructor(
    public _router: Router,
    private http: Http) { }


getProjectDetails(projectName,getProjectCallback) {
   var url ='story/get-project-details';
   var post_data={
      'projectName':projectName,
      'AccessKey': '3fd31d9a7ae286b9c6da983b35359915'
    }
     this.http.post(GlobalVariable.BASE_API_URL+url, JSON.stringify(post_data), headers)
      .subscribe(
      (data) => {
        var res = data.json();//For Success Response
          getProjectCallback(res);
      },
      err => { console.error("ERRR_____________________" + err) } //For Error Response
      );
  }
 

}