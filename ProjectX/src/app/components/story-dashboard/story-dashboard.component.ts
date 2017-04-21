import { StoryComponent } from '../story/story-form.component';
import { Component, Directive,ViewChild,ViewEncapsulation } from '@angular/core';
import { StoryService } from '../../services/story.service';
import { Router } from '@angular/router';
import { GlobalVariable } from '../../config';
import { Http, Headers } from '@angular/http';
declare var jQuery:any;

@Component({
    selector: 'story-dashboard-view',
    providers: [StoryService],
    templateUrl: 'story-dashboard-component.html',
    styleUrls: ['./story-dashboard.component.css']

})

export class StoryDashboardComponent {
    public FilterList=[];
     public selectedFilter=null;                  
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
        private _service: StoryService, private http: Http) { console.log("in constructor"); }

    ngOnInit() {
 var thisObj = this;

 /*
  @params    :  projectId
  @Description: get bucket details
  */  
 this._service.getFilterOptions(1,(response) => { 
     for(let option of response.data){
         thisObj.FilterList.push({label:option.Label,value:{id:option.Id,type:option.Type,showChild:option.ShowChild}})
     }
 });
        /*
        @params    :  offset,limit,sortvalue,sortorder
        @Description: Default routing
        */
        this.page(this.offset, this.limit, this.sortvalue, this.sortorder,this.selectedFilter);
        var ScrollHeightDataTable=jQuery(".ngx-datatable").width() - 12;
        jQuery("#filterDropdown").css("paddingRight",10);
       jQuery(".ngx-datatable").css("width",ScrollHeightDataTable);
       var thisObj = this;
       
          jQuery( window ).resize(function() { 
if( thisObj.checkScrollBar() == true){
 jQuery("#filterDropdown").css("paddingRight",0);
}else{
 jQuery("#filterDropdown").css("paddingRight",12);
}
});
 
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
    page(offset, limit, sortvalue, sortorder,selectedOption ) {
         this.rows =[];
        this._service.getAllStoryDetails(1, offset, limit, sortvalue, sortorder,selectedOption,(response) => {
           
            let jsonForm = {};
            if (response.statusCode == 200) {
                const start = offset * limit;
                const end = start + limit;
                let rows = [...this.rows];
                for (let i = 0; i < response.data.length; i++) {
                    rows[i + start] = response.data[i];
                }
                this.rows = rows;
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
        this.page(this.offset, this.limit, this.sortvalue, this.sortorder,this.selectedFilter);
    }

 
    /*
    @When Clicking Columns for Sorting
    */
    onSort(event) {
        this.sortvalue = event.sorts[0].prop;
        this.sortorder = event.sorts[0].dir;
        this.page(this.offset, this.limit, this.sortvalue, this.sortorder,this.selectedFilter);
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
            this._router.navigate(['story-detail', event.row[0].field_value]);
        }
    }
     /* @Praveen P
        * This method is used story/task details when story/task id click component
        */
    showStoryDetail(event) {
            this._router.navigate(['story-detail', event[0].field_value]);
    }

    

    renderStoryForm() {
        this._router.navigate(['story-form']);
    }

    filterDashboard(){
        this.offset=0;
        if(this.selectedFilter.id==8){
           this.sortvalue='bucket' ;
           this.sortorder='asc'; 
        }
      this.page(this.offset, this.limit, this.sortvalue, this.sortorder,this.selectedFilter);  
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

