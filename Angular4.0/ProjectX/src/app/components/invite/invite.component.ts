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

  ngOnInit() { alert("in invite");
      var thisObj=this;
    thisObj.route.queryParams.subscribe(
      params => 
      { 
        thisObj.inviteCode=params['code'];
        thisObj.route.params.subscribe(params => {
              thisObj.projectName=params['projectName'];
              this.projectService.getProjectDetails(thisObj.projectName,(data)=>{ 
                  if(data.data!=false){
                    thisObj.projectId=data.data.PId;alert("==Project Id=="+thisObj.projectId);
                    console.log(thisObj.projectId);
                    thisObj.verifyInvitation(thisObj.inviteCode);
                  }
            })
          });
          
      })
  }

  verifyInvitation(code){
  
   var userid=parseInt(this._cookieService.get('user'));
   var id_from_code=parseInt(code.substring(10));
   alert("==Id from Cookie=="+userid);
   alert("==Id from Code=="+id_from_code);
   var invite_obj={inviteCode:code,projectId:this.projectId};
  //  this._ajaxService.AjaxSubscribe("story/verify-invitation-code",invite_obj,(result)=>
  //   { 
  //     if(result.statusCode==200)
  //     { 
  //       if(result.data.IsValid=='1'){
  //         if(parseInt(result.data.UserId)!=0){
  //           this._router.navigate(['user-dashboard']);
  //         }else{
  //           this._router.navigate(['project',this.projectName,'create-user'],{queryParams: {email:result.data.Email}});
  //         }
  //       }else{
  //         this._router.navigate(['login']);
  //       }
  //     }
  //   })   

  }

}
