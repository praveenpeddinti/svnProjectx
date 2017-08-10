import { Component, OnInit,Output,EventEmitter } from '@angular/core';
import {Router,ActivatedRoute} from '@angular/router';
import { AjaxService } from '../../ajax/ajax.service';
import { ProjectService } from '../../services/project.service';
//import {UserDashboardComponent} from '../user-dashboard/user-dashboard.component';
import { GlobalVariable } from '../../config';
import { FileUploadService } from '../../services/file-upload.service';

declare var jQuery:any;
@Component({
  selector: 'app-create-user',
  templateUrl: './create-user.component.html',
  styleUrls: ['./create-user.component.css'],
  providers: [ProjectService]
})
export class CreateUserComponent implements OnInit {

  public form={};
  public isEmailValid;
  public isPasswordMatch:boolean=true;
  public projectName;
  public projectId;
  //public userComponent:UserDashboardComponent;
  public fileuploadClick=false;
  public filesToUpload: Array<File>;
  public hasBaseDropZoneOver:boolean = false;
  public hasFileDroped:boolean = false;
  public projectImage:any;
  public fileExtention:any;
  public fileUploadStatus:boolean = false;

  @Output() myevent=new EventEmitter();

  constructor(private _ajaxService: AjaxService,private _router:Router,private route:ActivatedRoute,private projectService:ProjectService,private fileUploadService: FileUploadService,) { }

  ngOnInit() {
     var thisObj=this;
    thisObj.route.queryParams.subscribe(
      params => 
      { 
        this.form['email']=params['email'];
        //this.form['email']=localStorage.getItem('email');
        thisObj.route.params.subscribe(params => {
              thisObj.projectName=params['projectName'];
              this.projectService.getProjectDetails(thisObj.projectName,(data)=>{ 
                  if(data.data!=false){
                    thisObj.projectId=data.data.PId;
                  }
            })
        })
      })
  }


    saveUser()
    {
     
      if(this.form['password'] ==this.form['confirmpassword']){
        this.isPasswordMatch=true;
      }else{
        this.isPasswordMatch=false;
      }

      if(this.isPasswordMatch)
      {
        // Make an ajax to save the User
        this.projectImage=jQuery('#projectlogo').attr("src");
        
        var invite_obj={projectId:this.projectId,user:this.form,profile:this.projectImage};
        this._ajaxService.AjaxSubscribe("collaborator/save-user",invite_obj,(result)=>
        {
          if(result.statusCode==200)
          {
            if(result.data>1){
              var email_obj={projectId:this.projectId,email:this.form['email']};
              this._ajaxService.AjaxSubscribe("story/invalidate-invitation",email_obj,(status)=>
              {
                if(status.statusCode==200){
                  
                    var user={'Id':status.data.Id,'username':status.data.UserName,'token':''};
                    localStorage.setItem('profilePicture',status.data.ProfilePic);
                    localStorage.setItem('ProjectName',this.projectName);
                    localStorage.setItem('user',JSON.stringify(user));
                    this._router.navigate(['user-dashboard']);//navigate to User Dashboard....
                }
              });
              
            }
          }
        })   
        
      }
      else
      {
        //Error Message for Mismatch Password
        this.isPasswordMatch=false;
      }
    }

    // fileUpload(fileInput: any, comeFrom: string){ alert("in file upload");
    //  // this.userComponent.fileUploadEvent(fileInput,comeFrom);
    //  this.myevent.emit(fileInput,comeFrom);
    // }

    CallFileupload(){
       jQuery("input[id='my_file']").click(); 
      this.fileuploadClick=true;
    }

    public fileUpload(fileInput: any, comeFrom: string):void 
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

}
