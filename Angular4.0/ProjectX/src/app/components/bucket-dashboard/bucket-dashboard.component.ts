import { Component,Directive,NgZone,HostListener,ViewChild,OnInit } from '@angular/core';
import { BucketService } from '../../services/bucket.service';
import {CalendarModule,AutoComplete,CheckboxModule} from 'primeng/primeng'; 
import { AjaxService } from '../../ajax/ajax.service';
import { Router, ActivatedRoute,NavigationExtras } from '@angular/router';
import { GlobalVariable } from '../../config';
import { Http, Headers } from '@angular/http';
import {SharedService} from '../../services/shared.service';
import {AuthGuard} from '../../services/auth-guard.service';
import { ProjectService } from '../../services/project.service';
import {AccordionModule,DropdownModule,SelectItem} from 'primeng/primeng';
import {SummerNoteEditorService} from '../../services/summernote-editor.service';
import {RoundProgressModule} from 'angular-svg-round-progressbar';
import { NgForm } from '@angular/forms';
import { CreateBucketComponent } from '../../components/create-bucket/create-bucket.component';

declare var jQuery:any;
@Component({
  selector: 'app-bucket-dashboard',
  providers: [BucketService,ProjectService],
  templateUrl: './bucket-dashboard.component.html',
  styleUrls: ['./bucket-dashboard.component.css']
})
export class BucketDashboardComponent implements OnInit {

  private navBucketId;
  private projectName; 
  private projectId;
  private bucketDetails= [];
  private bucketChangedFilterToDisplay;
  private bucketChangedFilterOption;

  private dashboardData:any;
  private noMoreActivities:boolean = false;
  private noActivitiesFound:boolean = false;
  private page=0;
  private statusData = {};
  private stateData = {};
  private form={};
  private bucketPageError="";
@ViewChild(CreateBucketComponent) editBucketObj:CreateBucketComponent;
  // private dropClass="selectpicker show-menu-arrow sbox float_right";


  constructor(private _router: Router,
        private _service: BucketService,private projectService:ProjectService, private _ajaxService: AjaxService,private http: Http, private route: ActivatedRoute,private shared:SharedService,private editor:SummerNoteEditorService,private zone: NgZone) { }

  ngOnInit() {
    jQuery('body').removeClass('modal-open');
    var thisObj = this;
        thisObj.route.queryParams.subscribe(
        params => 
            { 
                // alert(params["BucketId"]);
                thisObj.navBucketId = params["BucketId"];
            thisObj.route.params.subscribe(params => {
                thisObj.projectName=params['projectName'];
                thisObj.projectService.getProjectDetails(thisObj.projectName,(data)=>{
                if(data.statusCode!=404) {
                    this.page = 0;
                //   alert("+++++++++");
                    thisObj.projectId=data.data.PId;
                    thisObj.load_bucketContents(1,'','');
                    thisObj.projectActivities(this.page);
                    //this.shared.change(this._router.url,null,'Time Report','Other',thisObj.projectName);
                }
                });
            });
        })

  }

