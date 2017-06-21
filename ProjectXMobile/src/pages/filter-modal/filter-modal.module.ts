import { NgModule } from '@angular/core';
import { IonicPageModule } from 'ionic-angular';
import { FilterModal } from './filter-modal';

@NgModule({
  declarations: [
    FilterModal,
  ],
  imports: [
    IonicPageModule.forChild(FilterModal),
  ],
  exports: [
    FilterModal
  ]
})
export class FilterModalModule {}
