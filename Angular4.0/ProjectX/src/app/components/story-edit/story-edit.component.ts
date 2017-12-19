import { Component, OnInit,ViewChild } from '@angular/core';
import { Router,ActivatedRoute } from '@angular/router';
import { Headers, Http } from '@angular/http';
import { AjaxService } from '../../ajax/ajax.service';
import { StoryService} from '../../services/story.service';
import {NgForm} from '@angular/forms';
import {CalendarModule,AutoComplete} from 'primeng/primeng'; 	
import { FileUploadService } from '../../services/file-upload.service';
import { MentionService } from '../../services/mention.service';
import { GlobalVariable } from '../../config';
import {SummerNoteEditorService} from '../../services/summernote-editor.service';
import * as io from 'socket.io-client';
import { ProjectService} from '../../services/project.service';
import {SharedService} from '../../services/shared.service'; //added by Ryan

declare var jQuery:any; //reference to jquery
declare var summernote:any; //reference to summernote

@Component({
  selector: 'app-story-edit',
  templateUrl: './story-edit.component.html',
  styleUrls: ['./story-edit.component.css'],
  providers:[StoryService,ProjectService],
})

export class StoryEditComponent implements OnInit 
{
  
  @ViewChild('editor')  txt_area:any; //reference to CKEDITOR
  public dragTimeout;
  public minDate:Date;
  public datevalue:Date;
  private ticketData:any=[];
  private ticketid;
  private url_TicketId;
  public projectName;
  public projectId;
  private description;
  public form={};
  private fieldsData = [];
  private showMyEditableField =[];
  public dragdrop={extraPlugins:'dragdrop'};
  public taskArray:any;
  public taskIds=[];
  private childTaskData="";
  //ckeditor configuration options
  public toolbar={toolbar : [
      [ 'Heading 1', '-', 'Bold','-', 'Italic','-','Underline','Link','NumberedList','BulletedList']
  ],removePlugins:'elementspath,magicline',resize_enabled:true,};
  public filesToUpload: Array<File>;
  public hasBaseDropZoneOver:boolean = false;
  public hasFileDroped:boolean = false;
  public fileUploadStatus:boolean = false;
  public defaultTasksShow:boolean=true;
  public checkPlanLevel='';
  public getRows='';
  public min_row:any=1;
  private childTasks = [];
  private showTotalEstimated=false;   
  private taskFieldsEditable=[]; 
  public reportPopuplable:any;
  public postParams:any;
  public openReportPopup:boolean=false;
  public updatedFieldValue:any;
  constructor(private fileUploadService: FileUploadService, private _ajaxService: AjaxService,private _service: StoryService,
    public _router: Router,private mention:MentionService,private projectService:ProjectService,
    private http: Http,private route: ActivatedRoute,private editor:SummerNoteEditorService,private shared:SharedService) { 
           this.filesToUpload = [];
    }


  ngOnInit() 
  { var thisObj=this;
    /**
     *  @description getting the route param(Ticketid) for specific ticket to edit
    */
    this.route.params.subscribe(params => {
            this.url_TicketId = params['id'];
            this.projectName=params['projectName'];
            this.projectService.getProjectDetails(this.projectName,(data)=>{ 
             thisObj.projectId=data.data.PId;
             if(data.statusCode!=404){
         this._ajaxService.AjaxSubscribe("story/edit-ticket",{ticketId:this.url_TicketId,projectId:thisObj.projectId},(data)=>
          {   
          if(data.statusCode!=404){
           data.data.ticket_details.Fields.filter (function(obj){
              if(obj.field_name=='planlevel' && obj.value==2){
               thisObj.defaultTasksShow= false;
              }
            });
           this.taskArray=data.data.task_types;
           var subtasks = data.data.ticket_details.Tasks;
            this.taskIds=[];
          for(let st of subtasks)
           this.taskIds.push(st.TaskType.toString())
            for (let task of this.taskArray) {
              if(this.taskIds.some(x=>x==task.Id)){
                task.IsDefault=task.Id;
                task.disabled = true;
               }else{
                task.IsDefault=task.Id;
                task.disabled = null; 
               }
            }
             this.ticketData = data.data.ticket_details;
             this.description= this.ticketData.CrudeDescription;
             this.childTaskData=this.ticketData.Tasks;
             this.checkPlanLevel=this.ticketData.StoryType.Name;
             this.fieldsData = this.fieldsDataBuilder(this.ticketData.Fields,this.ticketData.TicketId); 
             this.childTasks=this.taskDataBuilder(this.ticketData.childTasks);     
             jQuery("#description").summernote('code',this.description); 
             this.shared.change(this._router.url,this.url_TicketId,'Detail',this.checkPlanLevel,this.projectName); //added by Ryan
        }else{
            this._router.navigate(['project',this.projectName,this.url_TicketId,'error']);
          }
        });
       }else{
          this._router.navigate(['project',this.projectName,'error']);
       }  
        
        });
        });

      
    
    this.minDate=new Date(); //set current date to datepicker as min date
     jQuery(document)
    .one('focus.autoExpand', 'textarea.autoExpand', function(){ 
         var minRows = this.getAttribute('data-min-rows')|0, rows;
        var savedValue = this.value;
        this.value = '';
        this.baseScrollHeight = this.scrollHeight;
        this.value = savedValue;
         rows = Math.floor((this.scrollHeight) / 30);
        this.rows = rows;
        thisObj.min_row=rows;
    })
    .on('input.autoExpand', 'textarea.autoExpand', function(){
       
      var minRows = this.getAttribute('data-min-rows')|0, rows;
        this.rows = minRows;
        rows = Math.ceil((this.scrollHeight - this.baseScrollHeight) / 17);
        var newrows = Math.floor(this.scrollHeight/30);
        this.rows = newrows;
    });
    
       var $textArea = jQuery("#title");
      setTimeout(()=>{
       $textArea.trigger("focus");
        },1000);
    jQuery("#title").keydown(function(e){
        if (e.keyCode == 13 && !e.shiftKey)
        {
            e.preventDefault();
        }
    }); 
  }

