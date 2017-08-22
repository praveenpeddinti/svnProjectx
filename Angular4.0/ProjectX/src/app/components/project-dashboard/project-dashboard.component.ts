import { Component, OnInit,HostListener,ViewChild} from '@angular/core';
import {AuthGuard} from '../../services/auth-guard.service';
import { Router,ActivatedRoute } from '@angular/router';
import { ProjectService } from '../../services/project.service';
import { GlobalVariable } from '../../config';
import { AjaxService } from '../../ajax/ajax.service';
import { DatePipe } from '@angular/common';
import { ActivitiesComponent } from '../../components/activities/activities.component';
import { ProjectFormComponent } from '../../components/project-form/project-form.component';
declare var jQuery:any;
@Component({
  selector: 'app-project-dashboard',
  templateUrl: './project-dashboard.component.html',
  styleUrls: ['./project-dashboard.component.css'],
  providers: [ProjectService,AuthGuard]
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
  public noActivitiesFound:boolean = false;
  public projectForm:string; 
  public setLogo:any;

   constructor(private route: ActivatedRoute,public _router: Router,private projectService:ProjectService,
          private _ajaxService: AjaxService) {}

  ngOnInit() {
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
                // alert("------33------"+JSON.stringify(thisObj.form['projectLogo']));
//                 if(thisObj.form['projectLogo']=='assets/images/logo.jpg'){
//                   thisObj.setlogo=true;
//                 }else{
//                   thisObj.setlogo=false;
//                 }
//  alert("------33345------"+JSON.stringify(thisObj.setlogo));
               //  jQuery("#summernote").summernote('code',thisObj.form['description']);
                 thisObj.copyProjectname=thisObj.form['projectName'];
                  thisObj.copydescription=thisObj.form['description'];
                 thisObj.currentProjectDetails();
                 thisObj.projectActivities(this.page);
            });
        });
           });

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
         if (thisObj.dashboardScroll && jQuery(window).scrollTop() >= (jQuery(document).height() - jQuery(window).height())) {
                        thisObj.dashboardScroll=false;
                        thisObj.page++;
                       //     setTimeout(() => { 
                        thisObj.projectActivities(thisObj.page);
                       //  alert("@@@@@-------"+thisObj.page);;
                      //    },2000); 
                        
                    }
    }
    projectActivities(page){
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
                         thisObj.noMoreActivities = false;     
                            thisObj.dashboardData = result.data;
                            console.log("Onload__activity__"+JSON.stringify(thisObj.dashboardData.activities))
                            var curActLength = thisObj.dashboardData.activities.length;
                              if(thisObj.dashboardData.activities.length==0){
                                  thisObj.noActivitiesFound=true;
                              }
                            
                    }else{
                      var curActLength = thisObj.dashboardData.activities.length;
                        if (result.data.activities.length > 0) {
                          console.log("Total__Activity"+JSON.stringify(result.data.activities));
                          console.log(thisObj.dashboardData.activities[curActLength - 1].activityDate +"==77777777777777777777===="+ result.data.activities[0].activityDate)
                          if (thisObj.dashboardData.activities[curActLength - 1].activityDate == result.data.activities[0].activityDate) {
                            thisObj.dashboardData.activities[curActLength - 1].activityData = thisObj.dashboardData.activities[curActLength - 1].activityData.concat(result.data.activities[0].activityData)
                            console.log("After__Concat"+JSON.stringify(thisObj.dashboardData.activities));
                            // // console.log("@@-44-"+JSON.stringify(thisObj.dashboardData.activities[curActLength - 1].activityData));
                            result.data.activities .splice(0, 1);
                            console.log("Final__IN"+JSON.stringify(result.data.activities));
                            thisObj.dashboardData.activities=thisObj.dashboardData.activities.concat(result.data.activities);
                            //alert("11");
                        } else {
                            thisObj.dashboardData.activities=thisObj.dashboardData.activities.concat(result.data.activities);
                          // alert("Final__out"+JSON.stringify(thisObj.dashboardData.activities));
                        }
                        } else {
                           thisObj.noMoreActivities = true;
                        }
                      // alert("###--"+JSON.stringify(thisObj.dashboardData));
                    }
                    this.dashboardScroll=true;
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
     //  this.fileuploadMessage=0; 
    //  this.editPopUp=true;
    //  this.submitted=false;
    // // setTimeout(()=>{
    //  var formobj=this;
    //  this.editor.initialize_editor('summernote','keyup',formobj);
    // //  }, 150);
    //  this.verifyProjectMess=false; 
    //  this.spinnerSettings.color='';
    //  this.spinnerSettings.class ='';
    //  this.form['projectName']=this.copyProjectname;
    //  //jQuery("#summernote").summernote('code',this.copydescription);;;
    //  this.checkImage=jQuery('.projectlogo').attr("src");
    //  if(this.checkImage=='assets/images/logo.jpg'){
    //     this.clearImgsrc=true; 
    //  }else{
    //     this.clearImgsrc=false;
    //   }
  }
 
}
