import { NgModule, ErrorHandler } from '@angular/core';
import { IonicApp, IonicModule, IonicErrorHandler } from 'ionic-angular';
import { MyApp } from './app.component';
import { HomePage } from '../pages/home/home';

import { Globalservice } from '../providers/globalservice';
import { Constants } from '../providers/constants'
import {WelcomePage} from '../pages/welcome/welcome';

import { StoryDetailsPage } from '../pages/story-details/story-details';
import { SelectAlertless } from '../pages/story-details/SelectAlert';

 import { DatePickerModule } from 'datepicker-ionic2';

import {Storage} from "@ionic/storage";
import { PopoverPage } from '../pages/popover/popover';

@NgModule({
  declarations: [
    MyApp,
    HomePage,
    WelcomePage,
    StoryDetailsPage,
    SelectAlertless,
    WelcomePage,
    PopoverPage
  ],
  imports: [
    IonicModule.forRoot(MyApp)
  ],
  bootstrap: [IonicApp],
  entryComponents: [
    MyApp,
    HomePage,
    WelcomePage,
    StoryDetailsPage,
    SelectAlertless,
    WelcomePage,
    PopoverPage
  ],
  providers: [Globalservice, Constants, Storage, {provide: ErrorHandler, useClass: IonicErrorHandler}]

})
export class AppModule {}
