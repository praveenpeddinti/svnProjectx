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
    public oldticketDesc: string='';
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
    fromDateString:string='';
    toDateString:string='';
    public fromDate:Date;
    public fromDateVal:Date;
    public toDate:Date;
    public toDateVal:Date;
    date4: string;
    public entryForm={};
    errors: string='';
    adderrors: string='';
    oldWorkLogHour: number = 0;
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
        jQuery(document).click(function(e){
                if(jQuery(e.target).closest(".deletebutton").length == 0 ) {
                    jQuery("#delete_timelog").css("display", "none");
                }

      });
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
                    this.shared.change(this._router.url,null,'Time Report','Other',thisObj.projectName);
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
        jQuery('#addHoursErrMsg').hide();
        jQuery('#editHoursErrMsg').hide();
        jQuery('#addTitleErrMsg').hide();
        jQuery('#editHoursErrMsg').hide();
        jQuery('#editTitleErrMsg').hide();
       
    }
    /*
    @params    :  offset,limit,sortvalue,sortorder
    @Description: StoryComponent/Task list Rendering
    */
    page(projectId,offset, limit, sortvalue, sortorder,fromDateVal,toDateVal ) {
        this._service.getTimeReportDetails(projectId, offset, limit, sortvalue, sortorder,fromDateVal,toDateVal,(response) => {
            let jsonForm = {};
            if (response.statusCode == 200) {
                this.rows =[];
                const start = offset * limit;
                const end = start + limit;
                let rows = [...this.rows];
                var data = response.data.data;
                 this.fromDateString = response.data.fromDate;
                 this.toDateString = response.data.toDate;
                for (let i = 0; i < data.length; i++) {
                    rows[i + start] = data[i];
                    jQuery('.datatable-row-wrapper').addClass('gggg');
                }
                this.rows = rows;
                this.count = response.totalCount;
                this.totaltimehours=response.data.totalHours;
            

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
       // alert("asdddddddddddd");
        this.extractFields=[];
        
    }
    searchTask(event) {
        console.log("##########"+event.query);
        var searchStrg=event.query;
        var modifiedString=event.query.replace("#","");
        var post_data={
        'projectId':this.projectId,
        'sortvalue':'Title',
        'searchString':modifiedString.trim()
        }
        let prepareSearchData = [];
        this.search_results=[];
           setTimeout(() => { 
                this._ajaxService.AjaxSubscribe("time-report/get-story-details-for-timelog",post_data,(result)=>
                {
                    
                    if(result.status !='401'){ 
                        var subTaskData = result.data;
                        for(let subTaskfield of subTaskData){
                            var currentData = '#'+subTaskfield.TicketId+' '+subTaskfield.Title;
                            prepareSearchData.push(currentData);
                        }
                        this.search_results=prepareSearchData;
                        jQuery(".ui-autocomplete-list-item").removeClass("ui-state-highlight");
                    }else{
                        if(!searchStrg.includes("#") && result.status =='401'){
                            let appendstring=['Please select valid story/task'];
                            this.search_results=appendstring;
                        }
                    
                    }
                    
                });
               },1000);
 
    }


    getSelectedValueForTask(event) {
        this.selectedValForTask=event;
    }

    getSelectedValueForDate(event) {
        this.selectedValForDate=event; 
    }
    editTimeLog(){
        this.selectedValForDate = null;
        var editableDate=  new Date(this.extractFields['readableDate']);
        if(this.extractFields['ticketDesc'].includes("#")){
            var post_data={
                'projectId':this.projectId,
                'slug':this.extractFields['Slug']['$oid'],
                'timelogHours':this.extractFields['Time'],
                 'description':this.extractFields['description'].trim(),
                'autocompleteTask':this.extractFields['ticketDesc'],
                'editableDate':this.extractFields['LogDate'],
                'calendardate':this.selectedValForDate,
                'oldWorkHours':this.oldWorkLogHour,
                'oldTicketDesc':this.oldticketDesc
            }
       
            if(this.extractFields['Time']!=0){
                    this._ajaxService.AjaxSubscribe("time-report/update-timelog",post_data,(response)=>
                    { 
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
            }else{
                    this.errorTimeLog('editHoursErrMsg','Invalid Time');
            }
        }else{
                    this.errorTimeLog('editTitleErrMsg','Please select valid story/task');
            }
    }
    
    addTimeLog(){ 
        var getTaskVal=this.entryForm['text']; 
        var thisObj = this;
        var finalDate= this.entryForm['dateVal'].toString();
        if(getTaskVal.includes("#")){
            var ticketSpilt = getTaskVal.split("#")[1];
            var ticket_Id = ticketSpilt.split(" ")[0];
            var timelogData={
                ticketId:ticket_Id,
                workHours:this.entryForm['hours'],
                addTimelogDesc:this.entryForm['description'].trim(),
                addTimelogTime:finalDate,
                projectId:this.projectId

            }
        
            if(this.entryForm['hours']!=0){
                this._ajaxService.AjaxSubscribe("time-report/add-timelog",timelogData,(response)=>
                { 
                    if (response.statusCode == 200) {
                        this.page(this.projectId,this.offset, this.limit, this.sortvalue, this.sortorder,this.fromDateVal,this.toDateVal);
                        jQuery('.timelogSuccessMsg').css('display','block');
                        jQuery('.timelogSuccessMsg').fadeOut( "slow" );
                        setTimeout(() => {
                            this.submitted=false;
                            jQuery('#addTimelogModel').modal('hide');
                        }, 500);
                    } else {
                    // this.errorMsg = 'dsasdasd';
                    }
                });
            }else{
                this.errorTimeLog('addHoursErrMsg','Invalid Time');
            }
    }else{
              this.errorTimeLog('addTitleErrMsg','Please select valid story/task');
        }
    }
     public  errorTimeLog(id,msg){
            jQuery("#"+id).html(msg);
            jQuery("#"+id).show();
            jQuery("#"+id).fadeOut(4000);
         
        }
 
    showdeleteDiv(delObj,slug,e){
         jQuery("#delete_timelog").css("display", "block");
        var delbutton_Height=25;
        var delbutton_Width=jQuery('#del_'+slug).width()/2;
        var delete_popup=jQuery('.delete_followersbgtable').width()/2;
        var offset=jQuery('#del_'+slug).offset();
        var offsetTop=offset.top+delbutton_Height;
        var offsetRight=offset.right-(delbutton_Width+delete_popup);
        jQuery('#delete_timelog').css({'top':offsetTop,'left':offsetRight,'min-width':"auto"});
       //jQuery('#delete_timelog').css('min-width',"auto");
        this.extractDelFields=delObj;
    }
  
    removeTimelog(){
        var input="_Input";
        var removeTicketSpilt = this.extractDelFields['ticketDesc'].split(".");
        var removeTicketId = removeTicketSpilt[0].split("#");
        var postObj={
            ticketId:removeTicketId[1],
            workHours:this.extractDelFields['Time'],
            slug:this.extractDelFields['Slug']['$oid'],
            projectId:this.projectId
        }
        this._ajaxService.AjaxSubscribe("time-report/remove-timelog",postObj,(response)=>
        { 
            jQuery('#delete_timelog').hide();
            this.page(this.projectId,this.offset, this.limit, this.sortvalue, this.sortorder,this.fromDateVal,this.toDateVal);
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
        }else{
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

    editTimeEntry(row){
           var copy = Object.assign({}, row);
          this.extractFields=copy;
          this.oldWorkLogHour=this.extractFields['Time'];
          this.oldticketDesc=this.extractFields['ticketDesc'];
    }
}