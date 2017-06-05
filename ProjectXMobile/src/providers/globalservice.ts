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
    public getLoginValidation(url, data) {
        this.getUserInfo();
        var response = this.http.post(url, JSON.stringify(data), {headers: this.headers}).map(
            res => res.json()
        );
        return response;
    }
    public getLogout(url, data) {
        var response = this.http.post(url, JSON.stringify(data), this.headers).map(
            res => res.json()
        );
        return response;
    }
    public getTicketDetailsById(url, data) {
        var ticketDetailsParams = this.getUserInfo();
        ticketDetailsParams["ticketId"] = data;
        var response = this.http.post(url, JSON.stringify(ticketDetailsParams), this.headers).map(
            res => res.json()
        );
        return response;
    }
    public getStoriesList(url, params) {
        var response = this.http.post(url,JSON.stringify(params),{headers: this.headers, }).map(
            res => res.json()
        );
        return response;
    }
     public getfilterOptions(url, params){
        var response = this.http.post(url, JSON.stringify(params), {headers: this.headers}).map(
            res => res.json()
        );
        return response;
    }
    public getFieldItemById(url, fieldDetails) {
        var fieldItemParams = this.getUserInfo();
        fieldItemParams["fieldId"] = fieldDetails.id;
        fieldItemParams["ticketId"] = fieldDetails.ticketId;
        fieldItemParams["projectId"] = 1;
//        fieldItemParams["timeZone"] = "Asia/Kolkata";
        fieldItemParams["workflowType"] = fieldDetails.workflowType;
        fieldItemParams["statusId"] = fieldDetails.readableValue.StateId;
       
        var response = this.http.post(url, JSON.stringify(fieldItemParams), this.headers).map(
            res => res.json()
        );
        return response;
    }
    public leftFieldUpdateInline(url, selectedItem, fieldDetails){
        var fieldUpdateParams = this.getUserInfo();
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
//        fieldUpdateParams["timeZone"] = "Asia/Kolkata";
        delete fieldUpdateParams["fieldId"];
      
       // delete fieldUpdateParams["projectId"];
        var response = this.http.post(url, JSON.stringify(fieldUpdateParams), this.headers).map(
            res => res.json()
        );
        return response;
    }
    public newStoryTemplate(url,data){
        var ticketParams = this.getUserInfo();
        ticketParams["ticketId"] = data;
        var response = this.http.post(url, JSON.stringify(ticketParams), this.headers).map(
            res => res.json()
        );
        return response;
    }
    public createStoryORTask(url, data) {
        var createStoryParams = this.getUserInfo();
        createStoryParams["data"] = data;
//        createStoryParams["timeZone"] = "Asia/Kolkata";
        var response = this.http.post(url, JSON.stringify(createStoryParams), this.headers).map(
            res => res.json()
        );
        return response;
    }
    public getTicketActivity(url, activityParams) {
        var ticketActivityParams = this.getUserInfo();
        ticketActivityParams["ticketId"] = activityParams;
//        ticketActivityParams["timeZone"] = "Asia/Kolkata";
        var response = this.http.post(url, JSON.stringify(ticketActivityParams), this.headers).map(
            res => res.json()
        );
        return response;
    }
    public deleteCommentById(url, commentParams){
        var deleteCommentParams = this.getUserInfo();
        deleteCommentParams["Comment"] = commentParams.Comment;
        deleteCommentParams["ticketId"] = commentParams.TicketId;
        var response = this.http.post(url, JSON.stringify(deleteCommentParams), this.headers).map(
            res => res.json()
        );
        return response;
    }
    public submitComment(url, commentParams){
        var submitCommentParams = this.getUserInfo();
        submitCommentParams["Comment"] = commentParams.Comment;
        submitCommentParams["ticketId"] = commentParams.TicketId;
        var response = this.http.post(url, JSON.stringify(submitCommentParams), this.headers).map(
            res => res.json()
        );
        return response;
    }
    // Ticket #113
    //delete it later
    public getUsersForFollow(url, usersData){
        var getUsersParams = this.getUserInfo();
        getUsersParams["ticketId"] = usersData.ticketId;
        getUsersParams["projectId"] = usersData.ProjectId;
        getUsersParams["searchValue"] = usersData.SearchValue;
        var response = this.http.post(url, JSON.stringify(getUsersParams), this.headers).map(
            res => res.json()
        );
        return response;
    }
    public makeUsersFollowTicket(url, addFollowerData){
        var addFollowerParams = this.getUserInfo();
        addFollowerParams["ticketId"] = addFollowerData.ticketId;
        addFollowerParams["projectId"] = 1;
        addFollowerParams["collaboratorId"] = addFollowerData.collaboratorId;
        var response = this.http.post(url, JSON.stringify(addFollowerParams), this.headers).map(
            res => res.json()
        );
        return response;
    }
    public makeUsersUnfollowTicket(url, removeFollowerData){
        var removeFollowerParams = this.getUserInfo();
        removeFollowerParams["ticketId"] = removeFollowerData.ticketId;
        removeFollowerParams["collaboratorId"] = removeFollowerData.collaboratorId;
        var response = this.http.post(url, JSON.stringify(removeFollowerParams), this.headers).map(
            res => res.json()
        );
        return response;
    }
    //  Ticket #113 ended
