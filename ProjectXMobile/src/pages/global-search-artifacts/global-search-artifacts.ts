import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams, App } from 'ionic-angular';
import { Http, Headers } from '@angular/http';
import { Constants } from '../../providers/constants';
import { Globalservice } from '../../providers/globalservice';
import {StoryDetailsPage} from '../../pages/story-details/story-details';
/**
 * Generated class for the GlobalSearchArtifacts page.
 *
 * See http://ionicframework.com/docs/components/#navigation for more info
 * on Ionic pages and navigation.
 */
@IonicPage()
@Component({
  selector: 'page-global-search-artifacts',
  templateUrl: 'global-search-artifacts.html',
})
export class GlobalSearchArtifacts {
    public dataArtifacts: Array<any>;
    searchValue = "";
    public moreDataLoaded: boolean = true;
    public toggled: boolean;
    
  constructor(protected app: App,public navCtrl: NavController, public navParams: NavParams,
      private http:Http,private constants: Constants,
       public globalService: Globalservice) {
       this.toggled = false;
       
      this.searchValue = this.navParams.data.searchValue;
      console.log("Search value from story is" + this.searchValue);
      var getglobalParams = {page: 1,searchFlag: 4, searchString: this.searchValue};
      console.log("this.searchString" + this.searchValue);
      this.globalService.getGlobalSearch(this.constants.globalSearch, getglobalParams).subscribe(
          (result) => {
              this.dataArtifacts = result.data.ticketArtifacts;
              console.log("this.dataArtifacts.length" + this.dataArtifacts.length);
              console.log("ticketCollection artifacts" + JSON.stringify(this.dataArtifacts));
            if (this.dataArtifacts.length == 0) {
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
//  toggle() {
//      console.log("clicked toggle");
//       this.toggled = this.toggled ? false : true;
//    }
//    search(event){
//         var getglobalParams = { page: 1, searchString:this.searchValue};
//         console.log("this.searchString" + this.searchValue);
//        this.globalService.getGlobalSearch(this.constants.globalSearch, getglobalParams).subscribe(
//       (result) => {
//           this.dataCollection = result.data.ticketArtifacts;
//           console.log("ticketCollection main page" + JSON.stringify(this.dataCollection));
//       },(error) => {
//      });
//}
    ionViewDidEnter(){
        this.searchValue = this.navParams.data.searchValue;
      console.log("Search value from story is" + this.searchValue);
      var getglobalParams = {page: 1,searchFlag: 4, searchString: this.searchValue};
      console.log("this.searchString" + this.searchValue);
      this.globalService.getGlobalSearch(this.constants.globalSearch, getglobalParams).subscribe(
          (result) => {
              this.dataArtifacts = result.data.ticketArtifacts;
              console.log("this.dataArtifacts.length" + this.dataArtifacts.length);
              console.log("ticketCollection artifacts" + JSON.stringify(this.dataArtifacts));
            if (this.dataArtifacts.length == 0) {
                        this.moreDataLoaded = false;
                         this.errorMessage="No results found.";
               }else{
                   this.errorMessage="That’s all. No results found.";
               
               }
            
          }, (error) => {
           this.errorMessage="No results found.";
          });
    }
  ionViewDidLoad() {
    console.log('ionViewDidLoad GlobalSearchArtifacts');
  }
      public openDetails(item): void {
        var clickedItemId = {"id": item.TicketId};
        console.log(JSON.stringify("the item id" + JSON.stringify(item)));
        this.app.getRootNav().push(StoryDetailsPage, clickedItemId);
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
