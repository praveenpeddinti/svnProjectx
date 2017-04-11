import { Injectable } from '@angular/core';
import { Http, Headers, Response, Request, RequestMethod, URLSearchParams, RequestOptions } from '@angular/http';
import 'rxjs/add/operator/map';
import { ActivatedRoute } from '@angular/router';
import {Observable} from 'rxjs/Rx';
import {Constants} from './constants';
declare var $: any;

/*
  Generated class for the FileuploadProvider provider.

  See https://angular.io/docs/ts/latest/guide/dependency-injection.html
  for more info on providers and Angular 2 DI.
*/
@Injectable()
export class FileuploadProvider {
requestUrl: string;
  responseData: any;
  handleError: any;
  constructor(private http: Http, 
  private constants: Constants) {
    console.log('Hello FileuploadProvider Provider');
     this.http = http;
  }
 postWithFile (url: string, postData: any, files: File[]) {

    let headers = new Headers();
    let formData:FormData = new FormData();
    formData.append('files', files[0], files[0].name);
    // For multiple files
    // for (let i = 0; i < files.length; i++) {
    //     formData.append(`files[]`, files[i], files[i].name);
    // }

    if(postData !=="" && postData !== undefined && postData !==null){
      for (var property in postData) {
          if (postData.hasOwnProperty(property)) {
              formData.append(property, postData[property]);
          }
      }
    }
//    var returnReponse = new Promise((resolve, reject) => {
//      this.http.post(this.constants.root_dir + url, formData, {
//        headers: headers
//      }).subscribe(
//          res => {
//            this.responseData = res.json();
//            resolve(this.responseData);
//          },
//          error => {
//            this.router.navigate(['/login']);
//            reject(error);
//          }
//      );
//    });
//    return returnReponse;
  }
}
