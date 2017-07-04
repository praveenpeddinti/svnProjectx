import { NgModule } from '@angular/core';
import { IonicPageModule } from 'ionic-angular';
import { GlobalSearchAll } from './global-search-all';

@NgModule({
  declarations: [
    GlobalSearchAll,
  ],
  imports: [
    IonicPageModule.forChild(GlobalSearchAll),
  ],
  exports: [
    GlobalSearchAll
  ]
})
export class GlobalSearchAllModule {}
