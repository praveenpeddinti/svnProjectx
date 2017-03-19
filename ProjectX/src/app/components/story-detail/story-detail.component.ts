import { Component, OnInit,ViewChild,Input } from '@angular/core';
import { Router,ActivatedRoute } from '@angular/router';
import { Headers, Http } from '@angular/http';
import { AjaxService } from '../../ajax/ajax.service';
import {AccordionModule,DropdownModule,SelectItem} from 'primeng/primeng';
import { FileUploadService } from '../../services/file-upload.service';
import { GlobalVariable } from '../../config';
import { MentionService } from '../../services/mention.service';
declare var jQuery:any;
declare const CKEDITOR;
@Component({
  selector: 'app-story-detail',
  templateUrl: './story-detail.component.html',
  styleUrls: ['./story-detail.component.css'],
  
})
export class StoryDetailComponent implements OnInit {

private text:string;
private search_results:string[];

private getAllData=  JSON.parse(localStorage.getItem('user'));

private editableSelect= "";
public blurTimeout=[];
@ViewChild('detailEditor')  detail_ckeditor; // reference for editor in view.
  public clickedOutside = false;
  public dragTimeout;
  public minDate:Date;
  private ticketData;
  private ticketId;
  private fieldsData = [];
  private showMyEditableField =[];
  private showMyEditableTaskField =[];
  private ticketEditableDesc="";
  private ticketDesc = "";
  private ticketCrudeDesc = "";
  private showDescEditor=true;
  private childTasksArray=[];
  private childTaskData="";
  public commentsList=[];
  private taskFieldsEditable=[];
  public totalWorkLog = '0.00';
  public individualLog=[];
  public isTimeValidErrorMessage;
  public relatedTaskArray=[];


  //Configuration varibale for CKEDITOR in detail page.
  private toolbarForDetail={toolbar : [
    [ 'Heading 1', '-', 'Bold','-', 'Italic','-','Underline','Link','NumberedList','BulletedList' ]
],removePlugins:'elementspath,magicline',resize_enabled:true};

  //common array for Dropdown's option.
  private dropList=[];

  public filesToUpload: Array<File>;
  public hasBaseDropZoneOver:boolean = false;
  public hasBaseDropZoneOverComment:boolean = false;
  public hasFileDroped:boolean = false;
  public fileUploadStatus:boolean = false;
  public hide:boolean=false;//added by Ryan
  public attachmentsData=[];

  constructor(private fileUploadService: FileUploadService, private _ajaxService: AjaxService,
    public _router: Router,private mention:MentionService,
    private http: Http,private route: ActivatedRoute) {
       this.filesToUpload = [];
    }

 private calenderClickedOutside = false;
 private comment_status:boolean=true;

  ngOnInit() {
     var thisObj = this;
    jQuery(document).ready(function(){
      jQuery(document).bind("click",function(event){                                                                                                                                                                                                                                                            //sets the flag, to know if the click happend on the dropdown or outside  
          if(jQuery(event.target).closest('div.customdropdown').length == 0){
          thisObj.clickedOutside = true;
          }else{
          thisObj.clickedOutside = false;
          }

          //sets the flag, to know if the click happend on the datepicker or outside
          if(jQuery(event.target).closest('p-calendar.primeDateComponent').length == 0){
          thisObj.calenderClickedOutside = true;
          }else{
          thisObj.calenderClickedOutside = false;
          }

      });
      jQuery("#collapse").show();//added by Ryan
      jQuery("#expand").hide();//added by Ryan
      jQuery('[id^=button_comment]').hide();
    });


    /** @Praveen P
     * Getting the TicketId for story dashboard
     */
      this.route.params.subscribe(params => {
            this.ticketId = params['id'];
        });

        
// alert("User Data---------->"+JSON.stringify(this.getAllData));
   console.log("+++++++++++++iniit+++++++++");

      var ticketIdObj={'ticketId': this.ticketId};
        this._ajaxService.AjaxSubscribe("story/get-ticket-details",ticketIdObj,(data)=>
        { 

            this.ticketData = data;
            this.ticketDesc = data.data.Description;
            this.ticketEditableDesc = this.ticketCrudeDesc = data.data.CrudeDescription;
            this.fieldsData = this.fieldsDataBuilder(data.data.Fields,data.data.TicketId);

            this.childTaskData=data.data.Tasks;
            // alert("dataaaaaaaa"+JSON.stringify(data.data.Tasks));
            this.childTasksArray=this.taskDataBuilder(data.data.Tasks);
          //  alert("subtasksdat"+JSON.stringify(this.childTasksArray));
             

            // this.commentsList = [];
            this._ajaxService.AjaxSubscribe("story/get-ticket-activity",ticketIdObj,(data)=>
            { 
              console.log(data.data.Activities);
              this.commentsList = data.data.Activities;
            });

        });
        this._ajaxService.AjaxSubscribe("story/get-work-log",ticketIdObj,(data)=>
            { 
               this.individualLog =data.data.individualLog;
                 if(data.data.TotalTimeLog > 0){
                  this.totalWorkLog = data.data.TotalTimeLog;
                 }
            });

        this._ajaxService.AjaxSubscribe("story/get-all-related-tasks",ticketIdObj,(result)=>
         { 
        
         this.relatedTaskArray=result.data;
        })
      this.minDate=new Date();

          //---------------------------- Attachments code---------------//
     /**
     * @author:Jagadish
     * @description: This is used to display Attachments
     */
       this._ajaxService.AjaxSubscribe("story/get-my-ticket-attachments",ticketIdObj,(data)=>
        { 
        if(data.statusCode == 200){
                         this.attachmentsData = data.data;                            
        } else {
            this.attachmentsData =[];
        }          
        });
    }

