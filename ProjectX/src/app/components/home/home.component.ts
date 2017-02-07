import { Component} from '@angular/core';
import { LoginService } from '../../services/login.service';
import {AuthGuard} from '../../services/auth-guard.service';
import { GlobalVariable } from '../../config';
import { Router,ActivatedRoute } from '@angular/router';

@Component({
    selector: 'home-view',
    providers: [LoginService,AuthGuard],
    templateUrl: 'home-component.html'       
    	
})

export class HomeComponent{
    public users=JSON.parse(localStorage.getItem('user'));


    ngOnInit() {
       this._service.checkCredentials();
   }
     constructor(
         
         private _service: LoginService,
          private _authGuard:AuthGuard
          ) { }
    
   
    logout() {
        this._service.logout();
    }
}