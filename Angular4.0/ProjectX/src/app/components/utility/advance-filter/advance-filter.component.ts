import { Component, OnInit,Input,Output,EventEmitter,ElementRef} from '@angular/core';
import { StoryService } from '../../../services/story.service';
declare var jQuery:any;
@Component({
  host: {
    '(document:click)': 'onClick($event)',
  },
  selector: 'app-advance-filter',
  templateUrl: './advance-filter.component.html',
  styleUrls: ['./advance-filter.component.css'],
   providers: [StoryService],
})
export class AdvanceFilterComponent implements OnInit {

  public showPanel:boolean=false;
  public advanceFilter:any;
  public selectedFilters:any=[];
  public selectedStateIds:any=[];
  public dateObj:any={
          "label": "Custome date",
          "id": "6",
          "type": "DueDate",
          "showChild": 1,
          "isChecked": false,
          "dateVal":''
}
public filterName:any="";
public filterError:any='';
public filterSelectedError:any='';
public emptyObjLength:any=0;
public defaultCall:boolean=false;
public filterCriteria:any=[];
   @Input()  projectId:any;
   @Input()  filterData:any='';
   @Output() emitFiltereResponse: EventEmitter<any> = new EventEmitter();
   @Output() emitCriteriaResponse: EventEmitter<any> = new EventEmitter();
  constructor( private _service: StoryService,private _eref: ElementRef) { }

  ngOnInit() {
      var thisObj=this;
      var filterKey:any={}; 
   thisObj._service.getAdvanceFilterOptions(thisObj.projectId,(response) => {
        
               this.advanceFilter=response.data;

               this.advanceFilter.forEach(element => {
                filterKey[element.type]=[];
                if(typeof thisObj.filterData === 'object'){
                if(thisObj.filterData.hasOwnProperty(element.type)){
                    element.filterValue.forEach(val=>{
                     thisObj.filterData[element.type].forEach(obj =>{
                     if(val.value.id==obj.id){ 
                       val.value.isChecked=true;
                       filterKey[element.type].push(val.value);
                       thisObj.defaultCall=true;
                       thisObj.filterCriteria.push({'type':element.type,'label':val.value.label});
                     }
                     })
                   })
                 }
                }  
       }) 
   this.selectedFilters.push(filterKey);
   var filterString=JSON.stringify(this.selectedFilters);
   this.emptyObjLength = 62;//filterString.length;
   if(this.defaultCall)this.applyFilter();
   })
    
  }

onClick(event) {
   if (!this._eref.nativeElement.contains(event.target)) 
     this.showPanel=false;
  }
showFilterPanel(){
   var thisObj=this;
     this.showPanel=true;
   
}

selectFilterOption(filterObj,filterType,index){
  var thisObj= this;
  if(this.selectedFilters[0][filterType].indexOf(filterObj)==-1){
    //check and push
    if(filterObj.type=='state'){
      this.selectedStateIds.push(filterObj.id);
      this.advanceFilter[index+1]['filterValue'].forEach(element => {
              if(element.value.stateId==filterObj.id){
                element.value.isChecked = true;
                thisObj.selectedFilters[0][thisObj.advanceFilter[index+1]['type']].push(element.value);
              }
               
      })
    }
     if(filterObj.type=='status'){
      this.selectedStateIds.push(filterObj.id);
      this.advanceFilter[index-1]['filterValue'].forEach(element => {
        if(this.selectedFilters[0][thisObj.advanceFilter[index-1]['type']].indexOf(element.value)==-1){
            if(element.value.id==filterObj.stateId){
                element.value.isChecked = true;
                thisObj.selectedFilters[0][thisObj.advanceFilter[index-1]['type']].push(element.value);
              }
        }      
      })
    }
    filterObj.isChecked=true;
    this.selectedFilters[0][filterType].push(filterObj);
    this.filterSelectedError='';
  }else{

    //uncheck and remove

   filterObj.isChecked=false;
   this.selectedFilters[0][filterType].splice(this.selectedFilters[0][filterType].indexOf(filterObj), 1);
   if(filterObj.type=='state'){
      this.advanceFilter[index+1]['filterValue'].forEach(element => {
              if(element.value.stateId==filterObj.id)
               element.value.isChecked = false;
               thisObj.selectedFilters[0][thisObj.advanceFilter[index+1]['type']].splice(thisObj.selectedFilters[0][thisObj.advanceFilter[index+1]['type']].indexOf(element.value), 1);
      })
      this.selectedStateIds.splice(this.selectedStateIds.indexOf(filterObj.id), 1);
    }
    if(filterObj.type=='status'){
      this.selectedStateIds.push(filterObj.id);
        this.advanceFilter[index-1]['filterValue'].forEach(element => {
       if(!(thisObj.selectedFilters[0][thisObj.advanceFilter[index-1]['type']].indexOf(element.value)==-1)){
              if(element.value.id==filterObj.stateId && thisObj.selectedFilters[0][filterType].filter(obj => obj.stateId === filterObj.stateId).length==0){
                element.value.isChecked = false;
                thisObj.selectedFilters[0][thisObj.advanceFilter[index-1]['type']].splice(thisObj.selectedFilters[0][thisObj.advanceFilter[index-1]['type']].indexOf(element.value), 1);
              }
       }    
      })
    
     
    }
 }

 console.log("Selected_________________filter"+JSON.stringify(this.selectedFilters));
}

selectCustomDate(value,type,index){
    this.dateObj.isChecked=true;
    this.selectedFilters[0][type].push(this.dateObj);
}



applyFilter(option=''){
  if(option=='save' && this.filterName == ''){
    this.filterError='Please give name to save filter as.';
  }else if(this.emptyObjLength != (JSON.stringify(this.selectedFilters).length)){
    this.filterName=(option=='go')?'':this.filterName;
    this.filterError='';
    this.filterSelectedError='';
    var reqParams={
    'projectId':this.projectId,
    'filterName':this.filterName,
    'selectedFilters':this.selectedFilters,
    'offset' : 0,
    'pagesize': 10,
    'sortvalue' : 'Id',
    'sortorder': 'desc',
  }
  this._service.applyAdvanceFilter(reqParams,(response)=>{
    this.filterName='';
     this.showPanel=false;
     if(option=='')
     this.emitCriteriaResponse.emit(this.filterCriteria);
    this.emitFiltereResponse.emit(response);
    // console.log("Filtered___data_______-----"+JSON.stringify(response));
  })
  }else{
    this.filterSelectedError="No filter selected";
  }
  

}

}
