import { NgModule } from '@angular/core';
import { IonicPageModule } from 'ionic-angular';
import { StoryDetailsWorklog } from './story-details-worklog';

@NgModule({
  declarations: [
    StoryDetailsWorklog,
  ],
  imports: [
    IonicPageModule.forChild(StoryDetailsWorklog),
  ],
  exports: [
    StoryDetailsWorklog
  ]
})
export class StoryDetailsWorklogModule {}
