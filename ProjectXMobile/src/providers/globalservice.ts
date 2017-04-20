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

    //private headers = new Headers({'Content-Type': 'application/json'});
    private headers = new Headers({'Content-Type': 'application/x-www-form-urlencoded'});
    params: {userInfo?: any, projectId?: any} = {};

    constructor(public http: Http, public storage: Storage) {

        this.storage.get("userCredentials").then((value) => {
            this.params.userInfo = value;
        });

        this.params.projectId = 1;
        // this.params.ticketId = "";
        
    }

    getLoginValidation(url, data) {
        //var response = this.http.get(url,).map(res => res.json());
        var response = this.http.post(url, JSON.stringify(data), {headers: this.headers}).map(
            res => res.json()
        );
        return response;
    }

    getLogout(url, data) {
        var response = this.http.post(url, JSON.stringify(data), this.headers).map(
            res => res.json()
        );
        return response;
    }

    public getTicketDetailsById(url, data) {
        var tickerDetailsParams = this.params;
        //this.params.ticketId = data;
        tickerDetailsParams["ticketId"] = data;
        var response = this.http.post(url, JSON.stringify(tickerDetailsParams), this.headers).map(
            res => res.json()
        );
        return response;
    }

    /**
        used for getting all the stories list from server
        author uday   
    
     */
    getStoriesList(url, params) {
        var response = this.http.post(
            url,
            JSON.stringify(params),
            {headers: this.headers, }).map(res => res.json());

        return response;
    }

    public getFieldItemById(url, fieldDetails) {

        var fieldItemParams = this.params;
        // var params:{FieldId:any}= {};
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
        // console.log("the fieldUpdateParams --- " + JSON.stringify(selectedItem) + " --- " + JSON.stringify(fieldDetails));
        var fieldUpdateParams = this.params;

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
        // response.refCount();
        return response;
    }
    
    public newStoryTemplate(url,data){
         var ticketParams = this.params;
        //this.params.ticketId = data;
        ticketParams["ticketId"] = data;
        var response = this.http.post(url, JSON.stringify(ticketParams), this.headers).map(
            res => res.json()
        );
        return response;
    }
    public createStoryORTask(url, data) {
        var createStoryParams = this.params;
        console.log("the create patams " + JSON.stringify(data));

        createStoryParams["data"] = data;
        createStoryParams["timeZone"] = "Asia/Kolkata";

        console.log("the createStoryParams " + JSON.stringify(createStoryParams));
        var response = this.http.post(url, JSON.stringify(createStoryParams), this.headers).map(
            res => res.json()
        );
        return response;

    }
    
    // Ticket #91
    public getTicketActivity(url, activityParams) {
        // console.log("getTicketActivity : "+url+"-"+JSON.stringify(activityParams));
        var ticketActivityParams = this.params;
        ticketActivityParams["ticketId"] = activityParams;
        ticketActivityParams["timeZone"] = "Asia/Kolkata";
        var response = this.http.post(url, JSON.stringify(ticketActivityParams), this.headers).map(
            res => res.json()
        );
        return response;
    }
    public deleteCommentById(url, commentParams){
        // console.log("deleteCommentById : "+url+"-"+JSON.stringify(commentParams));
        var deleteCommentParams = this.params;
        deleteCommentParams["Comment"] = commentParams.Comment;
        deleteCommentParams["TicketId"] = commentParams.TicketId;
        var response = this.http.post(url, JSON.stringify(deleteCommentParams), this.headers).map(
            res => res.json()
        );
        return response;
    }
    public submitComment(url, commentParams){
        // console.log("submitComment : "+url+"-"+JSON.stringify(commentParams));
        var submitCommentParams = this.params;
        submitCommentParams["Comment"] = commentParams.Comment;
        submitCommentParams["TicketId"] = commentParams.TicketId;
        var response = this.http.post(url, JSON.stringify(submitCommentParams), this.headers).map(
            res => res.json()
        );
        return response;
    }
    // Ticket #91 ended
}
