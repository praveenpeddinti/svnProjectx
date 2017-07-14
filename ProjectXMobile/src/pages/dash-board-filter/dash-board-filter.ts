import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams, ModalController, LoadingController } from 'ionic-angular';
import { CustomModalPage } from '../custom-modal/custom-modal';
import { FilterModal } from '../filter-modal/filter-modal';
import { Globalservice } from '../../providers/globalservice';
import { Constants } from '../../providers/constants';
import { DashboardPage } from '../dashboard/dashboard';

/**
 * Generated class for the DashBoardFilter page.
 *
 * See http://ionicframework.com/docs/components/#navigation for more info
 * on Ionic pages and navigation.
 */
@IonicPage()
@Component({
  selector: 'page-dash-board-filter',
  templateUrl: 'dash-board-filter.html',
})
export class DashBoardFilter {
  public items: Array<any>;
  userName: any = '';
  public headerName: any;
  SelectValue: any;
  public loader = this.loadingController.create({ content: "Loading..." });
  params = { "userId": 15, "page": 0, "limit": 5 };
  public moreDataLoaded: boolean = false;
  constructor(public navCtrl: NavController, public navParams: NavParams,
    public loadingController: LoadingController,
    private globalService: Globalservice,
    private urlConstants: Constants) {
    this.headerName = "DashBoard Filter";
    localStorage.setItem('headerInfo', JSON.stringify({ 'title': this.headerName, 'backButton': "hideBackButton", 'logo': 1, 'leftPannel': 0, 'searchBar': 1, notification: 1, profile: 1 }));
    var userInfo = JSON.parse(localStorage.getItem("userCredentials"));
    console.log("this.userNameId" + JSON.stringify(userInfo.Id));
    this.params.userId = userInfo.Id;
    this.globalService.getallProjectsList(this.urlConstants.getProjectsByUserId, this.params).subscribe(
      (result) => {
        console.log("DashBoardFilter" + JSON.stringify(result));
        this.items = result.data;
      });

  }
  ionViewDidLoad() {
    console.log('ionViewDidLoad DashBoardFilter');
  }

  public isEven(i): boolean {
    return i % 2 == 0 ? true : false;
  }


  public openProject(item): void {
    var clickedItem = { "ProjectId": item.PId ,"ProjectName":item.ProjectName};
    this.navCtrl.push(DashboardPage, clickedItem);
    console.log("project Name" + item.ProjectName);
  }


  public doInfinite(infiniteScroll) {
    setTimeout(() => {
      if (this.moreDataLoaded == true) {
        this.params.page = this.params.page + 1;
        this.globalService.getallProjectsList(this.urlConstants.getProjectsByUserId, this.params).subscribe(
          (result) => {
            console.log("DashBoardFilter" + JSON.stringify(result));
            this.items = result.data;
          });
        infiniteScroll.complete();
      } else {
        infiniteScroll.complete();
      }
    }, 2000);

  }
}
