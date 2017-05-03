import { Component,OnInit } from '@angular/core';
import { Router,ActivatedRoute } from '@angular/router';
import { GlobalVariable } from '../../config';
import {AuthGuard} from '../../services/auth-guard.service';
import { AjaxService } from '../../ajax/ajax.service';
declare var jQuery:any;
@Component({
   selector: 'search-view',
    templateUrl: 'search-component.html',
     styleUrls: ['./search-component.css'],
    providers: [AuthGuard]
})
export class SearchComponent implements OnInit{
    public searchString="";
    public searchArray=[];
    public stringPosition;
    private page=1;
    public ready=true;
    public searchFlag;
    ngOnInit(){
     this.route.queryParams.subscribe(
      params => 
      {
             this.searchString=params['v'];
              this.page=1;
           this.searchArray=[];
             this.load_contents(this.page,this.searchString,this.searchFlag);
           })
       // this.load_contents(this.page);
             var thisObj=this; 
             jQuery(document).ready(function(){
           jQuery(window).scroll(function() {
                if (thisObj.ready && jQuery(window).scrollTop() >= (jQuery(document).height() - jQuery(window).height())) {
                    thisObj.ready=false;
                    thisObj.page++;
                    thisObj.load_contents(thisObj.page,thisObj.searchString,thisObj.searchFlag); 
                    
                }
              
                });
        })
    }
   public  load_contents(page,searchString,searchFlag){
        var post_data={
        'projectId':1,
        'searchString':searchString,
        'page':page,
        'searchFlag':searchFlag
      }
           this._ajaxService.AjaxSubscribe("site/global-search",post_data,(result)=>
         { 
                  if(result.status !='401'){ 
                     this.searchArray= this.searchDataBuilder(result.data,this.searchArray);
                    this.ready=true;
                    }else{
                 jQuery('#searchsection').html('No Results Found');
                }
           });
         
    }
    constructor(
        private _router: Router,
         private _authGuard:AuthGuard,
        private route: ActivatedRoute,
        private _ajaxService: AjaxService
        ) {

         }

    // preparing serach data
    searchDataBuilder(searchData,prepareData){
        for(let searchArray in searchData){
        prepareData.push(searchData[searchArray]);
       }
       return prepareData;
    }
    navigateToStoryDetail(ticketId,slug){
         this._router.navigate(['/story-detail',ticketId],{queryParams: {Slug:slug}});
     }
     callsearchByClick(searchFlag){
        this.searchArray=[];
        this.searchFlag=searchFlag;
        this.page=1;
        this.load_contents(this.page,this.searchString,this.searchFlag);
        
     }
}

