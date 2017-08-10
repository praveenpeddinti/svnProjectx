import { Component, Directive,ViewChild,ViewEncapsulation } from '@angular/core';
import { StoryService } from '../../services/story.service';
import { Router,ActivatedRoute,NavigationExtras } from '@angular/router';
import { Http, Headers } from '@angular/http';
import { SharedService } from '../../services/shared.service';
import { ProjectService } from '../../services/project.service';
import { ChildtaskComponent } from '../childtask/childtask.component';
import {Location} from '@angular/common';
import { AjaxService } from '../../ajax/ajax.service';
declare var jQuery:any;

@Component({
    selector: 'story-dashboard-view',
    providers: [StoryService,ProjectService],
    templateUrl: 'story-dashboard-component.html',
    styleUrls: ['./story-dashboard.component.css']

})

export class StoryDashboardComponent {
    public FilterOption=[];
    public FilterOptionToDisplay=[];
     public selectedFilter=null;  
     public projectName; 
     private projectId;
     public statusId=''; 
     private dropList=[];
     private ticketData;
     private showMyEditableField =[];
     private dropDisplayList=[]
     private ticketId;
     public inlineTimeout;
     public editing = {};
     private dateVal = new Date();
    //private projectId;              
     //public projectId;  
    @ViewChild('myTable') table: any;
    rows = [];
    row1 = [];
    count: number = 0;
    offset: number = 0;
    limit: number = 10;
    sortvalue: string = "Id";
    sortorder: string = "desc";
    loading: boolean = false;
    columns = [
                {
                    name: 'Id',
                    flexGrow: 1,
                    sortby: 'Id',
                    class: 'taskRstory'
                },
                {
                    name: 'Title',
                    flexGrow: 3,
                    sortby: 'Title',
                    class: 'titlecolumn'
                },
                {
                    name: 'Assigned to',
                    flexGrow: 1.5,
                    sortby: 'assignedto',
                    class: ''
                },
                {
                    name: 'Priority',
                    flexGrow: 1,
                    sortby: 'priority',
                    class: 'prioritycolumn'
                },
                {
                    name: 'State',
                    flexGrow: 1,
                    sortby: 'status',
                    class: 'statusbold'
                },
                {
                    name: 'Bucket',
                    flexGrow: 1,
                    sortby: 'bucket',
                    class: 'bucket'
                },
                {
                    name: 'Due Date',
                    flexGrow: 1,
                    sortby: 'duedate',
                    class: 'duedate'
                },
                {
                    name: '',
                    flexGrow: 0.3,
                    sortby: '',
                    class: 'arrowClass'
                }
                
              ];
              

expanded: any = {};
    headers = new Headers({ 'Content-Type': 'application/x-www-form-urlencoded' });
    pageNo:number=0; //added by Ryan
    filterValue=null;//added by Ryan

    constructor(
        private _router: Router,
        private _service: StoryService,private projectService:ProjectService, private http: Http, private route: ActivatedRoute,private shared:SharedService,private _ajaxService: AjaxService,private location:Location) { console.log("in constructor"); }

    ngOnInit() {
 var thisObj = this;
  thisObj.route.queryParams.subscribe(
      params => 
      { 
          /* Section To Remember the State of Pages while user navigation */
          if(params['page']!=undefined) //added by Ryan
          {
            this.pageNo=params['page'];//added by Ryan
            this.sortorder=params['sort'];//added by Ryan
            this.sortvalue=params['col'];
            if(params['filter']!=undefined){this.filterValue=params['filter'];}
            thisObj.offset=this.pageNo-1;//added by Ryan
            this.rememberState(this.pageNo,this.sortorder,this.sortvalue,this.filterValue);
    
          }
           /* =========Section End==========================================*/ 

      thisObj.route.params.subscribe(params => {
           thisObj.projectName=params['projectName'];
           this.shared.change(this._router.url,thisObj.projectName,'Dashboard','',thisObj.projectName); //added by Ryan for breadcrumb purpose
            thisObj.projectService.getProjectDetails(thisObj.projectName,(data)=>{
                if(data.statusCode ==200) {
                thisObj.projectId=data.data.PId;  
                /*
                @params    :  projectId
                @Description: get bucket details
                */  
                localStorage.setItem('ProjectName',thisObj.projectName);
                localStorage.setItem('ProjectId',thisObj.projectId);
                thisObj._service.getFilterOptions(thisObj.projectId,(response) => { 
                thisObj.FilterOption=response.data[0].filterValue;
                thisObj.FilterOptionToDisplay=response.data;
            });
            if(localStorage.getItem('filterArray')!=null){
            var filterArray=JSON.parse(localStorage.getItem('filterArray'));
            thisObj.selectedFilter=filterArray;
            }
            
             
   /*
        @params    :  offset,limit,sortvalue,sortorder
        @Description: Default routing
        */
        thisObj.page(thisObj.projectId,thisObj.offset, thisObj.limit, thisObj.sortvalue, thisObj.sortorder,thisObj.selectedFilter);
        var ScrollHeightDataTable=jQuery(".ngx-datatable").width() - 12;
        jQuery("#filterDropdown").css("paddingRight",10);
       jQuery(".ngx-datatable").css("width",ScrollHeightDataTable);
      // var thisObj = this;
       
            jQuery( window ).resize(function() { 
            if( thisObj.checkScrollBar() == true){
            jQuery("#filterDropdown").css("paddingRight",0);
            }else{
            jQuery("#filterDropdown").css("paddingRight",12);
            }
            });
       }else{
       this._router.navigate(['project',this.projectName,'error']); 
       }
                
        });
        });
       
           })
 
}

