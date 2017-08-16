import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { Headers, Http } from '@angular/http';
import { GlobalVariable } from '../../app/config';
import { AjaxService } from '../ajax/ajax.service';
import 'rxjs/add/operator/toPromise';
var headers = new Headers({ 'Content-Type': 'application/json; charset=utf-8' });

@Injectable()
export class ProjectService {
  constructor(
    public _router: Router,
    private _ajaxService: AjaxService) { }


getProjectDetails(projectName,getProjectCallback) {
   var post_data={
      'projectName':projectName,
      'AccessKey': '3fd31d9a7ae286b9c6da983b35359915'
    }

 this._ajaxService.AjaxSubscribe("story/get-project-details",post_data,(data)=>
    { 
         getProjectCallback(data);
    });

  }

  getUserDetails(code,getUserCallback){
   
    var post_data={
      'code':code,
      'AccessKey': '3fd31d9a7ae286b9c6da983b35359915'
    }
 this._ajaxService.AjaxSubscribe("collaborator/get-user-email",post_data,(data)=>
    { 
         getUserCallback(data);
    });

  }
 
}