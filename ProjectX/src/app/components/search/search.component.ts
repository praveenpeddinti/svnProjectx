import { Component,OnInit } from '@angular/core';
import { Router,ActivatedRoute } from '@angular/router';
import { GlobalVariable } from '../../config';
import {AuthGuard} from '../../services/auth-guard.service';
import { AjaxService } from '../../ajax/ajax.service';
import {SharedService} from '../../services/shared.service';
import { Http, Headers } from '@angular/http';
import { ProjectService } from '../../services/project.service';
declare var jQuery:any;
@Component({
   selector: 'search-view',
    templateUrl: 'search-component.html',
     styleUrls: ['./search-component.css'],
    providers: [AuthGuard,ProjectService]
})
export class SearchComponent implements OnInit{
    public searchString="";
    public searchArray=[];
    public stringPosition;
    private page=1;
    public ready=true;
    public searchFlag;
    public projectName; 
    public projectId; 
    ngOnInit(){
   var thisObj = this;
     this.route.queryParams.subscribe(
      params => 
      {
           thisObj.searchString=params['q'];
           if(thisObj.searchString){
          console.log("searchhhhhhhhhhhhhhh"+JSON.stringify(thisObj.searchString));
        this.route.params.subscribe(params => {
           this.projectName=params['projectName'];
         //  alert("projectName"+this.projectName);
            if(this.projectName==""||this.projectName==undefined){
               // alert("55555555555555555");;
                 this.page=1;
                this.searchArray=[];
                    console.log("@@@@@@@@@@@@@"+JSON.stringify(thisObj.searchString));
                   this.load_contents(this.page,this.searchString,this.searchFlag,'','');
            }else{
                    this.projectService.getProjectDetails(this.projectName,(data)=>{ 
                    if(data.data!=false){
                        this.projectId=data.data.PId; 
                    this.page=1;
                    this.searchArray=[];
                    this.load_contents(this.page,this.searchString,this.searchFlag,this.projectId,'');
                    }else{
                    this._router.navigate(['pagenotfound']);  
                    }
                        
                });
            }
         });
           }else{
               this._router.navigate(['pagenotfound']);  
           }  
           })
        // this.load_contents(this.page);
             var thisObj=this; 
             jQuery(document).ready(function(){
           jQuery(window).scroll(function() {
                if (thisObj.ready && jQuery(window).scrollTop() >= (jQuery(document).height() - jQuery(window).height())) {
                    thisObj.ready=false;
                    thisObj.page++;
                  //  alert("loading"+thisObj.projectId);
                    thisObj.load_contents(thisObj.page,thisObj.searchString,thisObj.searchFlag,thisObj.projectId,'scroll'); 
                    
                }
              
                });
        });
        this.shared.change(this._router.url,this.searchString,'Search','Other',this.projectName); //added By Ryan for breadcrumb purpose
    }
   public  load_contents(page,searchString,searchFlag,projectId,scroll){
        var post_data={
        'projectId':projectId,
        'searchString':searchString,
        'page':page,
        'searchFlag':searchFlag
      }
      console.log("psearchparam"+JSON.stringify(post_data));
          this._ajaxService.AjaxSubscribe("site/global-search",post_data,(result)=>
         { 
                   if(result.status !='401'){
                    jQuery('#nosearchdiv').css("display","");
                    jQuery('#noseachdivclass').addClass('col-xs-12 col-sm-9 col-md-9 tabpaddingleftzero');
                     this.searchArray= this.searchDataBuilder(result.data,this.searchArray);
                    this.ready=true;
                    }else{
                        if(scroll=='scroll' && result.status =='401'){
                           if (jQuery('#searchsection:contains("No Results Found")').length > 0) {
                                console.log("@@@@@@@@@@@@@@");
                                jQuery('#searchsection').html('No Results Found');
                                
                          }else{
                               jQuery('#noseachdivclass').removeClass();
                            jQuery('#noseachdivclass').addClass('col-xs-12 col-sm-9 col-md-9 tabpaddingleftzero');
                              console.log("asddddddddddddddqwwwwwwwwww");
                              jQuery('#nosearchdiv').css("display","");
                               jQuery('#searchsection').html('');
                              jQuery('#searchsection').html("That's All. No Results found");
                          }   
                        }else{
                            if(searchFlag=='' || searchFlag==undefined){
                            jQuery('#noseachdivclass').removeClass();
                            jQuery('#noseachdivclass').addClass('col-xs-12 col-sm-12 col-md-12 tabpaddingleftzero');
                            jQuery('#nosearchdiv').css("display","none");
                            }
                            jQuery('#searchsection').html('');
                            jQuery('#searchsection').html('No Results Found');
                        }
                 
                }
           });
         
    }
    constructor(
        private _router: Router,
         private _authGuard:AuthGuard,
        private route: ActivatedRoute,
        private _ajaxService: AjaxService,
        private shared:SharedService,
        private projectService:ProjectService,
        private http: Http
        ) {

         }

    // preparing serach data
    searchDataBuilder(searchData,prepareData){
        for(let searchArray in searchData){
        prepareData.push(searchData[searchArray]);
       }
       return prepareData;
    }
    navigateToStoryDetail(project,ticketId,slug){
        if(slug=='' || slug==undefined){
            this._router.navigate(['project',project.ProjectName,ticketId,'details']);
        }else{
         this._router.navigate(['project',project.ProjectName,ticketId,'details'],{queryParams: {Slug:slug}});
        }
    }
     callsearchByClick(searchFlag){
        this.searchArray=[];
        this.searchFlag=searchFlag;
        this.page=1;
        // alert("loading12333"+this.projectId);
         jQuery('#searchsection').html('');
        this.load_contents(this.page,this.searchString,this.searchFlag,this.projectId,'');
        
     }
}

