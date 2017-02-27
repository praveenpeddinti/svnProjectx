import { Injectable } from '@angular/core';
import {Http, Headers } from '@angular/http';
import 'rxjs/add/operator/map';

/*
  Generated class for the Globalservice provider.

  See https://angular.io/docs/ts/latest/guide/dependency-injection.html
  for more info on providers and Angular 2 DI.
*/
@Injectable()
export class Globalservice {

//private headers = new Headers({'Content-Type': 'application/json'});
private headers = new Headers({ 'Content-Type': 'application/x-www-form-urlencoded' });
 params: {userInfo?: any, projectId?: any, ticketId?: any} = {};

  constructor(public http: Http) {
    this.params.userInfo = {"Id":"9","username":"hareesh.bekkam","token":"8120acd3de3141db5ed9@9"};
    this.params.projectId = "1";
    this.params.ticketId= "";

    console.log('Hello Globalservice Provider ' + JSON.stringify(this.params));
  }
  
  getLoginValidation(url, data){
      //var response = this.http.get(url,).map(res => res.json());
      var response = this.http.post(url, JSON.stringify(data), {headers: this.headers}).map(
          res => res.json()
      );
      return response;
  }
  public getTicketDetailsById(url, data){
    this.params.ticketId= data;
    var response = this.http.post(url, JSON.stringify(this.params), this.headers).map(
      res => res.json()
    );
    return response;
  }

}
