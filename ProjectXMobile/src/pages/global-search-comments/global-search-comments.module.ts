import { NgModule } from '@angular/core';
import { IonicPageModule } from 'ionic-angular';
import { GlobalSearchComments } from './global-search-comments';

@NgModule({
  declarations: [
    GlobalSearchComments,
  ],
  imports: [
    IonicPageModule.forChild(GlobalSearchComments),
  ],
  exports: [
    GlobalSearchComments
  ]
})
export class GlobalSearchCommentsModule {}
