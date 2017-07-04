import { NgModule } from '@angular/core';
import { IonicPageModule } from 'ionic-angular';
import { GlobalSearch } from './global-search';

@NgModule({
  declarations: [
    GlobalSearch,
  ],
  imports: [
    IonicPageModule.forChild(GlobalSearch),
  ],
  exports: [
    GlobalSearch
  ]
})
export class GlobalSearchModule {}
