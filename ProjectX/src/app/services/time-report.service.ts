import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { Headers, Http } from '@angular/http';
import { AjaxService } from '../ajax/ajax.service';
import 'rxjs/add/operator/toPromise';


@Injectable()
export class TimeReportService {
  constructor(
    private _ajaxService: AjaxService,
    public _router: Router,
    private http: Http) { }

private getAllData=  JSON.parse(localStorage.getItem('user'));
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
 

getAllStoryDetails(projectId,offset,pagesize,sortvalue,sortorder,getAllStoryDetailsCallback) { 
   var post_data={
      'projectId':projectId,
      'offset':offset,
      'pagesize':pagesize,
      'sortvalue':sortvalue,
      'sortorder':sortorder,
     
    }
    this._ajaxService.AjaxSubscribe("time-report/get-time-report-details",post_data,(data)=>
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

}