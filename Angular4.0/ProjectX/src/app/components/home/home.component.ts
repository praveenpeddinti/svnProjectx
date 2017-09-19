import { LoginService } from '../../services/login.service';
import {AuthGuard} from '../../services/auth-guard.service';
import { NgForm } from '@angular/forms';
import { Component, OnInit,ViewChild,Input,NgZone } from '@angular/core';
import { Router,ActivatedRoute } from '@angular/router';
import { Headers, Http, Response } from '@angular/http';
import { AjaxService } from '../../ajax/ajax.service';
import {AccordionModule,DropdownModule,SelectItem} from 'primeng/primeng';
import {SharedService} from '../../services/shared.service';
import { ProjectService } from '../../services/project.service';
// import {ActivityService} from '../../services/activity.service';
declare var jQuery:any;


@Component({
    selector: 'home-view',
    providers: [LoginService,AuthGuard],
    templateUrl: 'home-component.html',
    styleUrls: ['./home-component.css'],    
    	
})

export class HomeComponent{
    public selectedProject:any;
   // public projects:any=[{'label':'ProjectX',value:{'id':1,'name':'ProjectX.0'}},{'label':'Techo2',value:{'id':2,'name':'Techo2'}}];
    //public optionTodisplay:any=[{'type':"",'filterValue':this.projects}];
    public users=JSON.parse(localStorage.getItem('user'));
    public Email=localStorage.getItem('Email');
    public profilePicture=localStorage.getItem('profilePicture');
    public entryForm={};
    public verified =0;
    public submitted=false;
    private cities=[];
    private page=0;
    public dashboardScroll=true;
    public homeFlag=true;
    public projectFlag;
    public limit=10;
    public saved;
    public projectDetails=[];
    public assignedToDataCount;
    public followersDetailsCount;
    public projectwiseInfo=[];
    public totalProjectCount;
    public flag:any;
    public activityDetails=[];
    public activityPage=0;
    public projects=[];
    private optionTodisplay=[];
    public activityDropdownFlag=0;
    public activitydropdownDetails=[];
    public ProjectId;

    private projName;
    //public offset=0;
    public rows = [];
      ngOnInit() {
          this.route.queryParams.subscribe(
        params => 
            { 
                this.projName=params['ProjectName'];
                // alert(params["BucketId"]);
            //     thisObj.navBucketId = params["BucketId"];
            // thisObj.route.params.subscribe(params => {
            //     thisObj.projectName=params['projectName'];
            //     thisObj.projectService.getProjectDetails(thisObj.projectName,(data)=>{
            //     if(data.statusCode!=404) {
            //         this.page = 0;
            //     //   alert("+++++++++");
            //         thisObj.projectId=data.data.PId;
            //         thisObj.load_bucketContents(1,'','');
            //         thisObj.projectActivities(this.page);
            //         //this.shared.change(this._router.url,null,'Time Report','Other',thisObj.projectName);
            //     }
            //     });
            // });
        })
         // alert("----home");
    //    localStorage.setItem('ProjectName','');
    //    localStorage.setItem('ProjectId','');
    //     this.shared.change('','','','','');
    //   //  alert("----home"+localStorage.getItem('ProjectName'));
    //   //  alert("3333333333333333");
    //            this.load_contents(this.page,'',this.limit,this.flag,this.activityPage,this.ProjectId,this.activityDropdownFlag,'unscroll');
    //    var thisObj=this;
    //                 jQuery(document).ready(function(){
    //         jQuery(window).scroll(function() {
    //                 if (thisObj.dashboardScroll && jQuery(window).scrollTop() >= (jQuery(document).height() - jQuery(window).height())) {
    //                     thisObj.dashboardScroll=false;
    //                 //     alert("flagggggggggg------------"+thisObj.dashboardScroll);
    //                 //  alert("before------------"+thisObj.page);
    //                     thisObj.page++;
    //                 //  alert("After------------"+thisObj.flag);
    //                    if(thisObj.flag==2){
    //                        thisObj.activityPage++;
    //                   //     alert("After--33333----------"+thisObj.activityPage);
    //                    }
    //                     console.log("loading-------------");
    //                   //     setTimeout(() => { 
    //                     thisObj.load_contents(thisObj.page,thisObj.projectFlag,thisObj.limit,thisObj.flag,thisObj.activityPage,thisObj.ProjectId,thisObj.activityDropdownFlag,'scroll');
    //                   //    },2000); 
                        
    //                 }
                
    //                 });
    //         });
    //          this.getAllProjectNames();
   }
     constructor(
          private _router: Router,
          private route: ActivatedRoute,
         private _service: LoginService,
          private _authGuard:AuthGuard,
          private shared:SharedService,
          private _ajaxService: AjaxService,
          private zone:NgZone,
        //   private _activityService: ActivityService
          ) { }


