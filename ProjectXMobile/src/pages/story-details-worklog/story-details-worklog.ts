import { Component } from '@angular/core';
import { IonicPage, NavController, LoadingController, AlertController, NavParams } from 'ionic-angular';
import { Globalservice } from '../../providers/globalservice';
import { Constants } from '../../providers/constants';
declare var jQuery: any;
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
  //ticket details
  public taskDetails = { ticketId: "", title: "", type: "" };
  public titleAfterEdit: string = "";
  public items: Array<any>;
  public arrayList: Array<{ ticketId: any }>;
  public displayFieldvalue = [];
  public textFieldValue = "";
  public textAreaValue = "";
  public localDate: any = new Date();
  public displayedClassColorValue = "";
  //Work log parameters
  public ticketId: any;
  public workLog = { thours: "", iworkHours: "" };
  public workedLogtime: any={};
  public individualitems: Array<any>;
  public inputHourslog = "";
  public storyTicketId: any;
  public isTimeValidErrorMessage;
  constructor(public navCtrl: NavController, public navParams: NavParams,
    public globalService: Globalservice,
    public loadingController: LoadingController,
    private alertController: AlertController,
    private constants: Constants) {
    this.storyTicketId = this.navParams.data.ticketId;
    console.log("the worklog value is" + JSON.stringify(this.storyTicketId));
    let loader = this.loadingController.create({ content: "Loading..." });
    loader.present();

    //ticket details 
    globalService.getTicketDetailsById(this.constants.taskDetailsById, this.storyTicketId).subscribe(
      result => {
        this.taskDetails.ticketId = this.storyTicketId;
        this.taskDetails.title = result.data.Title;
       // this.taskDetails.description = result.data.Description;
        this.taskDetails.type = result.data.StoryType.Name;
       // this.taskDetails.workflowType = result.data.WorkflowType;
        this.titleAfterEdit = result.data.Title;
        this.items = result.data.Fields;
        this.arrayList = [];
        for (let i = 0; i < this.items.length; i++) {
          this.arrayList.push({
            ticketId: this.taskDetails.ticketId
          });
        }
        loader.dismiss();
      }, error => {
        loader.dismiss();
      }
    );


    globalService.getWorklog(this.constants.getWorkLog, this.storyTicketId).subscribe(
      (result) => {
        this.workedLogtime = result.data;
      }, (error) => {

      }
    );
  }
  
   inputWorkLog(event, index) {
       
     var thisObj=this;
      if(thisObj.workedLogtime > "0" && thisObj.inputHourslog > "0"){
    this.globalService.insertTimelog(this.constants.insertTimeLog, this.storyTicketId, this.inputHourslog).subscribe(
      (result) => {
        setTimeout(() => {
          thisObj.inputHourslog = null;
          thisObj.workedLogtime= result.data.timeLogData;
        }, 200);
      },
      (error) => {
      });
      }else{
            let alert = this.alertController.create({
            message: 'Invalid Time',
            buttons: [
            {
                text: 'OK',
                role: 'cancel',
                handler: () => {
                    jQuery("#logHourDetails_input").val("");
                }
            },
            ]
        });
        alert.present();
      }
  }
  ionViewDidLoad() {
    console.log('ionViewDidLoad StoryDetailsWorklog');
  }
//  public expandDescription() {
//    jQuery('#description').css('height', 'auto');
//    jQuery('#show').hide();
//    jQuery('#hide').show();
//  }
//  public collapseDescription() {
//    jQuery('#hide').hide();
//    jQuery('#show').show();
//    jQuery('#description').css("height", "200px");
//    jQuery('#description').css("overflow", "hidden");
//  }
}
