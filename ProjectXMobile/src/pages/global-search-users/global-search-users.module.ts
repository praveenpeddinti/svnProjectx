import { NgModule } from '@angular/core';
import { IonicPageModule } from 'ionic-angular';
import { GlobalSearchUsers } from './global-search-users';

@NgModule({
  declarations: [
    GlobalSearchUsers,
  ],
  imports: [
    IonicPageModule.forChild(GlobalSearchUsers),
  ],
  exports: [
    GlobalSearchUsers
  ]
})
export class GlobalSearchUsersModule {}