 load_contents(page,projectFlag,limit,flag,activityPage,projectId,activityDropdownFlag,forScroll){
         var post_data={
               'page':page,
               'projectFlag': projectFlag,
               'limit':limit,
                'activityPage':activityPage,
                'ProjectId':projectId,
                'activityDropdownFlag':activityDropdownFlag
            }
        //   alert("------sssssss-----------"+JSON.stringify(post_data));
                  //   setTimeout(()=>{ 
                    this._ajaxService.AjaxSubscribe("site/get-all-projects-by-user",post_data,(result)=>
                    {
                        //    this._activityService.data=result.data;
                        // var result = this._activityService.getProjectOrActivities(post_data);
                         var thisObj=this;
                         //  alert("--555555555---"+thisObj.flag);
                        //    this.flag=result.data.projectFlag;
                     //alert("@@"+JSON.stringify(result.data));
                        if(result.data !='No Results Found'){
                           this.assignedToDataCount=result.data.AssignedToData;
                           this.followersDetailsCount=result.data.FollowersDetails;
                           this.flag=result.data.projectFlag;
                           this.zone.run(() => {
                           // alert("-----"+thisObj.flag);
                            if(thisObj.flag==2){
                  
                                if(forScroll=='scroll'){
                                    thisObj.activityDropdownFlag=0;
                                }

                                if(thisObj.activityDropdownFlag==1){
                                    thisObj.activityDetails = [];
                                }

                                    thisObj.activityDetails=thisObj.scrollDataBuilder(result.data.ActivityData,thisObj.activityDetails);

                            }else{
                               
                                thisObj.activityDropdownFlag=0;
                                //  alert("-33----"+thisObj.activityDropdownFlag); 
                                thisObj.projectDetails=thisObj.scrollDataBuilder(result.data.ProjectwiseInfo,thisObj.projectDetails);
                             //   alert("projectsss---"+JSON.stringify(thisObj.projectDetails));
                                thisObj.totalProjectCount=result.totalProjectCount;
                            }
                             thisObj.dashboardScroll=true;
                          });
                        
                        }else{
                        //  alert("34q1---"+projectFlag);
                            this.zone.run(() => {

                           if(forScroll=='unscroll'){
                            thisObj.activityDetails=[];
                            thisObj.activitydropdownDetails=[];
                           }
                            jQuery('#searchsection').html('');
                            jQuery('#searchsection').html('No Results found');
                             });
                        }
                           
                          // alert("3333333333333333------"+JSON.stringify(this.projectDetails));
                    })
           
           //  },3000);
    }
  scrollDataBuilder(searchData,prepareData){
  // alert("----------"+JSON.stringify(searchData)); 
            for(let searchArray in searchData){
                prepareData.push(searchData[searchArray]);
            }
        return prepareData;
   }

        verifyProjectName(value){
         
         var postData={
                      projectName:value
                  } ;
                  console.log("post dateeeeeeeeeee-----------------"+value);
                  console.log("post dateeeeeeeeeee"+JSON.stringify(postData));
                  if(value=='' || value == undefined){
                      console.log("sssssssssss");
                  }else{
                      console.log("wwwwwwwwww");
                         this._ajaxService.AjaxSubscribe("site/verifying-project-name",postData,(result)=>
                        { 
                            jQuery('#messageForProject').html("");
                            if (result.status == 200) {
                                this.verified=0;
                                jQuery('#messageForProject').show();
                                jQuery('#messageForProject').html("This project is already exist");
                            }else{
                                this.verified=1;
                                jQuery('#messageForProject').show().fadeOut(4000);
                                jQuery('#messageForProject').html("Available");
                            }
                                    
                        })
                       
                  }
    }
    saveProjectDetails(){
        if(this.verified==1){
             var postData={
                      projectName:this.entryForm['projectName'].trim(),
                      description:this.entryForm['description'].trim(),
                  } ;
              this._ajaxService.AjaxSubscribe("site/save-project-details",postData,(result)=>
              {
                  this.saved=1;
                   var getIndex=result.data.ProjectwiseInfo;
                  // alert("getIndex-------"+JSON.stringify(getIndex));
                  if (result.status == 200) {
                       setTimeout(() => {
                          this.submitted=false;
                          this.entryForm=[];
                          jQuery('#addProjectModel').modal( 'hide' );
                          //   jQuery('#addProjectModel').modal('hide');
                        }, 1000);
                       // alert("------brfore--------"+JSON.stringify(this.projectDetails));
                        this.projectDetails.unshift(getIndex[0]);
                        this.totalProjectCount=result.data.TotalProjectCount;
                     // this._router.navigate(['home']);  
                    // location.reload();
                  }else{
         }
                }) 
       }else{

       }
    }
 
