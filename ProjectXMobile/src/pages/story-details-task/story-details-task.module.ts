import { NgModule } from '@angular/core';
import { IonicPageModule } from 'ionic-angular';
import { StoryDetailsTask } from './story-details-task';

@NgModule({
  declarations: [
    StoryDetailsTask,
  ],
  imports: [
    IonicPageModule.forChild(StoryDetailsTask),
  ],
  exports: [
    StoryDetailsTask
  ]
})
export class StoryDetailsTaskModule {}
