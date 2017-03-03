import { NgModule, ErrorHandler } from '@angular/core';
import { IonicApp, IonicModule, IonicErrorHandler } from 'ionic-angular';
import { MyApp } from './app.component';
import { HomePage } from '../pages/home/home';

import { Globalservice } from '../providers/globalservice';
import { Constants } from '../providers/constants'
import { WelcomePage } from '../pages/welcome/welcome';
import { DashboardPage } from '../pages/dashboard/dashboard';
import { LoginPage } from '../pages/login/login';
import { StoryDetailsPage } from '../pages/story-details/story-details';
import { SelectAlertless } from '../pages/story-details/SelectAlert';

import { Storage } from "@ionic/storage";
import { PopoverPage } from '../pages/popover/popover';

import { CKEditorModule } from 'ng2-ckeditor';

@NgModule({
  declarations: [
    MyApp,
    HomePage,
    WelcomePage,
    DashboardPage,
    LoginPage,
    StoryDetailsPage,
    SelectAlertless,
    PopoverPage
  ],
  imports: [
    IonicModule.forRoot(MyApp),
    CKEditorModule
  ],
  bootstrap: [IonicApp],
  entryComponents: [
    MyApp,
    HomePage,
    WelcomePage,
    DashboardPage,
    LoginPage,
    StoryDetailsPage,
    SelectAlertless,
    PopoverPage
  ],
  providers: [Globalservice, Constants, Storage, {provide: ErrorHandler, useClass: IonicErrorHandler}]

})
export class AppModule {}