    /**
     * @author:Ryan Marshal
     * @description:In general,This is for getting the contents of CKEDITOR on various events and then performing 
     *              operations based on the requirement.Here,it is used for getting @mention capabilitly.
     */
    ngAfterViewInit()
    {
      CKEDITOR.on('instanceReady', (event)=>
      {
        
         event.editor.on('key',(evt)=>
         {
            var this_obj=this;
            var at_config = {
            at: "@",
            callbacks: {
                    remoteFilter: function(query, callback) {
                      if(query.length>0)
                      {
                        var post_data={ProjectId:1,search_term:query};
                        this_obj._ajaxService.AjaxSubscribe("story/get-collaborators",post_data,(data)=> {
                        var mention=[];
                        for(let i in data.data)
                        {
                          //mention.push(data.data[i].Name);
                          mention.push({"name":data.data[i].Name,"Profile":data.data[i].ProfilePic});
                        }
                      callback(mention);
                    });
                      }
                  }
                },
            editableAtwhoQueryAttrs: {
                    "data-fr-verified": true
            },
            displayTpl:"<li value='${name}' name='${name}'><img width='20' height='20' src='http://10.10.73.77${Profile}'/> ${name}</li>",
            }
            var editor=evt.editor;
            this.mention.load_atwho(editor,at_config);
        });

       
      })
    }

  /*
  * Description part
  */
  openDescEditor(){
    this.showDescEditor = false;
  }

  private descError="";
  submitDesc(){
    setTimeout(()=>{
      var editorData = this.detail_ckeditor.instance.getData();
          if(editorData != "" && jQuery(editorData).text().trim() != ""){
          this.descError = "";
        
        // Added by Padmaja for Inline Edit
        var postEditedText={
          isLeftColumn:0,
          id:'Description',
          value:editorData,
          TicketId:this.ticketId,
          EditedId:'desc'
        };
        this.postDataToAjax(postEditedText);
        this.showDescEditor = true;
        this.ticketCrudeDesc = editorData;//this.ticketEditableDesc;
        }else{
          this.descError = "Description cannot be empty.";
        }
},250);
  }

