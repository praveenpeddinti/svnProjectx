import { Component,Directive,ViewChild,ViewEncapsulation } from '@angular/core';
import { TimeReportService } from '../../services/time-report.service';
import {CalendarModule,AutoComplete} from 'primeng/primeng'; 
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
    limit: number = 10;
    sortvalue: string = "Id";
    sortorder: string = "desc";
    loading: boolean = false;
    last7daystimehours: number = 0;
    showdays:string='';
    public fromDate:Date;
    public fromDateVal:Date;
    public toDate:Date;
    public toDateVal:Date;
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
var date1 = new Date();//set current date to datepicker as min date
date1.setHours(0,0,0,0);
this.toDateVal = date1;
var lastWeekDate = new Date(this.toDateVal);
lastWeekDate.setDate(lastWeekDate.getDate()-7);
this.fromDateVal=lastWeekDate;
 /*
  @params    :  projectId
  @Description: get bucket details
  */  
 
        /*
        @params    :  offset,limit,sortvalue,sortorder
        @Description: Default routing
        */
this.page(this.offset, this.limit, this.sortvalue, this.sortorder,this.fromDateVal,this.toDateVal);
    var thisObj = this;
 
}

FromDate(event){
    this.fromDateVal=event;
}
ToDate(event){
    this.toDateVal=event;
}
DateRangeForm(){
    jQuery("#fromDate_error").hide();
    jQuery("#toDate_error").hide();
    this.fromDate=this.fromDateVal;
    this.toDate=this.toDateVal;
    if( (new Date(this.fromDateVal) > new Date(this.toDateVal))){
    jQuery("#toDate_error").show();
    }
    this.page(this.offset, this.limit, this.sortvalue, this.sortorder,this.fromDate,this.toDate);
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
    page(offset, limit, sortvalue, sortorder,fromDateVal,toDateVal ) {
         this.rows =[];
        this._service.getTimeReportDetails(1, offset, limit, sortvalue, sortorder,fromDateVal,toDateVal,(response) => {
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
                var millisecondsPerDay = 1000 * 60 * 60 * 24;
                var millisBetween = toDateVal.getTime() - fromDateVal.getTime();
                var days = millisBetween / millisecondsPerDay;
                // Round down.
                if(days<30){this.showdays = days+ " DAY(S)";}
                else if((days>=30) && (days<=365)){
                var totalmonth=( fromDateVal.getFullYear() * 12 + fromDateVal.getMonth() )-( toDateVal.getFullYear() * 12 + toDateVal.getMonth() );
                this.showdays = totalmonth+ " MONTH(S)";}else{
                var totalYears= fromDateVal.getFullYear() - toDateVal.getFullYear();
                this.showdays = totalYears+ " YEAR(S)";}
                
                
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
        this.page(this.offset, this.limit, this.sortvalue, this.sortorder,this.fromDateVal,this.toDateVal);
    }

 
    /*
    @When Clicking Columns for Sorting
    */
    onSort(event) {
        this.sortvalue = event.sorts[0].prop;
        this.sortorder = event.sorts[0].dir;
        this.page(this.offset, this.limit, this.sortvalue, this.sortorder,this.fromDateVal,this.toDateVal);
    }
  

    renderStoryForm() {
        this._router.navigate(['story-form']);
    }

    

    }

