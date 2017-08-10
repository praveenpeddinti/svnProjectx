import { Component, OnInit,Input } from '@angular/core';

@Component({
  selector: 'top-ticket-stats',
  templateUrl: './top-ticket-stats.component.html',
  styleUrls: ['./top-ticket-stats.component.css']
})
export class TopTicketStatsComponent implements OnInit {
@Input() statsData: any;
@Input() projectName: any;
  constructor() { }

  ngOnInit() {
  }

}
