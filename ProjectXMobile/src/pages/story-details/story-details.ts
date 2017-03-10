import {Component} from '@angular/core';
import {NavController, NavParams, MenuController} from 'ionic-angular';

import {Globalservice} from '../../providers/globalservice';
import {Constants} from '../../providers/constants';
import {StoryDetailActivitiesPage} from '../story-detail-activities/story-detail-activities';

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
    public arrayList: Array<{id: string, title: string, assignTo: string, readOnly: string, priority: string, bucket: string, planlevel: string, ticketId: any}>;
    public displayFieldvalue ;
    public showEditableFieldOnly = [];
    public readOnlyDropDownField:boolean = false;
    public clickedDivRef;
public selectedValue = "";
    public tab1Root: any = StoryDetailActivitiesPage;

    public taskDetails = {ticketId: "", title: "", description: ""};
    public isBusy: boolean = false;
    public options = "options";
    public localDate: Date = new Date();
    // public maxDate: Date = new Date(new Date().setDate(new Date().getDate() + 30));
    public minDate: any = new Date();
    public myDate: string = "2017-02-25";

    public ckeditorContent = "";
    public config = {
        toolbar: [
            ['Heading 1', '-', 'Bold', '-', 'Italic', '-', 'Underline', 'Link', 'NumberedList', 'BulletedList']
        ], removePlugins: 'elementspath,magicline', resize_enabled: true
    };

    constructor(menu: MenuController, public globalService: Globalservice, private constants: Constants, public navCtrl: NavController, public navParams: NavParams) {
        //      menu.swipeEnable(false);
        this.minDate = this.formatDate(new Date());

        globalService.getTicketDetailsById(this.constants.taskDetailsById, this.navParams.get("id")).subscribe(
            result => {
                this.taskDetails.ticketId = result.data.TicketId;
                this.taskDetails.title = result.data.Title;
                this.taskDetails.description = result.data.Description;

                this.items = result.data.Fields;
                //console.log("the count value is from Appcomponent" + this.items.length);
                this.arrayList = [];
                for (let i = 0; i < this.items.length; i++) {
                    var _id = this.items[i].Id;
                    var _title = this.items[i].title;
                    var _assignTo = this.items[i].value_name;
                    var _readOnly = this.items[i].readonly;
                    this.arrayList.push({
                        id: _id, title: _title, assignTo: _assignTo, readOnly: _readOnly, priority: "", bucket: "", planlevel: "", ticketId: this.taskDetails.ticketId
                    });
                }
                //console.log("the field arrayList " + JSON.stringify(this.arrayList));
            }, error => {
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
        //console.log('ionViewDidLoad StoryDetailsPage');
    }

    public changeOption(event, index) {
        console.log("the options --- " + this.options + " -------------");
        console.log("the change " + JSON.stringify(event));
        this.readOnlyDropDownField = false;
        this.showEditableFieldOnly[index] = false;
        this.selectedValue = event;
        console.log("the div id " + this.clickedDivRef);
        (<HTMLInputElement>document.getElementById("Bucket_3")).value = JSON.stringify(event);
        
    }

    public getFieldValues(fieldDetails, index) { // 1 --> non editable 
        console.log("the field clicked - " + JSON.stringify(fieldDetails));

        if(fieldDetails.readOnly == 0){
            this.readOnlyDropDownField = true;
            this.showEditableFieldOnly[index] = true;
            this.clickedDivRef = fieldDetails.id;
            this.globalService.getFieldItemById(this.constants.fieldDetailsById, fieldDetails).subscribe(
            (result) => {
                //console.log("the detials field result ---- " + JSON.stringify(result));
                this.displayFieldvalue = result.getFieldDetails;
                //this.displayFieldvalue = [{"Id":"1","Name":"Backlog"},{"Id":"2","Name":"Sprint1"},{"Id":"3","Name":"Sprint2"},{"Id":"4","Name":"Sprint3"}];
                console.log("the before loop " + JSON.stringify(this.displayFieldvalue));
                for(var i = 0; i<result.getFieldDetails.length; i++){
                    console.log("the for loop data " + JSON.stringify(result.getFieldDetails[i]));
                }
            },
            (error) => {
                console.log("the fields error --- " + error);
            });
        }
    }

}