    // ngAfterViewInit()
    // {
    //  jQuery('#filter_dropdown_label #filter_dropdown').find(' > li.general:eq(0)').before('<label>Filter</label>');
    //  jQuery('#filter_dropdown_label #filter_dropdown').find(' > li.bucket:eq(0)').before('<label>Bucket</label>');
    // }
        /*
        @params    :  offset,limit,sortvalue,sortorder
        @Description: StoryComponent/Task list Rendering
        */
        
    page(projectId,offset, limit, sortvalue, sortorder,selectedOption ) { 
        if(selectedOption!=null) //added by Ryan
        {
            this.filterValue=selectedOption.id;
            localStorage.setItem('filterArray',JSON.stringify(selectedOption));
        }
       
         this.rows =[];
        this._service.getAllStoryDetails(projectId, offset, limit, sortvalue, sortorder,selectedOption,(response) => {
           
            let jsonForm = {};
            if (response.statusCode == 200) {

                this.rows = response.data;        
                
               // this.getRowClass(this.rows);
                //console.log("ROWS___"+JSON.stringify(this.rows));

                this.rows = response.data;

                this.count = response.totalCount;
                for(var i in this.editing){
    //    for(var j in this.editing){   
          this.editing[i] = false;
    //   }
      }
                
                /* Section To Remember the State of Pages and to replace the Url with required params while user navigation */
                this.pageNo=offset+1;//added by Ryan
                this.sortorder=sortorder;//added by Ryan
                this.sortvalue=sortvalue;//added by Ryan
                this.offset=this.pageNo-1;//added by Ryan
                this.rememberState(this.pageNo,this.sortorder,this.sortvalue,this.filterValue);
                var url='/project/'+this.projectName+'/list?page='+(offset+1)+'&sort='+sortorder+'&col='+sortvalue+'&filter='+this.filterValue;
                this.location.replaceState(url);
                /* =========Section End==========================================*/ 

            } else {
                console.log("fail---");
            }
        });
    }
    /*
    @When Clicking pages
    */
    onPage(event) {
        this.offset = event.offset;
        this.limit = event.limit;
        this.page(this.projectId,this.offset, this.limit, this.sortvalue, this.sortorder,this.selectedFilter);
    }

 
    /*
    @When Clicking Columns for Sorting
    */
    onSort(event) {
        this.sortvalue = event.sorts[0].prop;
        this.sortorder = event.sorts[0].dir;
        this.page(this.projectId,this.offset, this.limit, this.sortvalue, this.sortorder,this.selectedFilter);
    }
collapseAll(){ 
    this.table.rowDetail.collapseAllRows()
}
toggleExpandRow(row) { 
  this.table.rowDetail.toggleExpandRow(row);
    
}

      /* @Praveen P
        * This method is used subtask details when subtask id click component
        */
    onActivate(event) {
        if (event.hasOwnProperty("row")) {
			this._router.navigate(['project',event.row[0].other_data.project_name, event.row[0].field_value,'details']);        
}
    }
     /* @Praveen P
        * This method is used story/task details when story/task id click component
        */
    showStoryDetail(event) {
            this._router.navigate(['project',event[0].other_data.project_name, event[0].field_value,'details']);
    }

    

    renderStoryForm() {
        this._router.navigate(['project',this.projectName,'new']);
    }

