import { Component, OnInit,ViewChild } from '@angular/core';
import { Router,ActivatedRoute } from '@angular/router';
import { Headers, Http } from '@angular/http';
import { AjaxService } from '../../ajax/ajax.service';
import { StoryService} from '../../services/story.service';
import {NgForm} from '@angular/forms';

declare var jQuery:any;
@Component({
  selector: 'app-story-edit',
  templateUrl: './story-edit.component.html',
  styleUrls: ['./story-edit.component.css'],
  providers:[StoryService]
})

export class StoryEditComponent implements OnInit {

    private ticketData:any=[];
    private ticketid;
    private url_TicketId;
    private description;
    public form={};
  private fieldsData = [];
  // private fieldsBindingArray=[];
  private showMyEditableField =[];
  public toolbar={toolbar : [
    [ 'Heading 1', '-', 'Bold','-', 'Italic','-','Underline','Link','NumberedList','BulletedList' ]
]};
public filesToUpload: Array<File>;
public hasBaseDropZoneOver:boolean = false;
public hasFileDroped:boolean = false;
public fileUploadStatus:boolean = false;

  constructor(private _ajaxService: AjaxService,private _service: StoryService,
    public _router: Router,
    private http: Http,private route: ActivatedRoute) { 
           this.filesToUpload = [];
    }


  ngOnInit() {
    this.route.params.subscribe(params => {
            this.url_TicketId = params['id'];
        });
setTimeout(()=>{
  this._ajaxService.AjaxSubscribe("story/edit-ticket",{ticketId:this.url_TicketId},(data)=>
    { 
       
         
         this.ticketData = data.data;
         this.description=data.data.CrudeDescription;
         //console.log("++++++++++++++--"+data.data.CrudeDescription);
         this.fieldsData = this.fieldsDataBuilder(data.data.Fields,data.data.TicketId);
        //  var t = this.fieldsData.length;
        //  this.fieldsBindingArray[t];
        // console.log("Field Data----"+this.fieldsData);
         
    });

},150);
    
    
  }

   editThisField(event,fieldIndex){
    console.log(event.target.id);
    // var thisFieldId = event.target.id;
    // var thisField;
    // var replaceHtml ;
    // if(thisFieldId !="" && thisFieldId !=null){
    //     thisField = document.getElementById(thisFieldId);
    //     replaceHtml = document.createElement("input");
    //     replaceHtml.setAttribute("value",thisField.textContent);
    //     replaceHtml.setAttribute("id",thisFieldId);//"<input type='text' value='"+thisField.textContent+"'/>"
    //     thisField.parentNode.replaceChild(replaceHtml,thisField);
    // }
    
    this.showMyEditableField[fieldIndex] = false;

  }


  fieldsDataBuilder(fieldsArray,ticketId){
    let fieldsBuilt = [];
    let data = {title:"",value:"",readonly:true,required:true,id:"",fieldDataId:"",fieldName:"",fieldType:"",renderType:"",type:"",listdata:[]};
    for(let field of fieldsArray){
      if(field.field_name != "customfield_2"){
      data = {title:"",value:"",readonly:true,required:true,id:"",fieldDataId:"",fieldName:"",fieldType:"",renderType:"",type:"",listdata:[]};
          switch(field.field_type){
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
            data.value = field.readable_value.UserName;
            data.renderType = "select";
            data.listdata = this.ticketData.collaborators;
            data.fieldDataId = field.readable_value.CollaboratorId;
            
            break;
            // case "Checkbox":
            // break;
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
          // if(field.field_type == "Bucket" || field.field_type == "Team List" || field.field_type == "Team List"){
          //   data.type = "List";
          // }else{
            data.fieldType = field.field_type;
          // }
          data.fieldName =  field.Id;

          data.listdata=this.prepareItemArray(data.listdata);

          fieldsBuilt.push(data);
          this.showMyEditableField.push((field.readonly == 1)?false:true);
          // this.fieldsBindingArray.push(field.Id);
      }
    }
console.log(JSON.stringify(fieldsBuilt));
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
  editStory(edit_data){
      console.log("===Edit Data==="+JSON.stringify(edit_data));
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

public fileChangeEvent(fileInput: any):void {
  this.filesToUpload = <Array<File>> fileInput.target.files;
    this.hasBaseDropZoneOver = false;
        this.makeFileRequest("http://10.10.73.33:4201/upload", [], this.filesToUpload).then((result :Array<any>) => {
            for(var i = 0; i<result.length; i++){
                //this.sampleModel = this.sampleModel + "[[file:" +result[i].path + "]] ";
                var uploadedFileExtension = (result[i].originalname).split('.').pop();
                if(uploadedFileExtension == "png" || uploadedFileExtension == "jpg" || uploadedFileExtension == "jpeg" || uploadedFileExtension == "gif") {
                    this.description = this.description + "[[image:" +result[i].path + "|" + result[i].originalname + "]] ";
                } else{
                    this.description = this.description + "[[file:" +result[i].path + "|" + result[i].originalname + "]] ";
                }
            }
        }, (error) => {
            console.error(error);
            //this.sampleModel = "Error while uploading";
            this.description = this.description + "Error while uploading";
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

}
