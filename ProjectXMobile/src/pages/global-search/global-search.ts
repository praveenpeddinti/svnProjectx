import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams } from 'ionic-angular';
import { Http, Headers } from '@angular/http';
import { Constants } from '../../providers/constants';
import { Globalservice } from '../../providers/globalservice';
import { GlobalSearchAll } from '../global-search-all/global-search-all';
import {GlobalSearchArtifacts} from '../global-search-artifacts/global-search-artifacts';
import {GlobalSearchComments} from '../global-search-comments/global-search-comments';
import {GlobalSearchStorytask} from '../global-search-storytask/global-search-storytask';
import {GlobalSearchUsers} from '../global-search-users/global-search-users';
/**
 * Generated class for the GlobalSearch page.
 *
 * See http://ionicframework.com/docs/components/#navigation for more info
 * on Ionic pages and navigation.
 */
@IonicPage()
@Component({
  selector: 'page-global-search',
  templateUrl: 'global-search.html',
})
export class GlobalSearch {
    GlobalSearchAll:any;
    GlobalSearchArtifacts:any;
    GlobalSearchComments:any;
    GlobalSearchStorytask:any;
    GlobalSearchUsers:any;
 
   
   public searchValue : any;
     public rootParams: any = {searchValue: "" ,dataCollection:{} };
    //public getglobalParams = { page: 1, searchString:"moin"};
    public dataCollection = [];
    public allList: Array<{ id: string, title: string, description: string, planleve: string, reportedby: string, updatedon: string }>;
    public errorMessage: string="No results found.";
  constructor(public navCtrl: NavController, public navParams: NavParams,
      private http:Http,private constants: Constants,
       public globalService: Globalservice) {
      localStorage.setItem('headerInfo',JSON.stringify({'title':"",backButton:"",logo:0,leftPannel:0}));
      this.GlobalSearchAll = GlobalSearchAll;
      this.GlobalSearchArtifacts = GlobalSearchArtifacts;
      this.GlobalSearchComments = GlobalSearchComments;
      this.GlobalSearchStorytask = GlobalSearchStorytask;
      this.GlobalSearchUsers = GlobalSearchUsers;

      this.searchValue = this.navParams.get("searchValue");
     // alert(this.searchValue);
      this.rootParams.searchValue = this.searchValue;
//      var searchItem = JSON.parse(localStorage.getItem("searchData"));
//      this.searchValue = searchItem.search;
//      console.log("dashboard searchvalue" + this.searchValue);
  }
  
  ionViewDidEnter(){
      
       //this.rootParams.searchValue =  this.navParams.get("searchValue");
     // console.log("ionViewWillEnter" + JSON.stringify(this.rootParams.searchValue));
  }
    search(event){
        var thisObj=this;
        this.rootParams.searchValue = this.searchValue;
       console.log("root param<><><><><<222222222" + JSON.stringify(this.rootParams.searchValue));
         var getglobalParams = { page: 1, searchString:this.searchValue};
         console.log("this.searchString" + this.searchValue);      
         if (getglobalParams.searchString!= '') {
             thisObj.globalService.setActivity(getglobalParams.searchString);
         }       
        this.globalService.getGlobalSearch(this.constants.globalSearch, getglobalParams).subscribe(
       (result) => {
           this.dataCollection = result.data;
            this.rootParams.dataCollection = this.dataCollection;
           
             if (this.dataCollection.length == 0) {
                        this.moreDataLoaded = false;
                         this.errorMessage="No results found.";
                    }else{
                
            this.errorMessage="That’s all. No results found.";
            }
          
       },(error) => {
       
         this.errorMessage="No results found.";
      });
}

  ionViewDidLoad() {
    console.log('ionViewDidLoad GlobalSearch');
  }
  public moreDataLoaded: boolean = true;
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
    
    public getAllsearchResults():void{
        this.moreDataLoaded =false;
        this.errorMessage="That’s all. No results found.";
    }

}
