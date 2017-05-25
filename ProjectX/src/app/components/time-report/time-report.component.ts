import { Component,Directive,ViewChild,ViewEncapsulation } from '@angular/core';
import { TimeReportService } from '../../services/time-report.service';
import {CalendarModule,AutoComplete} from 'primeng/primeng'; 
import { AjaxService } from '../../ajax/ajax.service';
import { Router, ActivatedRoute,NavigationExtras } from '@angular/router';
import { GlobalVariable } from '../../config';
import { Http, Headers } from '@angular/http';
import {SharedService} from '../../services/shared.service';
import {AuthGuard} from '../../services/auth-guard.service';
import { ProjectService } from '../../services/project.service';
import {AccordionModule,DropdownModule,SelectItem} from 'primeng/primeng';
import { NgForm } from '@angular/forms';



//import {AutoCompleteModule} from 'primeng/primeng';
declare var jQuery:any;

@Component({
    selector: 'time-report-view',
    providers: [TimeReportService,ProjectService],
    templateUrl: 'time-report-component.html',
    styleUrls: ['./time-report.component.css']

})

export class TimeReportComponent{
    public FilterList=[];
    public selectedFilter=null;  
    public projectName; 
    public projectId;                  
    private search_results:string[];
    private ticketdesc=[];
    private text:string;
    private dateVal = new Date();
    private selectedValForTask="";
    private selectedValForDate:Date;
    private calendarVal = new Date();
    public extractFields={};
    public extractDelFields={};
    public submitted=false;
    @ViewChild('myTable') table: any;
    rows = [];
    row1 = [];
    count: number = 0;
    offset: number = 0;

    limit: number = 5;

