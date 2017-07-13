import {Injectable,Input,Output,EventEmitter} from '@angular/core';
import {Http, Headers} from '@angular/http';
import 'rxjs/add/operator/map';
import {Storage} from '@ionic/storage';
declare var socket:any;
/*
  Generated class for the Globalservice provider.

  See https://angular.io/docs/ts/latest/guide/dependency-injection.html
  for more info on providers and Angular 2 DI.
*/
@Injectable()
export class Globalservice {
     @Output() latestActivity: EventEmitter<any> = new EventEmitter();
    localDate = new Date().toISOString();
    private headers = new Headers({'Content-Type': 'application/x-www-form-urlencoded'});
    params: {userInfo?: any, projectId?: any,timeZone?:any} = {};
    constructor(public http: Http, public storage: Storage) {
        console.log('Globalservice');
    }
    public ajaxCall(url,data){
        var userInfo=JSON.parse(localStorage.getItem("userCredentials"));
        data["userInfo"] = userInfo;
        data["clientType"] = 'mobile';
        data["timeZone"] = "Asia/Kolkata";
        data["projectId"] = 1;
        var response = this.http.post(url, JSON.stringify(data), {headers: this.headers}).map(
            res => res.json()
        );
        return response;
    }
    public getLogout(url, data) {
           return this.ajaxCall(url,data);
    }
    public getLoginValidation (url,data){
        return this.ajaxCall(url,data);
    }
    public getTicketDetailsById(url, data) {
        var ticketDetailsParams = {};
        ticketDetailsParams["ticketId"] = data;
        return this.ajaxCall(url,ticketDetailsParams);
    }
    public getallStoriesList(url,params){
        return this.ajaxCall(url, params);
    }
    public getStoriesList(url, params) {
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
        return this.ajaxCall(url,insertTimelogParams);
    }
     public getfilterOptions(url, params){
         return this.ajaxCall(url,params);
    }
    public searchTicket(url, ticketParams){
        var searchTicketParams = {};
        searchTicketParams["ticketId"] = ticketParams.TicketId;
        searchTicketParams["sortvalue"] = ticketParams.sortvalue;
        searchTicketParams["searchString"] = ticketParams.searchString;
        return this.ajaxCall(url,searchTicketParams);
    }
   public getCollaborators(url, requestParams) {
        return this.ajaxCall(url,requestParams);
    }

    public getGlobalSearch(url,searchParams){
        return this.ajaxCall(url, searchParams);
    }

    public getAllNotification(url:string,params:Object){
    var userInfo=JSON.parse(localStorage.getItem("userCredentials"));
   if(userInfo != null){
      params["clientType"] = 'mobile';
      params["userInfo"] = userInfo;
      params["projectId"] = 1;
      params["timeZone"] = "Asia/Kolkata"
    }
        var response = this.http.post(url, params, this.headers).map(
            res => res.json()
        );
        return response;
}

public SocketSubscribe(url:string,params:Object)
{ 
   var userInfo=JSON.parse(localStorage.getItem("userCredentials"));;
   if(userInfo != null){
      params["userInfo"] = userInfo;
      params["projectId"] = 1;
      params["timeZone"] = "Asia/Kolkata"
    }
  
      socket.emit(url,params);
     
}


public deleteNotification(url:string,params:Object){
     var userInfo=JSON.parse(localStorage.getItem("userCredentials"));
   if(userInfo != null){
      params["clientType"] = 'mobile';
      params["userInfo"] = userInfo;
      params["projectId"] = 1;
      params["timeZone"] = "Asia/Kolkata";
    }
      return this.ajaxCall(url,params);
}




//get all projects List

public getallProjectsList(url,params){
return this.ajaxCall(url,params);
}


public setActivity(data:any){
    var activity={activityData:data}
    this.latestActivity.emit(activity);
}

public getActivity(){
    return this.latestActivity;
}

}