  cancelDesc(){
    this.descError = "";
    this.ticketEditableDesc = this.ticketCrudeDesc;

    this.showDescEditor = true;

  }

//------------------------Description part---------------------------------- 
/*
********/
//---------------------------Comments Part-------------------------------------

@ViewChild('commentEditor')  detail_comment_ckeditor;
public commentDesc = "";//"sdadas<img src='https://10.10.73.21/files/story/thumb1.png' height='50%' width='50%' />";

public commentCount = 0;
submitComment(){
var commentText = this.detail_comment_ckeditor.instance.getData();
// alert("****comment editor data***"+commentText);
// var commentPushData = {
//   text:commentText,//jQuery(commentText).html(),
//   id:this.commentCount++,
//   repliedToComment:"",
//   parentId:""
// };
// if(this.replying == true){
//   // commentPushData.text = "<div style='background:#C0C0C0;'>"+this.replyToComment.text+"</div>"+commentPushData.text;
//   commentPushData.repliedToComment=this.replyToComment.text
//   commentPushData.parentId = this.replyToComment.id;
// }
// alert("====comment data==>"+JSON.stringify(commentPushData));
// this.commentsList.push(commentPushData);
if(commentText != "" && jQuery(commentText).text().trim() != ""){
this.commentDesc="";

jQuery("#commentEditorArea").removeClass("replybox");
  console.log("comment is submitted");

var commentedOn = new Date()
var formatedDate =(commentedOn.getMonth() + 1) + '-' + commentedOn.getDate() + '-' +  commentedOn.getFullYear();
  var reqData = {
    TicketId:this.ticketId,
    Comment:{
      CrudeCDescription:commentText.replace(/^(((\\n)*<p>(&nbsp;)*<\/p>(\\n)*)+|(&nbsp;)+|(\\n)+)|(((\\n)*<p>(&nbsp;)*<\/p>(\\n)*)+|(&nbsp;)+|(\\n)+)$/gm,""),//.replace(/(<p>(&nbsp;)*<\/p>)+|(&nbsp;)+/g,""),
      CommentedOn:formatedDate,
      ParentIndex:""
    },
  };
  // alert(JSON.stringify(reqData));
  if(this.replying == true){
    if(this.replyToComment != -1){
    reqData.Comment.ParentIndex=this.replyToComment+"";
    }

  }
  this._ajaxService.AjaxSubscribe("story/submit-comment",reqData,(result)=>
        { 
          
          // alert("++++++++++++++++++++"+JSON.stringify(result));
          this.commentsList.push(result.data);
          if(this.replying == true){
            this.commentsList[this.replyToComment].repliesCount++;
          }
          
          this.replying = false;
          
        });
}
  
}


private replyToComment=-1;
private replying=false;
private commentAreaColor="";
replyComment(commentId){
// var commentEditorObject = document.getElementById("commentEditorArea");
// var offset = commentEditorObject.offsetTop;
// alert(commentId);
// var commentToReply = this.commentsList[commentId];//jQuery("#"+commentId+" #commentContent").html();
// this.replyToComment = this.commentsList[commentId];
// alert(JSON.stringify(this.commentsList[commentId]));
this.replyToComment = commentId;
this.replying = true;
// this.commentDesc = commentToReply+"<br/><img src='https://10.10.73.21/files/story/thumb1.png' height='50%' width='50%' />";
// alert(this.commentDesc)
this.commentAreaColor = jQuery("#commentEditorArea").css("background");
jQuery("#commentEditorArea").addClass("replybox");
jQuery('html, body').animate({
        scrollTop: jQuery("#commentEditorArea").offset().top
    }, 1000);

// jQuery.scrollTo(jQuery("#commentEditorArea"),500);
}


navigateToParentComment(parentId){
// alert(parentId+"---"+jQuery("#"+parentId).length);
// alert(jQuery("#"+parentId).offset().top);
  jQuery('html, body').animate({
        scrollTop: jQuery("#"+parentId).offset().top
    }, 1000);
}
cancelReply(){
  this.replying = false;
  this.replyToComment = -1;
 jQuery("#commentEditorArea").removeClass("replybox");

}
//------------------------------Comments Part end------------------------------------
/********
 */
/*
* Title part
*/
private showTitleEdit=true;
// private titleError="";
editTitle(){
  this.showTitleEdit = false;
}

closeTitleEdit(editedText){
        if(editedText !=""){
          // this.titleError="";
          document.getElementById(this.ticketId+"_title").innerHTML= editedText;
          this.showTitleEdit = true;
      // Added by Padmaja for Inline Edit
          var postEditedText={
            isLeftColumn:0,
            id:'Title',
            value:editedText,
            TicketId:this.ticketId,
            EditedId:'title'
          };
          this.postDataToAjax(postEditedText);
      }else{
        this.showTitleEdit = true;
        editedText = document.getElementById(this.ticketId+"_title").innerHTML;

      }
}
//------------------------------Title part-----------------------------------

//Navigate to Edit Page
  goToEditPage(){
    this._router.navigate(['story-edit',this.ticketId]);

  }

//Changes inline editable filed to thier respective edit modes - Left Column fields.
//Renders data to dropdowns dynamically.
  editThisField(event,fieldIndex,fieldId,fieldDataId,fieldTitle,renderType,where){ 
   // alert(event+fieldIndex+"--"+fieldId+"--"+fieldDataId+"--"+fieldTitle+"--"+renderType+"--");
    // this.dropList={};
     this.dropList=[];
    // var fieldName = fieldId.split("_")[1];alert(fieldName);
    var inptFldId = fieldId+"_"+fieldIndex;
    var q =0;
      for(let taskRow of this.taskFieldsEditable){
        
        for(let taskCol in taskRow){
          this.taskFieldsEditable[q][taskCol] = false;
        }
        q++;
      }
    if(where == "Tasks"){
      
      var row = fieldIndex.split("_")[0];
      var col = fieldIndex.split("_")[1];
      this.taskFieldsEditable[row][col]=true;

    }else{
      this.showMyEditableField[fieldIndex] = false;
    
   
   // this.showMyEditableTaskField[fieldIndex] = true;
    setTimeout(()=>{document.getElementById(inptFldId).focus();},150);
    }
    if(renderType == "select"){
        var reqData = {
          FieldId:fieldDataId,
          ProjectId:this.ticketData.data.Project.PId,
          TicketId:(where == "Tasks")?fieldId:this.ticketData.data.TicketId
        };
        //Fetches the field list data for current dropdown in edit mode.
        this._ajaxService.AjaxSubscribe("story/get-field-details-by-field-id",reqData,(data)=>
            { 
                // var currentId = document.getElementById(inptFldId+"_currentSelected").getAttribute("value");
                var listData = {
                  // currentSelectedId: (currentId != "" &&currentId != null )? currentId:"",
                  list:data.getFieldDetails
                };
                 var priority=(fieldTitle=="Priority"?true:false);
                this.dropList=this.prepareItemArray(listData.list,priority,fieldTitle);
                //alert("#"+inptFldId+" div");
                //sets the dropdown prefocused
                jQuery("#"+inptFldId+" div").click();
                
            });
    }else if(renderType == "date"){
      //sets the datepicker prefocused
      setTimeout(()=>{
        jQuery("#"+inptFldId+" span input").focus();
      },150);    
    }

 
    
  }

