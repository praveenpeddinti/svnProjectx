import { Component,ViewChild,EventEmitter,Output, OnInit } from '@angular/core';
import {SummerNoteEditorService} from '../../services/summernote-editor.service';
import { AjaxService } from '../../ajax/ajax.service';
import { Router, ActivatedRoute,NavigationExtras } from '@angular/router';
import { ProjectService } from '../../services/project.service';
import { BucketService } from '../../services/bucket.service';

declare var summernote:any;
declare var jQuery:any;
@Component({
  selector: 'app-create-bucket',
  inputs:["Type","formData","bucketId","projectId","projectName"],
  templateUrl: './create-bucket.component.html',
  providers: [BucketService,ProjectService],
  styleUrls: ['./create-bucket.component.css']
})
export class CreateBucketComponent implements OnInit {

  private Type:String;
  private projectName:String;
  private bucketId:String;
  private projectId:String;
  private formData:any={};
  private formB={
    Id:'',
    title:'',
    description:'',
    startDateVal:new Date(),
    dueDateVal:'',
    setCurrent:false,
   
    selectedUserFilter:'',
    
    };
  private submitted=false;
  private role: number = 1;
  private FilterOption=[];
  private FilterOptionToDisplay=[];
  private BucketFilterOption=[];
  private BucketFilterOptionToDisplay=[];
  private bucketSuccessMsg="";
  private dueDateMsg="";
  private bucketMsgClass="";
  private prevBucketName = "";
  private minDate = new Date();


  private typeAheadTimer=undefined;
private typeAheadResults ={
  flag:false,
  msg:""
} ;
 public spinnerSettings={
      color:"",
      class:""
    };
@ViewChild('addBucketForm') bucketForm:any;
@Output() bucketUpdated: EventEmitter<any> = new EventEmitter();
  constructor(private editor:SummerNoteEditorService,private _router: Router,
        private _service: BucketService,private projectService:ProjectService, private _ajaxService: AjaxService) { }

  ngAfterViewInit() { 
         this._service.getResponsibleFilter(this.projectId,this.role,(response) => { 
                        this.FilterOptionToDisplay=this.prepareItemArray(response.data,false,'Member');
                        this.FilterOption=this.FilterOptionToDisplay[0].filterValue;
                    }); 

                    var formobj=this;
        this.editor.initialize_editor('bucketDescId','keyup',formobj);
        if(this.Type == "Edit"){
          jQuery('#bucketDescId').summernote('code',this.formB['description']);
         
          this.typeAheadResults.flag = true;
        }
    }

