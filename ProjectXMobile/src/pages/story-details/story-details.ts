import { Component, ViewChild, NgZone } from '@angular/core';
import { DatePipe } from '@angular/common';
import { ToastController, Content, Platform, App, NavController } from 'ionic-angular';
import { ModalController, NavParams, MenuController, LoadingController, PopoverController, ActionSheetController, AlertController } from 'ionic-angular';
import { Globalservice } from '../../providers/globalservice';
import { Constants } from '../../providers/constants';
// Ticket #113
import { AutoCompleteProvider } from '../../providers/auto-complete-provider';
import { AutoCompleteComponent } from 'ionic2-auto-complete';
// Ticket #113 ended
import { LogoutPage } from '../logout/logout';
import { Storage } from '@ionic/storage';
import { CustomModalPage } from '../custom-modal/custom-modal';
import { Camera } from '@ionic-native/camera';
import { File } from '@ionic-native/file';
import { Transfer,TransferObject } from '@ionic-native/transfer';
import { FilePath } from '@ionic-native/file-path';
//Story details tabs 
import { StoryDetailsComments } from '../story-details-comments/story-details-comments';
import {StoryDetailsFollowers} from '../story-details-followers/story-details-followers';
import {StoryDetailsTask} from '../story-details-task/story-details-task';
import {StoryDetailsWorklog} from '../story-details-worklog/story-details-worklog';
//import { StoryFollowersPage } from "../story-followers/story-followers";
//import { StoryTaskPage } from "../story-task/story-task";
//import { StoryWorklogPage } from "../story-worklog/story-worklog";

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
       //for tabs : - prabhu
    StoryDetailsComments:any;
    StoryDetailsFollowers: any;
    StoryDetailsTask: any;
    StoryDetailsWorklog: any;

    // showIcons: boolean;
    // showTitles: boolean;
    // pageTitle: string;
    //end tabs : - prabhu
    public ticketId: any;
    public rootParams: any = {ticketId: ""};
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
    private newSubmitOpenClose = true;
    private editSubmitOpenClose = true;
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
    // Ticket #113
    public followers: Array<any>; 
    public userId: number;
    private follower_search_results: string[];
    private fList: any = [];
    public isTask:boolean=false;
    // Ticket #113 ended
    //Work log
    // public workLog = { thours: "", iworkHours: "" };
    // public workedLogtime: any = {};
    // public individualitems: Array<any>;
    // public inputHourslog = "";
   // public mySample: any= "";
    //public mySample: any;
    public fooId: any = {};

    @ViewChild(Content) content: Content;
    // Ticket #113
    @ViewChild('searchbar') searchbar: AutoCompleteComponent;
    // Ticket #113 ended
    // Ticket #113 added autoCompleteProvider in constructor
    constructor(menu: MenuController,
        private app: App,
        private modalController: ModalController,
        private toastCtrl: ToastController,
        public globalService: Globalservice,
        private constants: Constants,
        private camera: Camera,
        private file: File,
        private transfer: Transfer,
        private filePath:FilePath,
        public navParams: NavParams,
        public loadingController: LoadingController,
        public popoverCtrl: PopoverController,
        public actionSheetCtrl: ActionSheetController,
        public platform: Platform,
        private storage: Storage, 
        private datePipe: DatePipe,
        private ngZone: NgZone,
        private alertController: AlertController,
        public navCtrl: NavController,
        public autoCompleteProvider: AutoCompleteProvider ) {
        localStorage.setItem('headerInfo',JSON.stringify({'title':"Story Details",'backButton':"",'logo':0,'leftPannel':1,notification:1,profile:1}));
           this.navParams = navParams;
           this.StoryDetailsComments = StoryDetailsComments;
           this.StoryDetailsFollowers = StoryDetailsFollowers;
           this. StoryDetailsTask = StoryDetailsTask;
           this. StoryDetailsWorklog = StoryDetailsWorklog;
            // this.mySample = this.navParams.data;
            // console.log("the RootParam value is" + JSON.stringify(this.mySample));
         //for tabs :- prabhu
        // this.showIcons = navParams.get('icons');
        // this.showTitles = navParams.get('titles');
        // this.pageTitle = navParams.get('pageTitle');
        
        this.ticketId = this.navParams.get("id");
        var slug= this.navParams.get("slug"); 
        this.rootParams.ticketId =  this.navParams.get("id");
        this.rootParams.slug =  this.navParams.get("slug");
        StoryDetailsPage.menuControler = menu;
        this.minDate = new Date().toISOString();
        let loader = this.loadingController.create({ content: "Loading..." });
       // loader.present();
       var userInfo=JSON.parse(localStorage.getItem("userCredentials"));
            this.userName = userInfo.username;
            // Ticket #113
            this.userId = userInfo.Id;
            // Ticket #113 ended
      //  });
        globalService.getTicketDetailsById(this.constants.taskDetailsById, this.navParams.get("id")).subscribe(
            result => {
                console.log("ticket_details___"+JSON.stringify(result));
                this.taskDetails.ticketId = result.data.TicketId;
                this.taskDetails.title = result.data.Title;
                this.taskDetails.description = result.data.Description;
                this.taskDetails.type = result.data.StoryType.Name;                 
                this.taskDetails.workflowType = result.data.WorkflowType;
                this.titleAfterEdit = result.data.Title;
                this.items = result.data.Fields;
                // Ticket #113
                this.followers = result.data.Followers;
                // Ticket #113 ended
                this.arrayList = [];
                for (let i = 0; i < this.items.length; i++) {
                    var _id = this.items[i].Id;
                    var _title = this.items[i].title;
                     if (_title == "Total Estimate Points" && this.taskDetails.type == "Task") {
                     setTimeout(() => {
                         this.isTask=true;
                          document.getElementById("item_7").style.display = 'none';
                       //  document.getElementById("item_7").style.display = 'none';

                         
                         
                     }, 300)
                 }
                     if (_title == "Total Estimate Points" && this.items[i].value == ""){
                      setTimeout(() => {
                         document.getElementById("item_7").style.display = 'none';
                     }, 300)
                 }
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
        // Ticket #113
        this.follower_search_results=[];
        // Ticket #113 ended
        // Followers dummy
        // this.fList = [{ Name: "prabhu", id: 16, ProfilePic: "http://10.10.73.33/files/user/prabhu.png" },
        // { Name: "Satish Peta", id: 7, ProfilePic: "http://10.10.73.33/files/user/SatishPeta.png" }];
        // this.follower_search_results = this.fList;
        // this.getUsersForFollow();
        // Followers dummy ended
        
        // Total worked hours service method
        // globalService.getWorklog(this.constants.getWorkLog, this.navParams.get("id")).subscribe(
        //     (result) => {
        //         this.workedLogtime = result.data;
        //     }, (error) => {

        //     }
        // );
    }

    public isItTask(): boolean {
    return this.isTask;
  }

    public menuOpened() {
        StoryDetailsPage.isMenuOpen = true;
    }
    public menuClosed() {
        StoryDetailsPage.isMenuOpen = false;
    }
    public dateChange(event, index, fieldDetails) {
        var thisObj=this;
        this.globalService.leftFieldUpdateInline(this.constants.leftFieldUpdateInline, this.localDate, fieldDetails).subscribe(
            (result) => {
                setTimeout(() => {
                    document.getElementById("field_title_" + index).innerHTML = this.datePipe.transform(this.localDate, 'MMM-dd-yyyy');
                    this.enableDataPicker[index] = false;
                    document.getElementById("field_title_" + index).style.display = 'block';
                    if (result.data.activityData!='') {
                         thisObj.globalService.setActivity(result.data.activityData);
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
        this.autoCompleteProvider.getDataForSearch(this.ticketId);
    }
    ionViewDidEnter() {
        if (jQuery('#description').height() > 200) {
            jQuery('#description').css("height", "200px");
            jQuery('.show-morediv').show();
            jQuery('#show').show();
        }
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
        var thisObj=this;
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
                    if (result.data.activityData!='') {
                        thisObj.globalService.setActivity(result.data.activityData);
                    }
                    // if (result.data.activityData.referenceKey == -1) {
                    //     this.itemsInActivities.push(result.data.activityData.data);
                    // } else {
                    //     this.itemsInActivities[result.data.activityData.referenceKey]["PropertyChanges"].push(result.data.activityData.data);
                    // }
                }, 300);
            },
            (error) => {
                this.presentToast('Unsuccessful');
            });
    }
    public inputBlurMethod(event, index, fieldDetails) {
         var thisObj=this;
        this.globalService.leftFieldUpdateInline(this.constants.leftFieldUpdateInline, (event.target.value), fieldDetails).subscribe(
            (result) => {
                setTimeout(() => {
                    this.enableTextField[index] = false;
                    this.enableTextArea[index] = false;
                    document.getElementById("field_title_" + index).style.display = 'block';
                    document.getElementById("field_title_" + index).innerHTML = (event.target.value);
                    if (result.data.activityData!='') {
                        thisObj.globalService.setActivity(result.data.activityData);
                    }
                    // if (result.data.activityData.referenceKey == -1) {
                    //     this.itemsInActivities.push(result.data.activityData.data);
                    // } else {
                    //     this.itemsInActivities[result.data.activityData.referenceKey]["PropertyChanges"].push(result.data.activityData.data);
                    // }
                }, 200);
            },
            (error) => {
                this.presentToast('Unsuccessful');
            });
    }
    public openPopover(myEvent) {
         let popover = this.popoverCtrl.create(LogoutPage);
        popover.present({
            ev: myEvent
        });
    }

//    public titleEdit(event) {
//        //this.enableEdatable = true;
//    }

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
                    } 
                     for (let data of result.getFieldDetails) {
                            this.displayFieldvalue.push(data);
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
//    public navigateToParentComment(parentCommentId) {
//        jQuery("#"+parentCommentId)[0].scrollIntoView({
//            behavior: "smooth", // or "auto" or "instant"
//            block: "start" // or "end"
//        });
//    }
//    public replyComment(commentId) {
//        jQuery(".commentAction").removeClass("fab-close-active");
//        jQuery(".fab-list-active").removeClass("fab-list-active");
//        this.replyToComment = commentId;
//        this.replying = true;
//        jQuery("#commentEditorArea").addClass("replybox");
//        this.content.resize();
//        setTimeout(function(){
//            jQuery("#uploadAndSubmit")[0].scrollIntoView({
//                behavior: "smooth", // or "auto" or "instant"
//                block: "end" // or "start"
//            });
//        },500);
//    }
//    public cancelReply() {
//        this.replying = false;
//        this.replyToComment = -1;
//        jQuery("#commentEditorArea").removeClass("replybox");
//    }
//    public presentConfirmDelete(commentId, slug) {
//        jQuery(".commentAction").removeClass("fab-close-active");
//        jQuery(".fab-list-active").removeClass("fab-list-active");
//        let alert = this.alertController.create({
//            title: 'Confirm Delete',
//            message: 'Do you want to delete this comment?',
//            buttons: [
//            {
//                text: 'CANCEL',
//                role: 'cancel',
//                handler: () => {}
//            },
//            {
//                text: 'OK',
//                handler: () => {
//                    this.deleteComment(commentId, slug);
//                }
//            }
//            ]
//        });
//        alert.present();
//    }
//    public deleteComment(commentId, slug) {
//        var editedContent= jQuery("#Activity_content_"+commentId+" .commentp").html();
//        var commentParams;
//        var parentCommentId;
//        if (this.itemsInActivities[commentId].Status == 2) {
//            parentCommentId = parseInt(this.itemsInActivities[commentId].ParentIndex);
//            commentParams = {
//                TicketId: this.taskDetails.ticketId,
//                Comment: {
//                    Slug: slug,
//                    ParentIndex: parentCommentId,
//                    CrudeCDescription:editedContent.replace(/^(((\\n)*<p>(&nbsp;)*<\/p>(\\n)*)+|(&nbsp;)+|(\\n)+)|(((\\n)*<p>(&nbsp;)*<\/p>(\\n)*)+|(&nbsp;)+|(\\n)+)$/gm,""),
//                },
//            };
//        } else {
//            commentParams = {
//                TicketId: this.taskDetails.ticketId,
//                Comment: {
//                    Slug: slug,
//                    CrudeCDescription:editedContent.replace(/^(((\\n)*<p>(&nbsp;)*<\/p>(\\n)*)+|(&nbsp;)+|(\\n)+)|(((\\n)*<p>(&nbsp;)*<\/p>(\\n)*)+|(&nbsp;)+|(\\n)+)$/gm,""),
//                },
//            };
//        }
//        this.globalService.deleteCommentById(this.constants.deleteCommentById, commentParams).subscribe(
//            (result) => {
//                if (this.itemsInActivities[commentId].Status == 2) {
//                    this.itemsInActivities[parentCommentId].repliesCount--;
//                }
//                this.itemsInActivities.splice(commentId, 1);
//            }, (error) => {
//                this.presentToast('Unsuccessful');
//            }
//        );
//    }
//    public editComment(commentId) {
//        var thisObj = this;
//        jQuery(".commentAction").removeClass("fab-close-active");
//        jQuery(".fab-list-active").removeClass("fab-list-active");
//        jQuery("div").each(function (index,element) {
//            if(jQuery(element).hasClass("commentingTextArea")) {
//                var actionIdArray =  jQuery(element).attr('id').split('_'); 
//                var commentid = actionIdArray[1];
//                thisObj.editTheComment[commentid] = false;
//                thisObj.editCommentOpenClose[commentid] = false;
//            }
//        });
//        jQuery("#Actions_" + commentId + " .textEditor").val(this.itemsInActivities[commentId].CrudeCDescription);
//        this.editTheComment[commentId] = true;//show submit and cancel button on editor replace at the bottom
//        this.newCommentOpenClose = false;
//        this.editCommentOpenClose[commentId] = true;
//    }
//    public cancelEdit(commentId){
//        this.editTheComment[commentId] = false;//hide submit and cancel button on editor replace at the bottom
//        this.editCommentOpenClose[commentId] = false;
//        this.newCommentOpenClose = true;
//    }
//    public showSubmit(commentId){
//        if(commentId==-1){
//            this.newSubmitOpenClose = false;
//        }
//        else{
//            this.editSubmitOpenClose = false; 
//        }
//    }
//    public submitComment() {
//        alert("submit edit from story details");
//        var commentText = jQuery(".uploadAndSubmit .textEditor").val();
//        if (commentText != "" && commentText.trim() != "") {
//            this.commentDesc = "";
//            jQuery("#commentEditorArea").removeClass("replybox");
//            var commentedOn = new Date();
//            var formatedDate = (commentedOn.getMonth() + 1) + '-' + commentedOn.getDate() + '-' + commentedOn.getFullYear();
//            var commentData = {
//                TicketId: this.taskDetails.ticketId,
//                Comment: {
//                    CrudeCDescription: commentText.replace(/^(((\\n)*<p>(&nbsp;)*<\/p>(\\n)*)+|(&nbsp;)+|(\\n)+)|(((\\n)*<p>(&nbsp;)*<\/p>(\\n)*)+|(&nbsp;)+|(\\n)+)$/gm, ""),//.replace(/(<p>(&nbsp;)*<\/p>)+|(&nbsp;)+/g,""),
//                    CommentedOn: formatedDate,
//                    ParentIndex: "",
//                    Reply:this.replying,
//                    OriginalCommentorId:""
//                },
//            };
//            if (this.replying == true) {
//                if (this.replyToComment != -1) {
//                    commentData.Comment.OriginalCommentorId = jQuery("#replySnippetContent").attr("class");
//                    commentData.Comment.ParentIndex = this.replyToComment + "";
//                }
//            }
//            this.globalService.submitComment(this.constants.submitComment, commentData).subscribe(
//                (result) => {
//                    this.itemsInActivities.push(result.data);
//                    if (this.replying == true) {
//                        this.itemsInActivities[this.replyToComment].repliesCount++;
//                    }
//                    this.replying = false;
//                    jQuery(".uploadAndSubmit .textEditor").val('');
//                }, (error) => {
//                    this.presentToast('Unsuccessful');
//                }
//            );
//        }
//    }
//    public submitEditedComment(commentId, slug) {
//        var editedContent = jQuery("#Actions_" + commentId + " .textEditor").val();
//        if (editedContent != "" && editedContent.trim() != "") {
//            var commentedOn = new Date();
//            var formatedDate = (commentedOn.getMonth() + 1) + '-' + commentedOn.getDate() + '-' + commentedOn.getFullYear();
//            var commentData = {
//                TicketId: this.taskDetails.ticketId,
//                Comment: {
//                    CrudeCDescription: editedContent.replace(/^(((\\n)*<p>(&nbsp;)*<\/p>(\\n)*)+|(&nbsp;)+|(\\n)+)|(((\\n)*<p>(&nbsp;)*<\/p>(\\n)*)+|(&nbsp;)+|(\\n)+)$/gm, ""),
//                    CommentedOn: formatedDate,
//                    ParentIndex: "",
//                    Slug: slug
//                },
//            };
//            this.globalService.submitComment(this.constants.submitComment, commentData).subscribe(
//                (result) => {
//                    this.itemsInActivities[commentId].CrudeCDescription = result.data.CrudeCDescription;
//                    this.itemsInActivities[commentId].CDescription = result.data.CDescription;
//                    this.editTheComment[commentId] = false;//hide submit and cancel button on editor replace at the bottom
//                    this.editCommentOpenClose[commentId] = false;
//                    this.newCommentOpenClose = true;
//                }, (error) => {
//                    this.presentToast('Unsuccessful');
//                }
//            );
//        }
//    }
//    public presentActionSheet(comeFrom: string, where:string, comment:string) {
//        let actionSheet = this.actionSheetCtrl.create({
//            title: 'Select Image Source',
//            buttons: [
//                {
//                    text: 'Load from Library',
//                    handler: () => {
//                        this.takePicture(this.camera.PictureSourceType.PHOTOLIBRARY,comeFrom, where, comment);
//                    }
//                },
//                {
//                    text: 'Use Camera',
//                    handler: () => {
//                        this.takePicture(this.camera.PictureSourceType.CAMERA,comeFrom, where, comment);
//                    }
//                },
//                {
//                    text: 'Cancel',
//                    role: 'cancel'
//                }
//            ]
//            });
//        actionSheet.present();
//    }
//    public takePicture(sourceType,comeFrom: string, where:string, comment:string) {
//        var options = {
//            quality: 100,
//            sourceType: sourceType,
//            destinationType: this.camera.DestinationType.FILE_URI,
//            encodingType: this.camera.EncodingType.JPEG,
//            saveToPhotoAlbum: false,
//            correctOrientation: true,
//        };
//        this.camera.getPicture(options).then((imagePath) => {
//            if (this.platform.is('android') && sourceType === this.camera.PictureSourceType.PHOTOLIBRARY) {
//                this.filePath.resolveNativePath(imagePath).then((filePath) => {
//                    let correctPath = filePath.substr(0, filePath.lastIndexOf('/') + 1);
//                    let currentName = imagePath.substring(imagePath.lastIndexOf('/') + 1, imagePath.lastIndexOf('?'));
//                    this.copyFileToLocalDir(correctPath, currentName, this.createFileName(currentName), comeFrom, where, comment);
//                }, (err) => {});
//            } else {
//                var currentName = imagePath.substr(imagePath.lastIndexOf('/') + 1);
//                var correctPath = imagePath.substr(0, imagePath.lastIndexOf('/') + 1);
//                this.copyFileToLocalDir(correctPath, currentName, this.createFileName(currentName), comeFrom, where, comment);
//            }
//        }, (err) => {});
//    }
//    private createFileName(originalName) {
//        var d = new Date(),
//        n = d.getTime(),
//        newFileName =  "image"+n;
//        return newFileName;
//    }
//    private copyFileToLocalDir(namePath, currentName, newFileName, comeFrom: string, where:string, comment:string) {
//        this.file.copyFile(namePath, currentName, cordova.file.dataDirectory, newFileName).then(success => {
//            this.lastImage = newFileName;
//            this.uploadImage(currentName, newFileName, comeFrom, where, comment);
//        }, error => {});
//    }
//    public pathForImage(img) {
//        if (img === null) {
//            return '';
//        } else {
//            return cordova.file.dataDirectory + img;
//        }
//    }
//    public onProgressNew = (progressEvent: ProgressEvent) : void => {
//        this.ngZone.run(() => {
//            if (progressEvent.lengthComputable) {
//                let progress = Math.floor(progressEvent.loaded / progressEvent.total * 100);
//                this.progressNew = progress;
//                document.getElementById('progressFileUploadNew').innerHTML = progress + "% loaded...";
//            } else {
//                if (document.getElementById('progressFileUploadNew').innerHTML == "") {
//                    document.getElementById('progressFileUploadNew').innerHTML = "Loading";
//                } else {
//                    document.getElementById('progressFileUploadNew').innerHTML += ".";
//                }
//            }
//        });
//    }
//    public onProgressEdit = (progressEvent: ProgressEvent) : void => {
//        this.ngZone.run(() => {
//            if (progressEvent.lengthComputable) {
//                let progress = Math.floor(progressEvent.loaded / progressEvent.total * 100);
//                this.progressEdit = progress;
//                document.getElementById('progressFileUploadEdit').innerHTML = progress + "% loaded...";
//            } else {
//                if (document.getElementById('progressFileUploadEdit').innerHTML == "") {
//                    document.getElementById('progressFileUploadEdit').innerHTML = "Loading";
//                } else {
//                    document.getElementById('progressFileUploadEdit').innerHTML += ".";
//                }
//            }
//        });
//    }
//    public uploadImage(originalname, savedname, comeFrom: string, where:string, comment:string) {
//        var url = this.constants.filesUploading;
//        var targetPath = this.pathForImage(this.lastImage);
//        var filename = this.lastImage;
//        var options = {
//            fileKey: "commentFile",
//            fileName: filename,
//            chunkedMode: false,
//            mimeType: "image/jpeg",
//            params : {'filename': filename,'directory':this.constants.fileUploadsFolder,'originalname': originalname}
//        };
//       // const fileTransfer = new Transfer();
//        const fileTransfer: TransferObject = this.transfer.create();
//        if(where == "comments"){
//            fileTransfer.onProgress(this.onProgressNew);
//        }
//        if(where=="edit_comments"){
//            fileTransfer.onProgress(this.onProgressEdit);
//        }
//        fileTransfer.upload(targetPath, url, options).then(
//            (data) => {
//                var statusUpload = this.uploadedInserver(data, comeFrom, where, comment);
//                if(statusUpload=='uploaded'){
//                    if(where == "comments"){
//                        this.progressNew = 0;
//                        document.getElementById('progressFileUploadNew').innerHTML = "";
//                    }
//                    if(where=="edit_comments"){
//                        this.progressEdit = 0;
//                        document.getElementById('progressFileUploadEdit').innerHTML = "";
//                    }
//                }else if(statusUpload=='notuploaded'){
//                    this.presentToast('Unable to upload the image.');
//                }
//            }, (err) => {
//                this.presentToast('Unable to upload the image.');
//        });
//    }
//    public uploadedInserver(dataUploaded, comeFrom: string, where:string, comment:string){
//        var serverResponse = JSON.parse(dataUploaded.response);
//        if (serverResponse['status'] == '1') {
//            var editor_contents;
//            var appended_content;
//            if(where=="edit_comments"){
//                editor_contents = jQuery("#Actions_"+comment+" .textEditor").val();
//            }
//            var uploadedFileExtension = (serverResponse['originalname']).split('.').pop();
//            if (uploadedFileExtension == "png" || uploadedFileExtension == "jpg" || uploadedFileExtension == "jpeg" || uploadedFileExtension == "gif") {
//                if (where == "comments") {
//                    this.commentDesc = this.commentDesc + "[[image:" +serverResponse['path'] + "|" + serverResponse['originalname'] + "]] ";
//                    this.newSubmitOpenClose = false;
//                } else if (where == "edit_comments") {
//                    appended_content = editor_contents + "[[image:" +serverResponse['path'] + "|" + serverResponse['originalname'] + "]] ";
//                    jQuery("#Actions_" + comment + " .textEditor").val(appended_content);
//                    this.editSubmitOpenClose = false;
//                } 
//            }
//            return 'uploaded';
//        }else{
//            return 'notuploaded';
//        }
//    }
    // Followers dummy
//    public getUsersForFollow() {
//        this.follower_search_results = [];
//        var addFollowerData = {
//            ticketId: this.taskDetails.ticketId,
//            projectId: 1,
//            searchValue: "madan"
//        };
//        this.globalService.getUsersForFollow(this.constants.getUsersForFollow, addFollowerData).subscribe(
//            (result) => {
//                if (result.statusCode == 200) {
//                    var fList: any = [];
//                    for (var l = 0; l < result.data.length; l++) {
//                        fList.push({ Name: result.data[l].Name, id: result.data[l].Id, ProfilePic: result.data[l].ProfilePic });
//                    }
//                    this.follower_search_results = fList;
//                } else {
//                    console.log("service failed");
//                }
//            },
//            (error) => {
//                console.log("error in getUsersForFollow");
//            }
//        );
//    }
//    public addFollower(followerId) {
//        var followerData = {
//            ticketId: this.taskDetails.ticketId,
//            collaboratorId: followerId,
//        };
//        this.globalService.makeUsersFollowTicket(this.constants.makeUsersFollowTicket, followerData).subscribe(
//            (result) => {
//                if (result.statusCode == 200) {
//                    this.followers.push(result.data);
//                }
//            },
//            (error) => {
//                console.log("error in makeUsersFollowTicket");
//            }
//        );
//    }
//    public presentConfirmRemoveFollower(followerId) {
//        let alert = this.alertController.create({
//            title: 'Confirm Remove Follower',
//            message: 'Do you want to delete this follower?',
//            buttons: [
//            {
//                text: 'CANCEL',
//                role: 'cancel',
//                handler: () => {}
//            },
//            {
//                text: 'OK',
//                handler: () => {
//                    this.removeFollower(followerId);
//                }
//            }
//            ]
//        });
//        alert.present();
//    }
//    public removeFollower(followerId) {
//        var followerData = {
//            icketId: this.taskDetails.ticketId,
//            collaboratorId: followerId
//        };
//        this.globalService.makeUsersUnfollowTicket(this.constants.makeUsersUnfollowTicket, followerData).subscribe(
//            (result) => {
//                if (result.statusCode == 200) {
//                    jQuery("#followerdiv_" + followerId).remove();
//                    this.followers = this.followers.filter(function (el) {
//                        return el.FollowerId !== followerId;
//                    });
//                }
//            },
//            (error) => {
//                console.log("error in makeUsersUnfollowTicket");
//            }
//        );
//    }
//    itemCustomSelected($event){
//        this.addFollower($event.Id);
//        this.searchbar.clearValue();
//    }
    // onInput($event){
    //     this.getUsersForFollowEvent($event);
    // }
    // public getUsersForFollowEvent($event){
    //     console.log('getUsersForFollowEvent : '+JSON.stringify($event));
    // }
    // Followers dummy ended

    // Ticket #113
    // public checkFollower(followerId){
    //     if(jQuery("#check_"+followerId).hasClass("glyphicon glyphicon-ok")){
    //         jQuery("#check_"+followerId).removeClass("glyphicon glyphicon-ok");
    //         var followerData = {
    //             TicketId:this.taskDetails.ticketId,
    //             collaboratorId:followerId
    //         };
    //         this.globalService.makeUsersUnfollowTicket(this.constants.makeUsersUnfollowTicket, followerData).subscribe(
    //             (result) => {
    //                 if(result.statusCode==200){
    //                     jQuery("#followerdiv_"+followerId).remove();
    //                     this.followers = this.followers.filter(function(el) {
    //                         return el.FollowerId !== followerId;
    //                     });
    //                 }
    //             },
    //             (error)=> {
    //                 console.log("error in getUsersForFollow");
    //             }
    //         );
    //     }else{
    //         jQuery("#check_"+followerId).addClass("glyphicon glyphicon-ok");
    //         var followerData = {
    //             TicketId:this.taskDetails.ticketId,
    //             collaboratorId:followerId,
    //         };
    //         this.globalService.makeUsersFollowTicket(this.constants.makeUsersFollowTicket, followerData).subscribe(
    //             (result) => {
    //                 if(result.statusCode==200){
    //                     this.followers.push(result.data);  
    //                 }
    //             },
    //             (error)=> {
    //                 console.log("error in getUsersForFollow");
    //             }
    //         );
    //     }
    // }

    // public getUsersForFollow(event: any){
    //     this.follower_search_results=[];
    //     // set val to the value of the searchbar
    //     let val = event.target.value;
    //     if(val.length>=2){
    //         var defaultUserList:any=[];
    //         for(var x=0;x< this.followers.length;x++){
    //             defaultUserList.push(this.followers[x].FollowerId);
    //             defaultUserList.push(this.followers[x].CreatedBy);
    //         }
    //         var addFollowerData = {
    //             TicketId:this.taskDetails.ticketId,
    //             ProjectId:1,
    //             DafaultUserList:defaultUserList,
    //             SearchValue:val
    //         };
    //         this.globalService.getUsersForFollow(this.constants.getUsersForFollow,addFollowerData).map(
    //             (result) => {
    //                 if (result.statusCode == 200) {
    //                     var fList:any=[];
    //                     for(var l=0;l< result.data.length;l++){
    //                         fList.push({Name:result.data[l].Name,id:result.data[l].Id,ProfilePic:result.data[l].ProfilePic});
    //                     }
    //                     this.follower_search_results=fList;
    //                 } else {
    //                     console.log("service failed");
    //                 }
    //             },
    //             (error) => {
    //                 console.log("error in getUsersForFollow");
    //             }
    //         );
    //     }
    // }
    // Ticket #113 ended
    //workLog
    // public inputWorkLog(event, index) {
    //     console.log("the details " + JSON.stringify(this.ticketId));
    //     this.globalService.insertTimelog(this.constants.insertTimeLog, this.ticketId, this.inputHourslog).subscribe(
    //         (result) => {
    //             setTimeout(() => {
    //                 // document.getElementById("logHourDetails_input_" + index).style.display = 'block';
    //                 // document.getElementById("logHourDetails_input_" + index).innerHTML = this.workedLogtime.workHours;
    //                 this.inputHourslog = null;
    //                 this.workedLogtime = result.data;
    //                 // this.workedLogtime.TotalTimeLog = result.data.TotalTimeLog;
    //                 // this.individualitems = result.data.individualLog;

    //             }, 200);
    //         },
    //         (error) => {
    //             this.presentToast('Unsuccessful');
    //         });
    // }
//     onTabSelect(tab: { index: number; id: string; }) {
//      console.log(`Selected tab: `, tab);
//     }
}
