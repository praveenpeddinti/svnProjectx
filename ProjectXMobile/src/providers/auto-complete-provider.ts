import { Injectable } from '@angular/core';
import { Http, Headers } from '@angular/http';
import 'rxjs/add/operator/map';
import { AutoCompleteService } from 'ionic2-auto-complete';

/*
  Generated class for the DataProvider provider.

  See https://angular.io/docs/ts/latest/guide/dependency-injection.html
  for more info on providers and Angular 2 DI.
*/
@Injectable()
export class AutoCompleteProvider implements AutoCompleteService{
  labelAttribute = "Name";
  private headers = new Headers({'Content-Type': 'application/x-www-form-urlencoded'});
  constructor(private http:Http) {}
  getResults(keyword: string) {
    var getFollowersParams: any = {
                TicketId: "34",
                ProjectId: 1,
                SearchValue: keyword };
                // console.log("the params " + JSON.stringify(getFollowersParams));
    return this.http.post("http://10.10.73.12:802/story/get-collaborators-for-follow", JSON.stringify(getFollowersParams), this.headers).map(
            (res) => {
                    // console.log("the search response " + JSON.stringify(res.json().data));
              return res.json().data.filter(item => item.Name.toLowerCase().startsWith(keyword.toLowerCase()) );
            }
        );
  }
}


