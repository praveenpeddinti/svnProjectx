import { Component } from '@angular/core';
import { NavController, NavParams, MenuController } from 'ionic-angular';

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
public items: Array<any>;
public arrayList: Array<{ id: string, title: string, assignTo: string, priority: string, bucket: string, planlevel: string }>;
  public description: string;
  public isBusy: boolean = false;
  public options = "options";
  public localDate: Date = new Date();
 // public maxDate: Date = new Date(new Date().setDate(new Date().getDate() + 30));
  public minDate: any = new Date();
  public myDate: string = "2017-02-25";

  public ckeditorContent = "";
  public config = {toolbar : [
      [ 'Heading 1', '-', 'Bold','-', 'Italic','-','Underline','Link','NumberedList','BulletedList']
  ],removePlugins:'elementspath,magicline',resize_enabled:true};

  constructor(menu: MenuController, public globalService: Globalservice, private constants: Constants, public navCtrl: NavController, public navParams: NavParams) {
      menu.swipeEnable(false);
    this.minDate = this.formatDate(new Date());

    globalService.getTicketDetailsById(this.constants.taskDetailsById, 6).subscribe(
        result=>{
              //console.log("the ticket details " + JSON.stringify(result));
              this.description = result.data.Description;
    this.items = result.data.Fields;
        console.log("the count value is from Appcomponent" + this.items.length);
        this.arrayList = [];
         for(let i = 0; i < this.items.length; i++) {
            var _id = this.items[i].Id;
            var _title = this.items[i].title;
            var _assignTo = this.items[i].value_name;
         this.arrayList.push({
        id: _id, title: _title, assignTo: _assignTo, priority: "",bucket:"",planlevel:""
      });
    }
    console.log("assign to value is "+ JSON.stringify(this.arrayList));
        },error=>{
              console.log("the error in ticker derais " + JSON.stringify(error));
        }
    );

  }



   public formatDate(date) {
    var d = new Date(date),
        month = '' + (d.getMonth() + 1),
        day = '' + d.getDate(),
        year = d.getFullYear();

    if (month.length < 2) month = '0' + month;
    if (day.length < 2) day = '0' + day;
    
    return [year, month, day].join('-');
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

   public Log(stuff): void {
    console.log(stuff);
  }

  public event(data: Date): void {
    this.localDate = data;
}

}
