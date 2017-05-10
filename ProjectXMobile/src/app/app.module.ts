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

//import { Storage } from "@ionic/storage";
import { IonicStorageModule } from '@ionic/storage';
import { LogoutPage } from '../pages/logout/logout';

import { CKEditorModule } from 'ng2-ckeditor';
import {AutoCompleteProvider} from '../providers/auto.complete-provider';
import { AutoCompleteModule } from 'ionic2-auto-complete';
//Ionic2-tabs
import { SuperTabsModule, SuperTabsController } from 'ionic2-super-tabs';
import { StoryWorklogPage } from '../pages/story-worklog/story-worklog';
import { StoryFollowersPage } from '../pages/story-followers/story-followers';
import { StoryTaskPage } from '../pages/story-task/story-task';
import { BrowserModule } from '@angular/platform-browser';
import { HttpModule} from '@angular/http';
import { Camera } from '@ionic-native/camera';
import { File } from '@ionic-native/file';
import { Transfer } from '@ionic-native/transfer';
import { FilePath } from '@ionic-native/file-path';


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
    StoryWorklogPage,
    StoryFollowersPage,
    StoryTaskPage
  ],
  imports: [
    IonicModule.forRoot(MyApp),
    CKEditorModule,
    AutoCompleteModule,
    BrowserModule,
    HttpModule,
    SuperTabsModule.forRoot(),
    IonicStorageModule.forRoot(),
    
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
    LogoutPage,
    StoryWorklogPage,
    StoryFollowersPage,
    StoryTaskPage
  ],
  providers: [AutoCompleteProvider, Globalservice, Camera, File, Transfer, FilePath, Constants, {provide: ErrorHandler, useClass: IonicErrorHandler}]

})
export class AppModule {}
