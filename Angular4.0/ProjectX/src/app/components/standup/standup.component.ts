import { Component} from '@angular/core';
import {AuthGuard} from '../../services/auth-guard.service';
import { GlobalVariable } from '../../config';
import { Router,ActivatedRoute } from '@angular/router';

@Component({
    selector: 'standup-view',
    providers: [AuthGuard],
    templateUrl: 'standup.html'       
    	
})

export class StandupComponent{

public minDate:Date;
    ngOnInit() {
        this.minDate = new Date();
   }
     constructor(
         
          private _authGuard:AuthGuard
          ) { }
    

}