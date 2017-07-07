import { NgModule } from '@angular/core';
import { IonicPageModule } from 'ionic-angular';
import { UnreadNotificationPage } from './unread-notification';

@NgModule({
  declarations: [
    UnreadNotificationPage,
  ],
  imports: [
    IonicPageModule.forChild(UnreadNotificationPage),
  ],
  exports: [
    UnreadNotificationPage
  ]
})
export class UnreadNotificationPageModule {}
