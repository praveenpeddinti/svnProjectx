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
import {Location} from '@angular/common';

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
    limit: number = 10;
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
    public hourErr=false;
    public titleErrMsg=false;
    public addPopUp=true;
   public editPopUp=true;
   public deletePopUp=true;
   public editSuccessMsg:any;
   public addSuccessMsg:any;
   public teamMembers:SelectItem[];
   public selectedMember:string;
   public teamCount=0;
   public Members:string[]=[];
    columns = [
                {
                    name: 'Date',
                    flexGrow: 1,
                    sortby: 'Date',
                    class: 'paddingleft10'
                },
                { //new column added by Ryan
                    name:'User',
                    flexGrow: 2,
                    sortby: 'Id',
                    class: 'paddingleft10'
                },
                {
                    name: 'Story / Task',
                    flexGrow: 3,
                    sortby: 'Id',
                    class: 'titlecolumn paddingleft10'
                },
                {
                    name: 'Description',
                    flexGrow: 3,
                    sortby: 'Id',
                    class: 'paddingleft10'
                },
                {
                    name: 'Hours',
                    flexGrow: 1,
                    sortby: 'time ',
                    class: 'paddingleft10'
                },
                {
                    name: '',
                    flexGrow: 1.5,
                    sortby: '',
                    class: 'text-center'
                }

              ];

    expanded: any = {};
    headers = new Headers({ 'Content-Type': 'application/x-www-form-urlencoded' });
    pageNo:number=0; //added by Ryan
    
    constructor(
        private _router: Router,
        private _service: TimeReportService,private projectService:ProjectService, private _ajaxService: AjaxService,private http: Http, private route: ActivatedRoute,private shared:SharedService,private timeLocation:Location) { 
        let PageParameters = {
                offset: 0,
                Sortvalue: "Id",
                Sortorder:"desc"
            };
        }
    ngOnInit() {
        var thisObj=this;
         this.deletePopUp=false;
   
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
                this.shared.change(this._router.url,true,'Time Report','Other',thisObj.projectName);
                thisObj.projectService.getProjectDetails(thisObj.projectName,(data)=>{
                if(data.statusCode!=404) {
                    thisObj.projectId=data.data.PId;  
                    this.page(thisObj.projectId,this.offset, this.limit, this.sortvalue, this.sortorder,this.fromDateVal,this.toDateVal,this.Members);
                    //var thisObj = this;
                    this.getTeamMembers();
                }
                });
            });
 
        });
         
    }

    ngAfterViewInit(){ 
     }
/**
 * For start date
 */
    selectFromDate(event){
        this.fromDateVal=event;
    }
    /**
 * For end date
 */
    selectToDate(event){
        this.toDateVal=event;
    }
    /**
 * Filtering date based on requirement
 */
    dateFilterSearch(){
        this.offset = 0;
        jQuery("#toDate_error").hide();
        this.fromDate=this.fromDateVal;
        this.toDate=this.toDateVal;
        if( (new Date(this.fromDateVal) > new Date(this.toDateVal))){
        jQuery("#toDate_error").show();
        }else{
        this.page(this.projectId,this.offset, this.limit, this.sortvalue, this.sortorder,this.fromDate,this.toDate,this.Members);
        }
    }
    /**
     * Removing the date which selected previously
     */
    clearDateTimeEntry(){
        this.addPopUp=true;
        this.entryForm['text']='';
        this.entryForm={};
        this.entryForm={'dateVal':new Date()};
        this.hourErr=false;
        this.titleErrMsg=false;
        this.addSuccessMsg=false;
       
    }
    /*
    @params    :  offset,limit,sortvalue,sortorder
    @Description: StoryComponent/Task list Rendering
    */
    page(projectId,offset, limit, sortvalue, sortorder,fromDateVal,toDateVal,members ) {
        this._service.getTimeReportDetails(projectId, offset, limit, sortvalue, sortorder,fromDateVal,toDateVal,members,(response) => {
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
                this.rows = data;
                this.count = response.totalCount;
                if(response.data.totalHours.length==0){
                    this.totaltimehours=0.0;
                }else{
                    this.totaltimehours=response.data.totalHours;
                }
               
                this.pageNo=offset+1;//added by Ryan
                this.sortorder=sortorder;//added by Ryan
                this.sortvalue=sortvalue;
                this.offset=this.pageNo-1;//added by Ryan
                var pagination_count={page:'Time Report',count:this.pageNo,sort:this.sortorder,col:this.sortvalue,fromDate:this.fromDateString,toDate:this.toDateString};
                var pagination=JSON.stringify(pagination_count);
                localStorage.setItem('timeReportState',pagination);//added by Ryan
                var url='/project/'+this.projectName+'/time-report?page='+this.pageNo+'&sort='+sortorder+'&col='+sortvalue+'&fromDate='+this.fromDateString+'&toDate='+this.toDateString;
                this.timeLocation.go(url);
            } else {
            }
        });
    }
    /*
    @When Clicking pages
    */
    onPage(event) {
        this.offset = event.offset;
        this.limit = event.limit;
        this.page(this.projectId,this.offset, this.limit, this.sortvalue, this.sortorder,this.fromDateVal,this.toDateVal,this.Members);
    }

    /*
    @When Clicking Columns for Sorting
    */
    onSort(event) {
        this.sortvalue = event.sorts[0].prop;
        this.sortorder = event.sorts[0].dir;
        this.page(this.projectId,this.offset, this.limit, this.sortvalue, this.sortorder,this.fromDateVal,this.toDateVal,this.Members);
    }
