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
 public userProfileImage:string;
 public isValidFile:boolean=true;
  @Output() myevent=new EventEmitter();
public inviteCode;

  constructor(private _ajaxService: AjaxService,private _router:Router,private route:ActivatedRoute,private projectService:ProjectService,private fileUploadService: FileUploadService,) { }

  ngOnInit() {

     var thisObj =this;
     
     this.userProfileImage = GlobalVariable.BASE_API_URL+"files/user/user_noimage.png";
     this.form["userProfileImage"] = "/files/user/user_noimage.png"; 
    thisObj.route.queryParams.subscribe(
      params => 
      { 
         thisObj.inviteCode=params['code'];
        this.projectService.getUserDetails( thisObj.inviteCode,(data)=>{
          if(data.statusCode==200){
            if(data.data == "invalidCode"){
              setTimeout(function(){
                  thisObj._router.navigate(['login']);
              },3000)
            
            }else{
              this.form['email']=data.data.Email;
              this.projectName=data.data.ProjectName;
              thisObj.projectId=data.data.ProjectId;
            }
           
          }
        })
      })
  }

public prepareDisplayName(){
  if((this.form['firstName']!='' && this.form['firstName']!=undefined) && (this.form['lastName']!='' && this.form['lastName']!=undefined))
  this.form['displayName']=this.form['firstName']+this.form['lastName'];
}
    saveUser()
    {    
      if(this.form['password'] ==this.form['confirmpassword']){
        this.isPasswordMatch=true;
      }else{
        this.isPasswordMatch=false;
         jQuery("#mismatch_error").show(); //used jquery since Password mismatch validation doesn't sync with Form Inbuilt Validation
      }

      if(this.isPasswordMatch && this.isValidFile)
      {
        // Make an ajax to save the User
        //this.projectImage=jQuery('#projectlogo').attr("src");
       // var URL = this.projectImage;
        //var imageURL = URL.replace (/^[a-z]{4}\:\/{2}[a-z]{1,}\:[0-9]{1,4}.(.*)/, '$1');
        //var link = document.createElement('a');
       // link.setAttribute('href', URL);
        var invite_obj={projectId:this.projectId,user:this.form,code:this.inviteCode};
        this._ajaxService.AjaxSubscribe("collaborator/save-user",invite_obj,(result)=>
        {
          if(result.statusCode==200)
          { 
              var user={'Id':result.data.Id,'username':result.data.UserName,'token':''};
              localStorage.setItem('profilePicture',result.data.ProfilePic);
              localStorage.setItem('ProjectName',this.projectName);
              localStorage.setItem('user',JSON.stringify(user));
              this._router.navigate(['user-dashboard']);    
          }
        })   
        
      }
      else
      {
        //Error Message for Mismatch Password
        this.isPasswordMatch=false;
      }
    }

    // fileUpload(fileInput: any, comeFrom: string){ 
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
            this.fileUploadService.makeFileRequest(GlobalVariable.IMAGE_UPLOAD_URL, [], this.filesToUpload).then(
                (result :Array<any>) => {
    
                for(var i = 0; i<result.length; i++){
                  this.form["userProfileImage"] = "/"+result[i].path;
                  this.userProfileImage = GlobalVariable.BASE_API_URL + result[i].path;
                
                  this.form["originalImageName"] = result[i].originalname;
                   
                   result[i].originalname =  result[i].originalname.replace(/[^a-zA-Z0-9.]/g,'_'); 
                    var uploadedFileExtension = (result[i].originalname).split('.').pop();
                     if(uploadedFileExtension == "png" || uploadedFileExtension == "jpg" || uploadedFileExtension == "jpeg" || uploadedFileExtension == "gif") 
                     {
                        this.isValidFile=true;
                     }
                     else{
                       this.isValidFile=false;
                       }//      var postData={
                  //             logoName:"[[image:" +result[i].path + "|" + result[i].originalname + "]]"
                  //           }
                  //         this._ajaxService.AjaxSubscribe("site/get-project-image",postData,(result)=>
                  //           {
                  //               if(result.data){
                  //                  this.userProfile = result.data;
                  //                  // jQuery("#projectlogo").attr("src",result.data);
                  //                   this.fileExtention=uploadedFileExtension;
                  //              }
                  //              this.projectImage=jQuery("#projectlogo").attr("src");
                            
                  //   });
                  //    } else{
                  //  }
                }
                this.fileUploadStatus = false;
            }, (error) => {
                console.error("Error occured in story-formcomponent::fileUploadEvent"+error);
                this.fileUploadStatus = false;
            });
    }

    public checkConfirmField(event){

         if(event==''){
           jQuery("#mismatch_error").hide(); //used jquery since Password mismatch validation doesn't sync with Form Inbuilt Validation
         }
    }

}
