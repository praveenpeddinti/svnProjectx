import { Injectable } from '@angular/core';
import 'rxjs/add/operator/map';

/*
  Generated class for the Constants provider.

  See https://angular.io/docs/ts/latest/guide/dependency-injection.html
  for more info on providers and Angular 2 DI.
*/
@Injectable()
export class Constants {
   public baseUrl = 'https://10.10.73.62/';
    
   public loginUrl = this.baseUrl+"site/user-authentication";
   public getAllTicketDetails = this.baseUrl + "story/get-all-ticket-details";
   public taskDetailsById = this.baseUrl + "story/get-ticket-details";
   public LogutUrl = this.baseUrl+"site/update-collabarator-status";
   public fieldDetailsById = this.baseUrl+"story/get-field-details-by-field-id";

  constructor() {
    console.log('Hello Constants Provider');
  }

}
