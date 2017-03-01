import { Component } from '@angular/core';
import { NavController, NavParams, AlertController, ViewController, LoadingController, PopoverController  } from 'ionic-angular';
import { HomePage } from '../home/home';
import {Storage} from "@ionic/storage";
import { PopoverPage } from '../popover/popover';
import { StoryDetailsPage } from '../story-details/story-details'
import { Globalservice } from '../../providers/globalservice';
import {Constants} from '../../providers/constants';
/*
  Generated class for the Welcome page.

  See http://ionicframework.com/docs/v2/components/#navigation for more info on
  Ionic pages and navigation.
*/
@Component({
  selector: 'page-welcome',
  templateUrl: 'welcome.html',
  providers: [Globalservice, Constants]
})
export class WelcomePage {
  login: {username?: string, password?: string,token?:any} = {};
  public options = "options";
  changeStatus="";
  public items: Array<any>;
  public arrayObject: Array<{ id: string, title: string, assignTo: string, priority: string, workflow: string, bucket: string, duedate: string }>;
  userName: string='';
  userPassword:string='';
  userToken:any='';
  paramas = { "projectId": 1, "offset": 0, "pagesize": 10, "sortvalue": "Id", "sortorder": "desc", "userInfo": { "Id": "9", "username": "hareesh.bekkam", "token": "a1a3d7b95e950cbc1cb7@9" } };
  constructor(private globalService: Globalservice,private constants: Constants, public navCtrl: NavController, public navParams: NavParams, public loadingController: LoadingController,
  public popoverCtrl: PopoverController,
  public alertController: AlertController, private storage:Storage,   public viewCtrl: ViewController, private listService: Globalservice, private urlConstants: Constants) {
  //   let loader = this.loadingController.create({ content: "Loading..."});  
  //  loader.present();
 
  }
    
  ionViewDidLoad() {
    console.log('ionViewDidLoad WelcomePage');
    this.userName = this.navParams.get("username");
    this.userPassword = this.navParams.get("password");
    this.userToken = this.navParams.get("token");
    console.log("Tken value" + this.userToken);
     this.getAllStoriesList();
    
  }

  /**
      used for getting all the stories 
      author uday   
  
   */
  getAllStoriesList(): void {
    this.listService.getStoriesList(this.urlConstants.getAllTicketDetails, this.paramas).subscribe(
      data => {
        this.items = data.data;
        console.log("the count value is " + this.items.length);
        this.arrayObject = [];
        for (let i = 0; i < this.items.length; i++) {
          var _id = this.items[i][0].field_value;
          var _title = this.items[i][1].field_value;
          var _assignTo = this.items[i][2].field_value;
          var _priority = this.items[i][3].field_value;
          var _workflow = this.items[i][4].field_value;
          var _bucket = this.items[i][5].field_value;
          var _dudate = this.items[i][6].field_value;

          this.arrayObject.push({
            id: _id, title: _title, assignTo: _assignTo, priority: _priority, workflow: _workflow, bucket: _bucket, duedate: _dudate
          });
        }
      },
      error => {
        console.log("the error " + JSON.stringify(error));
      },
      () => console.log('login api call complete')
    );
  }

  openPopover(myEvent) {
    let userCredentials = {username: this.userName};
    let popover = this.popoverCtrl.create(PopoverPage,userCredentials);
    console.log("User name is "+ this.userName);
    popover.present({
      ev: myEvent
    });
  }
logoutApp() {
  this.globalService.getLogout(this.constants.LogutUrl,this.login).subscribe(
    data =>{
  let alert = this.alertController.create({
    title: 'Confirm Log Out',
    message: 'Are you sure you want to log out?',
    buttons: [
      {
        text: 'Cancel',
        role: 'cancel',
        handler: () => {
          console.log('Cancel clicked');
        }
      },
      {
        text: 'Log Out',
        handler: () => {
        this.storage.remove('username');
        this.storage.remove('password');
        this.storage.remove('token');
        this.navCtrl.setRoot(HomePage);
        console.log('Logged out');
        this.viewCtrl.dismiss();
        
        }
      }
    ]
  });
  alert.present();
    },
      error=>
      { console.log("the error " + JSON.stringify(error)); },
      ()=> console.log('logout api call complete'));
      }
 public changeOption(event){
    console.log("the options --- " + this.options + " -------------");
    console.log("the change " + JSON.stringify(event) );
  }
public storyDetail(): void{
      this.navCtrl.push(StoryDetailsPage);
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



}
