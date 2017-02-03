import { Component } from '@angular/core';
import { LoginService } from '../../services/login.service';

@Component({
    selector: 'home-view',
    providers: [LoginService],
    templateUrl: 'home-component.html'       
    	
})

export class HomeComponent {
    public users=['anand@techo2.com','jagadish@techo2.com','moin@techo2.com','madan@techo2.com','rahul@techo2.com','kishore@techo2.com']
    constructor(
        private _service: LoginService) { }

    ngOnInit() {
 //       this._service.checkCredentials();
    }

    logout() {
        this._service.logout();
    }
}