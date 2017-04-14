import { Component } from '@angular/core';
import { ToastController, ViewController, ActionSheetController,Platform , NavParams, LoadingController,PopoverController, ModalController } from 'ionic-angular';
import { Globalservice} from '../../providers/globalservice';
import { Constants } from '../../providers/constants';
import { PopoverPage } from '../popover/popover';
import { Storage } from "@ionic/storage";
import {CustomModalPage } from '../custom-modal/custom-modal';
import {Camera, File, Transfer, FilePath} from 'ionic-native';
declare var jQuery: any;
declare var cordova: any;

@Component({
    selector: 'page-story-create',
    templateUrl: 'story-create.html'
})
export class StoryCreatePage {
    Url: string;
    public itemfield: Array<any>;
    public tasktypes: Array<any>;
     create: {title?: any, description?: any, default_task?: any, planlevel?:any, priority?:any} = {};
    public userName: any = '';
    file: File;
    public templatedataList: Array<{ id: string, title: string, defaultValue: string, assignData: string, readOnly: string, fieldType: string, fieldName: string}>;
    public tasktypeList: Array<{id:string, name:string, IsDefault:string, selected : boolean}>;
    public showEditableFieldOnly = [];
    public previousSelectIndex: any;
    public previousSelectedValue = "";
    public readOnlyDropDownField: boolean = false;
    private submitted: boolean = false;
    lastImage: string = null;
    ImageLoc
    
    constructor(
        public modalController: ModalController,
        public navParams: NavParams,
        private globalService: Globalservice,
        private toastCtrl: ToastController,
        public viewCtrl: ViewController,
        public platform: Platform,
        public actionSheetCtrl: ActionSheetController,
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
                if(this.itemfield[i].Field_Name == 'planlevel'){
                    this.create.planlevel = this.itemfield[i].DefaultValue;
                } else if(this.itemfield[i].Field_Name == 'priority') {
                    this.create.priority = this.itemfield[i].DefaultValue;
                }
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
            this.create.default_task = [];

            for (let taskTypeItem of this.tasktypeList) {
                if (taskTypeItem.selected == true) {
                    delete taskTypeItem["selected"];
                    this.create.default_task.push(taskTypeItem);
                }
            }
            this.create.description = "<p>"+this.create.description+"</p>";

            console.log("the create  " + JSON.stringify(this.create));
            //    this.globalService.createStoryORTask(this.constants.createStory, this.create);
            this.globalService.createStoryORTask(this.constants.createStory, (this.create)).subscribe(
                (result) => {
                    console.log("the create tsk details are " + JSON.stringify(result));
                    loader.dismiss();
                    this.viewCtrl.dismiss();
                    alert("Successfully created...");
                    // let toast = this.toastCtrl.create({
                    //     message: 'Successfully created...',
                    //     duration: 3000,
                    //     position: 'bottom',
                    //     cssClass: "toast",
                    //     dismissOnPageChange: true
                    // });
                    // toast.present();
                }, (error) => {
                    loader.dismiss();
                    
                    console.log("the story create error are---------> " + JSON.stringify(error));
                    alert("Unable to created the ticket...");
                     this.viewCtrl.dismiss();
                    // let toast = this.toastCtrl.create({
                    //     message: 'Unable created the ticket...',
                    //     duration: 3000,
                    //     position: 'bottom',
                    //     cssClass: "toast",
                    //     dismissOnPageChange: true
                    // });
                   // toast.present();
                   
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
     public presentActionSheet() {
        let actionSheet = this.actionSheetCtrl.create({
            title: 'Select Image Source',
            buttons: [
                {
                    text: 'Upload from Library',
                    handler: () => {
                        this.takePicture(Camera.PictureSourceType.PHOTOLIBRARY);
                    }
                },
                {
                    text: 'Use Camera',
                    handler: () => {
                        this.takePicture(Camera.PictureSourceType.CAMERA);
                    }
                },
                {
                    text: 'Cancel',
                    role: 'cancel'
                }
            ]
        });
        actionSheet.present();
    }

        public takePicture(sourceType) {
        // Create options for the Camera Dialog
        var options = {
            quality: 100,
            sourceType: sourceType,
            destinationType: Camera.DestinationType.FILE_URI,
            saveToPhotoAlbum: false,
            correctOrientation: true
        };

        // Get the data of an image
        Camera.getPicture(options).then((imagePath) => {
           
            this.ImageLoc = imagePath;
            alert(this.ImageLoc);
           // this.Url = this.ImageLoc;
           // console.log("URL covert -----" + this.Url);
            //this.base64Image = 'data:image/jpeg;base64,'+imagePath;
           // var image: string = <string> <any> imagePath;
           // alert(image);
        }, (err) => {
            this.presentToast('Error while selecting image.');
        });
    }

    private createFileName() {
        var d = new Date(),
            n = d.getTime(),
            newFileName = n + ".jpg";
        return newFileName;
    }

    private presentToast(text) {
        let toast = this.toastCtrl.create({
            message: text,
            duration: 3000,
            position: 'top'
        });
        toast.present();
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
            if(data != null){
                if (fieldDetails.fieldName == "planlevel") {
                    this.create.planlevel = data.Id;
                } else if (fieldDetails.fieldName == "priority") {
                    this.create.priority = data.Id;
                }

            // document.getElementById("field_title_" + index).innerHTML = data.Name;
            jQuery("#field_title_"+index+ " div").text(data.Name);
            }
            // this.displayFieldvalue = [];
        });
        optionsModal.present();
    }


  }
