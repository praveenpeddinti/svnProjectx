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
  private ticketEditableDesc="";
  private ticketDesc = "";
  private ticketCrudeDesc = "";
  private showDescEditor=true;

  //Configuration varibale for CKEDITOR in detail page.
  private toolbarForDetail={toolbar : [
    [ 'Heading 1', '-', 'Bold','-', 'Italic','-','Underline','Link','NumberedList','BulletedList' ]
],removePlugins:'elementspath,magicline',resize_enabled:true};

  //common array for Dropdown's option.
  private dropList=[];

  public filesToUpload: Array<File>;
  public hasBaseDropZoneOver:boolean = false;
  public hasFileDroped:boolean = false;
  public fileUploadStatus:boolean = false;

  constructor(private fileUploadService: FileUploadService, private _ajaxService: AjaxService,
    public _router: Router,private mention:MentionService,
    private http: Http,private route: ActivatedRoute) {
       this.filesToUpload = [];
    }

 private calenderClickedOutside = false;

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
    });


    /** @Praveen P
     * Getting the TicketId for story dashboard
     */
      this.route.params.subscribe(params => {
            this.ticketId = params['id'];
        });

        
      var ticketIdObj={'ticketId': this.ticketId};
        this._ajaxService.AjaxSubscribe("story/get-ticket-details",ticketIdObj,(data)=>
        { 
    
            this.ticketData = data;
            this.ticketDesc = data.data.Description;
            this.ticketEditableDesc = this.ticketCrudeDesc = data.data.CrudeDescription;
            this.fieldsData = this.fieldsDataBuilder(data.data.Fields,data.data.TicketId);
            
        });

      this.minDate=new Date();
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
                          mention.push(data.data[i].Name);
                        }
                      callback(mention);
                    });
                      }
                  }
                },
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
      var editorData = this.detail_ckeditor.instance.getData();
          if(editorData != "" && jQuery(editorData).text().trim() != ""){
          this.descError = "";
        this.showDescEditor = true;
        // Added by Padmaja for Inline Edit
        var postEditedText={
          isLeftColumn:0,
          id:'Description',
          value:editorData,
          TicketId:this.ticketId,
          EditedId:'desc'
        };
        this.postDataToAjax(postEditedText);
        this.ticketCrudeDesc = editorData;//this.ticketEditableDesc;
        }else{
          this.descError = "Description cannot be empty.";
        }

  }

  cancelDesc(){
    this.descError = "";
    this.ticketEditableDesc = this.ticketCrudeDesc;

    this.showDescEditor = true;

  }

//------------------------Description part---------------------------------- 

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
  editThisField(event,fieldIndex,fieldId,fieldDataId,fieldTitle,renderType){ 
    // this.dropList={};
    this.dropList=[];
    var fieldName = fieldId.split("_")[1];
    var inptFldId = fieldId+"_"+fieldIndex;
    this.showMyEditableField[fieldIndex] = false;
    setTimeout(()=>{document.getElementById(inptFldId).focus();},150);
    if(renderType == "select"){
        var reqData = {
          FieldId:fieldDataId,
          ProjectId:this.ticketData.data.Project.PId,
          TicketId:this.ticketData.data.TicketId
        };
        //Fetches the field list data for current dropdown in edit mode.
        this._ajaxService.AjaxSubscribe("story/get-field-details-by-field-id",reqData,(data)=>
            { 
                var currentId = document.getElementById(inptFldId+"_currentSelected").getAttribute("value");
                var listData = {
                  currentSelectedId: (currentId != "" &&currentId != null )? currentId:"",
                  list:data.getFieldDetails
                };
                
                var priority=(fieldTitle=="Priority"?true:false);
                this.dropList=this.prepareItemArray(listData.list,priority,fieldTitle);
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
   restoreField(editedObj,restoreFieldId,fieldIndex,renderType,fieldId){
     var postEditedText={
                        isLeftColumn:1,
                        id:fieldId,
                        value:"",
                        TicketId:this.ticketId,
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
            break;

          }
     
        this.showMyEditableField[fieldIndex] = true;
        this.postDataToAjax(postEditedText);
  }

  closeCalendar(fieldIndex){

    this.showMyEditableField[fieldIndex] = true;
  }

//Dropdown's onFocus event
  dropdownFocus(event,fieldIndex){ 
 
    for(var i in this.showMyEditableField){
     
      if(i != fieldIndex){
        this.showMyEditableField[i] = true;
       }
    }
    this.showMyEditableField[fieldIndex] = false;
   
  }

//Dropdown's onBlur event
  selectBlurField(event,fieldIndex){ 
   var thisobj = this;
    if(this.blurTimeout[fieldIndex] != undefined && this.blurTimeout[fieldIndex] != "undefined"){
        clearTimeout(this.blurTimeout[fieldIndex]);
        }
        this.blurTimeout[fieldIndex]= setTimeout(function(){
              if(thisobj.clickedOutside == true){
              thisobj.showMyEditableField[fieldIndex] = true;
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
public fileOverBase(fileInput:any):void {
    this.hasBaseDropZoneOver = true;
    if(this.dragTimeout != undefined && this.dragTimeout != "undefined"){
    clearTimeout(this.dragTimeout);
    }

}

public fileDragLeave(fileInput: any){

var thisObj = this;
    if(this.dragTimeout != undefined && this.dragTimeout != "undefined"){
    clearTimeout(this.dragTimeout);
    }
     this.dragTimeout = setTimeout(function(){
     thisObj.hasBaseDropZoneOver = false;
    },500);
    
}

  public fileUploadEvent(fileInput: any, comeFrom: string):void {
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
                    this.ticketEditableDesc = this.ticketEditableDesc + "[[image:" +result[i].path + "|" + result[i].originalname + "]] ";
                } else{
                    this.ticketEditableDesc = this.ticketEditableDesc + "[[file:" +result[i].path + "|" + result[i].originalname + "]] ";
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
                document.getElementById(this.ticketId+'_'+postEditedText.EditedId).innerHTML=result.data;
          }
        });
    }

//Navigate back to dashboard page.
    public goBack()
    {
        this._router.navigate(['story-dashboard']);
    }

    public search(event)
    {
        let data=['#174 -Displaying List of Data',"#182 -Displaying List of Views"];
        this.search_results=data;
        // this._ajaxService.AjaxSubscribe("story/search",event.query,(data)=>
        // { 
        // //logic to search for related tickets
        //     this.search_results=data;
        // });
    }
  

}
