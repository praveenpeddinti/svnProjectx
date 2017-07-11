import { Component,Directive,NgZone } from '@angular/core';
import { BucketService } from '../../services/bucket.service';
import {CalendarModule,AutoComplete,CheckboxModule} from 'primeng/primeng'; 
import { AjaxService } from '../../ajax/ajax.service';
import { Router, ActivatedRoute,NavigationExtras } from '@angular/router';
import { GlobalVariable } from '../../config';
import { Http, Headers } from '@angular/http';
import {SharedService} from '../../services/shared.service';
import {AuthGuard} from '../../services/auth-guard.service';
import { ProjectService } from '../../services/project.service';
import {AccordionModule,DropdownModule,SelectItem} from 'primeng/primeng';
import {SummerNoteEditorService} from '../../services/summernote-editor.service';
import { NgForm } from '@angular/forms';

declare var summernote:any;
declare var jQuery:any;

@Component({
    selector: 'bucket-view',
    providers: [BucketService,ProjectService],
    templateUrl: 'bucket-component.html',
    styleUrls: ['./bucket.component.css']

})

export class BucketComponent{
    public calendarVal = new Date();
    loading: boolean = false;
    
    private page=1;
    public ready=true;
    public form={
    Id:'',
    title:'',
    description:'',
    startDateVal:new Date(),
    dueDateVal:'',
    notifyEmail:['1'],
    sendReminder:['1'],
    selectedUserFilter:'',
    selectedBucketTypeFilter:''
    };
    errors: string='';
    adderrors: string='';
    
    public projectName; 
    public projectId;
    role: number = 1;
    public submitted=false;
    public FilterOption=[];
    public FilterOptionToDisplay=[];
    public BucketFilterOption=[];
    public BucketFilterOptionToDisplay=[];
    public bucketChangedFilterOption=[];
    public bucketChangedFilterToDisplay=[]; 
    
