import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { Headers, Http } from '@angular/http';
import { AjaxService } from '../ajax/ajax.service';

import 'rxjs/add/operator/toPromise';
export class User {
  constructor(
    public email?: string,
    public password?: string) { }
}


@Injectable()
export class LoginService {
  constructor(
    private _ajaxService: AjaxService,
    public _router: Router,
    private http: Http) { }

  logout() {
    localStorage.removeItem("user");
    //this._router.navigate(['login']);
  }

  public user_data = {
    'username': '',
    'password': '',
    'AccessKey': '3fd31d9a7ae286b9c6da983b35359915'
  }
 
login(user,loginCallback) {
    this.user_data.username=user.email;
    this.user_data.password=user.password;
    this._ajaxService.AjaxSubscribe("site/login",this.user_data,(data)=>
    { 
         localStorage.setItem("user", this.user_data.username);
         loginCallback(data);
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
  }
}