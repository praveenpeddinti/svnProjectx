import { Component, ViewChild } from '@angular/core';
import {DatePipe} from '@angular/common';
import { ToastController, Content, Platform, App } from 'ionic-angular';
// Plugins
import { NavController, ModalController, NavParams, MenuController, LoadingController, PopoverController, ViewController, ActionSheetController } from 'ionic-angular';
// Plugins ended
import { Globalservice } from '../../providers/globalservice';
import { Constants } from '../../providers/constants';
import { PopoverPage } from '../popover/popover';
import { Storage } from "@ionic/storage";
import { CustomModalPage } from '../custom-modal/custom-modal';
// Plugins
import {Camera, File, Transfer, FilePath} from 'ionic-native';
declare var cordova: any;
// Plugins ended
declare var jQuery: any;
/*
  Generated class for the StoryDetails page.

  See http://ionicframework.com/docs/v2/components/#navigation for more info on
  Ionic pages and navigation.
*/
@Component({
    selector: 'page-story-details',
    templateUrl: 'story-details.html',
    providers: [DatePipe]
})
export class StoryDetailsPage {

    public items: Array<any>;
    public arrayList: Array<{ id: string, title: string, assignTo: string, readOnly: string, fieldType: string, fieldName: string, ticketId: any, readableValue: any }>;
    public displayFieldvalue = [];
    public showEditableFieldOnly = [];
    public readOnlyDropDownField: boolean = false;
    // Ticket #91
    // Activities
    public itemsInActivities: Array<any>;
    // Comments
    private replyToComment = -1;
    private replying = false;
    private commentAreaColor = "";
    private editTheComment = [];
    public commentDesc = "";
    // File upload
    // public filesToUpload: Array<File>;
    public filesToUpload: Array<any>;
    public fileUploadStatus:boolean = false;
    private ticketEditableDesc="";
    private openCommentMenuList = [];
    // Ticket #91 ended
    // Plugins
    private lastImage: string = null;
    // Plugins ended
    public selectedValue = "";
    public previousSelectedValue = "";

    public previousSelectIndex: any;
    public enableDataPicker = [];

    public enableTextField = [];
    public enableTextArea = [];

    public titleAfterEdit: string = "";
    public enableEdatable: boolean = false;
    public taskDetails = { ticketId: "", title: "", description: "", type: "", workflowType: "" };
    public isBusy: boolean = false;
    public options = "options";
    public localDate: any = new Date();
    // public maxDate: Date = new Date(new Date().setDate(new Date().getDate() + 30));
    public minDate: any = new Date();
    public userName: any = '';
    public static optionsModal;
    public static isMenuOpen: boolean = false;
    public static menuControler;

    public textFieldValue = "";
    public textAreaValue = "";
    public textDate = "";
    public displayedClassColorValue = "";

    @ViewChild(Content) content: Content;

    public ckeditorContent = "";
    public config = {
        toolbar: [
            ['Heading 1', '-', 'Bold', '-', 'Italic', '-', 'Underline', 'Link', 'NumberedList', 'BulletedList']
        ], removePlugins: 'elementspath,magicline', resize_enabled: true
    };

