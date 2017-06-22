import { Component } from '@angular/core';
import { NavController, NavParams, ViewController, AlertController } from 'ionic-angular';
import { Storage } from '@ionic/storage';
import { LoginPage } from '../login/login';
import { Globalservice } from '../../providers/globalservice';
import { Constants } from '../../providers/constants';
import { App } from 'ionic-angular';
import { StoryDetailsPage } from '../story-details/story-details';
/*
  Generated class for the Popover page.

  See http://ionicframework.com/docs/v2/components/#navigation for more info on
  Ionic pages and navigation.
*/
@Component({
  selector: 'page-notification',
  templateUrl: 'notification.html',
  providers: [Globalservice, Constants]
})
export class NotificationPage {
  moreDataLoaded: boolean = true;
  unread:boolean=true;
  page: any = 1;
  viewAll:any=0;
  notification_msg: any = [];
  userName: string = '';
  login: { username?: string, password?: string, token?: any } = {};
  logoutParams = { "userInfo": { "Id": "", "username": "", "token": "" }, "projectId": 1 }
  constructor(protected app: App,
    private globalService: Globalservice,
    private constants: Constants,
    public navCtrl: NavController,
    public alertController: AlertController,
    private storage: Storage,
    public navParams: NavParams,

    public viewCtrl: ViewController) {
    localStorage.setItem('headerInfo',JSON.stringify({'title':"Notifications",backButton:"",logo:0,leftPannel:0}));
    this.storage.get('userCredentials').then((value) => {
      this.logoutParams.userInfo.Id = value.Id;
      this.logoutParams.userInfo.username = value.username;
      this.logoutParams.userInfo.token = value.token;
    });
  }
  public close() {
    this.viewCtrl.dismiss();
  }
  ionViewDidLoad() {
    // this.notification_msg=this.navParams.get("notification_data");
    this.getAllNotification(this.viewAll, this.page);
    this.userName = this.navParams.get("username");
  }

  ionViewWillLeave() {
console.log("Looks like I'm about to leave :(");
}
  /**
      * @author Anand Singh
      * @uses Get All notifications
      */
  public getAllNotification(viewAll, page) {
    var post_data = { viewAll: viewAll, page: page };
    this.globalService.getAllNotification(this.constants.notificationUrl, post_data).subscribe(
      (result) => {
        console.log("data____length__" + result.notify_result.length);
        if (result.notify_result.length == 0) this.moreDataLoaded = false;
        if (this.page == 1) {
          this.notification_msg = [];
        }
        for (var i = 0; i < result.notify_result.length; i++) {

          this.notification_msg.push(result.notify_result[i]);

        }

      }, (error) => {
        console.log("user loading error")
      }
    )
  }


  public doInfinite(infiniteScroll) {
    setTimeout(() => {
      if (this.moreDataLoaded == true) {
        this.page += 1;
        this.viewAll=0
        this.getAllNotification(this.viewAll, this.page);
        infiniteScroll.complete();
      } else {
        infiniteScroll.complete();
      }
    }, 2000);

  }

  public getTotalNotifications(){
    this.unread=false;
    this.viewAll=1;this.page=1;
     this.getAllNotification(this.viewAll, this.page);
  }

  public gotoNotificationDetails(itemid,slug){
    var post_data={'projectId':1,'notifyid':itemid,viewAll:this.viewAll,page:this.page};
   
    this.globalService.deleteNotification(this.constants.deleteNotificationUrl, post_data).subscribe(
      (result) => {
       console.log("DELETE_RESULT"+JSON.stringify(result));
     var param = {"id":itemid,"slug": slug};
        this.navCtrl.push(StoryDetailsPage, param);

      }, (error) => {
        console.log("error delete notification")
      }
    )

  }

}
