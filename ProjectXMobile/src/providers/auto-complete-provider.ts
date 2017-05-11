import { Injectable } from '@angular/core';
import { Http, Headers } from '@angular/http';
import 'rxjs/add/operator/map';
import { AutoCompleteService } from 'ionic2-auto-complete';
import { Constants } from '../providers/constants';

/*
  Generated class for the DataProvider provider.

  See https://angular.io/docs/ts/latest/guide/dependency-injection.html
  for more info on providers and Angular 2 DI.
*/
@Injectable()
export class AutoCompleteProvider implements AutoCompleteService{
  labelAttribute = "Name";
  private headers = new Headers({'Content-Type': 'application/x-www-form-urlencoded'});
  public ticketId: any = "";
  constructor(private http:Http,private constants: Constants) {}
  getResults(keyword: string) {
    var getFollowersParams: any = {
                TicketId: this.ticketId,
                ProjectId: 1,
                SearchValue: keyword };
    return this.http.post(this.constants.getUsersForFollow, JSON.stringify(getFollowersParams), this.headers).map(
            (res) => {
              return res.json().data.filter(item => item.Name.toLowerCase().startsWith(keyword.toLowerCase()) );
            }
        );
  }

  public getDataForSearch(ticketIdParam){
    this.ticketId = ticketIdParam;
  }
}