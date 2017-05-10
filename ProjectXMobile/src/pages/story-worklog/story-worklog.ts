import { Component } from '@angular/core';
import { NavController, NavParams } from 'ionic-angular';
import { Globalservice } from '../../providers/globalservice';
import { Constants } from '../../providers/constants';

@Component({
  selector: 'page-story-worklog',
  templateUrl: 'story-worklog.html'
})
export class StoryWorklogPage {
  //Work log parameters
  public ticketId: any;
  public workLog = { thours: "", iworkHours: "" };
  public workedLogtime: any = {};
  public individualitems: Array<any>;
  public inputHourslog = "";
  constructor(public navCtrl: NavController,
    public navParams: NavParams,
    public globalService: Globalservice,
    private constants: Constants) {

    this.ticketId = this.navParams.get("id");
    // Total worked hours service method
    globalService.getWorklog(this.constants.getWorkLog, this.navParams.get("id")).subscribe(
      (result) => {
        this.workedLogtime = result.data;
      }, (error) => {

      }
    );
  }

  ionViewDidLoad() {
    console.log('ionViewDidLoad StoryWorklogPage');
  }

}