  dateBlur(event,fieldIndex){
    var thisobj = this;
    if(this.blurTimeout[fieldIndex] != undefined && this.blurTimeout[fieldIndex] != "undefined"){
        clearTimeout(this.blurTimeout[fieldIndex]);
        }
     this.blurTimeout[fieldIndex]= setTimeout(function(){
            if(thisobj.calenderClickedOutside == true){
            thisobj.showMyEditableField[fieldIndex] = true;
            }
        },1000);
    
  }
 
private dateVal = new Date();
//Restores the editable field to static mode.
//Also prepares the data to be sent to service to save the changes.
//This is common to left Column fields.
   restoreField(editedObj,restoreFieldId,fieldIndex,renderType,fieldId,where){
     var postEditedText={
                        isLeftColumn:1,
                        id:fieldId,
                        value:"",
                        TicketId:(where == "Tasks")?restoreFieldId.split("_")[0]:this.ticketId,
                        EditedId:restoreFieldId.split("_")[1]
                      };

          switch(renderType){
            case "input":
            case "textarea":
            document.getElementById(restoreFieldId).innerHTML = (editedObj == "") ? "--":editedObj;
            postEditedText.value = editedObj;
            break;
            
            case "select":
            var appendHtml = (restoreFieldId.split("_")[1] == "priority")?"&nbsp; <i class='fa fa-circle "+editedObj.text+"' aria-hidden='true'></i>":"";
            document.getElementById(restoreFieldId).innerHTML = (editedObj.text == ""||editedObj.text == "--Select a Member--") ? "--":editedObj.text+appendHtml;
            postEditedText.value = editedObj.value;
            break;

            case "date":
            var date = (this.dateVal.getMonth() + 1) + '-' + this.dateVal.getDate() + '-' +  this.dateVal.getFullYear();
            date = date.replace(/(\b\d{1}\b)/g, "0$1");
            document.getElementById(restoreFieldId).innerHTML = (date == "") ? "--":date;
            postEditedText.value = this.dateVal.toString();
            var rightNow = new Date();
            break;

          }
         if(where == "Tasks"){
          var row = fieldIndex.split("_")[0];
          var col = fieldIndex.split("_")[1];
          this.taskFieldsEditable[row][col]=false;

        }else{  
            this.showMyEditableField[fieldIndex] = true;
        }
        this.postDataToAjax(postEditedText);
  }

