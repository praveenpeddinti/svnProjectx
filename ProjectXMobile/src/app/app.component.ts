import { Component } from '@angular/core';
import { Platform } from 'ionic-angular';
import { StatusBar, Splashscreen } from 'ionic-native';
import { Storage } from '@ionic/storage'

import { HomePage } from '../pages/home/home';
import { LoginPage } from '../pages/login/login';
import { WelcomePage } from '../pages/welcome/welcome';
import { DashboardPage } from '../pages/dashboard/dashboard';

@Component({
  templateUrl: 'app.html'
})
export class MyApp {
  //rootPage = HomePage;
  private rootPage;

  constructor(platform: Platform, private storage: Storage) {

    this.storage.get('userCredentials').then((value) => {
        console.log("the credentails " + value)
          if(value != null || value != undefined){
              //this.rootPage = WelcomePage;
              this.rootPage = DashboardPage;
          } else {
              //this.rootPage = HomePage;
              this.rootPage = LoginPage;
          }
      });


    platform.ready().then(() => {
      // Okay, so the platform is ready and our plugins are available.
      // Here you can do any higher level native things you might need.
      StatusBar.styleDefault();
      Splashscreen.hide();
    });

  }
}