    public bucketDetails = [];
    public editBucketallDetails: any={};
    public Type:string;
    public selectedBucketChangeFilter =[];
    public bucketArray=[];
    public isCurrentBucketExist:number=0;
    public bStatus;
    headers = new Headers({ 'Content-Type': 'application/x-www-form-urlencoded' });
    constructor(
        private _router: Router,
        private _service: BucketService,private projectService:ProjectService, private _ajaxService: AjaxService,private http: Http, private route: ActivatedRoute,private shared:SharedService,private editor:SummerNoteEditorService,private zone: NgZone) { 
        console.log("in constructor"); 
    }
        
        
    /**
    * @author:Ryan Marshal
    * @description:This is used for initializing the summernote editor
    */
    ngAfterViewInit() { 
        var formobj=this;
        this.editor.initialize_editor('summernote','keyup',formobj);
    }

        
    ngOnInit() {
        var thisObj = this;
        thisObj.route.queryParams.subscribe(
        params => 
            { 
            thisObj.route.params.subscribe(params => {
                thisObj.projectName=params['projectName'];
                thisObj.projectService.getProjectDetails(thisObj.projectName,(data)=>{
                if(data.statusCode!=404) {
                    thisObj.projectId=data.data.PId;
                    thisObj._service.getResponsibleFilter(thisObj.projectId,this.role,(response) => { 
                        thisObj.FilterOptionToDisplay=this.prepareItemArray(response.data,false,'Member');
                        thisObj.FilterOption=this.FilterOptionToDisplay[0].filterValue;
                    }); 
                    thisObj._service.getBucketTypeFilter(this.projectId,'New',(response) => {
                        this.isCurrentBucketExist=response.data.length;
                        thisObj.BucketFilterOptionToDisplay=this.prepareItemArray(response.data,false,'bucket');
                        thisObj.BucketFilterOption=this.BucketFilterOptionToDisplay[0].filterValue;
                    }); 
                    this.callshowBuckets(1);
                    //this.shared.change(this._router.url,null,'Time Report','Other',thisObj.projectName);
                }
                });
            });
        })
        jQuery(document).ready(function(){
           jQuery(window).scroll(function() {
                if (thisObj.ready && jQuery(window).scrollTop() >= (jQuery(document).height() - jQuery(window).height())) {
                    thisObj.ready=false;
                    thisObj.page++;
                    thisObj.load_bucketContents(thisObj.page,thisObj.bStatus,'scroll'); 
                }
            });
        });
    }

   
    /*
    @params    :  buckettype
    @Description: All Buckets list Rendering
    */
    callshowBuckets(bucketStatus){
        this.bucketDetails=[];
        this.page=1;
        this.bStatus=bucketStatus;
        this.load_bucketContents(this.page,this.bStatus,'');
        
    }
    load_bucketContents(page,bucketStatus,scroll) {
        var postData={
        'projectId':this.projectId,
        'bucketStatus':bucketStatus,
        'page':page
        }
        this._ajaxService.AjaxSubscribe("bucket/get-all-bucket-details",postData,(response) => {
            if (response.statusCode == 200) {
            this.zone.run(() =>{ 
                this.bucketDetails= this.prepareBucketData(response.data,this.bucketDetails);
                var listData=[];
                if(postData.bucketStatus==1){
                   listData = [
                  {Id:"1",Name:"Set as Completed"},{Id:"2",Name:"Set as Backlog"},{Id:"3",Name:"Edit"},{Id:"4",Name:"Delete"}
                  ];
                }else if(postData.bucketStatus==2){
                  listData = [
                  {Id:"1",Name:"Re-opened milestone"},{Id:"2",Name:"Delete"}
                ];
                }else{
                    if(this.isCurrentBucketExist==1){
                    listData = [
                    {Id:"1",Name:"Edit"},{Id:"2",Name:"Delete"}
                ];
                }else{
                   listData = [
                  {Id:"1",Name:"Set as Current"},{Id:"2",Name:"Edit"},{Id:"3",Name:"Delete"}
                ];
                }
                }
                //var listData = [
                //  {Id:"1",Name:"Set as Completed"},{Id:"2",Name:"Set as Backlog"},{Id:"3",Name:"Edit"},{Id:"4",Name:"Delete"}
                //];
                this.bucketChangedFilterToDisplay=this.prepareItemArray(listData,false,'changebucket');
                this.bucketChangedFilterOption=this.bucketChangedFilterToDisplay[0].filterValue;
                this.ready=true;
                 })
                if(response.data==[]){
                    jQuery('#searchsection').html("No results found");
                   }
            } else {
                console.log("fail---");
            }
        });
    }

    prepareBucketData(bucketData,prepareData){
         for(let bucketArray in bucketData){
        prepareData.push(bucketData[bucketArray]);
       }
       return prepareData;
    }

