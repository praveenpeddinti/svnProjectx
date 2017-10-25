import { Component, OnInit,ViewChild,NgZone } from '@angular/core';
import { AjaxService } from '../../ajax/ajax.service';
import {Router,ActivatedRoute} from '@angular/router';
import { ProjectService } from '../../services/project.service';

declare var jQuery:any;
@Component({
  selector: 'app-email-invite',
  templateUrl: './email-invite.component.html',
  styleUrls: ['./email-invite.component.css'],
  providers: [ProjectService]
})


export class EmailInviteComponent implements OnInit {

  public projectName;
  public projectId;
  public inviteUsers;
  public selectedUsers;
  public selectedUser:any[]=[];
  public isEmailValid:boolean=false;
  public isEmpty:boolean=false;
  public emailList:any[]=[];
  public isSuccess:boolean=false;
  public checkAutoComplete:boolean=false;
  public noResult:boolean=false;
  public displayContainerDiv:boolean=false;
  public isExist:boolean=false;

  constructor(private _ajaxService: AjaxService,private _router:Router,private route:ActivatedRoute,private projectService:ProjectService,private zone:NgZone) { }

  ngOnInit() {

    var thisObj=this;
    thisObj.route.queryParams.subscribe(
      params => 
      { 
        thisObj.route.params.subscribe(params => {
           var projectName=decodeURIComponent(params['projectName']);
           this.projectName=projectName;
              this.projectService.getProjectDetails(thisObj.projectName,(data)=>{ 
                  if(data.data!=false){
                    thisObj.projectId=data.data.PId;
                    console.log(thisObj.projectId);
                  }
            })
          })
      });
                                 
  }
  /**
 * @description Checking the user is available or not and getting the username if exists.
 */

  searchUsersToInvite(event){ 
    this.isEmailValid=false;
    var search_obj={query:event.query,projectId:this.projectId};
    var user_data=[];
    this._ajaxService.AjaxSubscribe("collaborator/get-invite-users",search_obj,(result)=>
    {
      if(result.statusCode==200)
      {
        for(let user of result.data)
        {
          var user_info=user.UserName +" " + "(" +user.Email+")";
          user_data.push(user_info); 
          this.noResult=false;
          this.selectedUsers=null;
        }
         this.inviteUsers=user_data;
      
         if(this.inviteUsers.length==0){
          this.inviteUsers=['No Results'];
       
          this.checkEmail(event.query);
         }
      }
    })   
  }
/**
 * @description Auto populating in the text field when user selects the username.
 */
  selectedValue(value){ this.checkAutoComplete=true; //This is used to avoid conflict between (keyup.enter) and default enter of component
    if(!(this.selectedUser.indexOf(value)>-1))
    {
      if(value!="No Results"){ 
      this.selectedUser.push(value);
      
      jQuery("#invite_placeholder").hide();
      this.isExist=false;
      }
      this.isEmpty=false;
    }
    this.selectedUsers=undefined;
    jQuery(".ui-chips-input-token>input[type='text']").attr("disabled","disabled");
  }
/**
 * @description Validating the email format
 */
  validateEmail(object){ 
     
      if(!this.checkAutoComplete){
          var email =  object["inputEL"]["nativeElement"]["value"];
          this.isEmpty=false;
          this.isExist=false;
          var pattern=/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
          this.isEmailValid = pattern.test(email.trim());
          if(this.isEmailValid)
          {
            this.isEmailValid=false;
            if(!(this.selectedUser.indexOf(email)>-1)) /*added newly for change in email logic */
            {
              this.selectedUser.push(email);
              this.isEmpty=false;
            }
            object["inputEL"]["nativeElement"]["value"]="";
          }
          else{
            this.isEmailValid=true;
          }
        }
      this.checkAutoComplete=false;
    }

/**
 * @description To invite the users through the email.
 */
  sendInvitation(){ 
      if(this.selectedUser.length==0 && (this.selectedUsers==undefined || this.selectedUsers=='')){
        this.isEmpty=true;
      }else{
            var email_list;
            for(let email of this.selectedUser){
              var email_crud=email;
              if(email_crud.indexOf('(')>-1){
                email_list=email_crud.substr(email_crud.indexOf('(') + 1).slice(0, -1);
              }else{
                email_list=email;
              }
              this.emailList.push(email_list);
            }
            var invite_obj={recepients:this.emailList,projectName:this.projectName};
            this._ajaxService.AjaxSubscribe("collaborator/send-invite",invite_obj,(result)=>
            {
              if(result.statusCode==200)
              {
                if(result.data=='success'){
                console.log("Email Sent");
                this.selectedUser=[];
                this.selectedUsers=undefined;
                this.isSuccess=true;
                this.emailList=[];
                }else{
                  console.log("Email Not Sent");
                }
                setTimeout(()=>{jQuery("#inviteModel").modal('hide');this.isSuccess=false;},3000);
              }
            })
      }
    }
/**
 * @description  To cancel the user from sending invitations
 */

    cancelInvitation(){
      this.selectedUser=[];
      jQuery("#inviteModel").modal('hide');
      this.isEmpty=false;
      
      jQuery("#invite_search").attr("value",""); //jquery was used since model binding was not getting updated....
      this.isSuccess=false;
      this.isEmailValid=false;
      this.isExist=false;
     
      jQuery("#invite_placeholder").show();
    }
    
/**
 * @description To check the email exists or not
 */

    checkEmail(email){
      this.isExist=false;
      var pattern=/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
      var status = pattern.test(email.trim());
      if(status==true){
        this.noResult=true;
      }else{
        this.noResult=false;
      }
    }
/**
 * @description Adding new user if there is no user with the given details.
 */
    addNewUser(){ 
       var userInfo={email:this.selectedUsers,projectId:this.projectId};
       var userMail=this.selectedUsers;
       this._ajaxService.AjaxSubscribe("collaborator/check-user-exist",userInfo,(result)=>
       {
          if(result.statusCode==200){
              if(result.data=="not exist"){ 
                  this.selectedValue(userMail);
                  this.selectedUsers=null;
                }else{
                    /*Show error message */
                    this.isExist=true;
                    this.selectedUsers=userMail;
                  }
            }
        });
     
    }

}
