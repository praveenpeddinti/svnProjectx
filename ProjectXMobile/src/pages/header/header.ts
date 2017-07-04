import { Component } from '@angular/core';
import {IonicPage,NavController, NavParams, AlertController, ViewController, LoadingController, PopoverController, ModalController, Platform} from 'ionic-angular';
import {Storage} from "@ionic/storage";
import {LogoutPage} from '../logout/logout';
import {NotificationPage} from '../notification/notification';
import {Globalservice} from '../../providers/globalservice';
import {GlobalSearch} from '../global-search/global-search';
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
public leftPannel:any
public searchBar:any;
public searchValue : any;
 public toggled: boolean;
 
  constructor(private globalService: Globalservice,public navCtrl: NavController, public navParams: NavParams, public popoverCtrl: PopoverController) {
  this.toggled = false;
  var headerInfo=JSON.parse(localStorage.getItem("headerInfo"));
  this.title=headerInfo.title;
  this.backbutton=headerInfo.backbutton;
  this.logo =headerInfo.logo;
  this.leftPannel =headerInfo.leftPannel;
  this.searchBar = headerInfo.searchBar;
  var post_data = {};
            var thisObj = this;
            this.globalService.SocketSubscribe('getAllNotificationsCount', post_data);
            socket.on('getAllNotificationsCountResponse', function (data) {
                data = JSON.parse(data);
                console.log("getAllNotificationsCountResponse-------Mobile----" + data.count);
                thisObj.notify_count = data.count;
            });

 }

  ionViewDidLoad() {
  
    console.log('ionViewDidLoad HeaderPage');
  }
    toggle() {
      console.log("clicked toggle");
       this.toggled = this.toggled ? false : true;
    }
    public cancelSearch(){
       this.toggle(); 
    }
   public clickSearch(){
       console.log("searchValue is" + this.searchValue);
       var searchData = {"searchValue": this.searchValue};
        this.navCtrl.push(GlobalSearch, searchData);
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
