import { NgModule } from '@angular/core';
import { IonicPageModule } from 'ionic-angular';
import { GlobalSearchStorytask } from './global-search-storytask';

@NgModule({
  declarations: [
    GlobalSearchStorytask,
  ],
  imports: [
    IonicPageModule.forChild(GlobalSearchStorytask),
  ],
  exports: [
    GlobalSearchStorytask
  ]
})
export class GlobalSearchStorytaskModule {}
