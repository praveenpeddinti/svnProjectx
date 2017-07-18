import { Component, NgZone } from '@angular/core';
import { IonicPage, NavController, NavParams, ViewController } from 'ionic-angular';
import { Http, Headers } from '@angular/http';
import { Constants } from '../../providers/constants';
import { Globalservice } from '../../providers/globalservice';
import {StoryDetailsPage} from '../../pages/story-details/story-details';
import { App } from 'ionic-angular';
/**
 * Generated class for the GlobalSearchAll page.
 *
 * See http://ionicframework.com/docs/components/#navigation for more info
 * on Ionic pages and navigation.
 */
@IonicPage()
@Component({
  selector: 'page-global-search-all',
  templateUrl: 'global-search-all.html',
})
export class GlobalSearchAll {
    
    public errorMessage: string="No results found.";
    searchValue = "";
    dataValue = {};
    public dataCollection: Array<any>;
    public allList: Array<{ id: string, title: string, description: string, planleve: string, reportedby: string, updatedon: string }>;
    public moreDataLoaded: boolean = true;

  constructor(protected app: App,public navCtrl: NavController, public navParams: NavParams,
       private http:Http,private constants: Constants,
       public globalService: Globalservice,private ngZone: NgZone,public viewCtrl: ViewController) {
       
        //this.searchValue = "fd";
        this.searchValue = this.navParams.data.searchValue;
         this.dataValue = this.navParams.data.dataCollection;
        console.log("Search value from all is" + this.searchValue);
        console.log("constructor dataValue" + JSON.stringify(this.dataValue));
        var getglobalParams = {page: 1,searchFlag: 1, searchString: this.searchValue};
        console.log("this.searchString" + this.searchValue);
        this.globalService.getGlobalSearch(this.constants.globalSearch, getglobalParams).subscribe(
            (result) => {
                this.ngZone.run(() => {
                this.dataCollection = result.data.ticketCollection;
                
                console.log("ticketCollection globalall" + JSON.stringify(this.dataCollection));
                if (this.dataCollection.length == 0) {
                        this.moreDataLoaded = false;
                         this.errorMessage="No results found.";
                    }else{
                
            this.errorMessage="That’s all. No results found.";
            }
                });
            }, (error) => {
             this.errorMessage="No results found.";
            });
  }
  ionViewDidEnter(){
            this.searchValue = this.navParams.data.searchValue;
            this.dataValue = this.navParams.data.dataCollection;
        console.log("Search value from all is" + this.searchValue);
        console.log("ionViewDidEnter dataValue" + JSON.stringify(this.dataValue));
        var getglobalParams = {page: 1,searchFlag: 1, searchString: this.searchValue};
        console.log("this.searchString" + this.searchValue);
        this.globalService.getGlobalSearch(this.constants.globalSearch, getglobalParams).subscribe(
            (result) => {
                this.dataCollection = result.data.ticketCollection;
                console.log("ticketCollection globalall" + JSON.stringify(this.dataCollection));
            if (this.dataCollection.length == 0) {
                        this.moreDataLoaded = false;
                        this.errorMessage="No results found.";
            }else{
                
            this.errorMessage="That’s all. No results found.";
            }
            }, (error) => {
             this.errorMessage="No results found.";
            });
    console.log('ionViewDidLoad GlobalSearchAll');

  }
  public getErrorMessage():string{
      
      return this.getErrorMessage();
  }
  ionViewDidLoad() {
      this.searchValue = this.navParams.data.searchValue;
      this.dataValue = this.navParams.data.dataCollection;
       console.log("ionViewDidLoad dataValue" + JSON.stringify(this.dataValue));
        console.log("Search value from all is" + this.searchValue);
        var getglobalParams = {page: 1,searchFlag: 1, searchString: this.searchValue};
        console.log("this.searchString" + this.searchValue);
        this.globalService.getGlobalSearch(this.constants.globalSearch, getglobalParams).subscribe(
            (result) => {
                this.dataCollection = result.data.ticketCollection;
                console.log("ticketCollection globalall" + JSON.stringify(this.dataCollection));
            if (this.dataCollection.length == 0) {
                        this.moreDataLoaded = false;
            }
            }, (error) => {
            });
    console.log('ionViewDidLoad GlobalSearchAll');
  }
  
//  added uday
  
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
    public openDetails(item): void {
        var clickedItemId = {"id": item.TicketId};
        console.log(JSON.stringify("the item id" + JSON.stringify(item)));
        this.app.getRootNav().push(StoryDetailsPage, clickedItemId);
    }    
    
    public getAllsearchResults():void{
        this.moreDataLoaded =false;
        this.errorMessage="That’s all. No results found.";
    }

}
