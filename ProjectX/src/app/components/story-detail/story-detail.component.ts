import { Component, OnInit } from '@angular/core';
import { Router,ActivatedRoute } from '@angular/router';
import { Headers, Http } from '@angular/http';
import { AjaxService } from '../../ajax/ajax.service';


@Component({
  selector: 'app-story-detail',
  templateUrl: './story-detail.component.html',
  styleUrls: ['./story-detail.component.css'],
  
})
export class StoryDetailComponent implements OnInit {
  // private _ajaxService: AjaxService;
  //   public _router: Router;
  //   private http: Http;
//  testObj = {};
//   newObj = {};
  private ticketData;
  private ticketId;
  private fieldsData = [];
  private showMyEditableField =[];
  private ticketDesc="";
  private showDescEditor=true;
  private toolbarForDetail={toolbar : [
    [ 'Heading 1', '-', 'Bold','-', 'Italic','-','Underline','Link','NumberedList','BulletedList' ]
]};
  // private makeFocused = []; 
  private dropList=[];
  constructor( private _ajaxService: AjaxService,
    public _router: Router,
    private http: Http,private route: ActivatedRoute) {}
 
  ngOnInit() {
    // var parms=this.route.params.subscribe;
 /** @Praveen P
 * Getting the TicketId for story dashboard
 */
      this.route.params.subscribe(params => {
            this.ticketId = params['id'];
        });
        //  alert("constructor"+this.ticketId);
   
      var ticketIdObj={'ticketId': this.ticketId};
        this._ajaxService.AjaxSubscribe("story/get-ticket-details",ticketIdObj,(data)=>
        { 
          
            
            this.ticketData = data;
         //   alert(this.ticketId+"++++++++++++++"+JSON.stringify(this.ticketData));
            this.ticketDesc = data.data.Description;
            this.fieldsData = this.fieldsDataBuilder(data.data.Fields,data.data.TicketId);
            //  alert("========>"+JSON.stringify(this.fieldsData));
            
        });


  }
  openDescEditor(){
    this.showDescEditor = false;
    // document.getElementById('tktDesc').focus();
  }

submitDesc(){

 // alert("++++++submitted++++++++++"+this.ticketDesc);

}
cancelDesc(){
  this.showDescEditor = true;

}

  descBlur(){
  //  alert("yeaaa working");
  }

  onClick(){
    // alert(val);
    this._router.navigate(['story-edit',this.ticketId]);
  }

  editThisField(event,fieldIndex,fieldId,fieldDataId){
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
    this.dropList=[];
    var fieldName = fieldId.split("_")[1];
    var inptFldId = fieldId+"_"+fieldIndex;
    this.showMyEditableField[fieldIndex] = false;
    setTimeout(()=>{document.getElementById(inptFldId).focus();},150);
var reqData = {
  FieldId:fieldDataId,
  ProjectId:this.ticketData.data.Project.PId,
  TicketId:this.ticketData.data.TicketId
};
// alert(JSON.stringify(reqData)+"reqest data");
this._ajaxService.AjaxSubscribe("story/get-field-details-by-field-id",reqData,(data)=>
    { 
       
         console.log(JSON.stringify(data));
         var currentId = document.getElementById(inptFldId+"_currentSelected").getAttribute("value");
         data.getFieldDetails.currentSelectedId = currentId;
         this.dropList = data.getFieldDetails;
        
         
    });

    // switch(fieldName){
    //   case "planlevel":
    //   this.dropList=["Level1","Level2","Level3","Final Level"];
    //   break;
    //   case "tickettype":
    //   this.dropList=["Story","Task","Time Pass","Trash","Not yet Decided"];
    //   break;
    //   case "stakeholder":
    //   this.dropList=["Madan","Jagadish","Bekkam","Kishore","Moin","Paddamma"];
    //   break;
    // }
    

  }

   restoreField(editedVal,restoreFieldId,fieldIndex){
    document.getElementById(restoreFieldId).innerHTML = editedVal;
    var currentSelectedId = document.getElementById(restoreFieldId+"_"+fieldIndex+"_currentSelected");
    currentSelectedId.setAttribute("value",editedVal);
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
    let data = {title:"",value:"",valueId:"",readonly:true,required:true,elId:"",fieldType:"",renderType:"",type:"",Id:""};
    for(let field of fieldsArray){
      if(field.field_name != "customfield_2"){
      data = {title:"",value:"",valueId:"",readonly:true,required:true,elId:"",fieldType:"",renderType:"",type:"",Id:""};
          switch(field.field_type){
            case "Text":
            data.title = field.title;
            data.value = field.value;
            data.renderType = "input";
            data.type="text";
            break;
            case "List":
            data.title = field.title;
            if(field.readable_value != false){
                data.value = field.readable_value.Name;
                data.valueId = field.readable_value.Id
            }
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
            if(field.readable_value != false){
                data.value = field.readable_value.UserName;
                data.valueId = field.readable_value.CollaboratorId
            }
            data.renderType = "select";
            break;
            // case "Checkbox":
            // break;
            case "Bucket":
            data.title = field.title;
            if(field.readable_value != false){
              data.value = field.readable_value.Name;
              data.valueId = field.readable_value.Id
            }
            data.renderType = "select";
            break;

          }
          data.readonly = (field.readonly == 1)?true:false;
          data.required = (field.required == 1)?true:false;
          data.elId =  ticketId+"_"+field.field_name;
          data.Id = field.Id;
          // if(field.field_type == "Bucket" || field.field_type == "Team List" || field.field_type == "Team List"){
          //   data.type = "List";
          // }else{
            data.fieldType = field.field_type;
          // }
          fieldsBuilt.push(data);
          this.showMyEditableField.push((field.readonly == 1)?false:true);
          // this.makeFocused.push(new EventEmitter());
      }
    }

    return fieldsBuilt;

  }

}
