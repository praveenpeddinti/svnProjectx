import { Component, OnInit,HostListener} from '@angular/core';
import {AuthGuard} from '../../services/auth-guard.service';
import { Router,ActivatedRoute } from '@angular/router';
import { ProjectService } from '../../services/project.service';
import { GlobalVariable } from '../../config';
import { FileUploadService } from '../../services/file-upload.service';
import {SummerNoteEditorService} from '../../services/summernote-editor.service';
import { AjaxService } from '../../ajax/ajax.service';
import { DatePipe } from '@angular/common';
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
  public fileuploadMessage=0; 
  public fileExtention:any;
  public verifyByspinner:any;
  public summernoteLength=0;
  public fileuploadClick=false;
  public verified =0;
  public submitted=false;
  public creationPopUp=true;
  public projectDetails=[];
  public verifyProjectMess=false;
  public copyProjectname:any;
  public copydescription:any;
  public clearImgsrc=true;
  public checkImage:any;
  public activityDetails=[];
  private page=0;
  private offset=0;
  public dashboardScroll=true;
  public dashboardData:any;
  public userInfoLength:any;
  public noMoreActivities:boolean = false;
   constructor(private route: ActivatedRoute,public _router: Router,private projectService:ProjectService,   private fileUploadService: FileUploadService,
          private editor:SummerNoteEditorService, private _ajaxService: AjaxService) {this.filesToUpload = []; }

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
                //  alert("------------"+JSON.stringify(thisObj.projectLogo));;
                
                }else{
               this._router.navigate(['pagenotfound']);  
              }
                thisObj.form['projectId']=thisObj.projectId; 
                thisObj.form['projectName']=thisObj.projectName; 
                thisObj.form['projectLogo']=thisObj.projectLogo;
                thisObj.form['description']=thisObj.description;
              //  alert("------33------"+JSON.stringify(thisObj.form['projectLogo']));
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
       //  setTimeout(() => {
        //alert("12");
     //   var formobj=this;
    //    this.editor.initialize_editor('summernote','keyup',formobj);
       //  },2000);
     //   this.editor.initialize_editor('summernote',null,this);
  
    }

    currentProjectDetails(){
      var postData={
                    projectId: this.form['projectId'],
                    projectName:  this.form['projectName'],
                    page:this.page
                   }
            //   alert("33333444444444-----"+JSON.stringify(postData));
      this._ajaxService.AjaxSubscribe("site/get-project-dashboard-details",postData,(result)=>
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
               'projectId': this.form['projectId'],
               }
                this._ajaxService.AjaxSubscribe("site/get-all-activities-for-project-dashboard",post_data,(result)=>
                {   
                   this.noMoreActivities = false;
                    var thisObj=this;
                    if (page == 0 ) { 
                    //  alert("121212");        
                            thisObj.dashboardData = result.data;
                            console.log("Onload__activity__"+JSON.stringify(thisObj.dashboardData.activities))
                            var curActLength = thisObj.dashboardData.activities.length;
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
        clearTimeout(this.timer);
        if(this.copyProjectname.trim()===value.trim()){
           console.log("yes");
         }else{
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
    }
   veryInputByspinner(){
        //this.verifyByspinner='';
        this.spinnerSettings.color='';
        this.spinnerSettings.class ='';
        this.verifyProjectMess=false;
    }
  editProjectDetails(){
          // if(this.verified==1){
           this.projectImage=jQuery('.projectlogo').attr("src");
            var editor=jQuery('#summernote').summernote('code');
            editor=jQuery(editor).text().trim();
            this.form['description']=jQuery('#summernote').summernote('code');
            
          // alert("3543543535");
            if(editor.length>500){
                this.summernoteLength=1;
            }else{
             var postData={
                      projectName:this.form['projectName'].trim(),
                      description:this.form['description'],
                      projectId:this.form['projectId'],
                      projectLogo:this.projectImage,  
                      fileExtention:this.fileExtention
                  } ;
             //  alert("jsonnn---------"+JSON.stringify(postData));
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
    console.log("12333");
     this.fileuploadMessage=0; 
     this.creationPopUp=true;
     this.submitted=false;
     setTimeout(()=>{
     var formobj=this;
     this.editor.initialize_editor('summernote','keyup',formobj);
      }, 150);
     this.verifyProjectMess=false; 
     this.spinnerSettings.color='';
     this.spinnerSettings.class ='';
     this.form['projectName']=this.copyProjectname;
     //jQuery("#summernote").summernote('code',this.copydescription);;;
     this.checkImage=jQuery('.projectlogo').attr("src");
     if(this.checkImage=='assets/images/logo.jpg'){
        this.clearImgsrc=true; 
     }else{
        this.clearImgsrc=false;
      }
  }
    resetForm(){
       this.submitted=false;
        //  this.form={
        //          description:""
        //     };
        jQuery("#summernote").summernote('code',this.copydescription);
        jQuery("#summernote").summernote('destroy');
        this.verifyProjectMess=false; 
  }
}
