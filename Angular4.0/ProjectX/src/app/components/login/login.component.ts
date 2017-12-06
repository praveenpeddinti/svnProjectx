import { Component,OnInit } from '@angular/core';
import { LoginService, Collaborator } from '../../services/login.service';
import { Router,ActivatedRoute } from '@angular/router';
import { GlobalVariable } from '../../config';
import {AuthGuard} from '../../services/auth-guard.service';
import {SharedService} from '../../services/shared.service';
import {CookieService} from 'angular2-cookie/core';
@Component({
    selector: 'login-view',
    templateUrl: 'login-component.html',
    providers: [LoginService,AuthGuard]
})
export class LoginComponent implements OnInit{
    public isEmailValid=false;
    public title = "Login";
    public user = new Collaborator('', '');
    public errorMsg = '';
    public submitted = false;
    public checkData=false;
    public tURl;
     returnUrl: string;
    /*
    * Added by Padmaja
    * if the user object is set then we redirecting to dashboard page and if it is not set then redirecting to login
    */
    

    constructor(
        private _router: Router,
        private _service: LoginService,
        private _authGuard:AuthGuard,
        private route: ActivatedRoute,
        private shared:SharedService,
        private _cookieService:CookieService
        ) { 
           /*
           *@Lakshmi
           * Setting cookie to redirect page for specified url after login
           */
       this.route.queryParams.subscribe(
      params => 
      { 
            var rURL=params['returnUrl'];
            if(rURL!=undefined && rURL!=''){
               this._cookieService.putObject("rUrl",rURL);
            }
       })
    }

     ngOnInit(){
      var getAllObj=JSON.parse(localStorage.getItem("user"));
    if(getAllObj != null && getAllObj != "failure"){
        this._router.navigate(['user-dashboard']); 
    }else{
        this._router.navigate(['login']);
    }        
    }
/*
* Added by Padmaja
*This is login used for login of the user it returns string if it is error and redirect to the dashboard page if it is success
*/        
    login() {
         this._service.login(this.user,(data)=>{ 
            if(data.status==200){
            //Getting cookie to redirect page for specified url after login
            var rURL =this._cookieService.getObject("rUrl");
           
           if(rURL!=undefined && rURL!=''){
             
                    var rURLString:string;
                    rURLString = JSON.stringify(rURL);
                    rURLString = rURLString.replace(/"/g,'');
                    this._cookieService.remove("rUrl");
                    var urlPartArray = rURLString.split("?");
                    if(urlPartArray.length>1){
                      var queryParams = urlPartArray[1];
                    var queryParamArray = queryParams.split("=");
                    this._router.navigate([urlPartArray[0]], { queryParams: { Slug : queryParamArray[1] } });
                   }else{
                 this._router.navigate([rURL]);
              }
           
           }else{
             this._router.navigate(['user-dashboard']); 
             this.shared.change(null,null,'LogIn',null,'');  //newly changed
             var userInfo=JSON.parse(localStorage.getItem("user"));
             this._cookieService.put('user',userInfo.Id);
             }
            }else{
                this.checkData=true;
                this.errorMsg = 'Invalid Email/Password';
            }
           
        });

    }
/*To  clear the error mesasge */
unsetValidation(){
       this.checkData=false;
     }
    
     
/*
* Added by Padmaja
* Validating Email in Email pattern
*/
    validateEmail(email){
      var pattern=/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
      this.isEmailValid = pattern.test(email); 
    }
}

