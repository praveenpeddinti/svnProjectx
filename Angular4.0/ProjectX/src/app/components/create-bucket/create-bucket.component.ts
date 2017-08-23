import { Component,ViewChild, OnInit } from '@angular/core';
import {SummerNoteEditorService} from '../../services/summernote-editor.service';
import { AjaxService } from '../../ajax/ajax.service';
import { Router, ActivatedRoute,NavigationExtras } from '@angular/router';
import { ProjectService } from '../../services/project.service';
import { BucketService } from '../../services/bucket.service';

declare var summernote:any;
declare var jQuery:any;
@Component({
  selector: 'app-create-bucket',
  inputs:["Type","bucketDetails","bucketId","projectId","projectName"],
  templateUrl: './create-bucket.component.html',
  providers: [BucketService,ProjectService],
  styleUrls: ['./create-bucket.component.css']
})
export class CreateBucketComponent implements OnInit {

  private Type:String;
  private projectName:String;
  private bucketId:String;
  private projectId:String;
  private bucketDetails:any={};
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
  private minDate = new Date();
@ViewChild('addBucketForm') bucketForm:HTMLFormElement;

  constructor(private editor:SummerNoteEditorService,private _router: Router,
        private _service: BucketService,private projectService:ProjectService, private _ajaxService: AjaxService) { }

  ngAfterViewInit() { 
    // setTimeout(()=>{
            // alert(this.Type+"-----ngAfterViewInit------"+this.projectId+"---->"+this.projectName)

         this._service.getResponsibleFilter(this.projectId,this.role,(response) => { 
                        this.FilterOptionToDisplay=this.prepareItemArray(response.data,false,'Member');
                        this.FilterOption=this.FilterOptionToDisplay[0].filterValue;
                    }); 
                    // this._service.getBucketTypeFilter(this.projectId,'New',(response) => {
                    //     // this.isCurrentBucketExist=response.data.length;
                    //     this.BucketFilterOptionToDisplay=this.prepareItemArray(response.data,false,'bucket');
                    //     this.BucketFilterOption=this.BucketFilterOptionToDisplay[0].filterValue;
                    // });
                    // },250);
                    var formobj=this;
        this.editor.initialize_editor('bucketDescId','keyup',formobj);
    }

  ngOnInit() {
                // alert(this.Type+"-----ngOnInit------"+this.projectId)

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
    }

   
  }

