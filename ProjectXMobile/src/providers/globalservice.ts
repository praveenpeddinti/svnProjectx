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
    private headers = new Headers({'Content-Type': 'application/x-www-form-urlencoded'});
    params: {userInfo?: any, projectId?: any} = {};
    constructor(public http: Http, public storage: Storage) {
        console.log('Globalservice');
    }
    public getUserInfo(): any{
        this.storage.get("userCredentials").then((value) => {
            this.params.userInfo = value;
        });
        this.params.projectId = 1;
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
    public getFieldItemById(url, fieldDetails) {
        var fieldItemParams = this.getUserInfo();
        fieldItemParams["FieldId"] = fieldDetails.id;
        fieldItemParams["TicketId"] = fieldDetails.ticketId;
        fieldItemParams["ProjectId"] = 1;
        fieldItemParams["timeZone"] = "Asia/Kolkata";
        fieldItemParams["WorkflowType"] = fieldDetails.workflowType;
        fieldItemParams["StatusId"] = fieldDetails.readableValue.StateId;
        delete fieldItemParams["ticketId"];
        delete fieldItemParams["projectId"];
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
        fieldUpdateParams["TicketId"] = fieldDetails.ticketId;
        fieldUpdateParams["EditedId"] = fieldDetails.fieldName;
        if(fieldDetails.fieldName == 'workflow'){
            fieldUpdateParams["WorkflowType"] = fieldDetails.workflowType;
            fieldUpdateParams["StatusId"] = fieldDetails.readableValue.StateId;
        }
        fieldUpdateParams["projectId"] = 1;
        fieldUpdateParams["timeZone"] = "Asia/Kolkata";
        delete fieldUpdateParams["FieldId"];
        delete fieldUpdateParams["ticketId"];
        delete fieldUpdateParams["ProjectId"];
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
        createStoryParams["timeZone"] = "Asia/Kolkata";
        var response = this.http.post(url, JSON.stringify(createStoryParams), this.headers).map(
            res => res.json()
        );
        return response;
    }
    public getTicketActivity(url, activityParams) {
        var ticketActivityParams = this.getUserInfo();
        ticketActivityParams["ticketId"] = activityParams;
        ticketActivityParams["timeZone"] = "Asia/Kolkata";
        var response = this.http.post(url, JSON.stringify(ticketActivityParams), this.headers).map(
            res => res.json()
        );
        return response;
    }
    public deleteCommentById(url, commentParams){
        var deleteCommentParams = this.getUserInfo();
        deleteCommentParams["Comment"] = commentParams.Comment;
        deleteCommentParams["TicketId"] = commentParams.TicketId;
        var response = this.http.post(url, JSON.stringify(deleteCommentParams), this.headers).map(
            res => res.json()
        );
        return response;
    }
    public submitComment(url, commentParams){
        var submitCommentParams = this.getUserInfo();
        submitCommentParams["Comment"] = commentParams.Comment;
        submitCommentParams["TicketId"] = commentParams.TicketId;
        var response = this.http.post(url, JSON.stringify(submitCommentParams), this.headers).map(
            res => res.json()
        );
        return response;
    }
}