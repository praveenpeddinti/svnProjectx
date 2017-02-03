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
    this._ajaxService.AjaxSubscribe("story/story-fields",post_data,(data)=>
    { 
         getStoryCallback(data);
    });
  }
 
 saveStory(storyData,saveStoryCallback){ 
var post_data={
      'storyData':storyData,
      'AccessKey': '3fd31d9a7ae286b9c6da983b35359915'
    }
     this._ajaxService.AjaxSubscribe("story/save-story",post_data,(data)=>
    { 
         saveStoryCallback(data);
    });
 }


}