    filterDashboard(){
        this.offset=0;
        if(this.selectedFilter.id==8){
           this.sortvalue='bucket' ;
           this.sortorder='asc'; 
        }
      this.page(this.projectId,this.offset, this.limit, this.sortvalue, this.sortorder,this.selectedFilter);  
    }
     checkScrollBar() {
    var hContent = jQuery("body").height(); // get the height of your content
    var hWindow = jQuery(window).height();  // get the height of the visitor's browser window

    // if the height of your content is bigger than the height of the 
    // browser window, we have a scroll bar
    if(hContent>hWindow) { 
        return true;    
    }

    return false;
}

getRowClass(row) {
   // console.log("etRowVCla=**"+JSON.stringify(row));
 //  console.log("etRowVCla==="+JSON.stringify(row[0].other_data.planlevel));
  // console.log("etRowVCla==fildvalue="+row[0].field_value);
var className = "childTask childTask_"+row[0].other_data.parentStoryId;
if(row[0].other_data.planlevel==2){
   return className;
}else{
    return "";
}
 
  }


  /**
    * @author:Ryan Marshal
    * @description:This is used for remembering the state where the user left off
    */
  rememberState(page,sortorder,sortcol,selectedfilter)
  {
    var pagination_state={page:'Dashboard',count:this.pageNo,sort:this.sortorder,col:this.sortvalue,filter:this.filterValue};
    var page_state=JSON.stringify(pagination_state);
    localStorage.setItem('pageState',page_state);
  }


editThisField(event,fieldId,fieldDataId,fieldTitle,renderType,restoreFieldId,row){ 
    //alert("fieldId---"+fieldId+"---fieldDataId---"+fieldDataId+"---fieldTitle---"+fieldTitle+"---renderType--"+renderType); 
     this.dropList=[];
    var inptFldId = fieldId;
      for(var i in this.editing){
          this.editing[i] = false;
      }
      this.editing[restoreFieldId]=true;
     

    if(renderType == "select"){
        var reqData = {
          fieldId:fieldDataId,
          projectId:this.projectId,
          ticketId:row[0].field_value,
          workflowType:1,
          statusId:1,
        };
       // alert(JSON.stringify(reqData));
        //Fetches the field list data for current dropdown in edit mode.
        this._ajaxService.AjaxSubscribe("story/get-field-details-by-field-id",reqData,(data)=>
            { 
                var listData = {
                  list:data.data
                };
               // alert(JSON.stringify(data.data));
                var priority=(fieldTitle=="Priority"?true:false);
                this.dropDisplayList=this.prepareItemArray(listData.list,priority,fieldTitle);
                this.dropList=this.dropDisplayList[0].filterValue;
                //sets the dropdown prefocused
                jQuery("#"+inptFldId+" div").click();
                
            });
    }else if(renderType == "date"){
      //sets the datepicker prefocused
      this.dateVal = row[6].field_value;
      setTimeout(()=>{
        jQuery("#"+inptFldId+" span input").focus();
      },150);    
    }

 
    
  }

