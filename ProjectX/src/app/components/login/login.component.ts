import { Component,OnInit } from '@angular/core';
import { LoginService, Collaborator } from '../../services/login.service';
import { Router,ActivatedRoute } from '@angular/router';
import { GlobalVariable } from '../../config';
import {AuthGuard} from '../../services/auth-guard.service';


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
    returnUrl: string;
    ngOnInit(){
        // reset login status
        //this._service.logout();
 
        // get return url from route parameters or default to '/'
       // this.returnUrl = this.route.snapshot.queryParams['returnUrl'] || '/';
    }
    onSubmit() {
        this.submitted = true;
    }
    constructor(
        private _router: Router,
        private _service: LoginService,
        private _authGuard:AuthGuard,
        private route: ActivatedRoute
        ) { }

    login() {
        this._service.login(this.user,(data)=>{ 
            if(data.status==200){
              this._router.navigate(['story-dashboard']);  
            }else{
           // console.log("comming..");
           this.checkData=true;
            this.errorMsg = 'Invalid Email/Password';
            }

        });

    }

    validateEmail(email){
        console.log("------"+email);
        var pattern=/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
      this.isEmailValid = pattern.test(email); 
       console.log("------"+this.isEmailValid); 
    }
}

