import { Component, OnInit,Input,Output ,EventEmitter} from '@angular/core';
import {SummerNoteEditorService} from '../../../services/summernote-editor.service';
import { FileUploadService } from '../../../services/file-upload.service';
import { GlobalVariable } from '../../../config';

declare var jQuery:any;
@Component({
  selector: 'app-report-confirmation',
  templateUrl: './report-confirmation.component.html',
  styleUrls: ['./report-confirmation.component.css']
})
export class ReportConfirmationComponent implements OnInit {

  public reportData:any='this is test';
  public filesToUpload: Array<File>;
  public hasBaseDropZoneOver:boolean = false;
  public hasBaseDropZoneOverComment:boolean = false;
  public hasFileDroped:boolean = false;
  public fileUploadStatus:boolean = false;
  public dragTimeout;
  public form:any={
    description:''
  }
  @Input() title;
  @Input()  formData:any;
  @Input() updatedFieldValue:any;
  constructor(private editor:SummerNoteEditorService,private fileUploadService: FileUploadService) { }
  @Output() cancleToParent: EventEmitter<any> = new EventEmitter();
  @Output() saveFromParent: EventEmitter<any> = new EventEmitter();
  ngOnInit() {
    this.editor.initialize_editor('report_summernote','keyup',this);
    jQuery("#reportPopupId").click();
  }

canclePopup(){
  this.formData['updatedFieldValue']=this.updatedFieldValue
  this.cancleToParent.emit(this.formData);
}

saveReport(){
  this.formData['reportData']=this.form.description;
  this.saveFromParent.emit(this.formData);
}


//----------------------File Upload codes---------------------------------
public fileOverBase(fileInput:any,where:string,comment:string):void {
  if(where=="edit_comments"){
    
    jQuery("div[id^='dropble_comment_report_']").removeClass("dragdrop");

    if(jQuery("#Activity_content_"+comment).length >0)
    {
      jQuery("#dropble_comment_report_"+comment).addClass("dragdrop","true");
    }


    
  }else if(where=="comments")
  {
    
    jQuery("div[id^='dropble_comment_report_']").removeClass("dragdrop");
    jQuery("#dropble_comment_report_").addClass("dragdrop","true");
  }

    else{
      console.log("==in else fileOverBase==");
    this.hasBaseDropZoneOver = true;
  }
    
    if(this.dragTimeout != undefined && this.dragTimeout != "undefined"){
    clearTimeout(this.dragTimeout);
    }

}

public fileDragLeave(fileInput: any,where:string,comment:string){

var thisObj = this;
    if(this.dragTimeout != undefined && this.dragTimeout != "undefined"){
    clearTimeout(this.dragTimeout);
    }
     this.dragTimeout = setTimeout(function(){
     jQuery("div[id^='dropble_comment_report_']").removeClass("dragdrop");
     thisObj.hasBaseDropZoneOver = false;
    
     
    },500);
    
}


public fileUploadEvent(fileInput: any, comeFrom: string,where:string,comment:string):void {
    var editor_contents;
    var appended_content;
    if(where=="edit_comments"){
      //editor_contents=jQuery("#cke_Activity_content_"+comment).find("iframe").contents().find('body').html();
      editor_contents=jQuery("#Activity_content_"+comment).summernote('code'); // for summernote editor
      fileInput.preventDefault();
    }
   if(comeFrom == 'fileChange'){
        this.filesToUpload = <Array<File>> fileInput.target.files;
   } else if(comeFrom == 'fileDrop'){
        this.filesToUpload = <Array<File>> fileInput.dataTransfer.files;
   } else{
        this.filesToUpload = <Array<File>> fileInput.target.files;
   }

        if(where=="edit_comments"){
             jQuery("div[id^='dropble_comment_report_']").removeClass("dragdrop");
             jQuery("#comments_gif_"+comment).show();
          }
          else if(where=="comments")
          {
            jQuery("#dropble_comment_report_").removeClass("dragdrop","true");
            jQuery("#last_comments").show();
          }
          else{

             this.hasBaseDropZoneOver = false;
             this.fileUploadStatus = true;
          }
        this.fileUploadService.makeFileRequest(GlobalVariable.FILE_UPLOAD_URL, [], this.filesToUpload).then((result :Array<any>) => {
            for(var i = 0; i<result.length; i++){
                var uploadedFileExtension = (result[i].originalname).split('.').pop();
                result[i].originalname =  result[i].originalname.replace(/[^a-zA-Z0-9.]/g,'_'); 
                if(uploadedFileExtension == "png" || uploadedFileExtension == "jpg" || uploadedFileExtension == "jpeg" || uploadedFileExtension == "gif") {
                    if(where =="comments"){
                  
                      this.form.description = jQuery("#report_summernote").summernote('code') + "<p>[[image:" +result[i].path + "|" + result[i].originalname + "]] </p>";
                      jQuery("#report_summernote").summernote('code',this.form.description);
                        // jQuery('#detailEditor').summernote('code',this.form['description']+"[[image:" +result[i].path + "|" + result[i].originalname + "]] " +" ");
                        // this.form['description'] = jQuery('#detailEditor').summernote('code');
                    }
                } else{
                    if(where =="comments"){
                      this.form.description = jQuery("#report_summernote").summernote('code') + "<p>[[file:" +result[i].path + "|" + result[i].originalname + "]] </p>" +" ";
                      jQuery("#report_summernote").summernote('code',this.form.description);
                    }
                }
            }
            jQuery("#comments_gif_"+comment).hide();
            jQuery("#last_comments").hide();
            this.fileUploadStatus = false;
        }, (error) => {
             this.form.description  =  this.form.description + "Error while uploading";
            this.fileUploadStatus = false;
        });
}

}
