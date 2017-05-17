import {Component,Input,Output,Injectable,EventEmitter} from '@angular/core';

@Injectable()
export class SharedService {
  @Output() route_change: EventEmitter<any> = new EventEmitter();

   constructor() {
     console.log('shared service started');
   }

   change(url,params,page,ticket_type) { 
     console.log("==Url in shared service=="+url);
     console.log("==params in shared service=="+params);
     var route_data={url:url,params:params,page:page,type:ticket_type};
     this.route_change.emit(route_data);
   }

   getEmittedValue() {
     return this.route_change;
   }

} 