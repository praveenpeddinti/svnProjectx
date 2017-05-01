import { NgModule, ErrorHandler } from '@angular/core';
import { IonicApp, IonicModule, IonicErrorHandler } from 'ionic-angular';
import { MyApp } from './app.component';

import { Globalservice } from '../providers/globalservice';
import { Constants } from '../providers/constants'
import { DashboardPage } from '../pages/dashboard/dashboard';
import { LoginPage } from '../pages/login/login';
import { StoryDetailsPage } from '../pages/story-details/story-details';
import { StoryCreatePage } from '../pages/story-create/story-create';
import { SelectAlertless } from '../pages/story-details/SelectAlert';

import {CustomModalPage} from '../pages/custom-modal/custom-modal';

import { Storage } from "@ionic/storage";
import { LogoutPage } from '../pages/logout/logout';

import { CKEditorModule } from 'ng2-ckeditor';
import { AUTOCOMPLETE_DIRECTIVES, AUTOCOMPLETE_PIPES } from 'ionic2-auto-complete';
import {AutoCompleteProvider} from '../providers/auto.complete-provider';

@NgModule({
  declarations: [
    MyApp,
    DashboardPage,
    CustomModalPage,
    LoginPage,
    StoryDetailsPage,
    StoryCreatePage,
    SelectAlertless,
    LogoutPage,
    AUTOCOMPLETE_DIRECTIVES,
    AUTOCOMPLETE_PIPES
  ],
  imports: [
    IonicModule.forRoot(MyApp),
    CKEditorModule
  ],
  bootstrap: [IonicApp],
  entryComponents: [
    MyApp,
    DashboardPage,
    CustomModalPage,
    LoginPage,
    StoryDetailsPage,
    StoryCreatePage,
    SelectAlertless,
    LogoutPage
  ],
  providers: [AutoCompleteProvider, Globalservice, Constants, Storage, {provide: ErrorHandler, useClass: IonicErrorHandler}]

})
export class AppModule {}
