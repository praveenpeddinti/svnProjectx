import { Component, OnInit } from '@angular/core';
import {AuthGuard} from '../../services/auth-guard.service';
import { Router,ActivatedRoute } from '@angular/router';
import { ProjectService } from '../../services/project.service';
import { GlobalVariable } from '../../config';
import { FileUploadService } from '../../services/file-upload.service';
import {SummerNoteEditorService} from '../../services/summernote-editor.service';
import { AjaxService } from '../../ajax/ajax.service';
declare var jQuery:any;
@Component({
  selector: 'app-project-dashboard',
  templateUrl: './project-dashboard.component.html',
  styleUrls: ['./project-dashboard.component.css'],
  providers: [ProjectService,AuthGuard,FileUploadService]
})
export class ProjectDashboardComponent implements OnInit {
  private projectId;
  public projectName;
  public description;
  public projectLogo;
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
  public projectDetails=[];
   constructor(private route: ActivatedRoute,public _router: Router,private projectService:ProjectService,   private fileUploadService: FileUploadService,
          private editor:SummerNoteEditorService, private _ajaxService: AjaxService) {this.filesToUpload = []; }

  ngOnInit() {
    var thisObj = this;
  this.route.queryParams.subscribe(
      params => 
      { 
         this.route.params.subscribe(params => {
          //  this.projectId = params['id'];
           this.projectName=params['projectName'];
            this.projectService.getProjectDetails(this.projectName,(data)=>{ 
              if(data.data!=false){
                thisObj.projectId=data.data.PId;
                   alert("------------"+JSON.stringify(thisObj.form['projectId']));
                 thisObj.description=data.data.Description;
                  thisObj.projectLogo=data.data.ProjectLogo;
                //  alert("------------"+JSON.stringify(thisObj.projectLogo));
                }else{
               this._router.navigate(['pagenotfound']);  
              }
                
        });
        });
           });
         //   alert("------33------"+JSON.stringify(thisObj.form['projectId']));
 //alert("@@@@@@@@@2"+JSON.stringify(this.projectLogo));
          this.form['projectName']=this.projectName;
          this.form['projectId']=this.projectId;
          this.form['projectLogo']=this.projectLogo;
          this.form['description']=this.description;
        //alert("qqqqqqqqqqq------------"+JSON.stringify( this.form['projectId']));
          // this.form.projectName=params['projectName'];
          this.currentProjectDetails();
  }
   ngAfterViewInit() {
       //  setTimeout(() => {
         var formobj=this;
        this.editor.initialize_editor('summernote','keyup',formobj);
       //  },2000);
        this.editor.initialize_editor('summernote',null,this);
     jQuery("#summernote").summernote('code',this.form['description']);
    }
    currentProjectDetails(){
      var postData={
                    projectId: this.form['projectId'],
                    projectName:  this.form['projectName']
                   }
                 //  alert("33333444444444-----")
      this._ajaxService.AjaxSubscribe("site/get-project-dashboard-details",postData,(result)=>
                            {
                            //      alert("67868--"+JSON.stringify(result.data.ProjectDetails[0].closedTickets));
                                  this.projectDetails=result.data.ProjectDetails[0];
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
                 //  var copy = Object.assign({}, value);
                   //this.form['projectName']=copy;
                 // this.extractFields=copy;
               //  alert("@@@@"+value+"----"+this.form['projectName']);
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
  editProjectDetails(){
          // if(this.verified==1){
           this.projectImage=jQuery('#projectlogo').attr("src");
            var editor=jQuery('#summernote').summernote('code');
            editor=jQuery(editor).text().trim();
            if(editor.length>500){
                this.summernoteLength=1;
            }else{
             var postData={
                      projectName:this.form['projectName'].trim(),
                      description:this.form['description'].trim(),
                      projectId:this.form['projectId'],
                      projectLogo:this.projectImage,  
                      fileExtention:this.fileExtention
                  } ;
               //   alert("jsonnn---------"+JSON.stringify(this.fileExtention));
               //   alert("jsonnn"+JSON.stringify(postData));
              this._ajaxService.AjaxSubscribe("site/update-project-details",postData,(result)=>
              {
                    if (result.statusCode == 200) {
                       setTimeout(() => {
                         // this.submitted=false;
                          this.creationPopUp=false;
                         }, 1000);
                         this.creationPopUp=true;
                      // alert("2222222"+this.form['projectName']);
                           this._router.navigate(['project',this.form['projectName']]);
                      }else{
                      //   alert("333");
                 }
                }) 
            }
      //  }else{

      //  }
  }
  clearEditedDetails(form){
    this.creationPopUp=true;
    this.submitted=false;
   // alert("6666666666------------"+JSON.stringify(form));
      var formobj=this;
     this.editor.initialize_editor('summernote','keyup',formobj);
  }

}
