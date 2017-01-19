import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { Headers, Http } from '@angular/http';
import 'rxjs/add/operator/toPromise';
import { GlobalVariable } from '../../app/common';
export class User {
  constructor(
    public email?: string,
    public password?: string) { }
}

var users = [
  new User('admin@admin.com', 'adm9'),
  new User('user1@gmail.com', 'a23')
];

@Injectable()
export class LoginService {

  constructor(
    private _router: Router,
    private http: Http) { }

  logout() {
    localStorage.removeItem("user");
    this._router.navigate(['login']);
  }

  public user_data = {
    'username': '',
    'password': '',
    'AccessKey': '3fd31d9a7ae286b9c6da983b35359915'
  }
  private headers = new Headers({ 'Content-Type': 'application/x-www-form-urlencoded' });
  private Url = GlobalVariable.BASE_API_URL+'site/login';  // URL to web api
  login(user) {
    this.user_data.username=user.email;
    this.user_data.password=user.password;
    this.http.post(this.Url, JSON.stringify(this.user_data), this.headers)
      .subscribe(
      (data) => {
        var res = data.json();//For Success Response
        console.log(res.length)
        if(res.length == 1){
        localStorage.setItem("user", user.email);
        this._router.navigate(['home']);
        return true;
        }
        else{
          return true;
        }
        
      },
      err => { console.error("ERRR_____________________" + err) } //For Error Response
      );

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