import { NgModule } from '@angular/core';
import { IonicPageModule } from 'ionic-angular';
import { StoryDetailsFollowers } from './story-details-followers';

@NgModule({
  declarations: [
    StoryDetailsFollowers,
  ],
  imports: [
    IonicPageModule.forChild(StoryDetailsFollowers),
  ],
  exports: [
    StoryDetailsFollowers
  ]
})
export class StoryDetailsFollowersModule {}
