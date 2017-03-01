import { Component, ViewChild } from '@angular/core';
import { Platform, MenuController, Nav } from 'ionic-angular';
import { StatusBar, Splashscreen } from 'ionic-native';

import { HomePage } from '../pages/home/home';
import { WelcomePage } from '../pages/welcome/welcome';
import { StoryDetailsPage } from '../pages/story-details/story-details';
import { ListPage } from '../pages/list/list';

@Component({
  templateUrl: 'app.html'
})
export class MyApp {
  @ViewChild(Nav) nav: Nav;
    public options = "options";
  rootPage = HomePage;
   pages: Array<{title: string, component: any}>;

  constructor(platform: Platform, public menu: MenuController) {

    // set our app's pages
    this.pages = [
      { title: 'Hello welcome', component: WelcomePage },
      { title: 'My Story', component: StoryDetailsPage }
    ];


    platform.ready().then(() => {
      // Okay, so the platform is ready and our plugins are available.
      // Here you can do any higher level native things you might need.
      StatusBar.styleDefault();
      Splashscreen.hide();
    });

  }
  public changeOption(event){
    console.log("the options --- " + this.options + " -------------");
    console.log("the change " + JSON.stringify(event) );
  }
   openPage(page) {
    // close the menu when clicking a link from the menu
    this.menu.close();
    // navigate to the new page if it is not the current page
    this.nav.setRoot(page.component);
  }
}