    constructor(menu: MenuController,
        private app: App,
        private modalController: ModalController,
        private toastCtrl: ToastController,
        public globalService: Globalservice,
        private constants: Constants,
        public navCtrl: NavController,
        public navParams: NavParams,
        public loadingController: LoadingController,
        public popoverCtrl: PopoverController,
        // Plugins
        public actionSheetCtrl: ActionSheetController,
        public platform: Platform,
        // Plugins ended
        private storage: Storage, 
        public viewCtrl: ViewController, 
        private datePipe: DatePipe ) {
        StoryDetailsPage.menuControler = menu;
        
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
                this.taskDetails.workflowType = result.data.WorkflowType;
                this.titleAfterEdit = result.data.Title;

                this.items = result.data.Fields;
                //console.log("the count value is from Appcomponent" + this.items.length);
                this.arrayList = [];
                for (let i = 0; i < this.items.length; i++) {
                    var _id = this.items[i].Id;
                    var _title = this.items[i].title;
                    var _assignTo;
                    if (this.items[i].field_type == "Text") {
                        if (this.items[i].value == "") {
                            _assignTo = "--";
                        } else {
                            _assignTo = this.items[i].value;
                            if (this.items[i].field_name == "estimatedpoints") {
                                this.textFieldValue = this.items[i].value;
                            }
                        }
                    } else if (this.items[i].field_type == "TextArea") {
                        if (this.items[i].value == "") {
                            _assignTo = "--";
                        } else {
                            _assignTo = this.items[i].value;
                            this.textAreaValue = this.items[i].value;
                        }
                    } else if(this.items[i].field_type == "Date"){
//                        readable_value
                       if(this.items[i].readable_value == ""){
                             _assignTo = "--"; 
                            this.localDate = new Date().toISOString();
                          } else {
                            _assignTo = this.items[i].readable_value;
                            var date = new Date(this.items[i].readable_value);
                            date.setTime( date.getTime() + date.getTimezoneOffset()*-60*1000 );
                            this.localDate = new Date(date.setDate(date.getDate() )).toISOString();
                        }
                    }
                    else if (this.items[i].field_type == "DateTime") {
                        //                        readable_value
                        if (this.items[i].readable_value == "") {
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
                    if (_fieldName == 'priority') {
                        this.displayedClassColorValue = _assignTo;
                    }
                    var _readableValue = this.items[i].readable_value;

                    this.arrayList.push({
                        id: _id, title: _title, assignTo: _assignTo, readOnly: _readOnly, fieldType: _fieldType, fieldName: _fieldName, ticketId: this.taskDetails.ticketId, readableValue: _readableValue
                    });
                }
                //console.log("the field arrayList " + JSON.stringify(this.arrayList));
                loader.dismiss();
            }, error => {
                loader.dismiss();
                console.log("the error in ticker derais " + JSON.stringify(error));
            }
        );
        // Ticket #91
        // Activities
        this.itemsInActivities = [];
        globalService.getTicketActivity(this.constants.getTicketActivity, this.navParams.get("id")).subscribe(
            (result) => {
                // console.log("the result in ticket activities " + JSON.stringify(result.data.Activities));
                this.itemsInActivities = result.data.Activities;
            }, (error) => {
                console.log("the error in ticket activities " + JSON.stringify(error));
            }
        );
        // Ticket #91 ended
    }

    public menuOpened() {
        StoryDetailsPage.isMenuOpen = true;
    }
    public menuClosed() {
        StoryDetailsPage.isMenuOpen = false;
    }

