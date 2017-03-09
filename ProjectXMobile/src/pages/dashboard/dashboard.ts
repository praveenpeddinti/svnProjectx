import { Component } from '@angular/core';
import { NavController, NavParams, AlertController, ViewController, LoadingController, PopoverController } from 'ionic-angular';
import { Storage } from "@ionic/storage";
import { PopoverPage } from '../popover/popover';
import { StoryDetailsPage } from '../story-details/story-details';
import { Globalservice } from '../../providers/globalservice';
import { Constants } from '../../providers/constants';
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
    public arrayObject: Array<{ id: string, title: string, assignTo: string, priority: string, workflow: string, bucket: string, duedate: string }>;
    public moreDataLoaded: boolean = true;

    userName: any = '';
    // userPassword: string = '';
    // userToken: any = '';

    /*
    *
        paramas are used while getting List Results from the webservice.
    *
    */

    paramas = { "projectId": 1, "offset": this.offsetIndex, "pagesize": this.start, "sortvalue": "Id", "sortorder": "asc", "userInfo": { "Id": "", "username": "", "token": "" } };
    constructor(public navCtrl: NavController,
        public navParams: NavParams,
        public loadingController: LoadingController,
        public popoverCtrl: PopoverController,
        public alertController: AlertController,
        private storage: Storage,
        public viewCtrl: ViewController,
        private globalService: Globalservice,
        private urlConstants: Constants) {
        this.arrayObject = [];

    }

    /**
          is called once the screen is loaded
      
       */

    ionViewDidLoad() {

        /** 
         * User Local storage details getting logic.
         
         * **/

        this.storage.get('userCredentials').then((value) => {
            this.userName = value.username;
            this.paramas.userInfo.Id = value.Id;
            this.paramas.userInfo.username = value.username;
            this.paramas.userInfo.token = value.token;
        });

        /** 
         * Displaying stories list when first time loading the screen.
        * 
        * **/
        this.getAllStoriesList();
    }


    /**
           pop up for logout
       
        */
    openPopover(myEvent) {
        let userCredentials = { username: this.userName };
        let popover = this.popoverCtrl.create(PopoverPage, userCredentials);
        console.log("User name is " + this.userName);
        popover.present({
            ev: myEvent
        });
    }

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
                this.items = data.data;
                console.log("the count value is " + this.items.length);

                console.log("params are" + JSON.stringify(this.paramas));

                if (this.items.length == 0) {
                    this.moreDataLoaded = false;
                }
                for (let ticket = 0; ticket < this.items.length; ticket++) {
                    var _id = this.items[ticket][0].field_value;
                    var _title = this.items[ticket][1].field_value;
                    var _assignTo = this.items[ticket][2].field_value;
                    var _priority = this.items[ticket][3].field_value;
                    var _workflow = this.items[ticket][4].field_value;
                    var _bucket = this.items[ticket][5].field_value;
                    var _dudate = this.items[ticket][6].field_value;

                    this.arrayObject.push({
                        id: _id, title: _title, assignTo: _assignTo, priority: _priority, workflow: _workflow, bucket: _bucket, duedate: _dudate
                    });
                }
                this.paramas.offset = (this.paramas.offset) + 1;
            },
            error => {

            },
            () => console.log('login api call complete')
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
        var clickedItemId = { "id": item.id };
        this.navCtrl.push(StoryDetailsPage, clickedItemId);
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