private prepareItemArray(list:any,priority:boolean,status){
        var listItem=[];
        var listMainArray=[];
        if(list.length>0) { 
            if(status == "Member") {
                listItem.push({label:"Select Responsible ", value:"",priority:priority,type:status});
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
if(event=='New')
  this.addBucket();
// else
//   this.editBucket();
}

addBucket(){
    // var getBucketTitleVal=this.form['title']; 
    // var titlePattern = /^[a-zA-Z0-9\s]+$/;
    // //var isEmailValid = titlePattern.test(getBucketTitleVal);
    // if(titlePattern.test(getBucketTitleVal) == false){
    //     // this.errorBucketLog('addTitleErrMsg','Title can contain alphabet and digits');
    // }    
    var editor=jQuery('#bucketDescId').summernote('code');
    editor=jQuery(editor).text().trim();
    this.form['description']=jQuery('#bucketDescId').summernote('code').trim();
    // if(this.form['dueDateVal']!=undefined){
    //     if( (new Date(this.form['startDateVal']) > new Date(this.form['dueDateVal']))){
    //         // this.errorBucketLog('addDueDateErrMsg','Start Date is must be greater than Due Date');
    //         return false;
    //     }
    // }
    // if(this.form['selectedUserFilter']==''){
    //   //  this.errorBucketLog('responsibleErrMsg','Please select Responsible');
    // }
    this._service.saveBucket(this.projectId,this.form,(response)=>{
    this._router.navigate(['project',this.projectName,'bucket']);
    if(response.data=='failure'){
      this.bucketMsgClass='fielderror';
      this.bucketSuccessMsg = 'Bucket already exist';
      // jQuery('#bucketSuccessMsg').show();
      // jQuery('#bucketSuccessMsg').removeClass('timelogSuccessMsg');
      // jQuery('#bucketSuccessMsg').addClass('fielderror');
      // jQuery("#bucketSuccessMsg").html('Bucket already created');
      // jQuery('#bucketSuccessMsg').fadeOut( "slow" );
      // this.callshowBuckets('Current');
    }else if(response.data=='current'){
      this.bucketMsgClass='fielderror';
      this.bucketSuccessMsg = 'Current Bucket is exist';
      // jQuery('#bucketSuccessMsg').show();
      // jQuery('#bucketSuccessMsg').removeClass('timelogSuccessMsg');
      // jQuery('#bucketSuccessMsg').addClass('fielderror');
      // jQuery("#bucketSuccessMsg").html('Current Bucket is exist');
      // jQuery('#bucketSuccessMsg').fadeOut( "slow" );
      // this.callshowBuckets('Current');
    }else{
      this.bucketMsgClass='timelogSuccessMsg';
      this.bucketSuccessMsg = 'Bucket added successfully';
        // jQuery('#bucketSuccessMsg').show();
        // jQuery('#bucketSuccessMsg').addClass('timelogSuccessMsg');
        //jQuery('#bucketSuccessMsg').addClass('fielderror');
        // jQuery("#bucketSuccessMsg").html('Bucket added successfully');
      //jQuery('#bucketSuccessMsg').fadeOut( "slow" );
    }
    });
}    
private typeAheadTimer=undefined;
private typeAheadResults ={
  flag:false,
  msg:""
} ;
 public spinnerSettings={
      color:"",
      class:""
    };
checkBucketName(event){
  var titlePattern = /^[a-zA-Z0-9\s\.\-_]+$/;
  clearTimeout(this.typeAheadTimer);
  if(event.trim()=='' || event.trim() == undefined || titlePattern.test(event) == false){
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

clearForm(){
  this.bucketForm.reset();
  // alert(JSON.stringify(this.form));
  this.submitted = false;
  this.bucketSuccessMsg="";
  this.bucketMsgClass="";
  //   this.form['Id']='';
  //   this.form['title']='';
  //   this.form['description']='';
    this.form['startDateVal']=new Date();
  //   this.form['dueDateVal']='';
  //   this.form['setCurrent']=false;
  //   // this.form['sendReminder']=true;
  //   this.form['selectedUserFilter']='';
  //   // this.form['selectedBucketTypeFilter']='';
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
        jQuery('body').removeClass('modal-open');
      // alert(JSON.stringify(this.form)+"--------------After clear");

}

  // ngOnChanges(changes) {
  //   alert("===ngOnChanges==>"+JSON.stringify(changes));
  //   if(changes.Type.currentValue == "New"){
  //   this.form['Id']='';
  //   this.form['title']='';
  //   this.form['description']='';
  //   this.form['startDateVal']=new Date();
  //   this.form['dueDateVal']='';
  //   this.form['notifyEmail']=true;
  //   this.form['sendReminder']=true;
  //   this.form['selectedUserFilter']='';
  //   this.form['selectedBucketTypeFilter']='';
  //   }else{
  //     alert(JSON.stringify(changes.bucketDetails.currentValue)+"*******************");
  //     this.Type= 'Edit';
  //   this.form['Id'] = changes.bucketDetails.currentValue.BucketId;
  //   this.form['title']=changes.bucketDetails.currentValue.BucketName;
  //   this.form['description']=(changes.bucketDetails.currentValue.Description != null && changes.bucketDetails.currentValue.Description != '' && changes.bucketDetails.currentValue.Description != undefined ) ? changes.bucketDetails.currentValue.Description : "";
  //   jQuery("#summernote").summernote('code',this.form['description']);
  //   this.form['startDateVal'] = changes.bucketDetails.currentValue.StartDate;
  //   this.form['dueDateVal'] = changes.bucketDetails.currentValue.DueDate;
  //   this.form['selectedUserFilter']=changes.bucketDetails.currentValue.ResponsibleUser;
  //   this.form['selectedBucketTypeFilter']=changes.bucketDetails.currentValue.BucketType;
  //   // this.BucketRole=this.bucketDetails.BucketRole;
  //    (changes.bucketDetails.currentValue.EmailNotify==0)?this.form['notifyEmail']=false:this.form['notifyEmail']=true;
  //    (changes.bucketDetails.currentValue.EmailReminder==0)?this.form['sendReminder']=false:this.form['sendReminder']=true;
  //    alert("--------------"+JSON.stringify(this.form));
  //   }
  //     // this.childFunction()
  //   }


  // else{
  //     alert(JSON.stringify(this.bucketDetails)+"********else***********");
  //     this.Type= 'Edit';
  //   this.form['Id'] = this.bucketDetails.BucketId;
  //   this.form['title']=this.bucketDetails.BucketName;
  //   this.form['description']=(this.bucketDetails.Description != null && this.bucketDetails.Description != '' && this.bucketDetails.Description != undefined ) ? this.bucketDetails.Description : "";
  //   jQuery("#summernote").summernote('code',this.form['description']);
  //   this.form['startDateVal'] = this.bucketDetails.StartDate;
  //   this.form['dueDateVal'] = this.bucketDetails.DueDate;
  //   this.form['selectedUserFilter']=this.bucketDetails.ResponsibleUser;
  //   this.form['selectedBucketTypeFilter']=this.bucketDetails.BucketType;
  //   // this.BucketRole=this.bucketDetails.BucketRole;
  //    (this.bucketDetails.EmailNotify==0)?this.form['notifyEmail']=false:this.form['notifyEmail']=true;
  //    (this.bucketDetails.EmailReminder==0)?this.form['sendReminder']=false:this.form['sendReminder']=true;
  //    alert("------Form--------"+JSON.stringify(this.form));
  //   }

}