    public dateChange(event, index, fieldDetails) {

   this.globalService.leftFieldUpdateInline(this.constants.leftFieldUpdateInline, this.localDate, fieldDetails).subscribe( 
(result) => {
    setTimeout(() => {
        document.getElementById("field_title_" + index).innerHTML = this.datePipe.transform(this.localDate, 'MMM-dd-yyyy');
        this.enableDataPicker[index] = false;
         document.getElementById("field_title_" + index).style.display = 'block';
        //    document.getElementById("item_" + index).classList.remove("item-select");
               if (result.data.activityData.referenceKey == -1) {
                        this.itemsInActivities.push(result.data.activityData.data);
             }else{
                        this.itemsInActivities[result.data.activityData.referenceKey]["PropertyChanges"].push(result.data.activityData.data);
          }
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
            document.getElementById("field_title_" + index).style.display = 'none';
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
        console.log("the date " + [month, day, year].join('-'));
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

    public changeOption(event, index, fieldDetails) {
        this.readOnlyDropDownField = false;
        this.showEditableFieldOnly[index] = false;

        //console.log("the displayfieldvalues " + JSON.stringify(this.displayFieldvalue) + "------- " + event + " &&&&&&&&& " + index)

        this.globalService.leftFieldUpdateInline(this.constants.leftFieldUpdateInline, (event.Id), fieldDetails).subscribe(
            (result) => {
                setTimeout(() => {
                    //    document.getElementById("field_title_" + index).innerHTML = event.Name;
                    jQuery("#field_title_" + index + " div").text(event.Name);
                    if (fieldDetails.fieldName == 'priority') {
                        this.displayedClassColorValue = event.Name;
                    }
                    if (result.data.activityData.referenceKey == -1) {
                        this.itemsInActivities.push(result.data.activityData.data);
                    }else{
                        this.itemsInActivities[result.data.activityData.referenceKey]["PropertyChanges"].push(result.data.activityData.data);
                    }
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
    }

    public inputBlurMethod(event, index, fieldDetails) {

        console.log("inside the input blur method " + event.target.value);

        this.globalService.leftFieldUpdateInline(this.constants.leftFieldUpdateInline, (event.target.value), fieldDetails).subscribe(
            (result) => {
                setTimeout(() => {
                    this.enableTextField[index] = false;
                    this.enableTextArea[index] = false;
                    document.getElementById("field_title_" + index).style.display = 'block';
                    document.getElementById("field_title_" + index).innerHTML = (event.target.value);

                    if (result.data.activityData.referenceKey == -1) {
                        this.itemsInActivities.push(result.data.activityData.data);
                    }else{
                        this.itemsInActivities[result.data.activityData.referenceKey]["PropertyChanges"].push(result.data.activityData.data);
                    }

                }, 200);
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
            // document.getElementById("field_title_" + index).innerHTML = this.displayFieldvalue[event-1].Name;
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

    openOptionsModal(fieldDetails, index) {
        console.log("the model present");
        fieldDetails['workflowType'] = this.taskDetails.workflowType;

        if ((fieldDetails.readOnly == 0) && ((fieldDetails.fieldType == "List") || (fieldDetails.fieldType == "Team List") || (fieldDetails.fieldType == "Bucket"))) {

            this.globalService.getFieldItemById(this.constants.fieldDetailsById, fieldDetails).subscribe(
                (result) => {
                    if (fieldDetails.fieldType == "Team List") {
                        this.displayFieldvalue.push({ "Id": "", "Name": "--none--", "Email": "null" })
                        for (let data of result.getFieldDetails) {
                            this.displayFieldvalue.push(data);
                        }
                    } else {
                        for (let data of result.getFieldDetails) {
                            this.displayFieldvalue.push(data);
                        }
                    }
                    StoryDetailsPage.optionsModal = this.modalController.create(CustomModalPage, { activeField: fieldDetails, activatedFieldIndex: index, displayList: this.displayFieldvalue });
                    StoryDetailsPage.optionsModal.onDidDismiss((data) => {
                        if (data != null && (data.Name != data.previousValue)) {
                            this.changeOption(data, index, fieldDetails);
                        }
                        this.displayFieldvalue = [];
                    });
                    StoryDetailsPage.optionsModal.present();
                },
                (error) => {
                    console.log("the fields error --- " + error);
                });

        } else if ((fieldDetails.readOnly == 0) && (fieldDetails.fieldType == "Date")) {
            this.enableDataPicker[index] = true;
            document.getElementById("field_title_" + index).style.display = 'none';


        } else if ((fieldDetails.readOnly == 0) && ((fieldDetails.fieldType == "TextArea") || (fieldDetails.fieldType == "Text"))) {

            if (fieldDetails.fieldType == "TextArea") {
                this.enableTextArea[index] = true;
                document.getElementById("field_title_" + index).focus();
                document.getElementById("field_title_" + index).style.display = 'none';
            }
            else if (fieldDetails.fieldType == "Text") {
                this.enableTextField[index] = true;
                document.getElementById("field_title_" + index).focus();
                document.getElementById("field_title_" + index).style.display = 'none';
            }
        }
    }


    // Ticket #91
    // Comments
    public navigateToParentComment(parentCommentId) {
        // console.log('navigateToParentComment : ' + parentCommentId + " --- " + JSON.stringify(jQuery("#"+parentCommentId).position()));
        jQuery("#"+parentCommentId)[0].scrollIntoView({
            behavior: "smooth", // or "auto" or "instant"
            block: "start" // or "end"
        });
    }
    public replyComment(commentId) {
        // console.log('replyComment : ' + commentId);
        this.replyToComment = commentId;
        this.replying = true;
        this.commentAreaColor = jQuery("#commentEditorArea").css("background");
        jQuery("#commentEditorArea").addClass("replybox");
        jQuery("#commentEditorArea")[0].scrollIntoView({
            behavior: "smooth", // or "auto" or "instant"
            block: "start" // or "end"
        });
    }
    public cancelReply() {
        // console.log('cancelReply');
        this.replying = false;
        this.replyToComment = -1;
        jQuery("#commentEditorArea").removeClass("replybox");
    }
    public deleteComment(commentId, slug) {
        // console.log("deleteComment : "+commentId+"-"+slug);
        var commentParams;
        var parentCommentId;
        if (this.itemsInActivities[commentId].Status == 2) {
            parentCommentId = parseInt(this.itemsInActivities[commentId].ParentIndex);
            commentParams = {
                TicketId: this.taskDetails.ticketId,
                Comment: {
                    Slug: slug,
                    ParentIndex: parentCommentId
                },
            };
        } else {
            commentParams = {
                TicketId: this.taskDetails.ticketId,
                Comment: {
                    Slug: slug
                },
            };
        }
        this.globalService.deleteCommentById(this.constants.deleteCommentById, commentParams).subscribe(
            (result) => {
                if (this.itemsInActivities[commentId].Status == 2) {
                    this.itemsInActivities[parentCommentId].repliesCount--;
                }
                this.itemsInActivities.splice(commentId, 1);
            }, (error) => {
                console.log("the error in deleteCommentById " + JSON.stringify(error));
            }
        );
    }
    public editComment(commentId) {
        // console.log("editComment : "+commentId);
        jQuery("#Actions_" + commentId + " .textEditor").val(this.itemsInActivities[commentId].CrudeCDescription);
        this.editTheComment[commentId] = true;//show submit and cancel button on editor replace at the bottom
    }
    public cancelEdit(commentId){
        // console.log("cancelEdit : "+commentId);
        // jQuery("#Actions_"+commentId+" .textEditor").val('');
        this.editTheComment[commentId] = false;//hide submit and cancel button on editor replace at the bottom
    }
    public openCommentMenu(commentId){
        this.openCommentMenuList[commentId]=true;//show submit and cancel button on editor replace at the bottom
    }
    public submitComment() {
        console.log("submitComment");
        var commentText = jQuery(".uploadAndSubmit .textEditor").val();
        if (commentText != "" && commentText.trim() != "") {
            this.commentDesc = "";
            jQuery("#commentEditorArea").removeClass("replybox");
            var commentedOn = new Date();
            var formatedDate = (commentedOn.getMonth() + 1) + '-' + commentedOn.getDate() + '-' + commentedOn.getFullYear();
            var commentData = {
                TicketId: this.taskDetails.ticketId,
                Comment: {
                    CrudeCDescription: commentText.replace(/^(((\\n)*<p>(&nbsp;)*<\/p>(\\n)*)+|(&nbsp;)+|(\\n)+)|(((\\n)*<p>(&nbsp;)*<\/p>(\\n)*)+|(&nbsp;)+|(\\n)+)$/gm, ""),//.replace(/(<p>(&nbsp;)*<\/p>)+|(&nbsp;)+/g,""),
                    CommentedOn: formatedDate,
                    ParentIndex: "",
                    Reply:this.replying,
                    OrigianalCommentorId:""
                },
            };
            if (this.replying == true) {
                if (this.replyToComment != -1) {
                    commentData.Comment.OrigianalCommentorId = jQuery("#replySnippetContent").attr("class");
                    commentData.Comment.ParentIndex = this.replyToComment + "";
                }
            }
            this.globalService.submitComment(this.constants.submitComment, commentData).subscribe(
                (result) => {
                    this.itemsInActivities.push(result.data);
                    if (this.replying == true) {
                        this.itemsInActivities[this.replyToComment].repliesCount++;
                    }
                    this.replying = false;
                    jQuery(".uploadAndSubmit .textEditor").val('');
                }, (error) => {
                    console.log("the error in submitComment " + JSON.stringify(error));
                }
            );
        }
    }
    public submitEditedComment(commentId, slug) {
        // console.log("submitEditedComment : "+commentId+"-"+slug);
        var editedContent = jQuery("#Actions_" + commentId + " .textEditor").val();
        if (editedContent != "" && editedContent.trim() != "") {
            var commentedOn = new Date();
            var formatedDate = (commentedOn.getMonth() + 1) + '-' + commentedOn.getDate() + '-' + commentedOn.getFullYear();
            var commentData = {
                TicketId: this.taskDetails.ticketId,
                Comment: {
                    CrudeCDescription: editedContent.replace(/^(((\\n)*<p>(&nbsp;)*<\/p>(\\n)*)+|(&nbsp;)+|(\\n)+)|(((\\n)*<p>(&nbsp;)*<\/p>(\\n)*)+|(&nbsp;)+|(\\n)+)$/gm, ""),
                    CommentedOn: formatedDate,
                    ParentIndex: "",
                    Slug: slug
                },
            };
            this.globalService.submitComment(this.constants.submitComment, commentData).subscribe(
                (result) => {
                    this.itemsInActivities[commentId].CrudeCDescription = result.data.CrudeCDescription;
                    this.itemsInActivities[commentId].CDescription = result.data.CDescription;
                    // jQuery("#Actions_"+commentId+" .textEditor").val('');
                    this.editTheComment[commentId] = false;//hide submit and cancel button on editor replace at the bottom
                }, (error) => {
                    console.log("the error in submitComment " + JSON.stringify(error));
                }
            );
        }
    }
    // Ticket #91 ended
    
    // Plugins
    public presentActionSheet() {
        let actionSheet = this.actionSheetCtrl.create({
            title: 'Select Image Source',
            buttons: [
                {
                    text: 'Load from Library',
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
            console.log('imagePath'+imagePath);
            if (this.platform.is('android') && sourceType === Camera.PictureSourceType.PHOTOLIBRARY) {
                console.log('if'+imagePath);
                FilePath.resolveNativePath(imagePath).then((filePath) => {
                    let correctPath = filePath.substr(0, filePath.lastIndexOf('/') + 1);
                    let currentName = imagePath.substring(imagePath.lastIndexOf('/') + 1, imagePath.lastIndexOf('?'));
                    this.copyFileToLocalDir(correctPath, currentName, this.createFileName(currentName));
                }, (err) => {
                    console.log('Error while resolveNativePath.');
                });
            } else {
                console.log('else'+imagePath);
                var currentName = imagePath.substr(imagePath.lastIndexOf('/') + 1);
                var correctPath = imagePath.substr(0, imagePath.lastIndexOf('/') + 1);
                this.copyFileToLocalDir(correctPath, currentName, this.createFileName(currentName));
            }
        }, (err) => {
            this.presentToast('Error while selecting image.');
        });
    }
    // Create a new name for the image
    private createFileName(originalName) {
        console.log('createFileName : ');
        if(originalName!='' || originalName!=null || originalName!=undefined){
            console.log('originalName : '+originalName);
        }
        else{
            console.log('No original name');
        }
        var d = new Date(),
        n = d.getTime(),
        newFileName =  n + ".jpg";
        console.log('createFileName'+newFileName);
        return newFileName;
    }
    
    // Copy the image to a local folder
    private copyFileToLocalDir(namePath, currentName, newFileName) {
        console.log('copyFileToLocalDir'+newFileName);
        File.copyFile(namePath, currentName, cordova.file.dataDirectory, newFileName).then(success => {
            console.log('copyFile'+newFileName);
            this.lastImage = newFileName;
            this.uploadImage(currentName,newFileName);
        }, error => {
            this.presentToast('Error while storing file.');
        });
    }
    // Always get the accurate path to your apps folder
    public pathForImage(img) {
        if (img === null) {
            return '';
        } else {
            return cordova.file.dataDirectory + img;
        }
    }
    private presentToast(text) {
        let toast = this.toastCtrl.create({
            message: text,
            duration: 3000,
            position: 'top'
        });
        toast.present();
    }
    public uploadImage(originalname,savedname) {
        console.log('uploadImage');
        // Destination URL
        var url = this.constants.filesUploading;
        console.log('url'+url);
        // File for Upload
        var targetPath = this.pathForImage(this.lastImage);
        console.log('target path'+targetPath);
        // File name only
        var filename = this.lastImage;
        console.log('filename'+filename);
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
            console.log('data'+JSON.stringify(data));
            this.uploadedInserver(data);
        }, (err) => {
            console.log('Error while uploading file.'+ JSON.stringify(err));
        });
    }
    public uploadedInserver(dataUploaded){
        console.log('dataUploaded'+JSON.stringify(dataUploaded));
        var serverResponse = JSON.parse(dataUploaded.response);
        console.log('serverResponse'+JSON.stringify(serverResponse));
        if(serverResponse['status']=='1'){
            console.log(serverResponse['status']);
            this.commentDesc = this.commentDesc + "[[image:" +serverResponse['path'] + "|" + serverResponse['originalname'] + "]] ";
            console.log(this.commentDesc);
            this.presentToast('Image succesfully uploaded.');
        }else{
            console.log(serverResponse['status']);
            this.commentDesc = this.commentDesc + " No image ";
            console.log(this.commentDesc);
            this.presentToast('Error while uploading file.');
        }
    }
    
    // public fileUploadEvent(fileInput: any, comeFrom: string, where:string,comment:string):void {
    //     var editor_contents;
    //     var appended_content;
    //     // console.log("==FileInput=="+JSON.stringify(fileInput));
    //     if(where=="edit_comments"){
    //         editor_contents = jQuery("#Actions_"+comment+" .textEditor").val();
    //         fileInput.preventDefault();
    //     }
    //     if(comeFrom == 'fileChange'){
    //             this.filesToUpload = <Array<File>> fileInput.target.files;
    //     } else{
    //             this.filesToUpload = <Array<File>> fileInput.target.files;
    //     }
    //     this.globalService.makeFileRequest(this.constants.filesUploadingNode, [], this.filesToUpload).then(
    //         (result :Array<any>) => {
    //             // console.log("the result " + JSON.stringify(result));
    //             for(var i = 0; i<result.length; i++){
    //                 var uploadedFileExtension = (result[i].originalname).split('.').pop();
    //                 if(uploadedFileExtension == "png" || uploadedFileExtension == "jpg" || uploadedFileExtension == "jpeg" || uploadedFileExtension == "gif") {
    //                     if(where =="comments"){
    //                         this.commentDesc = this.commentDesc + "[[image:" +result[i].path + "|" + result[i].originalname + "]] ";
    //                     }else if(where == "edit_comments"){
    //                         appended_content = editor_contents+"[[image:" +result[i].path + "|" + result[i].originalname + "]]"; 
    //                         jQuery("#Actions_"+comment+" .textEditor").val(appended_content);
    //                     }else{
    //                         this.ticketEditableDesc = this.ticketEditableDesc + "[[image:" +result[i].path + "|" + result[i].originalname + "]] ";
    //                     }
    //                 }else{
    //                     if(where =="comments"){
    //                         this.commentDesc = this.commentDesc + "[[file:" +result[i].path + "|" + result[i].originalname + "]] ";
    //                     }else if(where == "edit_comments"){
    //                         appended_content = editor_contents+"[[file:" +result[i].path + "|" + result[i].originalname + "]]";
    //                         jQuery("#Actions_"+comment+" .textEditor").val(appended_content);
    //                     }else{
    //                         this.ticketEditableDesc = this.ticketEditableDesc + "[[file:" +result[i].path + "|" + result[i].originalname + "]] ";
    //                     }
    //                 }
    //             }
    //         }, (error) => {
    //             this.ticketEditableDesc = this.ticketEditableDesc + "Error while uploading";
    //     });
    // }
    // Plugins ended
}
