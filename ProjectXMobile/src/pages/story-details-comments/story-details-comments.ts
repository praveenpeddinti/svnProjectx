//---------------------------------------------------
import { Component, ViewChild, NgZone } from '@angular/core';
import { IonicPage, ToastController, Content, Platform, App, NavController } from 'ionic-angular';
import { ModalController, NavParams, MenuController, LoadingController, PopoverController, ActionSheetController, AlertController } from 'ionic-angular';
import { Globalservice } from '../../providers/globalservice';
import { Constants } from '../../providers/constants';
import { Storage } from '@ionic/storage';
import { Camera } from '@ionic-native/camera';
import { File } from '@ionic-native/file';
import { Transfer,TransferObject } from '@ionic-native/transfer';
import { FilePath } from '@ionic-native/file-path';
import { DatePipe } from '@angular/common';
import { CustomModalPage } from '../custom-modal/custom-modal';

declare var cordova: any;
declare var jQuery: any;
declare var RE: any;
/**
 * Generated class for the StoryDetailsComments page.
 *
 * See http://ionicframework.com/docs/components/#navigation for more info
 * on Ionic pages and navigation.
 */
@IonicPage()
@Component({
  selector: 'page-story-details-comments',
  templateUrl: 'story-details-comments.html',
   providers: [DatePipe]
})
export class StoryDetailsComments {
    //ticket details
    public taskDetails = { ticketId: "", title: "", description: "", type: "", workflowType: "" };
    public ticketId: any;
    public rootParams: any = {ticketId: ""};
    public itemsInActivities: Array<any>;
    private replyToComment = -1;
    private replying = false;
    private editTheComment = [];
    private editCommentOpenClose = [];
    private newCommentOpenClose = true;
    private newSubmitOpenClose = true;
    public editSubmitOpenClose = true;
    public commentDesc = "";
    private lastImage: string = null;
    private progressNew: number;
    private progressEdit: number;
    public storyTicketId: any;
    
    
   
    public items: Array<any>;
    public arrayList: Array<{ id: string, title: string, assignTo: string, readOnly: string, fieldType: string, fieldName: string, ticketId: any, readableValue: any }>;
    public displayFieldvalue = [];
   // public showEditableFieldOnly = [];
    //public readOnlyDropDownField: boolean = false;
    
    //public enableDataPicker = [];
   // public enableTextField = [];
  //  public enableTextArea = [];
    public titleAfterEdit: string = "";
   // public enableEdatable: boolean = false;
   // public taskDetails = { ticketId: "", title: "", description: "", type: "", workflowType: "" };
   // public options = "options";
    public localDate: any = new Date();
    public minDate: any = new Date();
    public userName: any = '';
    public static optionsModal;
  //  public static isMenuOpen: boolean = false;
//    public static menuControler;
    public textFieldValue = "";
    public textAreaValue = "";
    public displayedClassColorValue = "";
    public myHTML: any;

    @ViewChild(Content) content: Content;
    
