import { Component, OnInit,HostListener,ViewChild} from '@angular/core';
import {AuthGuard} from '../../services/auth-guard.service';
import { Router,ActivatedRoute } from '@angular/router';
import { ProjectService } from '../../services/project.service';
import { GlobalVariable } from '../../config';
import { AjaxService } from '../../ajax/ajax.service';
import { DatePipe } from '@angular/common';
import {BucketService} from '../../services/bucket.service';
import {SharedService} from '../../services/shared.service';
import { ActivitiesComponent } from '../../components/activities/activities.component';
import { ProjectFormComponent } from '../../components/project-form/project-form.component';
import { CreateBucketComponent } from '../../components/create-bucket/create-bucket.component';

declare var jQuery:any;
@Component({
  selector: 'app-project-dashboard',
  templateUrl: './project-dashboard.component.html',
  styleUrls: ['./project-dashboard.component.css'],
  providers: [ProjectService,AuthGuard,BucketService]
})
export class ProjectDashboardComponent implements OnInit {
  @ViewChild(ActivitiesComponent) activitiesComponent: ActivitiesComponent;
  @ViewChild(ProjectFormComponent) projectFormComponent: ProjectFormComponent;
  private projectId;
  public projectName;
  public description;
  public projectLogo;
  public form={};
  editorData:string='';
  public projectImage:any;
  public summernoteLength=0;
  public verified =0;
  public submitted=false;
  public creationPopUp=true;
  public editPopUp=true;
  public projectDetails=[];
  public copyProjectname:any;
  public copydescription:any;
  public activityDetails=[];
  private page=0;
  private offset=0;
  public dashboardScroll=true;
  public dashboardData:any;
  public userInfoLength:any;
  public noMoreActivities:boolean = false;
  public moreCount;
  public otherBucketsContainer:any;

  public bucketStats={
    'Total':0,
    'Current':0,
    'Backlog':0,
    'Completed':0,
    'Closed':0
  };
  public stateData:any={};
  public currentBucketContainer:any;
  public currentWeekBucketContainer:any;

  public noActivitiesFound:boolean = false;
  public projectForm:string; 
      @ViewChild(CreateBucketComponent) createBucketObj:CreateBucketComponent;
  public setLogo:any;
  public spinnerSettings={
      color:"",
      class:""
    };
       
   constructor(private route: ActivatedRoute,public _router: Router,private projectService:ProjectService,
           private _ajaxService: AjaxService,private bucketService:BucketService,private shared:SharedService) { }
          

  ngOnInit() {
     window.scrollTo(0,0);
    this.dashboardData ='';
    var thisObj = this;
   
  this.route.queryParams.subscribe(
      params => 
      { 
         this.route.params.subscribe(params => {
          //  this.projectId = params['id'];
          var projectName=decodeURIComponent(params['projectName']);
           this.projectName=projectName;
            this.projectService.getProjectDetails(this.projectName,(data)=>{ 
              if(data.data!=false){
                // alert(thisObj.projectId+"============");
                thisObj.projectId=data.data.PId;
                  // alert("------------"+JSON.stringify(thisObj.projectId));
                 thisObj.description=data.data.Description;
                 thisObj.projectLogo=data.data.ProjectLogo;
                 thisObj.setLogo=data.data.setLogo;
                //  alert("------------"+JSON.stringify(thisObj.projectLogo));;
                
                }else{
               this._router.navigate(['pagenotfound']);  
              }
                thisObj.form['projectId']=thisObj.projectId; 
                thisObj.form['projectName']=thisObj.projectName; 
                thisObj.form['projectLogo']=thisObj.projectLogo;
                thisObj.form['description']=thisObj.description;
                thisObj.form['setLogo']=thisObj.setLogo;
                thisObj.copyProjectname=thisObj.form['projectName'];
                thisObj.copydescription=thisObj.form['description'];
                 thisObj.currentProjectDetails();
                 thisObj.projectActivities(this.page);
                 this.bucketService.getTotalBucketStats(thisObj.projectId,(data)=>
                  {
                    this.bucketStats.Total=data.data.BucketTypesCount.Total;
                    this.bucketStats.Current=data.data.BucketTypesCount.Current;
                    this.bucketStats.Completed=data.data.BucketTypesCount.Completed;
                    this.bucketStats.Closed=data.data.BucketTypesCount.Closed;
                    this.bucketStats.Backlog=data.data.BucketTypesCount.Backlog;
                    
                  });
                  this.bucketService.getCurrentBucketsInfo(thisObj.projectId,(data)=>
                  {
                    console.log("==Current Buckets Data=="+JSON.stringify(data));
                    if(data.statusCode==200){
                      if(data.data.BucketInfo!=null){
                        this.currentBucketContainer=data.data.BucketInfo.Current;
                      }
                    }
                    
                  });
                  this.bucketService.getCurrentWeekActiveBuckets(thisObj.projectId,(data)=>
                  {
                    console.log("==Current Week Buckets=="+JSON.stringify(data));
                    if(data.statusCode==200){
                      this.currentWeekBucketContainer=data.data;
                      this.moreCount=data.totalCount;
                    }
                  });
                  
            });
        });
           });
           this.shared.change(this._router.url,thisObj.projectName,'','',thisObj.projectName); //added by Ryan for breadcrumb purpose
           
  }
   ngAfterViewInit() {
    
    }