  load_bucketContents(page,bucketStatus,scroll) {
        var postData={
        'projectId':this.projectId,
        'bucketId':this.navBucketId,
        'bucketStatus':bucketStatus,
        'page':page
        }
        // alert(JSON.stringify(postData));
        this._ajaxService.AjaxSubscribe("bucket/get-all-bucket-details",postData,(response) => {
        //   alert(JSON.stringify(response)+"---------getAllBucketDetails Data");
            if (response.statusCode == 200) {
            this.zone.run(() =>{ 
                this.bucketDetails= this.prepareBucketData(response.data,this.bucketDetails);
                this.form = this.prepareEditFromData();
                this.shared.change(this._router.url,null,this.bucketDetails[0].BucketName,'Other',this.projectName);
                
                this._service.getBucketTypeFilter(this.projectId,this.bucketDetails[0].BucketStatus,(response) => {
                    // alert(JSON.stringify(response)+"+++getBucketTypeFilter+++");
                    this.bucketChangedFilterToDisplay=this.prepareItemArray(response.data,false,'changebucket');
                    this.bucketChangedFilterOption=this.bucketChangedFilterToDisplay[0].filterValue;
                    });
                this.statusData = this.bucketDetails[0].chartDetails.statusCounts;
                    this.stateData = this.bucketDetails[0].chartDetails.stateCounts;
               
                 })
               
            } else {
                console.log("fail---");
            }
        });
    }
 parseDate(s) {
     if(s !=null){
        var months = {jan:0,feb:1,mar:2,apr:3,may:4,jun:5,
                        jul:6,aug:7,sep:8,oct:9,nov:10,dec:11};
        var p = s.split('-');
        return new Date(p[2], months[p[0].toLowerCase()], p[1]);
     }else{
         return new Date();
     }
}

prepareEditFromData(){
    var editBucketData = {};
    editBucketData['Id']=this.bucketDetails[0].BucketId;
    editBucketData['title']=this.bucketDetails[0].BucketName;
    editBucketData['description']=this.bucketDetails[0].Description;
    editBucketData['startDateVal']=this.parseDate(this.bucketDetails[0].StartDate);
    editBucketData['dueDateVal']=this.parseDate(this.bucketDetails[0].DueDate);
    editBucketData['setCurrent']=(this.bucketDetails[0].BucketStatusName == "Current")?true:false;
    editBucketData['selectedUserFilter']=this.bucketDetails[0].ResponsibleUser;

    return editBucketData;
}
    prepareBucketData(bucketData,prepareData){
         for(let bucketArray in bucketData){
        prepareData.push(bucketData[bucketArray]);
       }
       return prepareData;
    }

    public prepareItemArray(list:any,priority:boolean,status){
        var listItem=[];
        var listMainArray=[];
        if(list.length>0) { 
            if(status == "Member") {
                listItem.push({label:"Select Responsible ", value:"",priority:priority,type:status});
            }else if(status == "changebucket"){
                listItem.push({label:"Select an action", value:"none",priority:priority,type:status});
            }else{
                listItem.push({label:list[0].Name, value:"",priority:priority,type:status});
           }
           for(var i=0;list.length>i;i++){
              listItem.push({label:list[i].Name, value:list[i].Id,priority:priority,type:status});
           }
        }
        listMainArray.push({type:"",filterValue:listItem});
        return listMainArray;
    }

    filterBucketChange(event){
      var bucketId = this.navBucketId
    //         alert(JSON.stringify(event)+"+++++++++++++++++filterBucketChange");

    //   alert(event.value+"+++++++out if+++++++");
      if(event.value == 0){
          this.form = {};
          var editForm = this.prepareEditFromData();;
                    this.form = editForm;
                    this.bucketDetails[0].DropDownBucket = "none";
                    jQuery("#addBucketModel").modal();
                    
      }else if(event.value != "none"){
             
                // alert(event.value+"----in elif-------")
                var postData={
                        'projectId':this.projectId,
                        'bucketId':this.bucketDetails[0].BucketId,
                        'changeStatus':event.value
                        };
                        this._ajaxService.AjaxSubscribe("bucket/get-bucket-change-status",postData,(response) => {
                            // alert(JSON.stringify(response)+"<=====changeStatus=========>");
                            if(response.statusCode == 200){
                                if(response.message == "SUCCESS"){
                                    this.bucketChangedFilterToDisplay=this.prepareItemArray(response.data.dropList,false,'changebucket');
                                    this.bucketChangedFilterOption=this.bucketChangedFilterToDisplay[0].filterValue;
                                    this.bucketDetails[0].DropDownBucket = "none";
                                    this.bucketDetails[0].BucketStatusName = response.data.newBucketStatusName;
                                }else{
                                        if(event.value == 3){
                                            //  alert(response.message);
                                            this.showBucketPageError(response.message)
                                            this.bucketDetails[0].DropDownBucket = "none";
                                        }
                                }
                            // if(response.data.newBucketStatusName == "Completed"){
                            //     this.bucketDetails[0].topTicketStats = response.data.topTicketStats;
                            //     this.statusData={};
                            //     this.stateData={};
                            //     setTimeout(()=>{
                            //     this.statusData = response.data.statusCounts; 
                            //     this.stateData = response.data.stateCounts;
                            //     },200);
                            // }
                         }
                        });

      }

    }

