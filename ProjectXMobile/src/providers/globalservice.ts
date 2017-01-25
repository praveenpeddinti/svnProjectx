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

private headers = new Headers({'Content-Type': 'application/json'});

  constructor(public http: Http) {
    console.log('Hello Globalservice Provider');
  }
  
  getLoginValidation(url, data){
      //var response = this.http.get(url,).map(res => res.json());
      var response = this.http.post(
                        url,
                        JSON.stringify(data),
                        {headers: this.headers}).map(res => res.json());
      
      return response;
  }

}
