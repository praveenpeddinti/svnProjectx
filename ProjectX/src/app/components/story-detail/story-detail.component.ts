import { Component, OnInit,ViewChild } from '@angular/core';
import { Router,ActivatedRoute } from '@angular/router';
import { Headers, Http } from '@angular/http';
import { AjaxService } from '../../ajax/ajax.service';
import {AccordionModule,DropdownModule,SelectItem} from 'primeng/primeng';
declare var jQuery:any;
import { FileUploadService } from '../../services/file-upload.service';
import { GlobalVariable } from '../../config';
declare var jQuery:any;
@Component({
  selector: 'app-story-detail',
  templateUrl: './story-detail.component.html',
  styleUrls: ['./story-detail.component.css'],
  
})
export class StoryDetailComponent implements OnInit {
  // private _ajaxService: AjaxService;
  //   public _router: Router;
  //   private http: Http;
//  testObj = {};
//   newObj = {};


private editableSelect= "";
public blurTimeout=[];


@ViewChild('editor')  txt_area;
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
  private toolbarForDetail={toolbar : [
    [ 'Heading 1', '-', 'Bold','-', 'Italic','-','Underline','Link','NumberedList','BulletedList' ]
],removePlugins:'elementspath',resize_enabled:true};
  // private makeFocused = []; 
  private dropList=[];

public filesToUpload: Array<File>;
public hasBaseDropZoneOver:boolean = false;
public hasFileDroped:boolean = false;
public fileUploadStatus:boolean = false;

  constructor(private fileUploadService: FileUploadService, private _ajaxService: AjaxService,
    public _router: Router,
    private http: Http,private route: ActivatedRoute) {
       this.filesToUpload = [];
    }

 
  ngOnInit() {
     var thisObj = this;
    jQuery(document).ready(function(){
    jQuery(document).bind("click",function(event){                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               
    if(jQuery(event.target).closest('div.customdropdown').length == 0){
    thisObj.clickedOutside = true;

    }else{
    thisObj.clickedOutside = false;
    }

    })
    })


    // var parms=this.route.params.subscribe;
 /** @Praveen P
 * Getting the TicketId for story dashboard
 */
      this.route.params.subscribe(params => {
            this.ticketId = params['id'];
        });
        //  alert("constructor"+this.ticketId);
   
      var ticketIdObj={'ticketId': this.ticketId};
        this._ajaxService.AjaxSubscribe("story/get-ticket-details",ticketIdObj,(data)=>
        { 
          
            
            this.ticketData = data;
         //   alert(this.ticketId+"++++++++++++++"+JSON.stringify(this.ticketData));
            this.ticketDesc = data.data.Description;
            this.ticketEditableDesc = this.ticketCrudeDesc = data.data.CrudeDescription;
            this.fieldsData = this.fieldsDataBuilder(data.data.Fields,data.data.TicketId);
            //  alert("========>"+JSON.stringify(this.fieldsData));
            
        });

      this.minDate=new Date();
  }

  /*
  * Description part
  */
  openDescEditor(){
    this.showDescEditor = false;
    // document.getElementById('tktDesc').focus();
  }

private descError="";
submitDesc(){
  // this.ticketDesc = this.ticketEditableDesc;
  if(this.ticketEditableDesc != ""){
    this.descError = "";
  this.showDescEditor = true;
  // Added by Padmaja for Inline Edit
   var postEditedText={
    isLeftColumn:0,
    id:'Description',
    value:this.ticketEditableDesc,
    TicketId:this.ticketId,
    EditedId:'desc'
  };
  //alert(JSON.stringify(postEditedText));
  this.postDataToAjax(postEditedText);
 //alert("++++++submitted++++++++++"+this.ticketDesc);
  }else{
    this.descError = "Description cannot be empty.";
  }

}
cancelDesc(){
  this.ticketEditableDesc = this.ticketCrudeDesc;

  this.showDescEditor = true;

}

//------------------------Description part---------------------------------- 

/*
* Title part
*/
private showTitleEdit=true;
private titleError="";
editTitle(){
  this.showTitleEdit = false;
}

closeTitleEdit(editedText){
  if(editedText !=""){
    this.titleError="";
  document.getElementById(this.ticketId+"_title").innerHTML= editedText;
  //alert(this.ticketId);
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
  this.titleError = "Title cannot be empty";

}
}
//------------------------------Title part-----------------------------------

  onClick(){
    // alert(val);
    this._router.navigate(['story-edit',this.ticketId]);

  }

  editThisField(event,fieldIndex,fieldId,fieldDataId,fieldTitle,renderType){ 
    console.log(event.target.id);
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
// alert(JSON.stringify(reqData)+"reqest data");
this._ajaxService.AjaxSubscribe("story/get-field-details-by-field-id",reqData,(data)=>
    { 
       
         var currentId = document.getElementById(inptFldId+"_currentSelected").getAttribute("value");
        //  data.getFieldDetails.currentSelectedId = currentId;
         var listData = {
           currentSelectedId: (currentId != "" &&currentId != null )? currentId:"",
           list:data.getFieldDetails
         };
        //  console.log(JSON.stringify(listData));
         var priority=(fieldTitle=="Priority"?true:false);
         this.dropList=this.prepareItemArray(listData.list,priority,fieldTitle);
        
        jQuery("#"+inptFldId+" div").click();
        
    });
    }


    
  }

  dropBlur(){
    console.log("blur");
  }
 
