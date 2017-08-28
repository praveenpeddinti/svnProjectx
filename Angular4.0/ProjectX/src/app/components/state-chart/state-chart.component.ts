import { Component, OnInit,Input } from '@angular/core';
import {SharedService} from '../../services/shared.service';

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
  private options = {
              scaleShowValues: true,
            scales: {
                  yAxes: [{
                    ticks: {
                      beginAtZero: true
                    }
                  }],
                  xAxes: [{
                    ticks: {
                      beginAtZero: true,
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
  constructor(private shared:SharedService) { }


  ngOnInit() { 

  this.pieChartType = this.chartType;
  var key=Object.keys(this.stateCount);
  this.pieChartLabels=key;
  // alert(this.pieChartLabels+"++keys++");
  if(this.pieChartType == "pie"){
    this.pieChartData=[
      {data:[this.stateCount.New,this.stateCount.Paused,this.stateCount.InProgress,this.stateCount.Waiting,this.stateCount.Reopened,this.stateCount.Closed],
        label:""
    }];
    this.loaded = true;
  }else{
    // alert(JSON.stringify(this.stateCount));
    for(let i in key){
      this.pieChartData.push({label:key[i],data:[this.stateCount[key[i]]]})
      // this.pieChartData.push(this.stateCount[key[i]]);
      // this.pieChartData[i] = {data:this.stateCount[key[i]],label:key[i]};
    }

    this.loaded = true;
    

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
