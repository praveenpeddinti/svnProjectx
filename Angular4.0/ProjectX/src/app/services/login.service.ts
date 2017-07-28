import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { Headers, Http } from '@angular/http';
import { AjaxService } from '../ajax/ajax.service';
declare var io:any;
declare var socket:any;
import 'rxjs/add/operator/toPromise';
export class Collaborator {
  constructor(
    public email?: string,
    public password?: string,
    public rememberme?: boolean,
    public collabaratorStatus?:boolean) { }
}


@Injectable()
export class LoginService {
  constructor(
    private _ajaxService: AjaxService,
    public _router: Router,
    private http: Http) { }
/*
  * Added by Padmaja
  * logout the current Collabarator
 */
  logout(logoutCallback) {
   socket.emit("clearInterval");
      this._ajaxService.AjaxSubscribe("site/update-collabarator-status",{},function(result){
       localStorage.removeItem("user");
       localStorage.removeItem("projectId");
       localStorage.removeItem("projectName");
       logoutCallback(result);
    }); 
  }

  public collaboratorObj = {
    'username': '',
    'password': '',
    'rememberme':'',
  }
 
// posting collaboratorObject to UserAuthentication
  
login(user,loginCallback) {
    this.collaboratorObj.username=user.email;
    this.collaboratorObj.password=user.password;
    this.collaboratorObj.rememberme=user.rememberme;
   // this.collaboratorObj.AccessKey=user.AccessKey;

    this._ajaxService.AjaxSubscribe("site/user-authentication",this.collaboratorObj,(result)=>
    { 
      var userdata = result.data;
      localStorage.setItem("profilePicture",userdata.ProfilePicture);
      delete userdata.ProfilePicture;
      delete userdata.Email;
      localStorage.setItem("user",JSON.stringify(userdata));
      //this.collaboratorObj.AccessKey=token;//alert(this.collaboratorObj.headers);
      loginCallback(result);
    });
  

  }
  

  private handleError(error: any): Promise<any> {
    console.error('An error occurred', error); // for demo purposes only
    return Promise.reject(error.message || error);
  }


  // var authenticatedUser = users.find(u => u.email === user.email);
  // if (authenticatedUser && authenticatedUser.password === user.password){
  //   localStorage.setItem("user", authenticatedUser);
  //   this._router.navigate(['home']);      
  //   return true;
  // }




  checkCredentials() {
     if (localStorage.getItem("user") === null) {
       this._router.navigate(['login']);
    }
    return localStorage.getItem("user");
  }

}