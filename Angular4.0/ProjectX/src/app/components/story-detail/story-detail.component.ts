import { Component, OnInit,ViewChild,Input } from '@angular/core';
import { Router,ActivatedRoute } from '@angular/router';
import { Headers, Http, Response } from '@angular/http';
import { AjaxService } from '../../ajax/ajax.service';
import {AccordionModule,DropdownModule,SelectItem} from 'primeng/primeng';
import { FileUploadService } from '../../services/file-upload.service';
import { GlobalVariable } from '../../config';
import { MentionService } from '../../services/mention.service';
import {SummerNoteEditorService} from '../../services/summernote-editor.service';
import {SharedService} from '../../services/shared.service';
import { ProjectService } from '../../services/project.service';
declare var refresh;
declare var jQuery:any;
declare const CKEDITOR;
@Component({
  selector: 'app-story-detail',
  templateUrl: './story-detail.component.html',
  styleUrls: ['./story-detail.component.css'],
   providers: [ProjectService]
})
export class StoryDetailComponent implements OnInit {

private text:string;
private search_results:string[];

private getAllData=  JSON.parse(localStorage.getItem('user'));

private editableSelect= "";
public blurTimeout=[];
@ViewChild('detailEditor')  detail_ckeditor; // reference for editor in view.
  public clickedOutside = false;
  public form:any={description:''};
  public dragTimeout;
  public inlineTimeout;
  public minDate:Date;
  private ticketData;
  private ticketId;
  private projectId;
  public projectName;
  private fieldsData = [];
  private showMyEditableField =[];
  private showMyEditableTaskField =[];
  private ticketEditableDesc="";
  private ticketDesc = "";
  private ticketCrudeDesc = "";
  private showDescEditor=true;
  public statusId='';
  private followers:any=[];
  private added_follower=[];
  private follower_search_results:string[];
  private texts:string;
  private check_status:boolean=false;
  private showTotalEstimated=false;
  private childTasksArray=[];
  private childTaskData="";
  public commentsList=[];
  private taskFieldsEditable=[];
  public totalWorkLog = '0.00';
  public individualLog=[];
  public isTimeValidErrorMessage;
  public relatedTaskArray=[];
  public checkPlanLevel='';
  public bucketId:any;
  public commentError:any='';
  public editCommentError:any='';
  public reportPopuplable:any='';
  public openReportPopup:boolean=false;
  public postParams:any;
  public updatedFieldValue:any;
  //Configuration varibale for CKEDITOR in detail page.
  private toolbarForDetail={toolbar : [
    [ 'Heading 1', '-', 'Bold','-', 'Italic','-','Underline','Link','NumberedList','BulletedList' ]
],removePlugins:'elementspath,magicline',resize_enabled:true};

  //common array for Dropdown's option.
  private dropList=[];
  private dropDisplayList=[];
  public filesToUpload: Array<File>;
  public hasBaseDropZoneOver:boolean = false;
  public hasBaseDropZoneOverComment:boolean = false;
  public hasFileDroped:boolean = false;
  public fileUploadStatus:boolean = false;
  public hide:boolean=false;//added by Ryan
  public attachmentsData=[];
  public searchSlug='';
  public relateTicketId='';
  public navigatedFrom;//added by Ryan
  public currentFieldData={
    fieldDataId:'',
    fieldIndex:'',
    fieldValueId:''
  };
  public commentDelId='';
  public commentDelSlug='';
  public childTaskmodel={};
  constructor(private fileUploadService: FileUploadService, private _ajaxService: AjaxService,
    public _router: Router,private mention:MentionService,
    private http: Http,private route: ActivatedRoute,private editor:SummerNoteEditorService,private projectService:ProjectService,private shared:SharedService) {

    this.filesToUpload = [];
    route.queryParams.subscribe(
      params => 
      {
            this.searchSlug=params['Slug'];
            console.log(this.searchSlug);
            this.navigatedFrom=params['From'];//added by Ryan
       })
  

    }

 private calenderClickedOutside = false;
 private comment_status:boolean=true;

 //public form={description:''};//added by ryan
  ngOnInit() {
      this.showTotalEstimated=false;
var thisObj = this;
    //let jsonform={};//added by ryan
    //jsonform['description']='';//added by ryan
   this.replyToComment=-1;
   //@Praveen P toggle for plus button in follower list
   jQuery('body').removeClass('modal-open');// for enabling the scrollbar after popup closing
   jQuery(document).click(function(e) {
     if( jQuery(e.target).closest('div#followerdiv').length==0 && e.target.id != 'follwersAdd' && e.target.id != 'follwersAddI'  ) {
      jQuery("#followerdiv").css( "display",'none' );
    }
   });

   jQuery(document).ready(function(){
       jQuery(document)
    .one('focus.autoExpand', 'textarea.autoExpand', function(){ console.log("********focus");
         var minRows = this.getAttribute('data-min-rows')|0, rows;
        var savedValue = this.value;
        this.value = '';
        this.baseScrollHeight = this.scrollHeight;
        this.value = savedValue;
         rows = Math.floor((this.scrollHeight) / 30);
        this.rows = rows;
    })
    .on('input.autoExpand', 'textarea.autoExpand', function(){
         var minRows = this.getAttribute('data-min-rows')|0, rows;
        this.rows = minRows;
        rows = Math.ceil((this.scrollHeight - this.baseScrollHeight) / 17);
        var newrows = Math.floor(this.scrollHeight/30);
        this.rows = newrows;
    });
      })

  

   this.route.queryParams.subscribe(
      params => 
      { 
         console.log("==Slug params=="+params['Slug']);
         this.searchSlug = params['Slug'];
        // for navigation to ticket
      this.route.params.subscribe(params => {
            this.ticketId = params['id'];
           this.projectName=params['projectName'];
            this.projectService.getProjectDetails(this.projectName,(data)=>{ 
              if(data.data!=false){
                thisObj.projectId=data.data.PId;  
                this.callTicketDetailPage(thisObj.ticketId, thisObj.projectId);
              }else{
               this._router.navigate(['pagenotfound']);  
              }
                
        });

            jQuery("#notifications_list").hide();
            
            console.log("==Id=="+params['id']);
            console.log("==Slug params=="+params['Slug']);
            //this.searchSlug = params['Slug'];//added by Ryan
              //added By Ryan for BreadCrumb Purpose
            
        });
           });
          localStorage.setItem('ProjectName',this.projectName);
           

      //      this.route.queryParams.subscribe(
      // params => 
      // { 
      //   // for navigation to ticket
      // this.route.params.subscribe(params => {
      //       this.searchSlug = params['Slug'];
//alert("onInit"+JSON.stringify(this.relatedTaskArray));
      jQuery(document).click(function(e){
                if(jQuery(e.target).closest(".deletebutton").length == 0 ) {
                    jQuery("#delete_relateTask").css("display", "none");
                }

      });

        
}

