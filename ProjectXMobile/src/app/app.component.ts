import { Component } from '@angular/core';
import { Platform } from 'ionic-angular';
import { SplashScreen } from '@ionic-native/splash-screen';
import { StatusBar } from '@ionic-native/status-bar';
// import { StatusBar } from '@ionic-native';
// import { SplashScreen } from '@ionic-native';
import { Storage } from '@ionic/storage';
import { LoginPage } from '../pages/login/login';
import { DashboardPage } from '../pages/dashboard/dashboard';
import { DashBoardFilterPage } from '../pages/DashBoardFilterPage/DashBoardFilterPage';

import { Globalservice } from '../providers/globalservice';
import { Constants } from '../providers/constants';

@Component({
  templateUrl: 'app.html'
})
export class MyApp {
  //rootPage = HomePage;
  private rootPage;
  public items: Array<any>;
  public arrayList: Array<{ id: string, title: string, assignTo: string, priority: string, bucket: string, planlevel: string }>;
 // paramas = { "projectId": 1, "offset": 0, "pagesize": 10, "sortvalue": "Id", "sortorder": "desc", "userInfo": { "Id": "9", "username": "hareesh.bekkam", "token": "a1a3d7b95e950cbc1cb7@9" } };
  constructor(private globalService: Globalservice, private constants: Constants, platform: Platform, private storage: Storage) {
      var userInfo=JSON.parse(localStorage.getItem("userCredentials"));
        //  this.storage.get('userCredentials').then((value) => {
        if(userInfo == null ||userInfo == undefined){
          this.rootPage = LoginPage;
        }else{
          this.rootPage = DashboardPage;
        }
      //});
        
    platform.ready().then(() => {
      // Okay, so the platform is ready and our plugins are available.
      // Here you can do any higher level native things you might need.
      //StatusBar.styleDefault();
    //  Splashscreen.hide();
    });
  }
}