import { Component } from '@angular/core';
import { IonicPage, NavController, LoadingController, NavParams } from 'ionic-angular';
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
  public taskDetails = { ticketId: "", title: "", description: "", type: "", workflowType: "" };
  public titleAfterEdit: string = "";
  public items: Array<any>;
  public arrayList: Array<{ id: string, title: string, assignTo: string, readOnly: string, fieldType: string, fieldName: string, ticketId: any, readableValue: any }>;
  public displayFieldvalue = [];
  public textFieldValue = "";
  public textAreaValue = "";
  public localDate: any = new Date();
  public displayedClassColorValue = "";
  //Work log parameters
  public ticketId: any;
  public workLog = { thours: "", iworkHours: "" };
  public workedLogtime: any = {};
  public individualitems: Array<any>;
  public inputHourslog = "";
  public storyTicketId: any;
  constructor(public navCtrl: NavController, public navParams: NavParams,
    public globalService: Globalservice,
    public loadingController: LoadingController,
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
        this.taskDetails.description = result.data.Description;
        this.taskDetails.type = result.data.StoryType.Name;
        this.taskDetails.workflowType = result.data.WorkflowType;
        this.titleAfterEdit = result.data.Title;
        this.items = result.data.Fields;
        this.arrayList = [];
        for (let i = 0; i < this.items.length; i++) {
          var _id = this.items[i].Id;
          var _title = this.items[i].title;
          var _assignTo;
          if (this.items[i].field_type == "Text") {
            if (this.items[i].value == "") {
              _assignTo = "--";
            } else {
              _assignTo = this.items[i].value;
              if (this.items[i].field_name == "estimatedpoints") {
                this.textFieldValue = this.items[i].value;
              }
            }
          } else if (this.items[i].field_type == "TextArea") {
            if (this.items[i].value == "") {
              _assignTo = "--";
            } else {
              _assignTo = this.items[i].value;
              this.textAreaValue = this.items[i].value;
            }
          } else if (this.items[i].field_type == "Date") {
            //                        readable_value
            if (this.items[i].readable_value == "") {
              _assignTo = "--";
              this.localDate = new Date().toISOString();
            } else {
              _assignTo = this.items[i].readable_value;
              var date = new Date(this.items[i].readable_value);
              date.setTime(date.getTime() + date.getTimezoneOffset() * -60 * 1000);
              this.localDate = new Date(date.setDate(date.getDate())).toISOString();
            }
          }
          else if (this.items[i].field_type == "DateTime") {
            //                        readable_value
            if (this.items[i].readable_value == "") {
              _assignTo = "--";
            } else {
              _assignTo = this.items[i].readable_value;
            }
          }
          else {
            if (this.items[i].value_name == "") {
              _assignTo = "--";
            } else {
              _assignTo = this.items[i].value_name;
            }
          }
          var _readOnly = this.items[i].readonly;
          var _fieldType = this.items[i].field_type;
          var _fieldName = this.items[i].field_name;
          if (_fieldName == 'priority') {
            this.displayedClassColorValue = _assignTo;
          }
          var _readableValue = this.items[i].readable_value;
          this.arrayList.push({
            id: _id, title: _title, assignTo: _assignTo, readOnly: _readOnly, fieldType: _fieldType, fieldName: _fieldName, ticketId: this.taskDetails.ticketId, readableValue: _readableValue
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
    // console.log("the details " + JSON.stringify(this.ticketId));
    this.globalService.insertTimelog(this.constants.insertTimeLog, this.storyTicketId, this.inputHourslog).subscribe(
      (result) => {
        setTimeout(() => {
          // document.getElementById("logHourDetails_input_" + index).style.display = 'block';
          // document.getElementById("logHourDetails_input_" + index).innerHTML = this.workedLogtime.workHours;
        //  this.inputHourslog = null;
            if(result.data.TotalTimeLog > 0){
                 this.workedLogtime = result.data;
                 jQuery("#logHourDetails_input").val("");
            }else {
                 this.workedLogtime= "0.00";
                 jQuery("#logHourDetails_input").val("");
            }
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
  public expandDescription() {
    jQuery('#description').css('height', 'auto');
    jQuery('#show').hide();
    jQuery('#hide').show();
  }
  public collapseDescription() {
    jQuery('#hide').hide();
    jQuery('#show').show();
    jQuery('#description').css("height", "200px");
    jQuery('#description').css("overflow", "hidden");
  }
}
