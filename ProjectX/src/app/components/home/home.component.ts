import { Component } from '@angular/core';
import { LoginService } from '../../services/login.service';

@Component({
    selector: 'home-view',
    providers: [LoginService],
    templateUrl: 'home-component.html'       
    	
})

export class HomeComponent {

    constructor(
        private _service: LoginService) { }

    ngOnInit() {
 //       this._service.checkCredentials();
    }

    logout() {
        this._service.logout();
    }
}