  //Prepares the Custom Dropdown's Options array.
 public prepareItemArray(list:any,priority:boolean,status:string){
   var listItem=[];
    var listMainArray=[];
     if(list.length>0){
       if(status == "Assigned to" || status == "Stake Holder"){
       listItem.push({label:"--Select a Member--", value:"",priority:priority,type:status});
       }
         for(var i=0;list.length>i;i++){
           listItem.push({label:list[i].Name, value:list[i].Id,priority:priority,type:status});
       }
     }
      listMainArray.push({type:"",filterValue:listItem});
    return listMainArray;
}
 
//Restores the editable field to static mode.
//Also prepares the data to be sent to service to save the changes.
//This is common to left Column fields.
   restoreField(editedObj,restoreFieldId,fieldIndex,renderType,fieldId,showField,row,isChildActivity=0){
            //alert("---restoreFieldId---"+restoreFieldId+"---fieldIndex--"+fieldIndex+"--renderType--"+renderType+"---fieldId--"+fieldId);
    var intRegex = /^\d+$/;
    var floatRegex = /^((\d+(\.\d *)?)|((\d*\.)?\d+))$/;
      var postEditedText={
                        projectId:this.projectId,
                        isLeftColumn:1,
                        id:fieldId,
                        value:"",
                        ticketId:row[0].field_value,
                        editedId:restoreFieldId.split("_")[0]
                      };                  
          switch(renderType){
            case "input":
            case "textarea":
            editedObj=editedObj.trim();
            if(restoreFieldId == this.ticketId+"_dod".trim())
            jQuery("textarea#"+restoreFieldId+"_"+fieldIndex).val(editedObj);
            document.getElementById(restoreFieldId).innerText = (editedObj == "") ? "--":editedObj;
            postEditedText.value = editedObj;
            break;
            
            case "select":
            var appendHtml = (restoreFieldId.split("_")[0] == "priority")?"&nbsp; <i class='fa fa-circle "+editedObj.text+"' aria-hidden='true'></i>":"";
            document.getElementById(restoreFieldId).innerHTML = (editedObj.text == ""||editedObj.text == "--Select a Member--") ? "--":editedObj.text+appendHtml;            
            postEditedText.value = editedObj.value;
            break;

            case "date":
            var date = (this.dateVal.getMonth() + 1) + '-' + this.dateVal.getDate() + '-' +  this.dateVal.getFullYear();
            date = date.replace(/(\b\d{1}\b)/g, "0$1");
            document.getElementById(restoreFieldId).innerHTML = (date == "") ? "--":date;
            postEditedText.value = this.dateVal.toString();
            var rightNow = new Date();
            break;

          }
       
       this.postDataToAjax(postEditedText,isChildActivity,showField);
       this.editing[showField] = false;
  }
    inputKeyDown(event,eleId){
        if (event.shiftKey == true) {
            event.preventDefault();
        }

        if ((event.keyCode >= 48 && event.keyCode <= 57) || 
            (event.keyCode >= 96 && event.keyCode <= 105) || 
            event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 37 ||
            event.keyCode == 39 || event.keyCode == 46 || event.keyCode == 190) {

        } else {
            event.preventDefault();
        }

        if(jQuery("#"+eleId).val().indexOf('.') !== -1 && event.keyCode == 190)
            event.preventDefault(); 
  }
  closeCalendar(fieldIndex){

    this.showMyEditableField[fieldIndex] = true;
  }
   
closeTitleEdit(editedText,restoreFieldId,fieldIndex,renderType,fieldId,showField,row,isChildActivity=0){
    //alert(alert("editedObj----"+editedText+"---restoreFieldId---"+restoreFieldId+"---fieldIndex--"+fieldIndex+"--renderType--"+renderType+"---fieldId--"+fieldId);
        if(editedText.trim() !=""){
          // alert("if");
          // this.titleError="";
          document.getElementById(restoreFieldId).innerText= editedText;
          //alert("editedObj----"+editedText);
      // Added by Padmaja for Inline Edit
          var postEditedText={
            isLeftColumn:0,
            id:'Title',
            value:editedText,
            ticketId:row[0].field_value,
            projectId:this.projectId,
            editedId:'title'
          };
          this.postDataToAjax(postEditedText,isChildActivity,showField);
          this.editing[showField] = false;
      }else{
        
        jQuery("#"+restoreFieldId).val(document.getElementById(restoreFieldId).innerText) ;

      }
}
  // Added by Padmaja for Inline Edit
//Common Ajax method to save the changes.
    public postDataToAjax(postEditedText,isChildActivity=0,showField){
     clearTimeout(this.inlineTimeout);
    this.inlineTimeout =  setTimeout(() => { 
       this._ajaxService.AjaxSubscribe("story/update-story-field-inline",postEditedText,(result)=>
        {
          if(result.statusCode== 200){ 
          if(postEditedText.editedId == "title" || postEditedText.editedId == "desc"){
                     if(postEditedText.editedId == "title"){
                        document.getElementById(this.ticketId+'_'+postEditedText.editedId).innerText=result.data.updatedFieldData;
                      }else if(postEditedText.editedId == "desc"){
                      document.getElementById(this.ticketId+'_'+postEditedText.editedId).innerHTML=result.data.updatedFieldData;
                       var ticketIdObj={'ticketId': this.ticketId,'projectId':this.projectId};
                      //this.getArtifacts(ticketIdObj);                     
                   }
               
          }
    
             else if(postEditedText.editedId == "estimatedpoints"){ 
                 jQuery("#"+postEditedText.ticketId+"_totalestimatepoints").html(result.data.updatedFieldData.value);
               }
          
             else if(result.data.updatedState!=''){ 
                 document.getElementById(showField+"_workflow").innerText=result.data.updatedState.state;
                //this.statusId = result.data.updatedFieldData;
           }
            else if(result.data.activityData.data.ActionFieldName =='assignedto'){ 
                if(result.data.activityData.data.NewValue.ProfilePicture !=''){
                    var imgObj = <HTMLImageElement>document.getElementById(showField+"_assignedto")
                 imgObj.src=result.data.activityData.data.NewValue.ProfilePicture;
                //this.statusId = result.data.updatedFieldData;
           }
            }
        /**
        * @author:Praveen P
        * @description : This is used to show the selected user (Stake Holder, Assigned to and Reproted by) in Follower list 
        */




               

 }
        });

      
        },500)
    }

    }