  constructor(public navCtrl: NavController, public navParams: NavParams,
        menu: MenuController,
        private app: App,
        private modalController: ModalController,
        private toastCtrl: ToastController,
        public globalService: Globalservice,
        private constants: Constants,
        private camera: Camera,
        private file: File,
        private transfer: Transfer,
        private filePath:FilePath,
        public loadingController: LoadingController,
        public popoverCtrl: PopoverController,
        public actionSheetCtrl: ActionSheetController,
        public platform: Platform,
        private storage: Storage, 
        private ngZone: NgZone,
        private alertController: AlertController,) {
       let loader = this.loadingController.create({ content: "Loading..." });
       loader.present();
         this.storage.get('userCredentials').then((value) => {
            this.userName = value.username;
        });
         this.storyTicketId = this.navParams.data.ticketId;
         console.log("the story details comments ticket id " + JSON.stringify(this.storyTicketId));
         this.itemsInActivities = [];

//ticket details 
     globalService.getTicketDetailsById(this.constants.taskDetailsById, this.storyTicketId).subscribe(
            result => {
                this.taskDetails.ticketId = this.storyTicketId;
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


         globalService.getTicketActivity(this.constants.getTicketActivity, this.storyTicketId).subscribe(
             (result) => {
                 this.itemsInActivities = result.data.Activities;
             }, (error) => {
             }
         );
         this.progressNew = 0;
         this.progressEdit = 0;
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
   public dateChange(event, index, fieldDetails) {
        this.globalService.leftFieldUpdateInline(this.constants.leftFieldUpdateInline, this.localDate, fieldDetails).subscribe(
            (result) => {
                setTimeout(() => {
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

     public navigateToParentComment(parentCommentId) {
        jQuery("#"+parentCommentId)[0].scrollIntoView({
            behavior: "smooth", // or "auto" or "instant"
            block: "start" // or "end"
        });
    }
  ionViewDidLoad() {
        RE.editor = document.getElementById('editor');
        RE.editor.setAttribute("placeholder", "insert my text ...");
        RE.editor.addEventListener("keyup", RE.keyup);
    console.log('ionViewDidLoad StoryDetailsComments');
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
      ionViewDidEnter() {
//        if (jQuery('#description').height() > 200) {
//            jQuery('#description').css("height", "200px");
//            jQuery('.show-morediv').show();
//            jQuery('#show').show();
//        }
        var thisObj = this;
        jQuery(document).ready(function(){
            jQuery(document).bind("click",function(event){ 
                if(jQuery(event.target).closest('.submitcommentupload').length == 0 && jQuery(event.target).closest('.commentTextArea').length == 0){ 
                    thisObj.newSubmitOpenClose = true;
                    thisObj.editSubmitOpenClose = true;
                }
            });
        });
    }
    ionViewWillEnter() {
//        if (jQuery('#description').height() > 200) {
//            jQuery('#description').css("height", "200px");
//            jQuery('.show-morediv').show();
//            jQuery('#show').show();
//        }
    }
//    public selectCancel(index) {
//        this.showEditableFieldOnly[index] = false;
//    }
       public changeOption(event, index, fieldDetails) {
//        this.readOnlyDropDownField = false;
//        this.showEditableFieldOnly[index] = false;
        this.globalService.leftFieldUpdateInline(this.constants.leftFieldUpdateInline, (event.Id), fieldDetails).subscribe(
            (result) => {
                setTimeout(() => {
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
//                    this.enableTextField[index] = false;
//                    this.enableTextArea[index] = false;
//                    document.getElementById("field_title_" + index).style.display = 'block';
//                    document.getElementById("field_title_" + index).innerHTML = (event.target.value);
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
    public titleEdit(event) {
        //this.enableEdatable = true;
    }

//    public updateTitleSubmit() {
//        this.enableEdatable = false;
//        this.taskDetails.title = this.titleAfterEdit;
//    }
//    public updateTitleCancel() {
//        this.enableEdatable = false;
//        this.titleAfterEdit = this.taskDetails.title;
//    }
//    public expandDescription() {
//        jQuery('#description').css('height', 'auto');
//        jQuery('#show').hide();
//        jQuery('#hide').show();
//    }
//    public collapseDescription() {
//        jQuery('#hide').hide();
//        jQuery('#show').show();
//        jQuery('#description').css("height", "200px");
//        jQuery('#description').css("overflow", "hidden");
//    }
    public openOptionsModal(fieldDetails, index) {
        console.log("open option method from story details comment.");
       if ((fieldDetails.readOnly == 0) && ((fieldDetails.fieldType == "List") || (fieldDetails.fieldType == "Team List") || (fieldDetails.fieldType == "Bucket"))) {
           this.globalService.getFieldItemById(this.constants.fieldDetailsById, fieldDetails).subscribe(
               (result) => {
                StoryDetailsComments.optionsModal = this.modalController.create(CustomModalPage, { activeField: fieldDetails, activatedFieldIndex: index, displayList: this.displayFieldvalue });
                console.log("the index of open model is" + index);
                       StoryDetailsComments.optionsModal.onDidDismiss((data) => {
                       if (data != null && (data.Name != data.previousValue)) {
                           this.changeOption(data, index, fieldDetails);
                       }
                    
                   });
               },
               (error) => {
               });
       }
   }
    public replyComment(commentId) {
        jQuery(".commentAction").removeClass("fab-close-active");
        jQuery(".fab-list-active").removeClass("fab-list-active");
        this.replyToComment = commentId;
        this.replying = true;
        jQuery("#commentEditorArea").addClass("replybox");
        this.content.resize();
        setTimeout(function(){
            jQuery("#uploadAndSubmit")[0].scrollIntoView({
                behavior: "smooth", // or "auto" or "instant"
                block: "end" // or "start"
            });
        },500);
    }
    public cancelReply() {
        this.replying = false;
        this.replyToComment = -1;
        jQuery("#commentEditorArea").removeClass("replybox");
    }
    public presentConfirmDelete(commentId, slug) {
        jQuery(".commentAction").removeClass("fab-close-active");
        jQuery(".fab-list-active").removeClass("fab-list-active");
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
        var editedContent= jQuery("#Activity_content_"+commentId+" .commentp").html();
        var commentParams;
        var parentCommentId;
        if (this.itemsInActivities[commentId].Status == 2) {
            parentCommentId = parseInt(this.itemsInActivities[commentId].ParentIndex);
            commentParams = {
                TicketId: this.storyTicketId,
                Comment: {
                    Slug: slug,
                    ParentIndex: parentCommentId,
                    CrudeCDescription:editedContent.replace(/^(((\\n)*<p>(&nbsp;)*<\/p>(\\n)*)+|(&nbsp;)+|(\\n)+)|(((\\n)*<p>(&nbsp;)*<\/p>(\\n)*)+|(&nbsp;)+|(\\n)+)$/gm,""),
                },
            };
        } else {
            commentParams = {
                TicketId: this.storyTicketId,
                Comment: {
                    Slug: slug,
                    CrudeCDescription:editedContent.replace(/^(((\\n)*<p>(&nbsp;)*<\/p>(\\n)*)+|(&nbsp;)+|(\\n)+)|(((\\n)*<p>(&nbsp;)*<\/p>(\\n)*)+|(&nbsp;)+|(\\n)+)$/gm,""),
                },
            };
        }
        this.globalService.deleteCommentById(this.constants.deleteCommentById, commentParams).subscribe(
            (result) => {
                console.log("deleted the comment");
                if (this.itemsInActivities[commentId].Status == 2) {
                    this.itemsInActivities[parentCommentId].repliesCount--;
                }
                this.itemsInActivities[commentId].Status = 0;
                //this.itemsInActivities.splice(commentId, 1);
            }, (error) => {
                this.presentToast('Unsuccessful');
            }
        );
    }
    public editComment(commentId) {
        var thisObj = this;
        jQuery(".commentAction").removeClass("fab-close-active");
        jQuery(".fab-list-active").removeClass("fab-list-active");
        jQuery("div").each(function (index,element) {
            if(jQuery(element).hasClass("commentingTextArea")) {
                var actionIdArray =  jQuery(element).attr('id').split('_'); 
                var commentid = actionIdArray[1];
                thisObj.editTheComment[commentid] = false;
                thisObj.editCommentOpenClose[commentid] = false;
            }
        });
        jQuery(".comment_edit_editor_" + commentId ).html(this.itemsInActivities[commentId].CrudeCDescription);
        this.editTheComment[commentId] = true;//show submit and cancel button on editor replace at the bottom
        this.newCommentOpenClose = false;
        this.editCommentOpenClose[commentId] = true;
    }
    public cancelEdit(commentId){
        this.editTheComment[commentId] = false;//hide submit and cancel button on editor replace at the bottom
        this.editCommentOpenClose[commentId] = false;
        this.newCommentOpenClose = true;
    }
    public showSubmit(commentId){console.log(commentId)
        if(commentId==-1){ 
            this.newSubmitOpenClose = false;
        }
        else{
            this.editSubmitOpenClose = false; 
        }
    }
    public submitComment() {
        console.log("submit button clicked1");
        this.myHTML = jQuery('.comment_editor').html();
        console.log("submit button clicked2" + JSON.stringify(this.myHTML));
        //var commentText = jQuery(".uploadAndSubmit .textEditor").val();
        // var commentText = this.commentDesc;
        if (this.myHTML != "" && this.myHTML.trim() != "" ) {
          //  this.commentDesc = "";
            console.log("submit button clicked3");
            jQuery("#commentEditorArea").removeClass("replybox");
            var commentedOn = new Date();
            var formatedDate = (commentedOn.getMonth() + 1) + '-' + commentedOn.getDate() + '-' + commentedOn.getFullYear();
            var commentData = {
                TicketId: this.storyTicketId,
                Comment: {
                    CrudeCDescription: this.myHTML.replace(/^(((\\n)*<p>(&nbsp;)*<\/p>(\\n)*)+|(&nbsp;)+|(\\n)+)|(((\\n)*<p>(&nbsp;)*<\/p>(\\n)*)+|(&nbsp;)+|(\\n)+)$/gm, ""),//.replace(/(<p>(&nbsp;)*<\/p>)+|(&nbsp;)+/g,""),
                    CommentedOn: formatedDate,
                    ParentIndex: "",
                    Reply:this.replying,
                    OriginalCommentorId:""
                },
            };
            if (this.replying == true) {
                if (this.replyToComment != -1) {
                    commentData.Comment.OriginalCommentorId = jQuery("#replySnippetContent").attr("class");
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
                   jQuery('.comment_editor').html("");
                   // jQuery(".uploadAndSubmit .textEditor").val('');
                }, (error) => {
                    this.presentToast('Unsuccessful');
                }
            );
        }
    }
    public submitEditedComment(commentId, slug) {
        var editedContent = jQuery(".comment_edit_editor_" + commentId ).html();
        if (editedContent != "" && editedContent.trim() != "") {
            var commentedOn = new Date();
            var formatedDate = (commentedOn.getMonth() + 1) + '-' + commentedOn.getDate() + '-' + commentedOn.getFullYear();
            var commentData = {
                TicketId: this.storyTicketId,
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
                    jQuery(".comment_edit_editor_" + commentId ).html("");
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
                        this.takePicture(this.camera.PictureSourceType.PHOTOLIBRARY,comeFrom, where, comment);
                    }
                },
                {
                    text: 'Use Camera',
                    handler: () => {
                        this.takePicture(this.camera.PictureSourceType.CAMERA,comeFrom, where, comment);
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
            destinationType: this.camera.DestinationType.FILE_URI,
            encodingType: this.camera.EncodingType.JPEG,
            saveToPhotoAlbum: false,
            correctOrientation: true,
        };
        this.camera.getPicture(options).then((imagePath) => {
            if (this.platform.is('android') && sourceType === this.camera.PictureSourceType.PHOTOLIBRARY) {
                this.filePath.resolveNativePath(imagePath).then((filePath) => {
                    let correctPath = filePath.substr(0, filePath.lastIndexOf('/') + 1);
                    let currentName = imagePath.substring(imagePath.lastIndexOf('/') + 1, imagePath.lastIndexOf('?'));
                    this.copyFileToLocalDir(correctPath, currentName, this.createFileName(currentName), comeFrom, where, comment);
                }, (err) => {});
            } else {
                var currentName = imagePath.substr(imagePath.lastIndexOf('/') + 1);
                var correctPath = imagePath.substr(0, imagePath.lastIndexOf('/') + 1);
                this.copyFileToLocalDir(correctPath, currentName, this.createFileName(currentName), comeFrom, where, comment);
            }
        }, (err) => {});
    }
    private createFileName(originalName) {
        var d = new Date(),
        n = d.getTime(),
        newFileName =  "image"+n;
        return newFileName;
    }
    private copyFileToLocalDir(namePath, currentName, newFileName, comeFrom: string, where:string, comment:string) {
        this.file.copyFile(namePath, currentName, cordova.file.dataDirectory, newFileName).then(success => {
            this.lastImage = newFileName;
            this.uploadImage(currentName, newFileName, comeFrom, where, comment);
        }, error => {});
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
       // const fileTransfer = new Transfer();
        const fileTransfer: TransferObject = this.transfer.create();
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
        this.myHTML = document.getElementById('editor').innerHTML;
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
                   this.myHTML = this.myHTML + "[[image:" +serverResponse['path'] + "|" + serverResponse['originalname'] + "]] ";
                    this.newSubmitOpenClose = false;
                } else if (where == "edit_comments") {
                    appended_content = editor_contents + "[[image:" +serverResponse['path'] + "|" + serverResponse['originalname'] + "]] ";
                    jQuery("#Actions_" + comment + " .textEditor").val(appended_content);
                    this.editSubmitOpenClose = false;
                } 
            }
            return 'uploaded';
        }else{
            return 'notuploaded';
        }
    }

    public atmensions(){
            var thisObj=this;
    var reqParam={'ProjectId':1,'search_term':''};
    var userList;
    jQuery("#editor").atwho({
    at: "@",
     callbacks:{ remoteFilter: function(query, callback){
        console.log("==queyr=="+query);
      if(query.length > 0){
          reqParam.search_term=query;
        thisObj.globalService.getCollaborators(thisObj.constants.getCollaboratorsUrl,reqParam).subscribe(
                (result) => {
                   userList = jQuery.map(result.data, function(value, i) {
  return {'id':value.Id, 'name':value.Name,'profilepic':value.ProfilePic};
});
callback(userList);
      
                }, (error) => {
                   console.log("user loading error")
                }
            );
     // callback(userList);
        }
    },
}, 
    data:userList,
    displayTpl: "<li class='atmentionuserstyles' ><img src='${profilepic}' height='20' width='20'/> ${name} </li>",
    insertTpl: "@${name}",
});
    }

}
