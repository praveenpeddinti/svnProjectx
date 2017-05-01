import { Component, NgZone } from '@angular/core';
import { ToastController, NavController, ActionSheetController, Platform, NavParams, LoadingController, PopoverController, ModalController, AlertController } from 'ionic-angular';
import { Globalservice } from '../../providers/globalservice';
import { Constants } from '../../providers/constants';
import { LogoutPage } from '../logout/logout';
import { Storage } from "@ionic/storage";
import { CustomModalPage } from '../custom-modal/custom-modal';
import { Camera, File, FilePath, Transfer } from 'ionic-native';
import { DashboardPage } from '../dashboard/dashboard';
declare var jQuery: any;
declare var cordova: any;

@Component({
    selector: 'page-story-create',
    templateUrl: 'story-create.html'
})
export class StoryCreatePage {
    public itemfield: Array<any>;
    public tasktypes: Array<any>;
    public create: { title?: string, description?: string, default_task?: any, planlevel?: any, priority?: any } = { title: "", description: "", default_task: [], planlevel: "", priority: "" };
    public userName: any = '';
    public templatedataList: Array<{ id: string, title: string, defaultValue: string, assignData: string, readOnly: string, fieldType: string, fieldName: string }>;
    public tasktypeList: Array<{ Id: string, Name: string, IsDefault: string, selected: boolean }>;
    public showEditableFieldOnly = [];
    public displayedClassColorValue = "";
    private lastImage: string = null;
    private progressFile: number;

