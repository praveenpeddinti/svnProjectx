import { LoginService } from '../../services/login.service';
import {AuthGuard} from '../../services/auth-guard.service';
import { NgForm } from '@angular/forms';
import { Component, OnInit,ViewChild,Input,NgZone,HostListener } from '@angular/core';
import { Router,ActivatedRoute } from '@angular/router';
import { Headers, Http, Response } from '@angular/http';
import { AjaxService } from '../../ajax/ajax.service';
import {AccordionModule,DropdownModule,SelectItem} from 'primeng/primeng';
import {SharedService} from '../../services/shared.service';
import { ProjectService } from '../../services/project.service';
import { GlobalVariable } from '../../config';
import { DatePipe } from '@angular/common';
import { ProjectFormComponent } from '../../components/project-form/project-form.component';
declare var jQuery:any;
@Component({
  selector: 'app-user-dashboard',
  templateUrl: './user-dashboard.component.html',
  styleUrls: ['./user-dashboard.component.css'],
  providers:[AuthGuard]
})
export class UserDashboardComponent implements OnInit {
  @ViewChild(ProjectFormComponent) projectFormComponent: ProjectFormComponent;
  public users=JSON.parse(localStorage.getItem('user'));
  public projectOffset=0;
  public projectLimit=3;
  public activityOffset=0;
  public activityLimit=10;
  public dashboardData:any;
  public submitted=false;
  public creationPopUp=true;
  public noMoreActivities:boolean = false;
  public noMoreProjects:boolean = false;
  public noProjectsFound:boolean = false;
  public noActivitiesFound:boolean = false;
 public projectForm:string;
 public srch:any;
  constructor(
          private _router: Router,
          private _service: LoginService,
          private _authGuard:AuthGuard,
          private shared:SharedService,
          private _ajaxService: AjaxService,
          private zone:NgZone,
   ) { }

   ngAfterViewInit() { 
      }
  ngOnInit() {
     window.scrollTo(0,0);
    this.activityOffset=0;
    this.projectOffset =0;
    this.dashboardData ='';
    var thisObj = this;
    var req_params={
      projectOffset:this.projectOffset,
      projectLimit:this.projectLimit,
      activityOffset:this.activityOffset,
      activityLimit:this.activityLimit
    }
   thisObj.loadUserDashboard(req_params);
   this.shared.change('','','','','');
   }
/**
 * @description Fetching details to display in user dashboard
 */
  loadUserDashboard(req_params) {
    var thisObj = this;
    this._ajaxService.AjaxSubscribe('collaborator/get-user-dashboard-details', req_params, (result) => {
      if (result.statusCode == 200) {
        if (thisObj.projectOffset == 0 && thisObj.activityOffset == 0) {
          thisObj.dashboardData = result.data;
            thisObj.noMoreProjects = false;
            thisObj.noMoreActivities = false;
          if(thisObj.dashboardData.projects.length==0){
            thisObj.noProjectsFound=true;
          }
          if(thisObj.dashboardData.activities.length==0){
            thisObj.noActivitiesFound=true;
          }
        } else {
          var curActLength = thisObj.dashboardData.activities.length;
          if (result.data.projects.length > 0) {
            thisObj.dashboardData.projects =thisObj.dashboardData.projects.concat(result.data.projects);
          } else {
            thisObj.noMoreProjects = true;
          }
          if (result.data.activities.length > 0) {
            if (thisObj.dashboardData.activities[curActLength - 1].activityDate == result.data.activities[0].activityDate) {
               thisObj.dashboardData.activities[curActLength - 1].activityData = thisObj.dashboardData.activities[curActLength - 1].activityData.concat(result.data.activities[0].activityData)
               result.data.activities .splice(0, 1);
               thisObj.dashboardData.activities=thisObj.dashboardData.activities.concat(result.data.activities);
            } else {
              thisObj.dashboardData.activities=thisObj.dashboardData.activities.concat(result.data.activities);
            }
          } else {
            thisObj.noMoreActivities = true;
          }
          
        }

      }


    });
  }

    creationProject(){
      this.projectFormComponent.creationProject();
    }
/**
 * @description loading notification to display on page load
 */
     @HostListener('window:scroll', ['$event']) 
    loadNotificationsOnScroll(event) {
      if ((!this.noMoreActivities || !this.noMoreProjects ) && jQuery(window).scrollTop() == jQuery(document).height() - jQuery(window).height()) {
          var thisObj = this;
          this.projectOffset= this.projectOffset+1;
          this.activityOffset = this.activityOffset +1;
          var req_params={
            projectOffset:this.projectOffset,
            projectLimit:this.projectLimit,
            activityOffset:this.activityOffset,
            activityLimit:this.activityLimit
          }
          thisObj.loadUserDashboard(req_params);   
      }
    }
/**
 * @description Redirecting to ticket page when click
 */
  goToTicket(project,ticketid,notify_id,comment)
  {
    this._router.navigate(['project',project.ProjectName,ticketid,'details'],{queryParams: {Slug:comment}});
  }
/**
 * @description Providing global search option 
 */
  globalSearch(){
   var searchString=this.srch;
    if (typeof searchString !== 'undefined'){
      this._router.navigate(['search',],{queryParams: {q:searchString}});
    }else{
    }
     
  }

}
