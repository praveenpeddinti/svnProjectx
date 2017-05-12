import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams, AlertController } from 'ionic-angular';
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
        private alertController: AlertController,
       private storage: Storage) {
       
       this.storyTicketId = this.navParams.data.ticketId;
          this.storage.get('userCredentials').then((value) => {
              this.userName = value.username;
              // Ticket #113
              this.userId = value.Id;
              // Ticket #113 ended
               });
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
