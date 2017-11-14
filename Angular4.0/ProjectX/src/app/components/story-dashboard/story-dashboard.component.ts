import { Component, Directive,ViewChild,ViewEncapsulation } from '@angular/core';
import { StoryService } from '../../services/story.service';
import { Router,ActivatedRoute,NavigationExtras } from '@angular/router';
import { Http, Headers } from '@angular/http';
import { SharedService } from '../../services/shared.service';
import { ProjectService } from '../../services/project.service';
import { ChildtaskComponent } from '../childtask/childtask.component';
import { AdvanceFilterComponent } from '../utility/advance-filter/advance-filter.component';
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
     public minDate:Date;
     public filterType:any;
    public advFilterData:any;
    public criteriaLabel:any=[];
     private fieldsData = [];
     private isAdvanceFilter:boolean=false;
     private advanceFilterGo:boolean=false;
  public currentFieldData={
    fieldDataId:'',
    fieldIndex:'',
    fieldValueId:''
  };

    @ViewChild('myTable') table: any;
    @ViewChild(AdvanceFilterComponent) advFilterObj:AdvanceFilterComponent;

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
    trackImage:any;

    constructor(
        private _router: Router,
        private _service: StoryService,private projectService:ProjectService, private http: Http, private route: ActivatedRoute,private shared:SharedService,private _ajaxService: AjaxService,private location:Location) {  }

    ngOnInit() {
        window.scrollTo(0,0);
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
            if(params['filter']!=undefined){
                this.filterValue=params['filter'];
                this.filterType=params['filterType'];
            }else if(params['adv']!=undefined){
                this.isAdvanceFilter = params['adv'];
                this.advFilterData=JSON.parse(params['advData']);
            }
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
                    if(!this.isAdvanceFilter ){
                this.setFilterValue(response).then((val:any)=>{ ;
                thisObj.FilterOption=response.data[val].filterValue;
                thisObj.FilterOptionToDisplay=response.data;
                if(localStorage.getItem('filterArray')!=null){
            var filterArray=JSON.parse(localStorage.getItem('filterArray'));
            thisObj.selectedFilter=filterArray;
            }
                thisObj.page(thisObj.projectId,thisObj.offset, thisObj.limit, thisObj.sortvalue, thisObj.sortorder,thisObj.selectedFilter);
           
                })
        }else{
            thisObj.FilterOption=response.data[1].filterValue;
            thisObj.FilterOptionToDisplay=response.data;
        }
               
            })
                
            
             
   /*
        @params    :  offset,limit,sortvalue,sortorder
        @Description: Default routing
        */
        
        thisObj.page(thisObj.projectId,thisObj.offset, thisObj.limit, thisObj.sortvalue, thisObj.sortorder,thisObj.selectedFilter);
        var ScrollHeightDataTable=jQuery(".ngx-datatable").width() - 12;
        jQuery("#filterDropdown").css("paddingRight",10);
       jQuery(".ngx-datatable").css("width",ScrollHeightDataTable);
       
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

/**
 * @description Preparing dropdown list 
 */
