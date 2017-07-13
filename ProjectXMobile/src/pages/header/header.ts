import { Component,NgZone } from '@angular/core';
import {IonicPage,NavController, NavParams, AlertController, ViewController, LoadingController, PopoverController, ModalController, Platform} from 'ionic-angular';
import {Storage} from "@ionic/storage";
import {LogoutPage} from '../logout/logout';
import {NotificationPage} from '../notification/notification';
import {Globalservice} from '../../providers/globalservice';
import {GlobalSearch} from '../global-search/global-search';
import {DashboardPage} from '../dashboard/dashboard';
declare var jQuery: any;
declare var socket:any;
/**
 * Generated class for the HeaderPage page.
 *
 * See http://ionicframework.com/docs/components/#navigation for more info
 * on Ionic pages and navigation.
 */
@IonicPage()
@Component({
  selector: 'page-header',
  templateUrl: 'header.html',
})
export class HeaderPage {
public notify_count:any='';
public title:any;
public backbutton:any;
public logo:any;
public ngZone:any;
public leftPannel:any
public notification:any;
public profile:any;
public searchBar:any;
public searchValue : any;
 public toggled: boolean;
 public hideElement: boolean=true;
  constructor(private globalService: Globalservice,public navCtrl: NavController, public navParams: NavParams, public popoverCtrl: PopoverController, private alertController: AlertController) {
  this.toggled = false;
  var headerInfo=JSON.parse(localStorage.getItem("headerInfo"));
  this.title=headerInfo.title;
  this.backbutton=headerInfo.backbutton;
  this.logo =headerInfo.logo;
  this.leftPannel =headerInfo.leftPannel;
  this.notification=headerInfo.notification;
  this.profile=headerInfo.profile;
  this.searchBar = headerInfo.searchBar;
  var post_data = {};
  this.ngZone = new NgZone({ enableLongStackTrace: false });
            var thisObj = this;
            this.globalService.SocketSubscribe('getAllNotificationsCount', post_data);
            socket.on('getAllNotificationsCountResponse', function (data) {
                data = JSON.parse(data);
                console.log("getAllNotificationsCountResponse-------Mobile----" + data.count);
               thisObj.ngZone.run(() => {
                   thisObj.notify_count = data.count;
                 });
              
                console.log("new__count__"+thisObj.notify_count);
            });

 }

  ionViewDidLoad() {
  
    console.log('ionViewDidLoad HeaderPage');
  }
    toggle() {
      console.log("clicked toggle");
       this.toggled = this.toggled ? false : true;
       this.hideElement=false;
    }
    public cancelSearch(){
       this.toggle(); 
    }
    public backToggled(){
        console.log("backToggled");
        this.toggled = false;
        this.navCtrl.setRoot(DashboardPage);
    }
   public clickSearch(){
       console.log("searchValue is" + this.searchValue);
       var searchData = {"searchValue": this.searchValue};
        //this.navCtrl.push(GlobalSearch, searchData);
    }
    submitSearch(){
        var searchData = {"searchValue": this.searchValue};
        if (this.searchValue == undefined) {
            let alert = this.alertController.create({
            title: 'Warning!',
            message: 'Please enter search value!',
            buttons: [
                {
                    text: 'OK',
                    role: 'cancel',
                    handler: () => { }
                }
            ]
        });
        alert.present();
        } else {
        console.log("searchValue is22222" + this.searchValue);
        this.navCtrl.push(GlobalSearch, searchData);
        }
    }
  public openPopover(myEvent) {  
        let popover = this.popoverCtrl.create(LogoutPage);
        popover.present({
            ev: myEvent
        });
    };

     /**
     * @author Anand Singh
     * @uses Goto All notifications
     */
    public gotoNotification(){
          this.navCtrl.push(NotificationPage);
        
        } 
}