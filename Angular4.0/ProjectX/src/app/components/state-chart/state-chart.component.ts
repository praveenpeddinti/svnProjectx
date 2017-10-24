import { Component, OnInit,Input,ViewChild } from '@angular/core';
import {SharedService} from '../../services/shared.service';
// import * as Chart from 'chart.js';

declare var jQuery:any;
declare var Chart:any;

@Component({
  selector: 'app-state-chart',
  templateUrl: './state-chart.component.html',
  styleUrls: ['./state-chart.component.css']
})
export class StateChartComponent implements OnInit {

  public pieChartLabels:string[]=[];
  public pieChartData:any[] = [];
  public pieChartType:string;
  private loaded = false;
  private data:any;
  private options = {
              scaleShowValues: true,
            scales: {
                  yAxes: [{
                    ticks: {
                    }
                  }],
                  xAxes: [{
                    ticks: {
                      autoSkip: false
                    }
                  }]
            }
            };
  private datasets = [
    {
      label: [],
      data: []
    }
  ];
  public barChartOptions:any = {legend: {position: 'right'}};
  @Input('stateCount') stateCount:any={};
  @Input('chartType') chartType:string;
  @Input('index') index;
  @Input('classStr') classStr;
  
  public barData:any[]=[];


  private gradient = true;
  private showXAxis = true;
  private showYAxis = true;
  private showLegend = true;
  private  view: any[] = [750, 400];
  private sum=0;

  constructor(private shared:SharedService) { }


  ngOnInit() { 

    if(window.screen.width >=600 && window.screen.width <= 767){
      this.view = [580, 300];
    }else if(window.screen.width >=300 && window.screen.width <= 599){
      this.view = [300, 250];
    }
    
    var key=Object.keys(this.stateCount);
    this.pieChartLabels=key;


  this.pieChartType = this.chartType;
  
        for(let i in key){
          this.sum+=this.stateCount[key[i]];
          this.pieChartData.push({value:[this.stateCount[key[i]]],name:key[i]});
          
       
        }
       
  
  }
  ngAfterViewInit(){
  }

  // events
  public chartClicked(e:any):void {
    console.log(e);
  }
 
  public chartHovered(e:any):void {
    console.log(e);
  }

}
