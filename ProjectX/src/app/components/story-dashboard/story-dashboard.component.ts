import { register } from 'ts-node/dist';
import { Component, Directive, } from '@angular/core';
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
    public response :any = [];
    //public collection: any[] = someArrayOfThings; 
    public submitted = false;
    public rows = [
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
  ];
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

    this._service.getAllStoryDetails(1,(response)=>{
              let jsonForm={};
              if(response.statusCode==200){
                  response.data.forEach(element => {
                      this.response = response.data;
                     
                  });
                
            }else{
            console.log("fail---");
            }
        });
    }
        
        
  
  onPage(event){
   // alert("on click page");
  }
  
   
}