  closeCalendar(fieldIndex){

    this.showMyEditableField[fieldIndex] = true;
  }

//Dropdown's onFocus event
  dropdownFocus(event,fieldIndex,where){
     if(where == "Tasks"){
      var row = fieldIndex.split("_")[0];
      var col = fieldIndex.split("_")[1];
      this.taskFieldsEditable[row][col]=true;

    }else{ 
      for(var i in this.showMyEditableField){
      
        if(i != fieldIndex){
          this.showMyEditableField[i] = true;
        }
      }
      this.showMyEditableField[fieldIndex] = false;
    } 
  }

//Dropdown's onBlur event
  selectBlurField(event,fieldIndex,where){ 
   var thisobj = this;
   var i;
   if(where =="Tasks"){
     i = fieldIndex.split("_")[0];
   }else{
     i = fieldIndex;
   }
    if(this.blurTimeout[i] != undefined && this.blurTimeout[i] != "undefined"){
        clearTimeout(this.blurTimeout[i]);
        }
        this.blurTimeout[i]= setTimeout(function(){
              if(thisobj.clickedOutside == true){
                if(where == "Tasks"){
                  var row = fieldIndex.split("_")[0];
                  var col = fieldIndex.split("_")[1];
                  thisobj.taskFieldsEditable[row][col]=false;
                }else{
                 thisobj.showMyEditableField[fieldIndex] = true;
                }
              
            }

          },1000);

    }

//Processes the left column filed's data coming from the service 
//And creates a Common data array to render in Angular Views.
  fieldsDataBuilder(fieldsArray,ticketId){
    let fieldsBuilt = [];
    let data = {title:"",value:"",valueId:"",readonly:true,required:true,elId:"",fieldType:"",renderType:"",type:"",Id:""};
    for(let field of fieldsArray){
      if(field.field_name != "customfield_2"){
      data = {title:"",value:"",valueId:"",readonly:true,required:true,elId:"",fieldType:"",renderType:"",type:"",Id:""};
          switch(field.field_type){
            case "Text":
            case "TextArea":
            data.title = field.title;
            data.value = field.value;
            data.renderType = (field.field_type == "TextArea")?"textarea":"input";
            data.type="text";
            break;

            case "List":
            data.title = field.title;
            if(field.readable_value != false){
                data.value = field.readable_value.Name;
                data.valueId = field.readable_value.Id
            }
            data.renderType = "select";
            break;

            case "Numeric":
            data.title = field.title;
            data.value = field.value;
            data.renderType = "input";
            data.type="text";
            break;

            case "Date":
            data.title = field.title;
            data.value = field.readable_value;
            this.dateVal = field.readable_value;
            data.renderType = "date";
            data.type="date";
            break;

            case "DateTime":
            data.title = field.title;
            data.value = field.readable_value;
            data.renderType = "date";
            data.type="datetime";
            break;

            case "Team List":
            data.title = field.title;
            if(field.readable_value != false){
                data.value = field.readable_value.UserName;
                data.valueId = field.readable_value.CollaboratorId
            }
            data.renderType = "select";
            break;

            // case "Checkbox":
            // break;

            case "Bucket":
            data.title = field.title;
            if(field.readable_value != false){
              data.value = field.readable_value.Name;
              data.valueId = field.readable_value.Id
            }
            data.renderType = "select";
            break;

          }
          data.readonly = (field.readonly == 1)?true:false;
          data.required = (field.required == 1)?true:false;
          data.elId =  ticketId+"_"+field.field_name;
          data.Id = field.Id;
            data.fieldType = field.field_type;

            if(field.field_name == "dod"){
              data.renderType = "textarea";
          }

          fieldsBuilt.push(data);
          this.showMyEditableField.push((field.readonly == 1)?false:true);
      }
    }
    return fieldsBuilt;

  }

//Prepares the Custom Dropdown's Options array.
 public prepareItemArray(list:any,priority:boolean,status:string){
  var listItem=[];
     if(list.length>0){
       if(status == "Assigned to" || status == "Stake Holder"){
       listItem.push({label:"--Select a Member--", value:"",priority:priority,type:status});
       }
         for(var i=0;list.length>i;i++){
           listItem.push({label:list[i].Name, value:list[i].Id,priority:priority,type:status});
       }
     }
  return listItem;
}

//----------------------File Upload codes---------------------------------
public fileOverBase(fileInput:any,where:string,comment:string):void {
  if(where=="edit_comments"){
    jQuery("#"+comment).addClass("dragdrop","true");
  }else{
    this.hasBaseDropZoneOver = true;
  }
    
    if(this.dragTimeout != undefined && this.dragTimeout != "undefined"){
    clearTimeout(this.dragTimeout);
    }

}

public fileDragLeave(fileInput: any,where:string,comment:string){

var thisObj = this;
    if(this.dragTimeout != undefined && this.dragTimeout != "undefined"){
    clearTimeout(this.dragTimeout);
    }
     this.dragTimeout = setTimeout(function(){
       if(where=="edit_comments"){
        jQuery("#"+comment).removeClass("dragdrop");
      }else{
        thisObj.hasBaseDropZoneOver = false;
      }
     
    },500);
    
}