    currentProjectDetails(){
      var postData={
                    projectId: this.form['projectId'],
                    projectName:  this.form['projectName'],
                    page:this.page
                   }
            //   alert("33333444444444-----"+JSON.stringify(postData));
      this._ajaxService.AjaxSubscribe("collaborator/get-project-dashboard-details",postData,(result)=>
                            {
                            //      alert("67868--"+JSON.stringify(result.data.ProjectDetails[0].closedTickets));
                                  this.projectDetails=result.data.ProjectDetails[0];
                                  this.userInfoLength=result.data.ProjectDetails[0].userInfo.length;
                                  //  this.activityDetails=result.data.activityDetails;
                               });
    }
    @HostListener('window:scroll', ['$event']) 
    projectActivityScroll(){
     var thisObj=this;
         if ((!this.noMoreActivities) && jQuery(window).scrollTop() == jQuery(document).height() - jQuery(window).height()) {
                       // thisObj.dashboardScroll=false;
                        thisObj.page++;
                       //     setTimeout(() => { 
                        thisObj.projectActivities(thisObj.page);
                       //  alert("@@@@@-------"+thisObj.page);;
                      //    },2000); 
                        
                    }
    }
    projectActivities(page){
     // alert("page"+page);
              var post_data={
               'page':page,
               'pageLength':10,
               "attributes":{'ProjectId': this.form['projectId']},
               }
                this._ajaxService.AjaxSubscribe("collaborator/get-all-activities-for-project-dashboard",post_data,(result)=>
                {   
                  
                    var thisObj=this;
                   // alert(JSON.stringify(result.data));
                    if (page == 0 ) { 
                    //  alert("121212"); 
                         this.noMoreActivities = false;     
                            this.dashboardData = result.data;
                            //console.log("Onload__activity__"+JSON.stringify(this.dashboardData.activities))
                            var curActLength = this.dashboardData.activities.length;
                              if(this.dashboardData.activities.length==0){
                                  this.noActivitiesFound=true;
                              }
                            
                    }else{
                      var curActLength = this.dashboardData.activities.length;
                        if (result.data.activities.length > 0) {
                          //console.log("Total__Activity"+JSON.stringify(result.data.activities));
                          //console.log(this.dashboardData.activities[curActLength - 1].activityDate +"==77777777777777777777===="+ result.data.activities[0].activityDate)
                          if (this.dashboardData.activities[curActLength - 1].activityDate == result.data.activities[0].activityDate) {
                            this.dashboardData.activities[curActLength - 1].activityData = this.dashboardData.activities[curActLength - 1].activityData.concat(result.data.activities[0].activityData)
                           // console.log("After__Concat"+JSON.stringify(this.dashboardData.activities));
                            // // console.log("@@-44-"+JSON.stringify(this.dashboardData.activities[curActLength - 1].activityData));
                            result.data.activities .splice(0, 1);
                            this.dashboardData.activities=this.dashboardData.activities.concat(result.data.activities);
                              //alert("11");
                        } else {
                            this.dashboardData.activities=this.dashboardData.activities.concat(result.data.activities);
                          // alert("Final__out"+JSON.stringify(thisObj.dashboardData.activities));
                      }
                        } else {
                           this.noMoreActivities = true;
                        }
                      // alert("###--"+JSON.stringify(thisObj.dashboardData));
                    }
                  //  this.dashboardScroll=true;
               })
    }
   scrollDataBuilder(activityData,prepareData){
            for(let searchArray in activityData){
                prepareData.push(activityData[searchArray]);
            }
        return prepareData;
   }
  clearEditedDetails(form){
    console.log("12333");
      this.projectFormComponent.clearEditedDetails(form);
  }
  appendLogo(val){
      jQuery(".imgs").attr("src",'');
    if(val != undefined){
      this.setLogo=false;
      jQuery(".imgs").attr("src",val);
      this.projectLogo=val;
    }else{
      if(!this.projectLogo.includes("assets")){
         this.setLogo=false;
          this.projectLogo=this.projectLogo;
          jQuery(".imgs").attr("src",this.projectLogo);
       }else{
           this.setLogo=true;
          jQuery(".imgs").attr("src",'assets/images/logo.jpg');
       
      }
     
     // this.projectLogo=val;
    }
  }
  appendDescription(val){
    this.description=val;
  }
  
  /**
   * @author:Ryan
   * @description:Used for getting other buckets
   */
  getOtherBuckets(){ 
    if(jQuery("#toggle_other").attr('aria-expanded')=='true')
    {
      this.bucketService.getOtherBucketsInfo(this.projectId,(data)=>
      {
        console.log("==Other buckets data=="+JSON.stringify(data));
        this.otherBucketsContainer=data.data;
        jQuery(".panel-collapse").collapse("show");
      });
    }
  }

  gotoRepo(projName){
    this._router.navigate(['svn',projName],{queryParams:{ProjectName:projName}});
  }

 
}
