import { NgModule } from '@angular/core';
import { IonicPageModule } from 'ionic-angular';
import { DashBoardFilter } from './dash-board-filter';

@NgModule({
  declarations: [
    DashBoardFilter,
  ],
  imports: [
    IonicPageModule.forChild(DashBoardFilter),
  ],
  exports: [
    DashBoardFilter
  ]
})
export class DashBoardFilterModule {}