  public fileUploadEvent(fileInput: any, comeFrom: string,where:string,comment:string):void {
    var editor_contents;
    var appended_content;
    if(where=="edit_comments"){
      editor_contents=jQuery("#cke_"+comment).find("iframe").contents().find('body').html();
      // alert(editor_contents);
      fileInput.preventDefault();
    }
   if(comeFrom == 'fileChange'){
        this.filesToUpload = <Array<File>> fileInput.target.files;
   } else if(comeFrom == 'fileDrop'){
    //  alert(JSON.stringify(Object.keys(fileInput))+"**********************");
        this.filesToUpload = <Array<File>> fileInput.dataTransfer.files;
   } else{
        this.filesToUpload = <Array<File>> fileInput.target.files;
   }

        if(where=="edit_comments"){
             jQuery("#"+fileInput.target.id).removeClass("dragdrop","true");
          }

        this.hasBaseDropZoneOver = false;
        this.fileUploadStatus = true;
        this.fileUploadService.makeFileRequest(GlobalVariable.FILE_UPLOAD_URL, [], this.filesToUpload).then((result :Array<any>) => {
            for(var i = 0; i<result.length; i++){
                var uploadedFileExtension = (result[i].originalname).split('.').pop();
                if(uploadedFileExtension == "png" || uploadedFileExtension == "jpg" || uploadedFileExtension == "jpeg" || uploadedFileExtension == "gif") {
                    if(where =="comments"){
                      this.commentDesc = this.commentDesc + "[[image:" +result[i].path + "|" + result[i].originalname + "]] ";
                    }else if(where=="edit_comments"){
                      appended_content=editor_contents+"[[image:" +result[i].path + "|" + result[i].originalname + "]]"; 
                    jQuery("#cke_"+comment).find("iframe").contents().find('body').html(appended_content);
                    }else{
                      this.ticketEditableDesc = this.ticketEditableDesc + "[[image:" +result[i].path + "|" + result[i].originalname + "]] ";
                    }
                } else{
                    if(where =="comments"){
                      this.commentDesc = this.commentDesc + "[[file:" +result[i].path + "|" + result[i].originalname + "]] ";
                    }else if(where=="edit_comments"){
                      appended_content =editor_contents+"[[file:" +result[i].path + "|" + result[i].originalname + "]]";
                      jQuery("#cke_"+comment).find("iframe").contents().find('body').html(appended_content);
                    }else{
                      this.ticketEditableDesc = this.ticketEditableDesc + "[[file:" +result[i].path + "|" + result[i].originalname + "]] ";
                    }
                }
            }
            this.fileUploadStatus = false;
        }, (error) => {
            this.ticketEditableDesc = this.ticketEditableDesc + "Error while uploading";
            this.fileUploadStatus = false;
        });
}




//------------------------------------File Upload logics end-----------------------------------------------------

// Added by Padmaja for Inline Edit
//Common Ajax method to save the changes.
    public postDataToAjax(postEditedText){
       this._ajaxService.AjaxSubscribe("story/update-story-field-inline",postEditedText,(result)=>
        { 
          if(result.statusCode== 200){

         if(postEditedText.EditedId == "title" || postEditedText.EditedId == "desc")
                document.getElementById(this.ticketId+'_'+postEditedText.EditedId).innerHTML=result.data.updatedFieldData;



         //  this.commentsList = result.data.Activities;
            if(result.data.activityData.referenceKey == -1){
             this.commentsList.push(result.data.activityData.data);
            }
         this.commentsList[result.data.activityData.referenceKey]["PropertyChanges"].push(result.data.activityData.data);
          }
        });
    }

//Navigate back to dashboard page.
    public goBack()
    {
        this._router.navigate(['story-dashboard']);
    }


    public savechiledTask()
    {
       var title= jQuery('#childtitle').val();
       if(title !=""){
       var postTaskData={
            TicketId:this.ticketId,
            title:jQuery('#childtitle').val()
          };
          // var _this = this;
         // alert(JSON.stringify(postTaskData));
        this._ajaxService.AjaxSubscribe("story/save-chiled-task",postTaskData,(result)=>
        {
          var task=[];
          task.push(result.data.Tasks);
          var newChildData = this.taskDataBuilder(task);
          this.childTasksArray.push(newChildData[0]);
         });
       }else{
          alert("Please enter Title"); 
       }
    }

