import { Component } from '@angular/core';
import { NavController, NavParams } from 'ionic-angular';
import { Globalservice } from '../../providers/globalservice';
import { Constants } from '../../providers/constants';

@Component({
  selector: 'page-story-followers',
  templateUrl: 'story-followers.html'
})
export class StoryFollowersPage {

  constructor(public navCtrl: NavController, 
     public navParams: NavParams,
     public globalService: Globalservice,
     private constants: Constants) {
       
     }

  ionViewDidLoad() {
    console.log('ionViewDidLoad StoryFollowersPage');
  }

}
