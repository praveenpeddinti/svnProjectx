import { Component, OnInit,NgZone,Input,Output ,EventEmitter } from '@angular/core';
import { NgForm } from '@angular/forms';
import { AjaxService } from '../../ajax/ajax.service';
import {Router,ActivatedRoute} from '@angular/router';
import { ProjectService } from '../../services/project.service';
import { GlobalVariable } from '../../config';
import { FileUploadService } from '../../services/file-upload.service';
import {SummerNoteEditorService} from '../../services/summernote-editor.service';
declare var jQuery:any;
@Component({
  selector: 'app-project-form',
  inputs: ['projectForm'],
  templateUrl: './project-form.component.html',
  styleUrls: ['./project-form.component.css'],
   providers: [ProjectService],
  
})
export class ProjectFormComponent implements OnInit {
   @Output() appendLogoToParent: EventEmitter<any> = new EventEmitter();
   @Output() appendDescriptionParent: EventEmitter<any> = new EventEmitter();
  private projectId;
  public projectName;
  public description;
  public projectLogo;
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
  public editPopUp=true;
  public clearImgsrc:any;
  public checkImage:any;
  public fileuploadMessage=0; 
  public verifyProjectMess=false;
  public projectForm:string;
  public copyProjectname:any;
  public copydescription:any;
  public noMoreActivities:boolean = false;
  public noActivitiesFound:boolean = false;
  public form={};
  public setLogo:any;
  public updatedLogo:any;
 public spinnerSettings={
      color:"",
      class:""
    };

