import {Injectable} from '@angular/core';
import {Http, Headers} from '@angular/http';
import 'rxjs/add/operator/map';
import {Storage} from '@ionic/storage';

/*
  Generated class for the Globalservice provider.

  See https://angular.io/docs/ts/latest/guide/dependency-injection.html
  for more info on providers and Angular 2 DI.
*/
@Injectable()
export class Globalservice {
    localDate = new Date().toISOString();
    private headers = new Headers({'Content-Type': 'application/x-www-form-urlencoded'});
    params: {userInfo?: any, projectId?: any,timeZone?:any} = {};
    constructor(public http: Http, public storage: Storage) {
        console.log('Globalservice');
    }
    public getUserInfo(): any{
        this.storage.get("userCredentials").then((value) => {
            this.params.userInfo = value;
        });
        this.params.projectId = 1;
        this.params.timeZone = "Asia/Kolkata";
        return this.params;
    }
    public ajaxCall(url,data){
        var userInfo=JSON.parse(localStorage.getItem("userCredentials"));
        data["userInfo"] = userInfo;
        data["timeZone"] = "Asia/Kolkata";
        data["projectId"] = 1;
          var response = this.http.post(url, JSON.stringify(data), {headers: this.headers}).map(
            res => res.json()
        );
         return response;
    }
    public getLoginValidation (url,data){
        return this.ajaxCall(url,data);
    }
    public getLogout(url, data) {
           return this.ajaxCall(url,data);
    }
    public getTicketDetailsById(url, data) {
        var ticketDetailsParams = {};
        ticketDetailsParams["ticketId"] = data;
        return this.ajaxCall(url,ticketDetailsParams);
    }
    public getStoriesList(url, params) {
        return this.ajaxCall(url,params);
    }
     public getfilterOptions(url, params){
         return this.ajaxCall(url,params);
    }
    public getFieldItemById(url, fieldDetails) {
        var fieldItemParams = {};
        fieldItemParams["fieldId"] = fieldDetails.id;
        fieldItemParams["ticketId"] = fieldDetails.ticketId;
        fieldItemParams["workflowType"] = fieldDetails.workflowType;
        fieldItemParams["statusId"] = fieldDetails.readableValue.StateId;
          return this.ajaxCall(url,fieldItemParams);
    }
    public leftFieldUpdateInline(url, selectedItem, fieldDetails){
        var fieldUpdateParams = {};
        fieldUpdateParams["isLeftColumn"] = 1;
        fieldUpdateParams["id"] = fieldDetails.id;
        fieldUpdateParams["value"] = selectedItem;
        fieldUpdateParams["ticketId"] = fieldDetails.ticketId;
        fieldUpdateParams["editedId"] = fieldDetails.fieldName;
        if(fieldDetails.fieldName == 'workflow'){
            fieldUpdateParams["workflowType"] = fieldDetails.workflowType;
            fieldUpdateParams["statusId"] = fieldDetails.readableValue.StateId;
        }
        fieldUpdateParams["projectId"] = 1;
        delete fieldUpdateParams["fieldId"];
        return this.ajaxCall(url,fieldUpdateParams);
    }
    public newStoryTemplate(url,data){
        var ticketParams = {};
        return this.ajaxCall(url,ticketParams);     
    }
    public createStoryORTask(url, data) {
        var createStoryParams = {};
        createStoryParams["data"] = data;
        return this.ajaxCall(url,createStoryParams);
    }
    public getTicketActivity(url, activityParams) {
         var ticketActivityParams = {};
        ticketActivityParams["ticketId"] = activityParams;
         return this.ajaxCall(url,ticketActivityParams);
    }
    public deleteCommentById(url, commentParams){
        var deleteCommentParams = {};
        deleteCommentParams["Comment"] = commentParams.Comment;
        deleteCommentParams["ticketId"] = commentParams.TicketId;
        return this.ajaxCall(url,deleteCommentParams);
    }
    public submitComment(url, commentParams){
         var submitCommentParams = {};
        submitCommentParams["Comment"] = commentParams.Comment;
        submitCommentParams["ticketId"] = commentParams.TicketId;
        return this.ajaxCall(url,submitCommentParams);
        
    }
    public makeUsersFollowTicket(url, addFollowerData){
        var addFollowerParams = {};
        addFollowerParams["ticketId"] = addFollowerData.ticketId;
        addFollowerParams["projectId"] = 1;
        addFollowerParams["collaboratorId"] = addFollowerData.collaboratorId;
         return this.ajaxCall(url,addFollowerParams);
    }
    public makeUsersUnfollowTicket(url, removeFollowerData){
         var removeFollowerParams = {};
        removeFollowerParams["ticketId"] = removeFollowerData.ticketId;
        removeFollowerParams["collaboratorId"] = removeFollowerData.collaboratorId;
        return this.ajaxCall(url,removeFollowerParams);
    }
    public getWorklog(url, worklogParams){
        var getWorklogParams = {};
        getWorklogParams["ticketId"] = worklogParams;
        getWorklogParams["getimelog"] = worklogParams.getimelog;
        return this.ajaxCall(url,getWorklogParams);
    }
    public insertTimelog(url, timelogTicketId, enteredTimeLog){
        var insertTimelogParams = {};
        insertTimelogParams["ticketId"] = timelogTicketId;
        insertTimelogParams["workHours"] = enteredTimeLog;
        insertTimelogParams["addTimelogDesc"] = "";
        insertTimelogParams["addTimelogTime"] = this.localDate;
        insertTimelogParams["projectId"] = 1;
        insertTimelogParams["timeZone"] = "Asia/Kolkata";
        return this.ajaxCall(url,insertTimelogParams);
    }
    public searchTicket(url, ticketParams){
        var searchTicketParams = {};
        searchTicketParams["ticketId"] = ticketParams.TicketId;
        searchTicketParams["projectId"] = 1;
        searchTicketParams["sortvalue"] = ticketParams.sortvalue;
        searchTicketParams["searchString"] = ticketParams.searchString;
        return this.ajaxCall(url,searchTicketParams);
    }

   public getCollaborators(url, requestParams) {
        return this.ajaxCall(url,requestParams);
    }
    
}