    /**
     * @author:Ryan Marshal
     * @description:This is used for initializing the summernote editor in the comment section
     */
    ngAfterViewInit()
    {
      console.log("Comment Editor");
         this.editor.initialize_editor('commentEditor','keyup',this); //for comment
         console.log("=Plan Level="+this.checkPlanLevel);
      //jQuery('span[id^="check_"]').hide();
    }

getArtifacts(obj){
  this._ajaxService.AjaxSubscribe("story/get-my-ticket-attachments",obj,(data)=>
        { 
        if(data.statusCode == 200 && data.data.length!= 0){ 
                         this.attachmentsData = data.data;                            
        } else {
            this.attachmentsData =[];
        }          
        });
}

  /*
  * Description part
  */
  openDescEditor(){
    var formobj=this;//added by ryan
    
    
    //added by Ryan for summernote
    this.editor.initialize_editor('detailEditor',null,this);
     jQuery("#detailEditor").summernote('code','<p>'+this.ticketEditableDesc+'</p>');
    //  alert("here");
    //  jQuery("#detailEditor").summernote('code',jQuery("#detailEditor").html());
    this.showDescEditor = false;

  }

  private descError="";
  submitDesc(){
    //setTimeout(()=>{
      //var editorData = this.detail_ckeditor.instance.getData();
      //this.form['description']=jQuery('#detailEditor').summernote('code'); //added by Ryan for summernote
      var editorData=jQuery('#detailEditor').summernote('code');
      this.ticketEditableDesc = editorData;
      console.log("==in submit desc=="+editorData);
      //var editorData=this.form['description'];
      var editorDesc=jQuery(editorData).text().trim();
 
          if(editorDesc != ""){
          this.descError = "";
        
        // Added by Padmaja for Inline Edit
        var postEditedText={
          isLeftColumn:0,
          id:'Description',
          value:editorData,
          ticketId:this.ticketId,
          projectId:this.projectId,
          editedId:'desc'
        };
        this.postDataToAjax(postEditedText);
        this.showDescEditor = true;
        this.ticketCrudeDesc = editorDesc;//this.ticketEditableDesc;
        }else{
          this.descError = "Description cannot be empty.";
        }
//},250);
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
var commentText=jQuery("#commentEditor").summernote('code');

if(commentText != "" && jQuery(commentText).text().trim() != ""){
this.commentDesc="";
jQuery("#commentEditor").summernote('reset');

jQuery("#commentEditorArea").removeClass("replybox");
  console.log("comment is submitted");

var commentedOn = new Date()
var formatedDate =(commentedOn.getMonth() + 1) + '-' + commentedOn.getDate() + '-' +  commentedOn.getFullYear();
  var reqData = {
    ticketId:this.ticketId,
    projectId:this.projectId,
    Comment:{
      CrudeCDescription:commentText.replace(/^(((\\n)*<p>(&nbsp;)*<\/p>(\\n)*)+|(&nbsp;)+|(\\n)+)|(((\\n)*<p>(&nbsp;)*<\/p>(\\n)*)+|(&nbsp;)+|(\\n)+)$/gm,""),//.replace(/(<p>(&nbsp;)*<\/p>)+|(&nbsp;)+/g,""),
      CommentedOn:formatedDate,
      ParentIndex:"",
      Reply:this.replying,
      OriginalCommentorId:""

    },
  };
  if(this.replying == true){
    if(this.replyToComment != -1){
    reqData.Comment.OriginalCommentorId = jQuery("#replySnippetContent").attr("class");
    reqData.Comment.ParentIndex=this.replyToComment+"";
    }

  }
  this._ajaxService.AjaxSubscribe("story/submit-comment",reqData,(result)=>
        { 
          
          this.commentsList.push(result.data);
          if(this.replying == true){
            this.commentsList[this.replyToComment].repliesCount++;
          }
          this.form.description='';
          this.commentError='';
          this.replying = false;
          var ticketIdObj={'ticketId': this.ticketId,'projectId':this.projectId};
          this.getArtifacts(ticketIdObj);
          
        });
}else{
          this.commentError = "Comment cannot be empty.";
}
  
}


private replyToComment=-1;
private replying=false;
private commentAreaColor="";
replyComment(commentId,userId){ 
this.commentError='';
this.editCommentError='';
this.replyToComment = commentId;
this.replying = true;
this.commentAreaColor = jQuery("#commentEditorArea").css("background");
jQuery("#commentEditorArea").addClass("replybox");
jQuery('html, body').animate({
        scrollTop: jQuery("#commentEditorArea").offset().top
    }, 1000);

}


navigateToParentComment(parentId){
  jQuery('html, body').animate({
        scrollTop: jQuery("#"+parentId).offset().top
    }, 1000);
}
cancelReply(){
  this.commentError='';
  this.editCommentError='';
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
public min_row:any=1;
// private titleError="";

editTitle(titleId){
//  alert("+++++++++"+titleId);
  var offsetHeight:any = parseInt(jQuery('.viewinputtext').height());
  var lineheight:any = parseInt(jQuery('#'+this.ticketId+"_title").css('line-height'));
  this.showTitleEdit = false;
  this.min_row = offsetHeight/lineheight;
  jQuery("#"+titleId).focus();
    jQuery("#"+titleId).keydown(function(e){
        if (e.keyCode == 13 && !e.shiftKey)
        {
            e.preventDefault();
         }
     });
jQuery("#"+titleId).focus();
 setTimeout(()=>{ 
  jQuery("#"+titleId).focus();
console.log('time');
 },100);
  
}

closeTitleEdit(editedText){
        if(editedText.trim() !=""){
          // alert("if");
          // this.titleError="";
          document.getElementById(this.ticketId+"_title").innerText= editedText;
          this.showTitleEdit = true;
      // Added by Padmaja for Inline Edit
          var postEditedText={
            isLeftColumn:0,
            id:'Title',
            value:editedText,
            ticketId:this.ticketId,
            projectId:this.projectId,
            editedId:'title'
          };
          this.postDataToAjax(postEditedText);
      }else{
        this.showTitleEdit = true;
        jQuery("#"+this.ticketId+"_titleInput").val(document.getElementById(this.ticketId+"_title").innerText) ;

      }
}
//------------------------------Title part-----------------------------------

//Navigate to Edit Page
  goToEditPage(){
this._router.navigate(['project',this.projectName, this.ticketId,'edit']);
  }

//Changes inline editable filed to thier respective edit modes - Left Column fields.
//Renders data to dropdowns dynamically.
  editThisField(event,fieldIndex,fieldId,fieldDataId,fieldTitle,renderType,where){ 
   this.currentFieldData.fieldDataId=fieldDataId,
   this.currentFieldData.fieldIndex=fieldIndex
   this.dropList=[];
   this.dropDisplayList=[];
   var thisObj=this;
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
    
   
    setTimeout(()=>{document.getElementById(inptFldId).focus();},150);
    }
    if(renderType == "select"){ 
        var reqData = {
          fieldId:fieldDataId,
          projectId:this.ticketData.data.Project.PId,
          ticketId:(where == "Tasks")?fieldId:this.ticketData.data.TicketId,
          workflowType:this.ticketData.data.WorkflowType,
          statusId:thisObj.statusId 
        };
        //Fetches the field list data for current dropdown in edit mode.
        this._ajaxService.AjaxSubscribe("story/get-field-details-by-field-id",reqData,(data)=>
            { 
                var listData = {
                  list:data.data
                };
                 var priority=(fieldTitle=="Priority"?true:false);
                this.dropDisplayList=this.prepareItemArray(listData.list,priority,fieldTitle);
                this.dropList=this.dropDisplayList[0].filterValue;
                //sets the dropdown prefocused
                if(fieldTitle=='Status'){
                  let value=this.dropList[0].label;
                  let valueId=this.dropList[0].value.Id;
                  this.fieldsData[this.currentFieldData.fieldIndex].valueId=valueId;
                  this.fieldsData[this.currentFieldData.fieldIndex].value=value;
                }
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
   restoreField(editedObj,restoreFieldId,fieldIndex,renderType,fieldId,where,isChildActivity=0){
    var intRegex = /^\d+$/;
    var floatRegex = /^((\d+(\.\d *)?)|((\d*\.)?\d+))$/;

      var postEditedText={
                        projectId:this.projectId,
                        isLeftColumn:1,
                        id:fieldId,
                        value:"",
                        ticketId:(where == "Tasks")?restoreFieldId.split("_")[0]:this.ticketId,
                        editedId:restoreFieldId.split("_")[1]
                      };
          switch(renderType){
            case "input":
            case "textarea":
            editedObj=editedObj.trim();
            if(restoreFieldId == this.ticketId+"_dod".trim())
            jQuery("textarea#"+restoreFieldId+"_"+fieldIndex).val(editedObj);
            document.getElementById(restoreFieldId).innerText = (editedObj == "") ? "--":editedObj;
            postEditedText.value = editedObj;
             this.postDataToAjax(postEditedText,isChildActivity);
            break;
            
            case "select":
            if(postEditedText.editedId=='workflow'){
              if(editedObj.value.ConfigType==0){
             var appendHtml = (restoreFieldId.split("_")[1] == "priority")?"&nbsp; <i class='fa fa-circle "+editedObj.text+"' aria-hidden='true'></i>":"";
            document.getElementById(restoreFieldId).innerHTML = (editedObj.text == ""||editedObj.text == "--Select a Member--") ? "--":editedObj.text+appendHtml;
            postEditedText.value = editedObj.value.Id;
            this.postDataToAjax(postEditedText,isChildActivity);
            }else{
              this.reportPopuplable = editedObj.value.CaptureMessage;
              this.postParams=postEditedText;
              postEditedText.value = editedObj.value.Id;
              this.openReportPopup=true;
              this.updatedFieldValue = document.getElementById(restoreFieldId).innerHTML;
              var appendHtml = (restoreFieldId.split("_")[1] == "priority")?"&nbsp; <i class='fa fa-circle "+editedObj.text+"' aria-hidden='true'></i>":"";
              document.getElementById(restoreFieldId).innerHTML = (editedObj.text == ""||editedObj.text == "--Select a Member--") ? "--":editedObj.text+appendHtml;
          
            }
            }else{
            var appendHtml = (restoreFieldId.split("_")[1] == "priority")?"&nbsp; <i class='fa fa-circle "+editedObj.text+"' aria-hidden='true'></i>":"";
            document.getElementById(restoreFieldId).innerHTML = (editedObj.text == ""||editedObj.text == "--Select a Member--") ? "--":editedObj.text+appendHtml;
            postEditedText.value = editedObj.value.Id;
             this.postDataToAjax(postEditedText,isChildActivity);
            }
           
            break;

            case "date":
            var date = (this.dateVal.getMonth() + 1) + '-' + this.dateVal.getDate() + '-' +  this.dateVal.getFullYear();
            date = date.replace(/(\b\d{1}\b)/g, "0$1");
            document.getElementById(restoreFieldId).innerHTML = (date == "") ? "--":date;
            postEditedText.value = this.dateVal.toString();
            var rightNow = new Date();
            this.postDataToAjax(postEditedText,isChildActivity);
            break;

          }
         if(where == "Tasks"){
          var row = fieldIndex.split("_")[0];
          var col = fieldIndex.split("_")[1];
          this.taskFieldsEditable[row][col]=false;

        }else{  
            this.showMyEditableField[fieldIndex] = true;
        }

      
  }
  
    inputKeyDown(event,eleId){
        if (event.shiftKey == true) {
            event.preventDefault();
        }

        if ((event.keyCode >= 48 && event.keyCode <= 57) || 
            (event.keyCode >= 96 && event.keyCode <= 105) || 
            event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 37 ||
            event.keyCode == 39 || event.keyCode == 46 || event.keyCode == 190) {

        } else {
            event.preventDefault();
        }

        if(jQuery("#"+eleId).val().indexOf('.') !== -1 && event.keyCode == 190)
            event.preventDefault(); 
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
    let data = {title:"",value:"",valueId:"",readonly:true,required:true,elId:"",fieldType:"",renderType:"",type:"",Id:"",displayFlag:true};
    for(let field of fieldsArray){
      if(field.field_name != "customfield_2"){
      data = {title:"",value:"",valueId:"",readonly:true,required:true,elId:"",fieldType:"",renderType:"",type:"",Id:"",displayFlag:true};
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
              this.bucketId=field.readable_value.Id;
            }
            data.renderType = "select";
            break;

          }
          data.readonly = (field.readonly == 1)?true:false;
          data.required = (field.required == 1)?true:false;
          data.elId =  ticketId+"_"+field.field_name;
          data.Id = field.Id;
            data.fieldType = field.field_type;
            if('totalestimatepoints'==field.field_name){
              data.displayFlag=false;
            }

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
    var listMainArray=[];
     if(list.length>0){
       if(status == "Assigned to" || status == "Stake Holder"){
       listItem.push({label:"--Select a Member--", value:"",priority:priority,type:status});
       }
         for(var i=0;list.length>i;i++){
           listItem.push({label:list[i].Name, value:list[i],priority:priority,type:status});
       }
     }
      listMainArray.push({type:"",filterValue:listItem});
    return listMainArray;
}

//----------------------File Upload codes---------------------------------
public fileOverBase(fileInput:any,where:string,comment:string):void {
  if(where=="edit_comments"){
    
    jQuery("div[id^='dropble_comment_']").removeClass("dragdrop");

    if(jQuery("#Activity_content_"+comment).length >0)
    {
      jQuery("#dropble_comment_"+comment).addClass("dragdrop","true");
    }


    
  }else if(where=="comments")
  {
    
    jQuery("div[id^='dropble_comment_']").removeClass("dragdrop");
    jQuery("#dropble_comment_").addClass("dragdrop","true");
  }

    else{
      console.log("==in else fileOverBase==");
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
     jQuery("div[id^='dropble_comment_']").removeClass("dragdrop");
     thisObj.hasBaseDropZoneOver = false;
    
     
    },500);
    
}



  public fileUploadEvent(fileInput: any, comeFrom: string,where:string,comment:string):void {
    var editor_contents;
    var appended_content;
    if(where=="edit_comments"){
      //editor_contents=jQuery("#cke_Activity_content_"+comment).find("iframe").contents().find('body').html();
      editor_contents=jQuery("#Activity_content_"+comment).summernote('code'); // for summernote editor
      fileInput.preventDefault();
    }
   if(comeFrom == 'fileChange'){
        this.filesToUpload = <Array<File>> fileInput.target.files;
   } else if(comeFrom == 'fileDrop'){
        this.filesToUpload = <Array<File>> fileInput.dataTransfer.files;
   } else{
        this.filesToUpload = <Array<File>> fileInput.target.files;
   }

        if(where=="edit_comments"){
             jQuery("div[id^='dropble_comment_']").removeClass("dragdrop");
             jQuery("#comments_gif_"+comment).show();
          }
          else if(where=="comments")
          {
            jQuery("#dropble_comment_").removeClass("dragdrop","true");
            jQuery("#last_comments").show();
          }
          else{

             this.hasBaseDropZoneOver = false;
             this.fileUploadStatus = true;
          }
        this.fileUploadService.makeFileRequest(GlobalVariable.FILE_UPLOAD_URL, [], this.filesToUpload).then((result :Array<any>) => {
            for(var i = 0; i<result.length; i++){
                var uploadedFileExtension = (result[i].originalname).split('.').pop();
                result[i].originalname =  result[i].originalname.replace(/[^a-zA-Z0-9.]/g,'_'); 
                if(uploadedFileExtension == "png" || uploadedFileExtension == "jpg" || uploadedFileExtension == "jpeg" || uploadedFileExtension == "gif") {
                    if(where =="comments"){
                      this.commentDesc = jQuery("#commentEditor").summernote('code') + "<p>[[image:" +result[i].path + "|" + result[i].originalname + "]]</p> " + " ";
                      jQuery("#commentEditor").summernote('code',this.commentDesc);
                    }else if(where=="edit_comments"){
                      appended_content=editor_contents+"<p>[[image:" +result[i].path + "|" + result[i].originalname + "]]</p>" +" "; 
                    //jQuery("#cke_Activity_content_"+comment).find("iframe").contents().find('body').html(appended_content);
                    jQuery("#Activity_content_"+comment).summernote('code',appended_content);//for summernote
                  }else{
                      this.ticketEditableDesc = jQuery("#detailEditor").summernote('code') + "<p>[[image:" +result[i].path + "|" + result[i].originalname + "]] </p>";
                      jQuery("#detailEditor").summernote('code',this.ticketEditableDesc);
                        // jQuery('#detailEditor').summernote('code',this.form['description']+"[[image:" +result[i].path + "|" + result[i].originalname + "]] " +" ");
                        // this.form['description'] = jQuery('#detailEditor').summernote('code');
                    }
                } else{
                    if(where =="comments"){
                      this.commentDesc = jQuery("#commentEditor").summernote('code') + "<p>[[file:" +result[i].path + "|" + result[i].originalname + "]] </p>" +" ";
                      jQuery("#commentEditor").summernote('code',this.commentDesc);
                    }else if(where=="edit_comments"){
                      appended_content =editor_contents+"<p>[[file:" +result[i].path + "|" + result[i].originalname + "]]</p>" +" ";
                      // jQuery("#cke_Activity_content_"+comment).find("iframe").contents().find('body').html(appended_content);
                      jQuery("#Activity_content_"+comment).summernote('code',appended_content); //for summernote
                    }else{
                      this.ticketEditableDesc = jQuery("#detailEditor").summernote('code') + "<p>[[file:" +result[i].path + "|" + result[i].originalname + "]]</p> ";
                       jQuery("#detailEditor").summernote('code',this.ticketEditableDesc);
                    }
                }
            }
            jQuery("#comments_gif_"+comment).hide();
            jQuery("#last_comments").hide();
            this.fileUploadStatus = false;
        }, (error) => {
            this.ticketEditableDesc = this.ticketEditableDesc + "Error while uploading";
            this.fileUploadStatus = false;
        });
}




//------------------------------------File Upload logics end-----------------------------------------------------

// Added by Padmaja for Inline Edit
//Common Ajax method to save the changes.
    public postDataToAjax(postEditedText,isChildActivity=0){
      console.log("-------"+JSON.stringify(postEditedText));
     clearTimeout(this.inlineTimeout);
    this.inlineTimeout =  setTimeout(() => { 
       this._ajaxService.AjaxSubscribe("story/update-story-field-inline",postEditedText,(result)=>
        {
          if(result.statusCode== 200){ 
             this.openReportPopup=false;
             jQuery('body').removeClass('modal-open');
          if(postEditedText.editedId == "title" || postEditedText.editedId == "desc"){
                     if(postEditedText.editedId == "title"){
                        document.getElementById(this.ticketId+'_'+postEditedText.editedId).innerText=result.data.updatedFieldData;
                      }else if(postEditedText.editedId == "desc"){
                      document.getElementById(this.ticketId+'_'+postEditedText.editedId).innerHTML=result.data.updatedFieldData;
                       var ticketIdObj={'ticketId': this.ticketId,'projectId':this.projectId};
                      this.getArtifacts(ticketIdObj);
                   }
               
          }
           else if(postEditedText.editedId == "estimatedpoints"){ 
                 jQuery("#"+postEditedText.ticketId+"_totalestimatepoints").html(result.data.updatedFieldData.value);
               }
          
             else if(result.data.updatedState!=''){ 
                 document.getElementById(this.ticketId+'_'+result.data.updatedState.field_name).innerText=result.data.updatedState.state;
                this.statusId = result.data.updatedFieldData;
                var ticketIdObj={'ticketId': this.ticketId,'projectId':this.projectId};
                this.getArtifacts(ticketIdObj);
           }
          
        /**
        * @author:Praveen P
        * @description : This is used to show the selected user (Stake Holder, Assigned to and Reproted by) in Follower list 
        */
        var fieldType:any='';
        if(isChildActivity==1)postEditedText.ticketId=this.ticketId;
        if(result.data.activityData !='noupdate')
        {   
          if(result.data.activityData.referenceKey == -1)
           fieldType = result.data.activityData.data.PropertyChanges[0].ActionFieldType ;
           else 
           fieldType = result.data.activityData.data.ActionFieldType ;
          if(fieldType!='' && fieldType == 6){
            this._ajaxService.AjaxSubscribe("story/get-ticket-followers-list",postEditedText,(response)=>
            { 
                if (response.statusCode == 200) {
                    this.followers = response.data;
                }
            });
        }
      }


         if(isChildActivity==0){
            if(result.data.activityData.referenceKey == -1){
             this.commentsList.push(result.data.activityData.data);
            }
       else if(result.data.activityData != "noupdate"){
        this.commentsList[result.data.activityData.referenceKey]["PropertyChanges"].push(result.data.activityData.data);

     } 
         }
               

 }
        });

      
        },500)
    }

//Navigate back to dashboard page.
    public goBack()
    {
        this._router.navigate(['story-dashboard']);
    }
       /**
     * @author:Padmaja
     * @description : This is used to saving subtask details
     */
   public savechiledTask()
    {
      var title= this.childTaskmodel['childtitle'].trim();
       if(title=='' || title=='undefined'){
          this.commonErrorFunction("subtaskerr","Please enter title.")
       }else{
        var postTaskData={
            ticketId:this.ticketId,
            projectId:this.projectId,
            title:title
          };
        this._ajaxService.AjaxSubscribe("story/create-child-task",postTaskData,(result)=>
        {
          var task=[];
          task.push(result.data.Tasks);
          var newChildData = this.taskDataBuilder(task);
          this.childTasksArray.push(newChildData[0]);
           if(result.data.activityData.referenceKey == -1){
             this.commentsList.push(result.data.activityData.data);
            }
             else if(result.data.activityData != "noupdate"){
        this.commentsList[result.data.activityData.referenceKey]["PropertyChanges"].push(result.data.activityData.data);
     }  
          });
       this.childTaskmodel['childtitle']=[];
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
       var modifiedString=event.query.replace("#","");
        var post_data={
        'projectId':this.projectId,
        'sortvalue':'Title',
        'ticketId':this.ticketId,
        'searchString':modifiedString
    }
    let prepareSearchData = [];
      //  this.search_results=data;
        this._ajaxService.AjaxSubscribe("story/get-all-ticket-details-for-search",post_data,(result)=>
         { 
           var subTaskData = result.data;
            for(let subTaskfield of subTaskData){
               var currentData = '#'+subTaskfield.TicketId+' '+subTaskfield.Title;
               if (currentData.length > 145){
                var currentData= currentData.substring(0,145) + '...';
                }
                 prepareSearchData.push(currentData);
            }
           this.search_results=prepareSearchData;
         });
    }
 


    /**
     * @author:Praveen P
     * @description: This is used to add/remove the followers in the follower div section
     */
    
    /* When click the plus button to open the input box for followers*/
    public loadFollowersWidget(event){ console.log("load foloer");
    if( event.target.id== "follwersAdd" || event.target.id == "follwersAddI" ){

      jQuery("#followerdiv").show();
      jQuery("#followerId").val("");
      this.follower_search_results=[];
      }
    }
    /* Enter 2 char in the follower input box*/ 
    public getUsersForFollow(event){
      this.follower_search_results=[];
      if(event.length>=2){
        var dafaultUserList:any=[];
      for(var x=0;x<this.followers.length;x++){
           dafaultUserList.push(this.followers[x].FollowerId);
           dafaultUserList.push(this.followers[x].CreatedBy);
      }
         var followerData = {
          ticketId:this.ticketId,
          projectId:this.projectId,
          dafaultUserList:dafaultUserList,
          searchValue:event
        };
         this._ajaxService.AjaxSubscribe("story/get-collaborators-for-follow",followerData,(response)=>
         { 
        
          if (response.statusCode == 200) {
                 var fList:any=[];
         for(var l=0;l<response.data.length;l++){
           //fList.push(response.data[l].ProfilePicture+" "+response.data[l].UserName);
           fList.push({Name:response.data[l].Name,id:response.data[l].Id,ProfilePic:response.data[l].ProfilePic});
           }
           
             this.follower_search_results=fList;
               console.log(this.followers.length+"--followerdate-------"+JSON.stringify(this.follower_search_results));
            } else {
                console.log("fail---");
            } 
             
         });
      }
    }
    /* add the followers in the Follower div */
    public checkFollower(event)
    {
      if(jQuery("#check_"+event).hasClass("glyphicon glyphicon-ok"))
      {
        jQuery("#check_"+event).removeClass("glyphicon glyphicon-ok");
        var followerData = {
          ticketId:this.ticketId,
          projectId:this.projectId,
          collaboratorId:event
        
        };
        this._ajaxService.AjaxSubscribe("story/unfollow-ticket",followerData,(response)=>
        {
            if(response.statusCode==200)
            {
               if(response.data.activityData.referenceKey == -1){
                this.commentsList.push(response.data.activityData.data);
                } else if(response.data.activityData != "noupdate"){
        this.commentsList[response.data.activityData.referenceKey]["PropertyChanges"].push(response.data.activityData.data);
     }  
               jQuery("#followerdiv_"+event).remove();
               this.followers = this.followers.filter(function(el) {
               return el.FollowerId !== event;
               
             });
            }
        });


      }
      else{
        jQuery("#check_"+event).addClass("glyphicon glyphicon-ok");
        //make ajax to add follower to the Ticket.....
        var followerData = {
         ticketId:this.ticketId,
          projectId:this.projectId,
          collaboratorId:event,
        
       
        };
        this._ajaxService.AjaxSubscribe("story/follow-ticket",followerData,(response)=>
         {
                if(response.statusCode==200)
                {
                  this.followers.push(response.data);  
                   if(response.data.activityData.referenceKey == -1){
             this.commentsList.push(response.data.activityData.data);
            } else if(response.data.activityData != "noupdate"){
        this.commentsList[response.data.activityData.referenceKey]["PropertyChanges"].push(response.data.activityData.data);
     }  
                }
             

        });


      }
      
    }
    /*when click the 'yes' in the popup div on the follower to remove the user*/
    public removefollowerId(event){
    jQuery("#check_"+event).removeClass("glyphicon glyphicon-ok");
    document.getElementById("followerdiv").style.display='none';
    var followerData = {
          ticketId:this.ticketId,
          projectId:this.projectId,
          collaboratorId:event,
        };
        this._ajaxService.AjaxSubscribe("story/unfollow-ticket",followerData,(response)=>
         {
                if(response.statusCode==200)
                {
             if(response.data.activityData.referenceKey == -1){
                this.commentsList.push(response.data.activityData.data);
                } else if(response.data.activityData != "noupdate"){
        this.commentsList[response.data.activityData.referenceKey]["PropertyChanges"].push(response.data.activityData.data);
     }  
               jQuery("#followerdiv_"+event).remove();
               this.followers = this.followers.filter(function(el) {
                    return el.FollowerId !== event;
                });
              }
             
        });

    }
    /*when click the 'X' button in the follower div to open the popup*/
  public removeFollower(event)
  {
    document.getElementById("followerdiv").style.display='none';
  }
/* Followers list method end /*


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
    public commentorId:any;
   public editComment(comment)
   {
    this.commentError='';
    this.editCommentError='';
    this.commentorId=this.commentsList[comment].ActivityBy.CollaboratorId
    var edit_comment='Activity_content_'+comment;
    /* added for summernote */
      this.editor.initialize_editor(edit_comment,null,this);
      jQuery("#Activity_content_"+comment).summernote('code',this.commentsList[comment].CrudeCDescription);
      jQuery("#Reply_Icons_"+comment).hide();
    jQuery("#Actions_"+comment).show();//show submit and cancel button on editor replace at the bottom
   }

   submitEditedComment(commentIndex,slug){
     var editedContent= jQuery("#Activity_content_"+commentIndex).summernote('code');//added for summernote
     if(editedContent != "" && jQuery(editedContent).text().trim() != ""){
     var commentedOn = new Date()
     var formatedDate =(commentedOn.getMonth() + 1) + '-' + commentedOn.getDate() + '-' +  commentedOn.getFullYear();
     var reqData = {
    ticketId:this.ticketId,
    projectId:this.projectId,
    Comment:{
      CrudeCDescription:editedContent.replace(/^(((\\n)*<p>(&nbsp;)*<\/p>(\\n)*)+|(&nbsp;)+|(\\n)+)|(((\\n)*<p>(&nbsp;)*<\/p>(\\n)*)+|(&nbsp;)+|(\\n)+)$/gm,""),
      CommentedOn:formatedDate,
      ParentIndex:"",
      Slug:slug,
    },
  };
  this._ajaxService.AjaxSubscribe("story/submit-comment",reqData,(result)=>
        {   
            this.form.description='';
            this.commentsList[commentIndex].CrudeCDescription = result.data.CrudeCDescription;
            this.commentsList[commentIndex].CDescription = result.data.CDescription;
          var code= jQuery("#Activity_content_"+commentIndex).summernote('code',result.data.CDescription);
          jQuery("#Activity_content_"+commentIndex).summernote('destroy');
          jQuery("#Reply_Icons_"+commentIndex).show();
          this.editCommentError='';
          jQuery("#edit_comment_"+commentIndex).hide();

          jQuery("#Actions_"+commentIndex).hide();
          var ticketIdObj={'ticketId': this.ticketId,'projectId':this.projectId};
          this.getArtifacts(ticketIdObj);
          
        });
     }else{
       jQuery("#edit_comment_"+commentIndex).html('Comment can not be empty');
       jQuery("#edit_comment_"+commentIndex).show();
         //this.editCommentError='Comment can not be empty';
     }

   }

   deleteComment(){
    //  commentIndex=this.commentDelId;
    //  slug=this.commentDelSlug;
     var editedContent= jQuery("#Activity_content_"+this.commentDelId).summernote('code');
     var CrudeCDescription=editedContent.replace(/^(((\\n)*<p>(&nbsp;)*<\/p>(\\n)*)+|(&nbsp;)+|(\\n)+)|(((\\n)*<p>(&nbsp;)*<\/p>(\\n)*)+|(&nbsp;)+|(\\n)+)$/gm,"");
     var reqData;
     var parent;
    jQuery("#Activity_content_"+this.commentDelId).summernote('destroy');
     this.commentorId=this.commentsList[this.commentDelId].ActivityBy.CollaboratorId ; 
        if(this.commentsList[this.commentDelId].Status == 2){
            parent = parseInt(this.commentsList[this.commentDelId].ParentIndex);
             reqData = {
                  ticketId:this.ticketId,
                  projectId:this.projectId,
                  Comment:{
                    Slug:this.commentDelSlug,
                    ParentIndex:parent,
                    CrudeCDescription:CrudeCDescription
                  },
                };
          }else{
              reqData = {
                ticketId:this.ticketId,
                projectId:this.projectId,
                Comment:{
                  Slug:this.commentDelSlug,
                  CrudeCDescription:CrudeCDescription
                },
              };
          }
     this._ajaxService.AjaxSubscribe("story/delete-comment",reqData,(result)=>
        { 
          
          if(this.commentsList[this.commentDelId].Status == 2){
            this.commentsList[parent].repliesCount--;
          }
          this.commentsList[this.commentDelId].Status = 0;
         jQuery('#delete_comment').hide();
        });

   }


   cancelEdit(commentIndex){
    this.commentError='';
    this.editCommentError='';
   jQuery("#Activity_content_"+commentIndex).summernote('code',this.commentsList[commentIndex].CDescription);
   jQuery("#Activity_content_"+commentIndex).summernote('destroy');
    jQuery("#Actions_"+commentIndex).hide();
    jQuery("#Reply_Icons_"+commentIndex).show();
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
          obj.data = subTaskfield.Fields[fields];
          obj.fieldName = fields;
          prepareData.push(Object.assign({},obj));
          fieldsEditable.push(false);
          if(fields == 'estimatedpoints' && subTaskfield.Fields[fields].value != ""){
            this.showTotalEstimated=true;
          }
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
        projectId:this.projectId,
        'ticketId':this.ticketId,
        'bucketId':this.bucketId,
        'relatedSearchTicketId':suggestValue.split("#")[1]
         } 
       if(relatedTasks.relatedSearchTicketId==undefined){
         this.commonErrorFunction("relatedTaskerr_msg","Please select ticket") 
       }else{
       this._ajaxService.AjaxSubscribe("story/update-related-tasks",relatedTasks,(result)=>
         { 
      this.relatedTaskArray=result.data.ticketData;
            this.text="";
            if(result.data.activityData.referenceKey == -1){
             this.commentsList.push(result.data.activityData.data);
            } else if(result.data.activityData != "noupdate"){
        this.commentsList[result.data.activityData.referenceKey]["PropertyChanges"].push(result.data.activityData.data);
     }  
        })
       }
       
        }
      }
     /**
     * @author:suryaprakash
     * @description : This is used to capture workhours
     */
        public workLogCapture(event)
    {
       var pattern=/^([0-9])*(\.[0-9]{1,2})?$/
       this.isTimeValidErrorMessage = pattern.test(event); 
       if(this.isTimeValidErrorMessage==false)
       {
          this.errorTimeLog();
       }else{
        if(event!=0){
            var currentDate = new Date();
            var TimeLog={
                ticketId:this.ticketId,
                workHours:event,
                addTimelogDesc:'',
                addTimelogTime:currentDate.toString(),
                projectId:this.projectId
              };
            this._ajaxService.AjaxSubscribe("story/insert-time-log",TimeLog,(data)=>
              { 
              if(data.statusCode== 200){
                    this.individualLog =data.data.timeLogData.individualLog;
                    this.totalWorkLog =data.data.timeLogData.TotalTimeLog;
                     jQuery("#workedhours").val("");
                     if(data.data.activityData.referenceKey == -1){
             this.commentsList.push(data.data.activityData.data);
            }
             else if(data.data.activityData != "noupdate"){
        this.commentsList[data.data.activityData.referenceKey]["PropertyChanges"].push(data.data.activityData.data);
     }  
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
        showdeleteDiv(id,ticId){
          this.relateTicketId=ticId;
           if(id==1){
              jQuery("#delete_relateTask").css("display", "block");
               var delbutton_Height=10;
              var delbutton_Width=jQuery('#del_'+ticId).width()/2;
              var delete_popup=jQuery('.delete_followersbgtable').width()/2;
              var offset=jQuery('#del_'+ticId).offset();
              var offsetTop=offset.top+delbutton_Height-100;
              var offsetRight=offset.left;
             jQuery('#delete_relateTask').css({'top':offsetTop,'right':30,'min-width':"auto"});
          }else{
            jQuery("#delete_relateTask").css("display", "none");
          }
      }
      commentdeleteDiv(id,cId,cslug){
        this.commentDelId=cId;
        this.commentDelSlug=cslug;
         if(id==1){
           jQuery("#delete_comment").css("display", "block");
            //   jQuery("#delete_comment").css("display", "block");
               var delbutton_Height=10;
              var delbutton_Width=jQuery('#commentdel_'+cslug).width()/2;
              var delete_popup=jQuery('.delete_followersbgtable').width()/2;
              var offset=jQuery('#commentdel_'+cslug).offset();
              var offsetTop=offset.top+delbutton_Height-100;
              var offsetRight=offset.left;
             jQuery('#delete_comment').css({'top':offsetTop,'right':22,'min-width':"auto"});
          }else{
            jQuery("#delete_comment").css("display", "none");
          }
      }

        /**
        * @author:suryaprakash
        * @description : unrelate task from Story.
        */
        public unRelateTask(){
            var unRelateTicketData={
                ticketId:this.ticketId,
                projectId:this.projectId,
                unRelateTicketId: this.relateTicketId,
                bucketId:this.bucketId,
              };
            this._ajaxService.AjaxSubscribe("story/un-relate-task",unRelateTicketData,(data)=>
              { 
              if(data.statusCode== 200){
                   this.relatedTaskArray=data.data.ticketInfo;
                    if(data.data.activityData.referenceKey == -1){
             this.commentsList.push(data.data.activityData.data);
            }
             else if(data.data.activityData != "noupdate"){
        this.commentsList[data.data.activityData.referenceKey]["PropertyChanges"].push(data.data.activityData.data);
          }  
          jQuery('#delete_relateTask').hide();
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

    public navigateStoryDetail(ticketId,projectId){
    this.showTotalEstimated=false;
    this.fieldsData = []; 
    this.showMyEditableField =[];
          //this.callTicketDetailPage(ticketId,projectId);        
        }

public callTicketDetailPage(ticId,projectId){

    var thisObj = this;
    thisObj.text="";
    thisObj.showDescEditor = true;
    thisObj.showTotalEstimated=false;
    jQuery(document).ready(function(){
        window.scrollTo(0,0);
    
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
      if(ticId == ""){
         this.route.params.subscribe(params => {
            this.ticketId = params['id'];
        });
      }else{  
          this.ticketId = ticId;
      }
      jQuery("#commentEditor").summernote('reset');
      var ticketIdObj={'ticketId': this.ticketId,'projectId':projectId};
        this._ajaxService.AjaxSubscribe("story/get-ticket-details",ticketIdObj,(data)=>
        { 
            if(data.statusCode!=404){
              this.ticketData = data;
              this.ticketData.data.Fields.filter (function(obj){
              if(obj.field_name=='workflow'){
               thisObj.statusId =obj.value;
              }
            });
            this.followers = data.data.Followers; //@Praveen P This line to show the default followers in the Follower Div section
            this.ticketDesc = data.data.Description;
            this.ticketEditableDesc = this.ticketCrudeDesc = data.data.CrudeDescription;
            jQuery("#detailEditor").html(this.ticketEditableDesc);//for summernote editor
            this.fieldsData = this.fieldsDataBuilder(data.data.Fields,data.data.TicketId);
            this.checkPlanLevel=data.data.StoryType.Name;
            console.log("==Plan Level=="+this.checkPlanLevel);
            this.shared.navigatedFrom(this.navigatedFrom);//added by Ryan
            this.shared.change(this._router.url,this.ticketId,'Detail',this.checkPlanLevel,this.projectName);
            this.childTaskData=data.data.Tasks;
             this.childTasksArray=this.taskDataBuilder(data.data.Tasks);
             this._ajaxService.AjaxSubscribe("story/get-ticket-activity",ticketIdObj,(data)=>
            { 
              console.log(data.data.Activities);
              this.commentsList = data.data.Activities;
               setTimeout(() => { 
                    if(typeof this.searchSlug != "undefined"){ 
                        if(jQuery("."+this.searchSlug).length>0){
                         var getSlug = jQuery("."+this.searchSlug).offset().top;

                          jQuery('html, body').animate({
                             scrollTop: getSlug
                           }, 1000);
                           }
                     }
              }, 500);
              
            });

       this._ajaxService.AjaxSubscribe("story/get-work-log",ticketIdObj,(data)=>
            { 
               this.individualLog =data.data.individualLog;
                 if(data.data.TotalTimeLog > 0){
                  this.totalWorkLog = data.data.TotalTimeLog;
                 }else{
                  this.totalWorkLog = "0.00";
                 }
            });

        this._ajaxService.AjaxSubscribe("story/get-all-related-tasks",ticketIdObj,(result)=>
         { 
        
         this.relatedTaskArray=result.data;
        })

    //---------------------------- Attachments code---------------//
     /**
     * @author:Jagadish
     * @description: This is used to display Attachments
     */
       this.getArtifacts(ticketIdObj);
      }else{
        this._router.navigate(['project',this.projectName,this.ticketId ,'error']); 
      }
        
        });
       
      this.minDate=new Date();

   
}
     /**
     * @author:Anand
     * @description: Submit title on enter click
     */
submitOnEnter(event) { 
  if(event.keyCode == 13) {
   this.closeTitleEdit(event.target.value);
  }
}

cancleChangingStatus(value:any){
  this.openReportPopup=false;
  jQuery('body').removeClass('modal-open');
  var appendHtml = (value.editedId.split("_")[1] == "priority")?"&nbsp; <i class='fa fa-circle "+value.updatedFieldValue+"' aria-hidden='true'></i>":"";
  document.getElementById(value.ticketId+"_"+value.editedId).innerHTML = (value.updatedFieldValue == ""||value.updatedFieldValue == "--Select a Member--") ? "--":value.updatedFieldValue+appendHtml;
  this.fieldsData[this.currentFieldData.fieldIndex].Id=this.currentFieldData.fieldDataId;
  this.currentFieldData.fieldValueId='';
  this.currentFieldData.fieldDataId='';
  this.currentFieldData.fieldIndex='';
}
saveReportWithStatus(value:any){
  this.postDataToAjax(value);
}
}
