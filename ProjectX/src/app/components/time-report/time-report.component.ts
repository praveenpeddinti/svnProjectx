import { Component,Directive,ViewChild,ViewEncapsulation } from '@angular/core';
import { TimeReportService } from '../../services/time-report.service';
import { AjaxService } from '../../ajax/ajax.service';
import { Router } from '@angular/router';
import { GlobalVariable } from '../../config';
import { Http, Headers } from '@angular/http';
declare var jQuery:any;

@Component({
    selector: 'time-report-view',
    providers: [TimeReportService],
    templateUrl: 'time-report-component.html',
    styleUrls: ['./time-report.component.css']

})

export class TimeReportComponent{
    public FilterList=[];
     public selectedFilter=null;                  
    @ViewChild('myTable') table: any;
    rows = [];
    row1 = [];
    count: number = 0;
    offset: number = 0;
    limit: number = 2;
    sortvalue: string = "Id";
    sortorder: string = "desc";
    loading: boolean = false;
    last7daystimehours: number = 10;
    columns = [
                {
                    name: 'Date',
                    flexGrow: 1,
                    sortby: 'Date',
                    class: 'taskRstory'
                },
                {
                    name: 'Story / task Description',
                    flexGrow: 3,
                    sortby: 'Id',
                    class: 'titlecolumn'
                },
                {
                    name: 'Hours',
                    flexGrow: 1.5,
                    sortby: 'time',
                    class: ''
                },
                {
                    name: '',
                    flexGrow: 1.0,
                    sortby: '',
                    class: 'arrowClass'
                }
                
              ];

expanded: any = {};
    headers = new Headers({ 'Content-Type': 'application/x-www-form-urlencoded' });
    constructor(
        private _router: Router,
        private _service: TimeReportService, private http: Http) { console.log("in constructor"); }

    ngOnInit() {
 var thisObj = this;
   
 /*
  @params    :  projectId
  @Description: get bucket details
  */  
 
        /*
        @params    :  offset,limit,sortvalue,sortorder
        @Description: Default routing
        */
        this.page(this.offset, this.limit, this.sortvalue, this.sortorder);
       var thisObj = this;
       

 
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
    page(offset, limit, sortvalue, sortorder ) {
         this.rows =[];
        this._service.getAllStoryDetails(1, offset, limit, sortvalue, sortorder,(response) => {
           alert("--service after---"+response.data.length);
           console.log("responseoooo firsttime" +JSON.stringify(response.data))
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
                this.last7daystimehours=response.timehours;
            } else {
                console.log("fail---");
            }
        });
    }
    /*
    @When Clicking pages
    */
    onPage(event) {alert("-dfd-f-d-");
        this.offset = event.offset;
        this.limit = event.limit;
        alert("-dfd-f-d-"+event.offset+"----"+event.limit);
        this.page(this.offset, this.limit, this.sortvalue, this.sortorder);
    }

 
    /*
    @When Clicking Columns for Sorting
    */
    onSort(event) {
        this.sortvalue = event.sorts[0].prop;
        this.sortorder = event.sorts[0].dir;
        this.page(this.offset, this.limit, this.sortvalue, this.sortorder);
    }
  

    renderStoryForm() {
        this._router.navigate(['story-form']);
    }

    
    

    }

