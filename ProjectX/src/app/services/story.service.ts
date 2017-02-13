import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { Headers, Http } from '@angular/http';
import { AjaxService } from '../ajax/ajax.service';

import 'rxjs/add/operator/toPromise';


@Injectable()
export class StoryService {
  constructor(
    private _ajaxService: AjaxService,
    public _router: Router,
    private http: Http) { }


getStoryFields(projectId,getStoryCallback) { 
   var post_data={
      'projectId':projectId,
      'AccessKey': '3fd31d9a7ae286b9c6da983b35359915'
    }
    this._ajaxService.AjaxSubscribe("story/new-story-template",post_data,(data)=>
    { 
         getStoryCallback(data);
    });
  }
 
 saveStory(storyData,saveStoryCallback){ 
var post_data={
      'data':storyData,
      'userId':3,
      'AccessKey': '3fd31d9a7ae286b9c6da983b35359915'
    }

     this._ajaxService.AjaxSubscribe("story/save-ticket-details",post_data,(data)=>

    { 
         saveStoryCallback(data);
    });
 }
getAllStoryDetails(projectId,offset,pagesize,getAllStoryDetailsCallback) { 
   var post_data={
      'projectId':projectId,
      'offset':offset,
      'pagesize':pagesize
    }
    this._ajaxService.AjaxSubscribe("story/get-all-ticket-details",post_data,(data)=>
    { //console.log("---ddddd-----55-"+JSON.stringify(data));
   // alert("^^^^^^^^^^^^22^^^^^^^^^^^^^^");
         getAllStoryDetailsCallback(data);
    });
  }

}