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
  //  public baseUrl = 'http://localhost/';
  // Sandbox URL
  public baseUrl = 'http://10.10.73.77/';
  // Public URL
  // public baseUrl = 'http://113.193.181.131/';
  
  // Mobile team URL
   //public baseUrl = 'http://10.10.73.62/';

  //    public baseUrl = 'http://10.10.73.12:802/';
    //anand
  //  public baseUrl = 'http://10.10.73.9:8099/';
    public notificationUrl='http://10.10.73.9:4201/getAllNotifications'
    public deleteNotificationUrl=this.baseUrl+'story/delete-notification'
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
  //  Ticket #113
   public getUsersForFollow = this.baseUrl+'story/get-collaborators-for-follow';
   public makeUsersFollowTicket = this.baseUrl+'story/follow-ticket';
   public makeUsersUnfollowTicket = this.baseUrl+'story/unfollow-ticket';
  //  Ticket #113 ended
   public getWorkLog = this. baseUrl+"story/get-work-log";
   public insertTimeLog = this.baseUrl+"story/insert-time-log";
   public allDetailsforSearch = this.baseUrl+"story/get-all-ticket-details-for-search";
   public relateTask = this.baseUrl+"story/update-related-tasks";
   public getCollaboratorsUrl=this.baseUrl+"story/get-collaborators";
   // Ticket #153 filter
   public filterOptions = this.baseUrl+"story/get-filter-options";
   public getallStoryDetails = this.baseUrl+"story/get-all-story-details";
   public globalSearch = this.baseUrl+"site/global-search";
   constructor() {
      console.log('Constants');
   }
}