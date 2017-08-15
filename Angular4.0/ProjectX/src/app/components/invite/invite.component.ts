import { Component, OnInit } from '@angular/core';
import {Router,ActivatedRoute} from '@angular/router';
import { ProjectService } from '../../services/project.service';
import { AjaxService } from '../../ajax/ajax.service';
import {CookieService} from 'angular2-cookie/core';

@Component({
  selector: 'app-invite',
  templateUrl: './invite.component.html',
  styleUrls: ['./invite.component.css'],
  providers: [ProjectService]
})
export class InviteComponent implements OnInit {
  
  public projectName;
  public projectId;
  public inviteCode;

  constructor(private _ajaxService: AjaxService,private _router:Router,private route:ActivatedRoute,private projectService:ProjectService,private _cookieService:CookieService) {
  
   }

  ngOnInit() {
      var thisObj=this;
    thisObj.route.queryParams.subscribe(
      params => 
      { 
        thisObj.inviteCode=params['code'];
        thisObj.verifyInvitation(thisObj.inviteCode);
        // thisObj.route.params.subscribe(params => {
        //       thisObj.projectName=params['projectName'];
        //       this.projectService.getProjectDetails(thisObj.projectName,(data)=>{ 
        //           if(data.data!=false){
        //             thisObj.projectId=data.data.PId;
        //             console.log(thisObj.projectId);
        //             thisObj.verifyInvitation(thisObj.inviteCode);
        //           }
        //     })
        //   });
          
      })
  }

  verifyInvitation(code){ 
  
   var userid_from_cookie=parseInt(this._cookieService.get('user'));
   var id_from_code=parseInt(code.substring(10));
   var invite_obj={inviteCode:code};
   this._ajaxService.AjaxSubscribe("collaborator/verify-invitation-code",invite_obj,(result)=>
    { 
      if(result.statusCode==200)
      { 
        
        if(result.data.IsValid=='1'){
           this.projectId=result.data.ProjectId;
          if(parseInt(result.data.UserId)!=0){  //for existing user...
            if((userid_from_cookie==id_from_code)){ //auto-login if user exists in cookie....
              var email_obj={inviteCode:code,email:result.data.Email};
              this.invalidateInvite(email_obj,id_from_code);
            }else{
              this._router.navigate(['login']); // if cookie doesn't exist for user.....
            }
          }else{ //for new user.....
            this._router.navigate(['project',result.data.ProjectName,'create-user'],{queryParams: {email:result.data.Email,code:code}});
          }
        }else{
          this._router.navigate(['login']);
        }
      }
    })   

  }

  invalidateInvite(email_obj,id_from_code){ //make existing user invite invalid and add to team

    this._ajaxService.AjaxSubscribe("collaborator/invalidate-invitation",email_obj,(status)=>
              {
                if(status.statusCode==200){
                    var user_obj={projectId:this.projectId,userid:id_from_code};
                    this._ajaxService.AjaxSubscribe("collaborator/add-to-team",user_obj,(result)=>{
                      if(result.statusCode==200){
                        this._router.navigate(['user-dashboard']);//navigate to User Dashboard....
                      }
                    });
                    
                }
              });
  }

}