    @HostListener('window:scroll', ['$event']) 
    projectActivityScroll(){
     var thisObj=this;
         if ((!this.noMoreActivities) && jQuery(window).scrollTop() == jQuery(document).height() - jQuery(window).height()) {
                       // thisObj.dashboardScroll=false;
                        thisObj.page++;
                       //     setTimeout(() => { 
                        thisObj.projectActivities(thisObj.page);
                       //  alert("@@@@@-------"+thisObj.page);;
                      //    },2000); 
                        
                    }
    }

     projectActivities(page){
     // alert("page"+page);
              var post_data={
               'page':page,
               'pageLength':10,
               "attributes":{'ProjectId': this.projectId,
                             "Miscellaneous": {
                                                "BucketId": this.navBucketId
                                               }
                            },
               };
                this._ajaxService.AjaxSubscribe("collaborator/get-all-activities-for-project-dashboard",post_data,(result)=>
                {   
                  
                    var thisObj=this;
                   // alert(JSON.stringify(result.data));
                    if (page == 0 ) { 
                     console.log("121212"); 
                         this.noMoreActivities = false;     
                            this.dashboardData = result.data;
                            console.log("Onload__activity__"+JSON.stringify(this.dashboardData.activities))
                            var curActLength = this.dashboardData.activities.length;
                              if(this.dashboardData.activities.length==0){
                                  this.noActivitiesFound=true;
                              }
                            
                    }else{
                      var curActLength = this.dashboardData.activities.length;
                        if (result.data.activities.length > 0) {
                          console.log("Total__Activity"+JSON.stringify(result.data.activities));
                          console.log(this.dashboardData.activities[curActLength - 1].activityDate +"==77777777777777777777===="+ result.data.activities[0].activityDate)
                          if (this.dashboardData.activities[curActLength - 1].activityDate == result.data.activities[0].activityDate) {
                            this.dashboardData.activities[curActLength - 1].activityData = this.dashboardData.activities[curActLength - 1].activityData.concat(result.data.activities[0].activityData)
                            console.log("After__Concat"+JSON.stringify(this.dashboardData.activities));
                            // // console.log("@@-44-"+JSON.stringify(this.dashboardData.activities[curActLength - 1].activityData));
                            result.data.activities .splice(0, 1);
                            this.dashboardData.activities=this.dashboardData.activities.concat(result.data.activities);
                            //alert("11");
                        } else {
                            this.dashboardData.activities=this.dashboardData.activities.concat(result.data.activities);
                          // alert("Final__out"+JSON.stringify(thisObj.dashboardData.activities));
                        }
                        } else {
                           this.noMoreActivities = true;
                        }
                      // alert("###--"+JSON.stringify(thisObj.dashboardData));
                    }
                  //  this.dashboardScroll=true;
               })
    }

    onBucketUpdate(updatedData){
        // alert(JSON.stringify(updatedData)+"+++onBucketUpdate++++");
        this.bucketDetails = [];
        this.bucketDetails= this.prepareBucketData(updatedData,this.bucketDetails);
         this.bucketDetails[0].DropDownBucket = "none";
        jQuery("#addBucketModel").modal('hide');
        // alert(this.bucketDetails[0].DropDownBucket);
    }


    showBucketPageError(msg){
        this.bucketPageError = msg;
            setTimeout(()=>{
               this.bucketPageError = "";
           },5000);
    }

}