    sortvalue: string = "Id";
    sortorder: string = "desc";
    loading: boolean = false;
    totaltimehours: number = 0;
    showdays:string='';
    public fromDate:Date;
    public fromDateVal:Date;
    public toDate:Date;
    public toDateVal:Date;
    date4: string;
    public entryForm={};
   errors: string='';
   adderrors: string='';
     columns = [
                {
                    name: 'Date',
                    flexGrow: 1,
                    sortby: 'Date',
                    class: 'paddingleft10'
                },
                {
                    name: 'Story / Task Description',
                    flexGrow: 3,
                    sortby: 'Id',
                    class: 'titlecolumn paddingleft10'
                },
                {
                    name: 'Hours',
                    flexGrow: 1.5,
                    sortby: 'time ',
                    class: 'paddingleft10'
                },
                {
                    name: '',
                    flexGrow: 1.0,
                    sortby: '',
                    class: 'paddingleft10'
                }

              ];

expanded: any = {};
    headers = new Headers({ 'Content-Type': 'application/x-www-form-urlencoded' });
    constructor(
        private _router: Router,
            private _service: TimeReportService,private projectService:ProjectService, private _ajaxService: AjaxService,private http: Http, private route: ActivatedRoute,private shared:SharedService) { 
        console.log("in constructor"); 
        let PageParameters = {
                    offset: 0,
                    Sortvalue: "Id",
                    Sortorder:"desc"
                };
        }
    ngOnInit() {
var thisObj = this;
this.date4 = (this.calendarVal.getMonth() + 1) + '-' + this.calendarVal.getDate() + '-' + this.calendarVal.getFullYear(); 
var maxDate = new Date();//set current date to datepicker as min date
var date1 = new Date();//set current date to datepicker as min date
date1.setHours(0,0,0,0);
this.toDateVal = date1;
var lastWeekDate = new Date(this.toDateVal);
lastWeekDate.setDate(lastWeekDate.getDate()-7);
this.fromDateVal=lastWeekDate;
thisObj.route.queryParams.subscribe(
      params => 
      { 
      thisObj.route.params.subscribe(params => {
           thisObj.projectName=params['projectName'];
            thisObj.projectService.getProjectDetails(thisObj.projectName,(data)=>{
                if(data.statusCode!=404) {
                thisObj.projectId=data.data.PId;  
               
               this.page(thisObj.projectId,this.offset, this.limit, this.sortvalue, this.sortorder,this.fromDateVal,this.toDateVal);
    //var thisObj = this;
    this.shared.change(this._router.url,null,'TimeReport','Other');
                }
                });
                });
                });
                

         }

selectFromDate(event){
    this.fromDateVal=event;
}
selectToDate(event){
    this.toDateVal=event;
}
dateFilterSearch(){
    this.offset = 0;
    jQuery("#toDate_error").hide();
    this.fromDate=this.fromDateVal;
    this.toDate=this.toDateVal;
    if( (new Date(this.fromDateVal) > new Date(this.toDateVal))){
    jQuery("#toDate_error").show();
    }else{
    this.page(this.projectId,this.offset, this.limit, this.sortvalue, this.sortorder,this.fromDate,this.toDate);
    }
}
 clearDateTimeEntry(){ 
    this.entryForm={};
     this.entryForm={'dateVal':new Date()};
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
    page(projectId,offset, limit, sortvalue, sortorder,fromDateVal,toDateVal ) {
       
        this._service.getTimeReportDetails(projectId, offset, limit, sortvalue, sortorder,fromDateVal,toDateVal,(response) => {
         //  console.log("responseoooo firsttime" +JSON.stringify(response.data))

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
                this.totaltimehours=response.timehours;

                var millisecondsPerDay = 1000 * 60 * 60 * 24;
                var millisBetween = toDateVal.getTime() - fromDateVal.getTime();
                var days = millisBetween / millisecondsPerDay;
                // Round down.
                if(days<30){
                    this.showdays = days+ " DAY(S)";
                }else if((days>=30) && (days<=365)){
                    var totalmonth=( toDateVal.getFullYear() * 12 + toDateVal.getMonth() )-( fromDateVal.getFullYear() * 12 + fromDateVal.getMonth() );
                    this.showdays = totalmonth+ " MONTH(S)";}else{
                    var totalYears= toDateVal.getFullYear() - fromDateVal.getFullYear() ;
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
        this.page(this.projectId,this.offset, this.limit, this.sortvalue, this.sortorder,this.fromDateVal,this.toDateVal);
    }

 
    /*
    @When Clicking Columns for Sorting
    */
    onSort(event) {
        this.sortvalue = event.sorts[0].prop;
        this.sortorder = event.sorts[0].dir;
        this.page(this.projectId,this.offset, this.limit, this.sortvalue, this.sortorder,this.fromDateVal,this.toDateVal);
}


    renderStoryForm() {
        this._router.navigate(['story-form']);
    }
    editTimeReport(){
          document.getElementById("editTimeReport").style.display='block';

      }
    resetForm(){
        this.submitted= false;

    }
    searchTask(event)
    {
     var modifiedString=event.query.replace("#","");
        var post_data={
        'projectId':this.projectId,
        'sortvalue':'Title',
       'searchString':modifiedString
    }
   // alert("searchhhhhhhhhhh");
     let prepareSearchData = [];
     let appendstring=['Please select valid story/task'];
        this._ajaxService.AjaxSubscribe("time-report/get-story-details-for-timelog",post_data,(result)=>
         {
           //  alert("searchhhhhhhhhhh"+JSON.stringify(result.data)); 
              if(result.status !='401'){ 
                var subTaskData = result.data;
                    for(let subTaskfield of subTaskData){
                    var currentData = '#'+subTaskfield.TicketId+' '+subTaskfield.Title;
                        prepareSearchData.push(currentData);
                    }
                // alert("prepareSearchData"+prepareSearchData);
                this.search_results=prepareSearchData;
              }else{
                  this.search_results=appendstring;
              }
             // alert("eeeeeeeeeeeeeeee"+this.search_results);
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
    editTimeLog(){
      var editableDate=  new Date(this.extractFields['readableDate']);
                 var post_data={
                    'projectId':this.projectId,
                    'slug':this.extractFields['Slug']['$oid'],
                    'timelogHours':this.extractFields['Time'],
                    'ticketDesc':this.extractFields['ticketDesc'],
                    'description':this.extractFields['description'],
                    'autocompleteTask':this.selectedValForTask,
                    'editableDate':editableDate,
                    'calendardate':this.selectedValForDate,
                    }
                //   alert("@@@@@@@@"+JSON.stringify(post_data));
            this._ajaxService.AjaxSubscribe("time-report/update-timelog-for-edit",post_data,(response)=>
            { 
              // alert("onlyyyy" +JSON.stringify(response)); 
                   if (response.statusCode == 200) {
                      this.page(this.projectId,this.offset, this.limit, this.sortvalue, this.sortorder,this.fromDateVal,this.toDateVal);
                    jQuery('.timelogSuccessMsg').css('display','block');
                    jQuery('.timelogSuccessMsg').fadeOut( "slow" );;
                    setTimeout(() => {
                        jQuery('#editTimelogModel').modal('hide');
                     }, 500);
                } else {
                    console.log("fail---");
                }
            });
    }
    addTimeLog(){ 
        var getTaskVal=this.text;
        var thisObj = this;
        var date = (this.dateVal.getMonth() + 1) + '-' + this.dateVal.getDate() + '-' +  this.dateVal.getFullYear();
        date = date.replace(/(\b\d{1}\b)/g, "0$1");
        var finalDate=this.dateVal.toString();
        var ticketSpilt = getTaskVal.split("#")[1];
        var ticket_Id = ticketSpilt.split(" ")[0];
        var timelogData={
                ticketId:ticket_Id,
                workHours:this.entryForm['hours'],
                addTimelogDesc:this.entryForm['description'],
                addTimelogTime:finalDate,
                projectId:this.projectId
              
        }
//alert("ssssddddddddd"+JSON.stringify(timelogData));
            this._ajaxService.AjaxSubscribe("time-report/add-timelog",timelogData,(response)=>
                { 
               console.log("ssssssssssssss first"+JSON.stringify(timelogData));
                    if (response.statusCode == 200) {
                        this.page(this.projectId,this.offset, this.limit, this.sortvalue, this.sortorder,this.fromDateVal,this.toDateVal);
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
                            //jQuery('#addTimelogModel').find("input,textarea").val('').end();
                           
                }, 500);
               
                } else {
               // this.errorMsg = 'dsasdasd';
           
                }
                });
     
     
    }
     showdeleteDiv(delObj,slug){
             jQuery("#delete_timelog").css("display", "block");
             //alert("ddddddddddddddddddd");
             //alert(jQuery('#del_'+slug));
             var delbutton_Height=25;
             var delbutton_Width=jQuery('#del_'+slug).width()/2;
             var delete_popup=jQuery('.delete_followersbgtable').width()/2;
             var offset=jQuery('#del_'+slug).offset();
             var offsetTop=offset.top+delbutton_Height;
             var offsetRight=offset.right-(delbutton_Width+delete_popup);
         
            // alert("idddddddddddddd"+JSON.stringify(delObj));
            jQuery('#delete_timelog').css({'top':offsetTop,'left':offsetRight,'min-width':"auto"});
            
            //jQuery('#delete_timelog').css('min-width',"auto");
             this.extractDelFields=delObj;
    }
    removeTimelog(){
        var input="_Input";

         var postObj={
                    'projectId':this.projectId,
                    'slug':this.extractDelFields['Slug']['$oid'],
                    'ticketDesc':this.extractDelFields['ticketDesc'],
                    'timelogHours':this.extractDelFields['Time']
                     }
           this._ajaxService.AjaxSubscribe("time-report/remove-timelog",postObj,(response)=>
            { 
              console.log("onlyyyy@@@@@@@@@@@@@@" +JSON.stringify(response)); 
              //jQuery('.'+slug+input).hide();
               this.page(this.projectId,this.offset, this.limit, this.sortvalue, this.sortorder,this.fromDateVal,this.toDateVal);
             // this.page(this.offset, this.limit, this.sortvalue, this.sortorder,this.fromDate,this.toDate);
            });            
    }
    blockDeleteDiv(){
             jQuery("#delete_timelog").css("display", "none");

    }
     inputKeyDown(id){
     if(id==1){
          var initVal = jQuery("#addTimelogTime").val();
         var  outputVal = initVal.replace(/[^0-9\.]/g,'');       
        if (initVal != outputVal) {
            jQuery("#addTimelogTime").val(outputVal);
         }
        }
        else{
                 var initVal = jQuery("#editTimelogTime").val();
                var  outputVal = initVal.replace(/[^0-9\.]/g,'');       
                if (initVal != outputVal) {
                    jQuery("#editTimelogTime").val(outputVal);
                } 
        }

  }
    navigateToStoryDetail(ticketId){
        this._router.navigate(['project',this.projectName,ticketId,'details']);
     }

     editTimeEntry(Object){
          this.extractFields=Object;
     }
}
