import { Component } from '@angular/core';
import { IonicPage, NavController, LoadingController, NavParams } from 'ionic-angular';
import { Globalservice } from '../../providers/globalservice';
import { Constants } from '../../providers/constants';
declare var jQuery: any;
/**
 * Generated class for the StoryDetailsTask page.
 *
 * See http://ionicframework.com/docs/components/#navigation for more info
 * on Ionic pages and navigation.
 */
@IonicPage()
@Component({
  selector: 'page-story-details-task',
  templateUrl: 'story-details-task.html',
})
export class StoryDetailsTask {
  //ticket details
  public taskDetails = { ticketId: "", title: "", description: "", type: "", workflowType: "" };
  public titleAfterEdit: string = "";
  public items: Array<any>;
  public arrayList: Array<{ ticketId: any }>;
  public displayFieldvalue = [];
  public textFieldValue = "";
  public textAreaValue = "";
  public localDate: any = new Date();
  public displayedClassColorValue = "";
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
  }

  ionViewDidLoad() {
    console.log('ionViewDidLoad StoryDetailsTask');
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
