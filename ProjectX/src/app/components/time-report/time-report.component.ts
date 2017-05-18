import { Component,Directive,ViewChild,ViewEncapsulation } from '@angular/core';
import { TimeReportService } from '../../services/time-report.service';
import {CalendarModule,AutoComplete} from 'primeng/primeng'; 
import { AjaxService } from '../../ajax/ajax.service';
import { Router } from '@angular/router';
import { GlobalVariable } from '../../config';
import { Http, Headers } from '@angular/http';
import {AccordionModule,DropdownModule,SelectItem} from 'primeng/primeng';
//import {AutoCompleteModule} from 'primeng/primeng';
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
    private search_results:string[];
    private ticketdesc=[];
    private text:string;
    private dateVal = new Date();
    private selectedValForTask="";
    private selectedValForDate:Date;
    private calendarVal = new Date();
    @ViewChild('myTable') table: any;
    rows = [];
    row1 = [];
    count: number = 0;
    offset: number = 0;

    limit: number = 5;

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
        private _service: TimeReportService, private http: Http,private _ajaxService: AjaxService) { console.log("in constructor"); }

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
       

        this._service.getTimeReportDetails(1, offset, limit, sortvalue, sortorder,fromDateVal,toDateVal,(response) => {
           console.log("responseoooo firsttime" +JSON.stringify(response.data))

            let jsonForm = {};
            if (response.statusCode == 200) {
                  this.rows =[];
                const start = offset * limit;
                const end = start + limit;
                let rows = [...this.rows];
                for (let i = 0; i < response.data.length; i++) {
                    rows[i + start] = response.data[i];
                    jQuery('.datatable-row-wrapper').addClass('gggg');
                }
                this.rows = rows;
                //alert("rowsssssssss"+JSON.stringify(this.rows));
                this.count = response.totalCount;
                //this.ticketdesc=response.tick;
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
    onPage(event) {
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
    editTimeReport(){
          document.getElementById("editTimeReport").style.display='block';
        //  jQuery('#myModal').on('shown.bs.modal', function () {
        //     alert("herer");
        // jQuery('#myInput').focus();
 
        // }) 
        //  jQuery('#addTimelogModel').on('shown.bs.modal', function () {
          
        // jQuery('#myInput').focus();
 
        // }) 


    }
    searchTask(event)
    {
     var modifiedString=event.query.replace("#","");
        var post_data={
        'projectId':1,
        'sortvalue':'Title',
       'searchString':modifiedString
    }
     let prepareSearchData = [];
        this._ajaxService.AjaxSubscribe("time-report/get-story-details-for-timelog",post_data,(result)=>
         { 
           var subTaskData = result.data;
            for(let subTaskfield of subTaskData){
               var currentData = '#'+subTaskfield.TicketId+' '+subTaskfield.Title;
                 prepareSearchData.push(currentData);
            }
           // alert("prepareSearchData"+prepareSearchData);
           this.search_results=prepareSearchData;
         });
    }


    getSelectedValueForTask(event)
    {
        console.log("gettttttttttt"+event);
        this.selectedValForTask=event;
    }

    getSelectedValueForDate(event)
    {
      //  alert("ddddddddddddddd");
        console.log("gettttttttttt@@@@@@@@@@"+event);
        this.selectedValForDate=event; 
    }

    addTimeLog(){
        var getTaskVal=this.text;
        var date = (this.dateVal.getMonth() + 1) + '-' + this.dateVal.getDate() + '-' +  this.dateVal.getFullYear();
        date = date.replace(/(\b\d{1}\b)/g, "0$1");
        var finalDate=this.dateVal.toString();
        var timelogData={
              'projectId':1,
              'addTimelogTask':getTaskVal.split("#")[1],
              'addTimelogDesc':jQuery('#addTimelogDesc').val(),
              'addTimelogTime':jQuery('#addTimelogTime').val(),
              'addTimelogDate':finalDate,
        }
       // alert("timelogData"+JSON.stringify(timelogData));
        this._ajaxService.AjaxSubscribe("time-report/add-timelog",timelogData,(response)=>
            { 
              //  console.log("ssssssssssssss first"+JSON.stringify(this.rows));
                if (response.statusCode == 200) {
                    this.page(this.offset, this.limit, this.sortvalue, this.sortorder,this.fromDateVal,this.toDateVal);
               // console.log("onlyyyy" +JSON.stringify(response));
              // console.log("onlyyyy###" +JSON.stringify(response.data[0]));
                //  this.rows.push(response.data[0]);
                //   let rows = [...this.rows];
                // for (let i = 0; i < response.data.length; i++) {
                //     rows[i + 0] = response.data[i];
                // }
                // this.rows = rows;
              // console.log("@@@@@@@@@responseoooo final" +JSON.stringify(this.rows));
            jQuery('.timelogSuccessMsg').css('display','block');
            jQuery('.timelogSuccessMsg').fadeOut( "slow" );
                  setTimeout(() => {
                        jQuery('#addTimelogModel').modal('hide');
                        jQuery('#addTimelogModel').find("input,p").val('').end();
              }, 500);
              
            } else {
                console.log("fail---");
            }
            });
    
    }
    updateTimelog(slug,ticketDesc,timeLogDate){
   //  alert(JSON.stringify(ticketDesc));
     // alert(jQuery('#editTimelogTime'+'_'+slug).val());
      console.log("eeeeeeee"+this.selectedValForDate);
      var editableDate = new Date(timeLogDate);
                var post_data={
                    'projectId':1,
                    'slug':slug,
                    'timelogHours':jQuery('#editTimelogTime'+'_'+slug).val(),
                    'ticketDesc':ticketDesc,
                    'description':jQuery('#editableDesc'+'_'+slug).val(),
                    'autocompleteTask':this.selectedValForTask,
                    'editableDate':editableDate,
                    'calendardate':this.selectedValForDate
                   }
           this._ajaxService.AjaxSubscribe("time-report/update-timelog-for-edit",post_data,(response)=>
            { 
               //alert("onlyyyy" +JSON.stringify(response)); 
                   if (response.statusCode == 200) {
                        var input="_Input";
                      //  alert(slug+input);
                      //  jQuery('.'+slug+input).replaceWith(response.data[0]);
                //   this.rows.push(response.data[0]);
                //   console.log("onlyyyy###" +JSON.stringify(response.data[0]));
                //   let rows = [...this.rows];
                // for (let i = 0; i < response.data.length; i++) {
                //     rows[i + 0] = response.data[i];
                // }
                // this.rows = rows;
                //  console.log("@@@@@@@@@responseoooo final" +JSON.stringify(this.rows));
            } else {
                console.log("fail---");
            }
            });
    }
    showdeleteDiv(){
        jQuery("#delete_timelog").css("display", "block");
    }
    removeTimelog(ticketDesc,slug,timelogHours){
        var input="_Input";
      //  alert(slug+input);
         var postObj={
                    'projectId':1,
                    'slug':slug,
                    'ticketDesc':ticketDesc,
                    'timelogHours':timelogHours
                     }
          this._ajaxService.AjaxSubscribe("time-report/remove-timelog",postObj,(response)=>
            { 
              console.log("onlyyyy@@@@@@@@@@@@@@" +JSON.stringify(response)); 
              //jQuery('.'+slug+input).hide();
               this.page(this.offset, this.limit, this.sortvalue, this.sortorder,this.fromDateVal,this.toDateVal);
             // this.page(this.offset, this.limit, this.sortvalue, this.sortorder,this.fromDate,this.toDate);
            });            
    }
}