  ngOnInit() {
               
    if(this.Type == "New"){
    this.formB['Id']='';
    this.formB['title']='';
    this.formB['description']='';
    this.formB['startDateVal']=new Date();
    this.formB['dueDateVal']='';
    this.formB['setCurrent']=false;
    
    this.formB['selectedUserFilter']='';
    
    }else{
    
      this.formB = this.formData;
      this.prevBucketName = this.formB["title"];
    }

   
  }
/**
 * @description Prepares the bucket details array.
 */
private prepareItemArray(list:any,priority:boolean,status){
        var listItem=[];
        var listMainArray=[];
        if(list.length>0) { 
            if(status == "Member") {
                listItem.push({label:"Select Owner ", value:"",priority:priority,type:status});
            }else if(status == "bucket"){
                listItem.push({label:"Select Bucket Type", value:"",priority:priority,type:status});
            }else{
                listItem.push({label:list[0].Name, value:"",priority:priority,type:status});
           }
           for(var i=0;list.length>i;i++){
              listItem.push({label:list[i].Name, value:list[i].Id,priority:priority,type:status});
           }
        }
        listMainArray.push({type:"",filterValue:listItem});
        return listMainArray;
    }

public editorDesc="";
/**
 * @description To add or edit the bucket to the particulr project.
 */

BucketForAddorEdit(event){
  var editor=jQuery('#bucketDescId').summernote('code');
  
    editor=editor.replace(/\&nbsp;*\s*(<br>)*/gi,'');
    editor = editor.replace(/^(<p>(<br>)*\s*(<br>)*<\/p>)*(<br>)*|(<p>(<br>)*\s*(<br>)*<\/p>)*(<br>)*$/gi, "");
   
    this.formB['description']=editor;
    
   
    
    var startDate = this.formB['startDateVal'].toLocaleDateString();
    var endDate=new Date(this.formB['dueDateVal']).toLocaleDateString();
   if(endDate>=startDate){
          this.addBucket();
    }else{
          this.bucketMsgClass='fielderror';
          this.bucketSuccessMsg = 'End Date is must be greater or equal to Start Date.';
     }

}

/**
 * @description To add the bucket to the particulr project.
*/
addBucket(){
    if(this.Type == "New"){
    
        this._service.saveBucket(this.projectId,this.formB,(response)=>{
        
        if(response.data["status"]=='failure'){
          this.bucketMsgClass='fielderror';
          this.bucketSuccessMsg = 'Bucket already exist';
        }else if(response.data["status"]=='current'){
          this.bucketMsgClass='fielderror';
          this.bucketSuccessMsg = 'Current Bucket is exist';
        }else{
          this.bucketMsgClass='timelogSuccessMsg';
          this.bucketSuccessMsg = 'Bucket added successfully';
          setTimeout(()=>{
            this._router.navigate(['project',this.projectName,'bucket'],{queryParams:{BucketId:response.data["BucketId"]}});
          },1500);
        }
        });
    }else{
    
      this._service.updateBucket(this.projectId,this.bucketId,this.formB,(response)=>{
        
          this.bucketUpdated.emit(response.data);
        });
    }
}    

/**
 * @description Checking the given bucket name with existing buckets to prevent from duplicates.
 */
checkBucketName(event){
  var titlePattern = /^[a-zA-Z0-9\s\.\-_]+$/;
  clearTimeout(this.typeAheadTimer);
  
  if(event.trim()=='' || event.trim() == undefined || titlePattern.test(event) == false || (event.trim()).toLowerCase() == (this.prevBucketName).toLowerCase()){
    this.typeAheadResults.flag = false;
    this.typeAheadResults.msg = "";
    if(event.trim() == ""){
        this.typeAheadResults.flag = false;
    
        this.spinnerSettings.color="red";
        this.spinnerSettings.class = "fa fa-times";
    }else if(titlePattern.test(event.trim()) == false){
        this.spinnerSettings.color="red";
        this.spinnerSettings.class = "fa fa-times";
        this.typeAheadResults.msg = "";
      }
      else if((event.trim()).toLowerCase() == (this.prevBucketName).toLowerCase()){
        this.typeAheadResults.flag = true;
        this.typeAheadResults.msg = "";
        this.spinnerSettings.color="green";
        this.spinnerSettings.class = "fa fa-check";
        }else{
        this.spinnerSettings.color="";
        this.spinnerSettings.class = "";
      }
    
  }else{
    this.typeAheadResults.flag = false;
    this.typeAheadResults.msg = "";
    this.spinnerSettings.color="blue";
    this.spinnerSettings.class = "fa fa-spinner fa-spin";
 
    this.typeAheadTimer = setTimeout(()=>{
          var postData = {
            projectId:this.projectId,
            bucketName:event.trim()
          };
          this._ajaxService.AjaxSubscribe("bucket/check-bucket-name",postData,(result)=>
          { 
              if(result.data.available == "Yes"){
                  this.typeAheadResults.flag = true;
                  this.typeAheadResults.msg = "";
                  this.spinnerSettings.color="green";
                  this.spinnerSettings.class = "fa fa-check";
              }else{
                this.typeAheadResults.flag = false;
                this.typeAheadResults.msg = result.message;
                this.spinnerSettings.color="red";
                this.spinnerSettings.class = "fa fa-times";
              }
          });

    },2000);
 }
}
/**
 * @description To clearing the form on cancel or closing the popup
 */
clearForm(resetEditForm){

  this.bucketSuccessMsg="";
  this.bucketMsgClass="";
  this.dueDateMsg="";

    this.formB['startDateVal']=new Date();
    jQuery('#bucketDescId').summernote('code','');
    this.typeAheadResults ={
              flag:false,
              msg:""
            } ;
    this.spinnerSettings={
          color:"",
          class:""
        };
        clearTimeout(this.typeAheadTimer);
          if(this.Type == "Edit"){
                this.formB['Id']=resetEditForm.Id;
                this.formB["title"]=resetEditForm.title;
                this.formB['description']=resetEditForm.description;
                this.formB['startDateVal']=resetEditForm.startDateVal;
                this.formB['dueDateVal']=resetEditForm.dueDateVal;
                this.formB['setCurrent']=resetEditForm.setCurrent;
               
                this.formB['selectedUserFilter']=resetEditForm.selectedUserFilter;
          this.typeAheadResults.flag = true;
            jQuery('#bucketDescId').summernote('code',resetEditForm.description);
            this.prevBucketName = this.formB["title"];
          }
        jQuery('body').removeClass('modal-open');
    
}

/**
 *@description To reset the form on cancel or closing the popup based on bucket type
*/
resetForm(){ 
if(this.Type=="New"){
 jQuery('#bucketDescId').summernote('code','');
 this.bucketForm.reset();  
}
else{
 this.clearForm(this.formData);
  }
}
}

