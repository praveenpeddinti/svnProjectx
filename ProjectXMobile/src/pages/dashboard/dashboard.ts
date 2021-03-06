import {Component,ViewChild, NgZone} from '@angular/core';
import {NavController, NavParams,Content, AlertController, ViewController, LoadingController, PopoverController, ModalController, Platform} from 'ionic-angular';
import {Storage} from "@ionic/storage";
import {LogoutPage} from '../logout/logout';
import {StoryDetailsPage} from '../story-details/story-details';
import {GlobalSearch} from '../global-search/global-search';
import {StoryCreatePage} from '../story-create/story-create';
import {Globalservice} from '../../providers/globalservice';
import {Constants} from '../../providers/constants';
import { CustomModalPage } from '../custom-modal/custom-modal';
import {FilterModal} from '../filter-modal/filter-modal';
declare var jQuery: any;
declare var socket:any;
/*
  Generated class for the Dashboard page.

  See http://ionicframework.com/docs/v2/components/#navigation for more info on
  Ionic pages and navigation.
*/
   
@Component({
    selector: 'page-dashboard',
    templateUrl: 'dashboard.html'
})
export class DashboardPage {
    @ViewChild(Content) content: Content;
    ProjectId:string;
    public ngZone:any;
    SelectValue : any;
    public static optionsModal;
    public displayFieldvalue : any;
    public fielterField: Array<any>;
    public bucketField: Array<any>;
    public displayedClassColorValue = "";
    public filterList: Array<{ id: string, label: string, type: string, showChild: string}>;
    public items: Array<any>;
    public totalCount: any;
    public headerName : any;
    public projectName:string;
    public start: number = 10;//no of items showing in each page by default 10
    public offsetIndex: number = 0;//offset Index default value is 0 while pulling the list screen down the value will be incremented
    public arrayObject: Array<{storyOrTask: any, 
                                storyPointsHeading: string, 
                                id: string, 
                                subTasks: any, 
                                title: string, 
                                assignTo: string, 
                                priority: string, 
                                state: string,  
                                workflow: string, 
                                bucket: string, 
                                estimatedPoints: string, 
                                duedate: string, 
                                arrow: string}>;
    public moreDataLoaded: boolean = true;
    public loader = this.loadingController.create({content: "Loading..."});
    userName: any = '';
    searchParam = {};
    filterParam = {"projectId": 1,"timeZone":"Asia/Kolkata", "userInfo": {}, "filterOption": {}};
    params = {"projectId": "1", "offset": this.offsetIndex, "pagesize": this.start, "sortvalue": "Id", "sortorder": "desc","filterOption":{"label":"All My Stories/Task","id":"2","type":"general","showChild":"0"},"timeZone":"Asia/Kolkata", "userInfo": {}};
    constructor(public navCtrl: NavController,
    public modalController: ModalController,
        public navParams: NavParams,
        public platform: Platform,
         public popoverCtrl: PopoverController,
        public loadingController: LoadingController,
        public alertController: AlertController,
        private storage: Storage,
        public viewCtrl: ViewController,
        private globalService: Globalservice,
        private urlConstants: Constants) {
        
        this.ngZone = new NgZone({ enableLongStackTrace: false });
           this.projectName=this.navParams.get("ProjectName");
      //     this. filterParam.projectId=this.navParams.get("ProjectId");
      //     this.params.projectId=this.navParams.get("ProjectId");
           console.log("Project Name in Dashboard  "+this.navParams.get("ProjectName"));
           console.log("fileter params"+JSON.stringify(this.filterParam));
           console.log(" params"+JSON.stringify(this.params));
        this.headerName = "All My Stories/Task";
        this.SelectValue = "All My Stories/Task";
            this.arrayObject = [];
            localStorage.setItem('headerInfo', JSON.stringify({'title': this.projectName,'backButton':"",'logo':0,'backDash':1,'leftPannel':0,'FilterPannel':0,notification:1,profile:1,searchBar:1}));
            this.filterList = [];
            var userInfo=JSON.parse(localStorage.getItem("userCredentials"));
            this.userName = userInfo.username;
                this.params.userInfo = userInfo;
             //   this.filterParam.userInfo = userInfo;
              //  this.filterParam.filterOption = {id:"3",label:"My Assigned Stories/Task",showChild:"0",type:"general"};
                  this.getallfilterOptions();
                 // this.getAllStoriesList();
                  this.getallTickets();
                
                platform.registerBackButtonAction(() => {
                    if (StoryDetailsPage.optionsModal && StoryDetailsPage.optionsModal.index == 0) {
                        StoryDetailsPage.optionsModal.dismiss();
                        return;
                    } else {
                        if (this.navCtrl.getActive().index == 0) {
                            this.platform.exitApp();
                        } else if (StoryDetailsPage.isMenuOpen == true) {
                            StoryDetailsPage.menuControler.close();
                        } 
                        else {
                            return this.navCtrl.pop();
                            
                        }
                    }
                });
            
    }
    ionViewDidLoad() {
        console.log("ionviewdidload started");
        var thisObj= this;
          this.globalService.getTicketData().subscribe(value => {
            console.log("________________T" + JSON.stringify(value));
          var  ticketData={'ticketId':value.ticketId};
            thisObj.globalService.geUpdatedTicketDetails(this.urlConstants.getUpdatedTicketDetails,ticketData ).subscribe(
        result => {
          console.log("________________DEtails____" + JSON.stringify(result));
          this.items = result.data;
        }); 

        })
        
    }
    ionViewWillEnter() {
        console.log("ionViewWillEnter");
         this.arrayObject = [];
         this.params.offset = 0;
         this.getallTickets();
    }
    clickSearch(){
        this.navCtrl.setRoot(GlobalSearch);
    }
    public openPopover(myEvent) {  
        let popover = this.popoverCtrl.create(LogoutPage);
        popover.present({
            ev: myEvent
        });
    }
    public doRefresh(refresher) {
       var userInfo=JSON.parse(localStorage.getItem("userCredentials"));
         if(userInfo != null ||userInfo != undefined){
       this.userName = userInfo.username;
           // this.params.userInfo = value;
            this.arrayObject = [];
            this.params.offset = 0;
            this.getallTickets();
            if (refresher != 0)
                refresher.complete();
        }
    };
    
