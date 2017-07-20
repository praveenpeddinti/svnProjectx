import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams, AlertController } from 'ionic-angular';
import { Http, Headers } from '@angular/http';
import { Constants } from '../../providers/constants';
import { Globalservice } from '../../providers/globalservice';
/**
 * Generated class for the GlobalSearchUsers page.
 *
 * See http://ionicframework.com/docs/components/#navigation for more info
 * on Ionic pages and navigation.
 */
@IonicPage()
@Component({
  selector: 'page-global-search-users',
  templateUrl: 'global-search-users.html',
})
export class GlobalSearchUsers {
    searchValue = "";
    public dataAllUser: Array<any>;
    public moreDataLoaded: boolean = true;
    
  constructor(public navCtrl: NavController, public navParams: NavParams,
       private http:Http,private constants: Constants,
       public globalService: Globalservice, private alertController: AlertController) {
       
      this.searchValue = this.navParams.data.searchValue;
      console.log("Search value from alluser is" + this.searchValue);
      var getglobalParams = {page: 1,searchFlag: 3, searchString: this.searchValue};
      this.globalService.getGlobalSearch(this.constants.globalSearch, getglobalParams).subscribe(
          (result) => {
              this.dataAllUser = result.data.tinyUserData;
              console.log("ticketCollection user" + JSON.stringify(this.dataAllUser));
               if (this.dataAllUser.length == 0) {
                        this.moreDataLoaded = false;
                         this.errorMessage="No results found.";
               }
               
               else{
                this.errorMessage="That’s all. No results found.";  
              
              }
          }, (error) => {
           this.errorMessage="No results found.";
          });
  }
//    search(event){
//         var getglobalParams = { page: 1, searchString:this.searchValue};
//         console.log("this.searchString" + this.searchValue);
//        this.globalService.getGlobalSearch(this.constants.globalSearch, getglobalParams).subscribe(
//       (result) => {
//           this.dataCollection = result.data.tinyUserData;
//           console.log("ticketCollection main page" + JSON.stringify(this.dataCollection));
//       },(error) => {
//      });
//}
  ionViewDidEnter(){
      this.searchValue = this.navParams.data.searchValue;
      console.log("Search value from alluser is" + this.searchValue);
      var getglobalParams = {page: 1,searchFlag: 3, searchString: this.searchValue};
      this.globalService.getGlobalSearch(this.constants.globalSearch, getglobalParams).subscribe(
          (result) => {
              this.dataAllUser = result.data.tinyUserData;
              console.log("ticketCollection user" + JSON.stringify(this.dataAllUser));
               if (this.dataAllUser.length == 0) {
                        this.moreDataLoaded = false;
                         this.errorMessage="No results found.";
               }
               else{
                   
                   this.errorMessage="That’s all. No results found.";
               }
          }, (error) => {
           this.errorMessage="No results found.";
          });
  }
  ionViewDidLoad() {
         this.globalService.getActivity().subscribe(value=>
      {
          console.log("search value from emitted"+JSON.stringify(value.activityData));
          if (value.activityData == undefined){
           let alert = this.alertController.create({
            title: 'Warning!',
            message: 'Please enter search value!',
            buttons: [
                {
                    text: 'OK',
                    role: 'cancel',
                    handler: () => { }
                }
            ]
        });
        alert.present();
          }else{
              this.searchValue = value.activityData; 
               var getglobalParams = {page: 1,searchFlag: 3, searchString: this.searchValue};
              this.globalService.getGlobalSearch(this.constants.globalSearch, getglobalParams).subscribe(
                  (result) => {
                       this.dataAllUser = result.data.tinyUserData;
                      console.log("ticketCollection globalall" + JSON.stringify(this.dataAllUser));
                      if (this.dataAllUser.length == 0) {
                          this.moreDataLoaded = false;
                      }
                  }, (error) => {
                  });
          }
      })
  }
  
  public doInfinite(infiniteScroll) {
        setTimeout(() => {
            if (this.moreDataLoaded == true) {
                this.getAllsearchResults();
                infiniteScroll.complete();
            } else {
                infiniteScroll.complete();
            }
        }, 2000);

    }    
     public errorMessage: string="No results found.";
    public getAllsearchResults():void{
        this.moreDataLoaded =false;
        this.errorMessage="That’s all. No results found.";
    }


}