/**
 * Naviagating to story form page.
 */
    renderStoryForm() {
        this._router.navigate(['story-form']);
    }
   
   /**
 * Form Reset
  */
    resetForm(){
        this.extractFields=[];
        this.hourErr=false;
        this.titleErrMsg=false;
    }
 /**
 * When changing the text
 */
    textChanged(event) {
        if(event=='' || typeof event == 'undefined'){
            this.titleErrMsg=false;
        }
     } 
     /**
      * Fetching the story details based on the timelog
      */
    searchTask(event) {
         this.titleErrMsg=false;
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
                    
                        if(result.data.length !=0){ 
                        var subTaskData = result.data;
                        for(let subTaskfield of subTaskData){
                            var currentData = '#'+subTaskfield.TicketId+' '+subTaskfield.Title;
                            if (currentData.length > 55){
                              var currentData= currentData.substring(0,55) + '...';
                            }
                            prepareSearchData.push(currentData);
                        }
                        this.search_results=prepareSearchData;
                        jQuery(".ui-autocomplete-list-item").removeClass("ui-state-highlight");
                    }else{
                            if(!searchStrg.includes("#")){
                            let appendstring=['Please select valid story/task'];
                            this.search_results=appendstring;
                 
                        }
                    
                    }
                    
                });
               },700);
       
    }


    getSelectedValueForTask(event) {
        this.selectedValForTask=event;
     
    }

    getSelectedValueForDate(event) {
        this.selectedValForDate=event; 
    }
    /**
     * Functionality to edit the timelog
     */
    editTimeLog(){
        this.selectedValForDate = null;
        this.hourErr=false;
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
                            this.page(this.projectId,this.offset, this.limit, this.sortvalue, this.sortorder,this.fromDateVal,this.toDateVal,this.Members);
                             this.editSuccessMsg=true;
                            setTimeout(() => {
                                this.editPopUp=false;
                           }, 500);
                          this.editPopUp=true;
                         } else {
                        }
                    });
            }else{
                this.hourErr=true;
              }
        }else{
            this.titleErrMsg=true;
     }
    }
    /**
     * Functionality to add the timelog for the story or task
     */
    
    addTimeLog(){
        var getTaskVal=this.entryForm['text']; 
        var thisObj = this;
        var finalDate= this.entryForm['dateVal'].toString();
         this.hourErr=false;
         this.titleErrMsg=false;
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
        
            if(this.entryForm['hours']!='0'){
                this.hourErr=false;
                this._ajaxService.AjaxSubscribe("time-report/add-timelog",timelogData,(response)=>
                { 
                    if (response.statusCode == 200) {
                        this.page(this.projectId,this.offset, this.limit, this.sortvalue, this.sortorder,this.fromDateVal,this.toDateVal,this.Members);
                        this.addSuccessMsg=true;
                        setTimeout(() => {
                            this.submitted=false;
                            this.addPopUp=false;
                       }, 500);
                        this.addPopUp=true;
                    } else {
                  
                    }
                });
            }else{
                 this.hourErr=true;
            }
    }else{
        this.titleErrMsg=true;
       }
    }
 /**
  * Displaying delete optin
  */
    showdeleteDiv(delObj,slug,e){
        this.deletePopUp=true;
        var delbutton_Height=25;
        var delbutton_Width=jQuery('#del_'+slug).width()/2;
        var delete_popup=jQuery('.delete_followersbgtable').width()/2;
        var offset=jQuery('#del_'+slug).offset();
        var offsetTop=offset.top+delbutton_Height+2;
        var offsetRight=offset.left-(delbutton_Width+delete_popup)+16;
        jQuery('#delete_timelog').css({'top':offsetTop,'left':offsetRight,'min-width':"auto"});
     
        this.extractDelFields=delObj;
    }
  /**
   * Removing time log
   */
    removeTimelog(){
        var input="_Input";
        var removeTicketSpilt = this.extractDelFields['ticketDesc'].split(" ");
        var removeTicketId = removeTicketSpilt[0].split("#");
        var postObj={
            ticketId:removeTicketId[1],
            workHours:this.extractDelFields['Time'],
            slug:this.extractDelFields['Slug']['$oid'],
            projectId:this.projectId
        }
            this._ajaxService.AjaxSubscribe("time-report/remove-timelog",postObj,(response)=>
        { 
           setTimeout(() => {
                this.deletePopUp=false;
                        }, 250);

          this.deletePopUp=true;
            this.page(this.projectId,this.offset, this.limit, this.sortvalue, this.sortorder,this.fromDateVal,this.toDateVal,this.Members);
        });            
    }