private dateVal = new Date();
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
       document.getElementById(restoreFieldId).innerHTML = (editedObj.text == "") ? "--":editedObj.text;
       postEditedText.value = editedObj.value;
       break;
       case "date":
       var date = this.dateVal.toLocaleDateString();
       document.getElementById(restoreFieldId).innerHTML = (date == "") ? "--":date;
       postEditedText.value = this.dateVal.toLocaleDateString();
       break;

     }
     
    this.showMyEditableField[fieldIndex] = true;
   this.postDataToAjax(postEditedText);
   
    

  }
closeCalendar(fieldIndex){

  this.showMyEditableField[fieldIndex] = true;
}
  dropdownFocus(event,fieldIndex){ 
   
 
    for(var i in this.showMyEditableField){
     
      if(i != fieldIndex){
        this.showMyEditableField[i] = true;
       
      }
     
    }
    this.showMyEditableField[fieldIndex] = false;
   
  }
  selectBlurField(event,fieldIndex){ 
   var thisobj = this;
    if(this.blurTimeout[fieldIndex] != undefined && this.blurTimeout[fieldIndex] != "undefined"){
        clearTimeout(this.blurTimeout[fieldIndex]);
        }
     this.blurTimeout[fieldIndex]= setTimeout(function(){
    if(thisobj.clickedOutside == true){
    thisobj.showMyEditableField[fieldIndex] = true;
    }

    },1000)

    }

  fieldsDataBuilder(fieldsArray,ticketId){
    let fieldsBuilt = [];
    let data = {title:"",value:"",valueId:"",readonly:true,required:true,elId:"",fieldType:"",renderType:"",type:"",Id:""};
    for(let field of fieldsArray){
      if(field.field_name != "customfield_2"){
      data = {title:"",value:"",valueId:"",readonly:true,required:true,elId:"",fieldType:"",renderType:"",type:"",Id:""};
          switch(field.field_type){
            case "Text":
            data.title = field.title;
            data.value = field.value;
            data.renderType = "input";
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
            // alert(field.readable_value.date.split(" ")[0]+"++++++++Date++++++++++");
            data.value = field.readable_value;
            this.dateVal = field.readable_value;
            data.renderType = "date";
            data.type="date";
            break;
            case "DateTime":
            data.title = field.title;
            // alert(field.readable_value.date.split(".")[0]+"++++++++++DateTime++++++++++++");
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
          // if(field.field_type == "Bucket" || field.field_type == "Team List" || field.field_type == "Team List"){
          //   data.type = "List";
          // }else{
            data.fieldType = field.field_type;
            if(field.field_name == "dod"){
              data.renderType = "textarea";
              console.log(data.renderType);
          }
          // }
          fieldsBuilt.push(data);
          this.showMyEditableField.push((field.readonly == 1)?false:true);
          // this.makeFocused.push(new EventEmitter());
      }
    }

    return fieldsBuilt;

  }
 public prepareItemArray(list:any,priority:boolean,status:string){
  var listItem=[];
     if(list.length>0){
         for(var i=0;list.length>i;i++){
          listItem.push({label:list[i].Name, value:list[i].Id,priority:priority,type:status});
       }
     }
return listItem;
}
public fileOverBase(fileInput:any):void {
//console.log("fileoverbase");
    this.hasBaseDropZoneOver = true;
    if(this.dragTimeout != undefined && this.dragTimeout != "undefined"){ console.log("clear---");
    clearTimeout(this.dragTimeout);
    }

}

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

  public fileUploadEvent(fileInput: any, comeFrom: string):void {
   console.log("the source " + comeFrom);
   // console.log("cahnge event " + fileInput.name +"------- " + fileInput.size);
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
            console.error(error);
            this.ticketEditableDesc = this.ticketEditableDesc + "Error while uploading";
            this.fileUploadStatus = false;
        });
}

// Added by Padmaja for Inline Edit
    public postDataToAjax(postEditedText){
       // alert("helloo test"+JSON.stringify(postEditedText));alert(this.ticketId);
       this._ajaxService.AjaxSubscribe("story/update-story-field-details",postEditedText,(result)=>
        { 
       // alert("updating here"+data);
      // alert(JSON.stringify(result.data));
          if(result.statusCode== 200){
          //  alert("success");
         //  alert(this.ticketId+'_'+postEditedText.EditedId);
         if(postEditedText.EditedId == "title" || postEditedText.EditedId == "desc")
            document.getElementById(this.ticketId+'_'+postEditedText.EditedId).innerHTML=result.data;
          }
    
        });
    }

    public goBack()
    {
        this._router.navigate(['story-dashboard']);
    }

}
