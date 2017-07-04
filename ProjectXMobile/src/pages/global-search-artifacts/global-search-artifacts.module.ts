import { NgModule } from '@angular/core';
import { IonicPageModule } from 'ionic-angular';
import { GlobalSearchArtifacts } from './global-search-artifacts';

@NgModule({
  declarations: [
    GlobalSearchArtifacts,
  ],
  imports: [
    IonicPageModule.forChild(GlobalSearchArtifacts),
  ],
  exports: [
    GlobalSearchArtifacts
  ]
})
export class GlobalSearchArtifactsModule {}
