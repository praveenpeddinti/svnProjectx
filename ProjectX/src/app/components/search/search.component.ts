import { Component,OnInit } from '@angular/core';
import { LoginService, Collaborator } from '../../services/login.service';
import { Router,ActivatedRoute } from '@angular/router';
import { GlobalVariable } from '../../config';
import {AuthGuard} from '../../services/auth-guard.service';
import { AjaxService } from '../../ajax/ajax.service';
declare var jQuery:any;
@Component({
   selector: 'search-view',
    templateUrl: 'search-component.html',
     styleUrls: ['./search-component.css'],
    providers: [LoginService,AuthGuard]
})
export class SearchComponent implements OnInit{
    public searchString="";
    public searchArray=[];
    public stringPosition;
   private page=1;
    ngOnInit(){
        
        this.load_contents(this.page);
    
    
        //   jQuery(document).ready(function(){

        //     jQuery(window).scroll(function() {
        //     if (jQuery(window).scrollTop() >= jQuery(document).height() - jQuery(window).height() - 20)
        //     {
        //             alert("bottom!");
        //               this._ajaxService.AjaxSubscribe("site/global-search",post_data,(result)=>
        //  { 
        //         alert(JSON.stringify(result.data));
        //         if(result.status !='401'){ 
        //             this.searchArray= this.searchDataBuilder(result.data);
        //             }else{
        //             document.getElementById('searchsection').innerHTML='No Results Found';
        //         }
           
        //   });

        //     }
        //     });
        // })
              var thisObj=this; 
                console.log(thisObj.page);
            jQuery(document).ready(function(){
           
            jQuery(window).scroll(function() { 
               
            if (jQuery(window).scrollTop() >= jQuery(document).height() - jQuery(window).height() - 20) {
              //  alert("$$$$$$$$$$$$"+thisObj.page);
                 thisObj.page++;console.log("###################");
             //    console.log(thisObj.page);
                 thisObj.load_contents(thisObj.page);  
            }
            });
        })
    }
     load_contents(page){
       //  alert("here");
        var post_data={
        'projectId':1,
        'searchString':this.searchString,
        'page':page
      }
         this._ajaxService.AjaxSubscribe("site/global-search",post_data,(result)=>
         { 
                 if(result.status !='401'){ 
                    this.searchArray= this.searchDataBuilder(result.data,this.searchArray);
                    }else{
                  //  document.getElementById('searchsection').innerHTML='No Results Found';
                }
           
          });
    }
    constructor(
        private _router: Router,
        private _service: LoginService,
        private _authGuard:AuthGuard,
        private route: ActivatedRoute,
        private _ajaxService: AjaxService
        ) {
        route.queryParams.subscribe(
      params => 
      {
             this.searchString=params['SearchString'];
           })
         }
    
    // preparing serach data
    searchDataBuilder(searchData,prepareData){
    //    let prepareData = [];
        //    alert(JSON.stringify(searchData));
       for(let searchArray in searchData){
        //        console.log(searchArray);
        prepareData.push(searchData[searchArray]);
       }
       return prepareData;
    }
    navigateToStoryDetail(ticketId,slug){
         this._router.navigate(['/story-detail',ticketId],{queryParams: {Slug:slug}});
        // this._router.navigate(['story-detail', ticketId]);
    }
}

