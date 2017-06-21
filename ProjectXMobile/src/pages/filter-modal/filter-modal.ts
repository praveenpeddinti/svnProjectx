import { Component,ViewChild } from '@angular/core';
import { IonicPage, NavController, NavParams,Content,ViewController,ModalController,LoadingController } from 'ionic-angular';
import {DashboardPage} from '../../pages/dashboard/dashboard';
import {Globalservice} from '../../providers/globalservice';
import {Constants} from '../../providers/constants';
declare var jQuery: any;
/**
 * Generated class for the FilterModal page.
 *
 * See http://ionicframework.com/docs/components/#navigation for more info
 * on Ionic pages and navigation.
 */
@IonicPage()
@Component({
  selector: 'page-filter-modal',
  styles:[`
        .Normal {color:#eda74c}
        .Highest{color:#ff2200}
        .High{color:#ff7c7c}
        .Low{color:#c5c950}
        .Lowest{color:#e5e970}
        .selectedMember{position: relative;color:#337ab7;}
        .selectedMember .ion-md-checkmark::before{position: absolute; right: 16px; top: 25%;}
        .selectedMember .ion-ios-checkmark::before{position: absolute; right: 16px; top: 25%;}
        `],
  templateUrl: 'filter-modal.html',
})
export class FilterModal {
   @ViewChild(Content) content: Content;
    public activatedFieldDetails: any;
    public displayFieldvalue : any;
    public activatedFieldTitle: string;
    public activatedFieldValue: string = "";
     
  constructor(public viewCtrl: ViewController, params: NavParams,
      public modalController: ModalController) {
  var thisObj=this;
    this.activatedFieldDetails = params.get('activeField');
    console.log("activatedFieldDetails" + JSON.stringify(this.activatedFieldDetails));
    //this.activatedFieldTitle = this.activatedFieldDetails.title;

      this.displayFieldvalue = params.get('displayList');
      this.activatedFieldValue = params.get('activatedFieldIndex');//"All My Stories/Task";
      console.log("activatedFieldValue" + JSON.stringify(this.activatedFieldValue));
  }
  
    public dismiss(selectedItem) {
    console.log("the data from prabhu filter " + JSON.stringify(selectedItem));
    jQuery("#modelitem_"+selectedItem.id).addClass("itemClick");
      if(Object.keys(selectedItem).length > 0){
          selectedItem['previousValue'] = this.activatedFieldValue;
      }
      setTimeout( ()=>{
          this.viewCtrl.dismiss(selectedItem);
      }, 500);  
  }

  ionViewDidLoad() {
    console.log('ionViewDidLoad FilterModal');
  }
   public ionViewDidEnter(){
       console.log('ionViewDidEnter FilterModal');
   }
}
