import { Component, OnInit } from '@angular/core';
@Component({
  selector: 'app-activities',
  inputs: ['activityDetails'],
  templateUrl: './activities.component.html',
  styleUrls: ['./activities.component.css']
})
export class ActivitiesComponent implements OnInit {

  constructor() { 
    
  }

  activityDetails:any[];

  ngOnInit() {
  }

  
}
