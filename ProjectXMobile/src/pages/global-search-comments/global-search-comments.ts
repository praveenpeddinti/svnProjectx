import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams, App, ViewController } from 'ionic-angular';
import { Http, Headers } from '@angular/http';
import { Constants } from '../../providers/constants';
import { Globalservice } from '../../providers/globalservice';
import {StoryDetailsPage} from '../../pages/story-details/story-details';
/**
 * Generated class for the GlobalSearchComments page.
 *
 * See http://ionicframework.com/docs/components/#navigation for more info
 * on Ionic pages and navigation.
 */
@IonicPage()
@Component({
  selector: 'page-global-search-comments',
  templateUrl: 'global-search-comments.html',
})
export class GlobalSearchComments {
    searchValue :any;
    public dataComment: Array<any>;
    public moreDataLoaded: boolean = true;
    
  constructor(protected app: App,public navCtrl: NavController, public navParams: NavParams,
       private http:Http,private constants: Constants,
       public globalService: Globalservice, public viewCtrl: ViewController) {
       
        this.searchValue = this.navParams.data.searchValue;
        console.log("Search value from story is" + this.searchValue);
      console.log("Search value from comment is" + this.searchValue);
      var getglobalParams = {page: 1,searchFlag: 2, searchString: this.searchValue};
      console.log("this.searchString" + this.searchValue);
      this.globalService.getGlobalSearch(this.constants.globalSearch, getglobalParams).subscribe(
          (result) => {
              this.dataComment = result.data.ticketComments;
              console.log("ticketCollection comment" + JSON.stringify(this.dataComment));
              if (this.dataComment.length == 0) {
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
ionViewDidEnter(){
    this.searchValue = this.navParams.data.searchValue;
        console.log("Search value from story is" + this.searchValue);
      console.log("Search value from comment is" + this.searchValue);
      var getglobalParams = {page: 1,searchFlag: 2, searchString: this.searchValue};
      console.log("this.searchString" + this.searchValue);
      this.globalService.getGlobalSearch(this.constants.globalSearch, getglobalParams).subscribe(
          (result) => {
              this.dataComment = result.data.ticketComments;
              console.log("ticketCollection comment" + JSON.stringify(this.dataComment));
              if (this.dataComment.length == 0) {
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
    console.log('ionViewDidLoad GlobalSearchComments');
  }
   public errorMessage: string="No results found.";
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
        var clickedItemId = {"id": item.TicketId.TicketId};
        this.app.getRootNav().push(StoryDetailsPage, clickedItemId);
    }
    public getAllsearchResults():void{
        this.moreDataLoaded =false;
        this.errorMessage="That’s all. No results found.";
    }

}
