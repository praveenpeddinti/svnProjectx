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
  public projects:any;
  public activities:any;
  public form={};
  public filesToUpload: Array<File>;
  public hasBaseDropZoneOver:boolean = false;
  public hasFileDroped:boolean = false;
  editorData:string='';
  public fileUploadStatus:boolean = false;
  public projectImage:any;
  public fileExtention:any;
  public verifyByspinner:any;
  public summernoteLength=0;
  public fileuploadClick=false;
  public verified =0;
  public submitted=false;
  public creationPopUp=true;
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

  ngOnInit() {
    var thisObj = this;
    var req_params={
      projectOffset:this.projectOffset,
      projectLimit:this.projectLimit,
      activityOffset:this.activityOffset,
      activityLimit:this.activityLimit
    }
    thisObj.loadUserDashboard(req_params);
      setTimeout(() => {
         var formobj=this;
        this.editor.initialize_editor('summernote','keyup',formobj);
         },3000);
   }
    ngAfterViewInit() {
        //  setTimeout(() => {
        //  var formobj=this;
        // this.editor.initialize_editor('summernote','keyup',formobj);
        //  },2000);
    }
loadUserDashboard(req_params){
  var thisObj=this;
  this._ajaxService.AjaxSubscribe('collaborator/get-user-dashboard-details',req_params,(result)=>{
   if(thisObj.projectOffset ==0 && thisObj.activityOffset==0){
     thisObj.dashboardData = result.data;
   }else{
     console.log("Projects___"+result.data.projects);
     console.log("PActivity___"+JSON.stringify(result.data.activities));
    // thisObj.dashboardData.projects.push(result.data.projects);
     thisObj.dashboardData.activities.push(result.data.activities[0]);
   }
   
  });
}

  CallFileupload(){
       jQuery("input[id='my_file']").click(); 
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
                       var postData={
                              logoName:"[[image:" +result[i].path + "|" + result[i].originalname + "]]"
                            }
                          this._ajaxService.AjaxSubscribe("site/get-project-image",postData,(result)=>
                            {
                                if(result.data){
                                    jQuery("#projectlogo").attr("src",result.data);
                                    this.fileExtention=uploadedFileExtension;
                               }
                               this.projectImage=jQuery("#projectlogo").attr("src");
                            
                    });
                     } else{
                   }
                }
                this.fileUploadStatus = false;
            }, (error) => {
                console.error("Error occured in story-formcomponent::fileUploadEvent"+error);
                this.fileUploadStatus = false;
            });
    }
      verifyProjectName(value){
         
         var postData={
                      projectName:value
                  } ;
                  if(value=='' || value == undefined){
                      console.log("sssssssssss");
                  }else{
                        this.verifyByspinner=2;
                       setTimeout(() => {
                          this._ajaxService.AjaxSubscribe("site/verifying-project-name",postData,(result)=>
                        { 
                           
                            if (result.statusCode == 200) {
                               this.verified=0;
                               this.verifyByspinner=3;
                             }else{
                              this.verified=1;
                              this.verifyByspinner=1;
                            }
                                    
                        })
                         }, 3000);
                       
                  }
    }
   veryInputByspinner(){
        this.verifyByspinner='';
    }
      saveProjectDetails(){
        if(this.verified==1){

           this.projectImage=jQuery('#projectlogo').attr("src");
             var editor=jQuery('#summernote').summernote('code');
            editor=jQuery(editor).text().trim();
            if(editor.length>500){
                this.summernoteLength=1;
            }else{
             var postData={
                      projectName:this.form['projectName'].trim(),
                      description:this.form['description'].trim(),
                      projectLogo:this.projectImage,
                      fileExtention:this.fileExtention
                  } ;
              this._ajaxService.AjaxSubscribe("site/save-project-details",postData,(result)=>
              {
                    if (result.statusCode == 200) {
                       setTimeout(() => {
                          this.submitted=false;
                          this.form=[];
                            this.creationPopUp=false;
                         }, 1000);
                         this.creationPopUp=true;
                      }else{
                 }
                }) 
            }
       }else{

       }
    }
 
    resetForm(){
         this.form=[];
        }
    creationProject(){
       this.creationPopUp=true;
       this.summernoteLength=0;
       this.verifyByspinner='';
    }
}
