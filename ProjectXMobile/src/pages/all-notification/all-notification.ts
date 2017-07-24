import { Component } from '@angular/core';
import { NavController, NavParams, ViewController, AlertController } from 'ionic-angular';
import { Storage } from '@ionic/storage';
import { Globalservice } from '../../providers/globalservice';
import { Constants } from '../../providers/constants';
import { App } from 'ionic-angular';
import { StoryDetailsPage } from '../story-details/story-details';
import { NotificationPage } from '../notification/notification';
import { DashboardPage } from '../dashboard/dashboard';

/**
 * Generated class for the AllNotificationPage page.
 *
 * See http://ionicframework.com/docs/components/#navigation for more info
 * on Ionic pages and navigation.
 */
@Component({
  selector: 'page-all-notification',
  templateUrl: 'all-notification.html',
})
export class AllNotificationPage {
  NotificationPage: any = NotificationPage;
  moreDataLoaded: boolean = true;
  unread: boolean = true;
  page: any = 1;
  viewAll: any = 1;
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
    localStorage.setItem('headerInfo', JSON.stringify({ 'title': "Notifications", backButton: "", logo: 0, leftPannel: 0 }));
    this.storage.get('userCredentials').then((value) => {
      this.logoutParams.userInfo.Id = value.Id;
      this.logoutParams.userInfo.username = value.username;
      this.logoutParams.userInfo.token = value.token;
    });
  }

  ionViewDidLoad() {
    this.getAllNotification(this.viewAll, this.page);
    console.log('ionViewDidLoad AllNotificationPage');
  }

  /**
    * @author Anand Singh
    * @uses Get All notifications
    */
  public getAllNotification(viewAll, page) {
    var post_data = { viewAll: viewAll, page: page };
    this.globalService.getAllNotification(this.constants.notificationUrl, post_data).subscribe(
      (result) => {
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
        this.viewAll = 1
        this.getAllNotification(this.viewAll, this.page);
        infiniteScroll.complete();
      } else {
        infiniteScroll.complete();
      }
    }, 2000);

  }

  /**
   * @author Anand Singh.
   * @uses Navigate to details page from notification page.
   */

  public gotoNotificationDetails(project, ticketId, itemid, slug, isSeen, planLevel) {
    var storyOrTask = "Story";
    if (planLevel == 1) {
      storyOrTask = "Story";
    } else {
      storyOrTask = "Task";
    }
    var param = { "id": ticketId, "slug": slug, "storyOrTask":storyOrTask};
    var post_data = { 'projectId': project.PId, 'notifyid': itemid, viewAll: this.viewAll, page: 1 };
    if (isSeen == 0) {
      this.globalService.deleteNotification(this.constants.deleteNotificationUrl, post_data).subscribe(
        (result) => {
          this.app.getRootNav().push(StoryDetailsPage, param);
        }, (error) => {
          console.log("error delete notification")
        }
      )
    } else {
      this.app.getRootNav().push(StoryDetailsPage, param);
    }
  }

  /**
   * @author Anand Singh.
   * @uses Mark as read any particular unread notification.
   */

  public markAsRead(notifyObj) {
    var post_data = { 'projectId': notifyObj.Project.PId, 'notifyid': notifyObj.id.$oid, viewAll: this.viewAll, page: 1 };
    this.globalService.deleteNotification(this.constants.deleteNotificationUrl, post_data).subscribe(
      (result) => {
        notifyObj.IsSeen = 1;
      }, (error) => {
        console.log("the error is"+error);
        console.log("error delete notification")
      }
    )
  }

}
