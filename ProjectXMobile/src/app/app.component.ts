import { Component } from '@angular/core';
import { Platform } from 'ionic-angular';
import { StatusBar, Splashscreen } from 'ionic-native';
import { Storage } from '@ionic/storage'
import { HomePage } from '../pages/home/home';
import { LoginPage } from '../pages/login/login';
import { WelcomePage } from '../pages/welcome/welcome';
import { DashboardPage } from '../pages/dashboard/dashboard';
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
paramas = { "projectId": 1, "offset": 0, "pagesize": 10, "sortvalue": "Id", "sortorder": "desc", "userInfo": { "Id": "9", "username": "hareesh.bekkam", "token": "a1a3d7b95e950cbc1cb7@9" } };
  constructor(private globalService: Globalservice, private constants: Constants, platform: Platform, private storage: Storage) {

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
      globalService.getStoriesList(this.constants.getAllTicketDetails, this.paramas).subscribe(
      result => {
         this.items = result.data;
        console.log("the count value is from Appcomponent" + this.items.length);
        this.arrayList = [];
         for(let i = 0; i < this.items.length; i++) {
           var _id = this.items[i][0].field_value;
          var _title = this.items[i][1].field_value;
          var _assignTo = this.items[i][2].field_value;
          var _priority = this.items[i][3].field_value;
          var _bucket = this.items[i][4].field_value;
          var _planlevel = this.items[i][5].field_value;
         this.arrayList.push({
        id: _id, title: _title, assignTo: _assignTo, priority: _priority,bucket:_bucket,planlevel:_planlevel
      });
      console.log("assign to value is "+ _assignTo[i]);
    }
      },
       error => {
        console.log("the error " + JSON.stringify(error));
      },
      () => console.log('login api call complete')
    );

    platform.ready().then(() => {
      // Okay, so the platform is ready and our plugins are available.
      // Here you can do any higher level native things you might need.
      StatusBar.styleDefault();
      Splashscreen.hide();
    });

  }
}