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
declare var jQuery:any;
@Component({
  selector: 'app-user-dashboard',
  templateUrl: './user-dashboard.component.html',
  styleUrls: ['./user-dashboard.component.css'],
  providers:[AuthGuard,FileUploadService]
})
export class UserDashboardComponent implements OnInit {
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

  public clearImgsrc=true;
  public checkImage:any;
  public fileuploadMessage=0; 
  public verifyProjectMess=false;

  public noMoreActivities:boolean = false;
  public noMoreProjects:boolean = false;
  public noProjectsFound:boolean = false;
  public noActivitiesFound:boolean = false;
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
        var formobj=this;
        this.editor.initialize_editor('summernote','keyup',formobj);
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

  CallFileupload(){
       jQuery("input[id='inputFile']").click(); 
      this.fileuploadClick=true;
    }
        /*
    @params       : fileInput,comeFrom
    @ParamType    :  any,string
    @Description  : Uploading File
    */
    public fileUploadEvent(fileInput: any, comeFrom: string):void 
    {
        if(comeFrom == 'fileChange') {
            this.filesToUpload = <Array<File>> fileInput.target.files;
       } else if(comeFrom == 'fileDrop') {
            this.filesToUpload = <Array<File>> fileInput.dataTransfer.files;
       } else {
            this.filesToUpload = <Array<File>> fileInput.target.files;
       }
            
            this.hasBaseDropZoneOver = false;
            this.fileUploadStatus = true;
            this.fileUploadService.makeFileRequest(GlobalVariable.FILE_UPLOAD_URL, [], this.filesToUpload).then(
                (result :Array<any>) => {
    
                for(var i = 0; i<result.length; i++){
                   result[i].originalname =  result[i].originalname.replace(/[^a-zA-Z0-9.]/g,'_'); 
                    var uploadedFileExtension = (result[i].originalname).split('.').pop();
                     if(uploadedFileExtension == "png" || uploadedFileExtension == "jpg" || uploadedFileExtension == "jpeg" || uploadedFileExtension == "gif") {
                      this.fileuploadMessage=0; 
                       var postData={
                              logoName:"[[image:" +result[i].path + "|" + result[i].originalname + "]]"
                            }
                          this._ajaxService.AjaxSubscribe("site/get-project-image",postData,(result)=>
                            {
                                if(result.data){
                                    jQuery(".projectlogo").attr("src",result.data);
                                    this.fileExtention=uploadedFileExtension;
                               }
                               this.projectImage=jQuery(".projectlogo").attr("src");
                            
                    });
                     } else{
                        this.fileuploadMessage=1; 
                   }
                }
                this.fileUploadStatus = false;
            }, (error) => {
                console.error("Error occured in story-formcomponent::fileUploadEvent"+error);
                this.fileUploadStatus = false;
            });
    }
    public spinnerSettings={
      color:"",
      class:""
    };
    public makeAjax(){
      
    }
  
    public makeAjaxVar = function(postData){
       
    }
    public timer=undefined;
      verifyProjectName(value){
        // alert("@@@---");
        //  this.spinnerSettings.color='';
       // this.spinnerSettings.class ='';
       
       clearTimeout(this.timer);
         var postData={
                      projectName:value.trim()
                  } ;
                   console.log("sssssssssss---------"+value.trim());
                  if(value.trim()=='' || value.trim() == undefined){
                  // alert("sssssssssss");
                     this.spinnerSettings.color='';
                     this.spinnerSettings.class ='';
                     this.verifyProjectMess=false;
                  }else{
                        this.verifyByspinner=2;
                        this.spinnerSettings.color="blue";
                        this.spinnerSettings.class = "fa fa-spinner fa-spin";
                        // alert(this.timer);
                       this.timer = setTimeout(()=>{
                         this._ajaxService.AjaxSubscribe("site/verifying-project-name",postData,(result)=>
                        { 
                           //alert("@@@---"+JSON.stringify(result.statusCode));
                            if (result.data != false) {
                               this.verified=0;
                               this.verifyByspinner=3;
                              this.spinnerSettings.color="red";
                              this.spinnerSettings.class = "fa fa-times";
                               this.verifyProjectMess=true;
                              // alert(this.verifyProjectMess);
                             }else{
                              this.verified=1;
                              this.verifyByspinner=1;
                              this.spinnerSettings.color="green";
                              this.spinnerSettings.class = "fa fa-check";
                            }
                                    
                        })
                       },3000);
                       
                  }
      
    }
   veryInputByspinner(){
        //this.verifyByspinner='';
        this.spinnerSettings.color='';
        this.spinnerSettings.class ='';
        this.verifyProjectMess=false;
    }
    public editorDesc="";
      saveProjectDetails(){
        if(this.verified==1 && this.fileuploadMessage==0){
           this.projectImage=jQuery('.projectlogo').attr("src");
           var editor=jQuery('#summernote').summernote('code');
            this.editorDesc =jQuery(editor).text().trim();
            this.form['description']=this.editorDesc;
           // editor=jQuery(editor).text().trim();
            // if(editor.length>500){
            //     this.summernoteLength=true;
            // }else{
             var postData={
                      projectName:this.form['projectName'].trim(),
                      description:this.form['description'],
                      projectLogo:this.projectImage,
                      fileExtention:this.fileExtention
                  } ;
                
              this._ajaxService.AjaxSubscribe("site/save-project-details",postData,(result)=>
              {
                    if (result.statusCode == 200) {
                       setTimeout(() => {
                          this.submitted=false;
                          this.form={
                                  description:""
                          };
                            this.creationPopUp=false;
                         }, 1000);
                         this.creationPopUp=true;
                         this._router.navigate(['project',this.form['projectName']]);
                      }else{
                 }
                }) 
          //  }
       }else{

       }
    }
 
    resetForm(){
       this.submitted=false;
         this.form={
                 description:""
            };
        jQuery("#summernote").summernote('code','');
        this.verifyProjectMess=false; 
  }
    creationProject(){
       //jQuery("#summernote").summernote();
        this.form={
                 description:""
            };
       this.creationPopUp=true;
      // this.summernoteLength=false;
       this.verifyByspinner='';
       this.fileuploadMessage=0; 
       this.checkImage=jQuery('.projectlogo').attr("src");
       if(this.checkImage=='assets/images/logo.jpg'){
         this.clearImgsrc=true; 
       }else{
           this.clearImgsrc=false;
          // this.checkImage='assets/images/logo.jpg'; alert("555"+this.clearImgsrc);
       }
      // alert("@@--"+this.clearImgsrc);
        this.verifyProjectMess=false; 
         this.spinnerSettings.color='';
         this.spinnerSettings.class ='';
    }

    // clearlengthMessage(){
    //     this.summernoteLength=false; 
    // alert("alererer"+this.summernoteLength) ;   
    // }


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
