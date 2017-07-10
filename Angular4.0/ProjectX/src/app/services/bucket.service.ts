import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { Headers, Http } from '@angular/http';
import { AjaxService } from '../ajax/ajax.service';
import 'rxjs/add/operator/toPromise';


@Injectable()
export class BucketService {
  constructor(
    private _ajaxService: AjaxService,
    public _router: Router,
    private http: Http) { }

private getAllData=  JSON.parse(localStorage.getItem('user'));


getAllBucketDetails(projectId,getAllBucketDetailsCallback) { 
   var post_data={
      'projectId':projectId,
    }
    this._ajaxService.AjaxSubscribe("bucket/get-all-bucket-details",post_data,(data)=>
    { 
         getAllBucketDetailsCallback(data);
    });
  }

  
getResponsibleFilter(projectId,role,getResponsibleDetailsCallback) { 
   var post_data={
      'projectId':projectId,
      'role':role,
    }
    this._ajaxService.AjaxSubscribe("bucket/get-responsible-collaborators",post_data,(data)=>
    { 
         getResponsibleDetailsCallback(data);
    });
  }
  
  getBucketTypeFilter(projectId,type,getBucketTypeFilterDetailsCallback) { 
   var post_data={
      'projectId':projectId,
      'Type':type,
    }
    alert("--fbt---"+JSON.stringify(post_data));
    this._ajaxService.AjaxSubscribe("bucket/get-bucket-filters",post_data,(data)=>
    { 
         getBucketTypeFilterDetailsCallback(data);
    });
  }
  
  saveBucket(bucketData,saveBucketCallback){ 
   var projectId=parseInt(localStorage.getItem('ProjectId'));
   var projectName=localStorage.getItem('ProjectName');
var post_data={
      'projectId':projectId,
      'data':bucketData,
     
    }
    post_data.data.title = post_data.data.title.trim();
    if(post_data.data.title!='' && post_data.data.description !='' ){
       this._ajaxService.AjaxSubscribe("bucket/save-bucket-details",post_data,(data)=>
    { 
         this._router.navigate(['project',projectName,'bucket']);
         saveBucketCallback(data);

    });
      
    }
 
 }
 
 updateBucket(bucketData,updateBucketCallback){ 
   var projectId=parseInt(localStorage.getItem('ProjectId'));
   var projectName=localStorage.getItem('ProjectName');
var post_data={
      'projectId':projectId,
      'data':bucketData,
     
    }
    post_data.data.title = post_data.data.title.trim();
    if(post_data.data.title!='' && post_data.data.description !='' ){
       this._ajaxService.AjaxSubscribe("bucket/update-bucket-details",post_data,(data)=>
    { 
         this._router.navigate(['project',projectName,'bucket']);
         updateBucketCallback(data);

    });
      
    }
 
 }
  

}