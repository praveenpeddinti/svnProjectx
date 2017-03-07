import { Component } from '@angular/core';
import { NavController, NavParams, AlertController, ViewController, LoadingController, PopoverController } from 'ionic-angular';
import { HomePage } from '../home/home';
import { LoginPage } from '../login/login';
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
    templateUrl: 'dashboard.html'
})
export class DashboardPage {
    public items: Array<any>;
    public start: number = 10;
    public offsetIndex: number = 0;
    public arrayObject: Array<{ id: string, title: string, assignTo: string, priority: string, workflow: string, bucket: string, duedate: string }>;
    public moreDataLoaded: boolean = true;

    userName: any = '';
    userPassword: string = '';
    userToken: any = '';
    paramas = {"projectId": 1, "offset": this.offsetIndex, "pagesize": this.start, "sortvalue": "Id", "sortorder": "asc", "userInfo": { "Id": "9", "username": "hareesh.bekkam", "token": "045cdabd2bfc0bc46571@9" } };
    constructor(public navCtrl: NavController,
        public navParams: NavParams,
        public loadingController: LoadingController,
        public popoverCtrl: PopoverController,
        public alertController: AlertController,
        private storage: Storage,
        public viewCtrl: ViewController,
        private globalService: Globalservice,
        private urlConstants: Constants) {

        this.storage.get('userCredentials').then((value) => {
            console.log("in did load " + value.username);
            this.userName = value.username;
        });
        this.arrayObject = [];

    }

    ionViewDidLoad() {
        console.log('ionViewDidLoad DashboardPage');
        this.getAllStoriesList();
    }
    openPopover(myEvent) {
        let userCredentials = {username: this.userName };
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
        console.log("params are ");
        console.log(this.paramas);
        this.globalService.getStoriesList(this.urlConstants.getAllTicketDetails, this.paramas).subscribe(
            data => {
                this.items = data.data;
                console.log("the count value is " + this.items.length);

                if (this.items.length == 0) {
                    this.moreDataLoaded = false;
                }
                //console.log(this.paramas);
                console.log(this.items);

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
                console.log("the error " + JSON.stringify(error));
            },
            () => console.log('login api call complete')
        );
    }

    /**
      used for click event on list
      author uday   
  
   */
    ticketClick(item) {

        console.log("id is" + item.id);
        //   this.navCtrl.push(ItemDetailsPage, {
        //     item: item
        //  });
    }

    public storyDetail(): void {
        this.navCtrl.push(StoryDetailsPage);
    }

    doInfinite(infiniteScroll) {

        console.log("sample scrolling");

        setTimeout(() => {
            if (this.moreDataLoaded == true) {
                console.log('Async operation has ended');
                this.getAllStoriesList();
                infiniteScroll.complete();
            } else {
                console.log('failing condition');
                infiniteScroll.complete();
            }
        }, 2000);

    }
}
