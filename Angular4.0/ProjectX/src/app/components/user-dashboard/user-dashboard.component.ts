import { LoginService } from '../../services/login.service';
import {AuthGuard} from '../../services/auth-guard.service';
import { NgForm } from '@angular/forms';
import { Component, OnInit,ViewChild,Input,NgZone,HostListener } from '@angular/core';
import { Router,ActivatedRoute } from '@angular/router';
import { Headers, Http, Response } from '@angular/http';
import { AjaxService } from '../../ajax/ajax.service';
import {AccordionModule,DropdownModule,SelectItem} from 'primeng/primeng';
import {SharedService} from '../../services/shared.service';
import { ProjectService } from '../../services/project.service';
import { GlobalVariable } from '../../config';
import { FileUploadService } from '../../services/file-upload.service';
import {SummerNoteEditorService} from '../../services/summernote-editor.service';
import { DatePipe } from '@angular/common';
import { ProjectFormComponent } from '../../components/project-form/project-form.component';

declare var jQuery:any;
@Component({
  selector: 'app-user-dashboard',
  templateUrl: './user-dashboard.component.html',
  styleUrls: ['./user-dashboard.component.css'],
  providers:[AuthGuard,FileUploadService]
})
export class UserDashboardComponent implements OnInit {
  @ViewChild(ProjectFormComponent) projectFormComponent: ProjectFormComponent;
  public users=JSON.parse(localStorage.getItem('user'));
  public projectOffset=0;
  public projectLimit=3;
  public activityOffset=0;
  public activityLimit=10;
  public dashboardData:any;
  public form={
    description:""
  };
  public filesToUpload: Array<File>;
  public hasBaseDropZoneOver:boolean = false;
  public hasFileDroped:boolean = false;
  editorData:string='';
  public fileUploadStatus:boolean = false;
  public projectImage:any;
  public fileExtention:any;
  public verifyByspinner:any;
 // public summernoteLength=false;
  public fileuploadClick=false;
  public verified =0;
  public submitted=false;
  public creationPopUp=true;

  public clearImgsrc:any;
  public checkImage:any;
  public fileuploadMessage=0; 
  public verifyProjectMess=false;

  public noMoreActivities:boolean = false;
  public noMoreProjects:boolean = false;
  public noProjectsFound:boolean = false;
  public noActivitiesFound:boolean = false;
  public spinnerSettings={
      color:"",
      class:""
    };
 public projectForm:string;
  constructor(
          private _router: Router,
          private _service: LoginService,
          private _authGuard:AuthGuard,
          private shared:SharedService,
          private _ajaxService: AjaxService,
          private zone:NgZone,
          private fileUploadService: FileUploadService,
          private editor:SummerNoteEditorService
  ) {this.filesToUpload = []; }

   ngAfterViewInit() { 
       // var formobj=this;
        //this.editor.initialize_editor('summernote','keyup',formobj);
    }
  ngOnInit() {
     window.scrollTo(0,0);
    this.activityOffset=0;
    this.projectOffset =0;
    this.dashboardData ='';
    var thisObj = this;
    var req_params={
      projectOffset:this.projectOffset,
      projectLimit:this.projectLimit,
      activityOffset:this.activityOffset,
      activityLimit:this.activityLimit
    }
   thisObj.loadUserDashboard(req_params);
   this.shared.change('','','','','');
   }

  loadUserDashboard(req_params) {
    var thisObj = this;
    this._ajaxService.AjaxSubscribe('collaborator/get-user-dashboard-details', req_params, (result) => {
      if (result.statusCode == 200) {
        if (thisObj.projectOffset == 0 && thisObj.activityOffset == 0) {
          thisObj.dashboardData = result.data;
            thisObj.noMoreProjects = false;
            thisObj.noMoreActivities = false;
          if(thisObj.dashboardData.projects.length==0){
            thisObj.noProjectsFound=true;
          }
          if(thisObj.dashboardData.activities.length==0){
            thisObj.noActivitiesFound=true;
          }
        } else {
          var curActLength = thisObj.dashboardData.activities.length;
          if (result.data.projects.length > 0) {
            thisObj.dashboardData.projects =thisObj.dashboardData.projects.concat(result.data.projects);
          } else {
            thisObj.noMoreProjects = true;
          }
          if (result.data.activities.length > 0) {
            if (thisObj.dashboardData.activities[curActLength - 1].activityDate == result.data.activities[0].activityDate) {
               thisObj.dashboardData.activities[curActLength - 1].activityData = thisObj.dashboardData.activities[curActLength - 1].activityData.concat(result.data.activities[0].activityData)
               result.data.activities .splice(0, 1);
                            console.log("@@-44-"+JSON.stringify(result.data.activities));
               thisObj.dashboardData.activities=thisObj.dashboardData.activities.concat(result.data.activities);
            } else {
              thisObj.dashboardData.activities=thisObj.dashboardData.activities.concat(result.data.activities);
            }
          } else {
            thisObj.noMoreActivities = true;
          }
          
        }

      }


    });
  }

    creationProject(){
      this.projectFormComponent.creationProject();
    //    //jQuery("#summernote").summernote();
    //    // alert("121212");
    //     var formobj=this;
    //     //this.projectFormComponent.editor.initialize_editor('summernote','keyup',formobj);
    //     this.form={
    //              description:""
    //         };
    //    this.creationPopUp=true;
    //   // this.summernoteLength=false;
    //    this.verifyByspinner='';
    //    this.fileuploadMessage=0; 
    //    this.checkImage=jQuery('.projectlogo').attr("src");
    //  // this.clearImgsrc=="assets/images/logo.jpg";
    //    if(this.checkImage=="assets/images/logo.jpg"){
    //      this.clearImgsrc=true; 
    //    }else{
    //     //  alert("asd");
    //        this.clearImgsrc=false;
    //       // this.checkImage='assets/images/logo.jpg';; alert("555"+this.clearImgsrc);
    //    }
    //   //alert("@@--"+this.clearImgsrc);
    //     this.verifyProjectMess=false; 
    //      this.spinnerSettings.color='';
    //      this.spinnerSettings.class ='';
    }

     @HostListener('window:scroll', ['$event']) 
    loadNotificationsOnScroll(event) {
     // console.debug("Scroll Event", window.pageYOffset );
      if ((!this.noMoreActivities || !this.noMoreProjects ) && jQuery(window).scrollTop() == jQuery(document).height() - jQuery(window).height()) {
          var thisObj = this;
          this.projectOffset= this.projectOffset+1;
          this.activityOffset = this.activityOffset +1;
          var req_params={
            projectOffset:this.projectOffset,
            projectLimit:this.projectLimit,
            activityOffset:this.activityOffset,
            activityLimit:this.activityLimit
          }
          thisObj.loadUserDashboard(req_params);   
      }
    }

  goToTicket(project,ticketid,notify_id,comment)
  {
    this._router.navigate(['project',project.ProjectName,ticketid,'details'],{queryParams: {Slug:comment}});
  }

  globalSearch(){
    var searchString=jQuery("#userglobalsearch").val().trim();
     this._router.navigate(['search',],{queryParams: {q:searchString}});
  }

}
