import { Component,ViewChild,Output } from '@angular/core';
import { StoryService} from '../../services/story.service';
import { NgForm } from '@angular/forms';
import {Router} from '@angular/router';
import { FileUploadService } from '../../services/file-upload.service';
import { GlobalVariable } from '../../config';
import {AccordionModule,DropdownModule,SelectItem,CalendarModule} from 'primeng/primeng';

declare var jQuery:any;    //Reference to Jquery

 @Component({
    selector: 'story-form',
    templateUrl: 'story-form.html',
    styleUrls: ['story-form.css'],
    providers: [FileUploadService, StoryService]     

})

export class StoryComponent 
{
    @Output() public options = {
        readAs: 'ArrayBuffer'
      };
    public dragTimeout;
    public storyFormData=[];
    public storyData={};
    public form={};
    //CkEditor Configuration Options
    public toolbar={toolbar : [
    [ 'Heading 1', '-', 'Bold','-', 'Italic','-','Underline','Link','NumberedList','BulletedList' ]
    ],removePlugins:'elementspath',resize_enabled:true};
    public filesToUpload: Array<File>;
    public hasBaseDropZoneOver:boolean = false;
    public hasFileDroped:boolean = false;
    editorData:string='';
    public fileUploadStatus:boolean = false;

    constructor(private fileUploadService: FileUploadService, private _service: StoryService, private _router:Router) {
        this.filesToUpload = [];
    }

  
    ngOnInit() 
    {
        this._service.getStoryFields(1,(response)=>
        {
              let jsonForm={};
              let DefaultValue;
               jsonForm['title'] ='';
               jsonForm['description'] ='';
              if(response.statusCode==200)
              {
                  response.data.story_fields.forEach(element => {
                    var  item = element.Id;
                    if(element.Type == 5){
                        element.DefaultValue=new Date().toLocaleString();
                    }else if(element.Type == 6){
                        DefaultValue=response.data.collaborators;
                    }else if(element.Type == 2){
                        DefaultValue=element.data; 
                    }           
                    jsonForm[item] = element.DefaultValue;
                    var priority=(element.Title=="Priority"?true:false);
                    var listItemArray=this.prepareItemArray(DefaultValue,priority,element.Title);
                    this.storyFormData.push(
                       {'lable':element.Title,'model':element.Id,'value':element.DefaultValue,'required':element.Required,'readOnly':element.ReadOnly,'type':element.Type,'values':listItemArray}
                       )
                  });
                this.form = jsonForm;
              }else{
                    console.log("storyFrom Component ngOnInit fail---");
              }
        });
    }

    /*
    @params    :  list,priority,status
    @ParamType :  any,boolean,string
    @Description: Preparing DropDown List.
    */
    public prepareItemArray(list:any,priority:boolean,status:string)
    {
      var listItem=[];
      if(list.length>0)
      {
        for(var i=0;list.length>i;i++)
        {
            listItem.push({label:list[i].Name, value:list[i].Id,priority:priority,type:status});
        }
      }
        return listItem;
    }

/*
---------------File Drag And Drop Methods *START*-----------------------
*/

    /*
    @params      : fileInput
    @ParamType   :  any
    @Description : Enabling the dropzone DIV on dragOver
    */
    
    public fileOverBase(fileInput:any):void 
    {
        this.hasBaseDropZoneOver = true;
        if(this.dragTimeout != undefined && this.dragTimeout != "undefined")
        { 
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
       if(comeFrom == 'fileChange') {
            this.filesToUpload = <Array<File>> fileInput.target.files;
       } else if(comeFrom == 'fileDrop') {
            this.filesToUpload = <Array<File>> fileInput.dataTransfer.files;
       } else {
            this.filesToUpload = <Array<File>> fileInput.target.files;
       }
            
            this.hasBaseDropZoneOver = false;
            this.fileUploadStatus = true;
            this.fileUploadService.makeFileRequest(GlobalVariable.FILE_UPLOAD_URL, [], this.filesToUpload).then(
                (result :Array<any>) => {
    
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
                console.error("Error occured in story-formcomponent::fileUploadEvent"+error);
                this.form['description'] = this.form['description'] + "Error while uploading";
                this.fileUploadStatus = false;
            });
    }
    

/*
------------File Upload Methods **END**--------------------
*/

    /*
    @Description:Creating Ticket/Story 

    */
    saveStory(){
         this._service.saveStory(this.form,(response)=>{
         });
    }

}
