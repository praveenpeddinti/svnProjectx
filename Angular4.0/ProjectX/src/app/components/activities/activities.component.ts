import { Component, OnInit } from '@angular/core';
@Component({
  selector: 'app-activities',
  inputs: ['dashboardData','noMoreActivities','noActivitiesFound'],
  templateUrl: './activities.component.html',
  styleUrls: ['./activities.component.css']
})
export class ActivitiesComponent implements OnInit {
  public noMoreActivities:boolean = false;
  public noActivitiesFound:boolean = false;
  constructor() { 
    
  }

  dashboardData:any[];
   ngOnInit() {
    //alert(dashboardData.activities);
   
  }

  
}
