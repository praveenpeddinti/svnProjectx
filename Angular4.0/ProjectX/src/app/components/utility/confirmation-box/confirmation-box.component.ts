import { Component, OnInit , AfterViewInit, Input , Output, EventEmitter } from '@angular/core';

@Component({
  selector: 'app-confirmation-box',
  templateUrl: './confirmation-box.component.html',
  styleUrls: ['./confirmation-box.component.css']
})
export class ConfirmationBoxComponent implements OnInit {
  @Input() boxId;
  @Input() okAction;
  @Input() cancelAction;
  public selectedValue;

  @Output() callBackToParent: EventEmitter<any> = new EventEmitter();
  constructor() { }

  ngOnInit() {
   
  }
 
  getDataFromParent(domPosition){
       this.selectedValue = domPosition;
 
  }
  closeConfirmationBox(domPosition){
       this.selectedValue = domPosition;
 
  }
  emitDeleteNotification(){
    this.callBackToParent.emit();
  }
get self() { return this; }
}
