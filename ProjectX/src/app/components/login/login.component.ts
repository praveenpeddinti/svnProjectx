import { Component } from '@angular/core';
import { LoginService, User } from '../../services/login.service';

@Component({
    selector: 'login-view',
    templateUrl: 'login-component.html',
    providers: [LoginService]
})
export class LoginComponent {
    public isEmailValid=false;
    public title = "Login";
    public user = new User('', '');
    public errorMsg = '';
    public submitted = false;
    onSubmit() {
        this.submitted = true;
    }
    constructor(
        private _service: LoginService) { }

    login() {
        if (!this._service.login(this.user)) {
            this.errorMsg = 'Failed to login';
        }
    }

    validateEmail(email){
        console.log("------"+email);
        var pattern=/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
      this.isEmailValid = pattern.test(email); 
       console.log("------"+this.isEmailValid); 
    }
}