    navigateToChildDetail(childTicketId){
      this._router.navigate(['story-edit',childTicketId]);
    }
    /**
     * @author:suryaprakash
     * @description : This is used to capture search related story
     */
    public searchRelateTask(event)
    {
        var post_data={
        'projectId':1,
        'sortvalue':'Title',
        'ticketId':this.ticketId,
        'searchString':event.query
    }
    let prepareSearchData = [];
      //  this.search_results=data;GetTicketDetails get-all-ticket-details-for-search
        this._ajaxService.AjaxSubscribe("story/get-all-ticket-details-for-search",post_data,(result)=>
         { 
           var subTaskData = result.data;
            for(let subTaskfield of subTaskData){
               var currentData = '# '+subTaskfield.TicketId+' '+subTaskfield.Title;
                 prepareSearchData.push(currentData);
            }
           this.search_results=prepareSearchData;
         });
    }


    /**
     * @author:Ryan
     * @description: This is used to expand the div section
     */
    public expand()
    {
      jQuery(".main_div").stop().slideToggle();
      jQuery("#collapse").show();
      jQuery("#expand").hide();
    }

    /**
     * @author:Ryan
     * @description : This is used to collapse the div section
     */
    public collapse()
    {
        jQuery(".main_div").stop().slideToggle();
        jQuery("#expand").show();
        jQuery("#collapse").hide();
    } 


    /**
     * @author:Ryan
     * @updated:Madan
     * @description: This is used for replacing the individual comment to CKEDITOR ON EDIT
     * @param comment 
     */
    private commentEditorsInstance=[];
   public editComment(comment)
   {
    //  alert(comment);
    var comment_div=document.getElementById("Activity_content_"+comment);
    // alert(comment_div);
    // alert(comment_div.innerHTML);
    var editorInstance = CKEDITOR.replace(comment_div,this.toolbarForDetail);
    editorInstance.setData(this.commentsList[comment].CrudeCDescription);
    this.commentEditorsInstance[comment] = editorInstance;
    jQuery("#Actions_"+comment).show();//show submit and cancel button on editor replace at the bottom
   }

   submitEditedComment(commentIndex,slug){
     var editedContent = this.commentEditorsInstance[commentIndex].getData();
     if(editedContent != "" && jQuery(editedContent).text().trim() != ""){
     var commentedOn = new Date()
     var formatedDate =(commentedOn.getMonth() + 1) + '-' + commentedOn.getDate() + '-' +  commentedOn.getFullYear();

     var reqData = {
    TicketId:this.ticketId,
    Comment:{
      CrudeCDescription:editedContent.replace(/^(((\\n)*<p>(&nbsp;)*<\/p>(\\n)*)+|(&nbsp;)+|(\\n)+)|(((\\n)*<p>(&nbsp;)*<\/p>(\\n)*)+|(&nbsp;)+|(\\n)+)$/gm,""),
      CommentedOn:formatedDate,
      ParentIndex:"",
      Slug:slug
    },
  };
  // alert(JSON.stringify(reqData)+"<---->edited content");
  this._ajaxService.AjaxSubscribe("story/submit-comment",reqData,(result)=>
        { 
          // this.replying = false;
          // var obj = {"statusCode":200,
          // "message":"success",
          // "data":{"CrudeCDescription":"<p>test comment asjkdhaskjdhals edited by madan</p>\n\n<p>&nbsp;</p>\n<!--template bindings={\n  \"ng-reflect-ng-if\": \"true\"\n}-->\n\n<p>&nbsp;</p>\n<!--template bindings={\n  \"ng-reflect-ng-if\": \"true\"\n}-->\n\n<p>&nbsp;</p>\n",
          // "CDescription":"<p>test comment asjkdhaskjdhals edited by madan</p>\n\n<p>&nbsp;</p>\n<!--template bindings={\n  \"ng-reflect-ng-if\": \"true\"\n}-->\n\n<p>&nbsp;</p>\n<!--template bindings={\n  \"ng-reflect-ng-if\": \"true\"\n}-->\n\n<p>&nbsp;</p>\n"},"totalCount":0}
          // alert("++++++++++++++++++++"+JSON.stringify(result));
          this.commentsList[commentIndex].CrudeCDescription = result.data.CrudeCDescription;
          this.commentsList[commentIndex].CDescription = result.data.CDescription;
          this.commentEditorsInstance[commentIndex].destroy(true);
          jQuery("#Actions_"+commentIndex).hide();
          // this.commentsList.push(result.data);
          
        });
    //  alert(editedContent);
     }

   }

