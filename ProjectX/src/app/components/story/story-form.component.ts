import { Component,ViewChild,Output } from '@angular/core';
import { StoryService} from '../../services/story.service';
import { NgForm } from '@angular/forms';
import { TinyMCE } from '../../tinymce.component';
import {Router} from '@angular/router';
import { FileUploadService } from '../../services/file-upload.service';
import { GlobalVariable } from '../../config';

import {AccordionModule,DropdownModule,SelectItem,CalendarModule} from 'primeng/primeng';

declare var jQuery:any;     
 @Component({
    selector: 'story-form',
    templateUrl: 'story-form.html',
    styleUrls: ['story-form.css'],
    providers: [FileUploadService, StoryService]     

})

export class StoryComponent {
@Output() public options = {
    readAs: 'ArrayBuffer'
  };
 public dragTimeout;
    public storyFormData=[];
    public storyData={};
    public form={};
    public toolbar={toolbar : [
    [ 'Heading 1', '-', 'Bold','-', 'Italic','-','Underline','Link','NumberedList','BulletedList' ]
],removePlugins:'elementspath',resize_enabled:false};
public filesToUpload: Array<File>;
//sampleModel:string = "";
public hasBaseDropZoneOver:boolean = false;
public hasFileDroped:boolean = false;
editorData:string='';
public fileUploadStatus:boolean = false;

    constructor(private fileUploadService: FileUploadService, private _service: StoryService, private _router:Router) {
        this.filesToUpload = [];
    }
  
    ngOnInit() {
     
        this._service.getStoryFields(1,(response)=>{
              let jsonForm={};
              let DefaultValue;
               jsonForm['title'] ='';
               jsonForm['description'] ='';
             // console.log("new_data"+JSON.stringify(response));
              if(response.statusCode==200){
                  response.data.story_fields.forEach(element => {
                    var  item = element.Id;
                    
                    if(element.Type == 5){
                        element.DefaultValue=new Date().toLocaleString();
                    }else if(element.Type == 6){
                        DefaultValue=response.data.collaborators;
                    }else if(element.Type == 2){
                        DefaultValue=element.data; 
                    }           
                    console.log("Default values"+JSON.stringify(DefaultValue));
                    jsonForm[item] = element.DefaultValue;
                    var priority=(element.Title=="Priority"?true:false);
                    var listItemArray=this.prepareItemArray(DefaultValue,priority,element.Title);
                   this.storyFormData.push(
                       {'lable':element.Title,'model':element.Id,'value':element.DefaultValue,'required':element.Required,'readOnly':element.ReadOnly,'type':element.Type,'values':listItemArray}
                       )
                  });
                this.form = jsonForm;
                  console.log(JSON.stringify(this.form ));
            }else{
            console.log("fail---");
            }
        });
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
                    this.form['description'] = this.form['description'] + "[[image:" +result[i].path + "|" + result[i].originalname + "]] ";
                } else{
                    this.form['description'] = this.form['description'] + "[[file:" +result[i].path + "|" + result[i].originalname + "]] ";
                }
            }
            this.fileUploadStatus = false;
        }, (error) => {
            console.error(error);
            this.form['description'] = this.form['description'] + "Error while uploading";
            this.fileUploadStatus = false;
        });
}

saveStory(){
    console.log("post____data");
     this._service.saveStory(this.form,(response)=>{
     });
}

}
