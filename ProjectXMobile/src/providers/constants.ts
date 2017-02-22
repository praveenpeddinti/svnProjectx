import { Injectable } from '@angular/core';
import 'rxjs/add/operator/map';

/*
  Generated class for the Constants provider.

  See https://angular.io/docs/ts/latest/guide/dependency-injection.html
  for more info on providers and Angular 2 DI.
*/
@Injectable()
export class Constants {
   public baseUrl = 'https://10.10.73.62/';
    
   public loginUrl = this.baseUrl+"site/user-authentication";

  constructor() {
    console.log('Hello Constants Provider');
  }

}
