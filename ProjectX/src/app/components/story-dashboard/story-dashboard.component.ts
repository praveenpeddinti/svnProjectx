import { StoryComponent } from '../story/story-form.component';
import { Component, Directive,ViewChild,ViewEncapsulation } from '@angular/core';
import { StoryService } from '../../services/story.service';
import { Router,ActivatedRoute } from '@angular/router';
import { GlobalVariable } from '../../config';
import { Http, Headers } from '@angular/http';
import {SharedService} from '../../services/shared.service';
import { ProjectService } from '../../services/project.service';

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
     public projectId;               
    @ViewChild('myTable') table: any;
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

    constructor(

        private _router: Router,
        private _service: StoryService,private projectService:ProjectService, private http: Http, private route: ActivatedRoute,private shared:SharedService) { console.log("in constructor"); }
       
    ngOnInit() {
 var thisObj = this;
  thisObj.route.queryParams.subscribe(
      params => 
      { 
      thisObj.route.params.subscribe(params => {
           thisObj.projectName=params['projectName'];
           this.shared.change(this._router.url,thisObj.projectName,'Dashboard','',thisObj.projectName); //added by Ryan for breadcrumb purpose
            thisObj.projectService.getProjectDetails(thisObj.projectName,(data)=>{
                if(data.statusCode!=404) {
                thisObj.projectId=data.data.PId;  
                /*
                @params    :  projectId
                @Description: get bucket details
                */  
                thisObj._service.getFilterOptions(thisObj.projectId,(response) => { 
                thisObj.FilterOption=response.data[0].filterValue;
                thisObj.FilterOptionToDisplay=response.data;
                });

   /*
        @params    :  offset,limit,sortvalue,sortorder
        @Description: Default routing
        */
        thisObj.page(thisObj.projectId,thisObj.offset, thisObj.limit, thisObj.sortvalue, thisObj.sortorder,thisObj.selectedFilter);
        var ScrollHeightDataTable=jQuery(".ngx-datatable").width() - 12;
        jQuery("#filterDropdown").css("paddingRight",10);
       jQuery(".ngx-datatable").css("width",ScrollHeightDataTable);
      // var thisObj = this;
       
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
    // ngAfterViewInit()
    // {
    //  jQuery('#filter_dropdown_label #filter_dropdown').find(' > li.general:eq(0)').before('<label>Filter</label>');
    //  jQuery('#filter_dropdown_label #filter_dropdown').find(' > li.bucket:eq(0)').before('<label>Bucket</label>');
    // }
        /*
        @params    :  offset,limit,sortvalue,sortorder
        @Description: StoryComponent/Task list Rendering
        */
        
    page(projectId,offset, limit, sortvalue, sortorder,selectedOption ) {
         this.rows =[];
        this._service.getAllStoryDetails(projectId, offset, limit, sortvalue, sortorder,selectedOption,(response) => {
           
            let jsonForm = {};
            if (response.statusCode == 200) {
                const start = offset * limit;
                const end = start + limit;
                let rows = [...this.rows];
                for (let i = 0; i < response.data.length; i++) {
                    rows[i + start] = response.data[i];
                }
                this.rows = rows;
                //console.log("ROWS___"+JSON.stringify(rows));
                this.count = response.totalCount;
            } else {
                console.log("fail---");
            }
        });
    }
    /*
    @When Clicking pages
    */
    onPage(event) {
        this.offset = event.offset;
        this.limit = event.limit;
        this.page(this.projectId,this.offset, this.limit, this.sortvalue, this.sortorder,this.selectedFilter);
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
    if(row.$$expanded!=1){jQuery("#collapsediv").click();}
    this.row1=[];
    console.log('Toggled Expand Row!', row[0].field_value);
    this._service.getSubTasksDetails(1, row[0].field_value, (response) => {
                   let jsonForm = {};
            if (response.statusCode == 200) {
                this.row1=response.data;
                //this.row1.push(response.data);
                this.table.rowDetail.toggleExpandRow(row);
                //console.log('Toggled Expand Row!', this.row1);
            } else {
                console.log("fail---");
            }
        });
       
    //console.log('Toggled Expand Row2!', row);

   
   
    
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
        if(this.selectedFilter.id==8){
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

    }