setFilterValue(response){

     var error=false;
     var thisObj = this;
     var index:any=-1;
     var outer:boolean=true;
     var inner:boolean=true;
      var promise = new Promise((resolve, reject) => {
       
    setTimeout(() => {
      response.data.forEach(element => {
          
            if(outer){
                index+=1;
                 element.filterValue.forEach(obj=>{
                        if(inner && obj.value.type==thisObj.filterType && obj.value.id==thisObj.filterValue){
                           localStorage.setItem('filterArray',JSON.stringify(obj.value)); 
                           thisObj.selectedFilter=obj.value;
                           inner=outer=false;
                        }
                    })
                 }
                  });
  
      if (error) {
        reject();
      } else {
       if(index== -1 || inner)
        {
       index = 1;
       thisObj.selectedFilter =response.data[1].filterValue[0].value;
       localStorage.setItem('filterArray',JSON.stringify(thisObj.selectedFilter));
        }
        resolve(index);
      }
    }, 100);
  });
  return promise;
}

        /*
        @params    :  offset,limit,sortvalue,sortorder
        @Description: StoryComponent/Task list Rendering
        */
        
    page(projectId,offset, limit, sortvalue, sortorder,selectedOption ) {
        if(selectedOption!=null) //added by Ryan
        {
            this.filterValue=selectedOption.id;
            this.filterType=selectedOption.type;
            localStorage.setItem('filterArray',JSON.stringify(selectedOption));
        }
       
         this.rows =[];
        this._service.getAllStoryDetails(projectId, offset, limit, sortvalue, sortorder,selectedOption,(response) => {
            let jsonForm = {};
            if (response.statusCode == 200) {
                this.criteriaLabel=[];
                this.rows = response.data;        
              

                this.rows = response.data;

                this.count = response.totalCount;
                for(var i in this.editing){
          this.editing[i] = false;
      }
                
                /* Section To Remember the State of Pages and to replace the Url with required params while user navigation */
                this.pageNo=offset+1;//added by Ryan
                this.sortorder=sortorder;//added by Ryan
                this.sortvalue=sortvalue;//added by Ryan
                this.offset=this.pageNo-1;//added by Ryan
                this.rememberState(this.pageNo,this.sortorder,this.sortvalue,this.filterValue);
                var url='/project/'+this.projectName+'/list?page='+(offset+1)+'&sort='+sortorder+'&col='+sortvalue+'&filter='+this.filterValue+'&filterType='+this.filterType;
                this.location.replaceState(url);
                /* =========Section End==========================================*/ 

            } else {
            }
        });
    }
    /*
    @When Clicking pages
    */
    onPage(event) {
        this.offset = event.offset;
        this.limit = event.limit;
        if(!this.advanceFilterGo)
        this.page(this.projectId,this.offset, this.limit, this.sortvalue, this.sortorder,this.selectedFilter);
        else
        this.advFilterObj.applyFilter('go',this.offset);
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
        if(this.selectedFilter.type=='buckets'){
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

/**
 * @description Editing the story from dashboard
 */
editThisField(event,fieldId,fieldDataId,fieldTitle,renderType,restoreFieldId,value_id,row){ 
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
          statusId:value_id
        };
        //Fetches the field list data for current dropdown in edit mode.
        this._ajaxService.AjaxSubscribe("story/get-field-details-by-field-id",reqData,(data)=>
            { 
                var listData = {
                  list:data.data
                };
                var priority=(fieldTitle=="priority"?true:false);
                this.dropDisplayList=this.prepareItemArray(listData.list,priority,fieldTitle);
                this.dropList=this.dropDisplayList[0].filterValue;
                //sets the dropdown prefocused
                if(fieldTitle=='workflow'){
                  let value=this.dropList[0].label;
                  let valueId=this.dropList[0].value.Id;
                   this.fieldsData[this.currentFieldData.fieldIndex].valueId=valueId;
                   this.fieldsData[this.currentFieldData.fieldIndex].value=value;
                }
                jQuery("#"+inptFldId+" div").click();
                
            });
    }else if(renderType == "date"){
      //sets the datepicker prefocused
      this.minDate=new Date();
      this.dateVal = row[6].field_value;
      setTimeout(()=>{
        jQuery("#"+inptFldId+" span input").focus();
      },150);    
    }

 
    
  }

  /**
   * @description Prepares the Custom Dropdown's Options array.
   */
 public prepareItemArray(list:any,priority:boolean,status:string){
   var listItem=[];
    var listMainArray=[];
     if(list.length>0){
       if(status == "Assigned to" || status == "Stake Holder"){
       listItem.push({label:"--Select a Member--", value:"",image:"",priority:priority,type:status});
       }
         for(var i=0;list.length>i;i++){
           listItem.push({label:list[i].Name, value:list[i].Id,image:list[i].ProfilePic,priority:priority,type:status});
       }
     }
      listMainArray.push({type:"",filterValue:listItem});
    return listMainArray;
}
 /**
  * @description Restores the editable field to static mode.
                 Also prepares the data to be sent to service to save the changes.
                 This is common to left Column fields.
  */
  restoreField(editedObj,restoreFieldId,fieldIndex,renderType,fieldId,showField,row,isChildActivity=0){
    var intRegex = /^\d+$/;
    var floatRegex = /^((\d+(\.\d *)?)|((\d*\.)?\d+))$/;
      var postEditedText={
                        projectId:this.projectId,
                        isLeftColumn:1,
                        id:fieldId,
                        value:"",
                        ticketId:row[0].field_value,
                        editedId:restoreFieldId.split("_")[0],
                        userid:editedObj.value
                      };  
                       var appendHtml ='';  
                       var ProfilePic='';              
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
             if(restoreFieldId.split("_")[0] == "assignedto"){
             jQuery("#"+showField+'_assignedto').remove();
                 appendHtml = (restoreFieldId.split("_")[0] == "assignedto")?"<img id="+showField+"_assignedto data-toggle=tooltip data-placement=top class='profilepic_table' src='"+row[2].other_data+"'/>&nbsp;":"";
             
               document.getElementById(restoreFieldId).innerHTML = (editedObj.text == ""||editedObj.text == "--Select a Member--") ? "--":appendHtml+editedObj.text; 
         }else{
             appendHtml = (restoreFieldId.split("_")[0] == "priority")?"&nbsp; <i class='fa fa-circle "+editedObj.text+"' aria-hidden='true'></i>":"";
             document.getElementById(restoreFieldId).innerHTML = (editedObj.text == ""||editedObj.text == "--Select a Member--") ? "--":editedObj.text+appendHtml;
            }          
            postEditedText.value = editedObj.value;
            break;

            case "date":
            var monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
                            "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
          var date = (monthNames[this.dateVal.getMonth()]) + '-' + this.dateVal.getDate() + '-' +  this.dateVal.getFullYear();
            date = date.replace(/(\b\d{1}\b)/g, '0$1');         
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
   /**
    * @description Inline story title edit closing when click on out side.
    */
closeTitleEdit(editedText,restoreFieldId,fieldIndex,renderType,fieldId,showField,row,isChildActivity=0){
        if(editedText.trim() !=""){
        
          document.getElementById(restoreFieldId).innerText= editedText;
         
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
/**
 *  @description Added by Padmaja for Inline Edit
Common Ajax method to save the changes.
  
 */
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
                   }
               
          }
    
             else if(postEditedText.editedId == "estimatedpoints"){ 
                 jQuery("#"+postEditedText.ticketId+"_totalestimatepoints").html(result.data.updatedFieldData.value);
               }
          
             else if(result.data.updatedState!=''){ 
                 document.getElementById(showField+"_workflow").innerText=result.data.updatedState.state;
           }
            else if(result.data.activityData.data.ActionFieldName =='assignedto'){ 
                if(result.data.activityData.data.NewValue.ProfilePicture !=''){
                        var imgObj1=jQuery("#"+showField+'_assignedto').html();
                
                    jQuery("#"+showField+'_assignedto').attr('src',result.data.activityData.data.NewValue.ProfilePicture);                
                   }
            }
            else { 
                if(result.data.activityData.data.PropertyChanges[0].ActionFieldName =='assignedto'){ 
                if(result.data.activityData.data.PropertyChanges[0].NewValue.ProfilePicture  !=''){
                    var imgObj = <HTMLImageElement>document.getElementById(showField+"_assignedto");
                   imgObj.src=result.data.activityData.data.PropertyChanges[0].NewValue.ProfilePicture ;
                     }
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

/**
 * @author Anand
 * @description Get advance filtered data.
 */
getFilteredData(response){
   let jsonForm = {};
            if (response.statusCode == 200) {
                var filterData = response.data.filterData;
                if(filterData.length==0){
                this.advanceFilterGo = true
                this.rows = response.data.ticketData; 
                this.rows = response.data.ticketData;
                 this.count =response.totalCount;
                for(var i in this.editing){
                this.editing[i] = false;
   
                  }
                }else{
          this.advanceFilterGo = false;
          var filterOption ={
          "label":filterData.Name,
          "value": {
            "label": filterData.Name,
            "id": filterData.Id,
            "type": "personalfilters",
            "showChild": 0,
            "isChecked": false,
            "canDelete": true
          }
        }
        var updated:boolean=false;
         this.FilterOptionToDisplay[0].filterValue.forEach((element) =>{
             if(element.value.id==filterOption.value.id){
                 element=filterOption;
                 updated = true;
                 return true;
             }

         })
         if(!updated){
           this.FilterOptionToDisplay[0].filterValue.push(filterOption);
         }
        this.selectedFilter=filterOption.value;
        this.filterDashboard();
       }
   } 
}
 
/**
 * @author Anand
 */
getFilterCriteria(result){
this.criteriaLabel=result;
}
/**
 * @description Giving option to delete the custom filters
 */
deleteOption(event){
    var thisObj = this;
  this.FilterOptionToDisplay.forEach(element=>{
      if(element.type==event.type){
thisObj._service.deleteAdvanceFilter(thisObj.projectId,event.option.value.id,(response)=>{
     if (response.statusCode == 200) {
        element.filterValue.splice(event.index, 1);
     }
})
        
      }
  })

}

    }

