import {Component,Input,Output,Injectable,EventEmitter} from '@angular/core';

@Injectable()
export class SharedService {
  @Output() route_change: EventEmitter<any> = new EventEmitter();
   @Output() notification_change: EventEmitter<any> = new EventEmitter();
            public page;
   constructor() {
     console.log('shared service started');
   }

   change(url,params,page,ticket_type,project) { 
     console.log("==Url in shared service=="+url);
     console.log("==params in shared service=="+params);
     var route_data={url:url,params:params,page:page,type:ticket_type,projectName:project,navigatedFrom:this.page};
     this.route_change.emit(route_data);
     this.page='';
   }

   navigatedFrom(page)
   {
     this.page=page;
   }

   getEmittedValue() {
     return this.route_change;
   }
    changeNotificationCount(count) { 
     this.notification_change.emit(count);
    
   }
   getNotificationCount(){
     return this.notification_change;
   }

} 