import { Component } from '@angular/core';
import { NavController, NavParams, ViewController, AlertController } from 'ionic-angular';
import { Storage } from "@ionic/storage";
import { HomePage } from '../home/home';
import { LoginPage } from '../login/login';
/*
  Generated class for the Popover page.

  See http://ionicframework.com/docs/v2/components/#navigation for more info on
  Ionic pages and navigation.
*/
@Component({
  selector: 'page-popover',
  templateUrl: 'popover.html'
})
export class PopoverPage {
 userName: string='';
  constructor(public navCtrl: NavController,
            public alertController: AlertController,
            private storage:Storage,
            public navParams: NavParams, 
            public viewCtrl: ViewController) {
                
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
}
}