import { Component,ViewChild,Output } from '@angular/core';
import { StoryService} from '../../services/story.service';
import { NgForm } from '@angular/forms';
import { TinyMCE } from '../../tinymce.component';

import {AccordionModule,DropdownModule,SelectItem,CalendarModule} from 'primeng/primeng';     
 @Component({
    selector: 'story-form',
    templateUrl: 'story-form.html',
    styleUrls: ['story-form.css'],
    providers: [StoryService]     

})

export class StoryComponent {
@Output() public options = {
    readAs: 'ArrayBuffer'
  };
    public storyFormData=[];
    public storyData={};
    public form={};
    public toolbar={toolbar : [
    [ 'Heading 1', '-', 'Bold','-', 'Italic','-','Underline','Link','NumberedList','BulletedList' ]
]};
public filesToUpload: Array<File>;
//sampleModel:string = "";
public hasBaseDropZoneOver:boolean = false;
public hasFileDroped:boolean = false;
editorData:string='';
public fileUploadStatus:boolean = false;

    constructor( private _service: StoryService) {
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
                    var listItemArray=this.prepareItemArray(DefaultValue);
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
    //fileInput.preventDefault();
     //console.log("drag over " + fileInput);
}

public fileDragLeave(fileInput: any){
    this.hasBaseDropZoneOver = false;
    //console.log("drag leave " + fileInput); 
}

public fileChangeEvent(fileInput: any):void {
   // console.log("cahnge event " + fileInput.name +"------- " + fileInput.size);
  this.filesToUpload = <Array<File>> fileInput.target.files;
    this.hasBaseDropZoneOver = false;
        this.makeFileRequest("http://10.10.73.62:4200/upload", [], this.filesToUpload).then((result :Array<any>) => {
            for(var i = 0; i<result.length; i++){
                //this.sampleModel = this.sampleModel + "[[file:" +result[i].path + "]] ";
                var uploadedFileExtension = (result[i].originalname).split('.').pop();
                if(uploadedFileExtension == "png" || uploadedFileExtension == "jpg" || uploadedFileExtension == "jpeg" || uploadedFileExtension == "gif") {
                    this.form['description'] = this.form['description'] + "[[image:" +result[i].path + "|" + result[i].originalname + "]] ";
                } else{
                    this.form['description'] = this.form['description'] + "[[file:" +result[i].path + "|" + result[i].originalname + "]] ";
                }
            }
        }, (error) => {
            console.error(error);
            //this.sampleModel = "Error while uploading";
            this.form['description'] = this.form['description'] + "Error while uploading";
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
                    this.form['description'] = this.form['description'] + "[[image:" +result[i].path + "|" + result[i].originalname + "]] ";
                } else{
                    this.form['description'] = this.form['description'] + "[[file:" +result[i].path + "|" + result[i].originalname + "]] ";
                }
            }
        }, (error) => {
            console.error(error);
            //this.sampleModel = "Error while uploading";
            this.form['description'] = this.form['description'] + "Error while uploading";
        });
  }

public makeFileRequest(url: string, params: Array<string>, files: Array<File>) {
        return new Promise((resolve, reject) => {
            var formData: any = new FormData();
            var xhr = new XMLHttpRequest();
            // console.log("files length "+files.length);
            for(var i = 0; i < files.length; i++) { 
                formData.append("uploads[]", files[i], files[i].name);
            }
              //formData.append("uploads[]", files, files.name);

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

saveStory(){
    console.log("post____data");
     this._service.saveStory(this.form,(response)=>{
     });
}
ckeditorfocus(event){
//alert('focud');    
}
ckeditordragstart(event){
//alert('ckeditordragstart');    
}

}
