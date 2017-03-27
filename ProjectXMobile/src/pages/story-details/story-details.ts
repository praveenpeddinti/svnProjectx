import { Component } from '@angular/core';
import { ToastController } from 'ionic-angular';
import { NavController, NavParams, MenuController, LoadingController, PopoverController, ViewController } from 'ionic-angular';

import { Globalservice } from '../../providers/globalservice';
import { Constants } from '../../providers/constants';
import { PopoverPage } from '../popover/popover';
import { Storage } from "@ionic/storage";
declare var jQuery: any;
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
    public arrayList: Array<{ id: string, title: string, assignTo: string, readOnly: string, fieldType: string, fieldName: string, ticketId: any }>;
    public displayFieldvalue = [];
    public showEditableFieldOnly = [];
    public readOnlyDropDownField: boolean = false;

    public selectedValue = "";
    public previousSelectedValue = "";

    public previousSelectIndex: any;
    public enableDataPicker = [];

    public enableTextField = [];
    public enableTextArea = [];

    public titleAfterEdit: string = "";
    public enableEdatable: boolean = false;
    public taskDetails = { ticketId: "", title: "", description: "", type: "" };
    public isBusy: boolean = false;
    public options = "options";
    public localDate: any = new Date();
    // public maxDate: Date = new Date(new Date().setDate(new Date().getDate() + 30));
    public minDate: any = new Date();
    public myDate: string = "2017-02-25";
    public userName: any = '';

    public ckeditorContent = "";
    public config = {
        toolbar: [
            ['Heading 1', '-', 'Bold', '-', 'Italic', '-', 'Underline', 'Link', 'NumberedList', 'BulletedList']
        ], removePlugins: 'elementspath,magicline', resize_enabled: true
    };

    constructor(menu: MenuController,
        private toastCtrl: ToastController,
        public globalService: Globalservice,
        private constants: Constants,
        public navCtrl: NavController,
        public navParams: NavParams,
        public loadingController: LoadingController,
        public popoverCtrl: PopoverController,
        private storage: Storage, public viewCtrl: ViewController, ) {
        //      menu.swipeEnable(false);
        //this.minDate = this.formatDate(new Date());
        this.localDate = new Date().toISOString();
        this.minDate = new Date().toISOString();

        let loader = this.loadingController.create({ content: "Loading..." });
        loader.present();

        this.storage.get('userCredentials').then((value) => {
            this.userName = value.username;
        });
        globalService.getTicketDetailsById(this.constants.taskDetailsById, this.navParams.get("id")).subscribe(
            result => {
                this.taskDetails.ticketId = result.data.TicketId;
                this.taskDetails.title = result.data.Title;
                this.taskDetails.description = result.data.Description;
                this.taskDetails.type = result.data.StoryType.Name;
                this.titleAfterEdit = result.data.Title;

                this.items = result.data.Fields;
                //console.log("the count value is from Appcomponent" + this.items.length);
                this.arrayList = [];
                for (let i = 0; i < this.items.length; i++) {
                    var _id = this.items[i].Id;
                    var _title = this.items[i].title;
                    var _assignTo;
                    if ((this.items[i].field_type == "Text") || (this.items[i].field_type == "TextArea")) {
                        if (this.items[i].value == "") {
                            _assignTo = "--";
                        } else {
                            _assignTo = this.items[i].value;
                        }
                    } else if(this.items[i].field_type == "Date"){
//                        readable_value
                        if(this.items[i].readable_value == ""){
                            _assignTo = "--";
                        } else {
                            _assignTo = this.items[i].readable_value;
                        }
                    } 
                    else if(this.items[i].field_type == "DateTime"){
//                        readable_value
                        if(this.items[i].readable_value == ""){
                            _assignTo = "--";
                        } else {
                            _assignTo = this.items[i].readable_value;
                        }
                    }
                    else { 
                        if (this.items[i].value_name == "") {
                            _assignTo = "--";
                        } else {
                            _assignTo = this.items[i].value_name;
                        }
                    }
                    var _readOnly = this.items[i].readonly;
                    var _fieldType = this.items[i].field_type;
                    var _fieldName = this.items[i].field_name;
                    this.arrayList.push({
                        id: _id, title: _title, assignTo: _assignTo, readOnly: _readOnly, fieldType: _fieldType, fieldName: _fieldName, ticketId: this.taskDetails.ticketId
                    });
                }
                //console.log("the field arrayList " + JSON.stringify(this.arrayList));
                loader.dismiss();
            }, error => {
                loader.dismiss();
                console.log("the error in ticker derais " + JSON.stringify(error));
            }
        );

    }

   public dateChange(event, index, fieldDetails) {
       
       console.log("the date changed " + this.localDate  + " ------- " + JSON.stringify(this.event));
       console.log("changed date --- "+new Date(this.localDate).toLocaleString());

       this.globalService.leftFieldUpdateInline(this.constants.leftFieldUpdateInline, this.localDate, fieldDetails).subscribe( 
           (result) => {
               setTimeout(() => {
                    document.getElementById("field.title_field.id_" + index).innerHTML = this.localDate;
                //    document.getElementById("item_" + index).classList.remove("item-select");
               }, 300);
           },
           (error) => {
               console.log("the error --- " + JSON.stringify(error));
                let toast = this.toastCtrl.create({
                   message: 'Some thing Un-successfull',
                   duration: 3000,
                   position: 'bottom',
                   cssClass: "toast",
                   dismissOnPageChange: true
                 });
                 toast.present();
           });


       setTimeout(() => {
           document.getElementById("field.title_field.id_" + index).style.display = 'none';
           //document.getElementById("item_" + index).classList.add("item-select");
       }, 300);
   }


    public formatDate(date) {
        var d = new Date(date),
            month = '' + (d.getMonth() + 1),
            day = '' + d.getDate(),
            year = d.getFullYear();

        if (month.length < 2) month = '0' + month;
        if (day.length < 2) day = '0' + day;
        console.log("the date " + [month, day, year].join('-') );
        return [month, day, year].join('-');
    }

    ionViewDidLoad() {
        //console.log('ionViewDidLoad StoryDetailsPage');
    }
    ionViewDidEnter() {
        console.log("the ionViewDidEnter --- " + jQuery('#description').height());
        if (jQuery('#description').height() > 200) {
            jQuery('#description').css("height", "200px");
            jQuery('.show-morediv').show();
            jQuery('#show').show();
        }
    }
    ionViewWillEnter() {
        console.log("the ionViewWillEnter --- " + jQuery('#description').height());
        if (jQuery('#description').height() > 200) {
            jQuery('#description').css("height", "200px");
            jQuery('.show-morediv').show();
            jQuery('#show').show();
        }
    }

     public selectCancel(index) {
        console.log("selectCancel --- " + index);
        this.showEditableFieldOnly[index] = false;
        setTimeout(() => {
            //document.getElementById("item_"+index).classList.remove("item-select");
        }, 300);
    }

    public getFieldValues(fieldDetails, index) {

        this.selectCancel(this.previousSelectIndex);
        if((document.getElementById("field.title_field.id_" + index).innerHTML) != "--"){
            this.previousSelectedValue = (document.getElementById("field.title_field.id_" + index).innerHTML);
        }

        if ((fieldDetails.readOnly == 0) && ((fieldDetails.fieldType == "List") || (fieldDetails.fieldType == "Team List") || (fieldDetails.fieldType == "Bucket"))) {
            this.readOnlyDropDownField = true;
            this.showEditableFieldOnly[index] = true;
            this.previousSelectIndex = index;
            this.globalService.getFieldItemById(this.constants.fieldDetailsById, fieldDetails).subscribe(
                (result) => {
                    this.displayFieldvalue = result.getFieldDetails;
                },
                (error) => {
                    console.log("the fields error --- " + error);
                });

        } else if ((fieldDetails.readOnly == 0) && (fieldDetails.fieldType == "Date")) {
            document.getElementById("field.title_field.id_" + index).style.display = 'none';
            //@ViewChild("field.title_field.id_" + index) datePicker;
            //jQuery("#field.title_field.id_" + index).open();
            this.enableDataPicker[index] = true;

        } else if ((fieldDetails.readOnly == 0) && ((fieldDetails.fieldType == "TextArea") || (fieldDetails.fieldType == "Text"))) {
            console.log("TextArea was enabled " + fieldDetails.fieldType);

            if (fieldDetails.fieldType == "TextArea") {
                this.enableTextArea[index] = true;
                document.getElementById("field.title_field.id_" + index).style.display = 'none';
            }
            else if (fieldDetails.fieldType == "Text") {
                this.enableTextField[index] = true;
                document.getElementById("field.title_field.id_" + index).style.display = 'none';
            }
        }

    }

    public changeOption(event, index, fieldDetails) {
        this.readOnlyDropDownField = false;
        this.showEditableFieldOnly[index] = false;

        //console.log("the displayfieldvalues " + JSON.stringify(this.displayFieldvalue) + "------- " + event + " &&&&&&&&& " + index)

        this.globalService.leftFieldUpdateInline(this.constants.leftFieldUpdateInline, event, fieldDetails).subscribe( 
            (result) => {
                setTimeout(() => {
                    document.getElementById("field.title_field.id_" + index).innerHTML = this.displayFieldvalue[event-1].Name;
                    document.getElementById("item_" + index).classList.remove("item-select");
                }, 300);
            },
            (error) => {
                console.log("the error --- " + JSON.stringify(error));
                 let toast = this.toastCtrl.create({
                    message: 'Some thing Un-successfull',
                    duration: 3000,
                    position: 'bottom',
                    cssClass: "toast",
                    dismissOnPageChange: true
                  });
                  toast.present();
            });

        setTimeout(() => {
            // document.getElementById("field.title_field.id_" + index).innerHTML = this.displayFieldvalue[event-1].Name;
            // document.getElementById("item_" + index).classList.remove("item-select");
        }, 300);
    }
    
    public inputBlurMethod(event, index, fieldDetails){
        
        console.log("inside the input blur method " + event.target.value);
        
       this.globalService.leftFieldUpdateInline(this.constants.leftFieldUpdateInline, (event.target.value), fieldDetails).subscribe( 
            (result) => {
                setTimeout(() => {
                    // this.enableTextField[index] = false;
                    // this.showEditableFieldOnly[index] = true;
                    document.getElementById("field.title_field.id_" + index).innerHTML = (event.target.value);
                }, 300);
            },
            (error) => {
                console.log("the error --- " + JSON.stringify(error));
                 let toast = this.toastCtrl.create({
                    message: 'Some thing Un-successfull',
                    duration: 3000,
                    position: 'bottom',
                    cssClass: "toast",
                    dismissOnPageChange: true
                  });
                  toast.present();
            });

        setTimeout(() => {
            // document.getElementById("field.title_field.id_" + index).innerHTML = this.displayFieldvalue[event-1].Name;
            // document.getElementById("item_" + index).classList.remove("item-select");
        }, 300);
        
    }

    openPopover(myEvent) {
        let userCredentials = { username: this.userName };
        let popover = this.popoverCtrl.create(PopoverPage, userCredentials);
        popover.present({
            ev: myEvent
        });
    }

    public titleEdit(event) {
        //this.enableEdatable = true;
    }

    public updateTitleSubmit() {
        this.enableEdatable = false;
        this.taskDetails.title = this.titleAfterEdit;
    }
    public updateTitleCancel() {
        this.enableEdatable = false;
        this.titleAfterEdit = this.taskDetails.title;
    }

    public expandDescription() {
        jQuery('#description').css('height', 'auto');
        jQuery('#show').hide();
        jQuery('#hide').show();
    }
    public collapseDescription() {
        jQuery('#hide').hide();
        jQuery('#show').show();
        jQuery('#description').css("height", "200px");
        jQuery('#description').css("overflow", "hidden");
    }


    public isColorChange(fieldDetails) {

        if (fieldDetails.title == "Created on") {
            return true;
        }

        else if (fieldDetails.title == "Reported by") {
            return true;
        }

        else if (fieldDetails.title == "Plan Level") {
            return true;
        }
        else {
            return false;
        }

    }

}
