import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams } from 'ionic-angular';
import { Globalservice } from '../../providers/globalservice';
import { Constants } from '../../providers/constants';

/**
 * Generated class for the StoryDetailsWorklog page.
 *
 * See http://ionicframework.com/docs/components/#navigation for more info
 * on Ionic pages and navigation.
 */
@IonicPage()
@Component({
  selector: 'page-story-details-worklog',
  templateUrl: 'story-details-worklog.html',
})
export class StoryDetailsWorklog {
 //Work log parameters
  public ticketId: any;
  public workLog = { thours: "", iworkHours: "" };
  public workedLogtime: any = {};
  public individualitems: Array<any>;
  public inputHourslog = "";
  public storyTicketId: any;
  constructor(public navCtrl: NavController, public navParams: NavParams,
      public globalService: Globalservice,
      private constants: Constants) {
      this.storyTicketId = this.navParams.data.ticketId;
      console.log("the worklog value is" + JSON.stringify(this.storyTicketId));
      globalService.getWorklog(this.constants.getWorkLog, this.storyTicketId).subscribe(
          (result) => {
              this.workedLogtime = result.data;
          }, (error) => {

          }
      );
  }
  public inputWorkLog(event, index) {
    // console.log("the details " + JSON.stringify(this.ticketId));
    this.globalService.insertTimelog(this.constants.insertTimeLog, this.storyTicketId, this.inputHourslog).subscribe(
      (result) => {
        setTimeout(() => {
          // document.getElementById("logHourDetails_input_" + index).style.display = 'block';
          // document.getElementById("logHourDetails_input_" + index).innerHTML = this.workedLogtime.workHours;
          this.inputHourslog = null;
          this.workedLogtime = result.data;
          // this.workedLogtime.TotalTimeLog = result.data.TotalTimeLog;
          // this.individualitems = result.data.individualLog;

        }, 200);
      },
      (error) => {
        //  this.presentToast('Unsuccessful');
      });
  }
  ionViewDidLoad() {
    console.log('ionViewDidLoad StoryDetailsWorklog');
  }

}