    public getallfilterOptions(): void{
        this.globalService.getfilterOptions(this.urlConstants.filterOptions, this.filterParam).subscribe(
        result => {
            this.fielterField = result.data;  
            console.log(JSON.stringify(this.fielterField)); 
        });  
    }
    public getallTickets():void{
        if (this.params.offset == 0) {
            this.params.offset = 0;
        }       
        console.log(JSON.stringify(this.params));
        this.globalService.getallStoriesList(this.urlConstants.getallStoryDetails, this.params).subscribe(
            data => {
                if (data.statusCode == '200') {
                    if (this.params.offset == 0) {
                        this.loader.present();
                    }
                    this.items = data.data;
                    this.totalCount = data.totalCount;
                    if (this.items.length == 0) {
                        this.moreDataLoaded = false;
                    }
                    console.log("this.items.length" + this.items.length)
                    for (let ticket = 0; ticket < this.items.length; ticket++) {
                        for(let ticketData = 0; ticketData< this.items[ticket].length; ticketData++){
                            var _storyOrTask;
                            var _storyPointHeading = "";
                            if (this.items[ticket][0].other_data.planlevel == 1) {
                                _storyOrTask = "Story";
                                _storyPointHeading = "Total story points";
                            }
                            else {
                                _storyOrTask = "Task";
                                _storyPointHeading = "Estimated points";
                            }
                            switch(this.items[ticket][ticketData].field_name){
                                case "Id":
                                    var _id = this.items[ticket][ticketData].field_value;
                                    var _subTasks = 0;
                                        _subTasks = this.items[ticket][0].other_data.totalSubtasks;
                                break;
                                case "Title":
                                    var _title = this.items[ticket][ticketData].field_value;
                                 break;
                                case "assignedto":
                                    var _assignTo = this.items[ticket][ticketData].field_value;
                                break;
                                case "priority":
                                    var _priority = this.items[ticket][ticketData].field_value;
                                break;
                                case "workflow":
                                    var _state = this.items[ticket][ticketData].other_data;                                
                                    var _workflow = this.items[ticket][ticketData].field_value;
                                break;
                                case "bucket":
                                    var _bucket = this.items[ticket][ticketData].field_value;
                                break;
                                case "estimatedpoints":
                                    var _estimatedPoints = this.items[ticket][ticketData].field_value;
                                break;
                                case "duedate":
                                    var _dudate = this.items[ticket][ticketData].field_value;
                                break;
                                case "arrow":
                                    var _arrow = this.items[ticket][ticketData].other_data;
                                break;
                                
                                default:
                                break;
                            }
                        }
                        
                     this.ngZone.run(() => {
                     this.arrayObject.push({
                            storyOrTask: _storyOrTask, 
                            storyPointsHeading: _storyPointHeading, 
                            id: _id, 
                            subTasks: _subTasks, 
                            title: _title, 
                            assignTo: _assignTo,  
                            priority: _priority, 
                            state: _state, 
                            workflow: _workflow, 
                            bucket: _bucket, 
                            estimatedPoints: _estimatedPoints, 
                            duedate: _dudate, 
                            arrow: _arrow
                        });
                 });
                    }
                    if (this.params.offset == 0) {
                        this.loader.dismiss().catch(() => console.log('ERROR CATCH: LoadingController dismiss'));
                    }
                    this.params.offset = (this.params.offset) + 1;
                }
            },
            error => {
                this.loader.dismiss().catch(() => console.log('ERROR CATCH: LoadingController dismiss'));
                console.log("the error " + JSON.stringify(error));
            },
            () => console.log('listing stories api call complete')
        );
    }
//    public getAllStoriesList(): void {
//        if (this.params.offset == 0) {
//            this.params.offset = 0;
//        }
//        console.log(JSON.stringify(this.params));
//        this.globalService.getStoriesList(this.urlConstants.getAllTicketDetails, this.params).subscribe(
//            data => {
//              //  console.log(JSON.stringify(data));
//                if (data.statusCode == '200') {
//                    if (this.params.offset == 0) {
//                        this.loader.present();
//                    }
//                    this.items = data.data;
//                     console.log(JSON.stringify(data.data));
//                    this.totalCount = data.totalCount;
//                    if (this.items.length == 0) {
//                        this.moreDataLoaded = false;
//                    }
//                    for (let ticket = 0; ticket < this.items.length; ticket++) {
//                        for(let ticketData = 0; ticketData< this.items[ticket].length; ticketData++){
//                            var _storyOrTask;
//                            var _storyPointHeading = "";
//                            if (this.items[ticket][0].other_data.planlevel == 1) {
//                                _storyOrTask = "Story";
//                                _storyPointHeading = "Total story points";
//                            }
//                            else {
//                                _storyOrTask = "Task";
//                                _storyPointHeading = "Estimated points";
//                            }
//                            switch(this.items[ticket][ticketData].field_name){
//                                case "Id":
//                                    var _id = this.items[ticket][ticketData].field_value;
//                                    var _subTasks = 0;
//                                        _subTasks = this.items[ticket][0].other_data.totalSubtasks;
//                                break;
//                                case "Title":
//                                    var _title = this.items[ticket][ticketData].field_value;
//                                 break;
//                                case "assignedto":
//                                    var _assignTo = this.items[ticket][ticketData].field_value;
//                                break;
//                                case "priority":
//                                    var _priority = this.items[ticket][ticketData].field_value;
//                                break;
//                                case "workflow":
//                                    var _state = this.items[ticket][ticketData].other_data;                                
//                                    var _workflow = this.items[ticket][ticketData].field_value;
//                                break;
//                                case "bucket":
//                                    var _bucket = this.items[ticket][ticketData].field_value;
//                                break;
//                                case "estimatedpoints":
//                                    var _estimatedPoints = this.items[ticket][ticketData].field_value;
//                                break;
//                                case "duedate":
//                                    var _dudate = this.items[ticket][ticketData].field_value;
//                                break;
//                                case "arrow":
//                                    var _arrow = this.items[ticket][ticketData].other_data;
//                                break;
//                                
//                                default:
//                                break;
//                            }
//                        }
//                        this.arrayObject.push({
//                            storyOrTask: _storyOrTask, 
//                            storyPointsHeading: _storyPointHeading, 
//                            id: _id, 
//                            subTasks: _subTasks, 
//                            title: _title, 
//                            assignTo: _assignTo,  
//                            priority: _priority, 
//                            state: _state, 
//                            workflow: _workflow, 
//                            bucket: _bucket, 
//                            estimatedPoints: _estimatedPoints, 
//                            duedate: _dudate, 
//                            arrow: _arrow
//                        });
//                    }
//                    if (this.params.offset == 0) {
//                        this.loader.dismiss().catch(() => console.log('ERROR CATCH: LoadingController dismiss'));
//                    }
//                    this.params.offset = (this.params.offset) + 1;
//                }
//            },
//            error => {
//                this.loader.dismiss().catch(() => console.log('ERROR CATCH: LoadingController dismiss'));
//                console.log("the error " + JSON.stringify(error));
//            },
//            () => console.log('listing stories api call complete')
//        );
//    }
    