  constructor(
          private route: ActivatedRoute,
          private _router: Router,
          private _ajaxService: AjaxService,private zone:NgZone,
          private fileUploadService: FileUploadService,
          public editor:SummerNoteEditorService,
          private projectService:ProjectService
  ) {this.filesToUpload = []; }
  ngOnInit() {
   //alert("##"+this.projectForm);
     if(this.projectForm=='create'){
      // alert("121212");
     }else if(this.projectForm=='edit'){
     //  alert("3333");
      var thisObj=this;
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
                 thisObj.description= data.data.Description;
                 thisObj.projectLogo=data.data.ProjectLogo;
                 thisObj.setLogo=data.data.setLogo;
                //  alert("------------"+JSON.stringify(thisObj.projectLogo));;
                }else{
               this._router.navigate(['pagenotfound']);  
              }
                thisObj.form['projectId']=thisObj.projectId; 
                thisObj.form['projectName']=thisObj.projectName; 
                thisObj.form['projectLogo']=thisObj.projectLogo;
               // thisObj.form['description']=thisObj.description;;
                  thisObj.form['setLogo']=thisObj.setLogo;
             // alert("------------"+JSON.stringify(thisObj.form['description']));;
               thisObj.form['description'] =jQuery(thisObj.description).text().trim();
                console.log("343434--"+thisObj.form['projectName']);
               thisObj.copyProjectname=thisObj.form['projectName'];
                
                
            });
        });
           });
    }
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
                                 this.updatedLogo= result.data;
                                    jQuery(".projectlogo").attr("src",result.data);
                                    this.fileExtention=uploadedFileExtension;
                                    if(this.projectForm=='edit'){
                                    //  alert("1212");
                                    //   jQuery(".projectlogo").attr("src",'');
                                    //   jQuery(".projectlogo").attr("src",result.data)
                                    }
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
   public makeAjaxVar = function(postData){
       
    }
    public timer=undefined;
 
    verifyProjectName(value){
          clearTimeout(this.timer);
          if(this.projectForm=='create'){
            this.AjaxCallForProjectName(value);
          }else if(this.projectForm==='edit'){
             console.log("@@@---"+this.copyProjectname.trim());
            console.log("444---"+value.trim().toLowerCase( ));
              if(this.copyProjectname.trim().toLowerCase( )===value.trim().toLowerCase( )){
                console.log("yes");
              }else{
                this.AjaxCallForProjectName(value);
              }
          }
    }
    AjaxCallForProjectName(value){
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
                        this.spinnerSettings.color="";
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
    postProjectDetails(projectForm){
        if(projectForm=='create'){
          this.saveProjectDetails();
        }else{
          this.editProjectDetails();
        }

    }
   saveProjectDetails(){
        if(this.verified==1 && this.fileuploadMessage==0){
            this.projectImage=jQuery('.projectlogo').attr("src");
           var editor=jQuery('#summernote').summernote('code');
            this.editorDesc =jQuery(editor).text().trim();
           // alert("3434343++--"+this.editorDesc);
              this.form['description']=this.editorDesc;
             var postData={
                      projectName:this.form['projectName'].trim(),
                      description:editor,
                      projectLogo:this.projectImage,
                      fileExtention:this.fileExtention
                  } ;
                
              this._ajaxService.AjaxSubscribe("collaborator/save-project-details",postData,(result)=>
              {
                    if (result.statusCode == 200) {
                       setTimeout(() => {
                          this.submitted=false;
                          this.form=[];
                           this.creationPopUp=false;
                             jQuery('#addProjectModel').modal('hide');
                         }, 1000);
                         this.creationPopUp=true;
                        // alert("2"+this.creationPopUp);
                       //  alert("3"+this.editPopUp);
                         this._router.navigate(['project',this.form['projectName']]);
                      }else{
                 }
                }) 
         
       }else{

       }
    }
      editProjectDetails(){
           this.projectImage=jQuery('.projectlogo').attr("src");
            var editor=jQuery('#summernote').summernote('code');
            this.editorDesc =jQuery(editor).text().trim();
           // alert("3434343++--"+this.editorDesc);
              this.form['description']=this.editorDesc;
            // if(editor.length>500){
            //     this.summernoteLength=1;
            // }else{
             var postData={
                      projectName:this.form['projectName'].trim(),
                      description:editor,
                      projectId:this.form['projectId'],
                      projectLogo:this.projectImage,  
                      fileExtention:this.fileExtention
                  } ;
            // alert("jsonnn---------"+JSON.stringify(postData));
              this._ajaxService.AjaxSubscribe("collaborator/update-project-details",postData,(result)=>
              {
                    if (result.statusCode == 200) {
                       setTimeout(() => {
                          this.submitted=false;
                          this.editPopUp=false;
                          jQuery('#addProjectModel').modal('hide');
                         }, 500);
                        this.description=editor;
                       this.appendLogoToParent.emit(this.updatedLogo);
                       this.appendDescriptionParent.emit(editor);
                            this._router.navigate(['project',this.form['projectName']]);
                      }else{
                 }
                }) 
           // }
     
  }
 
    resetFormForcreate(){
       this.submitted=false;
       this.form={};
       this.form['description']='';
        //  this.form={
        //          description:""
        //     };
       // jQuery("#summernote").summernote('code','');
        jQuery("#summernote").summernote('destroy');
        this.verifyProjectMess=false;
        this.spinnerSettings.color='';
        this.spinnerSettings.class ='';
        this.checkImage=jQuery('.projectlogo').attr("src");
     // this.clearImgsrc=="assets/images/logo.jpg";
       if(this.checkImage=="assets/images/logo.jpg"){
         this.clearImgsrc=true; 
       }else{
           this.clearImgsrc=false;
          
       }  

  }
     resetFormForedit(){
       this.submitted=false;
       jQuery("#summernote").summernote('code',this.copydescription);
        this.spinnerSettings.color='';
        this.spinnerSettings.class ='';
        jQuery("#summernote").summernote('destroy',true);
       // alert("summer"+this.form['setLogo']);
         if(this.form['setLogo']!=true){
            jQuery(".projectlogo").attr("src",this.form['projectLogo']);
        }else{
      //    alert("111");
          this.form['setLogo']=true;
          jQuery(".projectlogo").attr("src","assets/images/logo.jpg");
        }
        this.verifyProjectMess=false; 
  }

 creationProject(){ 
       //jQuery("#summernote").summernote();
       // alert("121212");
        var formobj=this;
        this.editor.initialize_editor('summernote','keyup',formobj);
        // this.form={
        //          description:""
        //     };
       this.creationPopUp=true;
      // this.summernoteLength=false;
       this.verifyByspinner='';
       this.fileuploadMessage=0; 
       this.checkImage=jQuery('.projectlogo').attr("src");
     // this.clearImgsrc=="assets/images/logo.jpg";
       if(this.checkImage=="assets/images/logo.jpg"){
         this.clearImgsrc=true; 
       }else{
        //  alert("asd");
           this.clearImgsrc=false;
          // this.checkImage='assets/images/logo.jpg';; alert("555"+this.clearImgsrc);
       }
      //alert("@@--"+this.clearImgsrc);
        this.verifyProjectMess=false; 
         this.spinnerSettings.color='';
         this.spinnerSettings.class ='';
    }
   clearEditedDetails(form){
    console.log("12333");
     this.fileuploadMessage=0; 
     this.editPopUp=true;
     this.submitted=false;
    // setTimeout(()=>{
     var formobj=this;
     this.editor.initialize_editor('summernote','keyup',formobj);
    jQuery('#summernote').summernote('code',this.description);

    //  }, 150);
     this.verifyProjectMess=false; 
     this.spinnerSettings.color='';
     this.spinnerSettings.class ='';
     this.form['projectName']=this.copyProjectname;
     //jQuery("#summernote").summernote('code',this.copydescription);
     this.checkImage=jQuery('.projectlogo').attr("src");
     if(this.checkImage=='assets/images/logo.jpg'){
        this.clearImgsrc=true; 
     }else{
        this.clearImgsrc=false;
      }
  }

  checkDescField(event){
   // alert("34343");
    jQuery(event).text();
  }

}