   deleteComment(commentIndex,slug){
     var reqData;
     var parent;
     if(this.commentsList[commentIndex].Status == 2){
            parent = parseInt(this.commentsList[commentIndex].ParentIndex);
             reqData = {
                  TicketId:this.ticketId,
                  Comment:{
                    Slug:slug,
                    ParentIndex:parent
                  },
                };
          }else{
              reqData = {
                TicketId:this.ticketId,
                Comment:{
                  Slug:slug
                },
              };
          }
     this._ajaxService.AjaxSubscribe("story/delete-comment",reqData,(result)=>
        { 
          
          // alert("++++++++++++++++++++"+JSON.stringify(result));
          if(this.commentsList[commentIndex].Status == 2){
            // parent = parseInt(this.commentsList[commentIndex].ParentIndex);
            this.commentsList[parent].repliesCount--;
          }
          
          this.commentsList.splice(commentIndex,1);
          // this.commentsList.push(result.data);
          
        });

   }


   cancelEdit(commentIndex){
    //  var comment_div=document.getElementById("Activity_content_"+commentIndex);
    //  var name="cke_"+comment_div;
    this.commentEditorsInstance[commentIndex].destroy(true);
    jQuery("#Actions_"+commentIndex).hide();
   }


    taskDataBuilder(taskArray){
     var subTasksArray = [];
       let prepareData = [];
       var fieldsEditable = [];
       var i=0;
       var obj;
       for(let subTaskfield of taskArray){
          obj = {data:{},fieldName:""};
        for(let fields in subTaskfield.Fields){
          // alert(fields+"+++++++taskDataBuilder+++++++++");
          obj.data = subTaskfield.Fields[fields];
          obj.fieldName = fields;
          // alert("******************"+JSON.stringify(obj));
          prepareData.push(Object.assign({},obj));
          // alert("--prepareData---"+JSON.stringify(prepareData));
          fieldsEditable.push(false);
        }
        subTaskfield.Fields = prepareData;
        subTasksArray.push(subTaskfield);
        this.taskFieldsEditable.push(fieldsEditable);
        fieldsEditable = [];
        prepareData=[];
      }
       return subTasksArray;
    }
    /**
     * @author:suryaprakash
     * @description : This is used to capture related tickets
     */
    public saveRelatedTask(){
       var suggestValue=this.text;
       if(suggestValue==""||suggestValue==undefined){
        this.commonErrorFunction("relatedTaskerr_msg","Please enter title or id.")
       }else{
        var relatedTasks={
        'projectId':1,
        'ticketId':this.ticketId,
        'relatedSearchTicketId':suggestValue.split(" ")[1]
         } 
        this._ajaxService.AjaxSubscribe("story/update-related-tasks",relatedTasks,(result)=>
         { 
         this.relatedTaskArray=result.data;
            this.text="";
        })
        }
      
    }
  
     /**
     * @author:suryaprakash
     * @description : This is used to capture workhours
     */
        public workLogCapture(event)
    {
       var pattern=/^[0-9]+(\.[0-9]{1,2})?$/
       this.isTimeValidErrorMessage = pattern.test(event); 
       if(this.isTimeValidErrorMessage==false)
       {
          this.errorTimeLog();
       }else{
       if(event!=0){
            var TimeLog={
                TicketId:this.ticketId,
                workHours:event,
              };
            this._ajaxService.AjaxSubscribe("story/insert-time-log",TimeLog,(data)=>
              { 
              if(data.statusCode== 200){
                    this.individualLog =data.data.individualLog;
                    this.totalWorkLog =data.data.TotalTimeLog;
                     jQuery("#workedhours").val("");
              }
        });

           }else{
            this.errorTimeLog();
           }
       }
     
    }

        public  errorTimeLog(){
           jQuery("#timelog").html("Invalid Time");
          jQuery("#timelog").show();
          jQuery("#timelog").fadeOut(4000);
          jQuery("#workedhours").val("");
        }
        /**
        * @author:suryaprakash
        * @description : unrelate task from Story.
        */
        public unRelateTask(ticketId){
            var unRelateTicketData={
                ticketId:this.ticketId,
                unRelateTicketId:ticketId,
              };
            this._ajaxService.AjaxSubscribe("story/un-relate-task",unRelateTicketData,(data)=>
              { 
              if(data.statusCode== 200){
                   this.relatedTaskArray=data.data;
              }
        });
        }

        /**
        * @author:suryaprakash
        * @description : Error Message dispaly function
        */
        commonErrorFunction(id,message){
          jQuery("#"+id).html(message);
          jQuery("#"+id).show();
          jQuery("#"+id).fadeOut(4000);
            }

}
