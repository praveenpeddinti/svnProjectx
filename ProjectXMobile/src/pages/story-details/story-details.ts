import { Component, ViewChild, NgZone } from '@angular/core';
import { DatePipe } from '@angular/common';
import { ToastController, Content, Platform, App } from 'ionic-angular';
import { ModalController, NavParams, MenuController, LoadingController, PopoverController, ActionSheetController, AlertController } from 'ionic-angular';
import { Globalservice } from '../../providers/globalservice';
import { Constants } from '../../providers/constants';
import { PopoverPage } from '../popover/popover';
import { Storage } from "@ionic/storage";
import { CustomModalPage } from '../custom-modal/custom-modal';
import {Camera, File, Transfer, FilePath} from 'ionic-native';
declare var cordova: any;
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
    public itemsInActivities: Array<any>;
    private replyToComment = -1;
    private replying = false;
    private editTheComment = [];
    private editCommentOpenClose = [];
    private newCommentOpenClose = true;
    // changed
    // private editSubmitOpenClose = [];
    // private newSubmitOpenClose = true;
    private editSubmitOpenClose = [];
    private newSubmitOpenClose = false;
    // changed
    public commentDesc = "";
    private lastImage: string = null;
    private progressNew: number;
    private progressEdit: number;
    public enableDataPicker = [];
    public enableTextField = [];
    public enableTextArea = [];
    public titleAfterEdit: string = "";
    public enableEdatable: boolean = false;
    public taskDetails = { ticketId: "", title: "", description: "", type: "", workflowType: "" };
    public options = "options";
    public localDate: any = new Date();
    public minDate: any = new Date();
    public userName: any = '';
    public static optionsModal;
    public static isMenuOpen: boolean = false;
    public static menuControler;
    public textFieldValue = "";
    public textAreaValue = "";
    public displayedClassColorValue = "";

    @ViewChild(Content) content: Content;

    constructor(menu: MenuController,
        private app: App,
        private modalController: ModalController,
        private toastCtrl: ToastController,
        public globalService: Globalservice,
        private constants: Constants,
        public navParams: NavParams,
        public loadingController: LoadingController,
        public popoverCtrl: PopoverController,
        public actionSheetCtrl: ActionSheetController,
        public platform: Platform,
        private storage: Storage, 
        private datePipe: DatePipe,
        private ngZone: NgZone,
        private alertController: AlertController ) {
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
                loader.dismiss();
            }, error => {
                loader.dismiss();
            }
        );
        this.itemsInActivities = [];
        globalService.getTicketActivity(this.constants.getTicketActivity, this.navParams.get("id")).subscribe(
            (result) => {
                this.itemsInActivities = result.data.Activities;
            }, (error) => {
            }
        );
        this.progressNew = 0;
        this.progressEdit = 0;
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
                    if (result.data.activityData.referenceKey == -1) {
                        this.itemsInActivities.push(result.data.activityData.data);
                    } else {
                        this.itemsInActivities[result.data.activityData.referenceKey]["PropertyChanges"].push(result.data.activityData.data);
                    }
                }, 300);
            },
            (error) => {
                this.presentToast('Unsuccessful');
            });
        setTimeout(() => {
            document.getElementById("field_title_" + index).style.display = 'none';
        }, 300);
    }
    public formatDate(date) {
        var d = new Date(date),
            month = '' + (d.getMonth() + 1),
            day = '' + d.getDate(),
            year = d.getFullYear();
        if (month.length < 2) month = '0' + month;
        if (day.length < 2) day = '0' + day;
        return [month, day, year].join('-');
    }
    ionViewDidLoad() {
    
    }
    ionViewDidEnter() {
        if (jQuery('#description').height() > 200) {
            jQuery('#description').css("height", "200px");
            jQuery('.show-morediv').show();
            jQuery('#show').show();
        }
    }
    ionViewWillEnter() {
        if (jQuery('#description').height() > 200) {
            jQuery('#description').css("height", "200px");
            jQuery('.show-morediv').show();
            jQuery('#show').show();
        }
    }
    public selectCancel(index) {
        this.showEditableFieldOnly[index] = false;
    }
    public changeOption(event, index, fieldDetails) {
        this.readOnlyDropDownField = false;
        this.showEditableFieldOnly[index] = false;
        this.globalService.leftFieldUpdateInline(this.constants.leftFieldUpdateInline, (event.Id), fieldDetails).subscribe(
            (result) => {
                setTimeout(() => {
                    jQuery("#field_title_" + index + " div").text(event.Name);
                    if (fieldDetails.fieldName == 'priority') {
                        this.displayedClassColorValue = event.Name;
                    } else if(fieldDetails.fieldName == "workflow"){
                        jQuery("#field_title_" + (index-1) + " div").text(result.data.updatedState.state);
                    }
                    if (result.data.activityData.referenceKey == -1) {
                        this.itemsInActivities.push(result.data.activityData.data);
                    } else {
                        this.itemsInActivities[result.data.activityData.referenceKey]["PropertyChanges"].push(result.data.activityData.data);
                    }
                }, 300);
            },
            (error) => {
                this.presentToast('Unsuccessful');
            });
    }
    public inputBlurMethod(event, index, fieldDetails) {
        this.globalService.leftFieldUpdateInline(this.constants.leftFieldUpdateInline, (event.target.value), fieldDetails).subscribe(
            (result) => {
                setTimeout(() => {
                    this.enableTextField[index] = false;
                    this.enableTextArea[index] = false;
                    document.getElementById("field_title_" + index).style.display = 'block';
                    document.getElementById("field_title_" + index).innerHTML = (event.target.value);
                    if (result.data.activityData.referenceKey == -1) {
                        this.itemsInActivities.push(result.data.activityData.data);
                    } else {
                        this.itemsInActivities[result.data.activityData.referenceKey]["PropertyChanges"].push(result.data.activityData.data);
                    }
                }, 200);
            },
            (error) => {
                this.presentToast('Unsuccessful');
            });
    }
    public openPopover(myEvent) {
        let userCredentials = { username: this.userName };
        let popover = this.popoverCtrl.create(PopoverPage, userCredentials);
        popover.present({
            ev: myEvent
        });
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
    public openOptionsModal(fieldDetails, index) {
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
    public navigateToParentComment(parentCommentId) {
        jQuery("#"+parentCommentId)[0].scrollIntoView({
            behavior: "smooth", // or "auto" or "instant"
            block: "start" // or "end"
        });
    }
    public replyComment(commentId) {
        this.replyToComment = commentId;
        this.replying = true;
        jQuery("#commentEditorArea").addClass("replybox");
        jQuery("#commentEditorArea")[0].scrollIntoView({
            behavior: "smooth", // or "auto" or "instant"
            block: "start" // or "end"
        });
    }
    public cancelReply() {
        this.replying = false;
        this.replyToComment = -1;
        jQuery("#commentEditorArea").removeClass("replybox");
    }
    public presentConfirmDelete(commentId, slug) {
        let alert = this.alertController.create({
            title: 'Confirm Delete',
            message: 'Do you want to delete this comment?',
            buttons: [
            {
                text: 'CANCEL',
                role: 'cancel',
                handler: () => {}
            },
            {
                text: 'OK',
                handler: () => {
                    this.deleteComment(commentId, slug);
                }
            }
            ]
        });
        alert.present();
    }
    public deleteComment(commentId, slug) {
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
                this.presentToast('Unsuccessful');
            }
        );
    }
    public editComment(commentId) {
        jQuery("#Actions_" + commentId + " .textEditor").val(this.itemsInActivities[commentId].CrudeCDescription);
        this.editTheComment[commentId] = true;//show submit and cancel button on editor replace at the bottom
        this.newCommentOpenClose = false;
        this.editCommentOpenClose[commentId] = true;
        // this.editSubmitOpenClose[commentId] = true;
    }
    public cancelEdit(commentId){
        this.editTheComment[commentId] = false;//hide submit and cancel button on editor replace at the bottom
        this.editCommentOpenClose[commentId] = false;
        this.newCommentOpenClose = true;
        // this.editSubmitOpenClose[commentId] = true;
    }
    public showSubmit(commentId){
        if(commentId==-1){
            this.newSubmitOpenClose = false;
        }
        else{
            this.editSubmitOpenClose[commentId] = false;
        }
    }
    public hideSubmit(commentId,event){
        if (jQuery(event.target).hasClass('preventBlur')){
            event.stopImmediatePropagation();
            jQuery(event.target).off("blur");
        }else{
            if(commentId==-1){
                this.newSubmitOpenClose = true;
            }
            else{
                this.editSubmitOpenClose[commentId] = true;
            }
        }
    }
    public hideSubmitUpload(event){
        if (jQuery(event.target).hasClass('editorDiv')){
            event.stopImmediatePropagation();
            jQuery(this).off("blur");
        }
    }
    public submitComment() {
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
                    // if (commentText != "" && commentText.trim() != ""){
                    //     this.newSubmitOpenClose = true;
                    // }else{
                    //     this.newSubmitOpenClose = false;
                    // }
                }, (error) => {
                    this.presentToast('Unsuccessful');
                }
            );
        }
    }
    public submitEditedComment(commentId, slug) {
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
                    this.editTheComment[commentId] = false;//hide submit and cancel button on editor replace at the bottom
                    this.editCommentOpenClose[commentId] = false;
                    this.newCommentOpenClose = true;
                    // if (editedContent != "" && editedContent.trim() != ""){
                    //     this.editSubmitOpenClose[commentId] = true;
                    // }else{
                    //     this.editSubmitOpenClose[commentId] = false;
                    // }
                }, (error) => {
                    this.presentToast('Unsuccessful');
                }
            );
        }
    }
    public presentActionSheet(comeFrom: string, where:string, comment:string) {
        let actionSheet = this.actionSheetCtrl.create({
            title: 'Select Image Source',
            buttons: [
                {
                    text: 'Load from Library',
                    handler: () => {
                        this.takePicture(Camera.PictureSourceType.PHOTOLIBRARY,comeFrom, where, comment);
                    }
                },
                {
                    text: 'Use Camera',
                    handler: () => {
                        this.takePicture(Camera.PictureSourceType.CAMERA,comeFrom, where, comment);
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
    public takePicture(sourceType,comeFrom: string, where:string, comment:string) {
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
                    this.copyFileToLocalDir(correctPath, currentName, this.createFileName(currentName), comeFrom, where, comment);
                }, (err) => {
                    console.log('Error while resolveNativePath.');
                });
            } else {
                var currentName = imagePath.substr(imagePath.lastIndexOf('/') + 1);
                var correctPath = imagePath.substr(0, imagePath.lastIndexOf('/') + 1);
                this.copyFileToLocalDir(correctPath, currentName, this.createFileName(currentName), comeFrom, where, comment);
            }
        }, (err) => {
           // this.presentToast('Unable to select the image.');
        });
    }
    private createFileName(originalName) {
        var d = new Date(),
        n = d.getTime(),
        newFileName =  "image"+n;
        return newFileName;
    }
    private copyFileToLocalDir(namePath, currentName, newFileName, comeFrom: string, where:string, comment:string) {
        File.copyFile(namePath, currentName, cordova.file.dataDirectory, newFileName).then(success => {
            this.lastImage = newFileName;
            this.uploadImage(currentName, newFileName, comeFrom, where, comment);
        }, error => {
            console.log('Error while storing file.');
        });
    }
    public pathForImage(img) {
        if (img === null) {
            return '';
        } else {
            return cordova.file.dataDirectory + img;
        }
    }
    public onProgressNew = (progressEvent: ProgressEvent) : void => {
        this.ngZone.run(() => {
            if (progressEvent.lengthComputable) {
                let progress = Math.floor(progressEvent.loaded / progressEvent.total * 100);
                this.progressNew = progress;
                document.getElementById('progressFileUploadNew').innerHTML = progress + "% loaded...";
            } else {
                if (document.getElementById('progressFileUploadNew').innerHTML == "") {
                    document.getElementById('progressFileUploadNew').innerHTML = "Loading";
                } else {
                    document.getElementById('progressFileUploadNew').innerHTML += ".";
                }
            }
        });
    }
    public onProgressEdit = (progressEvent: ProgressEvent) : void => {
        this.ngZone.run(() => {
            if (progressEvent.lengthComputable) {
                let progress = Math.floor(progressEvent.loaded / progressEvent.total * 100);
                this.progressEdit = progress;
                document.getElementById('progressFileUploadEdit').innerHTML = progress + "% loaded...";
            } else {
                if (document.getElementById('progressFileUploadEdit').innerHTML == "") {
                    document.getElementById('progressFileUploadEdit').innerHTML = "Loading";
                } else {
                    document.getElementById('progressFileUploadEdit').innerHTML += ".";
                }
            }
        });
    }
    public uploadImage(originalname, savedname, comeFrom: string, where:string, comment:string) {
        var url = this.constants.filesUploading;
        var targetPath = this.pathForImage(this.lastImage);
        var filename = this.lastImage;
        var options = {
            fileKey: "commentFile",
            fileName: filename,
            chunkedMode: false,
            mimeType: "image/jpeg",
            params : {'filename': filename,'directory':this.constants.fileUploadsFolder,'originalname': originalname}
        };
        const fileTransfer = new Transfer();
        if(where == "comments"){
            fileTransfer.onProgress(this.onProgressNew);
        }
        if(where=="edit_comments"){
            fileTransfer.onProgress(this.onProgressEdit);
        }
        fileTransfer.upload(targetPath, url, options).then(
            (data) => {
                var statusUpload = this.uploadedInserver(data, comeFrom, where, comment);
                if(statusUpload=='uploaded'){
                    if(where == "comments"){
                        this.progressNew = 0;
                        document.getElementById('progressFileUploadNew').innerHTML = "";
                    }
                    if(where=="edit_comments"){
                        this.progressEdit = 0;
                        document.getElementById('progressFileUploadEdit').innerHTML = "";
                    }
                }else if(statusUpload=='notuploaded'){
                    this.presentToast('Unable to upload the image.');
                }
            }, (err) => {
                this.presentToast('Unable to upload the image.');
        });
    }
    public uploadedInserver(dataUploaded, comeFrom: string, where:string, comment:string){
        var serverResponse = JSON.parse(dataUploaded.response);
        if (serverResponse['status'] == '1') {
            var editor_contents;
            var appended_content;
            if(where=="edit_comments"){
                editor_contents = jQuery("#Actions_"+comment+" .textEditor").val();
            }
            var uploadedFileExtension = (serverResponse['originalname']).split('.').pop();
            if (uploadedFileExtension == "png" || uploadedFileExtension == "jpg" || uploadedFileExtension == "jpeg" || uploadedFileExtension == "gif") {
                if (where == "comments") {
                    this.commentDesc = this.commentDesc + "[[image:" +serverResponse['path'] + "|" + serverResponse['originalname'] + "]] ";
                } else if (where == "edit_comments") {
                    appended_content = editor_contents + "[[image:" +serverResponse['path'] + "|" + serverResponse['originalname'] + "]] ";
                    jQuery("#Actions_" + comment + " .textEditor").val(appended_content);
                } 
            }
            return 'uploaded';
        }else{
            return 'notuploaded';
        }
    }
}