//sprint 5 start :- prabhu
    public getWorklog(url, worklogParams){
        var getWorklogParams = this.getUserInfo();
        getWorklogParams["ticketId"] = worklogParams;
        getWorklogParams["getimelog"] = worklogParams.getimelog;
        getWorklogParams["projectId"] = 1;
       // getWorklogParams["timeZone"] = "Asia/Kolkata";
        var response = this.http.post(url, JSON.stringify(getWorklogParams), this.headers).map(
            res => res.json()
        );
        return response;
    }
    public insertTimelog(url, timelogTicketId, enteredTimeLog){
        var insertTimelogParams = this.getUserInfo();
        insertTimelogParams["ticketId"] = timelogTicketId;
        insertTimelogParams["workHours"] = enteredTimeLog;
        insertTimelogParams["addTimelogDesc"] = "";
        insertTimelogParams["addTimelogTime"] = this.localDate;
        insertTimelogParams["projectId"] = 1;
//        insertTimelogParams["timeZone"] = "Asia/Kolkata";
        var response = this.http.post(url, JSON.stringify(insertTimelogParams), this.headers).map(
            res => res.json()
        );
        return response;
    }

    public searchTicket(url, ticketParams){
        var searchTicketParams = this.getUserInfo();
        searchTicketParams["ticketId"] = ticketParams.TicketId;
        searchTicketParams["projectId"] = 1;
        searchTicketParams["sortvalue"] = ticketParams.sortvalue;
        searchTicketParams["searchString"] = ticketParams.searchString;
//         searchTicketParams["timeZone"] = "Asia/Kolkata";
         var response = this.http.post(url,JSON.stringify(searchTicketParams),this.headers).map(
             res => res.json()
         );
         return response;
    }

    public relateTask(url, relatedTask){
        var relateTaskParams = this.getUserInfo();
        relateTaskParams["ticketId"] = relatedTask.TicketId;
        relateTaskParams["projectId"] = 1;
        relateTaskParams["relatedSearchTicketId"] = relatedTask.relatedSearchTicketId;
         var response = this.http.post(url,JSON.stringify(relateTaskParams),this.headers).map(
             res => res.json()
         );
         return response;
    }

   public getCollaborators(url, requestParams) {
        requestParams['userInfo'] = this.getUserInfo();
        var response = this.http.post(url, JSON.stringify(requestParams), this.headers).map(
            res => res.json()
        );
        return response;
    }
    //sprint 5 end :- prabhu
    
}