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
  private form={
    Id:'',
    title:'',
    description:'',
    startDateVal:new Date(),
    dueDateVal:'',
    setCurrent:false,
    // sendReminder:true   ,
    selectedUserFilter:'',
    // selectedBucketTypeFilter:''
    };
//---------------------------------------------//
  private submitted=false;
  private role: number = 1;
  private FilterOption=[];
  private FilterOptionToDisplay=[];
  private BucketFilterOption=[];
  private BucketFilterOptionToDisplay=[];
  private bucketSuccessMsg="";
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
@ViewChild('addBucketForm') bucketForm:HTMLFormElement;
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
          jQuery('#bucketDescId').summernote('code',this.form['description']);
          // this.checkBucketName(this.form['title']);
          this.typeAheadResults.flag = true;
        }
    }

  ngOnInit() {
                // alert(this.Type+"-----ngOnInit------"+this.projectId)
                // alert("Form___data___"+JSON.stringify(this.formData))

    if(this.Type == "New"){
    this.form['Id']='';
    this.form['title']='';
    this.form['description']='';
    this.form['startDateVal']=new Date();
    this.form['dueDateVal']='';
    this.form['setCurrent']=false;
    // this.form['sendReminder']=true;
    this.form['selectedUserFilter']='';
    // this.form['selectedBucketTypeFilter']='';
    }else{
      // alert("onIntelse+++++"+JSON.stringify(this.formData));
      this.form = this.formData;
      this.prevBucketName = this.form.title;
    }

   
  }

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


BucketForAddorEdit(event){

  // alert("BucketForAddorEdit");
// if(event=='New')
  this.addBucket();
// else
//   this.editBucket();
}

addBucket(){
    
    var editor=jQuery('#bucketDescId').summernote('code');
    editor=jQuery(editor).text().trim();
    this.form['description']=jQuery('#bucketDescId').summernote('code').trim();

    if(this.Type == "New"){
        this._service.saveBucket(this.projectId,this.form,(response)=>{
        this._router.navigate(['project',this.projectName,'bucket'],{queryParams:{BucketId:response.data["BucketId"]}});
        if(response.data["status"]=='failure'){
          this.bucketMsgClass='fielderror';
          this.bucketSuccessMsg = 'Bucket already exist';
        }else if(response.data["status"]=='current'){
          this.bucketMsgClass='fielderror';
          this.bucketSuccessMsg = 'Current Bucket is exist';
        }else{
          this.bucketMsgClass='timelogSuccessMsg';
          this.bucketSuccessMsg = 'Bucket added successfully';
        }
        });
    }else{
      alert("In else"+JSON.stringify(this.form));
      this._service.updateBucket(this.projectId,this.bucketId,this.form,(response)=>{
        // alert(JSON.stringify(response)+"******updateBucket***********");
          this.bucketUpdated.emit(response.data);
        });
    }
}    

checkBucketName(event){
  var titlePattern = /^[a-zA-Z0-9\s\.\-_]+$/;
  clearTimeout(this.typeAheadTimer);
  if(event.trim()=='' || event.trim() == undefined || titlePattern.test(event) == false || event.trim() == this.prevBucketName){
    this.typeAheadResults.flag = false;
    this.typeAheadResults.msg = "";
      if(titlePattern.test(event) == false){
        this.spinnerSettings.color="red";
        this.spinnerSettings.class = "fa fa-times";
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
            // alert(JSON.stringify(result));
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

clearForm(resetEditForm){
  this.bucketForm.reset();
  // alert(JSON.stringify(this.form));
  this.submitted = false;
  this.bucketSuccessMsg="";
  this.bucketMsgClass="";

    this.form['startDateVal']=new Date();
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
            //  alert(JSON.stringify(this.form)+"+++++++Before-");
                this.form['Id']=resetEditForm.Id;
                this.form.title=resetEditForm.title;
                this.form['description']=resetEditForm.description;
                this.form['startDateVal']=resetEditForm.startDateVal;
                this.form['dueDateVal']=resetEditForm.dueDateVal;
                this.form['setCurrent']=resetEditForm.setCurrent;
                // this.form['sendReminder']=true;
                this.form['selectedUserFilter']=resetEditForm.selectedUserFilter;
            // alert(JSON.stringify(this.form)+"+++++++After");
            // this.form = resetEditForm;
            // alert(JSON.stringify(this.form)+"+++++++inside if clear form");
            jQuery('#bucketDescId').summernote('code',resetEditForm.description);
            this.prevBucketName = this.form.title;
          }
        jQuery('body').removeClass('modal-open');
      // alert(JSON.stringify(this.form)+"--------------After clear");

}

}
