import {Component} from '@angular/core';
import {NavController, NavParams, AlertController, ViewController, LoadingController, PopoverController, Platform} from 'ionic-angular';
import {Storage} from "@ionic/storage";
import {PopoverPage} from '../popover/popover';
import {StoryDetailsPage} from '../story-details/story-details';
import {StoryCreatePage} from '../story-create/story-create';
import {Globalservice} from '../../providers/globalservice';
import {Constants} from '../../providers/constants';
/*
  Generated class for the Dashboard page.

  See http://ionicframework.com/docs/v2/components/#navigation for more info on
  Ionic pages and navigation.
*/
@Component({
    selector: 'page-dashboard',
    templateUrl: 'dashboard.html',

})
export class DashboardPage {
    public items: Array<any>;
    public start: number = 10;//no of items showing in each page by default 10
    public offsetIndex: number = 0;//offset Index default value is 0 while pulling the list screen down the value will be incremented

    /*
    ***
    arrayObject is used for saving the stories list and passing it to the html page
     ** 
    */
    public arrayObject: Array<{id: string, storyOrTask: string, subTasks: number, storyPointsHeading: string, title: string, assignTo: string, userThumbNail: string, priority: string, workflow: string, bucket: string, duedate: string}>;
    public moreDataLoaded: boolean = true;

    public loader = this.loadingController.create({content: "Loading..."});

    userName: any = '';
    /*
    *
        paramas are used while getting List Results from the webservice.
    *
    */

    paramas = {"projectId": 1, "offset": this.offsetIndex, "pagesize": this.start, "sortvalue": "Id", "sortorder": "desc","filterOption":null,"timeZone":"Asia/Kolkata", "userInfo": {}};

    constructor(public navCtrl: NavController,
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


        this.storage.get('userCredentials').then((value) => {

            platform.registerBackButtonAction(() => {
                // console.log("the views length " + this.navCtrl.getActive().index);
                /* checks if modal is open */
                if (StoryDetailsPage.optionsModal && StoryDetailsPage.optionsModal.index == 0) {
                    /* closes modal */
                    StoryDetailsPage.optionsModal.dismiss();
                    return;
                } else {
                    if (this.navCtrl.getActive().index == 0) {
                        this.platform.exitApp();
                    } else if (StoryDetailsPage.isMenuOpen == true) {
                        StoryDetailsPage.menuControler.close();
                    } else {
                        return this.navCtrl.pop();
                    }
                }
            });

            this.userName = value.username;
            this.paramas.userInfo = value;

            this.getAllStoriesList();
        });

    }

    ionViewDidLoad() {

        // this.getAllStoriesList();
    }


    /**
           pop up for logout
       
        */
    openPopover(myEvent) {
        let userCredentials = {username: this.userName};
        let popover = this.popoverCtrl.create(PopoverPage, userCredentials);
        console.log("User name is " + this.userName);
        popover.present({
            ev: myEvent
        });
    }
    doRefresh(refresher) {
        this.storage.get('userCredentials').then((value) => {
            this.userName = value.username;
            this.paramas.userInfo = value;

            this.getAllStoriesList();
            if (refresher != 0)
                refresher.complete();
        });
    };
    /**
        used for getting all the stories 
        author uday   
    
     */
    getAllStoriesList(): void {

        if (this.paramas.offset == 0) {
            this.paramas.offset = 0;
        }

        this.globalService.getStoriesList(this.urlConstants.getAllTicketDetails, this.paramas).subscribe(
            data => {
                if (data.statusCode == '200') {
                    if (this.paramas.offset == 0) {
                        this.loader.present();
                    }
                    this.items = data.data;
                    if (this.items.length == 0) {
                        this.moreDataLoaded = false;
                    }
                    for (let ticket = 0; ticket < this.items.length; ticket++) {
                        var _id = this.items[ticket][0].field_value;
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
                        var _subTasks = 0;
                        _subTasks = this.items[ticket][0].other_data.totalSubtasks;
                        var _title = this.items[ticket][1].field_value;
                        var _assignTo = this.items[ticket][2].field_value;
                        var _thumbNail = this.items[ticket][2].other_data;
                        var _priority = this.items[ticket][3].field_value;
                        var _workflow = this.items[ticket][4].field_value;
                        var _bucket = this.items[ticket][5].field_value;
                        var _dudate = this.items[ticket][6].field_value;

                        this.arrayObject.push({
                            id: _id, storyOrTask: _storyOrTask, subTasks: _subTasks, storyPointsHeading: _storyPointHeading, title: _title, assignTo: _assignTo, userThumbNail: _thumbNail, priority: _priority, workflow: _workflow, bucket: _bucket, duedate: _dudate
                        });
                    }

                    if (this.paramas.offset == 0) {
                        this.loader.dismiss().catch(() => console.log('ERROR CATCH: LoadingController dismiss'));
                    }
                    this.paramas.offset = (this.paramas.offset) + 1;

                }
            },
            error => {
                this.loader.dismiss().catch(() => console.log('ERROR CATCH: LoadingController dismiss'));
                console.log("the error " + JSON.stringify(error));
            },
            () => console.log('listing stories api call complete')
        );
    }

    /**
      used for click event on list
      author uday   
  
   */


    /**
          openDetails() is use ful for calling while swiping/clicking on the stories list item  
      
       */

    public openDetails(item): void {
        var clickedItemId = {"id": item.id};
        this.navCtrl.push(StoryDetailsPage, clickedItemId);
    }
    public btnCreateTask() {
        this.navCtrl.push(StoryCreatePage);
    }

    /**
         doInfinite(event) is called when the list is pulling down 
     
      */
    doInfinite(infiniteScroll) {
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
