import { Component,OnInit,NgZone,HostListener } from '@angular/core';
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
    public noSearchDivClass='';
    public searchDivTabs:any; 
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
     this.loadsearchContent();
        
        this.shared.change(this._router.url,this.searchString,'Search','Other',this.projectName); //added By Ryan for breadcrumb purpose
    }
       @HostListener('window:scroll', ['$event']) 
        loadsearchContent(){
             var thisObj=this; 
           //  jQuery(document).ready(function(){
         //  jQuery(window).scroll(function() {
                if (thisObj.ready && jQuery(window).scrollTop() >= (jQuery(document).height() - jQuery(window).height())) {
                    thisObj.ready=false;
                    thisObj.page++;
                  //  alert("loading"+thisObj.projectId);
                    thisObj.load_contents(thisObj.page,thisObj.searchString,thisObj.searchFlag,thisObj.projectId,'scroll'); 
                    
                }
              
              //  });
      //  });
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
             this.zone.run(() => { 
                   if(result.message !='no result found'){
                   // document.getElementById("nosearchdiv").style.display = 'block';
                   this.searchDivTabs=true;
                    this.noSearchDivClass='col-xs-12 col-sm-9 col-md-9 tabpaddingleftzero';
                    this.searchArray= this.searchDataBuilder(result.data,this.searchArray);
                    this.ready=true;
                    }else{
                        if(scroll=='scroll' && result.message =='no result found'){
                               if (document.getElementById('searchsection').innerHTML.indexOf("No Results Found") != -1) {
                                  document.getElementById('searchsection').innerHTML='No Results Found';
                            }else{
                                this.noSearchDivClass=' ';
                                this.noSearchDivClass='col-xs-12 col-sm-9 col-md-9 tabpaddingleftzero';
                                document.getElementById("nosearchdiv").style.display = 'block';
                                document.getElementById('searchsection').innerHTML=' '; 
                                document.getElementById('searchsection').innerHTML="That’s all. No results found."; 
                           }   
                        }else{
                            if(searchFlag=='' || searchFlag==undefined){
                                this.noSearchDivClass='';
                                this.noSearchDivClass='col-xs-12 col-sm-12 col-md-12 tabpaddingleftzero';
                             //   document.getElementById("nosearchdiv").style.display ='';
                              //  document.getElementById("nosearchdiv").style.display = 'none'; 
                               this.searchDivTabs=false;
                            }
                                document.getElementById('searchsection').innerHTML=' ';
                                document.getElementById('searchsection').innerHTML='No Results Found';
                          }
                 
                }
           });
            });
         
    }
    constructor(
        private _router: Router,
         private _authGuard:AuthGuard,
        private route: ActivatedRoute,
        private _ajaxService: AjaxService,
        private shared:SharedService,
        private projectService:ProjectService,
        private http: Http,
        private zone:NgZone
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
        document.getElementById('searchsection').innerHTML=' ';
        this.load_contents(this.page,this.searchString,this.searchFlag,this.projectId,'');
        
     }
}

