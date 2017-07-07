import { Component } from '@angular/core';
import { NavController, NavParams, ViewController, AlertController } from 'ionic-angular';
import { Storage } from '@ionic/storage';
import { LoginPage } from '../login/login';
import { Globalservice } from '../../providers/globalservice';
import { Constants } from '../../providers/constants';
import { App } from 'ionic-angular';
import { UnreadNotificationPage } from '../unread-notification/unread-notification';
import { AllNotificationPage } from '../all-notification/all-notification';
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
  UnreadNotificationPage:any;
  AllNotificationPage;any;
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
      this.UnreadNotificationPage=UnreadNotificationPage;
      this.AllNotificationPage=AllNotificationPage;
    localStorage.setItem('headerInfo',JSON.stringify({'title':"Notifications",backButton:"",logo:0,leftPannel:0,notification:0,profile:0}));
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
  
    this.userName = this.navParams.get("username");
  }

  ionViewWillLeave() {
console.log("Looks like I'm about to leave :(");
}
  

}
