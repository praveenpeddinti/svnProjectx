import { Component,OnInit,HostListener } from '@angular/core';
import { Router,ActivatedRoute } from '@angular/router';
import { GlobalVariable } from '../../config';
import {AuthGuard} from '../../services/auth-guard.service';
import { AjaxService } from '../../ajax/ajax.service';
import {SharedService} from '../../services/shared.service';
declare var jQuery:any;
@Component({
   selector: 'page-not-found',
    templateUrl: 'pagenotfound.html',
    providers: [AuthGuard]
})
export class PageNotFoundComponent implements OnInit{
    public searchString="";
    public allNotification=[];
    public notify_count:any=0;
    public stringPosition;
    public pageNo=1;
    private page=1;
    public nomorenotifications:boolean= false;
    public ready=true;
     constructor(
        private _router: Router,
         private _authGuard:AuthGuard,
        private route: ActivatedRoute,
        private _ajaxService: AjaxService,
        private shared:SharedService
        ) {

         }

    ngOnInit(){
    
  this.shared.change(this._router.url,null,'Error','Other'); //added for breadcrumb purpose
}
}