import { Component, OnInit } from '@angular/core';
import { Router,ActivatedRoute } from '@angular/router';
@Component({
  selector: 'app-activities',
  inputs: ['dashboardData','noMoreActivities','noActivitiesFound'],
  templateUrl: './activities.component.html',
  styleUrls: ['./activities.component.css']
})
export class ActivitiesComponent implements OnInit {
  public noMoreActivities:any;
  public noActivitiesFound:any;
  constructor(public _router: Router) { 
    
  }

  dashboardData:any[];
   ngOnInit() {
    console.log("@@@@-------"+JSON.stringify(this.dashboardData));
   
  }

  
}
