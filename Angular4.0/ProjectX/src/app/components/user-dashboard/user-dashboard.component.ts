import { LoginService } from '../../services/login.service';
import {AuthGuard} from '../../services/auth-guard.service';
import { NgForm } from '@angular/forms';
import { Component, OnInit,ViewChild,Input,NgZone } from '@angular/core';
import { Router,ActivatedRoute } from '@angular/router';
import { Headers, Http, Response } from '@angular/http';
import { AjaxService } from '../../ajax/ajax.service';
import {AccordionModule,DropdownModule,SelectItem} from 'primeng/primeng';
import {SharedService} from '../../services/shared.service';
import { ProjectService } from '../../services/project.service';
@Component({
  selector: 'app-user-dashboard',
  templateUrl: './user-dashboard.component.html',
  styleUrls: ['./user-dashboard.component.css']
})
export class UserDashboardComponent implements OnInit {
  public users=JSON.parse(localStorage.getItem('user'));
  public projectOffset=0;
  public projectLimit=3;
  public activityOffset=0;
  public activityLimit=10;
  public dashboardData:any;
  public projects:any;
  public activities:any;
  constructor(
          private _router: Router,
          private _service: LoginService,
          private _authGuard:AuthGuard,
          private shared:SharedService,
          private _ajaxService: AjaxService,
          private zone:NgZone,
  ) { }

  ngOnInit() {
    var thisObj = this;
    var req_params={
      projectOffset:this.projectOffset,
      projectLimit:this.projectLimit,
      activityOffset:this.activityOffset,
      activityLimit:this.activityLimit
    }
    thisObj.loadUserDashboard(req_params);
  }

loadUserDashboard(req_params){
  var thisObj=this;
  this._ajaxService.AjaxSubscribe('collaborator/get-user-dashboard-details',req_params,(result)=>{
   thisObj.dashboardData = result.data;
  });
}
}
