import { Component, OnInit,ViewChild } from '@angular/core';
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

 // public isInvite:boolean=false;
  public projectName;
  public projectId;
  public inviteUsers;
  public selectedUsers;
  public selectedUser:any[]=[];
  public isEmailValid:boolean=false;
  public isEmpty:boolean=false;
  public emailList:any[]=[];
  public isSuccess:boolean=false;

  constructor(private _ajaxService: AjaxService,private _router:Router,private route:ActivatedRoute,private projectService:ProjectService) { }

  ngOnInit() {

    var thisObj=this;
    thisObj.route.queryParams.subscribe(
      params => 
      { 
        thisObj.route.params.subscribe(params => {
              thisObj.projectName=params['projectName'];
              this.projectService.getProjectDetails(thisObj.projectName,(data)=>{ 
                  if(data.data!=false){
                    thisObj.projectId=data.data.PId;
                    console.log(thisObj.projectId);
                  }
            })
          })
      })
  }
  // showToInviteUsers(){
  //   this.isInvite=true;
  // }

  searchUsersToInvite(event){ 
    this.isEmailValid=false;
    var search_obj={query:event.query,projectId:this.projectId};
    var user_data=[];
    this._ajaxService.AjaxSubscribe("collaborator/get-invite-users",search_obj,(result)=>
    {
      if(result.status=200)
      {
        for(let user of result.data)
        {
          var user_info=user.UserName +" " + "(" +user.Email+")";
          user_data.push(user_info); 
        }
         this.inviteUsers=user_data;
         this.selectedUsers="";
      }
    })   
  }

  selectedValue(value){ 
    if(!(this.selectedUser.indexOf(value)>-1))
    {
      this.selectedUser.push(value);
      this.isEmpty=false;
    }
    this.selectedUsers=undefined;
    jQuery(".ui-chips-input-token>input[type='text']").attr("disabled","disabled");
  }

  validateEmail(email){ 
      this.isEmpty=false;
      var pattern=/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
      this.isEmailValid = pattern.test(email);
      if(this.isEmailValid)
      {
        this.isEmailValid=false;
        this.selectedUser.push(email);
        this.selectedUsers=undefined;
      }
      else{
        this.isEmailValid=true;
      }
    }

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
                console.log("Email Sent");
                this.selectedUser=[];
                this.selectedUsers=undefined;
                this.isSuccess=true;
              }
            })
      }
    }

    cancelInvitation(){
      this.selectedUser=[];
      jQuery("#inviteModel").modal('hide');
      this.isEmpty=false;
    }

  
  

}