    clearDateTimeEntry(){
    jQuery('#summernote').summernote('code','');
    this.Type= 'New';
    this.form['Id']='';
    this.form['title']='';
    this.form['description']='';
    this.form['startDateVal']=new Date();
    this.form['dueDateVal']='';
    this.form['notifyEmail']=['1'];
    this.form['sendReminder']=['1'];
    this.form['selectedUserFilter']='';
    this.form['selectedBucketTypeFilter']='';
    jQuery('#bucketSuccessMsg').addClass('timelogSuccessMsg');
    jQuery('#bucketSuccessMsg').hide();
    }
    
    
    /*
    @params    :  list,priority,status
    @ParamType :  any,boolean,string
    @Description: Building Dynamic Dropdown List Values
    */
    public prepareItemArray(list:any,priority:boolean,status){
        var listItem=[];
        var listMainArray=[];
        if(list.length>0) { 
            if(status == "Member") {
                listItem.push({label:"Select Responsible ", value:"",priority:priority,type:status});
            }else if(status == "bucket"){
                listItem.push({label:"Select Bucket Type", value:"",priority:priority,type:status});
            }else{
                listItem.push({label:"Select Bucket Status", value:"",priority:priority,type:status});
           }
           for(var i=0;list.length>i;i++){
              listItem.push({label:list[i].Name, value:list[i].Id,priority:priority,type:status});
           }
        }
        listMainArray.push({type:"",filterValue:listItem});
        return listMainArray;
    }
 
/*filterCreateBucketUsers(){
var finalDate= this.form['selectedUserFilter'];
}
filterBucketType(){
var finalDate= this.form['selectedBucketTypeFilter'];
}*/

/*
@params    :  type,priority,status
@ParamType :  any,boolean,string
@Description: Add & Edit Bucket funcationality
*/
BucketForAddorEdit(event){
if(event=='New')
  this.addBucket();
else
  this.editBucket();
}

addBucket(){
    var getBucketTitleVal=this.form['title']; 
    var titlePattern = /^[a-zA-Z0-9\s]+$/;
    //var isEmailValid = titlePattern.test(getBucketTitleVal);
    if(titlePattern.test(getBucketTitleVal) == false){
        this.errorBucketLog('addTitleErrMsg','Title can contain alphabet and digits');
    }    
    var editor=jQuery('#summernote').summernote('code');
    editor=jQuery(editor).text().trim();
    this.form['description']=jQuery('#summernote').summernote('code');
    if(this.form['dueDateVal']!=undefined){
        if( (new Date(this.form['startDateVal']) > new Date(this.form['dueDateVal']))){
            this.errorBucketLog('addDueDateErrMsg','Start Date is must be greater than Due Date');
            return false;
        }
    }
    if(this.form['selectedUserFilter']==''){
       this.errorBucketLog('responsibleErrMsg','Please select Responsible');
    }
    this._service.saveBucket(this.form,(response)=>{
    if(response.data=='failure'){
      jQuery('#bucketSuccessMsg').show();
      jQuery('#bucketSuccessMsg').removeClass('timelogSuccessMsg');
      jQuery('#bucketSuccessMsg').addClass('alert alert-danger');
      jQuery("#bucketSuccessMsg").html('Bucket already created');
      jQuery('#bucketSuccessMsg').fadeOut( "slow" );
      this.callshowBuckets(1);
    }else if(response.data=='current'){
      jQuery('#bucketSuccessMsg').show();
      jQuery('#bucketSuccessMsg').removeClass('timelogSuccessMsg');
      jQuery('#bucketSuccessMsg').addClass('alert alert-danger');
      jQuery("#bucketSuccessMsg").html('Current Bucket is exist');
      jQuery('#bucketSuccessMsg').fadeOut( "slow" );
      this.callshowBuckets(1);
    }else{
        jQuery('#bucketSuccessMsg').show();
        jQuery('#bucketSuccessMsg').addClass('timelogSuccessMsg');
        //jQuery('#bucketSuccessMsg').addClass('alert alert-danger');
        jQuery("#bucketSuccessMsg").html('Bucket added successfully');
      //jQuery('#bucketSuccessMsg').fadeOut( "slow" );
    }
    });
}    

public errorBucketLog(id,msg){
    jQuery("#"+id).html(msg);
    jQuery("#"+id).show();
    jQuery("#"+id).fadeOut(4000);

}    
  
  
/* Edit Bucket Start */
editBucketPopup(){
    this.Type= 'Edit';
    this.form['Id'] = this.editBucketallDetails.BucketId;
    this.form['title']=this.editBucketallDetails.BucketName;
    this.form['summernote']= jQuery("#summernote").summernote('code',this.editBucketallDetails.Description);
    this.form['startDateVal'] = this.editBucketallDetails.StartDate;
    this.form['dueDateVal'] = this.editBucketallDetails.DueDate;
    this.form['selectedUserFilter']=this.editBucketallDetails.ResponsibleUser;
    this.form['selectedBucketTypeFilter']=this.editBucketallDetails.BucketType;
    //(this.editBucketallDetails.EmailNotify==0)?this.form['notifyEmail']=['0']:this.form['notifyEmail']=['1'];
    //(this.editBucketallDetails.EmailReminder==0)?this.form['sendReminder']=['0']:this.form['sendReminder']=['1'];
    }    
 
editBucket(){
   if(this.form['notifyEmail'].length>1){this.form['notifyEmail']=['1'];}else{this.form['notifyEmail']=['0'];}
   if(this.form['sendReminder'].length>1){this.form['sendReminder']=['1'];}else{this.form['sendReminder']=['0']};
    var getBucketTitleVal=this.form['title']; 
    var titlePattern = /^[a-zA-Z0-9\s]+$/;
    if(titlePattern.test(getBucketTitleVal) == false){
        this.errorBucketLog('addTitleErrMsg','Title can contain alphabet and digits');
    }    
    var editor=jQuery('#summernote').summernote('code');
    editor=jQuery(editor).text().trim();
    this.form['description']=jQuery('#summernote').summernote('code');
    if(this.form['dueDateVal']!=undefined){
        if( (new Date(this.form['startDateVal']) > new Date(this.form['dueDateVal']))){
            this.errorBucketLog('addDueDateErrMsg','Start Date is must be greater than Due Date');
        }
    }
    if(this.form['selectedUserFilter']==''){
       this.errorBucketLog('responsibleErrMsg','Please select Responsible');
    }
    this._service.updateBucket(this.form,(response)=>{
    if(response.data=='failure'){
      jQuery('#bucketSuccessMsg').show();
      jQuery('#bucketSuccessMsg').removeClass('timelogSuccessMsg');
      jQuery('#bucketSuccessMsg').addClass('alert alert-danger');
      jQuery("#bucketSuccessMsg").html('Bucket already created');
    }else{
        jQuery('#bucketSuccessMsg').show();
        jQuery('#bucketSuccessMsg').addClass('timelogSuccessMsg');
        jQuery("#bucketSuccessMsg").html('Bucket updated successfully');
      //jQuery('#bucketSuccessMsg').fadeOut( "slow" );
      this.callshowBuckets(1);
    }
    });
    }
      
filterBucketChange(bucketId,event){

    this.editBucketallDetails = bucketId;
    if(event.text=='Edit'){
        //this.getBucketTypeFilter(this.projectId,"Edit");
        jQuery("#editBucketButton").click();
    }else{
      
       var postData={
       'projectId':this.projectId,
       'bucketId':bucketId.BucketId,
       'changeStatus':event.text
       }
       this._ajaxService.AjaxSubscribe("bucket/get-bucket-change-status",postData,(response) => {
        if(response.data=='success'){
               jQuery('#bucketStatusErrorMsg').show();  
      jQuery("#bucketStatusErrorMsg").html('Bucket Status is updated');
      jQuery('#bucketStatusErrorMsg').fadeOut( "slow" );
      //jQuery('#bucketSuccessMsg').fadeOut( "slow" );
      this.callshowBuckets(bucketId.BucketRole);
      
    }else{
      jQuery('#bucketStatusErrorMsg').show();
      jQuery('#bucketSuccessMsg').removeClass('alert alert-danger');
      jQuery('#bucketSuccessMsg').addClass('timelogSuccessMsg');
      jQuery("#bucketSuccessMsg").html('Current Bucket is exist');
    }       
       });
   }
    
    
} 

   toggle_visibility(id,event) {
       var e = document.getElementById(id);
       
       if(e.style.display == 'block'){
       var el = jQuery("#example-one");
  el.text() == el.data("text-swap") 
    ? el.text(el.data("text-original")) 
    : el.text(el.data("text-swap"));
          e.style.display = 'none';
          jQuery("#bucket_description_short").css("display", "block");
       }else{
        var el = jQuery("#example-one");
        el.text() == el.data("text-swap") 
        ? el.text(el.data("text-original")) 
        : el.text(el.data("text-swap"));
        e.style.display = 'block';
        jQuery("#bucket_description_short").css("display", "none");
      }
      }
      
     
   
}