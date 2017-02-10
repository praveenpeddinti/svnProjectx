import { Component, OnInit,ViewChild } from '@angular/core';
import { Router } from '@angular/router';
import { Headers, Http } from '@angular/http';
import { AjaxService } from '../../ajax/ajax.service';
import { StoryService} from '../../services/story.service';
import {NgForm} from '@angular/forms';

@Component({
  selector: 'app-story-edit',
  templateUrl: './story-edit.component.html',
  styleUrls: ['./story-edit.component.css'],
  providers:[StoryService]
})
export class StoryEditComponent implements OnInit {

    private ticketData:any=[];
    private ticketid;
    private description;
    public form={};
  private fieldsData = [];
  // private fieldsBindingArray=[];
  private showMyEditableField =[];
  public toolbar={toolbar : [
    [ 'Heading 1', '-', 'Bold','-', 'Italic','-','Underline','Link','NumberedList','BulletedList' ]
]};

  constructor(private _ajaxService: AjaxService,private _service: StoryService,
    public _router: Router,
    private http: Http) { 
this._ajaxService.AjaxSubscribe("story/edit-ticket",{},(data)=>
    { 
       
         
         this.ticketData = data.data;
         this.description=data.data.Description;
         console.log("++++++++++++++"+JSON.stringify(this.ticketData));
         this.fieldsData = this.fieldsDataBuilder(data.data.Fields,data.data.TicketId);
        //  var t = this.fieldsData.length;
        //  this.fieldsBindingArray[t];
         console.log("Field Data----"+this.fieldsData);
         
    });
    }


  ngOnInit() {
    
  }

   editThisField(event,fieldIndex){
    console.log(event.target.id);
    // var thisFieldId = event.target.id;
    // var thisField;
    // var replaceHtml ;
    // if(thisFieldId !="" && thisFieldId !=null){
    //     thisField = document.getElementById(thisFieldId);
    //     replaceHtml = document.createElement("input");
    //     replaceHtml.setAttribute("value",thisField.textContent);
    //     replaceHtml.setAttribute("id",thisFieldId);//"<input type='text' value='"+thisField.textContent+"'/>"
    //     thisField.parentNode.replaceChild(replaceHtml,thisField);
    // }
    
    this.showMyEditableField[fieldIndex] = false;

  }


  fieldsDataBuilder(fieldsArray,ticketId){
    alert("in builder"+fieldsArray);
    let fieldsBuilt = [];
    let data = {title:"",value:"",readonly:true,required:true,id:"",fieldDataId:"",fieldName:"",fieldType:"",renderType:"",type:"",listdata:[]};
    for(let field of fieldsArray){
      if(field.field_name != "customfield_2"){
      data = {title:"",value:"",readonly:true,required:true,id:"",fieldDataId:"",fieldName:"",fieldType:"",renderType:"",type:"",listdata:[]};
          switch(field.field_type){
            case "Text":
            data.title = field.title;
            data.value = field.value;
            data.renderType = "input";
            data.type="text";
            break;
            case "List":
            data.title = field.title;
            data.value = field.readable_value.Name;
            data.renderType = "select";
            data.listdata = field.meta_data;
            data.fieldDataId = field.readable_value.Id;
            break;
            case "Numeric":
            data.title = field.title;
            data.value = field.value;
            data.renderType = "input";
            data.type="text";
            break;
            case "Date":
            data.title = field.title;
            // alert(field.readable_value.date.split(" ")[0]+"++++++++Date++++++++++");
            data.value = field.readable_value;
            data.renderType = "input";
            data.type="date";
            break;
            case "DateTime":
            data.title = field.title;
            // alert(field.readable_value.date.split(".")[0]+"++++++++++DateTime++++++++++++");
            data.value = field.readable_value;
            data.renderType = "input";
            data.type="datetime";
            break;
            case "Team List":
            data.title = field.title;
            data.value = field.readable_value.UserName;
            data.renderType = "select";
            data.listdata = this.ticketData.collaborators;
            data.fieldDataId = field.readable_value.CollaboratorId;
            
            break;
            // case "Checkbox":
            // break;
            case "Bucket":
            data.title = field.title;
            data.value = field.readable_value.Name;
            data.renderType = "select";
            data.listdata = field.meta_data;
            data.fieldDataId = field.readable_value.Id;
            break;

          }
          data.readonly = (field.readonly == 1)?true:false;
          data.required = (field.required == 1)?true:false;
          data.id =  ticketId+"_"+field.field_name;
          // if(field.field_type == "Bucket" || field.field_type == "Team List" || field.field_type == "Team List"){
          //   data.type = "List";
          // }else{
            data.fieldType = field.field_type;
          // }
          data.fieldName =  field.field_name;
          fieldsBuilt.push(data);
          this.showMyEditableField.push((field.readonly == 1)?false:true);
          // this.fieldsBindingArray.push(field.Id);
      }
    }
console.log(JSON.stringify(fieldsBuilt));
    return fieldsBuilt;

  }

  editStory(edit_data){
    console.log("===Edit Form Data==="+"===="+typeof(edit_data)+"==="+JSON.stringify(edit_data));
    this._ajaxService.AjaxSubscribe("story/edit-ticket-details",edit_data,(data)=>
    { 
      alert("success");
    });
     
     
}

}
