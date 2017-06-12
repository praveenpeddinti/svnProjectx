import {Component} from '@angular/core';
import {NavController, NavParams, AlertController, ViewController, LoadingController, PopoverController, ModalController, Platform} from 'ionic-angular';
import {Storage} from "@ionic/storage";
import {LogoutPage} from '../logout/logout';
import {StoryDetailsPage} from '../story-details/story-details';
import {StoryCreatePage} from '../story-create/story-create';
import {Globalservice} from '../../providers/globalservice';
import {Constants} from '../../providers/constants';
import { CustomModalPage } from '../custom-modal/custom-modal';
declare var jQuery: any;
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
    public fielterField: any={};
    public bucketField: Array<any>;
    public displayedClassColorValue = "";
    public filterList: Array<{ id: string, label: string, type: string, showChild: string}>;
    public items: Array<any>;
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
    filterParam = {"projectId": 1,"timeZone":"Asia/Kolkata", "userInfo": {}, "filterOption": {}};
    params = {"projectId": 1, "offset": this.offsetIndex, "pagesize": this.start, "sortvalue": "Id", "sortorder": "desc","filterOption":null,"timeZone":"Asia/Kolkata", "userInfo": {}};
    constructor(public navCtrl: NavController,
    public modalController: ModalController,
        public navParams: NavParams,
        public platform: Platform,
        public loadingController: LoadingController,
        public popoverCtrl: PopoverController,
        public alertController: AlertController,
        private storage: Storage,
        public viewCtrl: ViewController,
        private globalService: Globalservice,
        private urlConstants: Constants) {
            this.arrayObject = [];
            var userInfo=JSON.parse(localStorage.getItem("userCredentials"));
                this.userName = userInfo.username;
                this.params.userInfo = userInfo;
                this.filterParam.userInfo = userInfo;
                this.filterParam.filterOption = {id:"3",label:"My Assigned Stories/Task",showChild:"0",type:"general"};
                  this.getallfilterOptions();
                  this.getAllStoriesList();
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
    ionViewDidLoad() {}
    ionViewWillEnter() {}
    public openPopover(myEvent) {  
        let popover = this.popoverCtrl.create(LogoutPage);
        popover.present({
            ev: myEvent
        });
    }
    public doRefresh(refresher) {
        this.storage.get('userCredentials').then((value) => {
            this.userName = value.username;
            this.params.userInfo = value;
            this.getAllStoriesList();
            if (refresher != 0)
                refresher.complete();
        });
    };
    
    public getallfilterOptions(): void{
        if (this.params.offset == 0) {
            this.params.offset = 0;
        }
        this.globalService.getfilterOptions(this.urlConstants.filterOptions, this.filterParam).subscribe(
        result => {
            this.fielterField = JSON.stringify(result.data);
             console.log(JSON.stringify(result.data));
//              this.bucketField = result.data[1].filterValue;
//              this.filterList = [];
//              for(let filters = 0; filters < this.fielterField.length; filters++){
//                  console.log(this.fielterField[filters].length);
//                  console.log("the filterfield lenght is" + this.fielterField[filters].length);
//                  var _id = this.fielterField[filters].filterValue;
//                  console.log("the id is" + JSON.stringify(_id));
//                    var _label = this.fielterField[filters].label;;
//                    console.log("the label is" + _label);
//                    var _type = this.fielterField[filters].type;
//                    console.log("the type is" + _type);
//                    var _showChild = this.fielterField[filters].showChild;
//                    
//                     this.filterList.push({
//                        id: _id, label: _label, type: _type,showChild:_showChild
//                    });
//                     console.log("The console log is" +JSON.stringify(this.filterList));
//              }
        }
        );  
    }
    
    public getAllStoriesList(): void {
        if (this.params.offset == 0) {
            this.params.offset = 0;
        }
        this.globalService.getStoriesList(this.urlConstants.getAllTicketDetails, this.params).subscribe(
            data => {
                if (data.statusCode == '200') {
                    if (this.params.offset == 0) {
                        this.loader.present();
                    }
                    this.items = data.data;
                    if (this.items.length == 0) {
                        this.moreDataLoaded = false;
                    }
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
    
        public openOptionsModal(fieldDetails,index) {
            console.log("click optionmodel");
            
        let optionsModal = this.modalController.create(CustomModalPage, { activeField: fieldDetails, activatedFieldIndex: index, displayList: fieldDetails });
        console.log("the open option model index is" + index);
        console.log("the displayList are" + JSON.stringify(fieldDetails));
//            optionsModal.onDidDismiss((data) => {
//            if (data != null && Object.keys(data).length > 0) {
//                if (fieldDetails.type == "Filters" && fieldDetails.type == "Buckets") {
//                    //this.create.planlevel = data.Id;
//                    console.log("the ondismiss value filter and buckets");
//                } else if (fieldDetails.fieldName == "priority") {
//                  //  this.create.priority = data.Id;
//                  //  this.displayedClassColorValue = data.Name;
//                console.log("the else of ondismiss");
//                }
//                jQuery("#field_title_" + index + " div").text(data.Name);
//            }
//        });
        optionsModal.present();
        
        
    }
    
    public openDetails(item): void {
        var clickedItemId = {"id": item.id};
        this.navCtrl.push(StoryDetailsPage, clickedItemId);
    }
    public btnCreateTask() {
        this.navCtrl.push(StoryCreatePage);
    }
    public doInfinite(infiniteScroll) {
        setTimeout(() => {
            if (this.moreDataLoaded == true) {
                this.getAllStoriesList();
                infiniteScroll.complete();
            } else {
                infiniteScroll.complete();
            }
        }, 2000);

    }
}