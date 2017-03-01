import { Component } from '@angular/core';
import { NavController, NavParams, AlertController, ViewController, LoadingController, PopoverController  } from 'ionic-angular';
import { HomePage } from '../home/home';
import {Storage} from "@ionic/storage";
import { PopoverPage } from '../popover/popover';
import { StoryDetailsPage } from '../story-details/story-details'
import { Globalservice } from '../../providers/globalservice';
import {Constants} from '../../providers/constants';
/*
  Generated class for the Welcome page.

  See http://ionicframework.com/docs/v2/components/#navigation for more info on
  Ionic pages and navigation.
*/
@Component({
  selector: 'page-welcome',
  templateUrl: 'welcome.html',
  providers: [Globalservice, Constants]
})
export class WelcomePage {
  login: {username?: string, password?: string,token?:any} = {};
  public options = "options";
  changeStatus="";
  userName: string='';
  userPassword:string='';
  userToken:any='';
  constructor(private globalService: Globalservice,private constants: Constants, public navCtrl: NavController, public navParams: NavParams, public loadingController: LoadingController,
  public popoverCtrl: PopoverController,
  public alertController: AlertController, private storage:Storage,   public viewCtrl: ViewController) {
  //   let loader = this.loadingController.create({ content: "Loading..."});  
  //  loader.present();
 
  }
    
  ionViewDidLoad() {
    console.log('ionViewDidLoad WelcomePage');
    this.userName = this.navParams.get("username");
    this.userPassword = this.navParams.get("password");
    this.userToken = this.navParams.get("token");
    console.log("Tken value" + this.userToken);
    
  }
  openPopover(myEvent) {
    let userCredentials = {username: this.userName};
    let popover = this.popoverCtrl.create(PopoverPage,userCredentials);
    console.log("User name is "+ this.userName);
    popover.present({
      ev: myEvent
    });
  }
logoutApp() {
  this.globalService.getLogout(this.constants.LogutUrl,this.login).subscribe(
    data =>{
  let alert = this.alertController.create({
    title: 'Confirm Log Out',
    message: 'Are you sure you want to log out?',
    buttons: [
      {
        text: 'Cancel',
        role: 'cancel',
        handler: () => {
          console.log('Cancel clicked');
        }
      },
      {
        text: 'Log Out',
        handler: () => {
        this.storage.remove('username');
        this.storage.remove('password');
        this.storage.remove('token');
        this.navCtrl.setRoot(HomePage);
        console.log('Logged out');
        this.viewCtrl.dismiss();
        
        }
      }
    ]
  });
  alert.present();
    },
      error=>
      { console.log("the error " + JSON.stringify(error)); },
      ()=> console.log('logout api call complete'));
      }
 public changeOption(event){
    console.log("the options --- " + this.options + " -------------");
    console.log("the change " + JSON.stringify(event) );
  }
public storyDetail(): void{
      this.navCtrl.push(StoryDetailsPage);
    }
}
