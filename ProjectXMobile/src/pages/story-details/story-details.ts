import { Component } from '@angular/core';
import { ToastController } from 'ionic-angular';
import { NavController, ModalController, NavParams, MenuController, LoadingController, PopoverController, ViewController } from 'ionic-angular';

import { Globalservice } from '../../providers/globalservice';
import { Constants } from '../../providers/constants';
import { PopoverPage } from '../popover/popover';
import { Storage } from "@ionic/storage";
import {CustomModalPage} from '../custom-modal/custom-modal';

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
    public filesToUpload: Array<File>;
    public fileUploadStatus:boolean = false;
    private ticketEditableDesc="";
    // Ticket #91 ended
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
        private modalController: ModalController,
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
        // Ticket #91
        // Activities
        globalService.getTicketActivity(this.constants.getTicketActivity, this.navParams.get("id")).subscribe( 
            (result)=>{
                // console.log("the result in ticket activities " + JSON.stringify(result.data.Activities));
                this.itemsInActivities = result.data.Activities;
            }, (error) => {
                // loader.dismiss();
                 console.log("the error in ticket activities " + JSON.stringify(error));
            }
         );
        // Ticket #91 ended
    }
    
   public dateChange(event, index, fieldDetails) {

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

        jQuery(document).ready(function () {
            jQuery('.jquery-notebook.editor').notebook({
                autoFocus: true,
                placeholder: 'Type something awesome...'
            });
        });


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
                document.getElementById("field.title_field.id_" + index).innerHTML = event.Name;
                    // document.getElementById("item_" + index).classList.remove("item-select");
                    // this.itemsInActivities.push(result.data.activityData.data);
                    if (result.data.activityData.referenceKey == -1) {
                        this.itemsInActivities.push(result.data.activityData.data);
                }
                else {
                    this.itemsInActivities[result.data.activityData.referenceKey]["PropertyChanges"].push(result.data.activityData.data);
                   // console.log("the final Array " + JSON.stringify(this.itemsInActivities));
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
        
    openOptionsModal(fieldDetails, index){
        console.log("the model present");
            
        if ((fieldDetails.readOnly == 0) && ((fieldDetails.fieldType == "List") || (fieldDetails.fieldType == "Team List") || (fieldDetails.fieldType == "Bucket"))) {
            this.globalService.getFieldItemById(this.constants.fieldDetailsById, fieldDetails).subscribe(
                (result) => {
                    if(fieldDetails.fieldType == "Team List"){
                        this.displayFieldvalue.push({"Id":"","Name":"--none--","Email":"null"})
                    for(let data of result.getFieldDetails){
                        this.displayFieldvalue.push(data);
   }                        
                } else { 
                for(let data of result.getFieldDetails){
                            this.displayFieldvalue.push(data);
                    } 
                }
                     
                    let optionsModal = this.modalController.create(CustomModalPage, {activeField: fieldDetails, activatedFieldIndex: index, displayList: this.displayFieldvalue });
                        optionsModal.onDidDismiss((data) => {
                            if(data != null && (data.Name != data.previousValue)){
                                this.changeOption(data, index, fieldDetails);
                            }
                            this.displayFieldvalue = [];
                        }); 
                    optionsModal.present();
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
    
    
    // Ticket #91
    // Comments
    navigateToParentComment(parentCommentId) {
        console.log('navigateToParentComment : ' + parentCommentId);
        // var scrolltoelement = document.getElementById("#"+parentCommentId);
        // jQuery('html, body').animate({
        //         scrollTop: jQuery("#" + parentCommentId).offset().top
        // }, 1000);
    } 
    replyComment(commentId) {
        console.log('replyComment : ' + commentId);
        this.replyToComment = commentId;
        this.replying = true;
        this.commentAreaColor = jQuery("#commentEditorArea").css("background");
        jQuery("#commentEditorArea").addClass("replybox");
        // var scrolltoelement = document.getElementById("#commentEditorArea");
        // jQuery('html, body').animate({
        //         scrollTop: jQuery("#commentEditorArea").offset().top
        // }, 1000);
    }
    cancelReply(){
        console.log('cancelReply');
        this.replying = false;
        this.replyToComment = -1;
        jQuery("#commentEditorArea").removeClass("replybox");
    }
    deleteComment(commentId,slug){
        console.log("deleteComment : "+commentId+"-"+slug);
        var commentParams;
        var parentCommentId;
        if(this.itemsInActivities[commentId].Status == 2){
            parentCommentId = parseInt(this.itemsInActivities[commentId].ParentIndex);
            commentParams = {
                TicketId:this.taskDetails.ticketId,
                Comment:{
                    Slug:slug,
                    ParentIndex:parentCommentId
                },
            };
        }else{
            commentParams = {
                TicketId:this.taskDetails.ticketId,
                Comment:{
                    Slug:slug
                },
            };
        }
        this.globalService.deleteCommentById(this.constants.deleteCommentById, commentParams).subscribe( 
            (result)=>{
                if(this.itemsInActivities[commentId].Status == 2){
                    this.itemsInActivities[parentCommentId].repliesCount--;
                }
                this.itemsInActivities.splice(commentId,1);
            }, (error) => {
                 console.log("the error in deleteCommentById " + JSON.stringify(error));
            }
         );
    }
    public editComment(commentId){
        console.log("editComment : "+commentId);
        // var comment_div=document.getElementById("Activity_content_"+commentId);
        jQuery("#Actions_"+commentId+" .textEditor").val(this.itemsInActivities[commentId].CrudeCDescription);
        this.editTheComment[commentId]=true;//show submit and cancel button on editor replace at the bottom
    }
    public cancelEdit(commentId){
        console.log("cancelEdit : "+commentId);
        // jQuery("#Actions_"+commentId+" .textEditor").val('');
        this.editTheComment[commentId]=false;//hide submit and cancel button on editor replace at the bottom
   }
//    Check and edit
    public submitComment(){
        console.log("submitComment");
        var commentText = jQuery(".uploadAndSubmit .textEditor").val();
        if(commentText != "" && commentText.trim() != ""){
            this.commentDesc="";
            jQuery("#commentEditorArea").removeClass("replybox");
            var commentedOn = new Date();
            var formatedDate =(commentedOn.getMonth() + 1) + '-' + commentedOn.getDate() + '-' +  commentedOn.getFullYear();
            var commentData = {
                TicketId:this.taskDetails.ticketId,
                Comment:{
                    CrudeCDescription:commentText.replace(/^(((\\n)*<p>(&nbsp;)*<\/p>(\\n)*)+|(&nbsp;)+|(\\n)+)|(((\\n)*<p>(&nbsp;)*<\/p>(\\n)*)+|(&nbsp;)+|(\\n)+)$/gm,""),//.replace(/(<p>(&nbsp;)*<\/p>)+|(&nbsp;)+/g,""),
                    CommentedOn:formatedDate,
                    ParentIndex:""
                },
            };
            if(this.replying == true){
                if(this.replyToComment != -1){
                    commentData.Comment.ParentIndex=this.replyToComment+"";
                }
            }
            this.globalService.submitComment(this.constants.submitComment, commentData).subscribe( 
                (result)=>{
                    this.itemsInActivities.push(result.data);
                    if(this.replying == true){
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

    public submitEditedComment(commentId,slug){
        console.log("submitEditedComment : "+commentId+"-"+slug);
        var editedContent = jQuery("#Actions_"+commentId+" .textEditor").val();
        if(editedContent != "" && editedContent.trim() != ""){
            var commentedOn = new Date();
            var formatedDate =(commentedOn.getMonth() + 1) + '-' + commentedOn.getDate() + '-' +  commentedOn.getFullYear();
            var commentData = {
                TicketId:this.taskDetails.ticketId,
                Comment:{
                    CrudeCDescription:editedContent.replace(/^(((\\n)*<p>(&nbsp;)*<\/p>(\\n)*)+|(&nbsp;)+|(\\n)+)|(((\\n)*<p>(&nbsp;)*<\/p>(\\n)*)+|(&nbsp;)+|(\\n)+)$/gm,""),
                    CommentedOn:formatedDate,
                    ParentIndex:"",
                    Slug:slug
                },
            };
            this.globalService.submitComment(this.constants.submitComment, commentData).subscribe( 
                (result)=>{
                    this.itemsInActivities[commentId].CrudeCDescription = result.data.CrudeCDescription;
                    this.itemsInActivities[commentId].CDescription = result.data.CDescription;
                    // jQuery("#Actions_"+commentId+" .textEditor").val('');
                    this.editTheComment[commentId]=false;//hide submit and cancel button on editor replace at the bottom
                }, (error) => {
                    console.log("the error in submitComment " + JSON.stringify(error));
                }
            );
        }
    }

    public fileUploadEvent(fileInput: any, comeFrom: string, where:string,comment:string):void {
        var editor_contents;
        var appended_content;
        console.log("==FileInput=="+JSON.stringify(fileInput));
        if(where=="edit_comments"){
            editor_contents = jQuery("#Actions_"+comment+" .textEditor").val();
            fileInput.preventDefault();
        }
        if(comeFrom == 'fileChange'){
                this.filesToUpload = <Array<File>> fileInput.target.files;
        } else{
                this.filesToUpload = <Array<File>> fileInput.target.files;
        }
        this.globalService.makeFileRequest(this.constants.filesUploading, [], this.filesToUpload).then(
            (result :Array<any>) => {
                console.log("the result " + JSON.stringify(result));
                for(var i = 0; i<result.length; i++){
                    console.log('for : '+i);
                    var uploadedFileExtension = (result[i].originalname).split('.').pop();
                    console.log('uploadedFileExtension : '+uploadedFileExtension);
                    if(uploadedFileExtension == "png" || uploadedFileExtension == "jpg" || uploadedFileExtension == "jpeg" || uploadedFileExtension == "gif") {
                        if(where =="comments"){
                            console.log('commentDesc 1 : '+this.commentDesc);
                            this.commentDesc = this.commentDesc + "[[image:" +result[i].path + "|" + result[i].originalname + "]] ";
                            console.log('commentDesc 2 : '+this.commentDesc);
                        }else if(where == "edit_comments"){
                            console.log('appended_content 1 : '+appended_content);
                            appended_content = editor_contents+"[[image:" +result[i].path + "|" + result[i].originalname + "]]"; 
                            jQuery("#Actions_"+comment+" .textEditor").val(appended_content);
                            console.log('appended_content 2 : '+appended_content);
                        }else{
                            console.log('ticketEditableDesc 1 : '+this.ticketEditableDesc);
                            this.ticketEditableDesc = this.ticketEditableDesc + "[[image:" +result[i].path + "|" + result[i].originalname + "]] ";
                            console.log('ticketEditableDesc 2 : '+this.ticketEditableDesc);
                        }
                    }else{
                        if(where =="comments"){
                            this.commentDesc = this.commentDesc + "[[file:" +result[i].path + "|" + result[i].originalname + "]] ";
                        }else if(where == "edit_comments"){
                            appended_content = editor_contents+"[[file:" +result[i].path + "|" + result[i].originalname + "]]";
                            jQuery("#Actions_"+comment+" .textEditor").val(appended_content);
                        }else{
                            this.ticketEditableDesc = this.ticketEditableDesc + "[[file:" +result[i].path + "|" + result[i].originalname + "]] ";
                        }
                    }
                }
            }, (error) => {
                this.ticketEditableDesc = this.ticketEditableDesc + "Error while uploading";
        });
    }
    // Ticket #91 ended
}
