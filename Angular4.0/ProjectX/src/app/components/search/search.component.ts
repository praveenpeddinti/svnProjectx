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
    public taskCount:any;
    public commentsCount:any;
    public artifactsCount:any;
    public userDataCount:any;
    public allCount:any;
    public selectedProject:any;
    private projects=[];
    private optionTodisplay=[];
    public checkData:any;
    public checkDataForcount:any;
    public getPname;
    public copyList:any;
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
            if(this.projectName==""||this.projectName==undefined){
               this.checkData=0;
                  this.page=1;
                this.searchArray=[];
                    console.log("@@@@@@@@@@@@@"+JSON.stringify(thisObj.searchString));
                   this.load_contents(this.page,this.searchString,this.searchFlag,'','','');
            }else{
                 this.checkData=1;
                  this.projectService.getProjectDetails(this.projectName,(data)=>{ 
                    if(data.data!=false){
                        this.projectId=data.data.PId; 
                    this.page=1;
                    this.searchArray=[];
                    this.load_contents(this.page,this.searchString,this.searchFlag,this.projectId,'','');
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
             var thisObj=this; 
        
        this.shared.change(this._router.url,this.searchString,'Search','Other',this.projectName); //added By Ryan for breadcrumb purpose
        }
             @HostListener('window:scroll', ['$event']) 
              loadsearchDataOnScroll(event) {
                   var thisObj=this; 
                         if (thisObj.ready && jQuery(window).scrollTop() >= (jQuery(document).height() - jQuery(window).height())) {
                    thisObj.ready=false;
                    thisObj.page++;
                    thisObj.load_contents(thisObj.page,thisObj.searchString,thisObj.searchFlag,thisObj.projectId,'scroll',''); 
                    
                }
              }
     /**
      * @description To search the data on the given data
      */
   public  load_contents(page,searchString,searchFlag,projectId,scroll,pName){
        var post_data={
        'projectId':projectId,
        'searchString':searchString,
        'page':page,
        'searchFlag':searchFlag,
        'pName':pName
      }
      console.log("psearchparam"+JSON.stringify(post_data));
          this._ajaxService.AjaxSubscribe("site/global-search",post_data,(result)=>
         {
             this.zone.run(() => { 
                   if(result.message !='no result found'){
                   this.searchDivTabs=true;
                    document.getElementById('searchsection').innerHTML=' '; 
                    this.noSearchDivClass='col-xs-12 col-sm-9 col-md-9 tabpaddingleftzero';
                    this.searchArray= this.searchDataBuilder(result.data.mainData,this.searchArray);
                    this.taskCount= result.data.dataCount.TaskCount;
                    this.commentsCount=result.data.dataCount.commentsCount;
                    this.artifactsCount=result.data.dataCount.artifactsCount;
                    this.userDataCount=result.data.dataCount.userDataCount;
                    this.allCount=result.data.dataCount.allCount;
                    this.optionTodisplay=this.projectsArray(result.data.projectCountForAll);
                    this.projects=this.optionTodisplay[0].filterValue;
                    if(this.copyList!=undefined){
                    this.projects=this.copyList;
                    this.optionTodisplay=this.copyList;
                    }
                   console.log("###-555-----"+JSON.stringify(this.copyList));
                    this.checkDataForcount=0;
                    this.ready=true;
                    }else{
                          this.optionTodisplay=this.projectsArray(result.data.projectCountForAll);
                        this.projects=this.optionTodisplay[0].filterValue;
                        if(this.copyList!=undefined){
                        this.projects=this.copyList;
                         this.optionTodisplay=this.copyList;
                        }
                         this.checkDataForcount=1;
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
                                this.noSearchDivClass=' tabpaddingleftzero';
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
         /**
          * @description Preparing array 
          */
  projectsArray(list:any){
     var listItem=[];
        var listMainArray=[];
              for (var key in list) {
             listItem.push({label:key+' '+list[key], value:key,'count':list[key]});
               }
        listMainArray.push({type:"",filterValue:listItem});
         return listMainArray;
}
    /**
     * @description preparing serach data
    */
    searchDataBuilder(searchData,prepareData){
        for(let searchArray in searchData){
        prepareData.push(searchData[searchArray]);
       }
       return prepareData;
    }
    /**
     * @description Navigating to the story details page
     */
    navigateToStoryDetail(project,ticketId,slug){
        if(slug=='' || slug==undefined){
            this._router.navigate(['project',project.ProjectName,ticketId,'details']);
        }else{
         this._router.navigate(['project',project.ProjectName,ticketId,'details'],{queryParams: {Slug:slug}});
        }
    }
    /**
     * @description searching fucntionality when click 
     */
     callsearchByClick(searchFlag){
        this.searchArray=[];
        this.searchFlag=searchFlag;
        this.page=1;
        document.getElementById('searchsection').innerHTML=' ';
        this.load_contents(this.page,this.searchString,this.searchFlag,this.projectId,'',this.getPname);
        
     }
     /**
      * @description Changing the project with the dynamic dropdown
      */
      changeProject(){
        localStorage.setItem('ProjectName',this.selectedProject.name);
        localStorage.setItem('ProjectId',this.selectedProject.id);
    }
    /**
     * @description Fetching the project details
     */
    showDetailsByProject(pName){
        this.copyList=this.projects;
        this.copyList=this.optionTodisplay;
         console.log(pName);
        if(pName=='All'){
            this.getPname='';
        }else{
             this.getPname=pName;
        }
         this.searchArray=[];
        this.page=1;
        this.load_contents(this.page,this.searchString,this.searchFlag,this.projectId,'',this.getPname);
     
    }
}

