import { Component, OnInit } from '@angular/core';
import { Router,ActivatedRoute } from '@angular/router';
import { Headers, Http } from '@angular/http';
import { AjaxService } from '../../ajax/ajax.service';
import {AccordionModule,DropdownModule,SelectItem} from 'primeng/primeng'; 


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
]};
  // private makeFocused = []; 
  private dropList=[];

public filesToUpload: Array<File>;
public hasBaseDropZoneOver:boolean = false;
public hasFileDroped:boolean = false;
public fileUploadStatus:boolean = false;

  constructor( private _ajaxService: AjaxService,
    public _router: Router,
    private http: Http,private route: ActivatedRoute) {
       this.filesToUpload = [];
    }

 
  ngOnInit() {
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


  }

  /*
  * Description part
  */
  openDescEditor(){
    this.showDescEditor = false;
    // document.getElementById('tktDesc').focus();
  }

submitDesc(){
  this.ticketDesc = this.ticketEditableDesc;
  this.showDescEditor = true;
  // Added by Padmaja for Inline Edit
   var postEditedText={
    isLeftColumn:0,
    id:'Description',
    value:this.ticketDesc,
    TicketId:this.ticketId,
    EditedId:'desc'
  };
  //alert(JSON.stringify(postEditedText));
  this.postDataToAjax(postEditedText);
 //alert("++++++submitted++++++++++"+this.ticketDesc);

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
editTitle(){
  this.showTitleEdit = false;
}

closeTitleEdit(editedText){
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
}
//------------------------------Title part-----------------------------------

  onClick(){
    // alert(val);
    this._router.navigate(['story-edit',this.ticketId]);
  }

  editThisField(event,fieldIndex,fieldId,fieldDataId){
    console.log(event.target.id);
    // this.dropList={};
    this.dropList=[];
    var fieldName = fieldId.split("_")[1];
    var inptFldId = fieldId+"_"+fieldIndex;
    this.showMyEditableField[fieldIndex] = false;
    setTimeout(()=>{document.getElementById(inptFldId).focus();},150);
    if(fieldName !=="dod" && fieldName !=="duedate"){
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
         console.log(JSON.stringify(listData));
         this.dropList=this.prepareItemArray(listData.list);
          
    });
    }


    
  }

   restoreField(editedObj,restoreFieldId,fieldIndex){
   //  alert("++++");
    document.getElementById(restoreFieldId).innerHTML = editedObj.options[editedObj.selectedIndex].text;
    var currentSelectedId = document.getElementById(restoreFieldId+"_"+fieldIndex+"_currentSelected");
    currentSelectedId.setAttribute("value",editedObj.value);
    this.showMyEditableField[fieldIndex] = true;
    // alert(editedVal);
    

  }
  selectBlurField(fieldIndex){
      // document.getElementById(restoreFieldId).innerHTML = editedVal;
      this.showMyEditableField[fieldIndex] = true;
      // alert(editedVal);
      

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
            data.renderType = "input";
            data.type="date";
            break;
            case "DateTime":
            data.title = field.title;
            // alert(field.readable_value.date.split(".")[0]+"++++++++++DateTime++++++++++++");
            data.value = field.readable_value;
            data.renderType = "input";
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
          // }
          fieldsBuilt.push(data);
          this.showMyEditableField.push((field.readonly == 1)?false:true);
          // this.makeFocused.push(new EventEmitter());
      }
    }

    return fieldsBuilt;

  }
  public prepareItemArray(list:any){
  var listItem=[];
     if(list.length>0){
         for(var i=0;list.length>i;i++){
          listItem.push({label:list[i].Name, value:list[i].Id});
       }
     }
return listItem;
}
public fileOverBase(fileInput:any):void {
    this.hasBaseDropZoneOver = true;
}

public fileDragLeave(fileInput: any){
    this.hasBaseDropZoneOver = false;
}

  public fileChangeEvent(fileInput: any):void {
  this.filesToUpload = <Array<File>> fileInput.target.files;
    this.hasBaseDropZoneOver = false;
        this.makeFileRequest("http://10.10.73.62:4200/upload", [], this.filesToUpload).then((result :Array<any>) => {
            for(var i = 0; i<result.length; i++){
                //this.sampleModel = this.sampleModel + "[[file:" +result[i].path + "]] ";
                var uploadedFileExtension = (result[i].originalname).split('.').pop();
                if(uploadedFileExtension == "png" || uploadedFileExtension == "jpg" || uploadedFileExtension == "jpeg" || uploadedFileExtension == "gif") {
                    this.ticketEditableDesc = this.ticketEditableDesc + "[[image:" +result[i].path + "|" + result[i].originalname + "]] ";
                } else{
                    this.ticketEditableDesc = this.ticketEditableDesc + "[[file:" +result[i].path + "|" + result[i].originalname + "]] ";
                }
            }
        }, (error) => {
            console.error(error);
            //this.sampleModel = "Error while uploading";
            this.ticketEditableDesc = this.ticketEditableDesc + "Error while uploading";
        });
}

public onFileDrop(fileInput:any): void {
    //console.log("file drop " + "File Name "+fileInput.dataTransfer.files[0].name+"File Size "+fileInput.dataTransfer.files[0].size);

    this.filesToUpload = <Array<File>> fileInput.dataTransfer.files;
    this.hasBaseDropZoneOver = false;
    this.makeFileRequest("http://10.10.73.62:4200/upload", [], this.filesToUpload).then((result :Array<any>) => {
            for(var i = 0; i<result.length; i++){
                //this.sampleModel = this.sampleModel + "[[file:" +result[i].path + "]] ";
                var uploadedFileExtension = (result[i].originalname).split('.').pop();
                if(uploadedFileExtension == "png" || uploadedFileExtension == "jpg" || uploadedFileExtension == "jpeg" || uploadedFileExtension == "gif") {
                    this.ticketEditableDesc = this.ticketEditableDesc + "[[image:" +result[i].path + "|" + result[i].originalname + "]] ";
                } else{
                    this.ticketEditableDesc = this.ticketEditableDesc + "[[file:" +result[i].path + "|" + result[i].originalname + "]] ";
                }
            }
        }, (error) => {
            console.error(error);
            //this.sampleModel = "Error while uploading";
            this.ticketEditableDesc = this.ticketEditableDesc + "Error while uploading";
        });
  }

public makeFileRequest(url: string, params: Array<string>, files: Array<File>) {
        return new Promise((resolve, reject) => {
            var formData: any = new FormData();
            var xhr = new XMLHttpRequest();
            for(var i = 0; i < files.length; i++) { 
                formData.append("uploads[]", files[i], files[i].name);
            }
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4) {
                    if (xhr.status == 200) {
                        //console.log("the responc " + JSON.parse(xhr.response))
                        resolve(JSON.parse(xhr.response));
                    } else {
                        reject(xhr.response);
                    }
                }
            };

            xhr.upload.onloadstart= (event) => {
                this.fileUploadStatus = true;
            };
            xhr.upload.onloadend = (event) => {
                   this.fileUploadStatus = false;                
            };
            
            xhr.open("POST", url, true);
            xhr.send(formData);
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
            document.getElementById(this.ticketId+'_'+postEditedText.EditedId).innerHTML=result.data;
          }
    
        });
    }

}
