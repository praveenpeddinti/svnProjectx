import { register } from 'ts-node/dist';
import { Component, Directive,ChangeDetectorRef} from '@angular/core';
import { StoryService} from '../../services/story.service';
import { Router } from '@angular/router';
import { GlobalVariable } from '../../config';
import { Http, Headers } from '@angular/http';


@Component({

    selector: 'story-dashboard-view',
    providers: [StoryService],
    templateUrl: 'story-dashboard-component.html',
    
    	
})

export class StoryDashboardComponent {

    public errorMsg = '';
    public offset=0;
    public pagesize=5;
    // public aa=1;
    // public bb=1;
    public response :any = [];
    //public collection: any[] = someArrayOfThings; 
    public submitted = false;
    public rows:any  =[];
    /*public rows = [
    { name: 'Austin', gender: 'Male', company: 'Swimlane' },
    { name: 'Dany', gender: 'Male', company: 'KFC' },
    { name: 'Molly', gender: 'Female', company: 'Burger King' },
     { name: 'Austin', gender: 'Male', company: 'Swimlane' },
    { name: 'Dany', gender: 'Male', company: 'KFC' },
    { name: 'Molly', gender: 'Female', company: 'Burger King' },
     { name: 'Austin', gender: 'Male', company: 'Swimlane' },
    { name: 'Dany', gender: 'Male', company: 'KFC' },
    { name: 'Molly', gender: 'Female', company: 'Burger King' },
     { name: 'Austin', gender: 'Male', company: 'Swimlane' },
    { name: 'Dany', gender: 'Male', company: 'KFC' },
    { name: 'Molly', gender: 'Female', company: 'Burger King' },
     { name: 'Austin', gender: 'Male', company: 'Swimlane' },
    { name: 'Dany', gender: 'Male', company: 'KFC' },
    { name: 'Molly', gender: 'Female', company: 'Burger King' },
  ];*/
//   public columns = [
//     { prop: 'TicketId' },
//     { prop: 'Title' },
//     { prop: 'Description'}
//   ];
    onSubmit() {
        this.submitted = true;
    }

headers = new Headers({ 'Content-Type': 'application/x-www-form-urlencoded' });

    constructor(
        
        private _router: Router,
        private _service: StoryService, private http: Http) { }
ngOnInit() {

    this._service.getAllStoryDetails(1,this.offset,this.pagesize,(response)=>{ 
              let jsonForm={};
              if(response.statusCode==200){
                  response.data.forEach(element => {
                      //this.response = [{Id:this.aa},{Id:this.bb}];
                   //    this.count=20;
                   
                      this.rows = response.data;
                           //this.cureentPage=this.offset;
                     //console.log("responseoooo firsttime" +JSON.stringify(this.response)  )
                  });
                
            }else{
            console.log("fail---");
            }
        });
    }
        
        
  
  onPage(event){
    
  }

  renderStoryForm()
  {
      this._router.navigate(['story-form']);
  }
 /** @Praveen P
 * Pass the TicketId for story-detail component
 */
  showStoryDetail(row){
      //console.log('Toggled Expand Row!', row.TicketId);
       this._router.navigate(['story-detail',row.TicketId]);
      
  }
   
}

