import { Component } from '@angular/core';
import { ToastController,NavController, ViewController, ActionSheetController,Platform , NavParams, LoadingController,PopoverController, ModalController } from 'ionic-angular';
import { Globalservice} from '../../providers/globalservice';
import { Constants } from '../../providers/constants';
import { PopoverPage } from '../popover/popover';
import { Storage } from "@ionic/storage";
import { CustomModalPage } from '../custom-modal/custom-modal';
import { Camera, File,FilePath,Transfer } from 'ionic-native';
import { DashboardPage } from '../dashboard/dashboard';
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
    public create: {title?: string, description?: string, default_task?: any, planlevel?:any, priority?:any} = {title:"", description:"", default_task:[], planlevel:"", priority:""};
    public userName: any = '';
    file: File;
    public templatedataList: Array<{ id: string, title: string, defaultValue: string, assignData: string, readOnly: string, fieldType: string, fieldName: string}>;
    public tasktypeList: Array<{Id:string, Name:string, IsDefault:string, selected : boolean}>;
    public showEditableFieldOnly = [];
    public previousSelectIndex: any;
    public previousSelectedValue = "";
    public readOnlyDropDownField: boolean = false;
    private submitted: boolean = false;
    public displayedClassColorValue="";
    lastImage: string = null;
    
    constructor(
        public navCtrl: NavController,
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
                if(_fieldName == 'planlevel'){
                    this.create.planlevel = _defaultValue;
                } else if(_fieldName == 'priority') {
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
                    console.log("the error in ticker derais " + JSON.stringify(error));
                }
            );
    }

    ionViewDidLoad() {
        console.log('loaded at every Load the page');
    }
  
    onStoryCreate(form): void {
        console.log(" in onStoryCreate");
      if (jQuery("#createTitleError").is(":visible") == false && jQuery("#createDescriptionError").is(":visible") == false ) {
            let loader = this.loadingController.create({ content: "Loading..." });
            loader.present();
            this.create.default_task = [];

            //if((this.create.title) != undefined && (this.create.description) != undefined) {
                for (let taskTypeItem of this.tasktypeList) {
                    if (taskTypeItem.selected == true) {
                        delete taskTypeItem["selected"];
                        this.create.default_task.push(taskTypeItem);
                    }
                }
           // }
            if(typeof(this.create.title) == 'string' && this.create.title.length > 0){
                this.create.title.trim();
            } 
            
            if(this.create.description != null){
                this.create.description = "<p>"+this.create.description+"</p>";
            }

            console.log("the create  " + JSON.stringify(this.create));
            //    this.globalService.createStoryORTask(this.constants.createStory, this.create);
            
            this.globalService.createStoryORTask(this.constants.createStory, (this.create)).subscribe(
                (result) => {
                    console.log("the create tsk details are " + JSON.stringify(result));
                    // loader.dismiss();
                    loader.dismiss().then( () => {
                        alert("Successfully created...");
                        //this.viewCtrl.dismiss();
                         this.navCtrl.setRoot(DashboardPage);
                    }, (error) => {
                        console.log("error while dismissing the loader");
                    });
                    
                    
                }, (error) => {
                    loader.dismiss();
                    
                    console.log("the story create error are---------> " + JSON.stringify(error));
                  // alert("Unable to created the ticket...");
                   
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
        // Destination could be : DATA_URL or FILE_URI
        var options = {
            quality: 100,
            sourceType: sourceType,
            destinationType: Camera.DestinationType.FILE_URI,
            encodingType: Camera.EncodingType.JPEG,
            saveToPhotoAlbum: false,
            correctOrientation: true,
            // mediaType: Camera.MediaType.ALLMEDIA
        };

        // Get the data of an image
        Camera.getPicture(options).then((imagePath) => {
            // console.log('imagePath'+imagePath);
            if (this.platform.is('android') && sourceType === Camera.PictureSourceType.PHOTOLIBRARY) {
                FilePath.resolveNativePath(imagePath).then((filePath) => {
                    let correctPath = filePath.substr(0, filePath.lastIndexOf('/') + 1);
                    let currentName = imagePath.substring(imagePath.lastIndexOf('/') + 1, imagePath.lastIndexOf('?'));
                    this.copyFileToLocalDir(correctPath, currentName, this.createFileName(currentName));
                }, (err) => {
                    console.log('Error while resolveNativePath.');
                });
            } else {
                var currentName = imagePath.substr(imagePath.lastIndexOf('/') + 1);
                var correctPath = imagePath.substr(0, imagePath.lastIndexOf('/') + 1);
                this.copyFileToLocalDir(correctPath, currentName, this.createFileName(currentName));
            }
        }, (err) => {
            this.presentToast('Error while selecting image.');
        });
    }

 private createFileName(originalName) {
        // console.log('createFileName');
        var d = new Date(),
        n = d.getTime(),
        // newFileName =  n + ".jpg";
        newFileName =  "image"+n;
        return newFileName;
    }
       private copyFileToLocalDir(namePath, currentName, newFileName) {
        // console.log('copyFileToLocalDir');
        File.copyFile(namePath, currentName, cordova.file.dataDirectory, newFileName).then(success => {
            this.lastImage = newFileName;
            this.uploadImage(currentName, newFileName);
        }, error => {
            this.presentToast('Error while storing file.');
        });
    }

       public uploadImage(originalname, savedname) {
        // console.log('uploadImage');
        // Destination URL
        var url = this.constants.filesUploading;
        // File for Upload
        var targetPath = this.pathForImage(this.lastImage);
        // File name only
        var filename = this.lastImage;
        var options = {
            fileKey: "commentFile",
            fileName: filename,
            chunkedMode: false,
            mimeType: "image/jpeg",
            params : {'filename': filename,'directory':this.constants.fileUploadsFolder,'originalname': originalname}
        };
        const fileTransfer = new Transfer();
        // Use the FileTransfer to upload the image
        fileTransfer.upload(targetPath, url, options).then((data) => {
            // console.log('data'+JSON.stringify(data));
            this.uploadedInserver(data);
        }, (err) => {
            console.log('Error while uploading file.'+ JSON.stringify(err));
        });
    }
    public pathForImage(img) {
        if (img === null) {
            return '';
        } else {
            return cordova.file.dataDirectory + img;
        }
    }
        public uploadedInserver(dataUploaded){
        // console.log('uploadedInserver');
        var serverResponse = JSON.parse(dataUploaded.response);
        if (serverResponse['status'] == '1') {
            var uploadedFileExtension = (serverResponse['originalname']).split('.').pop();
            if (uploadedFileExtension == "png" || uploadedFileExtension == "jpg" || uploadedFileExtension == "jpeg" || uploadedFileExtension == "gif") {
                this.create.description = this.create.description + "[[image:" +serverResponse['path'] + "|" + serverResponse['originalname'] + "]] ";
            }
            console.log('Image succesfully uploaded.');
            // this.presentToast('Image succesfully uploaded.');
        }else{
            console.log('Error while uploading file.');
            // this.presentToast('Error while uploading file.');
        }
    }

    //     public takePicture(sourceType) {
    //     // Create options for the Camera Dialog
    //     var options = {
    //         quality: 100,
    //         sourceType: sourceType,
    //         destinationType: Camera.DestinationType.FILE_URI,
    //         saveToPhotoAlbum: false,
    //         correctOrientation: true
    //     };

    //     // Get the data of an image
    //     Camera.getPicture(options).then((imagePath) => {
           
    //         //this.ImageLoc = imagePath;
    //         this.create.description = "<p>" + this.create.description + imagePath +"</p>";
    //         //this.base64Image = 'data:image/jpeg;base64,'+imagePath;
    //     }, (err) => {
    //         this.presentToast('Error while selecting image.');
    //     });
    // }

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
            if(data != null && Object.keys(data).length > 0){
                if (fieldDetails.fieldName == "planlevel") {
                    this.create.planlevel = data.Id;
                } else if (fieldDetails.fieldName == "priority") {
                    this.create.priority = data.Id;
                    this.displayedClassColorValue = data.Name;
                }

                // document.getElementById("field_title_" + index).innerHTML = data.Name;
                jQuery("#field_title_"+index+ " div").text(data.Name);
             } 
        });
        optionsModal.present();
    }

  }
