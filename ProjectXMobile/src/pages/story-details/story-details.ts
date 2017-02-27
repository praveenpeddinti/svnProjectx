import { Component } from '@angular/core';
import { NavController, NavParams } from 'ionic-angular';

import { Globalservice } from '../../providers/globalservice';
import { Constants } from '../../providers/constants';

/*
  Generated class for the StoryDetails page.

  See http://ionicframework.com/docs/v2/components/#navigation for more info on
  Ionic pages and navigation.
*/
@Component({
  selector: 'page-story-details',
  templateUrl: 'story-details.html'
})
export class StoryDetailsPage {

  public description: string;
  public isBusy: boolean = false;
  public options = "options";

  constructor(public globalService: Globalservice, private constants: Constants, public navCtrl: NavController, public navParams: NavParams) {

    globalService.getTicketDetailsById(this.constants.taskDetailsById, 6).subscribe(
        result=>{

              //console.log("the ticket details " + JSON.stringify(result));
              this.description = result.data.Description;
        },error=>{

              console.log("the error in ticker derais " + JSON.stringify(error));
        }
    );
  }

  ionViewDidLoad() {
    console.log('ionViewDidLoad StoryDetailsPage');
  }

  public changeOption(event){
    console.log("the options --- " + this.options + " -------------");
    console.log("the change " + JSON.stringify(event) );
  }

  public openDatePicker(){
    
  }

}
