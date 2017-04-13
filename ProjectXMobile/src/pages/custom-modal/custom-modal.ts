import { Component, ViewChild } from '@angular/core';
import { NavParams, ViewController } from 'ionic-angular';
import {Content} from 'ionic-angular';
declare var jQuery: any;

/*
  Generated class for the CustomModal page.

  See http://ionicframework.com/docs/v2/components/#navigation for more info on
  Ionic pages and navigation.
*/
@Component({
  selector: 'page-custom-modal',
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
  templateUrl: 'custom-modal.html'
})
export class CustomModalPage {

    @ViewChild(Content) content: Content;
  
    public activatedFieldDetails: any;
    public displayFieldvalue: any;
    public activatedFieldTitle: string;
    public activatedFieldValue: string = "";
    public isSlected : boolean = false;
    public isPriority: boolean = false;

 constructor(public viewCtrl: ViewController, params: NavParams) {
   this.activatedFieldDetails = params.get('activeField');
   this.activatedFieldTitle = this.activatedFieldDetails.title;

   if(this.activatedFieldDetails.fieldName == "priority"){
        // show priority color images
        this.isPriority = true;
   }

    this.displayFieldvalue = params.get('displayList');
    this.activatedFieldValue = document.getElementById("field.title_field.id_" + params.get('activatedFieldIndex')).innerHTML;
 }

 dismiss(selectedItem) {
   console.log("the dismiss item " + JSON.stringify(selectedItem));
   if(Object.keys(selectedItem).length > 0){
      selectedItem['previousValue'] = this.activatedFieldValue;
   }
      this.viewCtrl.dismiss(selectedItem);
 }


  ionViewDidLoad() {
    console.log('ionViewDidLoad CustomModalPage');
    // this.content.scrollToBottom(300);
   }
   public ionViewDidEnter(){
    // this.content.scrollToBottom(300);
    console.log("the top of item " + jQuery("#item_5").position().top);
    let dimensions = this.content.getContentDimensions();
    console.log("the contentBottom " + JSON.stringify(dimensions) + " ----- " + dimensions.contentBottom);
    this.content.scrollTo(0, jQuery("#item_5").position().top, 300);
  }
}