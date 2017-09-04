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
 
 saveStory(projectId,storyData,saveStoryCallback){ 
var post_data={
      'projectId':projectId,
      'data':storyData,
     
    }
    post_data.data.title = post_data.data.title.trim();
    if(post_data.data.title!='' && post_data.data.description !='' ){
       this._ajaxService.AjaxSubscribe("story/save-ticket-details",post_data,(data)=>
    { 
         saveStoryCallback(data);

    });
      
    }
 
 }
getAllStoryDetails(projectId,offset,pagesize,sortvalue,sortorder,selectedOption,getAllStoryDetailsCallback) { 
   var post_data={
      'projectId':projectId,
      'offset':offset,
      'pagesize':pagesize,
      'sortvalue':sortvalue,
      'sortorder':sortorder,
      'filterOption':selectedOption,
    }
    this._ajaxService.AjaxSubscribe("story/get-all-story-details",post_data,(data)=>
    { 
         getAllStoryDetailsCallback(data);
    });
  }

  getSubTasksDetails(projectId,storyId,getSubTasksDetailsCallback) { 
   var post_data={
      'projectId':projectId,
      'storyId':storyId
    }
    this._ajaxService.AjaxSubscribe("story/get-sub-task-details",post_data,(data)=>
    { 
         getSubTasksDetailsCallback(data);
    });
  }

  getFilterOptions(projectId,getCallbackDetailsCallback) { 
   var post_data={
      'projectId':projectId,
    }
    this._ajaxService.AjaxSubscribe("story/get-filter-options",post_data,(data)=>
    { 
         getCallbackDetailsCallback(data);
    });
  }

  getPreferences(getPreferenceDetailsCallback)
  {
    var post_data={
      
    }
    this._ajaxService.AjaxSubscribe("story/get-preference",post_data,(data)=>
    { 
         
    getPreferenceDetailsCallback(data);
    });
  }

   getAdvanceFilterOptions(projectId,getCallbackDetailsCallback) { 
   var post_data={
      'projectId':projectId,
    }
    this._ajaxService.AjaxSubscribe("story/get-advance-filter-options",post_data,(data)=>
    { 
         getCallbackDetailsCallback(data);
    });
  }

}