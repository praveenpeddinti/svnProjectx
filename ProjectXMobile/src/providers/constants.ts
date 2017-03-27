import { Injectable } from '@angular/core';
import 'rxjs/add/operator/map';

/*
  Generated class for the Constants provider.

  See https://angular.io/docs/ts/latest/guide/dependency-injection.html
  for more info on providers and Angular 2 DI.
*/
@Injectable()
export class Constants {
  //Local URL
  // public baseUrl = 'http://10.10.73.62/';
  //public URL
   public baseUrl = 'http://113.193.181.131/';
 //  public baseUrl = 'http://10.10.73.77/';
    
   public loginUrl = this.baseUrl+"site/user-authentication";
   //public getAllTicketDetails = this.baseUrl + "story/get-all-ticket-details";
   public getAllTicketDetails = this.baseUrl + "story/get-my-tickets-details";
   public taskDetailsById = this.baseUrl + "story/get-ticket-details";
   public LogutUrl = this.baseUrl+"site/update-collabarator-status";
   public fieldDetailsById = this.baseUrl+"story/get-field-details-by-field-id";
   public leftFieldUpdateInline = this.baseUrl+"story/update-story-field-inline";

  constructor() {
    console.log('Hello Constants Provider');
  }

}