/**
 * Hiding the delete popup
 */     
    blockDeleteDiv(){
        this.deletePopUp=false;
    }
    
    inputKeyDown(id){
        this.hourErr=false;
        if(id==1){
            var initVal =this.entryForm['hours'];
            var  outputVal = initVal.replace(/[^0-9\.]/g,'');       
            if (initVal != outputVal) {
                jQuery("#addTimelogTime").val(outputVal);
             }
        }else{
            var initVal = this.extractFields['Time'];
            var  outputVal = initVal.replace(/[^0-9\.]/g,'');       
            if (initVal != outputVal) {
                jQuery("#editTimelogTime").val(outputVal);
            } 
        }
    }
    /**
     * Navigating to story detail page
     */
    navigateToStoryDetail(ticketId){
        this._router.navigate(['project',this.projectName,ticketId,'details']);
     }
/**
 * @description Giving edit functionolity fot timelog
 */
    editTimeEntry(row){
          this.editPopUp=true;
          this.editSuccessMsg=false;
          var copy = Object.assign({}, row);
          this.extractFields=copy;
          this.oldWorkLogHour=this.extractFields['Time'];
          this.oldticketDesc=this.extractFields['ticketDesc'];
    }
    
    fullTextshow(a){jQuery("#fullText_"+a).css("display", "block");
    
    } 

    getTeamMembers()
    {
        var post_data={'projectId':this.projectId};
        this._ajaxService.AjaxSubscribe("collaborator/get-team-members",post_data,(result)=>
        {
            this.teamMembers=[];
            this.teamCount=result.totalCount;
            if(result.data==null){
                /*Hide Dropdown for Team Members */
            }else{
                for(let member of result.data){
                    this.teamMembers.push({label:member.Name,value:{Name:member.Name,Id:member.Id,Profile:member.ProfilePic}});
                }
            }
        });
    }

    getSelectedMembers(event){
       var usersList=event.value;
       var ids=[];
       for(let user of usersList){
        ids.push(user.Id);
       }
       
      this.Members=ids;

    }

    clearForm(){
       this.entryForm['text']=' ';
    }
}