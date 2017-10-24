import { Component, OnInit,Input } from '@angular/core';
import { Router } from '@angular/router';
import { StoryService } from '../../services/story.service';
import { StoryComponent } from '../story/story-form.component';
import { AjaxService } from '../../ajax/ajax.service';
declare var jQuery:any;

@Component({
  selector: 'app-childtask',
  templateUrl: './childtask.component.html',
  styleUrls: ['./childtask.component.css']
})
export class ChildtaskComponent implements OnInit {
  @Input() ticketId;
  @Input() projectId;
   row1 = [];
  private ticketName;
  public FilterOption=[];
    public FilterOptionToDisplay=[];
     public selectedFilter=null;  
     public projectName; 
  
     public statusId=''; 
     private dropList=[];
     private ticketData;
     private showMyEditableField =[];
     private dropDisplayList=[];
     private dateVal = new Date();
     public minDate:Date;
    
     public editing = {};
     private fieldsData = [];
  public currentFieldData={
    fieldDataId:'',
    fieldIndex:'',
    fieldValueId:''
  };
     public inlineTimeout;
    columnsSub = [
                {
                    name: 'Id',
                    flexGrow: 1,
                    sortby: 'task',
                    class: 'taskRstory',
                    type:'task'
                },
                {
                    name: 'Title',
                    flexGrow: 3,
                    sortby: 'task',
                    class: 'titlecolumn',
                    type:'task'
                },
                {
                    name: 'Assigned to',
                    flexGrow: 1.5,
                    sortby: 'task',
                    class: '',
                    type:'task'
                },
                {
                    name: 'Priority',
                    flexGrow: 1,
                    sortby: 'task',
                    class: 'prioritycolumn',
                    type:'task'
                },
                {
                    name: 'State',
                    flexGrow: 1,
                    sortby: 'task',
                    class: 'statusbold',
                    type:'task'
                },
                {
                    name: 'Bucket',
                    flexGrow: 1,
                    sortby: 'task',
                    class: 'bucket',
                    type:'task'
                },
                {
                    name: 'Due Date',
                    flexGrow: 1,
                    sortby: 'task',
                    class: 'duedate',
                    type:'task'
                },
                {
                    name: '',
                    flexGrow: 0.3,
                    sortby: 'task',
                    class: 'arrowClass',
                    type:'task'
                }
                
              ];
  
  constructor( 
private _router: Router,
private _service: StoryService,private _ajaxService: AjaxService) { }

  ngOnInit() {
  this.ticketName = this.ticketId;
  
     this.row1=[];
     console.log('In child task', this.ticketId);
    this._service.getSubTasksDetails(this.projectId, this.ticketId, (response) => {
     console.log('response came----');
                   let jsonForm = {};
            if (response.statusCode == 200) {
                this.row1=response.data;
              
                console.log('Toggled Expand Row!', this.row1);
            } else {
                console.log("fail---");
            }
        });
  
  
  }
  
  /* @Praveen P
        * This child method is used story/task details when story/task id click component
        */
    showStoryDetail(event) {
            this._router.navigate(['project',event[0].other_data.project_name, event[0].field_value,'details']);
    }


editThisField(event,fieldId,fieldDataId,fieldTitle,renderType,restoreFieldId,row){ 
     this.dropList=[];
    var inptFldId = fieldId;
      for(var i in this.editing){
          this.editing[i] = false;
      }
      this.editing[restoreFieldId]=true;
     

    if(renderType == "select"){
        var reqData = {
          fieldId:fieldDataId,
          projectId:localStorage.getItem('ProjectId'),
          ticketId:row[0].field_value,
          workflowType:1,
          statusId:1,
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
          
    var intRegex = /^\d+$/;
    var floatRegex = /^((\d+(\.\d *)?)|((\d*\.)?\d+))$/;
      var postEditedText={
                        projectId:localStorage.getItem('ProjectId'),
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
   
closeTitleEdit(editedText,restoreFieldId,fieldIndex,renderType,fieldId,showField,row,isChildActivity=0){
        if(editedText.trim() !=""){
         
          document.getElementById(restoreFieldId).innerText= editedText;
        
      // Added by Padmaja for Inline Edit
          var postEditedText={
            isLeftColumn:0,
            id:'Title',
            value:editedText,
            ticketId:row[0].field_value,
            projectId:localStorage.getItem('ProjectId'),
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
                                  
                   }
               
          }
    
             else if(postEditedText.editedId == "estimatedpoints"){ 
                 jQuery("#"+postEditedText.ticketId+"_totalestimatepoints").html(result.data.updatedFieldData.value);
               }
          
             else if(result.data.updatedState!=''){ 
                 document.getElementById(showField+"_workflow_child").innerText=result.data.updatedState.state;
           } 
                else if(result.data.activityData.data.ActionFieldName =='assignedto'){ 
                if(result.data.activityData.data.NewValue.ProfilePicture !=''){
                    var imgObj = <HTMLImageElement>document.getElementById(showField+"_assignedto_child")
                 imgObj.src=result.data.activityData.data.NewValue.ProfilePicture;
             
           }
                }
             

 }
        });

      
        },500)
    }

    }

