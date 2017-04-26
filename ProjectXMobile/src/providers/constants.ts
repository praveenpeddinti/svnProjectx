import { Injectable } from '@angular/core';
import 'rxjs/add/operator/map';

/*
  Generated class for the Constants provider.

  See https://angular.io/docs/ts/latest/guide/dependency-injection.html
  for more info on providers and Angular 2 DI.
*/
@Injectable()
export class Constants {
  // Local URL
  // public baseUrl = 'http://10.10.73.33/';
  // Sandbox URL
  // public baseUrl = 'http://10.10.73.77/';
  // Public URL
   public baseUrl = 'http://113.193.181.131/';
  // Mobile team URL
  // public baseUrl = 'http://10.10.73.12:802/';
    
   public loginUrl = this.baseUrl+"site/user-authentication";
   public getAllTicketDetails = this.baseUrl + "story/get-my-tickets-details";
   public taskDetailsById = this.baseUrl + "story/get-ticket-details";
   public LogutUrl = this.baseUrl+"site/update-collabarator-status";
   public fieldDetailsById = this.baseUrl+"story/get-field-details-by-field-id";
   public leftFieldUpdateInline = this.baseUrl+"story/update-story-field-inline";
   public templateForStoryCreation = this.baseUrl+"story/new-story-template";
   public createStory = this.baseUrl+"story/save-ticket-details";
   public getTicketActivity = this.baseUrl + "story/get-ticket-activity";
   public deleteCommentById = this.baseUrl + "story/delete-comment";
   public submitComment = this.baseUrl + "story/submit-comment";
   public filesUploading = this.baseUrl+'story/upload-comment-artifacts';
   public fileUploadsFolder = 'uploads/';
   constructor() {
      console.log('Constants Provider');
   }
}