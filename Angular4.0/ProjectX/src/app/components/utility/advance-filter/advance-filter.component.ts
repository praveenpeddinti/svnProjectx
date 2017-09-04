import { Component, OnInit,Input,Output,EventEmitter} from '@angular/core';
import { StoryService } from '../../../services/story.service';
declare var jQuery:any;
@Component({
  selector: 'app-advance-filter',
  templateUrl: './advance-filter.component.html',
  styleUrls: ['./advance-filter.component.css'],
   providers: [StoryService],
})
export class AdvanceFilterComponent implements OnInit {

  public showPanel:boolean=false;
  public advanceFilter:any;
   @Input()  projectId:any;
   @Output() cancleToParent: EventEmitter<any> = new EventEmitter();
  constructor( private _service: StoryService) { }

  ngOnInit() {
      var thisObj=this;
     
     jQuery(document).ready(function(){
      jQuery(document).bind("click",function(event){ 
        if(event.target.id == "advance_filter_icon")
          return;
        else if(thisObj.showPanel){
          thisObj.showPanel=false;
          // alert(thisObj.showPanel)  ;
        }
      });
     
    });
  }

showFilterPanel(){
   var thisObj=this;
     
   thisObj._service.getAdvanceFilterOptions(thisObj.projectId,(response) => {
        
               console.log("from__adv_filter__"+JSON.stringify(response));
               this.advanceFilter=response.data;
               this.showPanel=true;
   })
   
   
}
}
