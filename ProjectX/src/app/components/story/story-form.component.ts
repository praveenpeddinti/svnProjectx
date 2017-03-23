import { Component,ViewChild,Output } from '@angular/core';
import { StoryService} from '../../services/story.service';
import { NgForm } from '@angular/forms';
import {Router} from '@angular/router';
import { FileUploadService } from '../../services/file-upload.service';
import { GlobalVariable } from '../../config';
import {AccordionModule,DropdownModule,SelectItem,CalendarModule,CheckboxModule} from 'primeng/primeng';
import { MentionService } from '../../services/mention.service';
import { AjaxService } from '../../ajax/ajax.service';

declare var jQuery:any;    //Reference to Jquery
declare const CKEDITOR;

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
    public selectedTickets: string[] = ['UI','PeerReview','QA'];
    public defaultTasksShow:boolean=true;
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

    constructor(private fileUploadService: FileUploadService, private _service: StoryService, private _router:Router,private mention:MentionService,private _ajaxService: AjaxService) {
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
               jsonForm['tasks']=this.selectedTickets;
         
              if(response.statusCode==200)
              {
                  response.data.story_fields.forEach(element => {
                    var  item = element.Field_Name;
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
                       {'lable':element.Title,'model':element.Field_Name,'value':element.DefaultValue,'required':element.Required,'readOnly':element.ReadOnly,'type':element.Type,'values':listItemArray}
                       )
                  });
                this.form = jsonForm;
              }else{
                    console.log("storyFrom Component ngOnInit fail---");
              }
        });
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
                          mention.push({"name":data.data[i].Name,"Profile":data.data[i].ProfilePic});
                        }
                      callback(mention);
                    });
                      }
                  }
                },
            editableAtwhoQueryAttrs: {
                    "data-fr-verified": true
            },
            displayTpl:"<li value='${name}' name='${name}'><img width='20' height='20' src='http://10.10.73.77${Profile}'/> ${name}</li>",
            }
            var editor=evt.editor;
            this.mention.load_atwho(editor,at_config);
        });
      })
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

     /*
    @params    :  eventVal,whichDrop
    @ParamType :  any,string
    @Description: Based on filed:planlevel default Tasks Div will  show/hide.
    */
    dropChange(eventVal,whichDrop){
        if(whichDrop == "Plan Level"){
              if(eventVal==1){
                this.defaultTasksShow = true;
              }else{
                this.defaultTasksShow = false;
              }

        }
    }


}
