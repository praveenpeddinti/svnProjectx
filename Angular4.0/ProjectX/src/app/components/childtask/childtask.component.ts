import { Component, OnInit,Input } from '@angular/core';
import { Router } from '@angular/router';
import { StoryService } from '../../services/story.service';

@Component({
  selector: 'app-childtask',
  templateUrl: './childtask.component.html',
  styleUrls: ['./childtask.component.css']
})
export class ChildtaskComponent implements OnInit {
  @Input() ticketId;
   row1 = [];
  private ticketName;
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
private _service: StoryService) { }

  ngOnInit() {
  this.ticketName = this.ticketId;
  
     this.row1=[];
     console.log('In child task', this.ticketId);
    this._service.getSubTasksDetails(1, this.ticketId, (response) => {
     console.log('response came----');
                   let jsonForm = {};
            if (response.statusCode == 200) {
                this.row1=response.data;
                //this.row1.push(response.data);
              //  this.table.rowDetail.toggleExpandRow(row);
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

}
