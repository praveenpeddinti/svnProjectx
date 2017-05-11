import { NgModule } from '@angular/core';
import { IonicPageModule } from 'ionic-angular';
import { StoryDetailsComments } from './story-details-comments';

@NgModule({
  declarations: [
    StoryDetailsComments,
  ],
  imports: [
    IonicPageModule.forChild(StoryDetailsComments),
  ],
  exports: [
    StoryDetailsComments
  ]
})
export class StoryDetailsCommentsModule {}