    constructor(
        public navCtrl: NavController,
        public modalController: ModalController,
        public navParams: NavParams,
        private globalService: Globalservice,
        private toastCtrl: ToastController,
        public platform: Platform,
        public actionSheetCtrl: ActionSheetController,
        public popoverCtrl: PopoverController,
        private alertCtrl: AlertController,
        public loadingController: LoadingController,private ngZone: NgZone,
        private storage: Storage, private constants: Constants) {
        this.storage.get('userCredentials').then((value) => {
            this.userName = value.username;
        });
        globalService.newStoryTemplate(this.constants.templateForStoryCreation, this.navParams.get("id")).subscribe(
            (result) => {
                this.itemfield = result.data.story_fields;
                this.tasktypes = result.data.task_types;
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
                    if (_fieldName == 'planlevel') {
                        this.create.planlevel = _defaultValue;
                    } else if (_fieldName == 'priority') {
                        this.create.priority = _defaultValue;
                        this.displayedClassColorValue = _assignData[2].Name;
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
                        Id: _id, Name: _Name, IsDefault: _IsDefault, selected: _selected
                    });
                }
            }, (error) => {
            }
        );
         this.progressFile = 0;
    }
    ionViewDidLoad() {}
    public onStoryCreate(form): void {
        if (jQuery("#createTitleError").is(":visible") == false && jQuery("#createDescriptionError").is(":visible") == false) {
            let loader = this.loadingController.create({ content: "Loading..." });
            loader.present();
            this.create.default_task = [];
            for (let taskTypeItem of this.tasktypeList) {
                if (taskTypeItem.selected == true) {
                    delete taskTypeItem["selected"];
                    this.create.default_task.push(taskTypeItem);
                }
            }
            if (typeof (this.create.title) == 'string' && this.create.title.length > 0) {
                this.create.title.trim();
            }
            if (this.create.description != null) {
                this.create.description = "<p>" + this.create.description + "</p>";
            }
            this.globalService.createStoryORTask(this.constants.createStory, (this.create)).subscribe(
                (result) => {
                    loader.dismiss().then(() => {
                        let alert = this.alertCtrl.create({
                            title: 'Alert',
                            subTitle: 'Successfully created.',
                            buttons: ['OK']
                        });
                        alert.present();
                        this.navCtrl.setRoot(DashboardPage);
                    }, (error) => {

                    });
                }, (error) => {
                    loader.dismiss();
                }
            );
        }
    }
    public selectCancel(index) {
        this.showEditableFieldOnly[index] = false;
    }
    private presentToast(text) {
        let toast = this.toastCtrl.create({
            message: text,
            duration: 3000,
            position: 'bottom',
            cssClass: "toast",
            dismissOnPageChange: true
        });
        toast.present();
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
        var options = {
            quality: 100,
            sourceType: sourceType,
            destinationType: Camera.DestinationType.FILE_URI,
            encodingType: Camera.EncodingType.JPEG,
            saveToPhotoAlbum: false,
            correctOrientation: true,
        };
        Camera.getPicture(options).then((imagePath) => {
            if (this.platform.is('android') && sourceType === Camera.PictureSourceType.PHOTOLIBRARY) {
                FilePath.resolveNativePath(imagePath).then((filePath) => {
                    let correctPath = filePath.substr(0, filePath.lastIndexOf('/') + 1);
                    let currentName = imagePath.substring(imagePath.lastIndexOf('/') + 1, imagePath.lastIndexOf('?'));
                    this.copyFileToLocalDir(correctPath, currentName, this.createFileName(currentName));
                }, (err) => {});
            } else {
                var currentName = imagePath.substr(imagePath.lastIndexOf('/') + 1);
                var correctPath = imagePath.substr(0, imagePath.lastIndexOf('/') + 1);
                this.copyFileToLocalDir(correctPath, currentName, this.createFileName(currentName));
            }
        }, (err) => {});
    }
    private createFileName(originalName) {
        var d = new Date(),
            n = d.getTime(),
            newFileName = "image" + n;
        return newFileName;
    }
    private copyFileToLocalDir(namePath, currentName, newFileName) {
        File.copyFile(namePath, currentName, cordova.file.dataDirectory, newFileName).then(success => {
            this.lastImage = newFileName;
            this.uploadImage(currentName, newFileName);
        }, error => {});
    }
    public uploadImage(originalname, savedname) {
        var url = this.constants.filesUploading;
        var targetPath = this.pathForImage(this.lastImage);
        var filename = this.lastImage;
        var options = {
            fileKey: "commentFile",
            fileName: filename,
            chunkedMode: false,
            mimeType: "image/jpeg",
            params: { 'filename': filename, 'directory': this.constants.fileUploadsFolder, 'originalname': originalname }
        };
        const fileTransfer = new Transfer();
        fileTransfer.onProgress(this.onProgressFile);
        fileTransfer.upload(targetPath, url, options).then((data) => {
            this.uploadedInserver(data);    
             this.progressFile = 0;
             document.getElementById('progressFileUploadFile').innerHTML = "";
        }, (err) => {
            this.presentToast('Unable to upload the image.');
        });
    }
    public pathForImage(img) {
        if (img === null) {
            return '';
        } else {
            return cordova.file.dataDirectory + img;
        }
    }
    public onProgressFile = (progressEvent: ProgressEvent) : void => {
        this.ngZone.run(() => {
            if (progressEvent.lengthComputable) {
                let progress = Math.floor(progressEvent.loaded / progressEvent.total * 100);
                this.progressFile = progress;
                document.getElementById('progressFileUploadFile').innerHTML = progress + "% Loading...";
            } else {
                if (document.getElementById('progressFileUploadFile').innerHTML == "") {
                    document.getElementById('progressFileUploadFile').innerHTML = "Loading";
                } else {
                    document.getElementById('progressFileUploadFile').innerHTML += ".";
                }
            }
        });
    }
    public uploadedInserver(dataUploaded) {
        var serverResponse = JSON.parse(dataUploaded.response);
        if (serverResponse['status'] == '1') {
            var uploadedFileExtension = (serverResponse['originalname']).split('.').pop();
            if (uploadedFileExtension == "png" || uploadedFileExtension == "jpg" || uploadedFileExtension == "jpeg" || uploadedFileExtension == "gif") {
                this.create.description = this.create.description + "[[image:" + serverResponse['path'] + "|" + serverResponse['originalname'] + "]] ";
            }
        } else {
            this.presentToast('Unable to upload the image.');
        }
    }
    public openPopover(myEvent) {
        let userCredentials = { username: this.userName };
        let popover = this.popoverCtrl.create(LogoutPage, userCredentials);
        popover.present({
            ev: myEvent
        });
    }
    public openOptionsModal(fieldDetails, index) {
        let optionsModal = this.modalController.create(CustomModalPage, { activeField: fieldDetails, activatedFieldIndex: index, displayList: fieldDetails.assignData });
        optionsModal.onDidDismiss((data) => {
            if (data != null && Object.keys(data).length > 0) {
                if (fieldDetails.fieldName == "planlevel") {
                    this.create.planlevel = data.Id;
                } else if (fieldDetails.fieldName == "priority") {
                    this.create.priority = data.Id;
                    this.displayedClassColorValue = data.Name;
                }
                jQuery("#field_title_" + index + " div").text(data.Name);
            }
        });
        optionsModal.present();
    }
}