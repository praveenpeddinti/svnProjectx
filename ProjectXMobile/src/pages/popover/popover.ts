import { Component } from '@angular/core';
import { NavController, NavParams, ViewController, AlertController } from 'ionic-angular';
import { Storage } from "@ionic/storage";
import { HomePage } from '../home/home';
import { LoginPage } from '../login/login';
import { Globalservice } from '../../providers/globalservice';
import {Constants} from '../../providers/constants';
/*
  Generated class for the Popover page.

  See http://ionicframework.com/docs/v2/components/#navigation for more info on
  Ionic pages and navigation.
*/
@Component({
  selector: 'page-popover',
  templateUrl: 'popover.html',
  providers: [Globalservice, Constants]
})
export class PopoverPage {

 userName: string='';
 login: {username?: string, password?: string,token?:any} = {};
// userInfo = {"Id":"","username":"","token":"","projectId":1}
logoutParams = {"userInfo":{"Id":"","username":"","token":""},"projectId":1}

  constructor(private globalService: Globalservice,private constants: Constants,public navCtrl: NavController,
            public alertController: AlertController,
            private storage:Storage,
            public navParams: NavParams, 
            public viewCtrl: ViewController) {

           this.storage.get('userCredentials').then((value) => {
            console.log("in did load " + value.username);
            this.logoutParams.userInfo.Id= value.Id;
            this.logoutParams.userInfo.username=value.username;
            this.logoutParams.userInfo.token=value.token;
        });
     
            }
  close() {
    this.viewCtrl.dismiss();
  }
  ionViewDidLoad() {
    console.log('ionViewDidLoad PopoverPage');
      this.userName = this.navParams.get("username");
      console.log("User name is " + this.userName);
  }
logoutApp() {
  this.globalService.getLogout(this.constants.LogutUrl,this.logoutParams).subscribe(
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
                  this.storage.remove('userCredentials').then( ()=>{  
                      this.navCtrl.push(LoginPage);
                      console.log('Logged out');
                      this.viewCtrl.dismiss();
                  }, 
                  (error)=>{
                      console.log("error while removing ");
                  });   
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

}