import { Component } from '@angular/core';
import { StoryService} from '../../services/story.service';

@Component({
    selector: 'story-form',
    templateUrl: 'story-form.html',
    providers: [StoryService]     
    	
})

export class StoryComponent {

    public storyFormData=[];
    public storyData={};
    public form={};
    public toolbar={toolbar : [
    [ 'Heading 1', '-', 'Bold','-', 'Italic','-','Underline','Link','NumberedList','BulletedList' ]
]};
filesToUpload: Array<File>;
//sampleModel:string = "";
public hasBaseDropZoneOver:boolean = false;
public hasFileDroped:boolean = false;

    constructor( private _service: StoryService) {
        this.filesToUpload = [];
     }

    ngOnInit() {
     
        this._service.getStoryFields(1,(response)=>{
              let jsonForm={};
              let DefaultValue;
             // console.log("new_data"+JSON.stringify(response));
              if(response.statusCode==200){
                  response.data.story_fields.forEach(element => {
                    var  item = element.Field_Name;
                    
                    if(element.Type == 5){
                        element.DefaultValue=new Date().toLocaleString();
                    }else if(element.Type == 6){
                        DefaultValue=response.data.collaborators;
                    }else if(element.Type == 2){
                        DefaultValue=element.data; 
                    } 
                    console.log("Default values"+JSON.stringify(DefaultValue));
                    jsonForm[item] = element.DefaultValue;
                   this.storyFormData.push(
                       {'lable':element.Title,'model':element.Field_Name,'value':element.DefaultValue,'required':element.Required,'readOnly':element.ReadOnly,'type':element.Type,'values':DefaultValue}
                       )
                  });
                this.form = jsonForm;
                  console.log(JSON.stringify(this.form ));
            }else{
            console.log("fail---");
            }
        });
    }

public fileOverBase(e:any):void {
    this.hasBaseDropZoneOver = e;
}

public fileChangeEvent(fileInput: any):void {
  this.filesToUpload = <Array<File>> fileInput.target.files;
    this.hasBaseDropZoneOver = false;
        this.makeFileRequest("http://10.10.73.62:4200/upload", [], this.filesToUpload).then((result :Array<any>) => {
            for(var i = 0; i<result.length; i++){
                //this.sampleModel = this.sampleModel + "[[file:" +result[i].path + "]] ";
                this.form['description'] = this.form['description'] + "[[file:" +result[i].path + "]] ";
            }
        }, (error) => {
            console.error(error);
            //this.sampleModel = "Error while uploading";
            this.form['description'] = "Error while uploading";
        });
}
public onFileDrop(fileInput:any): void{
    //this.hasFileDroped = fileInput;
    this.filesToUpload = <Array<File>> fileInput.dataTransfer.files;

      this.makeFileRequest("http://10.10.73.62:4200/upload", [], this.filesToUpload).then((result :Array<any>) => {
            for(var i = 0; i<result.length; i++){
                //this.sampleModel = this.sampleModel + "[[file:" +result[i].path + "]] ";
                this.form['description'] = this.form['description'] + "[[file:" +result[i].path + "]] ";
            }
        }, (error) => {
            console.error(error);
            //this.sampleModel = "Error while uploading";
            this.form['description'] = "Error while uploading";
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
            }
            xhr.open("POST", url, true);
            xhr.send(formData);
        });
    }

saveStory(){
    console.log("post____data"+JSON.stringify(this.form));
    this._service.saveStory(this.form,(response)=>{
    });
}
}