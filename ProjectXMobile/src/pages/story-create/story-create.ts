import { Component } from '@angular/core';
import { NavController, NavParams } from 'ionic-angular';
import { Globalservice } from '../../providers/globalservice';

@Component({
  selector: 'page-story-create',
  templateUrl: 'story-create.html'
})
export class StoryCreatePage {
public showEditableFieldOnly = [];
  constructor(public navCtrl: NavController, public navParams: NavParams,
    private globalService: Globalservice) {}
     
    onCreate(form): void {  
 }
}
