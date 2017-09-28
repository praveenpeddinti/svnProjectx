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
  
  getBucketTypeFilter(projectId,type,bucketId,getBucketTypeFilterDetailsCallback) { 
   var post_data={
      'projectId':projectId,
      'Type':type,
      'bucketId':bucketId
    }
    this._ajaxService.AjaxSubscribe("bucket/get-bucket-filters",post_data,(data)=>
    { 
         getBucketTypeFilterDetailsCallback(data);
    });
  }
  
  saveBucket(projectId,bucketData,saveBucketCallback){ 
var post_data={
      'projectId':projectId,
      'data':bucketData,
     
    }
    post_data.data.title = post_data.data.title.trim();
    // alert(JSON.stringify(post_data.data)+"-----------save bucket-------");
    if(post_data.data.title!='' && post_data.data.description !='' ){
       this._ajaxService.AjaxSubscribe("bucket/save-bucket-details",post_data,(data)=>
    { 
         
         saveBucketCallback(data);

    });
      
    }
 
 }
 
 updateBucket(projectId,bucketId,bucketData,updateBucketCallback){ 
var post_data={
      'projectId':projectId,
      'data':bucketData,
      'bucketId':bucketId
      // 'bucketStatus':bucketStatus,
     
    }
    post_data.data.title = post_data.data.title.trim();
    if(post_data.data.title!='' && post_data.data.description !='' ){
       this._ajaxService.AjaxSubscribe("bucket/update-bucket-details",post_data,(data)=>
    { 
         
         updateBucketCallback(data);

    });
      
    }
 
 }

 /**
  * @author:Ryan
  * @description:Used for getting Buckets Counts with their Types
  * @param projectId 
  * @param totalBucketsCallback 
  */
 getTotalBucketStats(projectId,totalBucketsCallback){
    var post_data={'projectId':projectId};
    this._ajaxService.AjaxSubscribe("bucket/get-total-bucket-stats",post_data,(data)=>
    { 
         
         totalBucketsCallback(data);

    });
 }

 /**
  * @author:Ryan
  * @description:Used for getting Current Buckets 
  * @param projectId 
  * @param bucketsInfoCallback 
  */
 getCurrentBucketsInfo(projectId,bucketsInfoCallback){
   var post_data={'projectId':projectId,type:2};
    this._ajaxService.AjaxSubscribe("bucket/get-buckets",post_data,(data)=>
    {  
         bucketsInfoCallback(data);
    });
 }

 /**
  * @author:Ryan
  * @description:Used for getting current week buckets
  * @param projectId 
  * @param currentWeekbucketsCallback 
  */
 getCurrentWeekActiveBuckets(projectId,currentWeekbucketsCallback){
   var post_data={'projectId':projectId};
   this._ajaxService.AjaxSubscribe("bucket/get-current-week-buckets",post_data,(data)=>
    {  
         currentWeekbucketsCallback(data);
    });
 }

 /**
  * @author:Ryan
  * @description:used for getting other buckets 
  * @param projectId 
  * @param otherBucketsCallback 
  */
 getOtherBucketsInfo(projectId,otherBucketsCallback){
  var post_data={'projectId':projectId};
   this._ajaxService.AjaxSubscribe("bucket/get-other-buckets",post_data,(data)=>
    {  
         otherBucketsCallback(data);
    }); 
 }
  
}