        public openOptionsModal(fieldDetails,index) {   
            console.log("fieldDetails is" + JSON.stringify(fieldDetails));
            console.log("the selectvalue from openoption1 is" + this.SelectValue);
            DashboardPage.optionsModal = this.modalController.create(FilterModal, {activeField: fieldDetails, activatedFieldIndex: this.SelectValue, displayList:this.fielterField });
            console.log("the selectvalue from openoption2 is" + this.SelectValue);
         DashboardPage.optionsModal.onDidDismiss((data) => {
             this.loader.present();
             console.log("open option model data are" + JSON.stringify(data));
                         if (data != null && (data.label != data.previousValue)) {
                             this.SelectValue = data.label;
                             console.log("selectvalue" + this.SelectValue);
                             this.headerName = data.label;
                             console.log("header name" + this.headerName);
                             //this.globalService.setTitle( this.headerName);
                            // jQuery("#header_title").find(".toolbar-title").html(this.headerName);
                             this.params.filterOption = data.value;
                             this.params.sortvalue = data.value.type;
                             this.params.offset = 0;
                             this.arrayObject =[]; 
                             this.content.resize();
                             console.log("filtersOption set to"+JSON.stringify(this.params.filterOption));
                               //this.viewCtrl.dismiss();
                                 setTimeout(()=> {
                                     this.getallTickets();
                             },300);
                             jQuery("#field_title_" + index + " label").text(data.label);
                        }
                    });
         DashboardPage.optionsModal.present();
    }
    
    public openDetails(item): void {
        var clickedItemId = {"id": item.id,"storyOrTask":item.storyOrTask};
        this.navCtrl.push(StoryDetailsPage, clickedItemId);
    }
    public btnCreateTask() {
        this.navCtrl.push(StoryCreatePage);
    }
    public doInfinite(infiniteScroll) {
        setTimeout(() => {
            if (this.moreDataLoaded == true) {
                this.getallTickets();
                infiniteScroll.complete();
            } else {
                infiniteScroll.complete();
            }
        }, 2000);

    }       
    }
