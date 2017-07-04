import { NgModule, ErrorHandler } from '@angular/core';
import { IonicApp, IonicModule, IonicErrorHandler } from 'ionic-angular';
import { MyApp } from './app.component';

//import { Storage } from "@ionic/storage";
import { IonicStorageModule } from '@ionic/storage';
import { CKEditorModule } from 'ng2-ckeditor';
// Ticket #113
import { AutoCompleteModule } from 'ionic2-auto-complete';
// Ticket #113 ended
import { SuperTabsModule, SuperTabsController } from 'ionic2-super-tabs';
import { BrowserModule } from '@angular/platform-browser';
import { HttpModule} from '@angular/http';
import { Camera } from '@ionic-native/camera';
import { File } from '@ionic-native/file';
import { Transfer } from '@ionic-native/transfer';
import { FilePath } from '@ionic-native/file-path';

import { Globalservice } from '../providers/globalservice';
import { Constants } from '../providers/constants'
import { DashboardPage } from '../pages/dashboard/dashboard';
import { LoginPage } from '../pages/login/login';
import { StoryDetailsPage } from '../pages/story-details/story-details';
import { StoryCreatePage } from '../pages/story-create/story-create';
import { SelectAlertless } from '../pages/story-details/SelectAlert';
import {CustomModalPage} from '../pages/custom-modal/custom-modal';
import {FilterModal} from '../pages/filter-modal/filter-modal';
import {GlobalSearch} from '../pages/global-search/global-search';
import { LogoutPage } from '../pages/logout/logout';
// Ticket #113
import { CustomAutocompleteItem } from '../pages/story-details/custom-autocomplete-item';
// Ticket #113 ended
import { AutoCompleteProvider } from '../providers/auto-complete-provider';
//Ionic2-tabs
import { StoryDetailsComments } from '../pages/story-details-comments/story-details-comments';
import {StoryDetailsFollowers} from '../pages/story-details-followers/story-details-followers';
import {StoryDetailsTask} from '../pages/story-details-task/story-details-task';
import {StoryDetailsWorklog} from '../pages/story-details-worklog/story-details-worklog';
import {GlobalSearchAll} from '../pages/global-search-all/global-search-all';
import {GlobalSearchArtifacts} from '../pages/global-search-artifacts/global-search-artifacts';
import {GlobalSearchComments} from '../pages/global-search-comments/global-search-comments';
import {GlobalSearchStorytask} from'../pages/global-search-storytask/global-search-storytask';
import {GlobalSearchUsers} from '../pages/global-search-users/global-search-users';
//import { StoryWorklogPage } from '../pages/story-worklog/story-worklog';
import { NotificationPage } from '../pages/notification/notification';
import { HeaderPage } from '../pages/header/header';

@NgModule({
  declarations: [
    MyApp,
    DashboardPage,
    CustomModalPage,
    FilterModal,
    GlobalSearch,
    LoginPage,
    StoryDetailsPage,
    StoryCreatePage,
    SelectAlertless,
    LogoutPage,
    CustomAutocompleteItem,
    StoryDetailsWorklog,
    StoryDetailsFollowers,
    StoryDetailsTask,
    StoryDetailsComments,
    GlobalSearchAll,
    GlobalSearchArtifacts,
    GlobalSearchComments,
    GlobalSearchStorytask,
    GlobalSearchUsers,
    NotificationPage,
    HeaderPage
  ],
  imports: [
    IonicModule.forRoot(MyApp),
    CKEditorModule,
    BrowserModule,
    AutoCompleteModule,
    HttpModule,
    SuperTabsModule.forRoot(),
    IonicStorageModule.forRoot()
  ],
  bootstrap: [IonicApp],
  entryComponents: [
    MyApp,
    DashboardPage,
    CustomModalPage,
    FilterModal,
    GlobalSearch,
    LoginPage,
    StoryDetailsPage,
    StoryCreatePage,
    SelectAlertless,
    LogoutPage,
    StoryDetailsWorklog,
    StoryDetailsFollowers,
    StoryDetailsTask,
    StoryDetailsComments,
    GlobalSearchAll,
    GlobalSearchArtifacts,
    GlobalSearchComments,
    GlobalSearchStorytask,
    GlobalSearchUsers,
    NotificationPage,
    HeaderPage
  ],
  providers: [AutoCompleteProvider, Globalservice, Camera, File, Transfer, FilePath, Constants, {provide: ErrorHandler, useClass: IonicErrorHandler}]

})
export class AppModule {}
