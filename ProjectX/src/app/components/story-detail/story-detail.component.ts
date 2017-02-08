import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { Headers, Http } from '@angular/http';
import { AjaxService } from '../../ajax/ajax.service';

@Component({
  selector: 'app-story-detail',
  templateUrl: './story-detail.component.html',
  styleUrls: ['./story-detail.component.css']
})
export class StoryDetailComponent implements OnInit {
  // private _ajaxService: AjaxService;
  //   public _router: Router;
  //   private http: Http;
//  testObj = {};
//   newObj = {};


  private ticketData;
  private fieldsData = [];
  private showMyEditableField =[]; 
  constructor( private _ajaxService: AjaxService,
    public _router: Router,
    private http: Http) { 
    // alert("constructor");
    this._ajaxService.AjaxSubscribe("story/get-ticket-details","",(data)=>
    { 
       
         
         this.ticketData = data;
         alert("++++++++++++++"+JSON.stringify(this.ticketData));
         this.fieldsData = this.fieldsDataBuilder(data.data.Fields,data.data.TicketId);
        //  alert("========>"+JSON.stringify(this.fieldsData));
         
    });
 
}
 
  ngOnInit() {
    
  }

  onClick(val){
    alert(val);
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

   restoreField(editedVal,restoreFieldId,fieldIndex){
    document.getElementById(restoreFieldId).innerHTML = editedVal;
    this.showMyEditableField[fieldIndex] = true;
    // alert(editedVal);
    

  }
  selectBlurField(fieldIndex){
      // document.getElementById(restoreFieldId).innerHTML = editedVal;
      this.showMyEditableField[fieldIndex] = true;
      // alert(editedVal);
      

    }
  fieldsDataBuilder(fieldsArray,ticketId){
    let fieldsBuilt = [];
    let data = {title:"",value:"",readonly:true,required:true,id:"",fieldType:"",renderType:"",type:""};
    for(let field of fieldsArray){
      if(field.field_name != "customfield_2"){
      data = {title:"",value:"",readonly:true,required:true,id:"",fieldType:"",renderType:"",type:""};
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
            data.value = field.readable_value.date.split(" ")[0];
            data.renderType = "input";
            data.type="date";
            break;
            case "DateTime":
            data.title = field.title;
            // alert(field.readable_value.date.split(".")[0]+"++++++++++DateTime++++++++++++");
            data.value = field.readable_value.date.split(".")[0];
            data.renderType = "input";
            data.type="datetime";
            break;
            case "Team List":
            data.title = field.title;
            data.value = field.readable_value.UserName;
            data.renderType = "select";
            break;
            // case "Checkbox":
            // break;
            case "Bucket":
            data.title = field.title;
            data.value = field.readable_value.Name;
            data.renderType = "select";
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
          fieldsBuilt.push(data);
          this.showMyEditableField.push((field.readonly == 1)?false:true);
      }
    }

    return fieldsBuilt;

  }

}