    resetForm(){
         this.entryForm=[];
        }
    callprojectsByClick(projectFlag){
         this.projectFlag=projectFlag;
       
         jQuery('#searchsection').html('');
         if(this.projectFlag==1){
            this.page=0;
         }else{
             this.activityPage=0;
         }
         this.projectDetails=[];
         this.activityDetails=[];
         this.activitydropdownDetails=[];
        //  this.ProjectId="";
         // alert("wwwwwww@@@"+this.page+"dddddddddddd"+this.activityPage);
       this.load_contents(this.page, this.projectFlag,this.limit,this.flag,this.activityPage,this.ProjectId,this.activityDropdownFlag,'unscroll');
        
     }
       getAllProjectNames(){
        var sendData={
           userId:JSON.parse(this.users.Id)
           }
         this._ajaxService.AjaxSubscribe('site/get-project-name-by-userid',sendData,(result)=>
        {
           this.optionTodisplay=this.projectsArray(result.data);
           this.projects=this.optionTodisplay[0].filterValue;
           })

      }
      projectsArray(list){
        var listItem=[];
        listItem.push({label:"All Activities", value:''});
            var listMainArray=[];
            if(list.length>0){
                for(var i=0;list.length>i;i++){
                listItem.push({label:list[i].ProjectName, value:{'id':list[i].PId,'name':list[i].ProjectName}});
                } 
            }     
            listMainArray.push({type:"",filterValue:listItem});
            return listMainArray;
        }
     displayProject(){
        console.log("s__P@@@@@@@@@---"+JSON.stringify(this.selectedProject));
         this.ProjectId=this.selectedProject.id;
        this.activityDropdownFlag=1;
        this.activityPage=0;
         this.load_contents(this.page, this.projectFlag,this.limit,this.flag,this.activityPage,this.ProjectId,this.activityDropdownFlag,'unscroll');
    }
    knowmore(projectId,projectName){
    //  alert("knowwww+++++++++"+projectId);
        this.ProjectId=projectId;
        this.page=0;
      //   this._router.navigate(['project',projectId,'project-detail']);
       // this._router.navigate(['project',projectId]);
       //  this._router.navigate(['project-detail']);
       // this.load_contents(this.page, this.projectFlag,this.limit,this.flag,this.activityPage,this.ProjectId,this.activityDropdownFlag,'unscroll');
    }
    public repo=[];

    createProject(){
    var sendData={
           userId:JSON.parse(this.users.Id),
           repName:this.projName
           }
           alert(JSON.stringify(sendData));
         this._ajaxService.AjaxSubscribe('site/create-repository',sendData,(result)=>
        {  jQuery('#createProjectDiv').show();
           jQuery('#showLogDiv').hide();
           jQuery('#createUserDiv').hide();
           console.log("----repodata----"+JSON.stringify(result));
           this.repo=result.data;
           })
    }
    
    createUser(){
    var sendData={
           userId:JSON.parse(this.users.Id),
           userName:'moin',
           password:'minimum8'
           }
           
         this._ajaxService.AjaxSubscribe('site/create-user',sendData,(result)=>
        {
           jQuery('#createProjectDiv').hide();
           jQuery('#showLogDiv').hide();
           jQuery('#createUserDiv').show();
           console.log("----repodata----"+JSON.stringify(result));
           this.repo=result.data;
           })
    }
    
    showLog(){
    var sendData={
           userId:JSON.parse(this.users.Id),
           repName:'ProjectX'
           }
           
         this._ajaxService.AjaxSubscribe('site/show-svnlog',sendData,(result)=>
        {
           jQuery('#createProjectDiv').hide();
           jQuery('#createUserDiv').hide();
           jQuery('#showLogDiv').show();
           
           console.log("----repodata----"+JSON.stringify(result));
           this.repo=result.data;
           })
    }
}
