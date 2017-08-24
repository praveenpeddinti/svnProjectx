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
  public pieChartData:number[] = [300, 500, 100];
  public pieChartType:string = 'pie';
  @Input('stateCount') stateCount:any={};
  @Input('index') index;
  constructor(private shared:SharedService) { }

  ngOnInit() { alert('state count=='+this.stateCount);
  //  this.shared.getStateStats().subscribe(value=>
  //  {
  //    alert("==From Shared=="+JSON.stringify(value));
  //  });
  var key=Object.keys(this.stateCount);
  this.pieChartLabels=key;
  this.pieChartData=[this.stateCount.New,this.stateCount.Paused,this.stateCount.Waiting,this.stateCount.Reopened,this.stateCount.Closed];
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