  ngAfterViewInit() 
  {
      this.editor.initialize_editor('description',null,this);      
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
  /*
    @params    :  fieldsArray,ticketId
    @ParamType :  array,int
    @Description: Dynamic Ticket/Story Fields Rendering
    */
  fieldsDataBuilder(fieldsArray,ticketId)
  {
    let fieldsBuilt = [];
    let data = {title:"",value:"",readonly:true,required:true,id:"",fieldDataId:"",fieldName:"",fieldType:"",renderType:"",type:"",listdata:[],listDisplayData:[]};
    for(let field of fieldsArray)
    {
      if(field.field_name != "customfield_2")
      {
          data = {title:"",value:"",readonly:true,required:true,id:"",fieldDataId:"",fieldName:"",fieldType:"",renderType:"",type:"",listdata:[],listDisplayData:[]};
          switch(field.field_type)
          {
            case "Text":
            case "TextArea":
            data.title = field.title;
            data.value = field.value;
            data.renderType = (field.field_type == "TextArea")?"textarea":"input";
            data.type="text";
            break;
            case "List":
            data.title = field.title;
            data.value = field.readable_value.Name;
            data.renderType = "select";
            data.listdata = field.meta_data;
            data.listDisplayData = field.meta_data;
            data.fieldDataId = field.readable_value.Id;
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
            data.value = field.readable_value.UserName;
            data.renderType = "select";
            data.listdata = this.ticketData.collaborators;
            data.listDisplayData = this.ticketData.collaborators;;
            data.fieldDataId = field.readable_value.CollaboratorId;
            break;
            case "Bucket":
            data.title = field.title;
            data.value = field.readable_value.Name;
            data.renderType = "select";
            data.listdata = field.meta_data;
            data.listDisplayData = field.meta_data;
            data.fieldDataId = field.readable_value.Id;
            break;
            }

          data.readonly = (field.readonly == 1)?true:false;
          data.required = (field.required == 1)?true:false;
          data.id =  ticketId+"_"+field.field_name;
          data.fieldType = field.field_type;
          data.fieldName =  field.field_name;
        
          var priority=(data.title=="Priority"?true:false);
          var status=data.title;
          data.listDisplayData=this.prepareItemArray(data.listdata,priority,status);
          data.listdata=data.listDisplayData[0].filterValue;
          fieldsBuilt.push(data);
          this.showMyEditableField.push((field.readonly == 1)?false:true);
      }
    }

    return fieldsBuilt;
  }

  /*
    @params    :  list,priority,status
    @ParamType :  any,boolean,string
    @Description: Building Dynamic Dropdown List Values
    */
 public prepareItemArray(list:any,priority:boolean,status)
 {
     var listItem=[];
     var listMainArray=[];
     if(list.length>0)
     { 
       if(status == "Assigned to" || status == "Stake Holder")
       {
         listItem.push({label:"--Select a Member--", value:"",priority:priority,type:status});
       }        
       for(var i=0;list.length>i;i++)
       {
        let val = (status=='Status')?list[i]:list[i].Id;
          listItem.push({label:list[i].Name, value:val,priority:priority,type:status});
       }
     }
    listMainArray.push({type:"",filterValue:listItem});
  return listMainArray;
 }

  /*
    @params    :  edit_data
    @ParamType :  Object
    @Description: Submit Edit Story/Ticket
    */
    
  editStorySubmit(edit_data)
  { 
    
    jQuery("#title_error").hide();
    jQuery("#desc_error").hide();
    var desc=jQuery("#description").summernote('code');
    desc=jQuery(desc).text().trim();
    edit_data.dod=edit_data.dod.trim();
    var error = 0;
    if(edit_data.title=='')
    {
      jQuery("#title_error").show();
       error=1;
    }
    if(desc=='')
    {
      jQuery("#desc_error").show();
       error=1;
    }
    if(error == 0)
    {
      if(typeof edit_data.workflow == 'object'){
        if(edit_data.workflow.ConfigType==0){
          edit_data.workflow=edit_data.workflow.Id;
        }else{
           this.reportPopuplable = edit_data.workflow.CaptureMessage;
           this.postParams=edit_data;
           this.openReportPopup=true;
            this.updatedFieldValue ='';
           return ;
        }
    }
     var description= jQuery("#description").summernote('code');
     var des = this.editor.linkFun(description);
    edit_data.description=des;
      edit_data.default_task=[];
      if(this.defaultTasksShow){
      var selectedTask=[];
            for (let task of this.taskArray) {
              if(this.taskIds.some(x=>x==task.Id) && !task.disabled){
               edit_data.default_task.push(task);
               }
            }
      }
      //console.log('edit data'+JSON.stringify(edit_data));
       var post_data={
          'projectId':this.projectId,
          'data':edit_data,
         
        }
        this._ajaxService.AjaxSubscribe("story/update-ticket-details",post_data,(data)=>
    
        {
           this.openReportPopup=false;
         this._router.navigate(['project',this.projectName,this.url_TicketId,'details']);
        });
    }
          
   }

   

  /*
    @params    :  event,id
    @ParamType :  eventobj,string
    @Description: Check Empty Title or Description
    */
  checkEmpty(event,id)
  {
    if(event!="" || event!=null)
    {
      var idAttr = id;
      if(idAttr=='title')
      {
        jQuery("#title_error").hide();
      }
      if(idAttr=='description')
      {
        jQuery("#desc_error").hide();
      }
    }
  }

    /*
    @params      : fileInput
    @ParamType   :  any
    @Description : Enabling the dropzone DIV on dragOver
    */
  public fileOverBase(fileInput:any):void 
  {
      this.hasBaseDropZoneOver = true;
      if(this.dragTimeout != undefined && this.dragTimeout != "undefined"){ 
      clearTimeout(this.dragTimeout);
      }
  
  }

   /*
    @params      : fileInput
    @ParamType   :  any
    @Description : Disabling the dropzone DIV on dragOver
    */
  public fileDragLeave(fileInput: any){
  var thisObj = this;
      if(this.dragTimeout != undefined && this.dragTimeout != "undefined"){
      clearTimeout(this.dragTimeout);
      }
       this.dragTimeout = setTimeout(function(){
       thisObj.hasBaseDropZoneOver = false;
      },500);
      
  }

    /*
    @params       : fileInput,comeFrom
    @ParamType    :  any,string
    @Description  : Uploading File
    */
  public fileUploadEvent(fileInput: any, comeFrom: string):void 
  {
     if(comeFrom == 'fileChange'){
          this.filesToUpload = <Array<File>> fileInput.target.files;
     } else if(comeFrom == 'fileDrop'){
          this.filesToUpload = <Array<File>> fileInput.dataTransfer.files;
     } else{
          this.filesToUpload = <Array<File>> fileInput.target.files;
     }

        this.hasBaseDropZoneOver = false;
        this.fileUploadStatus = true;
        this.fileUploadService.makeFileRequest(GlobalVariable.FILE_UPLOAD_URL, [], this.filesToUpload).then(
          (result :Array<any>) => {
            for(var i = 0; i<result.length; i++){
                var uploadedFileExtension = (result[i].originalname).split('.').pop();
                result[i].originalname =  result[i].originalname.replace(/[^a-zA-Z0-9.]/g,'_'); 
                if(uploadedFileExtension == "png" || uploadedFileExtension == "jpg" || uploadedFileExtension == "jpeg" || uploadedFileExtension == "gif") {
                    this.description = jQuery("#description").summernote('code') + "<p>[[image:" +result[i].path + "|" + result[i].originalname + "]]</p>";
                    jQuery("#description").summernote('code',this.description);
                } else{
                    this.description = jQuery("#description").summernote('code') + "<p>[[file:" +result[i].path + "|" + result[i].originalname + "]]</p>";
                    jQuery("#description").summernote('code',this.description);
                }
            }
            this.fileUploadStatus = false;
        }, (error) => {
            this.description = jQuery("#description").summernote('code')+ "Error while uploading";
            this.fileUploadStatus = false;
        });
   }

     /**
     @description Routing to Story Detail on Cancel Edit
     */
    cancelDesc()
    {
      this._router.navigate(['project',this.projectName,this.url_TicketId,'details']);
    }

 /**
  *  @description Preaparing task array to display the task data
  */
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
 *  @description Removing cancel button based on user priority.
 */
cancleChangingStatus(value:any){
  this.openReportPopup=false;
  jQuery('body').removeClass('modal-open');
  
}
/**
 *  @description Updating/saving the workflow when user did something on status
 */
saveReportWithStatus(value:any){
  value.workflow=value.workflow.Id;
  this.editStorySubmit(value);
}

}
