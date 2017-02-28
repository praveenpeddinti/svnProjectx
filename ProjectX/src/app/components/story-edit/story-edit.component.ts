import { Component, OnInit,ViewChild } from '@angular/core';
import { Router,ActivatedRoute } from '@angular/router';
import { Headers, Http } from '@angular/http';
import { AjaxService } from '../../ajax/ajax.service';
import { StoryService} from '../../services/story.service';
import {NgForm} from '@angular/forms';
import {CalendarModule} from 'primeng/primeng'; 	
import { FileUploadService } from '../../services/file-upload.service';
import { GlobalVariable } from '../../config';

declare var jQuery:any; //reference to JQUERY  

@Component({
  selector: 'app-story-edit',
  templateUrl: './story-edit.component.html',
  styleUrls: ['./story-edit.component.css'],
  providers:[StoryService]
})

export class StoryEditComponent implements OnInit 
{
  public dragTimeout;
  public minDate:Date;
  private ticketData:any=[];
  private ticketid;
  private url_TicketId;
  private description;
  public form={};
  private fieldsData = [];
  private showMyEditableField =[];
  public dragdrop={extraPlugins:'dragdrop'};
   //CkEditor Configuration Options
  public toolbar={toolbar : [
      [ 'Heading 1', '-', 'Bold','-', 'Italic','-','Underline','Link','NumberedList','BulletedList']
  ],removePlugins:'elementspath',resize_enabled:true};
  public filesToUpload: Array<File>;
  public hasBaseDropZoneOver:boolean = false;
  public hasFileDroped:boolean = false;
  public fileUploadStatus:boolean = false;

  constructor(private fileUploadService: FileUploadService, private _ajaxService: AjaxService,private _service: StoryService,
    public _router: Router,
    private http: Http,private route: ActivatedRoute) { 
           this.filesToUpload = [];
    }


  ngOnInit() 
  {
    //get route param(TicketId) for specific ticket to edit
    this.route.params.subscribe(params => {
            this.url_TicketId = params['id'];
        });

    setTimeout(()=>{
      this._ajaxService.AjaxSubscribe("story/edit-ticket",{ticketId:this.url_TicketId},(data)=>
        {   
             this.ticketData = data.data;
             this.description=data.data.CrudeDescription;
             this.fieldsData = this.fieldsDataBuilder(data.data.Fields,data.data.TicketId);        
        });
    
    },150);
    
    this.minDate=new Date(); //set the min date to current date
  }

  /*
    @params    :  fieldsArray,ticketId
    @ParamType :  array,int
    @Description: Dynamic Ticket/Story Fields Rendering
    */
  fieldsDataBuilder(fieldsArray,ticketId)
  {
    let fieldsBuilt = [];
    let data = {title:"",value:"",readonly:true,required:true,id:"",fieldDataId:"",fieldName:"",fieldType:"",renderType:"",type:"",listdata:[]};
    for(let field of fieldsArray)
    {
      if(field.field_name != "customfield_2")
      {
          data = {title:"",value:"",readonly:true,required:true,id:"",fieldDataId:"",fieldName:"",fieldType:"",renderType:"",type:"",listdata:[]};
          switch(field.field_type)
          {
            case "Text":
            data.title = field.title;
            data.value = field.value;
            data.renderType = "input";
            data.type="text";
            break;
            case "List":
            data.title = field.title;
            data.value = field.readable_value.Name;
            data.renderType = "select";
            data.listdata = field.meta_data;
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
            data.fieldDataId = field.readable_value.CollaboratorId;
            break;
            case "Bucket":
            data.title = field.title;
            data.value = field.readable_value.Name;
            data.renderType = "select";
            data.listdata = field.meta_data;
            data.fieldDataId = field.readable_value.Id;
            break;
          }

          data.readonly = (field.readonly == 1)?true:false;
          data.required = (field.required == 1)?true:false;
          data.id =  ticketId+"_"+field.field_name;
          data.fieldType = field.field_type;
          data.fieldName =  field.Id;
          if(field.field_name == "dod")
          {
              data.renderType = "textarea";
          }
          var priority=(data.title=="Priority"?true:false);
          var status=data.title;
          data.listdata=this.prepareItemArray(data.listdata,priority,status);
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
     if(list.length>0)
     { 
       if(status == "Assigned to" || status == "Stake Holder")
       {
         listItem.push({label:"--Select a Member--", value:"",priority:priority,type:status});
       }        
       for(var i=0;list.length>i;i++)
       {
          listItem.push({label:list[i].Name, value:list[i].Id,priority:priority,type:status});
       }
     }
    return listItem;
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
    if(edit_data.title=='')
    {
      jQuery("#title_error").show();
    }
    if(edit_data.description=='')
    {
      jQuery("#desc_error").show();
    }
    if(edit_data.description!="" && edit_data.title!="")
    {
       var post_data={
          'data':edit_data,
         
        }
        this._ajaxService.AjaxSubscribe("story/edit-ticket-details",post_data,(data)=>
    
        { 
         this._router.navigate(['story-detail',this.url_TicketId]);
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
      if(this.dragTimeout != undefined && this.dragTimeout != "undefined"){ console.log("clear---");
      clearTimeout(this.dragTimeout);
      }
  
  }

   /*
    @params      : fileInput
    @ParamType   :  any
    @Description : Disabling the dropzone DIV on dragOver
    */
  public fileDragLeave(fileInput: any){
  //console.log("fileDragLeave");
  var thisObj = this;
      if(this.dragTimeout != undefined && this.dragTimeout != "undefined"){
          // console.log("clear---");
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
        this.fileUploadService.makeFileRequest(GlobalVariable.FILE_UPLOAD_URL, [], this.filesToUpload).then((result :Array<any>) => {
            for(var i = 0; i<result.length; i++){
                var uploadedFileExtension = (result[i].originalname).split('.').pop();
                if(uploadedFileExtension == "png" || uploadedFileExtension == "jpg" || uploadedFileExtension == "jpeg" || uploadedFileExtension == "gif") {
                    this.description = this.description + "[[image:" +result[i].path + "|" + result[i].originalname + "]] ";
                } else{
                    this.description = this.description + "[[file:" +result[i].path + "|" + result[i].originalname + "]] ";
                }
            }
            this.fileUploadStatus = false;
        }, (error) => {
            this.description = this.description + "Error while uploading";
            this.fileUploadStatus = false;
        });
   }

     /*
     @Description  : Routing to Story Detail on Cancel Edit
     */
    cancelDesc()
    {
      this._router.navigate(['story-detail',this.url_TicketId]);
    }

}
