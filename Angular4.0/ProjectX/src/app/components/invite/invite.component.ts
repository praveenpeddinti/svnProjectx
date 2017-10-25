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
      })
  }
/**
 * @description To verify invited users details
 */
  verifyInvitation(code){ 
  
   if(localStorage.getItem('user')!=null){
      var userInfo=localStorage.getItem('user');
      var localUserInfo=JSON.parse(userInfo);
      var userid_from_local=parseInt(localUserInfo.Id);
   }
   var userid_from_cookie=parseInt(this._cookieService.get('user'));
   var id_from_code=parseInt(code.substring(10));
   var invite_obj={inviteCode:code};
   this._ajaxService.AjaxSubscribe("collaborator/verify-invitation-code",invite_obj,(result)=>
    { 
      if(result.statusCode==200)
      { 
        if(result.data.UserType=='Existing'){ 
          localStorage.setItem('user',null);
          localStorage.setItem('profilePicture',null);
          if(userid_from_cookie==id_from_code){ 
            var user={'Id':result.data.User.Id,'username':result.data.User.UserName,'token':''};
            localStorage.setItem('profilePicture',result.data.User.ProfilePic);
            localStorage.setItem('ProjectName',this.projectName);
            localStorage.setItem('user',JSON.stringify(user));
            this._router.navigate(['user-dashboard']);
          }else{
            this._router.navigate(['login']);
          }
        }
        else if(result.data.UserType=="New"){
       
          this._router.navigate(['create-user'],{queryParams: {code:code}});
        }else{
              if(userid_from_local==userid_from_cookie){
                this._router.navigate(['user-dashboard']);
              }else{
                    this._router.navigate(['login']);
              }
        }

      }
    })   

  }

}
