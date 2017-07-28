import { Component,ViewChild,Output } from '@angular/core';
import { StoryService} from '../../services/story.service';
import { NgForm } from '@angular/forms';
import {Router,ActivatedRoute} from '@angular/router';
import { FileUploadService } from '../../services/file-upload.service';
import { GlobalVariable } from '../../config';
import {AccordionModule,DropdownModule,SelectItem,CalendarModule,CheckboxModule} from 'primeng/primeng';
import { MentionService } from '../../services/mention.service';
import { AjaxService } from '../../ajax/ajax.service';
import {SummerNoteEditorService} from '../../services/summernote-editor.service';
import {SharedService} from '../../services/shared.service'; //added by Ryan
import { ProjectService } from '../../services/project.service';
declare var jQuery:any;    //Reference to Jquery
//declare const CKEDITOR;
//declare var tinymce:any;
declare var summernote:any;

 @Component({
    selector: 'story-form',
    templateUrl: 'story-form.html',
    styleUrls: ['story-form.css'],
    providers: [FileUploadService, StoryService,ProjectService]     

})

export class StoryComponent 
{
    @Output() public options = {
        readAs: 'ArrayBuffer'
      };
    public selectedTickets: string[] = [];
    public taskArray:any;
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
    public projectName;
    public projectId:any;
    constructor(private projectService:ProjectService,private fileUploadService: FileUploadService, private _service: StoryService, private _router:Router,private mention:MentionService,private _ajaxService: AjaxService,private editor:SummerNoteEditorService,private shared:SharedService,private route:ActivatedRoute) {
        this.filesToUpload = [];
    }

  
    ngOnInit() 
    {    
        var thisObj = this;
  thisObj.route.queryParams.subscribe(
      params => 
      { 
      thisObj.route.params.subscribe(params => {
           thisObj.projectName=params['projectName'];
           thisObj.shared.change(this._router.url,thisObj.projectName,'Dashboard','New',thisObj.projectName);            thisObj.projectService.getProjectDetails(thisObj.projectName,(data)=>{
                if(data.statusCode ==200) {
                thisObj.projectId=data.data.PId;  
                 let jsonForm={};//added by Ryan
        thisObj._service.getStoryFields(thisObj.projectId,(response)=>
        {
            
            thisObj.taskArray=response.data.task_types;
              
              let DefaultValue;
               jsonForm['title'] ='';
               jsonForm['description'] ='';
               //jsonForm['tasks']=this.selectedTickets;--> removed
               jsonForm['default_task']=[];
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
                    var listItemArray: any;
                     listItemArray=thisObj.prepareItemArray(DefaultValue,priority,element.Title);
                 //   alert(JSON.stringify(listItemArray[0].filterValue));
                    thisObj.storyFormData.push(
                       {'lable':element.Title,'model':element.Field_Name,'value':element.DefaultValue,'required':element.Required,'readOnly':element.ReadOnly,'type':element.Type,'values':listItemArray[0].filterValue,"labels":listItemArray}
                       )
                  });
                //this.form = jsonForm;-->removed
              }else{
                    console.log("storyFrom Component ngOnInit fail---");
              }
        });
          jQuery("#title").keydown(function(e){
        if (e.keyCode == 13 && !e.shiftKey)
        {
            e.preventDefault();
        }
    }); 
    
    /* Added By Ryan */ 
    this._service.getPreferences((response)=>
    {
        var preferences=response.data.PreferenceItems;
        var preferences_array=preferences.split(',');
        for(let item of preferences_array)
        {
            
            this.selectedTickets.push(item.trim());
        }
        jsonForm['tasks']=this.selectedTickets;//shifted by Ryan from above
    })
        this.form = jsonForm;//shifted by Ryan from above

      
               
       }else{
       this._router.navigate(['project',this.projectName,'error']); 
       }
                
        });
        });
       
           })
 
       
    }

    /**
     * @author:Ryan Marshal
     * @description:This is used for initializing the summernote editor
     */
    ngAfterViewInit()
    {

        var formobj=this;
        this.editor.initialize_editor('summernote','keyup',formobj);
        jQuery(document)
    .one('focus.autoExpand', 'textarea.autoExpand', function(){
        var savedValue = this.value;
        this.value = '';
        this.baseScrollHeight = this.scrollHeight;
        this.value = savedValue;
    })
    .on('input.autoExpand', 'textarea.autoExpand', function(){
      var minRows = this.getAttribute('data-min-rows')|0, rows;
        this.rows = minRows;
        rows = Math.ceil((this.scrollHeight - this.baseScrollHeight) / 17);
        var newrows = Math.floor(this.scrollHeight/30);
        this.rows = newrows;
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
        var listMainArray=[];
      if(list.length>0)
      {
        for(var i=0;list.length>i;i++)
        {
            listItem.push({label:list[i].Name, value:list[i].Id,priority:priority,type:status});
        }
      }
       listMainArray.push({type:"",filterValue:listItem});
     // listMainArray["type"]="sfads";
     // listMainArray["filterValue"]=listItem;
     // alert(JSON.stringify(listMainArray)+"---"+listMainArray["type"]);
        return listMainArray;
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
                   result[i].originalname =  result[i].originalname.replace(/[^a-zA-Z0-9.]/g,'_'); 
                    var uploadedFileExtension = (result[i].originalname).split('.').pop();
                    if(uploadedFileExtension == "png" || uploadedFileExtension == "jpg" || uploadedFileExtension == "jpeg" || uploadedFileExtension == "gif") {
                        jQuery('#summernote').summernote('code',this.form['description']+"<p>[[image:" +result[i].path + "|" + result[i].originalname + "]]</p>" +" ");
                        this.form['description'] = jQuery('#summernote').summernote('code');
                    } else{
                        jQuery('#summernote').summernote('code',this.form['description']+"<p>[[file:" +result[i].path + "|" + result[i].originalname + "]]</p>" +" ");
                        this.form['description'] = jQuery('#summernote').summernote('code');
                    }
                }
                this.fileUploadStatus = false;
            }, (error) => {
                console.error("Error occured in story-formcomponent::fileUploadEvent"+error);
                this.form['description'] =jQuery('#summernote').summernote('code') + "Error while uploading";
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
        var thisObj = this;
        var editor=jQuery('#summernote').summernote('code');
        editor=jQuery(editor).text().trim();
        this.form['description']=jQuery('#summernote').summernote('code'); //for summernote editor
      
       if(editor!='')
       {
             this.form['default_task']=[];
                for (let task of this.form['tasks']) {
                for(let tsk of this.taskArray) {
                         if(tsk.Id == task) 
                       this.form['default_task'].push(tsk);
                 };
               }  
              delete this.form['tasks']; 
            this._service.saveStory(thisObj.projectId,thisObj.form,(response)=>{
                if(response.statusCode == 200){
                     thisObj._router.navigate(['project',thisObj.projectName,'list']);
                }
                    });
       }
       
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


    /** Only for Testing **/

    public sendMail()
    {
        var recepients={
            emaillist:['ryan_marshal@hotmail.com','kishore.neelam@techo2.com']
        }
        this._ajaxService.AjaxSubscribe('story/send-mail',recepients,(data)=>
        {

        })
    }     


}
