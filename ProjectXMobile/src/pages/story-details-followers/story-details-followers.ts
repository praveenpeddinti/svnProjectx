import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams, LoadingController, AlertController } from 'ionic-angular';
// Ticket #113
import { AutoCompleteProvider } from '../../providers/auto-complete-provider';
import { AutoCompleteComponent } from 'ionic2-auto-complete';
import { ViewChild } from '@angular/core';
import { Storage } from '@ionic/storage';
import { Globalservice } from '../../providers/globalservice';
import { Constants } from '../../providers/constants';
declare var jQuery: any;
// Ticket #113 ended
/**
 * Generated class for the StoryDetailsFollowers page.
 *
 * See http://ionicframework.com/docs/components/#navigation for more info
 * on Ionic pages and navigation.
 */
@IonicPage()
@Component({
  selector: 'page-story-details-followers',
  templateUrl: 'story-details-followers.html',
})
export class StoryDetailsFollowers {
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
 // Ticket #113
    public followers: Array<any>; 
    public userId: number;
    private follower_search_results: string[];
    private fList: any = [];
    public userName: any = '';
    public storyTicketId: any;
    // Ticket #113 ended
     // Ticket #113
    @ViewChild('searchbar') searchbar: AutoCompleteComponent;
    // Ticket #113 ended
    // Ticket #113 added autoCompleteProvider in constructor
  constructor(public navCtrl: NavController, public navParams: NavParams,
      public autoCompleteProvider: AutoCompleteProvider,
      public globalService: Globalservice,
        private constants: Constants,
        public loadingController: LoadingController,
        private alertController: AlertController,
       private storage: Storage) {
       let loader = this.loadingController.create({ content: "Loading..." });
       loader.present();
       
       this.storyTicketId = this.navParams.data.ticketId;
          this.storage.get('userCredentials').then((value) => {
              this.userName = value.username;
              // Ticket #113
              this.userId = value.Id;
              // Ticket #113 ended
               });

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
                    } else if(this.items[i].field_type == "Date"){
//                        readable_value
                       if(this.items[i].readable_value == ""){
                             _assignTo = "--"; 
                            this.localDate = new Date().toISOString();
                          } else {
                            _assignTo = this.items[i].readable_value;
                            var date = new Date(this.items[i].readable_value);
                            date.setTime( date.getTime() + date.getTimezoneOffset()*-60*1000 );
                            this.localDate = new Date(date.setDate(date.getDate() )).toISOString();
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

              globalService.getTicketDetailsById(this.constants.taskDetailsById, this.storyTicketId).subscribe(
                  result => {
                      // Ticket #113
                      this.followers = result.data.Followers;
                      // Ticket #113 ended
                  }, error => {
                      //loader.dismiss();
                  }
              );
               // Ticket #113
           this.follower_search_results=[];
        // Ticket #113 ended
       
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
    public addFollower(followerId) {
        var followerData = {
            ticketId: this.storyTicketId,
            collaboratorId: followerId,
        };
        this.globalService.makeUsersFollowTicket(this.constants.makeUsersFollowTicket, followerData).subscribe(
            (result) => {
                if (result.statusCode == 200) {
                    this.followers.push(result.data);
                }
            },
            (error) => {
                console.log("error in makeUsersFollowTicket");
            }
        );
    }
    public presentConfirmRemoveFollower(followerId) {
        let alert = this.alertController.create({
            title: 'Confirm Remove Follower',
            message: 'Do you want to delete this follower?',
            buttons: [
            {
                text: 'CANCEL',
                role: 'cancel',
                handler: () => {}
            },
            {
                text: 'OK',
                handler: () => {
                    this.removeFollower(followerId);
                }
            }
            ]
        });
        alert.present();
    }
    public removeFollower(followerId) {
        var followerData = {
            ticketId: this.storyTicketId,
            collaboratorId: followerId
        };
        this.globalService.makeUsersUnfollowTicket(this.constants.makeUsersUnfollowTicket, followerData).subscribe(
            (result) => {
                if (result.statusCode == 200) {
                    jQuery("#followerdiv_" + followerId).remove();
                    this.followers = this.followers.filter(function (el) {
                        return el.FollowerId !== followerId;
                    });
                }
            },
            (error) => {
                console.log("error in makeUsersUnfollowTicket");
            }
        );
    }
    itemCustomSelected($event){
        this.addFollower($event.Id);
        this.searchbar.clearValue();
    }
  ionViewDidLoad() {
    console.log('ionViewDidLoad StoryDetailsFollowers');
  }

}
