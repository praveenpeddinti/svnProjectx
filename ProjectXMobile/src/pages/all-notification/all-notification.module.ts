import { NgModule } from '@angular/core';
import { IonicPageModule } from 'ionic-angular';
import { AllNotificationPage } from './all-notification';

@NgModule({
  declarations: [
    AllNotificationPage,
  ],
  imports: [
    IonicPageModule.forChild(AllNotificationPage),
  ],
  exports: [
    AllNotificationPage
  ]
})
export class AllNotificationPageModule {}
