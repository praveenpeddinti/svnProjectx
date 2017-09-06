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

  //public pieChartLabels:string[] = ['Download Sales', 'In-Store Sales', 'Mail Sales'];
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
                      // beginAtZero: true
                    }
                  }],
                  xAxes: [{
                    ticks: {
                      // beginAtZero: true,
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
  public barData:any[]=[];


  private gradient = true;
  private showXAxis = true;
  private showYAxis = true;
  private showLegend = true;
  private  view: any[] = [750, 400];

  constructor(private shared:SharedService) { }


  ngOnInit() { 
// alert(window.screen.width);
    //  this.shared.getStateStats().subscribe(value=>
    //  {
    //    alert("==From Shared=="+JSON.stringify(value));
    //  });
    if(window.screen.width >=600 && window.screen.width <= 767){
      this.view = [580, 300];
    }else if(window.screen.width >=300 && window.screen.width <= 599){
      this.view = [300, 250];
    }
    
    var key=Object.keys(this.stateCount);
    this.pieChartLabels=key;
    //this.pieChartData=[this.stateCount.New,this.stateCount.Paused,this.stateCount.InProgress,this.stateCount.Waiting,this.stateCount.Reopened,this.stateCount.Closed];


  this.pieChartType = this.chartType;
  // if(this.pieChartType == "pie"){
  //   this.pieChartData=[
  //     {data:[this.stateCount.New,this.stateCount.Paused,this.stateCount.InProgress,this.stateCount.Waiting,this.stateCount.Reopened,this.stateCount.Closed],
  //       label:""
  //   }];
    //this.loaded = true;
  // }else{
   //this.pieChartLabels=[' '];
    // this.barChartOptions=
    // {
    //   // scaleShowVerticalLines: false,
    //   // responsive: true
    //   scales: {
    //         yAxes: [{
    //             ticks: {
    //                 beginAtZero: true,
    //                 maxTicksLimit: 5,
                    
    //             }
    //         }],
    //         xAxes: [{
    //             categoryPercentage: 1.0,
    //             barPercentage: 0.6
    //         }]
    //     },
    //     legend:{position:'bottom'}
    // };
        // alert(JSON.stringify(this.stateCount));
        for(let i in key){
          if(this.stateCount[key[i]]!=0){
          this.pieChartData.push({value:[this.stateCount[key[i]]],name:key[i]});
          }
          //this.pieChartData.push({data:[10,20,30,40,50,60,70,80,90,100,110,120,130,140],label:'New'});
         //this.barData.push(this.stateCount[key[i]]);
        }
        // alert(JSON.stringify(this.pieChartData));
// this.data = {
//             labels: this.pieChartLabels,
//             datasets: this.pieChartData
//         };
       // this.pieChartData.push({data:this.barData,label:"ProjectX"});
        // this.loaded = true;
    

    // }
  
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
