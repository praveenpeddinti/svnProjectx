import { Component } from '@angular/core';
import { NavController, NavParams, ViewController } from 'ionic-angular';

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
        .selectedMember{background-color:#78c9ee}`],
  templateUrl: 'custom-modal.html'
})
export class CustomModalPage {

  
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
  }

}
