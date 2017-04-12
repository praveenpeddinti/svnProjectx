import { Component } from '@angular/core';
import { ToastController, ViewController, NavParams, LoadingController,PopoverController, ModalController } from 'ionic-angular';
import { Globalservice} from '../../providers/globalservice';
import { Constants } from '../../providers/constants';
import { PopoverPage } from '../popover/popover';
import { Storage } from "@ionic/storage";
import {CustomModalPage } from '../custom-modal/custom-modal';
declare var jQuery: any;

@Component({
    selector: 'page-story-create',
    templateUrl: 'story-create.html'
})
export class StoryCreatePage {
    public itemfield: Array<any>;
    public tasktypes: Array<any>;
     create: {title?: any, description?: any, tasks?: any, planlevel?:any, priority?:any} = {};
    public userName: any = '';
    file: File;
    public templatedataList: Array<{ id: string, title: string, defaultValue: string, assignData: string, readOnly: string, fieldType: string, fieldName: string}>;
    public tasktypeList: Array<{id:string, name:string, IsDefault:string, selected : boolean}>;
    public showEditableFieldOnly = [];
    public previousSelectIndex: any;
    public previousSelectedValue = "";
    public readOnlyDropDownField: boolean = false;
    private submitted: boolean = false;
    constructor(
        public modalController: ModalController,
        public navParams: NavParams,
        private globalService: Globalservice,
        private toastCtrl: ToastController,
        public viewCtrl: ViewController,
        public popoverCtrl: PopoverController,
        public loadingController: LoadingController,
        private storage: Storage, private constants: Constants) {

        this.storage.get('userCredentials').then((value) => {
            this.userName = value.username;
        });

        globalService.newStoryTemplate(this.constants.templateForStoryCreation, this.navParams.get("id")).subscribe(
    (result) => {
        this.itemfield = result.data.story_fields;
        console.log("result newStoryTemplate " + JSON.stringify(this.itemfield));
        this.tasktypes = result.data.task_types;
        console.log("result newStoryTemplate " + JSON.stringify(this.tasktypes));
        this.templatedataList = [];
        this.tasktypeList = [];

            for (let i = 0; i < this.itemfield.length; i++) {
                var _id = this.itemfield[i].Id;
                var _title = this.itemfield[i].Title;
                var _defaultValue = this.itemfield[i].DefaultValue;
                var _assignData = this.itemfield[i].data;
                var _readOnly = this.itemfield[i].readonly;
                var _fieldType = this.itemfield[i].field_type;
                var _fieldName = this.itemfield[i].Field_Name;
                this.templatedataList.push({
                    id: _id, title: _title, defaultValue: _defaultValue, assignData: _assignData, readOnly: _readOnly, fieldType: _fieldType, fieldName: _fieldName
                });
            }

            for (let j = 0; j < this.tasktypes.length; j++) {
                var _id = this.tasktypes[j].Id;
                var _Name = this.tasktypes[j].Name;
                var _IsDefault = this.tasktypes[j].IsDefault;
                var _selected = false;
                if (_IsDefault == "1") {
                    _selected = true;
                }

                this.tasktypeList.push({
                    id: _id, name: _Name, IsDefault: _IsDefault, selected: _selected
                });

            }
                
                }, (error) => {
                    console.log("the error in ticker derais " + JSON.stringify(error));
                }
            );
    }

    ionViewDidLoad() {
        console.log('loaded at every Load the page');
    }

    onStoryCreate(form): void {
        console.log("create task submit button");
        if (form.valid) {
            let loader = this.loadingController.create({ content: "Loading..." });
            loader.present();
            this.create.tasks = [];
            for (let taskTypeItem of this.tasktypeList) {
                if (taskTypeItem.selected == true) {
                    this.create.tasks.push(taskTypeItem.name);
                }
            }
            this.create.description = "<p>Testing</p>\n";

            console.log("the create  " + JSON.stringify(this.create));
            //    this.globalService.createStoryORTask(this.constants.createStory, this.create);
            this.globalService.createStoryORTask(this.constants.createStory, (this.create)).subscribe(
                (result) => {
                    console.log("the create tsk details are " + JSON.stringify(result));
                    loader.dismiss();
                    this.viewCtrl.dismiss();
                    let toast = this.toastCtrl.create({
                        message: 'Successfully created...',
                        duration: 3000,
                        position: 'bottom',
                        cssClass: "toast",
                        dismissOnPageChange: true
                    });
                    toast.present();
                }, (error) => {
                    loader.dismiss();
                    console.log("the story create error are---------> " + JSON.stringify(error));
                    let toast = this.toastCtrl.create({
                        message: 'Successfully not created...',
                        duration: 3000,
                        position: 'bottom',
                        cssClass: "toast",
                        dismissOnPageChange: true
                    });
                    toast.present();
                }
            );
        }
    }

    public selectCancel(index) {
        console.log("selectCancel --- " + index);
        this.showEditableFieldOnly[index] = false;
        setTimeout(() => {
            //document.getElementById("item_"+index).classList.remove("item-select");
        }, 300);
    }
 
 onChange(event: EventTarget) {
        let eventObj: MSInputMethodContext = <MSInputMethodContext> event;
        let target: HTMLInputElement = <HTMLInputElement> eventObj.target;
        let files: FileList = target.files;
        this.file = files[0];
        console.log(this.file);
    }
    openPopover(myEvent) {
        let userCredentials = { username: this.userName };
        let popover = this.popoverCtrl.create(PopoverPage, userCredentials);
        popover.present({
            ev: myEvent
        });
    }
    openOptionsModal(fieldDetails, index) {
        console.log("the model present");

        let optionsModal = this.modalController.create(CustomModalPage, { activeField: fieldDetails, activatedFieldIndex: index, displayList: fieldDetails.assignData });
        optionsModal.onDidDismiss((data) => {
            console.log("the dismiss data " + index + " ----- " + JSON.stringify(data));
            // if(data != null && (data.Name != data.previousValue)){
            if (fieldDetails.fieldName == "planlevel") {
                this.create.planlevel = data.Id;
            } else if (fieldDetails.fieldName == "priority") {
                this.create.priority = data.Id;
            }

            document.getElementById("field.title_field.id_" + index).innerHTML = data.Name;
            // }
            // this.displayFieldvalue = [];
        });
        optionsModal.present();
    }


  }
